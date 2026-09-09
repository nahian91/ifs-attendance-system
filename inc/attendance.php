<?php
if (!defined('ABSPATH')) exit;

function ifs_erp_render_attendance_view($wpdb, $table_students, $table_attendance, $available_batches, $portal_name, $base_url) {
    $sel_date  = isset($_GET['filter_date']) ? sanitize_text_field($_GET['filter_date']) : date('Y-m-d');
    $sel_batch = isset($_GET['filter_batch']) ? sanitize_text_field($_GET['filter_batch']) : '';

    $sql = "SELECT * FROM $table_students WHERE status = 'active'";
    if ($sel_batch !== '') $sql .= $wpdb->prepare(" AND batch = %s", $sel_batch);
    $sql .= " ORDER BY student_uid ASC";
    $students = $wpdb->get_results($sql);

    $att_records = $wpdb->get_results($wpdb->prepare("SELECT student_id, status, remarks FROM $table_attendance WHERE attendance_date = %s", $sel_date), OBJECT_K);

    $p = 0; $a = 0; $l = 0;
    foreach ($students as $stu) {
        $st = isset($att_records[$stu->id]) ? $att_records[$stu->id]->status : 'Present';
        if ($st === 'Present') $p++;
        elseif ($st === 'Absent') $a++;
        elseif ($st === 'Late') $l++;
    }
    ?>
    <div class="ifs-card">
        <div class="ifs-pills-row">
            <div class="ifs-status-counter sc-p"><div class="sc-count"><?php echo $p; ?></div><div class="sc-lbl">Present</div></div>
            <div class="ifs-status-counter sc-a"><div class="sc-count"><?php echo $a; ?></div><div class="sc-lbl">Absent</div></div>
            <div class="ifs-status-counter sc-l"><div class="sc-count"><?php echo $l; ?></div><div class="sc-lbl">Late</div></div>
        </div>

        <div class="ifs-toolbar">
            <form method="GET" action="<?php echo esc_url(admin_url('admin.php')); ?>" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin:0;">
                <input type="hidden" name="page" value="ifs-attendance">
                <input type="hidden" name="page_view" value="attendance">
                <label style="font-weight:700; font-size:0.85rem;">Date:</label>
                <input type="date" name="filter_date" value="<?php echo esc_attr($sel_date); ?>" onchange="this.form.submit()" style="padding:8px 14px; border-radius:8px; border:1.5px solid #cbd5e1;">

                <label style="font-weight:700; font-size:0.85rem; margin-left:8px;">Batch:</label>
                <select name="filter_batch" onchange="this.form.submit()" style="padding:8px 14px; border-radius:8px; border:1.5px solid #cbd5e1;">
                    <option value="">-- All Batches --</option>
                    <?php foreach ($available_batches as $b): ?>
                        <option value="<?php echo esc_attr($b); ?>" <?php selected($sel_batch, $b); ?>><?php echo esc_html($b); ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
            <div style="display:flex; gap:8px;">
                <button type="button" class="ifs-btn-ghost" onclick="setAll('Present')">All Present</button>
                <button type="button" class="ifs-btn-ghost" onclick="setAll('Absent')">All Absent</button>
            </div>
        </div>

        <?php if (empty($students)): ?>
            <p style="text-align:center; color:#94a3b8; padding:50px;">No students found for this batch.</p>
        <?php else: ?>
            <form method="POST">
                <?php wp_nonce_field('ifs_att_save_nonce'); ?>
                <input type="hidden" name="ifs_action" value="save_attendance">
                <input type="hidden" name="attendance_date" value="<?php echo esc_attr($sel_date); ?>">
                <input type="hidden" name="current_batch_filter" value="<?php echo esc_attr($sel_batch); ?>">

                <table class="ifs-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Batch</th>
                            <th style="text-align:right;">Status</th>
                            <th style="text-align:right;">Alert Parent</th>
                            <th style="text-align:right;">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $stu): ?>
                            <?php 
                            $status = isset($att_records[$stu->id]) ? $att_records[$stu->id]->status : 'Present'; 
                            $rem    = isset($att_records[$stu->id]) ? $att_records[$stu->id]->remarks : '';
                            $phone_to_notify = !empty($stu->guardian_phone) ? $stu->guardian_phone : $stu->phone;
                            ?>
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center;">
                                        <img src="<?php echo esc_url($stu->photo_url ?: 'https://via.placeholder.com/60'); ?>" class="ifs-avatar-sm">
                                        <div>
                                            <a href="<?php echo esc_url($base_url . '&page_view=profile&student_id=' . $stu->id); ?>" style="color:#0f172a; text-decoration:none; font-weight:700;">
                                                <?php echo esc_html($stu->name); ?>
                                            </a><br>
                                            <span style="font-size:0.75rem; color:#64748b;">#<?php echo esc_html($stu->student_uid); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="ifs-tag"><?php echo esc_html($stu->batch); ?></span></td>
                                <td style="text-align:right;">
                                    <div class="ifs-status-pill-group">
                                        <label><input type="radio" class="att-radio" name="status[<?php echo esc_attr($stu->id); ?>]" value="Present" <?php checked($status, 'Present'); ?>><span>Present</span></label>
                                        <label><input type="radio" class="att-radio" name="status[<?php echo esc_attr($stu->id); ?>]" value="Absent" <?php checked($status, 'Absent'); ?>><span>Absent</span></label>
                                        <label><input type="radio" class="att-radio" name="status[<?php echo esc_attr($stu->id); ?>]" value="Late" <?php checked($status, 'Late'); ?>><span>Late</span></label>
                                    </div>
                                </td>
                                <td style="text-align:right;">
                                    <?php if (!empty($phone_to_notify)): ?>
                                        <a href="https://wa.me/88<?php echo esc_attr(preg_replace('/[^0-9]/', '', $phone_to_notify)); ?>?text=<?php echo urlencode('Notice: ' . $stu->name . ' was absent on ' . $sel_date . '. - ' . $portal_name); ?>" target="_blank" class="ifs-btn-wa">WhatsApp</a>
                                    <?php else: ?>
                                        <span style="color:#cbd5e1; font-size:0.75rem;">No Phone</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:right;">
                                    <input type="text" name="remarks[<?php echo esc_attr($stu->id); ?>]" value="<?php echo esc_attr($rem); ?>" placeholder="Leave, Sick..." style="width: 130px; padding: 6px 10px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.82rem;">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="margin-top: 24px;">
                    <button type="submit" class="ifs-btn">Save Attendance Records</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
    <script>
        function setAll(status) {
            document.querySelectorAll('.att-radio[value="' + status + '"]').forEach(r => r.checked = true);
        }
    </script>
    <?php
}