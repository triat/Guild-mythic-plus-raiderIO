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

	/**
	 * Render the expand/collapse chevron icon.
	 */
	private static function render_expand_icon(): string {
		return '<div class="gmpr-expand-icon">'
			. '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">'
			. '<polyline points="6 9 12 15 18 9"></polyline>'
			. '</svg>'
			. '</div>';
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

		$attrs = 'data-gmpr-roster="1"';
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

		if (count($members) === 0) {
			$out .= '<div class="gmpr-empty">' . esc_html__('No members found.', 'gmpr') . '</div>';
		} else {
			// Filter and sort toolbar
			$out .= '<div class="gmpr-filters">';

			$out .= '<div class="gmpr-filter-group">';
			$out .= '<label for="gmpr-filter-role">' . esc_html__('Role:', 'gmpr') . '</label>';
			$out .= '<select id="gmpr-filter-role" class="gmpr-filter-control">';
			$out .= '<option value="all">' . esc_html__('All Roles', 'gmpr') . '</option>';
			$out .= '<option value="tank">' . esc_html__('Tank', 'gmpr') . '</option>';
			$out .= '<option value="healing">' . esc_html__('Healer', 'gmpr') . '</option>';
			$out .= '<option value="dps">' . esc_html__('DPS', 'gmpr') . '</option>';
			$out .= '</select>';
			$out .= '</div>';

			$out .= '<div class="gmpr-filter-group">';
			$out .= '<label for="gmpr-filter-name">' . esc_html__('Name:', 'gmpr') . '</label>';
			$out .= '<input type="text" id="gmpr-filter-name" class="gmpr-filter-control" placeholder="' . esc_attr__('Search...', 'gmpr') . '" />';
			$out .= '</div>';

			$out .= '<div class="gmpr-filter-group">';
			$out .= '<label for="gmpr-filter-score-min">' . esc_html__('Min Score:', 'gmpr') . '</label>';
			$out .= '<input type="number" id="gmpr-filter-score-min" class="gmpr-filter-control" placeholder="0" min="0" step="100" />';
			$out .= '</div>';

			$out .= '<div class="gmpr-filter-group">';
			$out .= '<label for="gmpr-filter-score-max">' . esc_html__('Max Score:', 'gmpr') . '</label>';
			$out .= '<input type="number" id="gmpr-filter-score-max" class="gmpr-filter-control" placeholder="9999" min="0" step="100" />';
			$out .= '</div>';

			$out .= '<div class="gmpr-filter-group">';
			$out .= '<label for="gmpr-sort-by">' . esc_html__('Sort:', 'gmpr') . '</label>';
			$out .= '<select id="gmpr-sort-by" class="gmpr-filter-control">';
			$out .= '<option value="none">' . esc_html__('Default', 'gmpr') . '</option>';
			$out .= '<option value="name-asc">' . esc_html__('Name (A-Z)', 'gmpr') . '</option>';
			$out .= '<option value="name-desc">' . esc_html__('Name (Z-A)', 'gmpr') . '</option>';
			$out .= '<option value="score-desc">' . esc_html__('Score (High to Low)', 'gmpr') . '</option>';
			$out .= '<option value="score-asc">' . esc_html__('Score (Low to High)', 'gmpr') . '</option>';
			$out .= '</select>';
			$out .= '</div>';

			$out .= '<div class="gmpr-filter-group">';
			$out .= '<label>&nbsp;</label>';
			$out .= '<button id="gmpr-clear-filters" class="gmpr-clear-btn" type="button">' . esc_html__('Clear', 'gmpr') . '</button>';
			$out .= '</div>';

			$out .= '<div class="gmpr-results-count" id="gmpr-results-count"></div>';

			$out .= '</div>'; // end filters

			$out .= '<div class="gmpr-roster-list" aria-label="' . esc_attr__('Guild members', 'gmpr') . '">';
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
			$has_details = count($details_runs) > 0;

			$faction_badge = '';
			if ($faction === 'alliance') {
				$faction_badge = '⚔';
			} elseif ($faction === 'horde') {
				$faction_badge = '☠';
			}

			// Extract data attributes for filtering/sorting
			$role = isset($m['active_spec_role']) && is_string($m['active_spec_role']) ? strtolower(trim((string) $m['active_spec_role'])) : '';
			$name_lower = function_exists('mb_strtolower') ? mb_strtolower($name, 'UTF-8') : strtolower($name);
			$score_numeric = isset($m['mplus_score']) && is_numeric($m['mplus_score']) ? (int) round((float) $m['mplus_score']) : 0;

			$out .= '<div class="gmpr-profile-card"'
				. ' data-role="' . esc_attr($role) . '"'
				. ' data-name="' . esc_attr($name_lower) . '"'
				. ' data-score="' . esc_attr((string) $score_numeric) . '"'
				. '>';

			// Header row - clickable to expand/collapse (when has details)
			$header_attrs = 'class="gmpr-profile-header"';
			if ($has_details) {
				$header_attrs .= ' role="button" tabindex="0" aria-expanded="false" aria-controls="gmpr-details-' . esc_attr(sanitize_title($name)) . '"';
			}
			$out .= '<div ' . $header_attrs . '>';

			$out .= '<div class="gmpr-avatar-wrapper">';
			$out .= '<img class="gmpr-avatar" data-gmpr-avatar="1" data-gmpr-placeholder-src="' . esc_attr($placeholder) . '" src="' . esc_url($img_src) . '" alt="' . esc_attr(sprintf(__('Avatar of %s', 'gmpr'), $name)) . '" loading="lazy" decoding="async" />';
			if ($faction_badge !== '') {
				$out .= '<span class="gmpr-faction-badge" aria-hidden="true">' . esc_html($faction_badge) . '</span>';
			}
			$out .= '</div>';

			$out .= '<div class="gmpr-character-info">';
			$out .= '<div class="gmpr-character-name">' . esc_html($name) . '</div>';
			$out .= '<div class="gmpr-character-meta">';
			if ($meta_text !== '') {
				$out .= '<span class="gmpr-class-spec">' . esc_html($meta_text) . '</span>';
			}
			if ($realm !== '') {
				$out .= '<span class="gmpr-realm-info">' . esc_html($realm) . '</span>';
			}
			$out .= '</div>';
			$out .= '</div>';

			if (count($pills) > 0) {
				$out .= '<div class="gmpr-dungeon-pills" aria-label="' . esc_attr__('Best Mythic+ runs', 'gmpr') . '">';
				foreach ($pills as $run) {
					if (!is_array($run)) {
						continue;
					}
					$sn = isset($run['short_name']) && is_string($run['short_name']) ? (string) $run['short_name'] : '';
					$ml = isset($run['mythic_level']) && is_numeric($run['mythic_level']) ? (int) $run['mythic_level'] : 0;
					if ($sn === '' || $ml <= 0) {
						continue;
					}
					$out .= '<div class="gmpr-dungeon-pill">';
					$out .= '<div class="gmpr-dungeon-pill-key">+' . esc_html((string) $ml) . '</div>';
					$out .= '<div class="gmpr-dungeon-pill-name">' . esc_html($sn) . '</div>';
					$out .= '</div>';
				}
				$out .= '</div>';
			}

			$out .= '<div class="gmpr-score-section" aria-label="' . esc_attr__('Mythic+ score', 'gmpr') . '">';
			$out .= '<div class="gmpr-score-label">' . esc_html__('M+ Score', 'gmpr') . '</div>';
			$out .= '<div class="gmpr-score-value">' . esc_html($score) . '</div>';
			$out .= '</div>';

			// Expand icon (only if has details)
			if ($has_details) {
				$out .= self::render_expand_icon();
			}

			$out .= '</div>'; // end header

			// Expandable content
			if ($has_details) {
				$out .= '<div class="gmpr-expandable-content" id="gmpr-details-' . esc_attr(sanitize_title($name)) . '">';
				$out .= '<div class="gmpr-dungeons-section">';
				$out .= '<div class="gmpr-section-title">' . esc_html__('Best Mythic+ Runs', 'gmpr') . '</div>';
				$out .= '<div class="gmpr-dungeons-grid">';
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
					$out .= '<div class="gmpr-dungeon-card">';
					$out .= '<div class="gmpr-dungeon-header"' . $style . '>';
					$out .= '<div class="gmpr-key-level">+' . esc_html((string) $ml) . '</div>';
					$out .= '</div>';
					$out .= '<div class="gmpr-dungeon-body">';
					$out .= '<div class="gmpr-dungeon-name">' . esc_html($dungeon !== '' ? $dungeon : $sn) . '</div>';
					$out .= '<div class="gmpr-dungeon-meta">';
					$out .= '<span class="gmpr-dungeon-shortname">' . esc_html($sn) . '</span>';
					$out .= '<span class="gmpr-dungeon-score">' . esc_html($rs) . '</span>';
					$out .= '</div>';
					$out .= '</div>';
					$out .= '</div>';
				}
				$out .= '</div>';

				// Footer with profile link
				if ($url !== '') {
					$out .= '<div class="gmpr-footer">';
					$out .= '<a class="gmpr-profile-link" href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">';
					$out .= '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">';
					$out .= '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>';
					$out .= '<polyline points="15 3 21 3 21 9"/>';
					$out .= '<line x1="10" y1="14" x2="21" y2="3"/>';
					$out .= '</svg>';
					$out .= esc_html__('View Raider.IO Profile', 'gmpr');
					$out .= '</a>';
					$out .= '</div>';
				}

				$out .= '</div>';
				$out .= '</div>';
			}

			$out .= '</div>'; // end profile-card
		}

		if (count($members) > 0) {
			$out .= '</div>'; // end roster-list
			$out .= '<div class="gmpr-filter-empty" id="gmpr-filter-empty" style="display:none;">';
			$out .= esc_html__('No characters match your filters.', 'gmpr');
			$out .= '</div>';
		}

		$out .= '</div>'; // end wrap
		return $out;
	}

	/**
	 * Render a lightweight, non-blocking loading state for cold starts.
	 *
	 * @param array<string, mixed> $async
	 */
	public static function render_loading(array $async): string {
		$attrs = 'data-gmpr-roster="1" data-gmpr-async="1" data-gmpr-refresh-needed="1"';
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
		$out .= '<div class="gmpr-empty">' . esc_html__('Fetching latest data…', 'gmpr') . '</div>';
		$out .= '</div>';
		return $out;
	}
}
