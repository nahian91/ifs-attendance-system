<?php
if (!defined('ABSPATH')) exit;

register_activation_hook(IFS_PLUGIN_FILE, 'ifs_erp_install_database_schema');
function ifs_erp_install_database_schema() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $table_students = $wpdb->prefix . 'ifs_students';
    $sql_students = "CREATE TABLE $table_students (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        student_uid VARCHAR(60) NOT NULL,
        name VARCHAR(150) NOT NULL,
        email VARCHAR(100) NOT NULL,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(30) DEFAULT '',
        guardian_phone VARCHAR(30) DEFAULT '',
        institution VARCHAR(190) NOT NULL,
        batch VARCHAR(80) NOT NULL,
        photo_url TEXT DEFAULT NULL,
        admission_fee DECIMAL(10,2) DEFAULT 0.00,
        admission_fee_type VARCHAR(20) DEFAULT 'full',
        admission_paid DECIMAL(10,2) DEFAULT 0.00,
        admission_due DECIMAL(10,2) DEFAULT 0.00,
        due_reminder_date DATE DEFAULT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY student_uid (student_uid),
        UNIQUE KEY student_email (email)
    ) $charset_collate;";

    $table_attendance = $wpdb->prefix . 'ifs_attendance';
    $sql_attendance = "CREATE TABLE $table_attendance (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        student_id BIGINT(20) UNSIGNED NOT NULL,
        attendance_date DATE NOT NULL,
        status ENUM('Present','Absent','Late','Excused') NOT NULL DEFAULT 'Present',
        remarks VARCHAR(255) DEFAULT '',
        marked_by BIGINT(20) UNSIGNED DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_daily_att (student_id, attendance_date)
    ) $charset_collate;";

    $table_fees = $wpdb->prefix . 'ifs_fees';
    $sql_fees = "CREATE TABLE $table_fees (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        receipt_no VARCHAR(40) NOT NULL UNIQUE,
        student_id BIGINT(20) UNSIGNED NOT NULL,
        fee_title VARCHAR(190) NOT NULL,
        total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        due_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        payment_method VARCHAR(50) NOT NULL DEFAULT 'Cash',
        payment_date DATE NOT NULL,
        remarks TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $table_batches = $wpdb->prefix . 'ifs_batches';
    $sql_batches = "CREATE TABLE $table_batches (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        batch_name VARCHAR(100) NOT NULL UNIQUE,
        course_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        schedule_json TEXT DEFAULT NULL,
        teacher_id BIGINT(20) UNSIGNED DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $table_teachers = $wpdb->prefix . 'ifs_teachers';
    $sql_teachers = "CREATE TABLE $table_teachers (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(150) NOT NULL,
        designation VARCHAR(100) NOT NULL,
        phone VARCHAR(30) NOT NULL,
        email VARCHAR(100) DEFAULT '',
        subject VARCHAR(100) DEFAULT '',
        salary DECIMAL(10,2) DEFAULT 0.00,
        photo_url TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $table_holidays = $wpdb->prefix . 'ifs_holidays';
    $sql_holidays = "CREATE TABLE $table_holidays (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        holiday_date DATE NOT NULL UNIQUE,
        title VARCHAR(150) NOT NULL,
        holiday_type VARCHAR(50) DEFAULT 'Official',
        PRIMARY KEY (id)
    ) $charset_collate;";

    $table_notices = $wpdb->prefix . 'ifs_notices';
    $sql_notices = "CREATE TABLE $table_notices (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        title VARCHAR(200) NOT NULL,
        description TEXT NOT NULL,
        target_batch VARCHAR(100) DEFAULT 'All',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $table_meta = $wpdb->prefix . 'ifs_meta';
    $sql_meta = "CREATE TABLE $table_meta (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        meta_type VARCHAR(50) NOT NULL,
        meta_value VARCHAR(190) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_students);
    dbDelta($sql_attendance);
    dbDelta($sql_fees);
    dbDelta($sql_batches);
    dbDelta($sql_teachers);
    dbDelta($sql_holidays);
    dbDelta($sql_notices);
    dbDelta($sql_meta);

    if (!$wpdb->get_var("SELECT COUNT(*) FROM $table_meta WHERE meta_type = 'institution'")) {
        $wpdb->insert($table_meta, ['meta_type' => 'institution', 'meta_value' => 'Shahjalal University of Science & Technology']);
        $wpdb->insert($table_meta, ['meta_type' => 'institution', 'meta_value' => 'Sylhet Engineering College']);
    }

    add_option('ifs_currency_symbol', '৳');
    add_option('ifs_portal_name', 'IFS Academic ERP');
    add_option('ifs_receipt_prefix', 'REC-');
}