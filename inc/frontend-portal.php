<?php
if (!defined('ABSPATH')) exit;

add_shortcode('attendance_portal', 'ifs_erp_frontend_master_portal');
function ifs_erp_frontend_master_portal() {
    ob_start();
    global $wpdb;
    ifs_pro_inject_ultimate_styles();

    $is_authenticated = !empty($_SESSION['ifs_student_auth']);
    $currency_symbol  = get_option('ifs_currency_symbol', '৳');
    $portal_name      = get_option('ifs_portal_name', 'IFS Academic ERP');

    if (!$is_authenticated):
        $num1 = wp_rand(1, 9);
        $num2 = wp_rand(1, 9);
        $_SESSION['ifs_captcha_sum'] = $num1 + $num2;
        $err = isset($_GET['ifs_err']) ? sanitize_text_field($_GET['ifs_err']) : '';
        ?>
        <div style="max-width: 440px; margin: 40px auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 36px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); font-family: 'Plus Jakarta Sans', sans-serif;">
            <div style="text-align: center; margin-bottom: 26px;">
                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #0284c7, #2563eb); border-radius: 14px; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; color: white;">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h3 style="margin: 0; font-size: 1.4rem; color: #0f172a; font-weight: 800;"><?php echo esc_html($portal_name); ?></h3>
                <p style="margin: 4px 0 0; color: #64748b; font-size: 0.85rem;">Student & Parent Access Portal</p>
            </div>

            <?php if ($err === 'invalid_credentials'): ?>
                <div class="ifs-alert ifs-alert-error" style="font-size:0.85rem;">✕ Incorrect email or password.</div>
            <?php elseif ($err === 'captcha_invalid'): ?>
                <div class="ifs-alert ifs-alert-error" style="font-size:0.85rem;">✕ Captcha verification failed.</div>
            <?php endif; ?>

            <form method="POST">
                <?php wp_nonce_field('ifs_front_login_nonce'); ?>
                <input type="hidden" name="ifs_frontend_login" value="1">
                <div class="ifs-field-group">
                    <label>Registered Email *</label>
                    <input type="email" name="login_email" class="ifs-input" placeholder="student@example.com" required>
                </div>
                <div class="ifs-field-group">
                    <label>Password *</label>
                    <input type="password" name="login_password" class="ifs-input" placeholder="••••••••" required>
                </div>
                <div class="ifs-field-group">
                    <label>Security Verification *</label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <div style="background: #f1f5f9; padding: 10px 16px; border-radius: 8px; font-weight: 800; font-size: 1.1rem; color: #0284c7; letter-spacing: 2px; border: 1.5px solid #cbd5e1; user-select: none;">
                            <?php echo $num1; ?> + <?php echo $num2; ?> = ?
                        </div>
                        <input type="number" name="login_captcha" class="ifs-input" placeholder="Result" style="text-align: center; font-weight: 700;" required>
                    </div>
                </div>
                <button type="submit" class="ifs-btn" style="width: 100%; padding: 12px; margin-top: 10px;">Sign In to Dashboard</button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    endif;

    $auth = $_SESSION['ifs_student_auth'];
    $student_id = intval($auth['id']);
    $student = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ifs_students WHERE id = %d", $student_id));

    $total_classes = (int) $wpdb->get_var("SELECT COUNT(DISTINCT attendance_date) FROM {$wpdb->prefix}ifs_attendance");
    $att_stats = $wpdb->get_results($wpdb->prepare("SELECT status, COUNT(*) as cnt FROM {$wpdb->prefix}ifs_attendance WHERE student_id = %d GROUP BY status", $student_id), OBJECT_K);
    $p_cnt = isset($att_stats['Present']) ? (int)$att_stats['Present']->cnt : 0;
    $presence_pct = ($total_classes > 0) ? round(($p_cnt / $total_classes) * 100, 1) : 0;

    $total_paid = (float) $wpdb->get_var($wpdb->prepare("SELECT SUM(paid_amount) FROM {$wpdb->prefix}ifs_fees WHERE student_id = %d", $student_id));
    $fee_history = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ifs_fees WHERE student_id = %d ORDER BY payment_date DESC", $student_id));
    $recent_att = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ifs_attendance WHERE student_id = %d ORDER BY attendance_date DESC LIMIT 8", $student_id));

    $batch_info = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ifs_batches WHERE batch_name = %s", $student->batch));
    $schedule_data = json_decode($batch_info->schedule_json ?? '', true) ?: [];
    $notices = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}ifs_notices WHERE target_batch = 'All' OR target_batch = %s ORDER BY id DESC LIMIT 5", $student->batch));
    ?>
    <div style="max-width: 1200px; margin: 30px auto; font-family: 'Plus Jakarta Sans', sans-serif;">
        <div style="display: flex; justify-content: space-between; align-items: center; background: #ffffff; padding: 20px 28px; border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 24px;">
            <div>
                <span style="font-size: 0.8rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Logged in Student</span>
                <h3 style="margin: 2px 0 0; font-size: 1.25rem; color: #0f172a; font-weight: 800;"><?php echo esc_html($student->name); ?></h3>
            </div>
            <a href="<?php echo esc_url(add_query_arg('ifs_front_logout', '1')); ?>" class="ifs-btn-ghost" style="color: #ef4444; border-color: #fecaca;">Log Out</a>
        </div>

        <div class="ifs-card" style="background: linear-gradient(135deg, #090d16 0%, #1e293b 100%); color:white; border:none; padding:32px;">
            <div style="display:flex; align-items:center; gap:24px; flex-wrap:wrap;">
                <img src="<?php echo esc_url($student->photo_url ?: 'https://via.placeholder.com/150'); ?>" style="width:90px; height:90px; border-radius:50%; object-fit:cover; border:3px solid #38bdf8;">
                <div>
                    <h2 style="margin:0 0 6px; font-size:1.7rem; color:white;"><?php echo esc_html($student->name); ?></h2>
                    <span class="ifs-tag" style="background:rgba(255,255,255,0.15); color:#e2e8f0; border:none;"><?php echo esc_html($student->batch); ?></span>
                    <span class="ifs-tag" style="background:rgba(255,255,255,0.15); color:#e2e8f0; border:none;"><?php echo esc_html($student->institution); ?></span>
                </div>
            </div>
        </div>

        <div class="ifs-kpi-grid">
            <div class="ifs-kpi-tile kpi-green">
                <span class="ifs-kpi-meta">Attendance Percentage</span>
                <div class="ifs-kpi-number" style="color:#10b981;"><?php echo $presence_pct; ?>%</div>
                <span class="ifs-kpi-desc"><?php echo $p_cnt; ?> Days Attended of <?php echo $total_classes; ?> Classes</span>
            </div>
            <div class="ifs-kpi-tile kpi-blue">
                <span class="ifs-kpi-meta">Total Fees Paid</span>
                <div class="ifs-kpi-number" style="color:#0284c7;"><?php echo $currency_symbol . ' ' . number_format($total_paid, 2); ?></div>
                <span class="ifs-kpi-desc">Verified Invoices</span>
            </div>
            <div class="ifs-kpi-tile kpi-red">
                <span class="ifs-kpi-meta">Admission Balance Due</span>
                <div class="ifs-kpi-number" style="color:#ef4444;"><?php echo $currency_symbol . ' ' . number_format($student->admission_due, 2); ?></div>
                <span class="ifs-kpi-desc">Reminder Date: <?php echo $student->due_reminder_date ? date('M d, Y', strtotime($student->due_reminder_date)) : 'None'; ?></span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
            <div class="ifs-card">
                <h3 class="ifs-card-title">My Class Schedule</h3>
                <?php if (empty($schedule_data)): ?>
                    <p style="color:#94a3b8; font-size:0.9rem;">No routine registered.</p>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:8px; margin-top:14px;">
                        <?php foreach ($schedule_data as $day => $times): ?>
                            <div style="display:flex; justify-content:space-between; background:#f8fafc; padding:10px 14px; border-radius:8px; border:1px solid #e2e8f0;">
                                <strong><?php echo esc_html($day); ?></strong>
                                <span class="ifs-tag"><?php echo esc_html($times['start']); ?> - <?php echo esc_html($times['end']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="ifs-card">
                <h3 class="ifs-card-title">Notice Feed</h3>
                <?php if (empty($notices)): ?>
                    <p style="color:#94a3b8; font-size:0.9rem;">No notices.</p>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:12px; margin-top:14px;">
                        <?php foreach ($notices as $not): ?>
                            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:12px;">
                                <div style="display:flex; justify-content:space-between;">
                                    <strong><?php echo esc_html($not->title); ?></strong>
                                    <span style="font-size:0.75rem; color:#64748b;"><?php echo date('M d', strtotime($not->created_at)); ?></span>
                                </div>
                                <p style="margin:6px 0 0; font-size:0.85rem; color:#475569;"><?php echo nl2br(esc_html($not->description)); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}