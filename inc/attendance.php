<?php
if (!defined('ABSPATH')) exit;

function ifs_erp_render_attendance_view($wpdb, $table_students, $table_attendance, $available_batches, $portal_name, $base_url) {
    $sel_date  = isset($_GET['filter_date']) ? sanitize_text_field($_GET['filter_date']) : date('Y-m-d');
    $sel_batch = isset($_GET['filter_batch']) ? sanitize_text_field($_GET['filter_batch']) : '';

    $sql = "SELECT * FROM $table_students WHERE status = 'active'";
    if ($sel_batch !== '') {
        $sql .= $wpdb->prepare(" AND batch = %s", $sel_batch);
    }
    $sql .= " ORDER BY student_uid ASC";
    $students = $wpdb->get_results($sql);

    $att_records = $wpdb->get_results($wpdb->prepare(
        "SELECT student_id, status, remarks FROM $table_attendance WHERE attendance_date = %s", 
        $sel_date
    ), OBJECT_K);

    $p = 0; $a = 0; $l = 0;
    foreach ($students as $stu) {
        $st = isset($att_records[$stu->id]) ? $att_records[$stu->id]->status : 'Present';
        if ($st === 'Present') $p++;
        elseif ($st === 'Absent') $a++;
        elseif ($st === 'Late') $l++;
    }
    ?>
    <style>
        /* কাউন্টার পিলস গ্রিড ও স্টাইল */
        .ifs-pills-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 22px;
        }
        .ifs-status-counter {
            padding: 16px 20px;
            border-radius: 12px;
            text-align: center;
            border: 1.5px solid transparent;
            transition: all 0.2s ease;
        }
        .sc-p {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #15803d;
        }
        .sc-a {
            background: #fef2f2;
            border-color: #fecaca;
            color: #b91c1c;
        }
        .sc-l {
            background: #fffbeb;
            border-color: #fde68a;
            color: #b45309;
        }
        .sc-count {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 4px;
            font-family: 'JetBrains Mono', monospace;
        }
        .sc-lbl {
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        /* ফিল্টার টুলবার */
        .ifs-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
            background: #f8fafc;
            padding: 14px 18px;
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            margin-bottom: 20px;
        }

        /* ইন্টারঅ্যাক্টিভ রেডিও পিল গ্রুপ */
        .ifs-status-pill-group {
            display: inline-flex;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 10px;
            gap: 4px;
            border: 1.5px solid #e2e8f0;
        }
        .ifs-status-pill-group label {
            padding: 6px 12px;
            font-size: 0.8rem;
            font-weight: 700;
            border-radius: 7px;
            cursor: pointer;
            margin: 0;
            user-select: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
        }
        .ifs-status-pill-group input {
            display: none;
        }
        .ifs-status-pill-group label span {
            display: inline-block;
            padding: 2px 4px;
        }
        .ifs-status-pill-group input[value="Present"]:checked + span {
            background: #10b981;
            color: #ffffff;
            border-radius: 6px;
            padding: 4px 10px;
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.35);
        }
        .ifs-status-pill-group input[value="Absent"]:checked + span {
            background: #ef4444;
            color: #ffffff;
            border-radius: 6px;
            padding: 4px 10px;
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.35);
        }
        .ifs-status-pill-group input[value="Late"]:checked + span {
            background: #f59e0b;
            color: #ffffff;
            border-radius: 6px;
            padding: 4px 10px;
            box-shadow: 0 2px 6px rgba(245, 158, 11, 0.35);
        }
    </style>

    <div class="ifs-card" style="display: flex; flex-direction: column;">
        <!-- কাউন্টার পিলস -->
        <div class="ifs-pills-row">
            <div class="ifs-status-counter sc-p">
                <div class="sc-count" id="count-present"><?php echo esc_html($p); ?></div>
                <div class="sc-lbl">Present</div>
            </div>
            <div class="ifs-status-counter sc-a">
                <div class="sc-count" id="count-absent"><?php echo esc_html($a); ?></div>
                <div class="sc-lbl">Absent</div>
            </div>
            <div class="ifs-status-counter sc-l">
                <div class="sc-count" id="count-late"><?php echo esc_html($l); ?></div>
                <div class="sc-lbl">Late</div>
            </div>
        </div>

        <!-- ফিল্টার টুলবার -->
        <div class="ifs-toolbar">
            <form method="GET" action="<?php echo esc_url(admin_url('admin.php')); ?>" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin:0;">
                <input type="hidden" name="page" value="ifs-attendance">
                <input type="hidden" name="page_view" value="attendance">
                
                <label style="font-weight:700; font-size:0.85rem; color:#334155;">Date:</label>
                <input type="date" name="filter_date" value="<?php echo esc_attr($sel_date); ?>" onchange="this.form.submit()" style="padding:8px 14px; border-radius:8px; border:1.5px solid #cbd5e1; font-family:inherit; background:#ffffff; color:#0f172a;">

                <label style="font-weight:700; font-size:0.85rem; margin-left:8px; color:#334155;">Batch:</label>
                <select name="filter_batch" onchange="this.form.submit()" style="padding:8px 14px; border-radius:8px; border:1.5px solid #cbd5e1; font-family:inherit; background:#ffffff; color:#0f172a;">
                    <option value="">-- All Batches --</option>
                    <?php foreach ($available_batches as $b): ?>
                        <option value="<?php echo esc_attr($b); ?>" <?php selected($sel_batch, $b); ?>><?php echo esc_html($b); ?></option>
                    <?php endforeach; ?>
                </select>
            </form>

            <div style="display:flex; gap:8px; align-items:center;">
                <button type="button" class="ifs-btn-ghost" onclick="setAll('Present')">Mark All Present</button>
                <button type="button" class="ifs-btn-ghost" onclick="setAll('Absent')">Mark All Absent</button>
            </div>
        </div>

        <?php if (empty($students)): ?>
            <div style="background:#f8fafc; border:2px dashed #cbd5e1; padding:48px 24px; border-radius:14px; text-align:center; margin: 10px 0;">
                <p style="margin:0; font-weight:700; color:#64748b; font-size: 0.95rem;">No active students enrolled in this batch.</p>
            </div>
        <?php else: ?>
            <form method="POST">
                <?php wp_nonce_field('ifs_att_save_nonce'); ?>
                <input type="hidden" name="ifs_action" value="save_attendance">
                <input type="hidden" name="attendance_date" value="<?php echo esc_attr($sel_date); ?>">
                <input type="hidden" name="current_batch_filter" value="<?php echo esc_attr($sel_batch); ?>">

                <!-- স্টিকি হেডার ও স্ক্রলবার টেবিল কন্টেইনার -->
                <div class="ifs-custom-scrollbar" style="max-height: 520px; overflow-y: auto; padding-right: 6px; border: 1.5px solid #f1f5f9; border-radius: 12px;">
                    <table class="ifs-table">
                        <thead style="position: sticky; top: 0; z-index: 5; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                            <tr>
                                <th style="background:#f8fafc; padding: 14px 16px;">Student</th>
                                <th style="background:#f8fafc; padding: 14px 16px;">Batch</th>
                                <th style="background:#f8fafc; text-align:center; padding: 14px 16px;">Status</th>
                                <th style="background:#f8fafc; text-align:center; padding: 14px 16px;">Notify</th>
                                <th style="background:#f8fafc; text-align:right; padding: 14px 16px;">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $stu): ?>
                                <?php 
                                $status = isset($att_records[$stu->id]) ? $att_records[$stu->id]->status : 'Present'; 
                                $rem    = isset($att_records[$stu->id]) ? $att_records[$stu->id]->remarks : '';
                                $phone_to_notify = !empty($stu->guardian_phone) ? $stu->guardian_phone : $stu->phone;
                                ?>
                                <tr style="transition: background 0.15s ease;">
                                    <td>
                                        <div style="display:flex; align-items:center;">
                                            <img src="<?php echo esc_url($stu->photo_url ?: 'https://via.placeholder.com/60'); ?>" class="ifs-avatar-sm" style="box-shadow: 0 2px 5px rgba(0,0,0,0.06);">
                                            <div>
                                                <a href="<?php echo esc_url($base_url . '&page_view=profile&student_id=' . $stu->id); ?>" style="color:#0f172a; text-decoration:none; font-weight:700;">
                                                    <?php echo esc_html($stu->name); ?>
                                                </a><br>
                                                <span style="font-size:0.75rem; color:#64748b; font-family: 'JetBrains Mono', monospace;">#<?php echo esc_html($stu->student_uid); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="ifs-tag"><?php echo esc_html($stu->batch); ?></span></td>
                                    <td style="text-align:center;">
                                        <div class="ifs-status-pill-group">
                                            <label><input type="radio" class="att-radio" name="status[<?php echo esc_attr($stu->id); ?>]" value="Present" <?php checked($status, 'Present'); ?>><span>Present</span></label>
                                            <label><input type="radio" class="att-radio" name="status[<?php echo esc_attr($stu->id); ?>]" value="Absent" <?php checked($status, 'Absent'); ?>><span>Absent</span></label>
                                            <label><input type="radio" class="att-radio" name="status[<?php echo esc_attr($stu->id); ?>]" value="Late" <?php checked($status, 'Late'); ?>><span>Late</span></label>
                                        </div>
                                    </td>
                                    <td style="text-align:center;">
                                        <?php if (!empty($phone_to_notify)): ?>
                                            <a href="https://wa.me/88<?php echo esc_attr(preg_replace('/[^0-9]/', '', $phone_to_notify)); ?>?text=<?php echo urlencode('Notice: ' . $stu->name . ' was marked absent on ' . $sel_date . '. - ' . $portal_name); ?>" target="_blank" class="ifs-btn-wa" style="font-size: 0.74rem; padding: 5px 10px;">WhatsApp</a>
                                        <?php else: ?>
                                            <span style="color:#cbd5e1; font-size:0.75rem;">No Phone</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:right;">
                                        <input type="text" name="remarks[<?php echo esc_attr($stu->id); ?>]" value="<?php echo esc_attr($rem); ?>" placeholder="Reason..." style="width: 130px; padding: 6px 10px; border-radius: 6px; border: 1.5px solid #e2e8f0; font-size: 0.82rem; font-family: inherit;">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 20px; display: flex; justify-content: flex-end;">
                    <button type="submit" class="ifs-btn" style="padding: 10px 24px;">Save Attendance Session</button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
        function setAll(status) {
            document.querySelectorAll('.att-radio[value="' + status + '"]').forEach(r => {
                r.checked = true;
            });
            updateCounters();
        }

        function updateCounters() {
            let p = 0, a = 0, l = 0;
            document.querySelectorAll('.att-radio:checked').forEach(r => {
                if (r.value === 'Present') p++;
                else if (r.value === 'Absent') a++;
                else if (r.value === 'Late') l++;
            });
            const elP = document.getElementById('count-present');
            const elA = document.getElementById('count-absent');
            const elL = document.getElementById('count-late');
            if (elP) elP.innerText = p;
            if (elA) elA.innerText = a;
            if (elL) elL.innerText = l;
        }

        document.querySelectorAll('.att-radio').forEach(r => {
            r.addEventListener('change', updateCounters);
        });
    </script>
    <?php
}