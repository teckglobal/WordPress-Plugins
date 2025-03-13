=== Teck Global Permissions Checker ===
Contributors: TeckGlobal LLC
Author URI: https://teck-global.com
Plugin URI: https://teck-global.com/wordpress-plugins
Tags: login, customization, branding
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Tags: security, permissions, file permissions, wordpress security

A WordPress plugin by Teck Global to check and fix file and folder permissions in the WordPress root directory.

== Description ==
Teck Global Permissions Checker scans all files and folders in the WordPress root directory and reports on their permissions. It highlights misconfigured permissions in a color-coded table:
- **Green:** Permissions are correct (644 for files, 755 for directories).
- **Yellow:** Permissions differ from recommended but are not critical.
- **Red:** Permissions are too permissive (e.g., 777 for directories, 666 for files), posing a security risk.

### Features
- Scans the entire WordPress root directory recursively.
- Reports current permissions, recommended permissions, and status for each file and folder.
- Color-coded table for easy identification of issues.
- "Fix" button to repair individual file/folder permissions.
- "Fix All" button to correct all permissions at once, with a warning during the process.
- Built-in sorting for the permissions report table.

### Company Information
Teck Global is a leading provider of IT solutions, specializing in cybersecurity and WordPress development. Visit us at [https://teck-global.com](https://teck-global.com) or check out our plugins at [https://teck-global.com/wordpress-plugins](https://teck-global.com/wordpress-plugins).

== Installation ==
1. Upload the `teck-global-permissions-checker` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to "Permissions Checker" in the WordPress admin sidebar to view and fix permissions.

== Frequently Asked Questions ==
= What are the recommended permissions? =
Files should be 644 (readable by all, writable by owner), and directories should be 755 (readable/executable by all, writable by owner).

= Why are some permissions marked as critical? =
Permissions like 777 or 666 allow anyone to write to the file or directory, which is a security risk on a web server.

= Why might fixing permissions fail? =
The web server user (e.g., `www-data`) must have permission to modify files. If files are owned by another user (e.g., `root`), fixes may fail, requiring manual adjustment via SSH.

== Screenshots ==
1. Permissions report table with "Fix" and "Fix All" options, showing color-coded statuses.

== Changelog ==
= 1.0.1 =
* Added function to fix individual file/folder permissions.
* Added "Fix All" function to repair all permissions at once with a warning during the process.

= 1.0.0 =
* Initial release with permissions scanning and reporting features.

== Upgrade Notice ==
= 1.0.1 =
This update adds the ability to fix permissions individually or all at once. Ensure your web server has sufficient permissions to modify files.

== Compatibility ==
- WordPress: 5.0+
- PHP: 7.4+
- Database: Compatible with MariaDB and MySQL (no database interaction required)
