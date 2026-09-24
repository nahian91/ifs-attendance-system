<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('ifs_get_default_avatar')) {
    function ifs_get_default_avatar() {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" fill="none">'
             . '<circle cx="32" cy="32" r="32" fill="#f1f5f9"/>'
             . '<circle cx="32" cy="24" r="11" fill="#94a3b8"/>'
             . '<path d="M12 52c0-9 8-15 20-15s20 6 20 15v2H12v-2z" fill="#94a3b8"/>'
             . '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}

function ifs_erp_render_reports_workspace($wpdb, $table_students, $table_attendance, $available_batches) {
    $month     = isset($_GET['rep_month']) ? sanitize_text_field($_GET['rep_month']) : current_time('Y-m');
    $rep_batch = isset($_GET['rep_batch']) ? sanitize_text_field($_GET['rep_batch']) : '';

    $month_like = $wpdb->esc_like($month) . '%';
    $default_avatar = ifs_get_default_avatar();

    // Fetch active students
    $sql = "SELECT * FROM {$table_students} WHERE status = 'active'";
    if ($rep_batch !== '') {
        $sql .= $wpdb->prepare(" AND batch = %s", $rep_batch);
    }
    $sql .= " ORDER BY student_uid ASC";
    $rep_students = $wpdb->get_results($sql);

    // Total distinct class days in selected month
    $total_days = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT attendance_date) FROM {$table_attendance} WHERE attendance_date LIKE %s", 
        $month_like
    ));

    // Batch aggregate all attendance records in one single query
    $student_ids = !empty($rep_students) ? wp_list_pluck($rep_students, 'id') : [];
    $attendance_matrix = [];

    if (!empty($student_ids)) {
        $id_placeholders = implode(',', array_map('intval', $student_ids));
        $batch_query = $wpdb->prepare(
            "SELECT student_id, status, COUNT(*) as cnt 
             FROM {$table_attendance} 
             WHERE student_id IN ($id_placeholders) AND attendance_date LIKE %s 
             GROUP BY student_id, status",
            $month_like
        );
        $raw_counts = $wpdb->get_results($batch_query);
        foreach ($raw_counts as $row) {
            $attendance_matrix[$row->student_id][$row->status] = (int) $row->cnt;
        }
    }

    $att_export_url = wp_nonce_url(
        add_query_arg([
            'page'       => 'ifs-attendance',
            'ifs_export' => 'attendance',
            'month'      => $month,
            'batch'      => $rep_batch
        ], admin_url('admin.php')),
        'ifs_export_nonce'
    );
    ?>
    <!-- DataTables Assets -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

    <style>
        .ifs-student-avatar {
            width: 36px !important;
            height: 36px !important;
            min-width: 36px !important;
            max-width: 36px !important;
            border-radius: 50% !important;
            object-fit: cover !important;
            border: 1.5px solid #e2e8f0;
            background-color: #f1f5f9;
            display: inline-block;
            vertical-align: middle;
            margin-right: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        /* Clean Modern DataTables Styling */
        .dataTables_wrapper {
            font-family: inherit;
            font-size: 0.85rem;
            color: #334155;
            padding: 8px 0;
        }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 14px;
        }
        .dataTables_wrapper .dataTables_length select {
            padding: 5px 28px 5px 10px !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 8px !important;
            background-color: #ffffff !important;
            font-family: inherit;
            outline: none;
        }
        .dataTables_wrapper .dataTables_filter input {
            padding: 6px 12px !important;
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 8px !important;
            outline: none;
            font-family: inherit;
            background: #ffffff;
            margin-left: 6px;
            width: 220px;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #0284c7 !important;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.12) !important;
        }
        table.dataTable.no-footer {
            border-bottom: 1.5px solid #f1f5f9 !important;
        }
        table.dataTable thead th {
            border-bottom: 1.5px solid #e2e8f0 !important;
            background: #f8fafc !important;
            color: #475569 !important;
            font-weight: 700 !important;
        }
        .dataTables_wrapper .dataTables_paginate {
            margin-top: 14px !important;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 5px 12px !important;
            border-radius: 6px !important;
            border: 1.5px solid #e2e8f0 !important;
            background: #ffffff !important;
            color: #334155 !important;
            font-weight: 600 !important;
            transition: all 0.15s ease;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #0284c7 !important;
            border-color: #0284c7 !important;
            color: #ffffff !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #f1f5f9 !important;
            border-color: #cbd5e1 !important;
            color: #0f172a !important;
        }
    </style>

    <div class="ifs-card" style="display: flex; flex-direction: column;">
        <div class="ifs-card-header" style="margin-bottom: 16px;">
            <div>
                <h3 class="ifs-card-title">Attendance Analytics & Monthly Reports</h3>
                <span style="font-size:0.8rem; color:#64748b; font-weight:500;">Aggregated student attendance metrics and progress indicators</span>
            </div>
            <div style="display:flex; align-items:center; gap:12px;">
                <a href="<?php echo esc_url($att_export_url); ?>" class="ifs-btn-ghost" style="padding: 7px 16px; font-size: 0.82rem;">📥 Export CSV</a>
            </div>
        </div>

        <div class="ifs-toolbar" style="margin-bottom: 16px;">
            <form method="GET" action="<?php echo esc_url(admin_url('admin.php')); ?>" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin:0;">
                <input type="hidden" name="page" value="ifs-attendance">
                <input type="hidden" name="page_view" value="reports">
                
                <label style="font-weight:700; font-size:0.85rem; color:#334155;">Select Month:</label>
                <input type="month" name="rep_month" value="<?php echo esc_attr($month); ?>" onchange="this.form.submit()" style="padding:8px 14px; border-radius:8px; border:1.5px solid #cbd5e1; font-family:inherit; background:#ffffff; color:#0f172a;">

                <label style="font-weight:700; font-size:0.85rem; margin-left:8px; color:#334155;">Batch:</label>
                <select name="rep_batch" onchange="this.form.submit()" style="padding:8px 14px; border-radius:8px; border:1.5px solid #0284c7; font-family:inherit; background:#ffffff; color:#0f172a; font-weight:600;">
                    <option value="">-- All Batches (Combined) --</option>
                    <?php foreach ($available_batches as $b): ?>
                        <option value="<?php echo esc_attr($b); ?>" <?php selected($rep_batch, $b); ?>><?php echo esc_html($b); ?></option>
                    <?php endforeach; ?>
                </select>
            </form>

            <div style="display:flex; align-items:center; gap:8px;">
                <span style="font-size:0.85rem; color:#475569; font-weight:600;">Active Class Days:</span>
                <span class="ifs-tag" style="background:#f0fdf4; border-color:#bbf7d0; color:#15803d; font-size:0.85rem; padding: 4px 10px;">
                    <?php echo esc_html($total_days); ?> Days Held
                </span>
            </div>
        </div>

        <?php if (empty($rep_students)): ?>
            <div style="background:#f8fafc; border:2px dashed #cbd5e1; padding:48px 24px; border-radius:14px; text-align:center; margin:10px 0;">
                <p style="margin:0; font-weight:700; color:#64748b; font-size:0.95rem;">No students found matching this batch and period selection.</p>
            </div>
        <?php else: ?>
            <div style="border: 1.5px solid #f1f5f9; border-radius: 12px; overflow: hidden; padding: 14px; background: #ffffff;">
                <table class="ifs-table stripe hover" id="ifsReportsDataTable" style="width: 100%; margin: 0;">
                    <thead>
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
                            $student_counts = $attendance_matrix[$stu->id] ?? [];
                            $present        = $student_counts['Present'] ?? 0;
                            $absent         = $student_counts['Absent'] ?? 0;
                            $late           = $student_counts['Late'] ?? 0;
                            $rate           = ($total_days > 0) ? round(($present / $total_days) * 100) : 0;
                            $color          = ($rate >= 75) ? '#10b981' : (($rate >= 50) ? '#f59e0b' : '#ef4444');
                            $photo_url      = !empty($stu->photo_url) ? esc_url($stu->photo_url) : $default_avatar;
                            ?>
                            <tr style="transition: background 0.15s ease;">
                                <td>
                                    <div style="display:flex; align-items:center;">
                                        <img src="<?php echo $photo_url; ?>" onerror="this.onerror=null;this.src='<?php echo $default_avatar; ?>';" class="ifs-student-avatar" alt="Avatar">
                                        <div>
                                            <strong style="color:#0f172a; font-size:0.92rem;"><?php echo esc_html($stu->name); ?></strong><br>
                                            <span style="font-size:0.75rem; color:#64748b; font-family:'JetBrains Mono', monospace;">#<?php echo esc_html($stu->student_uid); ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="ifs-tag"><?php echo esc_html($stu->batch); ?></span></td>
                                <td style="text-align:center; color:#10b981; font-weight:800; font-family:'JetBrains Mono', monospace; font-size:0.92rem;" data-order="<?php echo (int)$present; ?>">
                                    <?php echo esc_html($present); ?>
                                </td>
                                <td style="text-align:center; color:#ef4444; font-weight:800; font-family:'JetBrains Mono', monospace; font-size:0.92rem;" data-order="<?php echo (int)$absent; ?>">
                                    <?php echo esc_html($absent); ?>
                                </td>
                                <td style="text-align:center; color:#f59e0b; font-weight:800; font-family:'JetBrains Mono', monospace; font-size:0.92rem;" data-order="<?php echo (int)$late; ?>">
                                    <?php echo esc_html($late); ?>
                                </td>
                                <td data-order="<?php echo (int)$rate; ?>">
                                    <div style="display:flex; align-items:center; gap: 8px;">
                                        <div class="ifs-progress" style="width: 90px; height: 7px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                                            <div class="ifs-progress-bar" style="width: <?php echo esc_attr($rate); ?>%; height: 100%; background: <?php echo esc_attr($color); ?>;"></div>
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

            <script>
            jQuery(document).ready(function($) {
                $('#ifsReportsDataTable').DataTable({
                    pageLength: 25,
                    lengthMenu: [10, 25, 50, 100],
                    order: [[5, 'desc']], // Sorts by Attendance Ratio highest to lowest by default
                    columnDefs: [
                        { type: 'num', targets: [2, 3, 4, 5] }
                    ],
                    language: {
                        search: "_INPUT_",
                        searchPlaceholder: "🔍 Search report...",
                        lengthMenu: "Show _MENU_ students"
                    }
                });
            });
            </script>
        <?php endif; ?>
    </div>
    <?php
}