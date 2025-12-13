<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

final class GMPR_Settings {
	public const OPTION_NAME = 'gmpr_settings';
	private const PAGE_SLUG = 'gmpr';
	private const GROUP = 'gmpr_settings_group';
	private const SECTION_MAIN = 'gmpr_main';

	public static function init(): void {
		if (!is_admin()) {
			return;
		}

		add_action('admin_menu', array(__CLASS__, 'add_menu'));
		add_action('admin_init', array(__CLASS__, 'register_settings'));
	}

	public static function add_menu(): void {
		add_options_page(
			'GMPR',
			'GMPR',
			'manage_options',
			self::PAGE_SLUG,
			array(__CLASS__, 'render_page')
		);
	}

	public static function register_settings(): void {
		register_setting(self::GROUP, self::OPTION_NAME, array(__CLASS__, 'sanitize_settings'));

		add_settings_section(
			self::SECTION_MAIN,
			__('GMPR Settings', 'gmpr'),
			static function (): void {
				echo '<p>' . esc_html__('Configure the default guild and caching behavior. Shortcode attributes override these defaults.', 'gmpr') . '</p>';
			},
			self::PAGE_SLUG
		);

		self::add_field('api_key', __('Raider.IO API key', 'gmpr'), array(__CLASS__, 'field_api_key'));
		self::add_field('region', __('Region', 'gmpr'), array(__CLASS__, 'field_region'));
		self::add_field('realm', __('Realm', 'gmpr'), array(__CLASS__, 'field_realm'));
		self::add_field('guild', __('Guild name', 'gmpr'), array(__CLASS__, 'field_guild'));
		self::add_field('ttl_seconds', __('Cache TTL (seconds)', 'gmpr'), array(__CLASS__, 'field_ttl'));
		self::add_field('member_limit', __('Member limit', 'gmpr'), array(__CLASS__, 'field_member_limit'));
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function get_settings(): array {
		$defaults = array(
			'api_key'      => '',
			'region'       => '',
			'realm'        => '',
			'guild'        => '',
			'ttl_seconds'  => 15 * MINUTE_IN_SECONDS,
			'member_limit' => 20,
		);

		$stored = get_option(self::OPTION_NAME);
		if (!is_array($stored)) {
			return $defaults;
		}

		return array_merge($defaults, $stored);
	}

	/**
	 * @param mixed $input
	 * @return array<string, mixed>
	 */
	public static function sanitize_settings($input): array {
		$current = self::get_settings();
		$out = array();

		$input = is_array($input) ? $input : array();

		$api_key = isset($input['api_key']) ? trim((string) $input['api_key']) : '';
		if ($api_key === '') {
			$out['api_key'] = isset($current['api_key']) ? (string) $current['api_key'] : '';
		} else {
			$out['api_key'] = $api_key;
		}

		$region = isset($input['region']) ? sanitize_key((string) $input['region']) : '';
		$allowed = array('eu', 'us', 'kr', 'tw', 'cn');
		$out['region'] = in_array($region, $allowed, true) ? $region : '';

		$out['realm'] = isset($input['realm']) ? sanitize_text_field((string) $input['realm']) : '';
		$out['guild'] = isset($input['guild']) ? sanitize_text_field((string) $input['guild']) : '';

		$ttl = isset($input['ttl_seconds']) ? (int) $input['ttl_seconds'] : (int) $current['ttl_seconds'];
		if ($ttl < 60) {
			$ttl = 60;
		}
		if ($ttl > 6 * HOUR_IN_SECONDS) {
			$ttl = 6 * HOUR_IN_SECONDS;
		}
		$out['ttl_seconds'] = $ttl;

		$limit = isset($input['member_limit']) ? (int) $input['member_limit'] : (int) $current['member_limit'];
		if ($limit < 0) {
			$limit = 0;
		}
		if ($limit > 500) {
			$limit = 500;
		}
		$out['member_limit'] = $limit;

		return $out;
	}

	private static function add_field(string $key, string $label, callable $render): void {
		add_settings_field(
			$key,
			$label,
			$render,
			self::PAGE_SLUG,
			self::SECTION_MAIN,
			array('key' => $key)
		);
	}

	public static function render_page(): void {
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have permission to access this page.', 'gmpr'));
		}

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__('GMPR Settings', 'gmpr') . '</h1>';
		echo '<form method="post" action="options.php">';
		settings_fields(self::GROUP);
		do_settings_sections(self::PAGE_SLUG);
		submit_button();
		echo '</form>';

		echo '<hr />';
		echo '<h2>' . esc_html__('Help', 'gmpr') . '</h2>';
		echo '<p>' . esc_html__('Shortcode attributes override admin defaults.', 'gmpr') . '</p>';
		echo '<pre>[gmpr_guild region="eu" realm="dalaran" guild="Guild Name"]</pre>';
		echo '<p>' . esc_html__('Tip: use refresh="1" (admin only) to bypass cache while troubleshooting.', 'gmpr') . '</p>';
		echo '</div>';
	}

