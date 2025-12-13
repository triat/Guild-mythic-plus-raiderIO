<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

final class GMPR_Plugin {
	private const DEFAULT_MEMBER_LIMIT = 20;

	public static function init(): void {
		add_shortcode('gmpr_guild', array(__CLASS__, 'shortcode_guild'));
		GMPR_Settings::init();
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

		$cached = $can_refresh ? null : $cache->get_fresh($cache_key);
		if (is_array($cached)) {
			$cached = self::apply_member_limit($cached, $settings);
			self::enqueue_assets();
			return GMPR_Renderer::render_guild_table($cached, false);
		}

		$client = new GMPR_RaiderIO_Client($api_key);
		$result = $client->fetch_guild_roster($region, $realm_slug, $guild_name);

		if (is_wp_error($result)) {
			$stale = $cache->get_stale($cache_key);
			if (is_array($stale)) {
				self::enqueue_assets();
				return GMPR_Renderer::render_guild_table($stale, true);
			}

			return GMPR_Renderer::render_error(
				__('Raider.IO is currently unavailable. Please try again later.', 'gmpr')
			);
		}

		$normalized = GMPR_RaiderIO_Client::normalize_guild_roster_response($result, $region, $realm_slug);

		// Temporary limit to speed up page loads (also reduces characters/profile calls).
		$normalized = self::apply_member_limit($normalized, $settings);

		// Hydrate per-character Mythic+ scores (character profile endpoint) when needed.
		$normalized = self::hydrate_member_scores($normalized, $client, $cache, $region, $realm_slug, $ttl, $can_refresh);

		$cache->set_fresh($cache_key, $normalized, $ttl);
		$cache->set_stale($cache_key, $normalized);

		self::enqueue_assets();
		return GMPR_Renderer::render_guild_table($normalized, false);
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

			if (isset($m['mplus_score']) && is_numeric($m['mplus_score'])) {
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
			if (is_array($cached_char) && isset($cached_char['mplus_score']) && is_numeric($cached_char['mplus_score'])) {
				$members[$i]['mplus_score'] = (float) $cached_char['mplus_score'];
				continue;
			}

			$char = $client->fetch_character_profile($region, $realm_slug, $name);
			if (is_wp_error($char)) {
				$stale_char = $cache->get_stale($char_key);
				if (is_array($stale_char) && isset($stale_char['mplus_score']) && is_numeric($stale_char['mplus_score'])) {
					$members[$i]['mplus_score'] = (float) $stale_char['mplus_score'];
				}
				continue;
			}

			$score = self::extract_character_mplus_score($char);
			if ($score !== null) {
				$members[$i]['mplus_score'] = $score;
			}

			$cache->set_fresh($char_key, array('mplus_score' => $score), $ttl);
			$cache->set_stale($char_key, array('mplus_score' => $score));
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

	private static function enqueue_assets(): void {
		wp_enqueue_style(
			'gmpr-guild',
			GMPR_PLUGIN_URL . 'assets/gmpr.css',
			array(),
			GMPR_VERSION
		);

		wp_enqueue_script(
			'gmpr-guild',
			GMPR_PLUGIN_URL . 'assets/gmpr.js',
			array(),
			GMPR_VERSION,
			true
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


