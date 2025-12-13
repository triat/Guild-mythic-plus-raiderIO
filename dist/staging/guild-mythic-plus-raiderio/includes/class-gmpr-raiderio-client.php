<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

final class GMPR_RaiderIO_Client {
	private const API_BASE = 'https://raider.io/api/v1/';

	private string $api_key;

	public function __construct(string $api_key) {
		$this->api_key = $api_key;
	}

	public static function resolve_api_key(): string {
		$key = '';

		$settings = GMPR_Settings::get_settings();
		if (isset($settings['api_key']) && is_string($settings['api_key'])) {
			$key = (string) $settings['api_key'];
		}

		if (defined('GMPR_RAIDERIO_API_KEY') && is_string(constant('GMPR_RAIDERIO_API_KEY'))) {
			if (trim($key) === '') {
				$key = (string) constant('GMPR_RAIDERIO_API_KEY');
			}
		}

		/**
		 * Filter to inject the API key from a secret manager.
		 *
		 * @param string $key
		 */
		$key = (string) apply_filters('gmpr_raiderio_api_key', $key);

		return trim($key);
	}

	/**
	 * Récupère le profil de guilde avec roster via Raider.IO.
	 *
	 * @return array<string, mixed>|\WP_Error
	 */
	public function fetch_guild_roster(string $region, string $realm_slug, string $guild_name) {
		$endpoint = self::API_BASE . 'guilds/profile';

		// Minimum fields: members. Scores are hydrated separately when needed.
		$query = array(
			'region' => $region,
			'realm'  => $realm_slug,
			'name'   => $guild_name,
			'fields' => 'members',
		);

		$url = add_query_arg($query, $endpoint);
		$safe_url = $url;

		$headers = array(
			'Accept' => 'application/json',
			// Raider.IO API key is expected in a header.
			// IMPORTANT: never log this value.
			'Authorization' => 'Bearer ' . $this->api_key,
		);

		/**
		 * Allows overriding/adding Raider.IO auth headers.
		 *
		 * @param array<string, string> $headers
		 */
		$headers = (array) apply_filters('gmpr_raiderio_auth_headers', $headers);

		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 10,
				'redirection' => 2,
				'headers' => $headers,
			)
		);

		if (is_wp_error($response)) {
			self::debug_log(
				'wp_remote_get error',
				array(
					'url' => $safe_url,
					'region' => $region,
					'realm' => $realm_slug,
					'guild' => $guild_name,
					'wp_error_code' => $response->get_error_code(),
					'wp_error_message' => $response->get_error_message(),
				)
			);
			return $response;
		}

		$code = (int) wp_remote_retrieve_response_code($response);
		$body = (string) wp_remote_retrieve_body($response);

		if ($code < 200 || $code >= 300) {
			self::debug_log(
				'non-2xx response',
				array(
					'url' => $safe_url,
					'status' => $code,
					'body_excerpt' => self::excerpt($body, 500),
				)
			);
			return new WP_Error(
				'gmpr_raiderio_http_error',
				'Raider.IO HTTP error',
				array(
					'status' => $code,
					'body'   => $body,
				)
			);
		}

		$decoded = json_decode($body, true);
		if (!is_array($decoded)) {
			self::debug_log(
				'bad json',
				array(
					'url' => $safe_url,
					'body_excerpt' => self::excerpt($body, 500),
				)
			);
			return new WP_Error('gmpr_raiderio_bad_json', 'Invalid JSON from Raider.IO');
		}

		return $decoded;
	}

	/**
	 * Fetch a character profile (to obtain the Mythic+ score).
	 *
	 * @return array<string, mixed>|\WP_Error
	 */
	public function fetch_character_profile(string $region, string $realm_slug, string $character_name) {
		$endpoint = self::API_BASE . 'characters/profile';

		$query = array(
			'region' => $region,
			'realm'  => $realm_slug,
			'name'   => $character_name,
			'fields' => 'mythic_plus_scores_by_season:current',
		);

		$url = add_query_arg($query, $endpoint);
		$safe_url = $url;

		$headers = array(
			'Accept' => 'application/json',
			'Authorization' => 'Bearer ' . $this->api_key,
		);

		/** @var array<string, string> $headers */
		$headers = (array) apply_filters('gmpr_raiderio_auth_headers', $headers);

		$response = wp_remote_get(
			$url,
			array(
				// Shorter timeout: we may do multiple calls (one per member) on cache miss.
				'timeout' => 4,
				'redirection' => 2,
				'headers' => $headers,
			)
		);

		if (is_wp_error($response)) {
			self::debug_log(
				'character wp_remote_get error',
				array(
					'url' => $safe_url,
					'region' => $region,
					'realm' => $realm_slug,
					'name' => $character_name,
					'wp_error_code' => $response->get_error_code(),
					'wp_error_message' => $response->get_error_message(),
				)
			);
			return $response;
		}

		$code = (int) wp_remote_retrieve_response_code($response);
		$body = (string) wp_remote_retrieve_body($response);

		if ($code < 200 || $code >= 300) {
			self::debug_log(
				'character non-2xx response',
				array(
					'url' => $safe_url,
					'status' => $code,
					'body_excerpt' => self::excerpt($body, 500),
				)
			);
			return new WP_Error(
				'gmpr_raiderio_char_http_error',
				'Raider.IO HTTP error (character)',
				array(
					'status' => $code,
					'body'   => $body,
				)
			);
		}

		$decoded = json_decode($body, true);
		if (!is_array($decoded)) {
			self::debug_log(
				'character bad json',
				array(
					'url' => $safe_url,
					'body_excerpt' => self::excerpt($body, 500),
				)
			);
			return new WP_Error('gmpr_raiderio_char_bad_json', 'Invalid JSON from Raider.IO (character)');
		}

		return $decoded;
	}

	/**
	 * Normalize Raider.IO response into a minimal internal model for rendering.
	 *
	 * @param array<string, mixed> $data
	 * @return array<string, mixed>
	 */
	public static function normalize_guild_roster_response(array $data, string $region, string $realm_slug): array {
		$members = array();

		if (isset($data['members']) && is_array($data['members'])) {
			foreach ($data['members'] as $member) {
				if (!is_array($member)) {
					continue;
				}

				$character = isset($member['character']) && is_array($member['character']) ? $member['character'] : array();
				$name = isset($character['name']) && is_string($character['name']) ? $character['name'] : '';
				if ($name === '') {
					continue;
				}
				$name = self::sanitize_character_name($name);

				$profile_url = isset($character['profile_url']) && is_string($character['profile_url']) ? $character['profile_url'] : '';

				$score = self::extract_mplus_score($character);

				$members[] = array(
					'name'        => $name,
					'realm'       => isset($character['realm']) && is_string($character['realm']) ? $character['realm'] : $realm_slug,
					'mplus_score' => $score, // float|null
					'profile_url' => $profile_url,
				);
			}
		}

		// Simple sort: score desc, then name asc (stable).
		usort(
			$members,
			static function (array $a, array $b): int {
				$sa = isset($a['mplus_score']) && is_numeric($a['mplus_score']) ? (float) $a['mplus_score'] : -1.0;
				$sb = isset($b['mplus_score']) && is_numeric($b['mplus_score']) ? (float) $b['mplus_score'] : -1.0;
				if ($sa === $sb) {
					return strcmp((string) $a['name'], (string) $b['name']);
				}
				return ($sa < $sb) ? 1 : -1;
			}
		);

		$guild = array(
			'region' => $region,
			'realm'  => $realm_slug,
			'name'   => isset($data['name']) && is_string($data['name']) ? $data['name'] : '',
		);

		return array(
			'guild'   => $guild,
			'members' => $members,
			'fetched_at' => time(),
		);
	}

	/**
	 * @param array<string, mixed> $character
	 */
	private static function extract_mplus_score(array $character): ?float {
		// Raider.IO commonly includes mythic_plus_scores_by_season (array) with scores.all.
		if (!isset($character['mythic_plus_scores_by_season']) || !is_array($character['mythic_plus_scores_by_season'])) {
			return null;
		}

		$seasons = $character['mythic_plus_scores_by_season'];
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
	 * Debug-only logging (no secrets).
	 *
	 * @param array<string, mixed> $context
	 */
	private static function debug_log(string $message, array $context = array()): void {
		if (!defined('WP_DEBUG') || WP_DEBUG !== true) {
			return;
		}

		$payload = '';
		if (!empty($context)) {
			$payload = ' ' . wp_json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log('[GMPR] Raider.IO: ' . $message . $payload);
	}

	private static function excerpt(string $s, int $max): string {
		$s = trim($s);
		if ($s === '') {
			return '';
		}
		if (strlen($s) <= $max) {
			return $s;
		}
		return substr($s, 0, $max) . '…';
	}

	public static function sanitize_character_name(string $name): string {
		$name = trim($name);
		if ($name === '') {
			return '';
		}

		// Some rosters include a "-<id>" suffix (e.g. "Cielã-267166348") which is not valid for characters/profile.
		if (preg_match('/^(.+)-\d+$/u', $name, $m)) {
			return (string) $m[1];
		}

		return $name;
	}
}


