=== Teck Global Brute Force Protection ===
Contributors: teckglobal
Tags: security, brute force, ip management, geolocation, wordpress security
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.0.2
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

A WordPress plugin by Teck Global to prevent brute force login attacks with IP management and geolocation features.

== Description ==
Teck Global Brute Force Protection is a robust security plugin designed to safeguard your WordPress site from brute force login attacks. It includes advanced features such as IP banning, login attempt logging, a sortable and searchable IP log table, and geolocation visualization using MaxMind's GeoLite2 database and Google Maps.

### Features
- **Brute Force Protection:** Limits login attempts and bans IPs after exceeding the threshold.
- **IP Management:** Add or remove banned IPs via an intuitive interface.
- **IP Logs:** View login attempts in a sortable, searchable table with timestamps.
- **Geolocation:** Visualize IP locations on Google Maps with color-coded dots based on attempt frequency:
  - Red: 26+ attempts from the same country
  - Orange: 11-25 attempts from the same country
  - Green: 1-10 attempts from the same country
- **MaxMind Integration:** Uses the GeoLite2-City.mmdb file (configurable path, default: `/your/path/GeoIP/GeoLite2-City.mmdb`) for geolocation data.

### Company Information
Teck Global is a leading provider of IT solutions, specializing in cybersecurity and WordPress development. Visit us at [https://teck-global.com](https://teck-global.com) or check out our plugins at [https://teck-global.com/wordpress-plugins](https://teck-global.com/wordpress-plugins).

== Installation ==
1. Upload the `teck-global-brute-force-protection` folder to the `/wp-content/plugins/` directory.
2. Install the MaxMind GeoIP2 library by running `composer require geoip2/geoip2:~2.0` in the plugin directory. "You might need to install compose"
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. Download the GeoLite2-City.mmdb file from [MaxMind](https://dev.maxmind.com/geoip/geoip2/geolite2/) and place it at `/usr/share/GeoIP/GeoLite2-City.mmdb` (or configure a different path in the settings).
5. Replace `YOUR_GOOGLE_MAPS_API_KEY` in `teck-global-brute-force-protection.php` with your Google Maps API key. "Get your API key at https://console.cloud.google.com/apis"
6. Configure settings under the "TeckGlobal Security" menu in the WordPress admin sidebar.

== Frequently Asked Questions ==
= Where do I get the GeoLite2 database? =
Download it from [MaxMind](https://dev.maxmind.com/geoip/geoip2/geolite2/) and place it at `/usr/share/GeoIP/GeoLite2-City.mmdb` or your preferred location (configurable in settings).

= What is the default lockout duration? =
The default lockout duration is 1 hour (3600 seconds), configurable in the settings.

= Why do I see a "GeoLite2 database not found" error? =
This means the GeoLite2-City.mmdb file is missing or not at the specified path. Download it from MaxMind and update the path in the plugin settings if needed.

== Screenshots ==
1. Settings page for configuring max attempts, lockout duration, and GeoLite2 database path.
2. IP Management interface for adding/removing banned IPs.
3. IP Logs table with sorting and search functionality.
4. Geolocation Map showing IP locations with color-coded markers.

== Changelog ==
= 1.0.2 =
* Added configurable GeoLite2 database path in settings.
* Fixed error message to display the full path correctly.
* Improved documentation for GeoLite2 database setup.

= 1.0.1 =
* Fixed critical error on Geolocation page by adding dependency checks and error handling.
* Improved Google Maps initialization and enqueue logic.

= 1.0.0 =
* Initial release with brute force protection, IP management, logs, and geolocation features.

== Upgrade Notice ==
= 1.0.2 =
This update adds a configurable GeoLite2 database path to avoid "not found" errors. Update your settings if the default path doesn’t match your server.

== Compatibility ==
- WordPress: 5.0+
- PHP: 7.4+
- Database: Compatible with MariaDB and MySQL
