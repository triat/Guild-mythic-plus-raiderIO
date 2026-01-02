<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

final class GMPR_Plugin {
	private const DEFAULT_MEMBER_LIMIT = 20;

	public static function init(): void {
		load_plugin_textdomain('gmpr', false, basename(GMPR_PLUGIN_DIR) . '/languages');
		add_shortcode('gmpr_guild', array(__CLASS__, 'shortcode_guild'));
		GMPR_Settings::init();
		GMPR_Async_Refresh::init();
	}

	/**
	 * Shortcode: [gmpr_guild region="eu" realm="dalaran" guild="My Guild" ttl="900"]
	 *
	 * @param array<string, mixed> $atts
	 */
	public static function shortcode_guild(array $atts = array()): string {
		$defaults = array(
			'region' => '',
			'realm'  => '',
			'guild'  => '',
			'ttl'    => '', // seconds (optional)
			'refresh' => '', // "1" to bypass cache (admin only)
		);

		$atts = shortcode_atts($defaults, $atts, 'gmpr_guild');

		$settings = GMPR_Settings::get_settings();

		$region = self::resolve_setting_string((string) $atts['region'], $settings, 'region', 'GMPR_REGION');
		$realm  = self::resolve_setting_string((string) $atts['realm'], $settings, 'realm', 'GMPR_REALM');
		$guild  = self::resolve_setting_string((string) $atts['guild'], $settings, 'guild', 'GMPR_GUILD');

		$region = sanitize_key($region);
		$realm_slug = self::normalize_realm_for_raiderio($realm);
		$guild_name = trim($guild);
		$guild_cache_norm = self::normalize_guild_key($guild_name);

		$allowed_regions = array('eu', 'us', 'kr', 'tw', 'cn');
		if ($region === '' || !in_array($region, $allowed_regions, true) || $realm_slug === '' || $guild_name === '') {
			return GMPR_Renderer::render_error(
				__('Invalid configuration: please provide valid region/realm/guild.', 'gmpr')
			);
		}

		$api_key = GMPR_RaiderIO_Client::resolve_api_key();
		if ($api_key === '') {
			return GMPR_Renderer::render_error(
				__('Missing Raider.IO API key: define GMPR_RAIDERIO_API_KEY (or use the gmpr_raiderio_api_key filter).', 'gmpr')
			);
		}

		$ttl_default = isset($settings['ttl_seconds']) ? (int) $settings['ttl_seconds'] : (15 * MINUTE_IN_SECONDS);
		$ttl = self::parse_ttl_seconds((string) $atts['ttl'], $ttl_default);
		$refresh_requested = self::parse_bool((string) $atts['refresh']);
		$can_refresh = $refresh_requested && is_user_logged_in() && current_user_can('manage_options');

		$cache = new GMPR_Cache();
		$cache_key = $cache->build_guild_cache_key($region, $realm_slug, $guild_cache_norm);

		// SWR: render fresh when available; otherwise render stale/loading and refresh asynchronously.
		$cached = $can_refresh ? null : $cache->get_fresh($cache_key);
		if (is_array($cached)) {
			$cached = self::apply_member_limit($cached, $settings);
			self::enqueue_assets();
			return GMPR_Renderer::render_guild_table($cached, false, null);
		}

		$async = array(
			'async' => true,
			'region' => $region,
			'realm' => $realm_slug,
			'guild' => $guild_name,
			'sig' => GMPR_Async_Refresh::sign_context($region, $realm_slug, $guild_name),
			'fetched_at' => 0,
			'refresh_needed' => true,
		);

		// If we have stale cache, render it immediately and refresh in background.
		$stale = $cache->get_stale($cache_key);
		if (is_array($stale)) {
			$stale = self::apply_member_limit($stale, $settings);
			$async['fetched_at'] = isset($stale['fetched_at']) ? (int) $stale['fetched_at'] : 0;
			$stale_fetched_at = isset($stale['fetched_at']) ? (int) $stale['fetched_at'] : 0;
			$stale_is_recent = ($stale_fetched_at > 0) && ((time() - $stale_fetched_at) <= $ttl);
			if ($stale_is_recent) {
				// Treat recently-updated stale cache as fresh to avoid refresh loops when the fresh transient is missing.
				self::enqueue_assets();
				return GMPR_Renderer::render_guild_table($stale, false, null);
			}

			GMPR_Async_Refresh::schedule_refresh($region, $realm_slug, $guild_name, 60);
			self::enqueue_assets();
			return GMPR_Renderer::render_guild_table($stale, true, $async);
		}

		// Cold start: avoid blocking Raider.IO calls during render.
		GMPR_Async_Refresh::schedule_refresh($region, $realm_slug, $guild_name, 60);
		self::enqueue_assets();
		return GMPR_Renderer::render_loading($async);
	}

