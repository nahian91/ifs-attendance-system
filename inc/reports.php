<?php
if (!defined('ABSPATH')) exit;

function ifs_erp_render_reports_workspace($wpdb, $table_students, $table_attendance, $available_batches) {
    $month     = isset($_GET['rep_month']) ? sanitize_text_field($_GET['rep_month']) : date('Y-m');
    $rep_batch = isset($_GET['rep_batch']) ? sanitize_text_field($_GET['rep_batch']) : '';

    $sql = "SELECT * FROM $table_students WHERE status = 'active'";
    if ($rep_batch !== '') {
        $sql .= $wpdb->prepare(" AND batch = %s", $rep_batch);
    }
    $sql .= " ORDER BY student_uid ASC";
    $rep_students = $wpdb->get_results($sql);

    $total_days = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT attendance_date) FROM $table_attendance WHERE attendance_date LIKE %s", 
        $month . '%'
    ));
    $att_export_url = wp_nonce_url(admin_url("admin.php?ifs_export=attendance&month=$month&batch=$rep_batch"), 'ifs_export_nonce');
    ?>
    <div class="ifs-card" style="display: flex; flex-direction: column;">
        <div class="ifs-card-header" style="margin-bottom: 16px;">
            <div>
                <h3 class="ifs-card-title">Attendance Analytics & Monthly Reports</h3>
                <span style="font-size:0.8rem; color:#64748b; font-weight:500;">Aggregated student attendance metrics and progress indicators</span>
            </div>
            <div style="display:flex; align-items:center; gap:12px;">
                <input type="text" id="reportSearchInput" placeholder="🔍 Search reports..." onkeyup="filterReportTable()" style="padding: 6px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.82rem; outline: none; font-family: inherit;">
                <a href="<?php echo esc_url($att_export_url); ?>" class="ifs-btn-ghost" style="padding: 6px 14px; font-size: 0.82rem;">📥 Export CSV</a>
            </div>
        </div>

        <div class="ifs-toolbar">
            <form method="GET" action="<?php echo esc_url(admin_url('admin.php')); ?>" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin:0;">
                <input type="hidden" name="page" value="ifs-attendance">
                <input type="hidden" name="page_view" value="reports">
                
                <label style="font-weight:700; font-size:0.85rem; color:#334155;">Select Month:</label>
                <input type="month" name="rep_month" value="<?php echo esc_attr($month); ?>" onchange="this.form.submit()" style="padding:8px 14px; border-radius:8px; border:1.5px solid #cbd5e1; font-family:inherit;">

                <label style="font-weight:700; font-size:0.85rem; margin-left:8px; color:#334155;">Batch:</label>
                <select name="rep_batch" onchange="this.form.submit()" style="padding:8px 14px; border-radius:8px; border:1.5px solid #cbd5e1; font-family:inherit;">
                    <option value="">-- All Batches --</option>
                    <?php foreach ($available_batches as $b): ?>
                        <option value="<?php echo esc_attr($b); ?>" <?php selected($rep_batch, $b); ?>><?php echo esc_html($b); ?></option>
                    <?php endforeach; ?>
                </select>
            </form>

            <div style="display:flex; align-items:center; gap:8px;">
                <span style="font-size:0.85rem; color:#475569; font-weight:600;">Total Active Class Days:</span>
                <span class="ifs-tag" style="background:#f0fdf4; border-color:#bbf7d0; color:#15803d; font-size:0.85rem; padding: 4px 10px;">
                    <?php echo esc_html($total_days); ?> Days
                </span>
            </div>
        </div>

        <?php if (empty($rep_students)): ?>
            <div style="background:#f8fafc; border:2px dashed #cbd5e1; padding:48px 24px; border-radius:14px; text-align:center; margin:10px 0;">
                <p style="margin:0; font-weight:700; color:#64748b; font-size:0.95rem;">No attendance records found for this period and batch selection.</p>
            </div>
        <?php else: ?>
            <div class="ifs-custom-scrollbar" style="max-height: 540px; overflow-y: auto; padding-right: 6px; border: 1px solid #f1f5f9; border-radius: 12px;">
                <table class="ifs-table" id="reportDirectoryTable">
                    <thead style="position: sticky; top: 0; z-index: 5; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                        <tr>
                            <th style="background:#f8fafc; padding: 14px 16px;">Student</th>
                            <th style="background:#f8fafc; padding: 14px 16px;">Batch</th>
                            <th style="background:#f8fafc; text-align:center; padding: 14px 16px;">Present</th>
                            <th style="background:#f8fafc; text-align:center; padding: 14px 16px;">Absent</th>
                            <th style="background:#f8fafc; text-align:center; padding: 14px 16px;">Late</th>
                            <th style="background:#f8fafc; padding: 14px 16px;">Attendance Ratio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rep_students as $stu): ?>
                            <?php
                            $counts = $wpdb->get_results($wpdb->prepare(
                                "SELECT status, COUNT(*) as cnt FROM $table_attendance WHERE student_id = %d AND attendance_date LIKE %s GROUP BY status", 
                                $stu->id, 
                                $month . '%'
                            ), OBJECT_K);
                            
                            $present = isset($counts['Present']) ? (int)$counts['Present']->cnt : 0;
                            $absent  = isset($counts['Absent']) ? (int)$counts['Absent']->cnt : 0;
                            $late    = isset($counts['Late']) ? (int)$counts['Late']->cnt : 0;
                            $rate    = ($total_days > 0) ? round(($present / $total_days) * 100) : 0;
                            $color   = ($rate >= 75) ? '#10b981' : (($rate >= 50) ? '#f59e0b' : '#ef4444');
                            ?>
                            <tr style="transition: background 0.15s ease;">
                                <td>
                                    <strong style="color:#0f172a; font-size:0.92rem;"><?php echo esc_html($stu->name); ?></strong><br>
                                    <span style="font-size:0.75rem; color:#64748b; font-family:'JetBrains Mono', monospace;">#<?php echo esc_html($stu->student_uid); ?></span>
                                </td>
                                <td><span class="ifs-tag"><?php echo esc_html($stu->batch); ?></span></td>
                                <td style="text-align:center; color:#10b981; font-weight:800; font-family:'JetBrains Mono', monospace; font-size:0.92rem;">
                                    <?php echo esc_html($present); ?>
                                </td>
                                <td style="text-align:center; color:#ef4444; font-weight:800; font-family:'JetBrains Mono', monospace; font-size:0.92rem;">
                                    <?php echo esc_html($absent); ?>
                                </td>
                                <td style="text-align:center; color:#f59e0b; font-weight:800; font-family:'JetBrains Mono', monospace; font-size:0.92rem;">
                                    <?php echo esc_html($late); ?>
                                </td>
                                <td>
                                    <div style="display:flex; align-items:center;">
                                        <div class="ifs-progress" style="width: 100px; height: 7px;">
                                            <div class="ifs-progress-bar" style="width: <?php echo esc_attr($rate); ?>%; background: <?php echo esc_attr($color); ?>;"></div>
                                        </div>
                                        <strong style="color:<?php echo esc_attr($color); ?>; font-family:'JetBrains Mono', monospace; font-size:0.85rem;">
                                            <?php echo esc_html($rate); ?>%
                                        </strong>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function filterReportTable() {
            const input = document.getElementById('reportSearchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('reportDirectoryTable');
            if (!table) return;
            const tr = table.getElementsByTagName('tr');
            for (let i = 1; i < tr.length; i++) {
                let tdStudent = tr[i].getElementsByTagName('td')[0];
                let tdBatch   = tr[i].getElementsByTagName('td')[1];
                if (tdStudent || tdBatch) {
                    let txtStudent = tdStudent ? (tdStudent.textContent || tdStudent.innerText) : '';
                    let txtBatch   = tdBatch ? (tdBatch.textContent || tdBatch.innerText) : '';
                    if (txtStudent.toLowerCase().indexOf(filter) > -1 || txtBatch.toLowerCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>
    <?php
}