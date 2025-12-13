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
	 */
	public static function render_guild_table(array $data, bool $is_stale): string {
		$members = isset($data['members']) && is_array($data['members']) ? $data['members'] : array();
		$placeholder = defined('GMPR_PLUGIN_URL') ? GMPR_PLUGIN_URL . 'assets/avatar-placeholder.svg' : '';

		$out = '<div class="gmpr gmpr-wrap" data-gmpr-roster="1" data-gmpr-view="inline">';

		if ($is_stale) {
			$out .= '<div class="gmpr gmpr-notice">' . esc_html__('Cached data (may be out of date).', 'gmpr') . '</div>';
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

			$out .= '<li class="gmpr-inline-row">';
			$out .= '<img class="gmpr-avatar gmpr-avatar--sm" data-gmpr-avatar="1" data-gmpr-placeholder-src="' . esc_attr($placeholder) . '" src="' . esc_url($img_src) . '" alt="' . esc_attr(sprintf(__('Avatar of %s', 'gmpr'), $name)) . '" loading="lazy" decoding="async" />';
			$out .= '<span class="gmpr-inline-name">' . esc_html($name) . '</span>';
			$out .= '<span class="gmpr-inline-score" aria-label="' . esc_attr__('Mythic+ score', 'gmpr') . '">' . esc_html($score) . '</span>';
			if ($url !== '') {
				$out .= '<span class="gmpr-inline-link"><a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html__('Profile', 'gmpr') . '</a></span>';
			} else {
				$out .= '<span class="gmpr-inline-link">—</span>';
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
}