	/**
	 * @param array<string, mixed> $data
	 * @return array<string, mixed>
	 */
	private static function hydrate_member_scores(
		array $data,
		GMPR_RaiderIO_Client $client,
		GMPR_Cache $cache,
		string $region,
		string $default_realm_slug,
		int $ttl,
		bool $force_refresh
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
			$cached_char = $force_refresh ? null : $cache->get_fresh($char_key);
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
					if (isset($cached_char['active_spec_role']) && is_string($cached_char['active_spec_role']) && trim((string) $cached_char['active_spec_role']) !== '') {
						$members[$i]['active_spec_role'] = (string) $cached_char['active_spec_role'];
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
				$stale_char = $cache->get_stale($char_key);
				if (is_array($stale_char)) {
					if ($needs_score && isset($stale_char['mplus_score']) && is_numeric($stale_char['mplus_score'])) {
						$members[$i]['mplus_score'] = (float) $stale_char['mplus_score'];
					}
					if ($needs_avatar && isset($stale_char['avatar_url']) && is_string($stale_char['avatar_url']) && trim($stale_char['avatar_url']) !== '') {
						$members[$i]['avatar_url'] = (string) $stale_char['avatar_url'];
					}
					if ($needs_best_runs && isset($stale_char['best_runs']) && is_array($stale_char['best_runs']) && count($stale_char['best_runs']) > 0) {
						$members[$i]['best_runs'] = $stale_char['best_runs'];
					}
					if ($needs_meta) {
						if (isset($stale_char['class']) && is_string($stale_char['class']) && trim((string) $stale_char['class']) !== '') {
							$members[$i]['class'] = (string) $stale_char['class'];
						}
						if (isset($stale_char['active_spec_name']) && is_string($stale_char['active_spec_name']) && trim((string) $stale_char['active_spec_name']) !== '') {
							$members[$i]['active_spec_name'] = (string) $stale_char['active_spec_name'];
						}
						if (isset($stale_char['active_spec_role']) && is_string($stale_char['active_spec_role']) && trim((string) $stale_char['active_spec_role']) !== '') {
							$members[$i]['active_spec_role'] = (string) $stale_char['active_spec_role'];
						}
						if (isset($stale_char['faction']) && is_string($stale_char['faction']) && trim((string) $stale_char['faction']) !== '') {
							$members[$i]['faction'] = (string) $stale_char['faction'];
						}
					}
				}
				continue;
			}

			$score = self::extract_character_mplus_score($char);
			if ($score !== null) {
				$members[$i]['mplus_score'] = $score;
			}

			$avatar = self::extract_character_avatar_url($char);
			if ($avatar !== '') {
				$members[$i]['avatar_url'] = $avatar;
			}

			$best_runs = self::extract_character_best_runs($char);
			if (count($best_runs) > 0) {
				$members[$i]['best_runs'] = $best_runs;
			}

			$meta = self::extract_character_meta($char);
			foreach ($meta as $k => $v) {
				$members[$i][$k] = $v;
			}

			$cache_value = array(
				'mplus_score' => $score,
				'avatar_url'  => $avatar,
				'best_runs' => $best_runs,
				'class' => isset($meta['class']) ? (string) $meta['class'] : '',
				'active_spec_name' => isset($meta['active_spec_name']) ? (string) $meta['active_spec_name'] : '',
				'active_spec_role' => isset($meta['active_spec_role']) ? (string) $meta['active_spec_role'] : '',
				'faction' => isset($meta['faction']) ? (string) $meta['faction'] : '',
			);
			$cache->set_fresh($char_key, $cache_value, $ttl);
			$cache->set_stale($char_key, $cache_value);
		}

