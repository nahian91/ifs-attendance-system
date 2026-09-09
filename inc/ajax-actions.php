<?php
if (!defined('ABSPATH')) exit;

add_action('init', 'ifs_erp_master_action_dispatcher');
function ifs_erp_master_action_dispatcher() {
    global $wpdb;

    // ১. ফ্রন্টএন্ড লগইন প্রসেস
    if (isset($_POST['ifs_frontend_login']) && wp_verify_nonce($_POST['_wpnonce'], 'ifs_front_login_nonce')) {
        $email      = sanitize_email($_POST['login_email']);
        $password   = $_POST['login_password'];
        $user_cap   = intval($_POST['login_captcha']);
        $actual_cap = isset($_SESSION['ifs_captcha_sum']) ? intval($_SESSION['ifs_captcha_sum']) : -1;

        if ($user_cap !== $actual_cap) {
            wp_redirect(add_query_arg('ifs_err', 'captcha_invalid'));
            exit;
        }

        $student = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ifs_students WHERE email = %s AND status = 'active'", $email));

        if ($student && wp_check_password($password, $student->password)) {
            $_SESSION['ifs_student_auth'] = [
                'id'          => $student->id,
                'uid'         => $student->student_uid,
                'name'        => $student->name,
                'email'       => $student->email,
                'batch'       => $student->batch,
                'institution' => $student->institution
            ];
            wp_redirect(remove_query_arg(['ifs_err']));
            exit;
        } else {
            wp_redirect(add_query_arg('ifs_err', 'invalid_credentials'));
            exit;
        }
    }

    if (isset($_GET['ifs_front_logout'])) {
        unset($_SESSION['ifs_student_auth']);
        wp_redirect(remove_query_arg(['ifs_front_logout']));
        exit;
    }

    if (!is_user_logged_in() || !isset($_REQUEST['ifs_action'])) {
        return;
    }

    $action = sanitize_text_field($_REQUEST['ifs_action']);

    // ২. স্টুডেন্ট যোগ
    if ($action === 'add_student' && check_admin_referer('ifs_stu_nonce')) {
        $uid         = trim(sanitize_text_field($_POST['student_uid']));
        $name        = trim(sanitize_text_field($_POST['student_name']));
        $email       = sanitize_email($_POST['student_email']);
        $raw_pass    = $_POST['student_password'];
        $hashed_pass = wp_hash_password($raw_pass);
        $phone       = trim(sanitize_text_field($_POST['student_phone'] ?? ''));
        $g_phone     = trim(sanitize_text_field($_POST['guardian_phone'] ?? ''));
        $institution = trim(sanitize_text_field($_POST['student_institution']));
        $batch       = trim(sanitize_text_field($_POST['student_batch']));
        $photo_url   = esc_url_raw($_POST['student_photo_url'] ?? '');

        $adm_fee     = floatval($_POST['admission_fee'] ?? 0);
        $fee_type    = sanitize_text_field($_POST['admission_fee_type'] ?? 'full');
        $adm_paid    = floatval($_POST['admission_paid'] ?? 0);
        $adm_due     = max(0, $adm_fee - $adm_paid);
        $rem_date    = !empty($_POST['due_reminder_date']) ? sanitize_text_field($_POST['due_reminder_date']) : null;

        $res = $wpdb->insert(
            $wpdb->prefix . 'ifs_students',
            [
                'student_uid'        => $uid,
                'name'               => $name,
                'email'              => $email,
                'password'           => $hashed_pass,
                'phone'              => $phone,
                'guardian_phone'     => $g_phone,
                'institution'        => $institution,
                'batch'              => $batch,
                'photo_url'          => $photo_url,
                'admission_fee'      => $adm_fee,
                'admission_fee_type' => $fee_type,
                'admission_paid'     => $adm_paid,
                'admission_due'      => $adm_due,
                'due_reminder_date'  => $rem_date
            ]
        );

        if ($res && $adm_paid > 0) {
            $student_id = $wpdb->insert_id;
            $prefix = get_option('ifs_receipt_prefix', 'REC-');
            $receipt_no = $prefix . strtoupper(wp_generate_password(8, false));
            $wpdb->insert($wpdb->prefix . 'ifs_fees', [
                'receipt_no'     => $receipt_no,
                'student_id'     => $student_id,
                'fee_title'      => 'Admission Fee (' . ucfirst($fee_type) . ')',
                'total_amount'   => $adm_fee,
                'paid_amount'    => $adm_paid,
                'due_amount'     => $adm_due,
                'payment_method' => 'Cash',
                'payment_date'   => date('Y-m-d')
            ]);
        }

        wp_redirect(add_query_arg(['ifs_alert' => ($res ? 'stu_added' : 'stu_err'), 'page_view' => 'students', 'sub_tab' => 'all'], wp_get_referer()));
        exit;
    }

    // ৩. স্টুডেন্ট আপডেট
    if ($action === 'edit_student' && check_admin_referer('ifs_edit_stu_nonce')) {
        $student_id  = intval($_POST['student_id']);
        $data_to_update = [
            'student_uid'    => trim(sanitize_text_field($_POST['student_uid'])),
            'name'           => trim(sanitize_text_field($_POST['student_name'])),
            'email'          => sanitize_email($_POST['student_email']),
            'phone'          => trim(sanitize_text_field($_POST['student_phone'] ?? '')),
            'guardian_phone' => trim(sanitize_text_field($_POST['guardian_phone'] ?? '')),
            'institution'    => trim(sanitize_text_field($_POST['student_institution'])),
            'batch'          => trim(sanitize_text_field($_POST['student_batch'])),
            'status'         => trim(sanitize_text_field($_POST['student_status'])),
            'photo_url'      => esc_url_raw($_POST['student_photo_url'] ?? '')
        ];
        if (!empty($_POST['student_password'])) {
            $data_to_update['password'] = wp_hash_password($_POST['student_password']);
        }
        $wpdb->update($wpdb->prefix . 'ifs_students', $data_to_update, ['id' => $student_id]);
        wp_redirect(add_query_arg(['ifs_alert' => 'stu_updated', 'page_view' => 'students', 'sub_tab' => 'all'], wp_get_referer()));
        exit;
    }

    // ৪. স্টুডেন্ট ডিলিট
    if ($action === 'del_student' && check_admin_referer('ifs_del_stu_nonce')) {
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        $id = intval($_GET['student_id']);
        $wpdb->delete($wpdb->prefix . 'ifs_students', ['id' => $id], ['%d']);
        $wpdb->delete($wpdb->prefix . 'ifs_attendance', ['student_id' => $id], ['%d']);
        $wpdb->delete($wpdb->prefix . 'ifs_fees', ['student_id' => $id], ['%d']);
        wp_redirect(add_query_arg(['ifs_alert' => 'stu_deleted', 'page_view' => 'students', 'sub_tab' => 'all'], wp_get_referer()));
        exit;
    }

    // ৫. উপস্থিতি সংরক্ষণ
    if ($action === 'save_attendance' && check_admin_referer('ifs_att_save_nonce')) {
        $att_date  = sanitize_text_field($_POST['attendance_date']);
        $b_filter  = sanitize_text_field($_POST['current_batch_filter'] ?? '');
        $statuses  = isset($_POST['status']) ? (array)$_POST['status'] : [];
        $remarks   = isset($_POST['remarks']) ? (array)$_POST['remarks'] : [];

        foreach ($statuses as $student_id => $st) {
            $student_id = intval($student_id);
            $rem = isset($remarks[$student_id]) ? sanitize_text_field($remarks[$student_id]) : '';
            $wpdb->replace($wpdb->prefix . 'ifs_attendance', [
                'student_id'      => $student_id,
                'attendance_date' => $att_date,
                'status'          => sanitize_text_field($st),
                'remarks'         => $rem,
                'marked_by'       => get_current_user_id()
            ]);
        }
        $args = ['ifs_alert' => 'att_saved', 'filter_date' => $att_date, 'page_view' => 'attendance'];
        if ($b_filter !== '') $args['filter_batch'] = $b_filter;
        wp_redirect(add_query_arg($args, wp_get_referer()));
        exit;
    }

    // ৬. ফি গ্রহণ
    if ($action === 'save_fee' && check_admin_referer('ifs_fee_nonce')) {
        $total_amount = floatval($_POST['total_amount']);
        $paid_amount  = floatval($_POST['paid_amount']);
        $due_amount   = max(0, $total_amount - $paid_amount);
        $receipt_no   = get_option('ifs_receipt_prefix', 'REC-') . strtoupper(wp_generate_password(8, false));

        $wpdb->insert($wpdb->prefix . 'ifs_fees', [
            'receipt_no'     => $receipt_no,
            'student_id'     => intval($_POST['student_id']),
            'fee_title'      => sanitize_text_field($_POST['fee_title']),
            'total_amount'   => $total_amount,
            'paid_amount'    => $paid_amount,
            'due_amount'     => $due_amount,
            'payment_method' => sanitize_text_field($_POST['payment_method']),
            'payment_date'   => sanitize_text_field($_POST['payment_date']),
            'remarks'        => sanitize_textarea_field($_POST['fee_remarks'] ?? '')
        ]);
        wp_redirect(add_query_arg(['ifs_alert' => 'fee_saved', 'page_view' => 'fees', 'sub_tab' => 'all'], wp_get_referer()));
        exit;
    }

    // ৭. ব্যাচ যোগ
    if ($action === 'add_batch_entity' && check_admin_referer('ifs_batch_nonce')) {
        $days = ['Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
        $schedule = [];
        foreach ($days as $day) {
            if (!empty($_POST['day_active'][$day])) {
                $schedule[$day] = [
                    'start' => sanitize_text_field($_POST['day_start'][$day] ?? '10:00 AM'),
                    'end'   => sanitize_text_field($_POST['day_end'][$day] ?? '11:30 AM')
                ];
            }
        }
        $wpdb->insert($wpdb->prefix . 'ifs_batches', [
            'batch_name'    => trim(sanitize_text_field($_POST['batch_name'])),
            'course_fee'    => floatval($_POST['course_fee']),
            'schedule_json' => wp_json_encode($schedule),
            'teacher_id'    => intval($_POST['teacher_id'])
        ]);
        wp_redirect(add_query_arg(['ifs_alert' => 'batch_added', 'page_view' => 'batches'], wp_get_referer()));
        exit;
    }

    if ($action === 'del_batch_entity' && check_admin_referer('ifs_del_batch_nonce')) {
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        $wpdb->delete($wpdb->prefix . 'ifs_batches', ['id' => intval($_GET['batch_id'])], ['%d']);
        wp_redirect(add_query_arg(['ifs_alert' => 'batch_deleted', 'page_view' => 'batches'], wp_get_referer()));
        exit;
    }

    // ৮. শিক্ষক যোগ
    if ($action === 'add_teacher_entity' && check_admin_referer('ifs_teacher_nonce')) {
        $wpdb->insert($wpdb->prefix . 'ifs_teachers', [
            'name'        => trim(sanitize_text_field($_POST['teacher_name'])),
            'designation' => sanitize_text_field($_POST['teacher_designation']),
            'phone'       => sanitize_text_field($_POST['teacher_phone']),
            'salary'      => floatval($_POST['teacher_salary'] ?? 0),
            'photo_url'   => esc_url_raw($_POST['teacher_photo_url'] ?? '')
        ]);
        wp_redirect(add_query_arg(['ifs_alert' => 'teacher_added', 'page_view' => 'teachers'], wp_get_referer()));
        exit;
    }

    if ($action === 'del_teacher_entity' && check_admin_referer('ifs_del_teacher_nonce')) {
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        $wpdb->delete($wpdb->prefix . 'ifs_teachers', ['id' => intval($_GET['teacher_id'])], ['%d']);
        wp_redirect(add_query_arg(['ifs_alert' => 'teacher_deleted', 'page_view' => 'teachers'], wp_get_referer()));
        exit;
    }

    // ৯. ছুটি সংরক্ষণ
    if ($action === 'add_holiday_entity' && check_admin_referer('ifs_holiday_nonce')) {
        $wpdb->replace($wpdb->prefix . 'ifs_holidays', [
            'holiday_date' => sanitize_text_field($_POST['holiday_date']),
            'title'        => sanitize_text_field($_POST['holiday_title']),
            'holiday_type' => sanitize_text_field($_POST['holiday_type'])
        ]);
        wp_redirect(add_query_arg(['ifs_alert' => 'holiday_added', 'page_view' => 'schedule'], wp_get_referer()));
        exit;
    }

    if ($action === 'del_holiday_entity' && check_admin_referer('ifs_del_holiday_nonce')) {
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        $wpdb->delete($wpdb->prefix . 'ifs_holidays', ['id' => intval($_GET['holiday_id'])], ['%d']);
        wp_redirect(add_query_arg(['ifs_alert' => 'holiday_deleted', 'page_view' => 'schedule'], wp_get_referer()));
        exit;
    }

    // ১০. সেটিংস ও প্রেফারেন্স
    if ($action === 'add_setting_meta' && check_admin_referer('ifs_setting_nonce')) {
        $meta_value = trim(sanitize_text_field($_POST['meta_value']));
        if (!empty($meta_value)) {
            $wpdb->insert($wpdb->prefix . 'ifs_meta', ['meta_type' => sanitize_text_field($_POST['meta_type']), 'meta_value' => $meta_value]);
        }
        wp_redirect(add_query_arg(['ifs_alert' => 'meta_added', 'page_view' => 'settings', 'sub_tab' => 'institutions'], wp_get_referer()));
        exit;
    }

    if ($action === 'del_setting_meta' && check_admin_referer('ifs_del_setting_nonce')) {
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        $wpdb->delete($wpdb->prefix . 'ifs_meta', ['id' => intval($_GET['meta_id'])], ['%d']);
        wp_redirect(add_query_arg(['ifs_alert' => 'meta_deleted', 'page_view' => 'settings', 'sub_tab' => 'institutions'], wp_get_referer()));
        exit;
    }

    if ($action === 'save_general_settings' && check_admin_referer('ifs_gen_setting_nonce')) {
        update_option('ifs_currency_symbol', sanitize_text_field($_POST['ifs_currency_symbol']));
        update_option('ifs_portal_name', sanitize_text_field($_POST['ifs_portal_name']));
        update_option('ifs_receipt_prefix', sanitize_text_field($_POST['ifs_receipt_prefix']));
        wp_redirect(add_query_arg(['ifs_alert' => 'settings_saved', 'page_view' => 'settings', 'sub_tab' => 'preferences'], wp_get_referer()));
        exit;
    }
}