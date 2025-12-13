<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

final class GMPR_Renderer {
	public static function render_error(string $message): string {
		return '<div class="gmpr gmpr-error">' . esc_html($message) . '</div>';
	}

	/**
	 * @param array<string, mixed> $data
	 */
	public static function render_guild_table(array $data, bool $is_stale): string {
		$members = isset($data['members']) && is_array($data['members']) ? $data['members'] : array();

		$out = '<div class="gmpr gmpr-wrap">';

		if ($is_stale) {
			$out .= '<div class="gmpr gmpr-notice">' . esc_html__('Données en cache (possiblement périmées).', 'gmpr') . '</div>';
		}

		$out .= '<div class="gmpr-table-wrap">';
		$out .= '<table class="gmpr-table">';
		$out .= '<thead><tr>';
		$out .= '<th scope="col">' . esc_html__('Membre', 'gmpr') . '</th>';
		$out .= '<th scope="col" class="gmpr-col-score">' . esc_html__('Score Mythic+', 'gmpr') . '</th>';
		$out .= '<th scope="col">' . esc_html__('Raider.IO', 'gmpr') . '</th>';
		$out .= '</tr></thead>';
		$out .= '<tbody>';

		foreach ($members as $m) {
			if (!is_array($m)) {
				continue;
			}

			$name = isset($m['name']) ? (string) $m['name'] : '';
			$score = isset($m['mplus_score']) && is_numeric($m['mplus_score']) ? number_format_i18n((float) $m['mplus_score'], 0) : '—';
			$url = isset($m['profile_url']) ? (string) $m['profile_url'] : '';

			$out .= '<tr>';
			$out .= '<td data-label="' . esc_attr__('Membre', 'gmpr') . '">' . esc_html($name) . '</td>';
			$out .= '<td data-label="' . esc_attr__('Score Mythic+', 'gmpr') . '" class="gmpr-col-score">' . esc_html($score) . '</td>';
			if ($url !== '') {
				$out .= '<td data-label="' . esc_attr__('Raider.IO', 'gmpr') . '"><a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html__('Profil', 'gmpr') . '</a></td>';
			} else {
				$out .= '<td data-label="' . esc_attr__('Raider.IO', 'gmpr') . '">—</td>';
			}
			$out .= '</tr>';
		}

		if (count($members) === 0) {
			$out .= '<tr><td colspan="3">' . esc_html__('Aucun membre trouvé.', 'gmpr') . '</td></tr>';
		}

		$out .= '</tbody></table></div></div>';
		return $out;
	}
}


