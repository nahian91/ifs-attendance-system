<?php
if (!defined('ABSPATH')) exit;

function ifs_erp_render_dashboard_view($wpdb, $table_students, $table_attendance, $table_fees, $table_batches, $available_batches, $currency_symbol, $portal_name, $total_students_all, $today, $base_url) {
    
    // ১. সঠিক টেবিল নেম এবং সরাসরি ডাটাবেজ থেকে মোট স্টুডেন্ট সংখ্যা ফেচ করা
    $target_student_table = !empty($table_students) ? $table_students : $wpdb->prefix . 'ifs_students';
    $total_students_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM $target_student_table");

    // ২. আজকের তারিখ ভ্যালিডেশন (খালি বা ভুল ফরম্যাট হলে বর্তমান তারিখ নিয়ে নিবে)
    $today_date = (!empty($today) && strtotime($today)) ? $today : current_time('Y-m-d');

    // আজকের উপস্থিতির হিসাব
    $today_records = $wpdb->get_results($wpdb->prepare("SELECT student_id, status FROM $table_attendance WHERE attendance_date = %s", $today_date), OBJECT_K);
    $today_p = 0; $today_a = 0; $today_l = 0;
    foreach ($today_records as $rec) {
        if ($rec->status === 'Present') $today_p++;
        elseif ($rec->status === 'Absent') $today_a++;
        elseif ($rec->status === 'Late') $today_l++;
    }
    $recorded_today = count($today_records);
    $today_rate = ($total_students_count > 0 && $recorded_today > 0) ? round(($today_p / $total_students_count) * 100) : 0;
    
    // ফাইন্যান্সিয়াল ও ব্যাচ ডেটা নিশ্চিতকরণ
    $total_collected = (float) $wpdb->get_var("SELECT SUM(paid_amount) FROM $table_fees");
    $total_due       = (float) $wpdb->get_var("SELECT SUM(due_amount) FROM $table_fees");
    $total_batches   = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_batches");

    // বকেয়া রিমাইন্ডার
    $upcoming_reminders = $wpdb->get_results("SELECT student_uid, name, phone, guardian_phone, admission_due, due_reminder_date FROM $target_student_table WHERE admission_due > 0 AND due_reminder_date IS NOT NULL AND due_reminder_date >= '$today_date' ORDER BY due_reminder_date ASC LIMIT 10");

    // আজকের বারের নাম বের করে রুটিন ফিল্টার করা
    $current_day_name = date('D', strtotime($today_date));
    $all_batches = $wpdb->get_results("SELECT * FROM $table_batches");
    $todays_routines = [];
    foreach ($all_batches as $b) {
        $sched = json_decode($b->schedule_json, true) ?: [];
        if (isset($sched[$current_day_name])) {
            $todays_routines[] = [
                'batch_name' => $b->batch_name,
                'start'      => $sched[$current_day_name]['start'] ?? '',
                'end'        => $sched[$current_day_name]['end'] ?? '',
            ];
        }
    }
    
    // সিকিউর কারেন্সি স্ট্রিং নিশ্চিতকরণ
    $currency_str = is_array($currency_symbol) ? '৳' : (string)$currency_symbol;
    ?>
    <!-- ৪-কলামের মডার্ন কেপিআই গ্রিড -->
    <div class="ifs-kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));">
        <div class="ifs-kpi-tile kpi-blue">
            <span class="ifs-kpi-meta">Active Students</span>
            <div class="ifs-kpi-number"><?php echo esc_html($total_students_count); ?></div>
            <span class="ifs-kpi-desc">Total Active Students</span>
        </div>
        <div class="ifs-kpi-tile kpi-green">
            <span class="ifs-kpi-meta">Today's Presence</span>
            <div class="ifs-kpi-number" style="color:#10b981;"><?php echo esc_html($today_rate); ?>%</div>
            <span class="ifs-kpi-desc"><?php echo esc_html($today_p); ?> Present / <?php echo esc_html($today_a); ?> Absent</span>
        </div>
        <div class="ifs-kpi-tile kpi-amber">
            <span class="ifs-kpi-meta">Fees Collected</span>
            <div class="ifs-kpi-number" style="color:#f59e0b;"><?php echo esc_html($currency_str . ' ' . number_format($total_collected, 2)); ?></div>
            <span class="ifs-kpi-desc">Total Collections to Date</span>
        </div>
        <div class="ifs-kpi-tile kpi-red">
            <span class="ifs-kpi-meta">Pending Due</span>
            <div class="ifs-kpi-number" style="color:#ef4444;"><?php echo esc_html($currency_str . ' ' . number_format($total_due, 2)); ?></div>
            <span class="ifs-kpi-desc">Outstanding Receivables</span>
        </div>
    </div>

    <!-- দুই কলাম লেআউট: আজকের রুটিন ও ডিউ রিমাইন্ডার -->
    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start;">
        <!-- আজকের রুটিন উইজেট -->
        <div class="ifs-card" style="display: flex; flex-direction: column;">
            <div class="ifs-card-header" style="margin-bottom: 16px;">
                <div>
                    <h3 class="ifs-card-title">📅 Today's Class Routine</h3>
                    <span style="font-size: 0.8rem; color: #64748b; font-weight: 500;"><?php echo esc_html(date('l, M d, Y', strtotime($today_date))); ?></span>
                </div>
                <span class="ifs-tag" style="background:#e0f2fe; border-color:#bae6fd; color:#0369a1; font-weight: 800;">
                    <?php echo count($todays_routines); ?> Classes
                </span>
            </div>
            
            <?php if (empty($todays_routines)): ?>
                <div style="background:#f8fafc; border:2px dashed #cbd5e1; padding:48px 24px; border-radius:14px; text-align:center; margin: 10px 0;">
                    <p style="margin:0; font-weight:700; color:#64748b; font-size: 0.95rem;">No classes scheduled for today.</p>
                </div>
            <?php else: ?>
                <div class="ifs-custom-scrollbar" style="max-height: 420px; overflow-y: auto; padding-right: 6px; display:flex; flex-direction:column; gap:12px;">
                    <?php foreach ($todays_routines as $tr): ?>
                        <div style="background:#f8fafc; border:1.5px solid #e2e8f0; padding:16px; border-radius:12px; display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <strong style="color: #0f172a; font-size: 1rem;"><?php echo esc_html($tr['batch_name']); ?></strong><br>
                                <span style="font-size:0.78rem; color:#64748b;">Scheduled Session</span>
                            </div>
                            <span class="ifs-tag" style="background:#f0fdf4; border-color:#bbf7d0; color:#15803d; font-size: 0.82rem; padding: 4px 10px; font-family: 'JetBrains Mono', monospace;">
                                🕒 <?php echo esc_html($tr['start']); ?> - <?php echo esc_html($tr['end']); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- বকেয়া রিমাইন্ডার উইজেট -->
        <div class="ifs-card" style="display: flex; flex-direction: column;">
            <div class="ifs-card-header" style="margin-bottom: 16px;">
                <div>
                    <h3 class="ifs-card-title" style="color:#d97706;">⏰ Due Reminders</h3>
                    <span style="font-size:0.8rem; color:#64748b; font-weight: 500;">Scheduled follow-ups</span>
                </div>
                <span class="ifs-tag" style="background:#fef3c7; border-color:#fde68a; color:#b45309; font-weight: 800;">
                    <?php echo count($upcoming_reminders); ?> Pending
                </span>
            </div>
            
            <?php if (empty($upcoming_reminders)): ?>
                <div style="background:#f8fafc; border:1px solid #e2e8f0; padding:48px 16px; border-radius:12px; text-align:center; color:#94a3b8; font-size:0.88rem;">
                    No pending fee reminders scheduled.
                </div>
            <?php else: ?>
                <div class="ifs-custom-scrollbar" style="max-height: 420px; overflow-y: auto; padding-right: 6px; display:flex; flex-direction:column; gap:12px;">
                    <?php foreach ($upcoming_reminders as $rem): ?>
                        <div style="background:#fffbeb; border:1.5px solid #fde68a; padding:14px; border-radius:12px;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                                <div>
                                    <strong style="color: #0f172a; font-size: 0.95rem;"><?php echo esc_html($rem->name); ?></strong> 
                                    <span style="font-family:'JetBrains Mono', monospace; font-size: 0.78rem; color: #64748b;">#<?php echo esc_html($rem->student_uid); ?></span><br>
                                    <span style="font-size:0.82rem; color:#b45309;">Amount: <strong><?php echo esc_html($currency_str . ' ' . number_format((float)$rem->admission_due, 2)); ?></strong></span>
                                </div>
                                <span class="ifs-tag" style="background:#ffffff; border-color:#fde68a; color:#b45309; font-size: 0.72rem;">
                                    <?php echo esc_html(date('M d, Y', strtotime($rem->due_reminder_date))); ?>
                                </span>
                            </div>
                            
                            <?php $notify_num = $rem->guardian_phone ?: $rem->phone; ?>
                            <?php if ($notify_num): ?>
                                <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed rgba(217, 119, 6, 0.2); display: flex; justify-content: flex-end;">
                                    <a href="https://wa.me/88<?php echo esc_attr(preg_replace('/[^0-9]/', '', $notify_num)); ?>?text=<?php echo urlencode('Reminder: Partial fee due of ' . $currency_str . ' ' . $rem->admission_due . ' for ' . $rem->name . ' is scheduled on ' . $rem->due_reminder_date . '. - ' . $portal_name); ?>" target="_blank" class="ifs-btn-wa" style="font-size: 0.76rem; padding: 5px 10px;">
                                        WhatsApp Alert
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}