=== TeckGlobal Brute Force Protect ===
Contributors: TeckGlobal LLC
Tags: security, brute force, login protection, geolocation, ip management
Requires at least: 5.0
Tested up to: 6.5
Stable tag: 1.0.4
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A WordPress plugin by TeckGlobal LLC to prevent brute force login attacks with IP management and geolocation features.

== Description ==
TeckGlobal Brute Force Protect is a robust security plugin designed by TeckGlobal LLC to safeguard your WordPress site from brute force login attacks. It includes features like IP banning, a sortable and searchable IP logs table, and geolocation visualization using MaxMind's GeoLite2 database and Google Maps.

### Features:
- **Brute Force Protection**: Configurable login attempt limit before IP ban (default: 5).
- **IP Management**: Add or remove banned IPs via an admin interface or IP Logs table.
- **IP Logs**: View a table of IP logs with timestamps, attempts, ban status, expiry, and country, sortable and searchable, with an option to remove bans.
- **Geolocation**: Displays IP locations on a Google Map with color-coded dots (red: 26+, orange: 11-25, green: 1-10 attempts per country).
- **MaxMind Integration**: Uses a configurable GeoLite2-City.mmdb file path (default: `/usr/share/GeoIP/GeoLite2-City.mmdb`) for geolocation lookups.
- **Summary Dashboard**: Displays total brute force attempts, TeckGlobal LLC website link, and an option to upload a logo.
- **Configurable Ban Settings**: Adjust max attempts, ban duration, and auto-ban invalid usernames.

### Company Information:
TeckGlobal LLC is a technology solutions provider based at [https://teck-global.com](https://teck-global.com). Visit our plugin page at [https://teck-global.com/wordpress-plugins](https://teck-global.com/wordpress-plugins) for more details and support.

== Installation ==
1. Upload the `teckglobal-brute-force-protect` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Ensure the MaxMind GeoLite2-City.mmdb file is available and configure its path in the plugin settings.
4. Replace `YOUR_GOOGLE_MAPS_API_KEY` in `teckglobal-brute-force-protect.php` with your Google Maps API key.
5. Enable `WP_DEBUG` in `wp-config.php` to log debug info to `wp-content/teckglobal-bfp-debug.log`.
6. Navigate to the "Brute Force Protect" menu in the WordPress admin sidebar to configure settings.

== Frequently Asked Questions ==
= Does this plugin work with MariaDB? =
Yes, the plugin is fully compatible with MariaDB via WordPress's database abstraction layer.

= How do I get a Google Maps API key? =
Visit [Google Cloud Console](https://console.cloud.google.com) to create a project, enable the Maps JavaScript API, and generate an API key.

= Why isn’t the country showing for IPs? =
Check the debug log (`wp-content/teckglobal-bfp-debug.log`) for GeoIP errors. Ensure the GeoLite2-City.mmdb file is at the specified path and the MaxMind library is installed.

== Changelog ==
= 1.0.4 =
* Added "Remove Ban" option in the IP Logs table.
* Fixed country not showing for banned IPs by improving GeoIP integration and debugging.

= 1.0.3 =
* Fixed IP logging and banning issues.
* Added Nginx compatibility with `HTTP_X_FORWARDED_FOR`.
* Enhanced PHP 8.3 compatibility with strict typing.

= 1.0.2 =
* Added options to adjust max login attempts, ban duration, and auto-ban invalid usernames.

= 1.0.1 =
* Added summary with total brute force attempts, GeoLite2 path configuration, and logo upload.

= 1.0.0 =
* Initial release with brute force protection, IP management, logs, and geolocation features.

== Upgrade Notice ==
= 1.0.4 =
Added "Remove Ban" in IP Logs and fixed country display with enhanced GeoIP handling.

== Compatibility ==
- WordPress: 5.0+
- PHP: 7.4+ (Tested up to 8.3)
- Database: MySQL/MariaDB
- Server: Nginx