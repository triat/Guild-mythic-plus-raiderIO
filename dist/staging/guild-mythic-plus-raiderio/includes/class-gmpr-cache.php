<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

final class GMPR_Cache {
	private const PREFIX = 'gmpr_raiderio_guild_';
	private const CHAR_PREFIX = 'gmpr_raiderio_char_';
	private const FRESH_SUFFIX = '_fresh';
	private const STALE_SUFFIX = '_stale';

	/**
	 * @return string Base key (without suffix)
	 */
	public function build_guild_cache_key(string $region, string $realm_slug, string $guild_key): string {
		$raw = $region . '|' . $realm_slug . '|' . $guild_key;
		return self::PREFIX . md5($raw);
	}

	/**
	 * @return string Base key (without suffix)
	 */
	public function build_character_cache_key(string $region, string $realm_slug, string $character_name): string {
		$raw = $region . '|' . $realm_slug . '|' . $character_name;
		return self::CHAR_PREFIX . md5($raw);
	}

	/**
	 * @return array<string, mixed>|null
	 */
	public function get_fresh(string $base_key): ?array {
		$value = get_transient($base_key . self::FRESH_SUFFIX);
		return is_array($value) ? $value : null;
	}

	/**
	 * @return array<string, mixed>|null
	 */
	public function get_stale(string $base_key): ?array {
		$value = get_transient($base_key . self::STALE_SUFFIX);
		return is_array($value) ? $value : null;
	}

	/**
	 * @param array<string, mixed> $value
	 */
	public function set_fresh(string $base_key, array $value, int $ttl_seconds): void {
		set_transient($base_key . self::FRESH_SUFFIX, $value, $ttl_seconds);
	}

	/**
	 * @param array<string, mixed> $value
	 */
	public function set_stale(string $base_key, array $value): void {
		// Stale conservé plus longtemps pour permettre un fallback en cas de panne.
		set_transient($base_key . self::STALE_SUFFIX, $value, 7 * DAY_IN_SECONDS);
	}
}


