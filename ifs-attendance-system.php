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

// গ্লোবাল কনস্ট্যান্ট
define('IFS_VERSION', '1.0');
define('IFS_PLUGIN_FILE', __FILE__);
define('IFS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('IFS_INC_DIR', IFS_PLUGIN_DIR . 'inc/');

// সেশন হ্যান্ডলিং (নিরাপদ উপায়ে)
add_action('init', 'ifs_erp_init_session', 1);
function ifs_erp_init_session() {
    if (!session_id() && !headers_sent() && !is_admin()) {
        session_start();
    }
}

// ইনক্লুড ফোল্ডার থেকে ফাইলগুলো লোড করা
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

// অ্যাডমিন মেনু রেজিস্টার
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

// মাস্টার ওয়ার্কস্পেস ফাংশন
function ifs_erp_render_master_workspace() {
    global $wpdb;
    ifs_pro_inject_ultimate_styles();

    $page_view = isset($_GET['page_view']) ? sanitize_text_field($_GET['page_view']) : 'dashboard';
    $sub_tab   = isset($_GET['sub_tab']) ? sanitize_text_field($_GET['sub_tab']) : 'all';
    $base_url  = admin_url('admin.php?page=ifs-attendance');
    $alert     = $_GET['ifs_alert'] ?? '';

    $table_students   = $wpdb->prefix . 'ifs_students';
    $table_attendance = $wpdb->prefix . 'ifs_attendance';
    $table_fees       = $wpdb->prefix . 'ifs_fees';
    $table_batches    = $wpdb->prefix . 'ifs_batches';
    $table_teachers   = $wpdb->prefix . 'ifs_teachers';
    $table_holidays   = $wpdb->prefix . 'ifs_holidays';
    $table_notices    = $wpdb->prefix . 'ifs_notices';
    $table_meta       = $wpdb->prefix . 'ifs_meta';

    $available_batches = $wpdb->get_col("SELECT batch_name FROM $table_batches ORDER BY batch_name ASC");
    $available_insts   = $wpdb->get_col("SELECT meta_value FROM $table_meta WHERE meta_type = 'institution' ORDER BY meta_value ASC");
    $teachers_map      = $wpdb->get_results("SELECT id, name FROM $table_teachers ORDER BY name ASC", OBJECT_K);

    $currency_symbol = get_option('ifs_currency_symbol', '৳');
    $portal_name     = get_option('ifs_portal_name', 'IFS Academy');
    $total_students_all = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_students WHERE status = 'active'");
    $today              = date('Y-m-d');
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
                    <div class="ifs-menu-item-left"><?php echo ifs_get_icon('dashboard'); ?><span>Dashboard</span></div>
                </a>

                <div class="ifs-menu-heading">Academics</div>
                <a href="<?php echo esc_url($base_url . '&page_view=attendance'); ?>" class="ifs-menu-item <?php echo ($page_view === 'attendance') ? 'active' : ''; ?>">
                    <div class="ifs-menu-item-left"><?php echo ifs_get_icon('attendance'); ?><span>Attendance</span></div>
                </a>
                <a href="<?php echo esc_url($base_url . '&page_view=students&sub_tab=all'); ?>" class="ifs-menu-item <?php echo ($page_view === 'students' || $page_view === 'profile') ? 'active' : ''; ?>">
                    <div class="ifs-menu-item-left"><?php echo ifs_get_icon('students'); ?><span>Students</span></div>
                </a>
                <a href="<?php echo esc_url($base_url . '&page_view=batches'); ?>" class="ifs-menu-item <?php echo ($page_view === 'batches') ? 'active' : ''; ?>">
                    <div class="ifs-menu-item-left"><?php echo ifs_get_icon('batches'); ?><span>Batches</span></div>
                </a>
                <a href="<?php echo esc_url($base_url . '&page_view=teachers'); ?>" class="ifs-menu-item <?php echo ($page_view === 'teachers') ? 'active' : ''; ?>">
                    <div class="ifs-menu-item-left"><?php echo ifs_get_icon('teachers'); ?><span>Teachers</span></div>
                </a>
                <a href="<?php echo esc_url($base_url . '&page_view=schedule'); ?>" class="ifs-menu-item <?php echo ($page_view === 'schedule') ? 'active' : ''; ?>">
                    <div class="ifs-menu-item-left"><?php echo ifs_get_icon('schedule'); ?><span>Holidays</span></div>
                </a>

                <div class="ifs-menu-heading">Finance & System</div>
                <a href="<?php echo esc_url($base_url . '&page_view=fees&sub_tab=all'); ?>" class="ifs-menu-item <?php echo ($page_view === 'fees') ? 'active' : ''; ?>">
                    <div class="ifs-menu-item-left"><?php echo ifs_get_icon('fees'); ?><span>Fees Ledger</span></div>
                </a>
                <a href="<?php echo esc_url($base_url . '&page_view=reports'); ?>" class="ifs-menu-item <?php echo ($page_view === 'reports') ? 'active' : ''; ?>">
                    <div class="ifs-menu-item-left"><?php echo ifs_get_icon('reports'); ?><span>Analytics</span></div>
                </a>
                <a href="<?php echo esc_url($base_url . '&page_view=notices'); ?>" class="ifs-menu-item <?php echo ($page_view === 'notices') ? 'active' : ''; ?>">
                    <div class="ifs-menu-item-left"><?php echo ifs_get_icon('notices'); ?><span>Notice Board</span></div>
                </a>
                <a href="<?php echo esc_url($base_url . '&page_view=settings&sub_tab=institutions'); ?>" class="ifs-menu-item <?php echo ($page_view === 'settings') ? 'active' : ''; ?>">
                    <div class="ifs-menu-item-left"><?php echo ifs_get_icon('settings'); ?><span>Settings</span></div>
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
                <div class="ifs-date-pill">📅 <?php echo date('M d, Y'); ?></div>
            </header>

            <div class="ifs-body">
                <?php if ($alert): ?>
                    <div class="ifs-alert ifs-alert-success">✓ Operation executed successfully!</div>
                <?php endif; ?>

                <?php
                if ($page_view === 'dashboard') {
                    if (function_exists('ifs_erp_render_dashboard_view')) {
                        ifs_erp_render_dashboard_view($wpdb, $table_students, $table_attendance, $table_fees, $table_batches, $table_teachers, $available_batches, $currency_symbol, $portal_name, $total_students_all, $today, $base_url);
                    }
                } elseif ($page_view === 'attendance') {
                    if (function_exists('ifs_erp_render_attendance_view')) {
                        ifs_erp_render_attendance_view($wpdb, $table_students, $table_attendance, $available_batches, $portal_name, $base_url);
                    }
                } elseif ($page_view === 'students' || $page_view === 'profile') {
                    if ($page_view === 'profile') {
                        if (function_exists('ifs_erp_render_student_profile_view')) {
                            $student_id = intval($_GET['student_id'] ?? 0);
                            ifs_erp_render_student_profile_view($wpdb, $student_id, $table_students, $table_attendance, $table_fees, $currency_symbol, $base_url);
                        }
                    } else {
                        if (function_exists('ifs_erp_render_students_workspace')) {
                            ifs_erp_render_students_workspace($wpdb, $table_students, $available_batches, $available_insts, $sub_tab, $base_url, $currency_symbol);
                        }
                    }
                } elseif ($page_view === 'batches') {
                    if (function_exists('ifs_erp_render_batches_workspace')) {
                        ifs_erp_render_batches_workspace($wpdb, $table_batches, $teachers_map, $currency_symbol);
                    }
                } elseif ($page_view === 'teachers') {
                    if (function_exists('ifs_erp_render_teachers_workspace')) {
                        ifs_erp_render_teachers_workspace($wpdb, $table_teachers, $currency_symbol);
                    }
                } elseif ($page_view === 'schedule') {
                    if (function_exists('ifs_erp_render_schedule_workspace')) {
                        ifs_erp_render_schedule_workspace($wpdb, $table_holidays);
                    }
                } elseif ($page_view === 'fees') {
                    if (function_exists('ifs_erp_render_fees_workspace')) {
                        ifs_erp_render_fees_workspace($wpdb, $table_fees, $table_students, $table_batches, $sub_tab, $base_url, $currency_symbol, $portal_name);
                    }
                } elseif ($page_view === 'reports') {
                    if (function_exists('ifs_erp_render_reports_workspace')) {
                        ifs_erp_render_reports_workspace($wpdb, $table_students, $table_attendance, $available_batches);
                    }
                } elseif ($page_view === 'notices') {
                    if (function_exists('ifs_erp_render_notices_workspace')) {
                        ifs_erp_render_notices_workspace($wpdb, $table_notices, $available_batches);
                    }
                } elseif ($page_view === 'settings') {
                    if (function_exists('ifs_erp_render_settings_workspace')) {
                        ifs_erp_render_settings_workspace($wpdb, $table_meta, $sub_tab, $portal_name, $currency_symbol);
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <script>
    function openMediaUploader(input_id, preview_id) {
        var mediaUploader = wp.media({
            title: 'Choose Profile Picture',
            button: { text: 'Use this photo' },
            multiple: false
        });
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            document.getElementById(input_id).value = attachment.url;
            document.getElementById(preview_id).src = attachment.url;
        });
        mediaUploader.open();
    }
    </script>
    <?php
}