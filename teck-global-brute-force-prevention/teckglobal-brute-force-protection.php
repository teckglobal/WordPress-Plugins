<?php
/*
Plugin Name: TeckGlobal Brute Force Protect
Plugin URI: https://teck-global.com/wordpress-plugins
Description: A WordPress plugin by TeckGlobal LLC to prevent brute force login attacks with IP management and geolocation features.
Version: 1.0.4
Author: TeckGlobal LLC
Author URI: https://teck-global.com
License: GPL-2.0+
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TECKGLOBAL_BFP_VERSION', '1.0.4');
define('TECKGLOBAL_BFP_PATH', plugin_dir_path(__FILE__));
define('TECKGLOBAL_BFP_URL', plugin_dir_url(__FILE__));
define('TECKGLOBAL_BFP_DEBUG_LOG', WP_CONTENT_DIR . '/teckglobal-bfp-debug.log');

// Include functions file
require_once TECKGLOBAL_BFP_PATH . 'includes/functions.php';

// Debugging function
function teckglobal_bfp_debug(string $message): void {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        $timestamp = current_time('mysql');
        file_put_contents(TECKGLOBAL_BFP_DEBUG_LOG, "[$timestamp] $message\n", FILE_APPEND);
    }
}

// Enqueue styles and scripts
function teckglobal_bfp_enqueue_assets(): void {
    wp_enqueue_style('teckglobal-bfp-style', TECKGLOBAL_BFP_URL . 'assets/css/style.css', [], TECKGLOBAL_BFP_VERSION);
    wp_enqueue_script('teckglobal-bfp-script', TECKGLOBAL_BFP_URL . 'assets/js/script.js', ['jquery'], TECKGLOBAL_BFP_VERSION, true);
    wp_enqueue_script('google-maps', 'https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY', [], null, true); // Replace with your API key
    wp_localize_script('teckglobal-bfp-script', 'teckglobal_bfp', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('teckglobal_bfp_nonce'),
    ]);
}
add_action('admin_enqueue_scripts', 'teckglobal_bfp_enqueue_assets');

// Register admin menu
function teckglobal_bfp_admin_menu(): void {
    add_menu_page(
        'TeckGlobal Brute Force Protect',
        'Brute Force Protect',
        'manage_options',
        'teckglobal-bfp',
        'teckglobal_bfp_settings_page',
        'dashicons-shield',
        80
    );
    add_submenu_page('teckglobal-bfp', 'Manage IPs', 'Manage IPs', 'manage_options', 'teckglobal-bfp-manage-ips', 'teckglobal_bfp_manage_ips_page');
    add_submenu_page('teckglobal-bfp', 'IP Logs', 'IP Logs', 'manage_options', 'teckglobal-bfp-ip-logs', 'teckglobal_bfp_ip_logs_page');
    add_submenu_page('teckglobal-bfp', 'Geolocation Map', 'Geolocation Map', 'manage_options', 'teckglobal-bfp-geo-map', 'teckglobal_bfp_geo_map_page');
}
add_action('admin_menu', 'teckglobal_bfp_admin_menu');

// Activation hook to create database table and set default options
function teckglobal_bfp_activate(): void {
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
        country VARCHAR(100) DEFAULT NULL,
        latitude FLOAT DEFAULT NULL,
        longitude FLOAT DEFAULT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY ip_unique (ip)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    if ($wpdb->last_error) {
        teckglobal_bfp_debug("Database error on activation: " . $wpdb->last_error);
    }

    // Set default options
    update_option('teckglobal_bfp_geo_path', get_option('teckglobal_bfp_geo_path', '/usr/share/GeoIP/GeoLite2-City.mmdb'));
    update_option('teckglobal_bfp_max_attempts', get_option('teckglobal_bfp_max_attempts', 5));
    update_option('teckglobal_bfp_ban_time', get_option('teckglobal_bfp_ban_time', 60));
    update_option('teckglobal_bfp_auto_ban_invalid', get_option('teckglobal_bfp_auto_ban_invalid', 0));
}
register_activation_hook(__FILE__, 'teckglobal_bfp_activate');

// Get client IP (Nginx compatibility)
function teckglobal_bfp_get_client_ip(): string {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ip_list[0]);
    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    $ip = filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    teckglobal_bfp_debug("Detected client IP: $ip");
    return $ip;
}

// Brute force protection with configurable attempts and auto-ban invalid usernames
function teckglobal_bfp_check_login($user, string $username, string $password) {
    if (!$username || !$password) {
        return $user;
    }

    $ip = teckglobal_bfp_get_client_ip();
    $max_attempts = (int) get_option('teckglobal_bfp_max_attempts', 5);
    $auto_ban_invalid = (int) get_option('teckglobal_bfp_auto_ban_invalid', 0);

    teckglobal_bfp_log_attempt($ip);

    if (teckglobal_bfp_is_ip_banned($ip)) {
        teckglobal_bfp_debug("IP $ip is banned, blocking login.");
        wp_die('Your IP has been banned due to excessive login attempts.', 'Access Denied', ['response' => 403]);
    }

    $attempts = teckglobal_bfp_get_attempts($ip);
    teckglobal_bfp_debug("IP $ip has $attempts attempts, max allowed: $max_attempts");
    if ($attempts >= $max_attempts) {
        teckglobal_bfp_ban_ip($ip);
        teckglobal_bfp_debug("IP $ip exceeded max attempts, banned.");
        wp_die('Too many login attempts. Your IP has been banned.', 'Access Denied', ['response' => 403]);
    }

    if ($auto_ban_invalid && !username_exists($username)) {
        teckglobal_bfp_ban_ip($ip);
        teckglobal_bfp_debug("IP $ip attempted invalid username '$username', banned.");
        wp_die('Invalid username detected. Your IP has been banned.', 'Access Denied', ['response' => 403]);
    }

    return $user;
}
add_filter('authenticate', 'teckglobal_bfp_check_login', 20, 3);

// Schedule IP unban check
function teckglobal_bfp_schedule_unban_check(): void {
    if (!wp_next_scheduled('teckglobal_bfp_unban_event')) {
        wp_schedule_event(time(), 'every_minute', 'teckglobal_bfp_unban_event');
    }
}
add_action('wp', 'teckglobal_bfp_schedule_unban_check');

// Unban expired IPs
function teckglobal_bfp_unban_expired_ips(): void {
    global $wpdb;
    $table_name = $wpdb->prefix . 'teckglobal_bfp_logs';
    $result = $wpdb->query("UPDATE $table_name SET banned = 0, ban_expiry = NULL WHERE banned = 1 AND ban_expiry <= NOW()");
    if ($result === false) {
        teckglobal_bfp_debug("Error unbanning IPs: " . $wpdb->last_error);
    } else {
        teckglobal_bfp_debug("Unbanned expired IPs: $result rows affected.");
    }
}
add_action('teckglobal_bfp_unban_event', 'teckglobal_bfp_unban_expired_ips');

// Custom cron interval
function teckglobal_bfp_add_cron_interval(array $schedules): array {
    $schedules['every_minute'] = [
        'interval' => 60,
        'display'  => __('Every Minute'),
    ];
    return $schedules;
}
add_filter('cron_schedules', 'teckglobal_bfp_add_cron_interval');
?>
