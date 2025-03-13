<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Settings page
function tgbfp_settings_page() {
    ?>
    <div class="wrap">
        <h1>Teck Global Brute Force Protection</h1>
        <p>Welcome to Teck Global's security solution for protecting your WordPress site from brute force attacks.</p>
        <form method="post" action="options.php">
            <?php
            settings_fields('tgbfp_settings_group');
            do_settings_sections('tgbfp_settings_group');
            ?>
            <table class="form-table">
                <tr>
                    <th><label for="tgbfp_max_attempts">Max Login Attempts</label></th>
                    <td><input type="number" id="tgbfp_max_attempts" name="tgbfp_max_attempts" value="<?php echo esc_attr(get_option('tgbfp_max_attempts', 5)); ?>" min="1" /></td>
                </tr>
                <tr>
                    <th><label for="tgbfp_lockout_duration">Lockout Duration (seconds)</label></th>
                    <td><input type="number" id="tgbfp_lockout_duration" name="tgbfp_lockout_duration" value="<?php echo esc_attr(get_option('tgbfp_lockout_duration', 3600)); ?>" min="60" /></td>
                </tr>
                <tr>
                    <th><label for="tgbfp_geo_db_path">GeoLite2 Database Path</label></th>
                    <td>
                        <input type="text" id="tgbfp_geo_db_path" name="tgbfp_geo_db_path" value="<?php echo esc_attr(get_option('tgbfp_geo_db_path', '/usr/share/GeoIP/GeoLite2-City.mmdb')); ?>" style="width: 100%; max-width: 400px;" />
                        <p class="description">Path to the MaxMind GeoLite2-City.mmdb file on your server.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// IP Management page
function tgbfp_ip_management_page() {
    $banned_ips = get_option('tgbfp_banned_ips', []);

    if (isset($_POST['tgbfp_add_ip']) && check_admin_referer('tgbfp_add_ip_action')) {
        $new_ip = sanitize_text_field($_POST['tgbfp_new_ip']);
        if (filter_var($new_ip, FILTER_VALIDATE_IP)) {
            $banned_ips[] = $new_ip;
            update_option('tgbfp_banned_ips', array_unique($banned_ips));
        }
    }

    if (isset($_POST['tgbfp_remove_ip']) && check_admin_referer('tgbfp_remove_ip_action')) {
        $remove_ip = sanitize_text_field($_POST['tgbfp_remove_ip']);
        $banned_ips = array_diff($banned_ips, [$remove_ip]);
        update_option('tgbfp_banned_ips', array_unique($banned_ips));
    }

    ?>
    <div class="wrap">
        <h1>IP Management</h1>
        <form method="post">
            <?php wp_nonce_field('tgbfp_add_ip_action'); ?>
            <input type="text" name="tgbfp_new_ip" placeholder="Enter IP to ban" required />
            <input type="submit" name="tgbfp_add_ip" value="Add IP" class="button button-primary" />
        </form>
        <h2>Banned IPs</h2>
        <ul>
            <?php foreach ($banned_ips as $ip) : ?>
                <li>
                    <?php echo esc_html($ip); ?>
                    <form method="post" style="display:inline;">
                        <?php wp_nonce_field('tgbfp_remove_ip_action'); ?>
                        <input type="hidden" name="tgbfp_remove_ip" value="<?php echo esc_attr($ip); ?>" />
                        <input type="submit" name="tgbfp_remove_ip" value="Remove" class="button button-secondary" />
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
}

// IP Logs page
function tgbfp_ip_logs_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tgbfp_login_attempts';
    ?>
    <div class="wrap">
        <h1>IP Logs</h1>
        <input type="text" id="tgbfp_ip_search" placeholder="Search IPs..." />
        <table id="tgbfp_ip_table" class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th class="sortable" data-sort="ip">IP Address <span class="sorting-indicator"></span></th>
                    <th class="sortable" data-sort="time">Timestamp <span class="sorting-indicator"></span></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $logs = $wpdb->get_results("SELECT ip_address, attempt_time FROM $table_name ORDER BY attempt_time DESC LIMIT 100");
                if ($logs) {
                    foreach ($logs as $log) {
                        echo '<tr>';
                        echo '<td>' . esc_html($log->ip_address) . '</td>';
                        echo '<td>' . esc_html($log->attempt_time) . '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="2">No login attempts recorded yet.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Geolocation page
function tgbfp_geolocation_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'tgbfp_login_attempts';

    // Check for MaxMind library
    $autoload_file = TGBFP_PLUGIN_DIR . 'vendor/autoload.php';
    if (!file_exists($autoload_file)) {
        echo '<div class="notice notice-error"><p>MaxMind GeoIP2 library not found. Please install it via Composer: <code>composer require geoip2/geoip2:~2.0</code> in the plugin directory.</p></div>';
        return;
    }
    require_once $autoload_file;

    $geo_db_path = get_option('tgbfp_geo_db_path', '/usr/share/GeoIP/GeoLite2-City.mmdb');
    if (!file_exists($geo_db_path)) {
        echo '<div class="notice notice-error"><p>GeoLite2 database not found at <code>' . esc_html($geo_db_path) . '</code>. Please download it from <a href="https://dev.maxmind.com/geoip/geoip2/geolite2/" target="_blank">MaxMind</a> and place it in the specified location, or update the path in the plugin settings.</p></div>';
        return;
    }

    if (!class_exists('MaxMind\Db\Reader')) {
        echo '<div class="notice notice-error"><p>MaxMind\Db\Reader class not found. Ensure the GeoIP2 library is correctly installed.</p></div>';
        return;
    }

    $reader = new MaxMind\Db\Reader($geo_db_path);
    $ips = $wpdb->get_results("SELECT ip_address, COUNT(*) as count FROM $table_name GROUP BY ip_address");

    $locations = [];
    if ($ips) {
        foreach ($ips as $ip_data) {
            try {
                $record = $reader->city($ip_data->ip_address);
                $country = $record->country->isoCode ?? 'Unknown';
                $lat = $record->location->latitude;
                $lng = $record->location->longitude;
                if ($lat && $lng) {
                    $locations[$country][] = [
                        'ip' => $ip_data->ip_address,
                        'lat' => $lat,
                        'lng' => $lng,
                        'count' => $ip_data->count
                    ];
                }
            } catch (Exception $e) {
                // Silently skip invalid IPs
                continue;
            }
        }
    }
    $reader->close();

    ?>
    <div class="wrap">
        <h1>Geolocation Map</h1>
        <?php if (empty($locations)) : ?>
            <p>No geolocation data available yet. Failed login attempts are required to populate the map.</p>
        <?php else : ?>
            <div id="tgbfp_map" style="height: 600px;"></div>
            <script>
                var tgbfp_locations = <?php echo json_encode($locations); ?>;
            </script>
        <?php endif; ?>
    </div>
    <?php
}

// Register settings
add_action('admin_init', 'tgbfp_register_settings');
function tgbfp_register_settings() {
    register_setting('tgbfp_settings_group', 'tgbfp_max_attempts', ['sanitize_callback' => 'absint']);
    register_setting('tgbfp_settings_group', 'tgbfp_lockout_duration', ['sanitize_callback' => 'absint']);
    register_setting('tgbfp_settings_group', 'tgbfp_geo_db_path', ['sanitize_callback' => 'sanitize_text_field']);
}
?>
