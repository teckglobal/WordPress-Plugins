<?php
/*
 * Plugin Name: TeckGlobal Brute Force Protect
 * Author: TeckGlobal LLC
 * Author URI: https://teck-global.com/
 * Plugin URI: https://teck-global.com/wordpress-plugins/
 * Description: A WordPress plugin by TeckGlobal LLC to prevent brute force login attacks with IP management and geolocation features. If you enjoy this free product please donate at https://teck-global.com/buy-me-a-coffee/
 * Version: 1.0.0
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: teckglobal-brute-force-protect
 * Requires at least: 5.0
 * Tested up to: 6.7
 * Requires PHP: 7.4 or later
 * WordPress Available: yes
 * Requires License: no
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('TECKGLOBAL_BFP_PATH', plugin_dir_path(__FILE__));
define('TECKGLOBAL_BFP_URL', plugin_dir_url(__FILE__));

// Include functions file
require_once TECKGLOBAL_BFP_PATH . 'includes/functions.php';

// Debug logging function
function teckglobal_bfp_debug(string $message): void {
    if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
        $log_file = WP_CONTENT_DIR . '/teckglobal-bfp-debug.log';
        $timestamp = current_time('Y-m-d H:i:s');
        file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
    }
}

// Get client IP address
function teckglobal_bfp_get_client_ip(): string {
    $ip = '0.0.0.0';
    $headers = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR',
    ];

    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ip_list = explode(',', $_SERVER[$header]);
            $ip = trim($ip_list[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                teckglobal_bfp_debug("Client IP detected from $header: $ip");
                break;
            }
        }
    }

    if ($ip === '0.0.0.0') {
        teckglobal_bfp_debug("Failed to detect valid client IP. Using fallback: $ip");
    }
    return $ip;
}

// Hook into failed login attempts
function teckglobal_bfp_login_failed($username) {
    $ip = teckglobal_bfp_get_client_ip();
    teckglobal_bfp_log_attempt($ip);

    $max_attempts = (int) get_option('teckglobal_bfp_max_attempts', 5);
    $attempts = teckglobal_bfp_get_attempts($ip);

    if ($attempts >= $max_attempts) {
        teckglobal_bfp_ban_ip($ip);
        teckglobal_bfp_debug("IP $ip exceeded $max_attempts attempts. Banned.");
    }
}
add_action('wp_login_failed', 'teckglobal_bfp_login_failed');

// Check for invalid username attempts (only ban invalid usernames)
function teckglobal_bfp_check_invalid_username($username, $password) {
    $ip = teckglobal_bfp_get_client_ip();
    $auto_ban_invalid = get_option('teckglobal_bfp_auto_ban_invalid', 0);

    // Only log and ban if username is invalid and setting is enabled
    if ($auto_ban_invalid && !username_exists($username) && !email_exists($username)) {
        teckglobal_bfp_log_attempt($ip);
        teckglobal_bfp_ban_ip($ip);
        teckglobal_bfp_debug("IP $ip attempted invalid username '$username'. Auto-banned.");
    }
}
add_action('wp_authenticate', 'teckglobal_bfp_check_invalid_username', 10, 2);

// Block banned IPs (skip during login process)
function teckglobal_bfp_block_banned_ips() {
    // Skip if on wp-login.php to avoid interfering with login
    if (strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false) {
        return;
    }
    $ip = teckglobal_bfp_get_client_ip();
    if (teckglobal_bfp_is_ip_banned($ip)) {
        wp_die(
            'Your IP has been banned due to suspicious activity. Please contact the site administrator.',
            'Access Denied',
            ['response' => 403]
        );
    }
}
add_action('init', 'teckglobal_bfp_block_banned_ips');

// Register admin menu
function teckglobal_bfp_admin_menu() {
    add_menu_page(
        'TeckGlobal BFP',
        'Brute Force Protect',
        'manage_options',
        'teckglobal-bfp',
        'teckglobal_bfp_settings_page',
        'dashicons-shield',
        80
    );
    add_submenu_page(
        'teckglobal-bfp',
        'Settings',
        'Settings',
        'manage_options',
        'teckglobal-bfp',
        'teckglobal_bfp_settings_page'
    );
    add_submenu_page(
        'teckglobal-bfp',
        'Manage IPs',
        'Manage IPs',
        'manage_options',
        'teckglobal-bfp-manage-ips',
        'teckglobal_bfp_manage_ips_page'
    );
    add_submenu_page(
        'teckglobal-bfp',
        'IP Logs',
        'IP Logs',
        'manage_options',
        'teckglobal-bfp-ip-logs',
        'teckglobal_bfp_ip_logs_page'
    );
    add_submenu_page(
        'teckglobal-bfp',
        'Geolocation Map',
        'Geolocation Map',
        'manage_options',
        'teckglobal-bfp-geo-map',
        'teckglobal_bfp_geo_map_page'
    );
}
add_action('admin_menu', 'teckglobal_bfp_admin_menu');

// Enqueue admin assets
function teckglobal_bfp_enqueue_admin_assets($hook) {
    if (strpos($hook, 'teckglobal-bfp') !== false) {
        wp_enqueue_style('teckglobal-bfp-style', TECKGLOBAL_BFP_URL . 'assets/css/style.css', [], '1.0.0');
        wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [], '1.9.4');
        wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], '1.9.4', true);
        wp_enqueue_script('teckglobal-bfp-script', TECKGLOBAL_BFP_URL . 'assets/js/script.js', ['leaflet-js'], '1.0.0', true);
    }
}
add_action('admin_enqueue_scripts', 'teckglobal_bfp_enqueue_admin_assets');

// Database setup on activation
function teckglobal_bfp_activate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'teckglobal_bfp_logs';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        ip VARCHAR(45) NOT NULL,
        timestamp DATETIME NOT NULL,
        attempts INT NOT NULL DEFAULT 1,
        banned TINYINT(1) NOT NULL DEFAULT 0,
        ban_expiry DATETIME DEFAULT NULL,
        country VARCHAR(100) DEFAULT 'Unknown',
        latitude DECIMAL(10,7) DEFAULT NULL,
        longitude DECIMAL(10,7) DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY ip (ip)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    // Set default options
    add_option('teckglobal_bfp_geo_path', '/usr/share/GeoIP/GeoLite2-City.mmdb');
    add_option('teckglobal_bfp_max_attempts', 5);
    add_option('teckglobal_bfp_ban_time', 60);
    add_option('teckglobal_bfp_auto_ban_invalid', 0);
}
register_activation_hook(__FILE__, 'teckglobal_bfp_activate');

// Clean up on deactivation
function teckglobal_bfp_deactivate() {
    // Optionally remove options or table here if desired
}
register_deactivation_hook(__FILE__, 'teckglobal_bfp_deactivate');
