<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Permissions report page
function tgpchecker_report_page() {
    // Handle single permission fix request
    if (isset($_POST['tgpchecker_fix_permissions']) && check_admin_referer('tgpchecker_fix_permissions_action')) {
        $path = sanitize_text_field($_POST['tgpchecker_path']);
        $result = tgpchecker_fix_permissions($path);
        if ($result === true) {
            echo '<div class="notice notice-success is-dismissible"><p>Permissions for ' . esc_html($path) . ' fixed successfully.</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>Failed to fix permissions for ' . esc_html($path) . '. Check server permissions or file ownership.</p></div>';
        }
    }

    // Handle fix all permissions request
    if (isset($_POST['tgpchecker_fix_all_permissions']) && check_admin_referer('tgpchecker_fix_all_permissions_action')) {
        echo '<div class="notice notice-warning is-dismissible"><p><strong>Warning:</strong> Fixing all permissions is in progress. This may take time depending on the number of files and could fail if the web server lacks sufficient permissions. Do not close this page until complete.</p></div>';
        $results = tgpchecker_fix_all_permissions(ABSPATH);
        echo '<div class="notice notice-info is-dismissible"><p>Fix All Permissions completed. Fixed: ' . esc_html($results['fixed']) . ', Errors: ' . esc_html($results['errors']) . '</p></div>';
    }

    ?>
    <div class="wrap">
        <h1>Teck Global Permissions Checker</h1>
        <p>This tool checks file and folder permissions in the WordPress root directory (<?php echo esc_html(ABSPATH); ?>). Recommended permissions are 644 for files and 755 for directories. Use the "Fix" button for individual items or "Fix All" for everything.</p>
        <form method="post" style="margin-bottom: 20px;">
            <?php wp_nonce_field('tgpchecker_fix_all_permissions_action'); ?>
            <input type="submit" name="tgpchecker_fix_all_permissions" value="Fix All Permissions" class="button button-primary" onclick="return confirm('Are you sure you want to fix all permissions? This may take time and could fail if server permissions are restricted.');" />
        </form>
        <table id="tgpchecker_permissions_table" class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Path</th>
                    <th>Current Permissions</th>
                    <th>Recommended Permissions</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php tgpchecker_scan_directory(ABSPATH); ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Function to scan directory and check permissions
function tgpchecker_scan_directory($dir) {
    $recommended_file_perms = '0644'; // Files should be 644
    $recommended_dir_perms = '0755';  // Directories should be 755

    // Use RecursiveDirectoryIterator to scan all files and folders
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $path = $item->getPathname();
        $perms = substr(sprintf('%o', fileperms($path)), -4); // Get permissions in octal (e.g., 0644)
        $is_dir = is_dir($path);
        $recommended = $is_dir ? $recommended_dir_perms : $recommended_file_perms;
        $status_class = '';
        $status_message = '';

        // Determine status based on permissions
        if ($perms === $recommended) {
            $status_class = 'status-ok';
            $status_message = 'OK';
        } elseif ($is_dir && ($perms === '0777' || substr($perms, -2) > '55')) {
            $status_class = 'status-critical';
            $status_message = 'Critical: Directory too permissive (e.g., 777)';
        } elseif (!$is_dir && ($perms === '0666' || substr($perms, -2) > '44')) {
            $status_class = 'status-critical';
            $status_message = 'Critical: File too permissive (e.g., 666)';
        } else {
            $status_class = 'status-warning';
            $status_message = 'Warning: Permissions differ from recommended';
        }

        ?>
        <tr class="<?php echo esc_attr($status_class); ?>">
            <td><?php echo esc_html($path); ?></td>
            <td><?php echo esc_html($perms); ?></td>
            <td><?php echo esc_html($recommended); ?></td>
            <td><?php echo esc_html($status_message); ?></td>
            <td>
                <?php if ($perms !== $recommended) : ?>
                    <form method="post" style="display:inline;">
                        <?php wp_nonce_field('tgpchecker_fix_permissions_action'); ?>
                        <input type="hidden" name="tgpchecker_path" value="<?php echo esc_attr($path); ?>" />
                        <input type="submit" name="tgpchecker_fix_permissions" value="Fix" class="button button-primary" />
                    </form>
                <?php else : ?>
                    <span class="button button-disabled">No Fix Needed</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php
    }
}

// Function to fix permissions for a single file or folder
function tgpchecker_fix_permissions($path) {
    if (!file_exists($path)) {
        return false; // File/folder doesn’t exist
    }

    $recommended_perms = is_dir($path) ? 0755 : 0644; // Octal values: 755 for dirs, 644 for files

    // Attempt to change permissions
    if (@chmod($path, $recommended_perms)) {
        clearstatcache(); // Clear file status cache to ensure updated perms are read
        $new_perms = substr(sprintf('%o', fileperms($path)), -4);
        return $new_perms === sprintf('%04o', $recommended_perms); // Verify change succeeded
    }

    return false; // chmod failed (e.g., due to ownership or server restrictions)
}

// Function to fix all permissions in the directory
function tgpchecker_fix_all_permissions($dir) {
    $recommended_file_perms = 0644; // Files should be 644 (integer octal)
    $recommended_dir_perms = 0755;  // Directories should be 755 (integer octal)
    $fixed_count = 0;
    $error_count = 0;

    // Use RecursiveDirectoryIterator to scan all files and folders
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $path = $item->getPathname();
        $perms = substr(sprintf('%o', fileperms($path)), -4); // Current permissions in octal
        $is_dir = is_dir($path);
        $recommended = $is_dir ? $recommended_dir_perms : $recommended_file_perms;

        // Only attempt to fix if permissions are incorrect
        if ($perms !== sprintf('%04o', $recommended)) {
            if (@chmod($path, $recommended)) {
                clearstatcache();
                $new_perms = substr(sprintf('%o', fileperms($path)), -4);
                if ($new_perms === sprintf('%04o', $recommended)) {
                    $fixed_count++;
                } else {
                    $error_count++;
                }
            } else {
                $error_count++;
            }
        }
    }

    return ['fixed' => $fixed_count, 'errors' => $error_count];
}
