<?php
if (!defined('ABSPATH')) exit;

// ১. থিম ওভাররাইডার: শর্টকোড যুক্ত পেজে থিমের সমস্ত হেডার/ফুটার/HTML বাইপাস করে সরাসরি ফুলস্ক্রিন লোড করা
add_action('template_redirect', 'ifs_erp_takeover_theme_template');
function ifs_erp_takeover_theme_template() {
    if (is_singular() && !is_admin()) {
        global $post;
        if (isset($post->post_content) && has_shortcode($post->post_content, 'attendance_portal')) {
            ifs_erp_render_standalone_frontend_portal();
            exit;
        }
    }
}

// ২. শর্টকোড ফলব্যাক
add_shortcode('attendance_portal', 'ifs_erp_frontend_master_portal');
function ifs_erp_frontend_master_portal() {
    ob_start();
    ifs_erp_render_standalone_frontend_portal(false);
    return ob_get_clean();
}

// ৩. থিম-মুক্ত স্বতন্ত্র মোবাইল-ফার্স্ট ফ্রন্টএন্ড পোর্টাল
function ifs_erp_render_standalone_frontend_portal($standalone = true) {
    global $wpdb;

    $is_authenticated = !empty($_SESSION['ifs_student_auth']);
    $currency_symbol  = get_option('ifs_currency_symbol', '৳');
    $portal_name      = get_option('ifs_portal_name', 'IFS Academic ERP');

    if ($standalone) {
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
            <title><?php echo esc_html($portal_name); ?> - Student Portal</title>
            <?php ifs_pro_inject_ultimate_styles(); ?>
            <style>
                html, body {
                    margin: 0 !important;
                    padding: 0 !important;
                    width: 100vw !important;
                    min-height: 100vh !important;
                    background: #f8fafc !important;
                    box-sizing: border-box !important;
                    overflow-x: hidden !important;
                }
                .ifs-theme-neutralizer {
                    min-height: 100vh;
                    width: 100vw;
                    background: #f8fafc;
                    display: flex;
                    flex-direction: column;
                }
            </style>
        </head>
        <body>
        <div class="ifs-theme-neutralizer">
        <?php
    } else {
        ifs_pro_inject_ultimate_styles();
        echo '<div class="ifs-fullscreen-wrapper">';
    }

    // মোবাইল রেসপন্সিভ স্টাইলিং
    ?>
    <style>
        .ifs-portal-container {
            flex-grow: 1;
            width: 100%;
            max-width: 1360px;
            margin: 0 auto;
            padding: 24px 20px;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .ifs-portal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            padding: 14px 20px;
            border-radius: 14px;
            border: 1.5px solid #e2e8f0;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }
        .ifs-banner-card {
            background: linear-gradient(135deg, #f0f9ff 0%, #ffffff 55%, #f8fafc 100%);
            border: 1.5px solid #bae6fd;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        .ifs-dashboard-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 22px;
            align-items: start;
        }
        .ifs-table-responsive-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border: 1.5px solid #f1f5f9;
            border-radius: 12px;
        }

        /* মোবাইল স্ক্রিন অপ্টিমাইজেশন (Max 768px) */
        @media (max-width: 768px) {
            .ifs-portal-container {
                padding: 14px 12px;
            }
            .ifs-portal-header {
                padding: 12px 14px;
                border-radius: 12px;
                margin-bottom: 14px;
            }
            .ifs-portal-header h3 {
                font-size: 1rem !important;
            }
            .ifs-banner-card {
                padding: 18px 14px;
                flex-direction: column;
                align-items: flex-start;
                gap: 14px;
                border-radius: 14px;
            }
            .ifs-banner-avatar-group {
                display: flex;
                align-items: center;
                gap: 14px;
                width: 100%;
            }
            .ifs-banner-avatar-group img {
                width: 64px !important;
                height: 64px !important;
            }
            .ifs-dashboard-2col {
                grid-template-columns: 1fr !important;
                gap: 16px !important;
                margin-bottom: 16px !important;
            }
            .ifs-kpi-grid {
                grid-template-columns: 1fr !important;
                gap: 12px !important;
                margin-bottom: 16px !important;
            }
            .ifs-kpi-tile {
                padding: 16px !important;
            }
            .ifs-kpi-number {
                font-size: 1.75rem !important;
            }
            .ifs-card {
                padding: 16px !important;
                margin-bottom: 16px !important;
                border-radius: 12px !important;
            }
            .ifs-login-box {
                padding: 24px 18px !important;
                border-radius: 14px !important;
                margin: 10px auto !important;
            }
        }
    </style>
    <?php

    // ৪. অথেনটিকেশন চেক
    if (!$is_authenticated):
        $num1 = wp_rand(1, 9);
        $num2 = wp_rand(1, 9);
        $_SESSION['ifs_captcha_sum'] = $num1 + $num2;
        $err = isset($_GET['ifs_err']) ? sanitize_text_field($_GET['ifs_err']) : '';
        ?>
        <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 16px;">
            <div class="ifs-login-box" style="width: 100%; max-width: 420px; background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 18px; padding: 36px; box-shadow: 0 4px 25px -4px rgba(0, 0, 0, 0.05); font-family: 'Plus Jakarta Sans', sans-serif;">
                <div style="text-align: center; margin-bottom: 22px;">
                    <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #0284c7, #2563eb); border-radius: 14px; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25);">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <h3 style="margin: 0; font-size: 1.35rem; color: #0f172a; font-weight: 800;"><?php echo esc_html($portal_name); ?></h3>
                    <p style="margin: 4px 0 0; color: #64748b; font-size: 0.82rem;">Student & Parent Access Portal</p>
                </div>

                <?php if ($err === 'invalid_credentials'): ?>
                    <div class="ifs-alert ifs-alert-error" style="font-size:0.82rem; padding: 10px 14px; margin-bottom: 16px;">✕ Incorrect email or password.</div>
                <?php elseif ($err === 'captcha_invalid'): ?>
                    <div class="ifs-alert ifs-alert-error" style="font-size:0.82rem; padding: 10px 14px; margin-bottom: 16px;">✕ Math verification failed.</div>
                <?php endif; ?>

                <form method="POST">
                    <?php wp_nonce_field('ifs_front_login_nonce'); ?>
                    <input type="hidden" name="ifs_frontend_login" value="1">
                    
                    <div class="ifs-field-group" style="margin-bottom: 14px;">
                        <label>Registered Email Address *</label>
                        <input type="email" name="login_email" class="ifs-input" placeholder="student@example.com" required>
                    </div>

                    <div class="ifs-field-group" style="margin-bottom: 14px;">
                        <label>Portal Password *</label>
                        <input type="password" name="login_password" class="ifs-input" placeholder="••••••••" required>
                    </div>

                    <div class="ifs-field-group" style="margin-bottom: 18px;">
                        <label>Security Verification *</label>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <div style="background: #f1f5f9; padding: 9px 14px; border-radius: 8px; font-weight: 800; font-size: 1rem; color: #0284c7; border: 1.5px solid #cbd5e1; user-select: none; font-family: 'JetBrains Mono', monospace; white-space: nowrap;">
                                <?php echo esc_html($num1); ?> + <?php echo esc_html($num2); ?> = ?
                            </div>
                            <input type="number" name="login_captcha" class="ifs-input" placeholder="Result" style="text-align: center; font-weight: 700; font-family: 'JetBrains Mono', monospace;" required>
                        </div>
                    </div>

                    <button type="submit" class="ifs-btn" style="width: 100%; padding: 12px; font-size: 0.92rem;">Sign In to Dashboard</button>
                </form>
            </div>
        </div>
        <?php
        if ($standalone) {
            echo '</div></body></html>';
        } else {
            echo '</div>';
        }
        return;
    endif;

    // ৫. লগইন স্টেট: ফুলস্ক্রিন মোবাইল-ফ্রেন্ডলি ড্যাশবোর্ড
    $auth = $_SESSION['ifs_student_auth'];
    $student_id = intval($auth['id']);
    $student = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ifs_students WHERE id = %d", $student_id));

    $total_classes = (int) $wpdb->get_var("SELECT COUNT(DISTINCT attendance_date) FROM {$wpdb->prefix}ifs_attendance");
    $att_stats = $wpdb->get_results($wpdb->prepare("SELECT status, COUNT(*) as cnt FROM {$wpdb->prefix}ifs_attendance WHERE student_id = %d GROUP BY status", $student_id), OBJECT_K);
    $p_cnt = isset($att_stats['Present']) ? (int)$att_stats['Present']->cnt : 0;
    $a_cnt = isset($att_stats['Absent']) ? (int)$att_stats['Absent']->cnt : 0;
    $l_cnt = isset($att_stats['Late']) ? (int)$att_stats['Late']->cnt : 0;
    $presence_pct = ($total_classes > 0) ? round(($p_cnt / $total_classes) * 100, 1) : 0;

    $total_paid = (float) $wpdb->get_var($wpdb->prepare("SELECT SUM(paid_amount) FROM {$wpdb->prefix}ifs_fees WHERE student_id = %d", $student_id));
    $fee_history = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ifs_fees WHERE student_id = %d ORDER BY payment_date DESC, id DESC", $student_id));
    $recent_att = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ifs_attendance WHERE student_id = %d ORDER BY attendance_date DESC LIMIT 15", $student_id));

    $batch_info = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ifs_batches WHERE batch_name = %s", $student->batch));
    $schedule_data = json_decode($batch_info->schedule_json ?? '', true) ?: [];
    $notices = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ifs_notices WHERE target_batch = 'All' OR target_batch = %s ORDER BY id DESC LIMIT 6", $student->batch));
    ?>

    <div class="ifs-portal-container">
        <!-- টপ হেডার বার -->
        <div class="ifs-portal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div><img src="https://attendance.infinityflamesoft.com/wp-content/uploads/2026/09/logo.png" alt="Logo" style="height: 25px; border-radius: 10px; object-fit: cover;">
                    <h3 style="margin: 0; font-size: 1.1rem; color: #0f172a; font-weight: 800; line-height: 1.2;"><?php echo esc_html($portal_name); ?></h3>
                    <span style="font-size: 0.7rem; color: #64748b; font-weight: 600;">Student Workspace</span>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="ifs-tag" style="font-family:'JetBrains Mono', monospace; font-size:0.75rem;">#<?php echo esc_html($student->student_uid); ?></span>
                <a href="<?php echo esc_url(add_query_arg('ifs_front_logout', '1')); ?>" class="ifs-btn-ghost" style="color: #ef4444; border-color: #fecaca; background: #fef2f2; padding: 5px 10px; font-size: 0.78rem; display: flex; align-items: center; gap: 4px; height: 32px;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <!-- রেসপন্সিভ প্রোফাইল ব্যানার কার্ড -->
        <div class="ifs-banner-card">
            <div class="ifs-banner-avatar-group">
                <img src="<?php echo esc_url($student->photo_url ?: 'https://via.placeholder.com/150'); ?>" style="width:78px; height:78px; border-radius:50%; object-fit:cover; border:3px solid #0284c7; box-shadow: 0 2px 8px rgba(2, 132, 199, 0.15); flex-shrink: 0;">
                <div style="flex-grow:1;">
                    <h2 style="margin:0 0 4px; font-size:1.45rem; color:#0f172a; font-weight:800; line-height: 1.2;"><?php echo esc_html($student->name); ?></h2>
                    <div style="display:flex; gap:6px; flex-wrap:wrap; margin-top: 6px;">
                        <span class="ifs-tag" style="background:#e0f2fe; color:#0369a1; border-color:#bae6fd;">Batch: <?php echo esc_html($student->batch); ?></span>
                        <span class="ifs-badge <?php echo ($student->status === 'active') ? 'badge-present' : 'badge-absent'; ?>" style="padding: 2px 8px; font-size:0.7rem;">
                            <?php echo esc_html(ucfirst($student->status)); ?>
                        </span>
                    </div>
                </div>
            </div>

            <div style="display:flex; gap:6px; flex-wrap:wrap; width:100%;">
                <span class="ifs-tag" style="background:#f1f5f9; color:#475569; border-color:#cbd5e1;">🏛️ <?php echo esc_html($student->institution); ?></span>
                <span class="ifs-tag" style="background:#f1f5f9; color:#475569; border-color:#cbd5e1;">📧 <?php echo esc_html($student->email); ?></span>
            </div>
        </div>

        <!-- কেপিআই কার্ড গ্রিড -->
        <div class="ifs-kpi-grid">
            <div class="ifs-kpi-tile kpi-green">
                <span class="ifs-kpi-meta">Attendance Record</span>
                <div class="ifs-kpi-number" style="color:#10b981;"><?php echo esc_html($presence_pct); ?>%</div>
                <span class="ifs-kpi-desc"><?php echo esc_html($p_cnt); ?> Present / <?php echo esc_html($a_cnt); ?> Absent</span>
            </div>
            <div class="ifs-kpi-tile kpi-blue">
                <span class="ifs-kpi-meta">Verified Payments</span>
                <div class="ifs-kpi-number" style="color:#0284c7;"><?php echo esc_html($currency_symbol) . ' ' . esc_html(number_format($total_paid, 2)); ?></div>
                <span class="ifs-kpi-desc">Total Cleared Fees</span>
            </div>
            <div class="ifs-kpi-tile kpi-red">
                <span class="ifs-kpi-meta">Admission Balance Due</span>
                <div class="ifs-kpi-number" style="color:#ef4444;"><?php echo esc_html($currency_symbol) . ' ' . esc_html(number_format($student->admission_due, 2)); ?></div>
                <span class="ifs-kpi-desc">Due Date: <?php echo $student->due_reminder_date ? esc_html(date('M d, Y', strtotime($student->due_reminder_date))) : 'None'; ?></span>
            </div>
        </div>

        <!-- রুটিন ও নোটিশ সেকশন -->
        <div class="ifs-dashboard-2col">
            <div class="ifs-card" style="display:flex; flex-direction:column;">
                <div class="ifs-card-header" style="margin-bottom: 12px;">
                    <h3 class="ifs-card-title">Class Routine</h3>
                </div>
                <?php if (empty($schedule_data)): ?>
                    <p style="color:#94a3b8; font-size:0.85rem; margin:0;">No routine configured for this batch.</p>
                <?php else: ?>
                    <div class="ifs-custom-scrollbar" style="max-height: 250px; overflow-y: auto; padding-right: 4px; display:flex; flex-direction:column; gap:6px;">
                        <?php foreach ($schedule_data as $day => $times): ?>
                            <div style="display:flex; justify-content:space-between; align-items:center; background:#f8fafc; padding:10px 14px; border-radius:8px; border:1px solid #e2e8f0;">
                                <strong style="color:#0f172a; font-size:0.88rem;"><?php echo esc_html($day); ?></strong>
                                <span class="ifs-tag" style="background:#f0fdf4; border-color:#bbf7d0; color:#15803d; font-size:0.75rem;">
                                    <?php echo esc_html($times['start']); ?> - <?php echo esc_html($times['end']); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="ifs-card" style="display:flex; flex-direction:column;">
                <div class="ifs-card-header" style="margin-bottom: 12px;">
                    <h3 class="ifs-card-title">Notices & Announcements</h3>
                </div>
                <?php if (empty($notices)): ?>
                    <p style="color:#94a3b8; font-size:0.85rem; margin:0;">No active notices published for your batch.</p>
                <?php else: ?>
                    <div class="ifs-custom-scrollbar" style="max-height: 250px; overflow-y: auto; padding-right: 4px; display:flex; flex-direction:column; gap:8px;">
                        <?php foreach ($notices as $not): ?>
                            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px;">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:8px;">
                                    <strong style="color:#0f172a; font-size:0.88rem;"><?php echo esc_html($not->title); ?></strong>
                                    <span style="font-size:0.7rem; color:#64748b; font-family:'JetBrains Mono', monospace; white-space:nowrap;"><?php echo esc_html(date('M d', strtotime($not->created_at))); ?></span>
                                </div>
                                <p style="margin:6px 0 0; font-size:0.82rem; color:#475569; line-height:1.4; white-space:pre-line;">
                                    <?php echo esc_html($not->description); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- উপস্থিতি ও পেমেন্ট হিস্ট্রি টেবিল (মোবাইল হরিজন্টাল স্ক্রল সহ) -->
        <div class="ifs-dashboard-2col">
            <div class="ifs-card" style="display:flex; flex-direction:column;">
                <div class="ifs-card-header" style="margin-bottom: 12px;">
                    <h3 class="ifs-card-title">Recent Attendance</h3>
                </div>
                <div class="ifs-table-responsive-wrapper ifs-custom-scrollbar" style="max-height: 320px; overflow-y: auto;">
                    <table class="ifs-table">
                        <thead style="position:sticky; top:0; z-index:2;">
                            <tr><th>Date</th><th style="text-align:center;">Status</th><th style="text-align:right;">Remarks</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_att as $ra): ?>
                                <tr>
                                    <td style="white-space:nowrap;"><?php echo esc_html(date('M d, Y', strtotime($ra->attendance_date))); ?></td>
                                    <td style="text-align:center;"><span class="ifs-badge badge-<?php echo esc_attr(strtolower($ra->status)); ?>"><?php echo esc_html($ra->status); ?></span></td>
                                    <td style="color:#64748b; font-size:0.8rem; text-align:right; white-space:nowrap;"><?php echo esc_html($ra->remarks ?: '—'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="ifs-card" style="display:flex; flex-direction:column;">
                <div class="ifs-card-header" style="margin-bottom: 12px;">
                    <h3 class="ifs-card-title">Paid Invoices</h3>
                </div>
                <div class="ifs-table-responsive-wrapper ifs-custom-scrollbar" style="max-height: 320px; overflow-y: auto;">
                    <table class="ifs-table">
                        <thead style="position:sticky; top:0; z-index:2;">
                            <tr><th>Receipt</th><th>Title</th><th>Paid</th><th style="text-align:right;">Date</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fee_history as $fh): ?>
                                <tr>
                                    <td><span class="ifs-tag" style="white-space:nowrap;"><?php echo esc_html($fh->receipt_no); ?></span></td>
                                    <td style="white-space:nowrap;"><?php echo esc_html($fh->fee_title); ?></td>
                                    <td style="color:#10b981; font-weight:700; white-space:nowrap;"><?php echo esc_html($currency_symbol) . ' ' . esc_html(number_format($fh->paid_amount, 2)); ?></td>
                                    <td style="text-align:right; color:#64748b; font-size:0.8rem; white-space:nowrap;"><?php echo esc_html(date('M d, Y', strtotime($fh->payment_date))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php
    if ($standalone) {
        echo '</div></body></html>';
    } else {
        echo '</div>';
    }
}