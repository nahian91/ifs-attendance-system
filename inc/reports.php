<?php
if (!defined('ABSPATH')) exit;

function ifs_erp_render_reports_workspace($wpdb, $table_students, $table_attendance, $available_batches) {
    $month     = isset($_GET['rep_month']) ? sanitize_text_field($_GET['rep_month']) : date('Y-m');
    $rep_batch = isset($_GET['rep_batch']) ? sanitize_text_field($_GET['rep_batch']) : '';

    $sql = "SELECT * FROM $table_students WHERE status = 'active'";
    if ($rep_batch !== '') $sql .= $wpdb->prepare(" AND batch = %s", $rep_batch);
    $sql .= " ORDER BY student_uid ASC";
    $rep_students = $wpdb->get_results($sql);

    $total_days = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT attendance_date) FROM $table_attendance WHERE attendance_date LIKE %s", $month . '%'));
    $att_export_url = wp_nonce_url(admin_url("admin.php?ifs_export=attendance&month=$month&batch=$rep_batch"), 'ifs_export_nonce');
    ?>
    <div class="ifs-card">
        <div class="ifs-toolbar">
            <form method="GET" action="<?php echo esc_url(admin_url('admin.php')); ?>" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin:0;">
                <input type="hidden" name="page" value="ifs-attendance">
                <input type="hidden" name="page_view" value="reports">
                <label style="font-weight:700; font-size:0.85rem;">Month:</label>
                <input type="month" name="rep_month" value="<?php echo esc_attr($month); ?>" onchange="this.form.submit()" style="padding:8px 14px; border-radius:8px; border:1.5px solid #cbd5e1;">

                <label style="font-weight:700; font-size:0.85rem; margin-left:8px;">Batch:</label>
                <select name="rep_batch" onchange="this.form.submit()" style="padding:8px 14px; border-radius:8px; border:1.5px solid #cbd5e1;">
                    <option value="">-- All Batches --</option>
                    <?php foreach ($available_batches as $b): ?>
                        <option value="<?php echo esc_attr($b); ?>" <?php selected($rep_batch, $b); ?>><?php echo esc_html($b); ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
            <div style="display:flex; align-items:center; gap:16px;">
                <span style="font-weight:700; color:#475569;">Class Days: <strong><?php echo $total_days; ?></strong></span>
                <a href="<?php echo esc_url($att_export_url); ?>" class="ifs-btn-ghost" style="font-weight:700;">📥 Export CSV</a>
            </div>
        </div>

        <?php if (empty($rep_students)): ?>
            <p style="text-align:center; color:#94a3b8; padding:50px;">No records found for this period.</p>
        <?php else: ?>
            <table class="ifs-table">
                <thead>
                    <tr><th>Student ID</th><th>Full Name</th><th>Batch</th><th>Present</th><th>Absent</th><th>Late</th><th>Attendance Rate</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($rep_students as $stu): ?>
                        <?php
                        $counts = $wpdb->get_results($wpdb->prepare("SELECT status, COUNT(*) as cnt FROM $table_attendance WHERE student_id = %d AND attendance_date LIKE %s GROUP BY status", $stu->id, $month . '%'), OBJECT_K);
                        $present = isset($counts['Present']) ? (int)$counts['Present']->cnt : 0;
                        $absent  = isset($counts['Absent']) ? (int)$counts['Absent']->cnt : 0;
                        $late    = isset($counts['Late']) ? (int)$counts['Late']->cnt : 0;
                        $rate = ($total_days > 0) ? round(($present / $total_days) * 100) : 0;
                        $color = ($rate >= 75) ? '#10b981' : (($rate >= 50) ? '#f59e0b' : '#ef4444');
                        ?>
                        <tr>
                            <td><strong>#<?php echo esc_html($stu->student_uid); ?></strong></td>
                            <td style="font-weight:700;"><?php echo esc_html($stu->name); ?></td>
                            <td><span class="ifs-tag"><?php echo esc_html($stu->batch); ?></span></td>
                            <td style="color:#10b981; font-weight:800;"><?php echo $present; ?></td>
                            <td style="color:#ef4444; font-weight:800;"><?php echo $absent; ?></td>
                            <td style="color:#f59e0b; font-weight:800;"><?php echo $late; ?></td>
                            <td>
                                <div class="ifs-progress"><div class="ifs-progress-bar" style="width: <?php echo $rate; ?>%; background: <?php echo $color; ?>;"></div></div>
                                <strong style="color:<?php echo $color; ?>; font-family:'JetBrains Mono', monospace;"><?php echo $rate; ?>%</strong>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php
}