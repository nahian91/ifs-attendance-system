<?php
if (!defined('ABSPATH')) exit;

function ifs_erp_render_dashboard_view($wpdb, $table_students, $table_attendance, $table_fees, $table_batches, $table_teachers, $available_batches, $currency_symbol, $portal_name, $total_students_all, $today, $base_url) {
    $today_records = $wpdb->get_results($wpdb->prepare("SELECT student_id, status FROM $table_attendance WHERE attendance_date = %s", $today), OBJECT_K);
    $today_p = 0; $today_a = 0; $today_l = 0;
    foreach ($today_records as $rec) {
        if ($rec->status === 'Present') $today_p++;
        elseif ($rec->status === 'Absent') $today_a++;
        elseif ($rec->status === 'Late') $today_l++;
    }
    $recorded_today = count($today_records);
    $today_rate = ($total_students_all > 0 && $recorded_today > 0) ? round(($today_p / $total_students_all) * 100) : 0;
    $total_collected = (float) $wpdb->get_var("SELECT SUM(paid_amount) FROM $table_fees");
    $total_due       = (float) $wpdb->get_var("SELECT SUM(due_amount) FROM $table_fees");
    $total_batches   = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_batches");
    $total_teachers  = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_teachers");

    $upcoming_reminders = $wpdb->get_results("SELECT student_uid, name, phone, guardian_phone, admission_due, due_reminder_date FROM $table_students WHERE admission_due > 0 AND due_reminder_date IS NOT NULL AND due_reminder_date >= '$today' ORDER BY due_reminder_date ASC LIMIT 5");
    ?>
    <div class="ifs-kpi-grid">
        <div class="ifs-kpi-tile kpi-blue">
            <span class="ifs-kpi-meta">Active Students</span>
            <div class="ifs-kpi-number"><?php echo $total_students_all; ?></div>
            <span class="ifs-kpi-desc">Enrolled in <?php echo $total_batches; ?> Batches</span>
        </div>
        <div class="ifs-kpi-tile kpi-green">
            <span class="ifs-kpi-meta">Today's Presence</span>
            <div class="ifs-kpi-number" style="color:#10b981;"><?php echo $today_rate; ?>%</div>
            <span class="ifs-kpi-desc"><?php echo $today_p; ?> Present / <?php echo $today_a; ?> Absent</span>
        </div>
        <div class="ifs-kpi-tile kpi-amber">
            <span class="ifs-kpi-meta">Fees Collected</span>
            <div class="ifs-kpi-number" style="color:#f59e0b;"><?php echo $currency_symbol . ' ' . number_format($total_collected, 2); ?></div>
            <span class="ifs-kpi-desc">Total Collections to Date</span>
        </div>
        <div class="ifs-kpi-tile kpi-red">
            <span class="ifs-kpi-meta">Pending Due</span>
            <div class="ifs-kpi-number" style="color:#ef4444;"><?php echo $currency_symbol . ' ' . number_format($total_due, 2); ?></div>
            <span class="ifs-kpi-desc">Outstanding Receivables</span>
        </div>
        <div class="ifs-kpi-tile kpi-purple">
            <span class="ifs-kpi-meta">Faculty Staff</span>
            <div class="ifs-kpi-number" style="color:#8b5cf6;"><?php echo $total_teachers; ?></div>
            <span class="ifs-kpi-desc">Instructors</span>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: 2fr 1.2fr; gap: 24px;">
        <div class="ifs-card">
            <div class="ifs-card-header">
                <h3 class="ifs-card-title">Today's Attendance Session</h3>
                <a href="<?php echo esc_url($base_url . '&page_view=attendance'); ?>" class="ifs-btn" style="padding: 8px 16px; font-size: 0.85rem;">Take Attendance</a>
            </div>
            <?php if ($recorded_today === 0): ?>
                <div style="background:#f8fafc; border:2px dashed #cbd5e1; padding:40px; border-radius:12px; text-align:center;">
                    <p style="margin:0 0 12px; font-weight:700; color:#64748b;">Today's attendance has not been recorded yet.</p>
                    <a href="<?php echo esc_url($base_url . '&page_view=attendance'); ?>" class="ifs-btn-ghost">Mark Daily Sheet</a>
                </div>
            <?php else: ?>
                <?php $recent = $wpdb->get_results("SELECT s.*, a.status FROM $table_students s JOIN $table_attendance a ON s.id = a.student_id WHERE a.attendance_date = '$today' LIMIT 6"); ?>
                <table class="ifs-table">
                    <thead><tr><th>Student</th><th>Batch</th><th style="text-align:right;">Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($recent as $r): ?>
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center;">
                                        <img src="<?php echo esc_url($r->photo_url ?: 'https://via.placeholder.com/60'); ?>" class="ifs-avatar-sm">
                                        <div>
                                            <a href="<?php echo esc_url($base_url . '&page_view=profile&student_id=' . $r->id); ?>" style="color:#0f172a; text-decoration:none; font-weight:700;">
                                                <?php echo esc_html($r->name); ?>
                                            </a><br>
                                            <span style="font-size:0.75rem; color:#64748b;">#<?php echo esc_html($r->student_uid); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="ifs-tag"><?php echo esc_html($r->batch); ?></span></td>
                                <td style="text-align:right;"><span class="ifs-badge badge-<?php echo strtolower($r->status); ?>"><?php echo esc_html($r->status); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="ifs-card">
            <h3 class="ifs-card-title" style="color:#d97706;">⏰ Due Reminders</h3>
            <p style="font-size:0.8rem; color:#64748b; margin-top:-14px; margin-bottom:16px;">Scheduled Partial Fee Follow-ups</p>
            <?php if (empty($upcoming_reminders)): ?>
                <p style="color:#94a3b8; font-size:0.85rem;">No due reminders scheduled.</p>
            <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <?php foreach ($upcoming_reminders as $rem): ?>
                        <div style="background:#fffbeb; border:1px solid #fde68a; padding:12px; border-radius:10px;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                                <div>
                                    <strong><?php echo esc_html($rem->name); ?></strong> (#<?php echo esc_html($rem->student_uid); ?>)<br>
                                    <span style="font-size:0.8rem; color:#b45309;">Due: <strong><?php echo $currency_symbol . ' ' . number_format($rem->admission_due, 2); ?></strong></span>
                                </div>
                                <span class="ifs-tag" style="background:#fef3c7; border-color:#fde68a; color:#b45309;"><?php echo date('M d', strtotime($rem->due_reminder_date)); ?></span>
                            </div>
                            <?php $notify_num = $rem->guardian_phone ?: $rem->phone; ?>
                            <?php if ($notify_num): ?>
                                <a href="https://wa.me/88<?php echo esc_attr(preg_replace('/[^0-9]/', '', $notify_num)); ?>?text=<?php echo urlencode('Reminder: Partial fee due of ' . $currency_symbol . ' ' . $rem->admission_due . ' for ' . $rem->name . ' is scheduled on ' . $rem->due_reminder_date . '. - ' . $portal_name); ?>" target="_blank" class="ifs-btn-wa" style="margin-top:8px; display:inline-block; font-size:0.75rem;">
                                    WhatsApp Reminder
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}