		$data['members'] = $members;
		return $data;
	}

	/**
	 * Convert a realm into a Raider.IO realm slug while preserving accents (WordPress sanitize_title strips them).
	 */
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

		// Apostrophes and spaces -> hyphens.
		$realm = str_replace(array('’', '\'', ' '), array('-', '-', '-'), $realm);
		// Replace everything else (except letters/digits/hyphens) by a hyphen.
		$realm = preg_replace('/[^\p{L}\p{N}-]+/u', '-', $realm);
		// Normalize hyphens.
		$realm = preg_replace('/-+/', '-', (string) $realm);
		$realm = trim((string) $realm, '-');

		return $realm;
	}

	/**
	 * @param array<string, mixed> $char
	 */
	private static function extract_character_mplus_score(array $char): ?float {
		if (!isset($char['mythic_plus_scores_by_season']) || !is_array($char['mythic_plus_scores_by_season'])) {
			return null;
		}

		$seasons = $char['mythic_plus_scores_by_season'];
		$first = isset($seasons[0]) && is_array($seasons[0]) ? $seasons[0] : null;
		if (!$first || !isset($first['scores']) || !is_array($first['scores'])) {
			return null;
		}

		$scores = $first['scores'];
		if (isset($scores['all']) && is_numeric($scores['all'])) {
			return (float) $scores['all'];
		}

		return null;
	}

	/**
	 * @param array<string, mixed> $char
	 */
	private static function extract_character_avatar_url(array $char): string {
		if (isset($char['thumbnail_url']) && is_string($char['thumbnail_url'])) {
			return trim($char['thumbnail_url']);
		}
		if (isset($char['avatar_url']) && is_string($char['avatar_url'])) {
			return trim($char['avatar_url']);
		}
		if (isset($char['portrait_url']) && is_string($char['portrait_url'])) {
			return trim($char['portrait_url']);
		}
		return '';
	}

	/**
	 * @param array<string, mixed> $char
	 * @return array<int, array<string, mixed>>
	 */
	private static function extract_character_best_runs(array $char): array {
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
	private static function extract_character_meta(array $char): array {
		$out = array();

		if (isset($char['class']) && is_string($char['class']) && trim((string) $char['class']) !== '') {
			$out['class'] = trim((string) $char['class']);
		}
		if (isset($char['active_spec_name']) && is_string($char['active_spec_name']) && trim((string) $char['active_spec_name']) !== '') {
			$out['active_spec_name'] = trim((string) $char['active_spec_name']);
		}
		if (isset($char['active_spec_role']) && is_string($char['active_spec_role']) && trim((string) $char['active_spec_role']) !== '') {
			$out['active_spec_role'] = strtolower(trim((string) $char['active_spec_role']));
		}
		if (isset($char['faction']) && is_string($char['faction']) && trim((string) $char['faction']) !== '') {
			$out['faction'] = trim((string) $char['faction']);
		}

		return $out;
	}

	private static function enqueue_assets(): void {
		$css_ver = defined('GMPR_VERSION') ? GMPR_VERSION : '0';
		$js_ver = defined('GMPR_VERSION') ? GMPR_VERSION : '0';

		if (defined('GMPR_PLUGIN_DIR')) {
			$css_path = GMPR_PLUGIN_DIR . 'assets/gmpr.css';
			$js_path = GMPR_PLUGIN_DIR . 'assets/gmpr.js';
			if (file_exists($css_path)) {
				$css_ver = $css_ver . '-' . (string) filemtime($css_path);
			}
			if (file_exists($js_path)) {
				$js_ver = $js_ver . '-' . (string) filemtime($js_path);
			}
		}

		wp_enqueue_style(
			'gmpr-guild',
			GMPR_PLUGIN_URL . 'assets/gmpr.css',
			array(),
			$css_ver
		);

		wp_enqueue_script(
			'gmpr-guild',
			GMPR_PLUGIN_URL . 'assets/gmpr.js',
			array(),
			$js_ver,
			true
		);

		wp_localize_script(
			'gmpr-guild',
			'gmprData',
			array(
				// Provide full endpoint URLs so JS works with both pretty permalinks (/wp-json/...)
				// and plain permalinks (index.php?rest_route=...).
				'rosterUrl' => esc_url_raw(rest_url('gmpr/v1/roster')),
				'refreshUrl' => esc_url_raw(rest_url('gmpr/v1/refresh')),
				// Backward compatibility: keep restBase for older JS versions.
				'restBase' => esc_url_raw(rest_url('gmpr/v1')),
				'pollIntervalMs' => 2000,
				'pollMaxMs' => 30000,
				// Translatable strings for JavaScript
				'i18n' => array(
					/* translators: %d is the total number of characters */
					'showingAll' => __('Showing all %d characters', 'gmpr'),
					/* translators: %1$d is the visible number, %2$d is the total number */
					'showingFiltered' => __('Showing %1$d of %2$d characters', 'gmpr'),
				),
			)
		);
	}

	/**
	 * Resolution order: shortcode attribute > admin settings > constant.
	 *
	 * @param array<string, mixed> $settings
	 */
	private static function resolve_setting_string(string $from_atts, array $settings, string $settings_key, string $constant_name): string {
		$from_atts = trim($from_atts);
		if ($from_atts !== '') {
			return $from_atts;
		}

		if (isset($settings[$settings_key]) && is_string($settings[$settings_key])) {
			$from_settings = trim((string) $settings[$settings_key]);
			if ($from_settings !== '') {
				return $from_settings;
			}
		}

		if (defined($constant_name) && is_string(constant($constant_name))) {
			return (string) constant($constant_name);
		}

		return '';
	}

	private static function normalize_guild_key(string $guild): string {
		$guild = trim($guild);
		if ($guild === '') {
			return '';
		}

		if (function_exists('mb_strtolower')) {
			$guild = mb_strtolower($guild, 'UTF-8');
		} else {
			$guild = strtolower($guild);
		}

		return $guild;
	}

	private static function parse_ttl_seconds(string $ttl_raw, int $default): int {
		$ttl_raw = trim($ttl_raw);
		if ($ttl_raw === '') {
			return $default;
		}

		$ttl = (int) $ttl_raw;
		if ($ttl < 60) {
			return 60;
		}
		if ($ttl > 6 * HOUR_IN_SECONDS) {
			return 6 * HOUR_IN_SECONDS;
		}

		return $ttl;
	}

	private static function parse_bool(string $raw): bool {
		$raw = strtolower(trim($raw));
		return in_array($raw, array('1', 'true', 'yes', 'y', 'on'), true);
	}

	/**
	 * @param array<string, mixed> $data
	 * @return array<string, mixed>
	 */
	/**
	 * @param array<string, mixed> $data
	 * @param array<string, mixed>|null $settings
	 * @return array<string, mixed>
	 */
	private static function apply_member_limit(array $data, ?array $settings = null): array {
		$default = isset($settings['member_limit']) ? (int) $settings['member_limit'] : self::DEFAULT_MEMBER_LIMIT;
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
}


