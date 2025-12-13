<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

final class GMPR_Renderer {
	/**
	 * @return string
	 */
	private static function initials(string $name): string {
		$name = trim($name);
		if ($name === '') {
			return '';
		}

		// Take up to 2 characters (UTF-8 safe when mbstring is available).
		if (function_exists('mb_substr')) {
			return mb_strtoupper(mb_substr($name, 0, 2, 'UTF-8'), 'UTF-8');
		}

		return strtoupper(substr($name, 0, 2));
	}

	public static function render_error(string $message): string {
		return '<div class="gmpr gmpr-error">' . esc_html($message) . '</div>';
	}

	/**
	 * @param array<string, mixed> $data
	 * @param array<string, mixed>|null $async
	 */
	public static function render_guild_table(array $data, bool $is_stale, ?array $async = null): string {
		$members = isset($data['members']) && is_array($data['members']) ? $data['members'] : array();
		$placeholder = defined('GMPR_PLUGIN_URL') ? GMPR_PLUGIN_URL . 'assets/avatar-placeholder.svg' : '';

		$attrs = 'data-gmpr-roster="1" data-gmpr-view="inline"';
		if (is_array($async) && !empty($async['async'])) {
			$attrs .= ' data-gmpr-async="1"';
			if (isset($async['region'])) {
				$attrs .= ' data-gmpr-region="' . esc_attr((string) $async['region']) . '"';
			}
			if (isset($async['realm'])) {
				$attrs .= ' data-gmpr-realm="' . esc_attr((string) $async['realm']) . '"';
			}
			if (isset($async['guild'])) {
				$attrs .= ' data-gmpr-guild="' . esc_attr((string) $async['guild']) . '"';
			}
			if (isset($async['sig'])) {
				$attrs .= ' data-gmpr-sig="' . esc_attr((string) $async['sig']) . '"';
			}
			if (isset($async['fetched_at'])) {
				$attrs .= ' data-gmpr-fetched-at="' . esc_attr((string) ((int) $async['fetched_at'])) . '"';
			}
			$refresh_needed = !empty($async['refresh_needed']) ? '1' : '0';
			$attrs .= ' data-gmpr-refresh-needed="' . esc_attr($refresh_needed) . '"';
		}

		$out = '<div class="gmpr gmpr-wrap" ' . $attrs . '>';

		if ($is_stale) {
			$out .= '<div class="gmpr gmpr-notice">' . esc_html__('Updating… (showing cached data)', 'gmpr') . '</div>';
		}

		$out .= '<div class="gmpr-view-toggle" role="group" aria-label="' . esc_attr__('Roster view', 'gmpr') . '">';
		$out .= '<button type="button" class="gmpr-toggle-btn" data-gmpr-view-btn="inline" aria-pressed="true">' . esc_html__('Inline', 'gmpr') . '</button>';
		$out .= '<button type="button" class="gmpr-toggle-btn" data-gmpr-view-btn="cards" aria-pressed="false">' . esc_html__('Cards', 'gmpr') . '</button>';
		$out .= '</div>';

		$out .= '<div class="gmpr-view gmpr-view-inline">';
		if (count($members) === 0) {
			$out .= '<div class="gmpr-empty">' . esc_html__('No members found.', 'gmpr') . '</div>';
		} else {
			$out .= '<ul class="gmpr-inline-list" aria-label="' . esc_attr__('Guild members', 'gmpr') . '">';
		}

		foreach ($members as $m) {
			if (!is_array($m)) {
				continue;
			}

			$name = isset($m['name']) ? (string) $m['name'] : '';
			$score = isset($m['mplus_score']) && is_numeric($m['mplus_score']) ? number_format_i18n((float) $m['mplus_score'], 0) : '—';
			$url = isset($m['profile_url']) ? (string) $m['profile_url'] : '';
			$avatar_url = isset($m['avatar_url']) && is_string($m['avatar_url']) ? trim((string) $m['avatar_url']) : '';
			$img_src = $avatar_url !== '' ? $avatar_url : $placeholder;
			$realm = isset($m['realm']) && is_string($m['realm']) ? (string) $m['realm'] : '';
			$class = isset($m['class']) && is_string($m['class']) ? trim((string) $m['class']) : '';
			$spec = isset($m['active_spec_name']) && is_string($m['active_spec_name']) ? trim((string) $m['active_spec_name']) : '';
			$faction = isset($m['faction']) && is_string($m['faction']) ? strtolower(trim((string) $m['faction'])) : '';
			$meta_text = '';
			if ($class !== '' && $spec !== '') {
				$meta_text = $spec . ' ' . $class;
			} elseif ($class !== '') {
				$meta_text = $class;
			}

			$best_runs = (isset($m['best_runs']) && is_array($m['best_runs'])) ? $m['best_runs'] : array();
			$pills = array_slice($best_runs, 0, 4);
			$details_runs = array_slice($best_runs, 0, 8);

			$faction_badge = '';
			if ($faction === 'alliance') {
				$faction_badge = '⚔';
			} elseif ($faction === 'horde') {
				$faction_badge = '☠';
			}

			$out .= '<li class="gmpr-inline-profile">';
			$out .= '<div class="gmpr-inline-profile-header">';
			$out .= '<div class="gmpr-inline-profile-avatar-wrap">';
			$out .= '<img class="gmpr-inline-profile-avatar" data-gmpr-avatar="1" data-gmpr-placeholder-src="' . esc_attr($placeholder) . '" src="' . esc_url($img_src) . '" alt="' . esc_attr(sprintf(__('Avatar of %s', 'gmpr'), $name)) . '" loading="lazy" decoding="async" />';
			if ($faction_badge !== '') {
				$out .= '<span class="gmpr-inline-profile-faction" aria-hidden="true">' . esc_html($faction_badge) . '</span>';
			}
			$out .= '</div>';

			$out .= '<div class="gmpr-inline-profile-info">';
			$out .= '<div class="gmpr-inline-profile-name">' . esc_html($name) . '</div>';
			$out .= '<div class="gmpr-inline-profile-meta">';
			if ($meta_text !== '') {
				$out .= '<span class="gmpr-inline-profile-class">' . esc_html($meta_text) . '</span>';
			}
			if ($realm !== '') {
				$out .= '<span class="gmpr-inline-profile-realm">' . esc_html($realm) . '</span>';
			}
			$out .= '</div>';
			$out .= '</div>';

			if (count($pills) > 0) {
				$out .= '<div class="gmpr-inline-profile-pills" aria-label="' . esc_attr__('Best Mythic+ runs', 'gmpr') . '">';
				foreach ($pills as $run) {
					if (!is_array($run)) {
						continue;
					}
					$sn = isset($run['short_name']) && is_string($run['short_name']) ? (string) $run['short_name'] : '';
					$ml = isset($run['mythic_level']) && is_numeric($run['mythic_level']) ? (int) $run['mythic_level'] : 0;
					if ($sn === '' || $ml <= 0) {
						continue;
					}
					$out .= '<div class="gmpr-inline-profile-pill">';
					$out .= '<div class="gmpr-inline-profile-pill-key">+' . esc_html((string) $ml) . '</div>';
					$out .= '<div class="gmpr-inline-profile-pill-name">' . esc_html($sn) . '</div>';
					$out .= '</div>';
				}
				$out .= '</div>';
			}

			$out .= '<div class="gmpr-inline-profile-score" aria-label="' . esc_attr__('Mythic+ score', 'gmpr') . '">';
			$out .= '<div class="gmpr-inline-profile-score-label">' . esc_html__('M+ Score', 'gmpr') . '</div>';
			$out .= '<div class="gmpr-inline-profile-score-value">' . esc_html($score) . '</div>';
			$out .= '</div>';

			$out .= '<div class="gmpr-inline-profile-cta">';
			if ($url !== '') {
				$out .= '<a class="gmpr-inline-profile-link" href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html__('Profile', 'gmpr') . '</a>';
			} else {
				$out .= '<span class="gmpr-inline-profile-link gmpr-inline-profile-link--disabled">—</span>';
			}
			$out .= '</div>';
			$out .= '</div>';

			if (count($details_runs) > 0) {
				$out .= '<details class="gmpr-inline-profile-details">';
				$out .= '<summary class="gmpr-inline-profile-summary">' . esc_html__('Best Mythic+ Runs', 'gmpr') . '</summary>';
				$out .= '<div class="gmpr-inline-profile-runs">';
				foreach ($details_runs as $run) {
					if (!is_array($run)) {
						continue;
					}
					$dungeon = isset($run['dungeon']) && is_string($run['dungeon']) ? (string) $run['dungeon'] : '';
					$sn = isset($run['short_name']) && is_string($run['short_name']) ? (string) $run['short_name'] : '';
					$ml = isset($run['mythic_level']) && is_numeric($run['mythic_level']) ? (int) $run['mythic_level'] : 0;
					$bg = isset($run['background_image_url']) && is_string($run['background_image_url']) ? trim((string) $run['background_image_url']) : '';
					$rs = isset($run['score']) && is_numeric($run['score']) ? number_format_i18n((float) $run['score'], 1) : '—';
					if ($sn === '' || $ml <= 0) {
						continue;
					}

					$style = $bg !== '' ? ' style="background-image:url(' . esc_url($bg) . ');"' : '';
					$out .= '<div class="gmpr-inline-profile-run">';
					$out .= '<div class="gmpr-inline-profile-run-header"' . $style . '>';
					$out .= '<div class="gmpr-inline-profile-run-level">+' . esc_html((string) $ml) . '</div>';
					$out .= '</div>';
					$out .= '<div class="gmpr-inline-profile-run-body">';
					$out .= '<div class="gmpr-inline-profile-run-name">' . esc_html($dungeon !== '' ? $dungeon : $sn) . '</div>';
					$out .= '<div class="gmpr-inline-profile-run-meta">';
					$out .= '<span class="gmpr-inline-profile-run-short">' . esc_html($sn) . '</span>';
					$out .= '<span class="gmpr-inline-profile-run-score">' . esc_html($rs) . '</span>';
					$out .= '</div>';
					$out .= '</div>';
					$out .= '</div>';
				}
				$out .= '</div>';
				$out .= '</details>';
			}

			$out .= '</li>';
		}

		if (count($members) > 0) {
			$out .= '</ul>';
		}

		$out .= '</div>';

		$out .= '<div class="gmpr-view gmpr-view-cards">';
		if (count($members) === 0) {
			$out .= '<div class="gmpr-empty">' . esc_html__('No members found.', 'gmpr') . '</div>';
		} else {
			$out .= '<div class="gmpr-cards">';
			foreach ($members as $m) {
				if (!is_array($m)) {
					continue;
				}
				$name = isset($m['name']) ? (string) $m['name'] : '';
				$score_raw = isset($m['mplus_score']) && is_numeric($m['mplus_score']) ? (float) $m['mplus_score'] : null;
				$score = $score_raw !== null ? number_format_i18n($score_raw, 0) : '—';
				$url = isset($m['profile_url']) ? (string) $m['profile_url'] : '';
				$avatar_url = isset($m['avatar_url']) && is_string($m['avatar_url']) ? trim((string) $m['avatar_url']) : '';
				$img_src = $avatar_url !== '' ? $avatar_url : $placeholder;

				$out .= '<div class="gmpr-card">';
				$out .= '<div class="gmpr-card-header">';
				$out .= '<div class="gmpr-card-avatar">';
				$out .= '<img class="gmpr-avatar gmpr-avatar--md" data-gmpr-avatar="1" data-gmpr-placeholder-src="' . esc_attr($placeholder) . '" src="' . esc_url($img_src) . '" alt="' . esc_attr(sprintf(__('Avatar of %s', 'gmpr'), $name)) . '" loading="lazy" decoding="async" />';
				$out .= '</div>';
				$out .= '<div class="gmpr-card-name">' . esc_html($name) . '</div>';
				$out .= '<div class="gmpr-card-score" aria-label="' . esc_attr__('Mythic+ score', 'gmpr') . '">' . esc_html($score) . '</div>';
				$out .= '</div>';
				$out .= '<div class="gmpr-card-footer">';
				if ($url !== '') {
					$out .= '<a class="gmpr-card-link" href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html__('Open Raider.IO profile', 'gmpr') . '</a>';
				} else {
					$out .= '<span class="gmpr-card-link gmpr-card-link-disabled">—</span>';
				}
				$out .= '</div>';
				$out .= '</div>';
			}
			$out .= '</div>';
		}
		$out .= '</div>';

		$out .= '</div>';
		return $out;
	}

