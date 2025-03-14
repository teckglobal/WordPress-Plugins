=== TeckGlobal Brute Force Protect ===
Contributors: TeckGlobal LLC
Author URI: https://teck-global.com
Plugin URI: https://teck-global.com/wordpress-plugins
Requires at least: 5.0
Tested up to: 6.7
Stable tag: 1.0.4
Requires PHP: 7.4 or later
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: wordpress, security, brute force, login protection, geolocation, ip management, wordpress plugin

Protect your WordPress site from brute force attacks by logging and banning suspicious IPs with geolocation tracking.

== Description ==

TeckGlobal Brute Force Protect is a lightweight security plugin designed to safeguard your WordPress site from brute force login attempts. It tracks failed login attempts, bans IPs exceeding a configurable threshold, and provides detailed logs and a geolocation map of suspicious activity. Built by TeckGlobal LLC, this plugin integrates MaxMind GeoIP2 for location data and Leaflet.js for interactive mapping.

### Features
- **IP Logging**: Records all login attempts with timestamps, attempt counts, and geolocation data.
- **Automatic IP Banning**: Bans IPs after a set number of failed attempts or for invalid username attempts (optional).
- **Geolocation**: Displays the country, latitude, and longitude of logged IPs using MaxMind GeoIP2.
- **Interactive Map**: Visualizes banned IPs on a Leaflet.js-powered map.
- **Admin Dashboard**: Includes settings, IP management, logs, and a geolocation map view.
- **Customizable Options**: Configure max attempts, ban duration, GeoIP database path, and auto-ban settings.

### Requirements
- **MaxMind GeoIP2 Library**: Install via Composer (`geoip2/geoip2:~2.0`).
- **GeoLite2-City Database**: Download from [MaxMind](https://dev.maxmind.com/geoip/geoip2/geolite2/) (free account required).
- **PHP 7.4+**: Required for GeoIP2 compatibility.

== Installation ==

1. **Upload the Plugin**:
   - Download the plugin ZIP file.
   - In WordPress, go to Plugins > Add New > Upload Plugin, and upload the ZIP.
   - Or extract the folder to `wp-content/plugins/teckglobal-brute-force-protect/`.

2. **Activate the Plugin**:
   - Go to Plugins and activate "TeckGlobal Brute Force Protect".

3. **Install GeoIP2 Library**:
   - Run `composer require geoip2/geoip2:~2.0` in the plugin directory (`wp-content/plugins/teckglobal-brute-force-protect/`) to install the MaxMind GeoIP2 library.

4. **Set Up GeoIP Database**:
   - Download `GeoLite2-City.mmdb` from [MaxMind](https://dev.maxmind.com/geoip/geoip2/geolite2/).
   - Upload it to a server location (e.g., `wp-content/uploads/GeoLite2-City.mmdb`).
   - In WordPress admin, go to Settings > Brute Force Protect and enter the full server path to the file.

5. **Configure Settings**:
   - Adjust max login attempts, ban duration, and enable auto-ban for invalid usernames as needed.

== Frequently Asked Questions ==

= Why is the country showing as "Unknown"? =
Ensure the `GeoLite2-City.mmdb` path in Settings is correct and the file is readable by PHP. Private IPs (e.g., `127.0.0.1`) may also return "Unknown".

= Why isn’t the map showing? =
Check the browser console (F12 > Console) for errors. Ensure Leaflet.js is loading (via CDN or local files) and that logged IPs have valid latitude/longitude data.

= How do I enable debug logging? =
Add `define('WP_DEBUG', true);` and `define('WP_DEBUG_LOG', true);` to `wp-config.php`. Logs will appear in `wp-content/teckglobal-bfp-debug.log`.

= Can I use a custom logo? =
Yes, upload an image via the Settings page to display your logo in the plugin summary.

== Screenshots ==

1. **Settings Page**: Configure GeoIP path, max attempts, ban duration, and upload a logo.
   *(Add screenshot URL or note: "Settings configuration interface.")*
2. **IP Logs**: View a table of logged IPs with ban status and geolocation data.
   *(Add screenshot URL or note: "IP logs table view.")*
3. **Geolocation Map**: Interactive map showing banned IPs with popup details.
   *(Add screenshot URL or note: "Map displaying IP locations.")*

== Changelog ==

= 1.0.0 =
* Initial release with IP logging, banning, geolocation, and map features.

== Upgrade Notice ==

= 1.0.0 =
This is the first version of the plugin. No upgrades available yet.

== Additional Notes ==
- Debug logs are stored in `wp-content/teckglobal-bfp-debug.log` when debugging is enabled.
- For support, visit [https://teck-global.com](https://teck-global.com).

== Compatibility ==
- WordPress: 5.0+
- PHP: 7.4+ (Tested up to 8.3)
- Database: MySQL/MariaDB (no database interaction required)
- Server: Nginx