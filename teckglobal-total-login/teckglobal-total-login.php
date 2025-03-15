<?php
/**
 * Plugin Name: TeckGlobal Total Login
 * Author: TeckGlobal LLC
 * Author URI: https://teck-global.com/
 * Plugin URI: https://teck-global.com/wordpress-plugins
 * Description: Customizes the WordPress login page with TeckGlobal branding and provides admin options for customization. If you enjoy this free product please donate at https://teck-global.com/buy-me-a-coffee/
 * Version: 1.0.0
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: teckglobal-total-login
 * Requires at least: 5.0
 * Tested up to: 6.7
 * Requires PHP: 7.4 or later
 * WordPress Available: yes
 * Requires License: no
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue custom styles for the login page
function teck_global_custom_login_styles() {
    $logo = get_option('teck_global_logo', plugin_dir_url(__FILE__) . 'assets/default-logo.webp');
    $background = get_option('teck_global_background', plugin_dir_url(__FILE__) . 'assets/default-bg.webp');
    $font = get_option('teck_global_font', 'Arial, sans-serif');
    $color = get_option('teck_global_font_color', '#000000');
    echo '<style>
        body.login { background: url(' . esc_url($background) . ') no-repeat center center fixed; background-size: cover; }
        .login h1 a { background-image: url(' . esc_url($logo) . ') !important; background-size: contain !important; width: 100%; height: 84px; }
        .login form { font-family: ' . esc_attr($font) . '; color: ' . esc_attr($color) . '; }
    </style>';
}
add_action('login_head', 'teck_global_custom_login_styles');

// Admin menu for customization options
function teck_global_admin_menu() {
    add_menu_page('TeckGlobal Login Settings', 'Login Customizer', 'manage_options', 'teck-global-login', 'teck_global_settings_page', 'dashicons-admin-customizer', 110);
}
add_action('admin_menu', 'teck_global_admin_menu');

// Settings page content
function teck_global_settings_page() {
    ?>
    <div class="wrap">
        <h1>TeckGlobal Total Login</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('teck_global_settings_group');
            do_settings_sections('teck-global-login');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings
function teck_global_register_settings() {
    register_setting('teck_global_settings_group', 'teck_global_logo');
    register_setting('teck_global_settings_group', 'teck_global_background');
    register_setting('teck_global_settings_group', 'teck_global_font');
    register_setting('teck_global_settings_group', 'teck_global_font_color');

    add_settings_section('teck_global_main_section', 'Customization Options', null, 'teck-global-login');

    add_settings_field('teck_global_logo', 'Logo URL', 'teck_global_logo_callback', 'teck-global-login', 'teck_global_main_section');
    add_settings_field('teck_global_background', 'Background Image URL', 'teck_global_background_callback', 'teck-global-login', 'teck_global_main_section');
    add_settings_field('teck_global_font', 'Font Type', 'teck_global_font_callback', 'teck-global-login', 'teck_global_main_section');
    add_settings_field('teck_global_font_color', 'Font Color', 'teck_global_font_color_callback', 'teck-global-login', 'teck_global_main_section');
}
add_action('admin_init', 'teck_global_register_settings');

// Callbacks for settings fields
function teck_global_logo_callback() {
    $logo = get_option('teck_global_logo');
    echo '<input type="text" name="teck_global_logo" value="' . esc_attr($logo) . '" size="50" />';
}

function teck_global_background_callback() {
    $background = get_option('teck_global_background');
    echo '<input type="text" name="teck_global_background" value="' . esc_attr($background) . '" size="50" />';
}

function teck_global_font_callback() {
    $font = get_option('teck_global_font', 'Arial, sans-serif');
    echo '<input type="text" name="teck_global_font" value="' . esc_attr($font) . '" size="50" />';
}

function teck_global_font_color_callback() {
    $color = get_option('teck_global_font_color', '#000000');
    echo '<input type="color" name="teck_global_font_color" value="' . esc_attr($color) . '" />';
}
?>
