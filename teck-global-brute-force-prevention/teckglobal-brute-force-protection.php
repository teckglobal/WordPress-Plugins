<?php
/*
Plugin Name: Teck Global Brute Force Protection
Plugin URI: https://teck-global.com/wordpress-plugins
Description: A WordPress plugin to prevent brute force login attacks with IP management and geolocation features.
Version: 1.0.2
Author: Teck Global
Author URI: https://teck-global.com
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain: teck-global-brute-force
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TGBFP_VERSION', '1.0.2');
define('TGBFP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TGBFP_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include functions file
require_once TGBFP_PLUGIN_DIR . 'includes/functions.php';

// Activation hook to create database table
register_activation_hook(__FILE__, 'tgbfp_activate');
function tgbfp_activate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tgbfp_login_attempts';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        ip_address VARCHAR(45) NOT NULL,
        attempt_time DATETIME NOT NULL,
        PRIMARY KEY (id),
        INDEX idx_ip (ip_address)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    // Set default options
    if (!get_option('tgbfp_max_attempts')) {
        update_option('tgbfp_max_attempts', 5);
        update_option('tgbfp_lockout_duration', 3600); // 1 hour in seconds
        update_option('tgbfp_geo_db_path', '/var/www/html/teck-global.com/wp-content/plugins/teck-global-brute-force-prevention/vendor/GeoLite2-City.mmdb'); // Default path
    }
}

// Enqueue styles and scripts
add_action('admin_enqueue_scripts', 'tgbfp_enqueue_assets');
function tgbfp_enqueue_assets($hook) {
    if (strpos($hook, 'teck-global-brute-force-protection') === false) {
        return;
    }
    wp_enqueue_style('tgbfp-style', TGBFP_PLUGIN_URL . 'assets/css/style.css', [], TGBFP_VERSION);
    wp_enqueue_script('tgbfp-script', TGBFP_PLUGIN_URL . 'assets/js/script.js', ['jquery'], TGBFP_VERSION, true);

    // Only enqueue Google Maps for the geolocation page
    if ($hook === 'teckglobal-security_page_tgbfp-geolocation') {
        $google_maps_key = 'AIzaSyDpp4Cm9gaFDBnRgPMk-ZFBoBjrHArztaM'; // Replace with your API key
        if (!empty($google_maps_key)) {
            wp_enqueue_script('google-maps', "https://maps.googleapis.com/maps/api/js?key={$google_maps_key}", [], null, true);
        }
    }

    wp_localize_script('tgbfp-script', 'tgbfp_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('tgbfp_nonce')
    ]);
}

// Add admin menu
add_action('admin_menu', 'tgbfp_admin_menu');
function tgbfp_admin_menu() {
    add_menu_page(
        'Teck Global Brute Force Protection',
        'TeckGlobal Security',
        'manage_options',
        'teck-global-brute-force-protection',
        'tgbfp_settings_page',
        'dashicons-shield',
        80
    );
    add_submenu_page(
        'teck-global-brute-force-protection',
        'IP Management',
        'IP Management',
        'manage_options',
        'tgbfp-ip-management',
        'tgbfp_ip_management_page'
    );
    add_submenu_page(
        'teck-global-brute-force-protection',
        'IP Logs',
        'IP Logs',
        'manage_options',
        'tgbfp-ip-logs',
        'tgbfp_ip_logs_page'
    );
    add_submenu_page(
        'teck-global-brute-force-protection',
        'Geolocation Map',
        'Geolocation Map',
        'manage_options',
        'tgbfp-geolocation',
        'tgbfp_geolocation_page'
    );
}

// Brute force protection logic
add_action('wp_login_failed', 'tgbfp_log_failed_login');
function tgbfp_log_failed_login($username) {
    global $wpdb;
    $ip = $_SERVER['REMOTE_ADDR'];
    $table_name = $wpdb->prefix . 'tgbfp_login_attempts';

    // Log the attempt
    $wpdb->insert(
        $table_name,
        [
            'ip_address' => $ip,
            'attempt_time' => current_time('mysql')
        ],
        ['%s', '%s']
    );

    // Check if IP should be blocked
    $max_attempts = get_option('tgbfp_max_attempts', 5);
    $lockout_duration = get_option('tgbfp_lockout_duration', 3600);
    $cutoff_time = date('Y-m-d H:i:s', time() - $lockout_duration);

    $attempts = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE ip_address = %s AND attempt_time > %s",
        $ip,
        $cutoff_time
    ));

    if ($attempts >= $max_attempts) {
        $banned_ips = get_option('tgbfp_banned_ips', []);
        $banned_ips[] = $ip;
        update_option('tgbfp_banned_ips', array_unique($banned_ips));
    }
}

// Block banned IPs
add_action('init', 'tgbfp_block_banned_ips');
function tgbfp_block_banned_ips() {
    $banned_ips = get_option('tgbfp_banned_ips', []);
    $current_ip = $_SERVER['REMOTE_ADDR'];

    if (in_array($current_ip, $banned_ips) && !is_user_logged_in()) {
        wp_die('Your IP has been blocked due to excessive login attempts.', 'Access Denied', ['response' => 403]);
    }
}
?>
