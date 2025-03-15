<?php
/*
Plugin Name: Teck Global Permissions Checker
Plugin URI: https://teck-global.com/wordpress-plugins
Description: A WordPress plugin to check file and folder permissions in the WordPress root directory and report issues.
Version: 1.0.0
Author: Teck Global
Author URI: https://teck-global.com
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain: teck-global-permissions
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TGPCHECKER_VERSION', '1.0.0');
define('TGPCHECKER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TGPCHECKER_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include functions file
require_once TGPCHECKER_PLUGIN_DIR . 'includes/functions.php';

// Enqueue styles and scripts
add_action('admin_enqueue_scripts', 'tgpchecker_enqueue_assets');
function tgpchecker_enqueue_assets($hook) {
    if ($hook !== 'toplevel_page_teck-global-permissions-checker') {
        return;
    }
    wp_enqueue_style('tgpchecker-style', TGPCHECKER_PLUGIN_URL . 'assets/css/style.css', [], TGPCHECKER_VERSION);
    wp_enqueue_script('tgpchecker-script', TGPCHECKER_PLUGIN_URL . 'assets/js/script.js', ['jquery'], TGPCHECKER_VERSION, true);
}

// Add admin menu
add_action('admin_menu', 'tgpchecker_admin_menu');
function tgpchecker_admin_menu() {
    add_menu_page(
        'Teck Global Permissions Checker',
        'Permissions Checker',
        'manage_options',
        'teck-global-permissions-checker',
        'tgpchecker_report_page',
        'dashicons-lock',
        81
    );
}