	/**
	 * Render a lightweight, non-blocking loading state for cold starts.
	 *
	 * @param array<string, mixed> $async
	 */
	public static function render_loading(array $async): string {
		$attrs = 'data-gmpr-roster="1" data-gmpr-view="inline" data-gmpr-async="1" data-gmpr-refresh-needed="1"';
		if (isset($async['region'])) {
			$attrs .= ' data-gmpr-region="' . esc_attr((string) $async['region']) . '"';
		}
		if (isset($async['realm'])) {
			$attrs .= ' data-gmpr-realm="' . esc_attr((string) $async['realm']) . '"';
		}
		if (isset($async['guild'])) {
			$attrs .= ' data-gmpr-guild="' . esc_attr((string) $async['guild']) . '"';
		}
		if (isset($async['sig'])) {
			$attrs .= ' data-gmpr-sig="' . esc_attr((string) $async['sig']) . '"';
		}
		$attrs .= ' data-gmpr-fetched-at="0"';

		$out = '<div class="gmpr gmpr-wrap" ' . $attrs . '>';
		$out .= '<div class="gmpr gmpr-notice">' . esc_html__('Loading roster…', 'gmpr') . '</div>';
		$out .= '<div class="gmpr-view-toggle" role="group" aria-label="' . esc_attr__('Roster view', 'gmpr') . '">';
		$out .= '<button type="button" class="gmpr-toggle-btn" data-gmpr-view-btn="inline" aria-pressed="true">' . esc_html__('Inline', 'gmpr') . '</button>';
		$out .= '<button type="button" class="gmpr-toggle-btn" data-gmpr-view-btn="cards" aria-pressed="false">' . esc_html__('Cards', 'gmpr') . '</button>';
		$out .= '</div>';
		$out .= '<div class="gmpr-empty">' . esc_html__('Fetching latest data…', 'gmpr') . '</div>';
		$out .= '</div>';
		return $out;
	}
}


