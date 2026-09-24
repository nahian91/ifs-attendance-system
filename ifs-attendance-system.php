<?php
/**
 * Plugin Name: IFS Academy Management System
 * Plugin URI: https://example.com/
 * Description: 10/10 Enterprise Modular ERP with Ultra-Smooth Scrollbars, Split Files, Media Library Uploader, Admission Fee Reminders, Day-wise Batch Routine & Frontend Portal. Shortcode: [attendance_portal]
 * Version: 1.0
 * Author: Abdullah Nahian
 * Text Domain: ifs-attendance
 */

if (!defined('ABSPATH')) {
    exit;
}

// Global Constants
define('IFS_VERSION', '1.0');
define('IFS_PLUGIN_FILE', __FILE__);
define('IFS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('IFS_INC_DIR', IFS_PLUGIN_DIR . 'inc/');

// Session Handling
add_action('init', 'ifs_erp_init_session', 1);
function ifs_erp_init_session() {
    if (session_status() === PHP_SESSION_NONE && !headers_sent() && !is_admin()) {
        session_start();
    }
}

// Include Module Files
require_once IFS_INC_DIR . 'db.php';
require_once IFS_INC_DIR . 'assets.php';
require_once IFS_INC_DIR . 'ajax-actions.php';
require_once IFS_INC_DIR . 'dashboard.php';
require_once IFS_INC_DIR . 'attendance.php';
require_once IFS_INC_DIR . 'students.php';
require_once IFS_INC_DIR . 'batches.php';
require_once IFS_INC_DIR . 'teachers.php';
require_once IFS_INC_DIR . 'schedule.php';
require_once IFS_INC_DIR . 'fees.php';
require_once IFS_INC_DIR . 'reports.php';
require_once IFS_INC_DIR . 'notices.php';
require_once IFS_INC_DIR . 'settings.php';
require_once IFS_INC_DIR . 'frontend-portal.php';

// Activation Hook
register_activation_hook(__FILE__, 'ifs_erp_create_database_tables');

// Enqueue WP Media on Admin
add_action('admin_enqueue_scripts', 'ifs_erp_admin_enqueue_media');
function ifs_erp_admin_enqueue_media($hook) {
    if (isset($_GET['page']) && $_GET['page'] === 'ifs-attendance') {
        wp_enqueue_media();
    }
}

// Register Admin Menu
add_action('admin_menu', 'ifs_erp_register_workspace_menu');
function ifs_erp_register_workspace_menu() {
    add_menu_page(
        'IFS Academy', 
        'IFS Academy', 
        'manage_options', 
        'ifs-attendance', 
        'ifs_erp_render_master_workspace', 
        'dashicons-superhero', 
        2
    );
}

// Master Workspace View
function ifs_erp_render_master_workspace() {
    global $wpdb;
    
    if (function_exists('ifs_pro_inject_ultimate_styles')) {
        ifs_pro_inject_ultimate_styles();
    }

    $page_view = isset($_GET['page_view']) ? sanitize_key($_GET['page_view']) : 'dashboard';
    $sub_tab   = isset($_GET['sub_tab']) ? sanitize_key($_GET['sub_tab']) : 'all';
    $base_url  = admin_url('admin.php?page=ifs-attendance');
    $alert     = isset($_GET['ifs_alert']) ? sanitize_text_field($_GET['ifs_alert']) : '';

    $table_students   = $wpdb->prefix . 'ifs_students';
    $table_attendance = $wpdb->prefix . 'ifs_attendance';
    $table_fees       = $wpdb->prefix . 'ifs_fees';
    $table_batches    = $wpdb->prefix . 'ifs_batches';
    $table_teachers   = $wpdb->prefix . 'ifs_teachers';
    $table_holidays   = $wpdb->prefix . 'ifs_holidays';
    $table_notices    = $wpdb->prefix . 'ifs_notices';
    $table_meta       = $wpdb->prefix . 'ifs_meta';

    $available_batches = $wpdb->get_col("SELECT batch_name FROM {$table_batches} ORDER BY batch_name ASC");
    $available_insts   = $wpdb->get_col($wpdb->prepare("SELECT meta_value FROM {$table_meta} WHERE meta_type = %s ORDER BY meta_value ASC", 'institution'));
    $teachers_map      = $wpdb->get_results("SELECT id, name FROM {$table_teachers} ORDER BY name ASC", OBJECT_K);

    $currency_symbol    = get_option('ifs_currency_symbol', '৳');
    $portal_name        = get_option('ifs_portal_name', 'IFS Academy');
    $total_students_all = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table_students} WHERE status = 'active'");
    $today              = current_time('Y-m-d');
    ?>

    <div class="ifs-shell">
        <aside class="ifs-sidebar">
            <div class="ifs-sidebar-brand">
                <div class="ifs-brand-icon" style="padding: 0; background: transparent; box-shadow: none; display: flex; align-items: center; justify-content: center;">
                    <img src="https://attendance.infinityflamesoft.com/wp-content/uploads/2026/09/logo.png" alt="Logo" style="height: 25px; border-radius: 10px; object-fit: cover;">
                </div>
                <div>
                    <div class="ifs-brand-name"><?php echo esc_html($portal_name); ?></div>
                </div>
            </div>

            <nav class="ifs-menu-col ifs-custom-scrollbar">
                <div class="ifs-menu-heading">Overview</div>
                <a href="<?php echo esc_url($base_url . '&page_view=dashboard'); ?>" class="ifs-menu-item <?php echo ($page_view === 'dashboard') ? 'active' : ''; ?>">
                    <div class="ifs-menu-item-left"><?php echo function_exists('ifs_get_icon') ? ifs_get_icon('dashboard') : ''; ?><span>Dashboard</span></div>
                </a>

                <div class="ifs-menu-heading">Academics</div>
                <a href="<?php echo esc_url($base_url . '&page_view=attendance'); ?>" class="ifs-menu-item <?php echo ($page_view === 'attendance') ? 'active' : ''; ?>">
                    <div class="ifs-menu-item-left"><?php echo function_exists('ifs_get_icon') ? ifs_get_icon('attendance') : ''; ?><span>Attendance</span></div>
                </a>
                <a href="<?php echo esc_url($base_url . '&page_view=students&sub_tab=all'); ?>" class="ifs-menu-item <?php echo ($page_view === 'students' || $page_view === 'profile') ? 'active' : ''; ?>">
                    <div class="ifs-menu-item-left"><?php echo function_exists('ifs_get_icon') ? ifs_get_icon('students') : ''; ?><span>Students</span></div>
                </a>
                <a href="<?php echo esc_url($base_url . '&page_view=batches'); ?>" class="ifs-menu-item <?php echo ($page_view === 'batches') ? 'active' : ''; ?>">
                    <div class="ifs-menu-item-left"><?php echo function_exists('ifs_get_icon') ? ifs_get_icon('batches') : ''; ?><span>Batches</span></div>
                </a>
                <a href="<?php echo esc_url($base_url . '&page_view=teachers'); ?>" class="ifs-menu-item <?php echo ($page_view === 'teachers') ? 'active' : ''; ?>">
                    <div class="ifs-menu-item-left"><?php echo function_exists('ifs_get_icon') ? ifs_get_icon('teachers') : ''; ?><span>Teachers</span></div>
                </a>
                <a href="<?php echo esc_url($base_url . '&page_view=schedule'); ?>" class="ifs-menu-item <?php echo ($page_view === 'schedule') ? 'active' : ''; ?>">
                    <div class="ifs-menu-item-left"><?php echo function_exists('ifs_get_icon') ? ifs_get_icon('schedule') : ''; ?><span>Holidays</span></div>
                </a>

                <div class="ifs-menu-heading">Finance & System</div>
                <a href="<?php echo esc_url($base_url . '&page_view=fees&sub_tab=all'); ?>" class="ifs-menu-item <?php echo ($page_view === 'fees') ? 'active' : ''; ?>">
                    <div class="ifs-menu-item-left"><?php echo function_exists('ifs_get_icon') ? ifs_get_icon('fees') : ''; ?><span>Fees Ledger</span></div>
                </a>
                <a href="<?php echo esc_url($base_url . '&page_view=reports'); ?>" class="ifs-menu-item <?php echo ($page_view === 'reports') ? 'active' : ''; ?>">
                    <div class="ifs-menu-item-left"><?php echo function_exists('ifs_get_icon') ? ifs_get_icon('reports') : ''; ?><span>Analytics</span></div>
                </a>
                <a href="<?php echo esc_url($base_url . '&page_view=notices'); ?>" class="ifs-menu-item <?php echo ($page_view === 'notices') ? 'active' : ''; ?>">
                    <div class="ifs-menu-item-left"><?php echo function_exists('ifs_get_icon') ? ifs_get_icon('notices') : ''; ?><span>Notice Board</span></div>
                </a>
                <a href="<?php echo esc_url($base_url . '&page_view=settings&sub_tab=institutions'); ?>" class="ifs-menu-item <?php echo ($page_view === 'settings') ? 'active' : ''; ?>">
                    <div class="ifs-menu-item-left"><?php echo function_exists('ifs_get_icon') ? ifs_get_icon('settings') : ''; ?><span>Settings</span></div>
                </a>
            </nav>

            <div class="ifs-sidebar-footer">
                <a href="<?php echo esc_url(wp_logout_url(admin_url())); ?>" class="ifs-sidebar-link">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <div class="ifs-content-wrapper">
            <header class="ifs-main-topbar">
                <h2 class="ifs-topbar-title">ERP Control Center</h2>
                <div class="ifs-date-pill">📅 <?php echo esc_html(current_time('M d, Y')); ?></div>
            </header>

            <div class="ifs-body">
                <?php if ($alert): ?>
                    <div class="ifs-alert ifs-alert-success">✓ Operation executed successfully!</div>
                <?php endif; ?>

                <?php
                if ($page_view === 'dashboard' && function_exists('ifs_erp_render_dashboard_view')) {
                    ifs_erp_render_dashboard_view($wpdb, $table_students, $table_attendance, $table_fees, $table_batches, $table_teachers, $available_batches, $currency_symbol, $portal_name, $total_students_all, $today, $base_url);
                } elseif ($page_view === 'attendance' && function_exists('ifs_erp_render_attendance_view')) {
                    ifs_erp_render_attendance_view($wpdb, $table_students, $table_attendance, $available_batches, $portal_name, $base_url);
                } elseif ($page_view === 'students' || $page_view === 'profile') {
                    if ($page_view === 'profile' && function_exists('ifs_erp_render_student_profile_view')) {
                        $student_id = intval($_GET['student_id'] ?? 0);
                        ifs_erp_render_student_profile_view($wpdb, $student_id, $table_students, $table_attendance, $table_fees, $currency_symbol, $base_url);
                    } elseif (function_exists('ifs_erp_render_students_workspace')) {
                        ifs_erp_render_students_workspace($wpdb, $table_students, $available_batches, $available_insts, $sub_tab, $base_url, $currency_symbol);
                    }
                } elseif ($page_view === 'batches' && function_exists('ifs_erp_render_batches_workspace')) {
                    ifs_erp_render_batches_workspace($wpdb, $table_batches, $teachers_map, $currency_symbol);
                } elseif ($page_view === 'teachers' && function_exists('ifs_erp_render_teachers_workspace')) {
                    ifs_erp_render_teachers_workspace($wpdb, $table_teachers, $currency_symbol);
                } elseif ($page_view === 'schedule' && function_exists('ifs_erp_render_schedule_workspace')) {
                    ifs_erp_render_schedule_workspace($wpdb, $table_holidays);
                } elseif ($page_view === 'fees' && function_exists('ifs_erp_render_fees_workspace')) {
                    ifs_erp_render_fees_workspace($wpdb, $table_fees, $table_students, $table_batches, $sub_tab, $base_url, $currency_symbol, $portal_name);
                } elseif ($page_view === 'reports' && function_exists('ifs_erp_render_reports_workspace')) {
                    ifs_erp_render_reports_workspace($wpdb, $table_students, $table_attendance, $available_batches);
                } elseif ($page_view === 'notices' && function_exists('ifs_erp_render_notices_workspace')) {
                    ifs_erp_render_notices_workspace($wpdb, $table_notices, $available_batches);
                } elseif ($page_view === 'settings' && function_exists('ifs_erp_render_settings_workspace')) {
                    ifs_erp_render_settings_workspace($wpdb, $table_meta, $sub_tab, $portal_name, $currency_symbol);
                }
                ?>
            </div>
        </div>
    </div>

    <script>
    function openMediaUploader(input_id, preview_id) {
        if (typeof wp === 'undefined' || !wp.media) {
            alert('Media uploader is not available.');
            return;
        }
        var mediaUploader = wp.media({
            title: 'Choose Profile Picture',
            button: { text: 'Use this photo' },
            multiple: false
        });
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            var inputElem = document.getElementById(input_id);
            var previewElem = document.getElementById(preview_id);
            if (inputElem) inputElem.value = attachment.url;
            if (previewElem) previewElem.src = attachment.url;
        });
        mediaUploader.open();
    }
    </script>
    <?php
}

// Redirect on Admin Login
add_filter('login_redirect', 'ifs_erp_custom_admin_login_redirect', 10, 3);
function ifs_erp_custom_admin_login_redirect($redirect_to, $request, $user) {
    if (isset($user->roles) && is_array($user->roles)) {
        if (in_array('administrator', $user->roles, true) || in_array('editor', $user->roles, true)) {
            return admin_url('admin.php?page=ifs-attendance&page_view=dashboard');
        }
    }
    return $redirect_to;
}