	public static function field_api_key(): void {
		echo '<input type="password" name="' . esc_attr(self::OPTION_NAME) . '[api_key]" value="" class="regular-text" autocomplete="new-password" />';
		echo '<p class="description">' . esc_html__('Leave empty to keep the existing key. This value is never shown in plaintext.', 'gmpr') . '</p>';
	}

	public static function field_region(): void {
		$settings = self::get_settings();
		$current = isset($settings['region']) ? (string) $settings['region'] : '';
		$options = array('' => __('(not set)', 'gmpr'), 'eu' => 'EU', 'us' => 'US', 'kr' => 'KR', 'tw' => 'TW', 'cn' => 'CN');

		echo '<select name="' . esc_attr(self::OPTION_NAME) . '[region]">';
		foreach ($options as $value => $label) {
			echo '<option value="' . esc_attr($value) . '"' . selected($current, $value, false) . '>' . esc_html($label) . '</option>';
		}
		echo '</select>';
	}

	public static function field_realm(): void {
		$settings = self::get_settings();
		$value = isset($settings['realm']) ? (string) $settings['realm'] : '';
		echo '<input type="text" name="' . esc_attr(self::OPTION_NAME) . '[realm]" value="' . esc_attr($value) . '" class="regular-text" />';
		echo '<p class="description">' . esc_html__('Realm slug or name. Accents may matter for Raider.IO.', 'gmpr') . '</p>';
	}

	public static function field_guild(): void {
		$settings = self::get_settings();
		$value = isset($settings['guild']) ? (string) $settings['guild'] : '';
		echo '<input type="text" name="' . esc_attr(self::OPTION_NAME) . '[guild]" value="' . esc_attr($value) . '" class="regular-text" />';
	}

	public static function field_ttl(): void {
		$settings = self::get_settings();
		$value = isset($settings['ttl_seconds']) ? (int) $settings['ttl_seconds'] : 900;
		echo '<input type="number" min="60" max="' . esc_attr((string) (6 * HOUR_IN_SECONDS)) . '" name="' . esc_attr(self::OPTION_NAME) . '[ttl_seconds]" value="' . esc_attr((string) $value) . '" />';
		echo '<p class="description">' . esc_html__('Min 60, max 6 hours.', 'gmpr') . '</p>';
	}

	public static function field_member_limit(): void {
		$settings = self::get_settings();
		$value = isset($settings['member_limit']) ? (int) $settings['member_limit'] : 20;
		echo '<input type="number" min="0" max="500" name="' . esc_attr(self::OPTION_NAME) . '[member_limit]" value="' . esc_attr((string) $value) . '" />';
		echo '<p class="description">' . esc_html__('0 disables the limit.', 'gmpr') . '</p>';
	}
}


