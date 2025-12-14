<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

final class GMPR_Async_Refresh {
	private const REST_NAMESPACE = 'gmpr/v1';
	private const ROUTE_ROSTER = '/roster';
	private const ROUTE_REFRESH = '/refresh';
	private const CRON_HOOK = 'gmpr_refresh_roster';
	private const LOCK_PREFIX = 'gmpr_refresh_lock_';

	public static function init(): void {
		add_action('rest_api_init', array(__CLASS__, 'register_routes'));
		add_action(self::CRON_HOOK, array(__CLASS__, 'cron_refresh_roster'), 10, 1);
	}

	public static function register_routes(): void {
		register_rest_route(
			self::REST_NAMESPACE,
			self::ROUTE_ROSTER,
			array(
				'methods' => 'GET',
				'permission_callback' => '__return_true',
				'callback' => array(__CLASS__, 'rest_get_roster'),
				'args' => array(
					'region' => array('required' => true),
					'realm' => array('required' => true),
					'guild' => array('required' => true),
					'sig' => array('required' => true),
				),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			self::ROUTE_REFRESH,
			array(
				'methods' => 'POST',
				'permission_callback' => '__return_true',
				'callback' => array(__CLASS__, 'rest_trigger_refresh'),
				'args' => array(
					'region' => array('required' => true),
					'realm' => array('required' => true),
					'guild' => array('required' => true),
					'sig' => array('required' => true),
				),
			)
		);
	}

	/**
	 * @return array<string, mixed>|\WP_REST_Response
	 */
	public static function rest_get_roster(\WP_REST_Request $request) {
		$ctx = self::read_context_from_request($request);
		if (is_wp_error($ctx)) {
			return $ctx;
		}

		$settings = GMPR_Settings::get_settings();
		$ttl_seconds = isset($settings['ttl_seconds']) ? (int) $settings['ttl_seconds'] : (15 * MINUTE_IN_SECONDS);
		if ($ttl_seconds < 60) {
			$ttl_seconds = 60;
		}

		$cache = new GMPR_Cache();
		$cache_key = $cache->build_guild_cache_key($ctx['region'], $ctx['realm_slug'], $ctx['guild_cache_key']);

		$fresh = $cache->get_fresh($cache_key);
		if (is_array($fresh)) {
			$async = array(
				'async' => true,
				'region' => $ctx['region'],
				'realm' => $ctx['realm_slug'],
				'guild' => $ctx['guild_name'],
				'sig' => (string) $request->get_param('sig'),
				'fetched_at' => isset($fresh['fetched_at']) ? (int) $fresh['fetched_at'] : 0,
				'refresh_needed' => false,
			);
			return array(
				'status' => 'ready',
				'is_stale' => false,
				'fetched_at' => isset($fresh['fetched_at']) ? (int) $fresh['fetched_at'] : 0,
				'data' => $fresh,
				'html' => GMPR_Renderer::render_guild_table($fresh, false, $async),
			);
		}

		$stale = $cache->get_stale($cache_key);
		if (is_array($stale)) {
			$stale_fetched_at = isset($stale['fetched_at']) ? (int) $stale['fetched_at'] : 0;
			$stale_is_recent = ($stale_fetched_at > 0) && ((time() - $stale_fetched_at) <= $ttl_seconds);
			$is_stale = !$stale_is_recent;

			$async = array(
				'async' => true,
				'region' => $ctx['region'],
				'realm' => $ctx['realm_slug'],
				'guild' => $ctx['guild_name'],
				'sig' => (string) $request->get_param('sig'),
				'fetched_at' => $stale_fetched_at,
				'refresh_needed' => $is_stale,
			);
			return array(
				'status' => 'ready',
				'is_stale' => $is_stale,
				'fetched_at' => $stale_fetched_at,
				'data' => $stale,
				'html' => GMPR_Renderer::render_guild_table($stale, $is_stale, $async),
			);
		}

		return array('status' => 'pending');
	}

	/**
	 * @return array<string, mixed>|\WP_REST_Response
	 */
	public static function rest_trigger_refresh(\WP_REST_Request $request) {
		$ctx = self::read_context_from_request($request);
		if (is_wp_error($ctx)) {
			return $ctx;
		}

		// Execute refresh immediately instead of scheduling (works on localhost)
		$args = array(
			'region' => $ctx['region'],
			'realm'  => $ctx['realm_slug'],
			'guild'  => $ctx['guild_name'],
		);

		// Check throttle to prevent abuse
		$lock_key = self::LOCK_PREFIX . md5($ctx['region'] . '|' . $ctx['realm_slug'] . '|' . $ctx['guild_name']);
		if (get_transient($lock_key)) {
			return array('status' => 'throttled');
		}
		set_transient($lock_key, 1, 60); // 60 second throttle

		self::cron_refresh_roster($args);
		return array('status' => 'refreshed');
	}

	/**
	 * @param array<string, mixed> $args
	 */
	public static function cron_refresh_roster(array $args): void {
		$region = isset($args['region']) ? sanitize_key((string) $args['region']) : '';
		$realm_slug = isset($args['realm']) ? (string) $args['realm'] : '';
		$guild_name = isset($args['guild']) ? (string) $args['guild'] : '';

		$realm_slug = trim($realm_slug);
		$guild_name = trim($guild_name);
		if ($region === '' || $realm_slug === '' || $guild_name === '') {
			return;
		}

		$settings = GMPR_Settings::get_settings();
		$ttl_default = isset($settings['ttl_seconds']) ? (int) $settings['ttl_seconds'] : (15 * MINUTE_IN_SECONDS);
		$ttl = $ttl_default;

		$api_key = GMPR_RaiderIO_Client::resolve_api_key();
		if ($api_key === '') {
			return;
		}

		$cache = new GMPR_Cache();
		$guild_cache_norm = self::normalize_guild_key($guild_name);
		$cache_key = $cache->build_guild_cache_key($region, $realm_slug, $guild_cache_norm);

		$client = new GMPR_RaiderIO_Client($api_key);
		$result = $client->fetch_guild_roster($region, $realm_slug, $guild_name);
		if (is_wp_error($result)) {
			return;
		}

		$normalized = GMPR_RaiderIO_Client::normalize_guild_roster_response($result, $region, $realm_slug);
		$normalized['fetched_at'] = time();
		$normalized = self::apply_member_limit($normalized, $settings);
		$normalized = self::hydrate_member_scores_and_avatars($normalized, $client, $cache, $region, $realm_slug, $ttl);

		$cache->set_fresh($cache_key, $normalized, $ttl);
		$cache->set_stale($cache_key, $normalized);
	}

	public static function schedule_refresh(string $region, string $realm_slug, string $guild_name, int $min_interval_seconds): bool {
		$region = sanitize_key($region);
		$realm_slug = trim($realm_slug);
		$guild_name = trim($guild_name);
		if ($region === '' || $realm_slug === '' || $guild_name === '') {
			return false;
		}

		$lock_key = self::LOCK_PREFIX . md5($region . '|' . $realm_slug . '|' . $guild_name);
		$lock_ttl = $min_interval_seconds > 0 ? $min_interval_seconds : 60;
		if (get_transient($lock_key)) {
			return false;
		}
		set_transient($lock_key, 1, $lock_ttl);

		$args = array(
			'region' => $region,
			'realm'  => $realm_slug,
			'guild'  => $guild_name,
		);

		// Avoid scheduling duplicates with same args.
		if (wp_next_scheduled(self::CRON_HOOK, array($args))) {
			return false;
		}

		return (bool) wp_schedule_single_event(time() + 1, self::CRON_HOOK, array($args));
	}

	/**
	 * @return array{region:string, realm_slug:string, guild_name:string, guild_cache_key:string}|\WP_Error
	 */
	private static function read_context_from_request(\WP_REST_Request $request) {
		$region = sanitize_key((string) $request->get_param('region'));
		$realm = (string) $request->get_param('realm');
		$guild = (string) $request->get_param('guild');
		$sig = (string) $request->get_param('sig');

		$realm_slug = self::normalize_realm_for_raiderio($realm);
		$guild_name = trim($guild);
		if ($region === '' || $realm_slug === '' || $guild_name === '' || $sig === '') {
			return new WP_Error('gmpr_bad_request', 'Invalid request', array('status' => 400));
		}

		$expected = self::sign_context($region, $realm_slug, $guild_name);
		if (!hash_equals($expected, $sig)) {
			return new WP_Error('gmpr_bad_signature', 'Invalid signature', array('status' => 403));
		}

		return array(
			'region' => $region,
			'realm_slug' => $realm_slug,
			'guild_name' => $guild_name,
			'guild_cache_key' => self::normalize_guild_key($guild_name),
		);
	}

	public static function sign_context(string $region, string $realm_slug, string $guild_name): string {
		$payload = $region . '|' . $realm_slug . '|' . $guild_name;
		return hash_hmac('sha256', $payload, wp_salt('auth'));
	}

	private static function normalize_guild_key(string $guild): string {
		$guild = trim($guild);
		if ($guild === '') {
			return '';
		}
		if (function_exists('mb_strtolower')) {
			return mb_strtolower($guild, 'UTF-8');
		}
		return strtolower($guild);
	}

	private static function normalize_realm_for_raiderio(string $realm): string {
		$realm = trim($realm);
		if ($realm === '') {
			return '';
		}

		if (function_exists('mb_strtolower')) {
			$realm = mb_strtolower($realm, 'UTF-8');
		} else {
			$realm = strtolower($realm);
		}

		$realm = str_replace(array('’', '\'', ' '), array('-', '-', '-'), $realm);
		$realm = preg_replace('/[^\p{L}\p{N}-]+/u', '-', $realm);
		$realm = preg_replace('/-+/', '-', (string) $realm);
		return trim((string) $realm, '-');
	}

	/**
	 * @param array<string, mixed> $data
	 * @param array<string, mixed> $settings
	 * @return array<string, mixed>
	 */
	private static function apply_member_limit(array $data, array $settings): array {
		$default = isset($settings['member_limit']) ? (int) $settings['member_limit'] : 20;
		$limit = (int) apply_filters('gmpr_member_limit', $default);
		if ($limit <= 0) {
			return $data;
		}
		if (!isset($data['members']) || !is_array($data['members'])) {
			return $data;
		}
		$data['members'] = array_slice($data['members'], 0, $limit);
		return $data;
	}

	/**
	 * Reuse the same hydration logic as the shortcode (score + avatar). Kept local to avoid exposing private methods.
	 *
	 * @param array<string, mixed> $data
	 * @return array<string, mixed>
	 */
	private static function hydrate_member_scores_and_avatars(
		array $data,
		GMPR_RaiderIO_Client $client,
		GMPR_Cache $cache,
		string $region,
		string $default_realm_slug,
		int $ttl
	): array {
		if (!isset($data['members']) || !is_array($data['members'])) {
			return $data;
		}

		$members = $data['members'];
		foreach ($members as $i => $m) {
			if (!is_array($m)) {
				continue;
			}

			$needs_score = !(isset($m['mplus_score']) && is_numeric($m['mplus_score']));
			$needs_avatar = !(isset($m['avatar_url']) && is_string($m['avatar_url']) && trim((string) $m['avatar_url']) !== '');
			$needs_best_runs = !(isset($m['best_runs']) && is_array($m['best_runs']) && count($m['best_runs']) > 0);
			$needs_meta = !(isset($m['class']) && is_string($m['class']) && trim((string) $m['class']) !== '');
			if (!$needs_score && !$needs_avatar && !$needs_best_runs && !$needs_meta) {
				continue;
			}

			$name = isset($m['name']) ? (string) $m['name'] : '';
			$name = GMPR_RaiderIO_Client::sanitize_character_name($name);
			if ($name === '') {
				continue;
			}

			$realm_raw = isset($m['realm']) ? (string) $m['realm'] : '';
			$realm_slug = self::normalize_realm_for_raiderio($realm_raw);
			if ($realm_slug === '') {
				$realm_slug = $default_realm_slug;
			}

			$char_key = $cache->build_character_cache_key($region, $realm_slug, $name);
			$cached_char = $cache->get_fresh($char_key);
			if (is_array($cached_char)) {
				if ($needs_score && isset($cached_char['mplus_score']) && is_numeric($cached_char['mplus_score'])) {
					$members[$i]['mplus_score'] = (float) $cached_char['mplus_score'];
					$needs_score = false;
				}
				if ($needs_avatar && isset($cached_char['avatar_url']) && is_string($cached_char['avatar_url']) && trim($cached_char['avatar_url']) !== '') {
					$members[$i]['avatar_url'] = (string) $cached_char['avatar_url'];
					$needs_avatar = false;
				}
				if ($needs_best_runs && isset($cached_char['best_runs']) && is_array($cached_char['best_runs']) && count($cached_char['best_runs']) > 0) {
					$members[$i]['best_runs'] = $cached_char['best_runs'];
					$needs_best_runs = false;
				}
				if ($needs_meta) {
					if (isset($cached_char['class']) && is_string($cached_char['class']) && trim((string) $cached_char['class']) !== '') {
						$members[$i]['class'] = (string) $cached_char['class'];
						$needs_meta = false;
					}
					if (isset($cached_char['active_spec_name']) && is_string($cached_char['active_spec_name']) && trim((string) $cached_char['active_spec_name']) !== '') {
						$members[$i]['active_spec_name'] = (string) $cached_char['active_spec_name'];
					}
					if (isset($cached_char['faction']) && is_string($cached_char['faction']) && trim((string) $cached_char['faction']) !== '') {
						$members[$i]['faction'] = (string) $cached_char['faction'];
					}
				}
				if (!$needs_score && !$needs_avatar && !$needs_best_runs && !$needs_meta) {
					continue;
				}
			}

			$char = $client->fetch_character_profile($region, $realm_slug, $name);
			if (is_wp_error($char)) {
				continue;
			}

			$score = null;
			if (isset($char['mythic_plus_scores_by_season']) && is_array($char['mythic_plus_scores_by_season'])) {
				$seasons = $char['mythic_plus_scores_by_season'];
				$first = isset($seasons[0]) && is_array($seasons[0]) ? $seasons[0] : null;
				if ($first && isset($first['scores']) && is_array($first['scores']) && isset($first['scores']['all']) && is_numeric($first['scores']['all'])) {
					$score = (float) $first['scores']['all'];
					$members[$i]['mplus_score'] = $score;
				}
			}

			$avatar = '';
			if (isset($char['thumbnail_url']) && is_string($char['thumbnail_url'])) {
				$avatar = trim($char['thumbnail_url']);
			} elseif (isset($char['avatar_url']) && is_string($char['avatar_url'])) {
				$avatar = trim($char['avatar_url']);
			} elseif (isset($char['portrait_url']) && is_string($char['portrait_url'])) {
				$avatar = trim($char['portrait_url']);
			}
			if ($avatar !== '') {
				$members[$i]['avatar_url'] = $avatar;
			}

			$best_runs = self::extract_best_runs($char);
			if (count($best_runs) > 0) {
				$members[$i]['best_runs'] = $best_runs;
			}

			$meta = self::extract_meta($char);
			foreach ($meta as $k => $v) {
				$members[$i][$k] = $v;
			}

			$cache_value = array(
				'mplus_score' => $score,
				'avatar_url'  => $avatar,
				'best_runs' => $best_runs,
				'class' => isset($meta['class']) ? (string) $meta['class'] : '',
				'active_spec_name' => isset($meta['active_spec_name']) ? (string) $meta['active_spec_name'] : '',
				'faction' => isset($meta['faction']) ? (string) $meta['faction'] : '',
			);
			$cache->set_fresh($char_key, $cache_value, $ttl);
			$cache->set_stale($char_key, $cache_value);
		}

		$data['members'] = $members;
		return $data;
	}

	/**
	 * @param array<string, mixed> $char
	 * @return array<int, array<string, mixed>>
	 */
	private static function extract_best_runs(array $char): array {
		if (!isset($char['mythic_plus_best_runs']) || !is_array($char['mythic_plus_best_runs'])) {
			return array();
		}

		$out = array();
		foreach ($char['mythic_plus_best_runs'] as $run) {
			if (!is_array($run)) {
				continue;
			}

			$short = isset($run['short_name']) && is_string($run['short_name']) ? trim((string) $run['short_name']) : '';
			$level = isset($run['mythic_level']) && is_numeric($run['mythic_level']) ? (int) $run['mythic_level'] : 0;
			if ($short === '' || $level <= 0) {
				continue;
			}

			$out[] = array(
				'dungeon' => isset($run['dungeon']) && is_string($run['dungeon']) ? (string) $run['dungeon'] : '',
				'short_name' => $short,
				'mythic_level' => $level,
				'score' => isset($run['score']) && is_numeric($run['score']) ? (float) $run['score'] : null,
				'background_image_url' => isset($run['background_image_url']) && is_string($run['background_image_url']) ? trim((string) $run['background_image_url']) : '',
				'url' => isset($run['url']) && is_string($run['url']) ? (string) $run['url'] : '',
			);
		}

		return $out;
	}

	/**
	 * @param array<string, mixed> $char
	 * @return array<string, string>
	 */
	private static function extract_meta(array $char): array {
		$out = array();
		if (isset($char['class']) && is_string($char['class']) && trim((string) $char['class']) !== '') {
			$out['class'] = trim((string) $char['class']);
		}
		if (isset($char['active_spec_name']) && is_string($char['active_spec_name']) && trim((string) $char['active_spec_name']) !== '') {
			$out['active_spec_name'] = trim((string) $char['active_spec_name']);
		}
		if (isset($char['faction']) && is_string($char['faction']) && trim((string) $char['faction']) !== '') {
			$out['faction'] = trim((string) $char['faction']);
		}
		return $out;
	}
}


