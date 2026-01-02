<?php
/**
 * Plugin Name: Guild Mythic+ Raider.IO
 * Description: Affiche les membres d’une guilde World of Warcraft avec leur score Raider.IO (Mythic+).
 * Version: 0.1.5
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author: Guild Mythic+ Raider.IO Contributors
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: gmpr
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

define('GMPR_VERSION', '0.1.5');
define('GMPR_PLUGIN_FILE', __FILE__);
define('GMPR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GMPR_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once GMPR_PLUGIN_DIR . 'includes/class-gmpr-plugin.php';
require_once GMPR_PLUGIN_DIR . 'includes/class-gmpr-raiderio-client.php';
require_once GMPR_PLUGIN_DIR . 'includes/class-gmpr-cache.php';
require_once GMPR_PLUGIN_DIR . 'includes/class-gmpr-renderer.php';
require_once GMPR_PLUGIN_DIR . 'includes/class-gmpr-settings.php';
require_once GMPR_PLUGIN_DIR . 'includes/class-gmpr-async-refresh.php';

add_action('plugins_loaded', array('GMPR_Plugin', 'init'));


