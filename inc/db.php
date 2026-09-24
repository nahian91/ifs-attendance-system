<?php
/**
 * Database Migration & Schema Installer (Enterprise Pro)
 * 
 * @package IFS_Academic_ERP
 */

if (!defined('ABSPATH')) {
    exit;
}

// Activation hook registration
if (defined('IFS_PLUGIN_FILE')) {
    register_activation_hook(IFS_PLUGIN_FILE, 'ifs_erp_install_database_schema');
}

/**
 * Creates and updates custom database tables using dbDelta.
 */
function ifs_erp_install_database_schema() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();

    // 1. Students Table
    $table_students = $wpdb->prefix . 'ifs_students';
    $sql_students = "CREATE TABLE $table_students (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        student_uid varchar(60) NOT NULL,
        name varchar(150) NOT NULL,
        email varchar(100) NOT NULL,
        password varchar(255) NOT NULL,
        phone varchar(30) DEFAULT '' NOT NULL,
        guardian_phone varchar(30) DEFAULT '' NOT NULL,
        institution varchar(190) NOT NULL,
        batch varchar(80) NOT NULL,
        photo_url text DEFAULT NULL,
        admission_fee decimal(10,2) DEFAULT '0.00' NOT NULL,
        admission_fee_type varchar(20) DEFAULT 'full' NOT NULL,
        admission_paid decimal(10,2) DEFAULT '0.00' NOT NULL,
        admission_due decimal(10,2) DEFAULT '0.00' NOT NULL,
        due_reminder_date date DEFAULT NULL,
        status varchar(20) DEFAULT 'active' NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY student_uid (student_uid),
        UNIQUE KEY student_email (email),
        KEY batch_idx (batch),
        KEY status_idx (status),
        KEY due_reminder_date_idx (due_reminder_date)
    ) $charset_collate;";

    // 2. Attendance Table
    $table_attendance = $wpdb->prefix . 'ifs_attendance';
    $sql_attendance = "CREATE TABLE $table_attendance (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        student_id bigint(20) unsigned NOT NULL,
        attendance_date date NOT NULL,
        status varchar(20) DEFAULT 'Present' NOT NULL,
        remarks varchar(255) DEFAULT '' NOT NULL,
        marked_by bigint(20) unsigned DEFAULT 1 NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY unique_daily_att (student_id,attendance_date),
        KEY date_idx (attendance_date),
        KEY student_id_idx (student_id),
        KEY status_idx (status)
    ) $charset_collate;";

    // 3. Fees Ledger Table
    $table_fees = $wpdb->prefix . 'ifs_fees';
    $sql_fees = "CREATE TABLE $table_fees (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        receipt_no varchar(40) NOT NULL,
        student_id bigint(20) unsigned NOT NULL,
        fee_title varchar(190) NOT NULL,
        total_amount decimal(12,2) DEFAULT '0.00' NOT NULL,
        paid_amount decimal(12,2) DEFAULT '0.00' NOT NULL,
        due_amount decimal(12,2) DEFAULT '0.00' NOT NULL,
        payment_method varchar(50) DEFAULT 'Cash' NOT NULL,
        payment_date date NOT NULL,
        remarks text DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY receipt_no (receipt_no),
        KEY student_fees_idx (student_id),
        KEY payment_date_idx (payment_date)
    ) $charset_collate;";

    // 4. Batches Table
    $table_batches = $wpdb->prefix . 'ifs_batches';
    $sql_batches = "CREATE TABLE $table_batches (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        batch_name varchar(100) NOT NULL,
        course_fee decimal(10,2) DEFAULT '0.00' NOT NULL,
        schedule_json text DEFAULT NULL,
        teacher_id bigint(20) unsigned DEFAULT 0 NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY batch_name (batch_name),
        KEY teacher_id_idx (teacher_id)
    ) $charset_collate;";

    // 5. Teachers Table
    $table_teachers = $wpdb->prefix . 'ifs_teachers';
    $sql_teachers = "CREATE TABLE $table_teachers (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        name varchar(150) NOT NULL,
        designation varchar(100) NOT NULL,
        phone varchar(30) NOT NULL,
        email varchar(100) DEFAULT '' NOT NULL,
        subject varchar(100) DEFAULT '' NOT NULL,
        salary decimal(10,2) DEFAULT '0.00' NOT NULL,
        photo_url text DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        KEY phone_idx (phone)
    ) $charset_collate;";

    // 6. Holidays Table
    $table_holidays = $wpdb->prefix . 'ifs_holidays';
    $sql_holidays = "CREATE TABLE $table_holidays (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        holiday_date date NOT NULL,
        title varchar(150) NOT NULL,
        holiday_type varchar(50) DEFAULT 'Official' NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY holiday_date (holiday_date),
        KEY holiday_type_idx (holiday_type)
    ) $charset_collate;";

    // 7. Notice Board Table
    $table_notices = $wpdb->prefix . 'ifs_notices';
    $sql_notices = "CREATE TABLE $table_notices (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        title varchar(200) NOT NULL,
        description text NOT NULL,
        target_batch varchar(100) DEFAULT 'All' NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        KEY target_batch_idx (target_batch)
    ) $charset_collate;";

    // 8. Meta Settings Table
    $table_meta = $wpdb->prefix . 'ifs_meta';
    $sql_meta = "CREATE TABLE $table_meta (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        meta_type varchar(50) NOT NULL,
        meta_value varchar(190) NOT NULL,
        PRIMARY KEY  (id),
        KEY meta_type_idx (meta_type)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    
    // Execute dbDelta updates
    dbDelta($sql_students);
    dbDelta($sql_attendance);
    dbDelta($sql_fees);
    dbDelta($sql_batches);
    dbDelta($sql_teachers);
    dbDelta($sql_holidays);
    dbDelta($sql_notices);
    dbDelta($sql_meta);

    // Insert initial institutions if none exist
    $has_inst = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_meta WHERE meta_type = %s",
        'institution'
    ));
    
    if (!$has_inst) {
        $wpdb->insert($table_meta, ['meta_type' => 'institution', 'meta_value' => 'Shahjalal University of Science & Technology']);
        $wpdb->insert($table_meta, ['meta_type' => 'institution', 'meta_value' => 'Sylhet Engineering College']);
        $wpdb->insert($table_meta, ['meta_type' => 'institution', 'meta_value' => 'Sylhet Govt College']);
    }

    // Default global options
    add_option('ifs_currency_symbol', '৳');
    add_option('ifs_portal_name', 'IFS Academic ERP');
    add_option('ifs_receipt_prefix', 'REC-');
    
    // Update DB Version
    update_option('ifs_erp_db_version', defined('IFS_VERSION') ? IFS_VERSION : '1.0');
}

/**
 * Automatically check and apply schema updates inside admin
 */
add_action('admin_init', 'ifs_erp_check_database_state');
function ifs_erp_check_database_state() {
    $installed_ver = get_option('ifs_erp_db_version');
    $current_ver   = defined('IFS_VERSION') ? IFS_VERSION : '1.0';

    if ($installed_ver !== $current_ver) {
        ifs_erp_install_database_schema();
    }
}