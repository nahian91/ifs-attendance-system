<?php
if (!defined('ABSPATH')) exit;

function ifs_erp_render_students_workspace($wpdb, $table_students, $available_batches, $available_insts, $sub_tab, $base_url, $currency_symbol) {
    ?>
    <style>
        .ifs-subtabs-nav {
            display: flex;
            gap: 6px;
            background: #e2e8f0;
            padding: 4px;
            border-radius: 10px;
            width: fit-content;
            margin-bottom: 22px;
        }
        .ifs-subtab-btn {
            padding: 7px 16px;
            font-size: 0.82rem;
            font-weight: 700;
            color: #475569;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .ifs-subtab-btn:hover {
            color: #0284c7;
        }
        .ifs-subtab-btn.active {
            background: #ffffff;
            color: #0284c7;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        }
        .ifs-modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(4px);
            align-items: center;
            justify-content: center;
        }
        .ifs-modal.open {
            display: flex;
        }
        .ifs-modal-content {
            background: #ffffff;
            width: 100%;
            max-width: 580px;
            border-radius: 16px;
            padding: 28px;
            position: relative;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.1);
            max-height: 90vh;
            overflow-y: auto;
            border: 1.5px solid #e2e8f0;
        }
        .ifs-modal-close {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 1.4rem;
            color: #94a3b8;
            cursor: pointer;
            border: none;
            background: none;
            line-height: 1;
        }
        .ifs-modal-close:hover {
            color: #ef4444;
        }
    </style>

    <nav class="ifs-subtabs-nav">
        <a href="<?php echo esc_url($base_url . '&page_view=students&sub_tab=all'); ?>" class="ifs-subtab-btn <?php echo ($sub_tab === 'all') ? 'active' : ''; ?>">👥 Enrolled Students</a>
        <a href="<?php echo esc_url($base_url . '&page_view=students&sub_tab=add'); ?>" class="ifs-subtab-btn <?php echo ($sub_tab === 'add') ? 'active' : ''; ?>">➕ Add Student</a>
    </nav>

    <?php if ($sub_tab === 'all'): ?>
        <?php
        $sel_batch = isset($_GET['batch_filter']) ? sanitize_text_field($_GET['batch_filter']) : '';
        $sql = "SELECT * FROM $table_students";
        if ($sel_batch !== '') $sql .= $wpdb->prepare(" WHERE batch = %s", $sel_batch);
        $sql .= " ORDER BY student_uid ASC";
        $list = $wpdb->get_results($sql);
        ?>
        <div class="ifs-card">
            <div class="ifs-card-header">
                <div>
                    <h3 class="ifs-card-title">Enrolled Students (<?php echo count($list); ?>)</h3>
                    <span style="font-size:0.8rem; color:#64748b; font-weight:500;">Comprehensive directory and admission tracker</span>
                </div>
                <div style="display:flex; gap:12px; align-items:center;">
                    <input type="text" id="liveSearchInput" placeholder="🔍 Instant search..." onkeyup="filterStudentDirectory()" style="padding:8px 14px; border:1.5px solid #cbd5e1; border-radius:8px; font-size:0.88rem; outline:none; font-family:inherit; background:#ffffff; color:#0f172a;">
                    <form method="GET" action="<?php echo esc_url(admin_url('admin.php')); ?>" style="margin:0;">
                        <input type="hidden" name="page" value="ifs-attendance">
                        <input type="hidden" name="page_view" value="students">
                        <input type="hidden" name="sub_tab" value="all">
                        <select name="batch_filter" onchange="this.form.submit()" style="padding:8px 14px; border-radius:8px; border:1.5px solid #cbd5e1; font-family:inherit; background:#ffffff; color:#0f172a;">
                            <option value="">-- All Batches --</option>
                            <?php foreach ($available_batches as $b): ?>
                                <option value="<?php echo esc_attr($b); ?>" <?php selected($sel_batch, $b); ?>><?php echo esc_html($b); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>

            <?php if (empty($list)): ?>
                <div style="background:#f8fafc; border:2px dashed #cbd5e1; padding:48px 24px; border-radius:14px; text-align:center; margin:10px 0;">
                    <p style="margin:0; font-weight:700; color:#64748b; font-size:0.95rem;">No students found matching your criteria.</p>
                </div>
            <?php else: ?>
                <div class="ifs-custom-scrollbar" style="max-height: 560px; overflow-y: auto; padding-right: 6px; border: 1.5px solid #f1f5f9; border-radius: 12px;">
                    <table class="ifs-table" id="studentDirectoryTable">
                        <thead style="position: sticky; top: 0; z-index: 5; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                            <tr>
                                <th style="background:#f8fafc; padding: 14px 16px;">Student</th>
                                <th style="background:#f8fafc; padding: 14px 16px;">Email</th>
                                <th style="background:#f8fafc; padding: 14px 16px;">Guardian Phone</th>
                                <th style="background:#f8fafc; padding: 14px 16px;">Batch</th>
                                <th style="background:#f8fafc; padding: 14px 16px;">Admission Due</th>
                                <th style="background:#f8fafc; padding: 14px 16px;">Reminder</th>
                                <th style="background:#f8fafc; text-align:right; padding: 14px 16px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($list as $stu): ?>
                                <tr style="transition: background 0.15s ease;">
                                    <td>
                                        <div style="display:flex; align-items:center;">
                                            <img src="<?php echo esc_url($stu->photo_url ?: 'https://via.placeholder.com/60'); ?>" class="ifs-avatar-sm" style="box-shadow: 0 2px 5px rgba(0,0,0,0.06);">
                                            <div>
                                                <a href="<?php echo esc_url($base_url . '&page_view=profile&student_id=' . $stu->id); ?>" style="color:#0f172a; text-decoration:none; font-weight:700;">
                                                    <?php echo esc_html($stu->name); ?>
                                                </a><br>
                                                <span style="font-size:0.75rem; color:#64748b; font-family:'JetBrains Mono', monospace;">#<?php echo esc_html($stu->student_uid); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo esc_html($stu->email); ?></td>
                                    <td><?php echo esc_html($stu->guardian_phone ?: '—'); ?></td>
                                    <td><span class="ifs-tag"><?php echo esc_html($stu->batch); ?></span></td>
                                    <td>
                                        <?php if ($stu->admission_due > 0): ?>
                                            <strong style="color:#ef4444;"><?php echo esc_html($currency_symbol) . ' ' . esc_html(number_format($stu->admission_due, 2)); ?></strong>
                                        <?php else: ?>
                                            <span style="color:#10b981; font-weight:700;">Cleared</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($stu->due_reminder_date): ?>
                                            <span class="ifs-tag" style="background:#fffbeb; color:#b45309; border-color:#fde68a; font-size:0.75rem;">
                                                <?php echo esc_html(date('M d, Y', strtotime($stu->due_reminder_date))); ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color:#cbd5e1;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:right;">
                                        <div style="display:flex; justify-content:flex-end; gap:6px; align-items:center;">
                                            <a href="<?php echo esc_url($base_url . '&page_view=profile&student_id=' . $stu->id); ?>" class="ifs-btn-ghost" style="padding:4px 8px; font-size:0.78rem;">Profile</a>
                                            <button type="button" class="ifs-btn-ghost" style="padding:4px 8px; font-size:0.78rem;" 
                                                onclick="openEditModal('<?php echo esc_attr($stu->id); ?>', '<?php echo esc_js($stu->student_uid); ?>', '<?php echo esc_js($stu->name); ?>', '<?php echo esc_js($stu->email); ?>', '<?php echo esc_js($stu->phone); ?>', '<?php echo esc_js($stu->guardian_phone); ?>', '<?php echo esc_js($stu->institution); ?>', '<?php echo esc_js($stu->batch); ?>', '<?php echo esc_js($stu->status); ?>', '<?php echo esc_js($stu->photo_url); ?>')">
                                                Edit
                                            </button>
                                            <?php $del_url = wp_nonce_url(add_query_arg(['page' => 'ifs-attendance', 'ifs_action' => 'del_student', 'student_id' => $stu->id], admin_url('admin.php')), 'ifs_del_stu_nonce'); ?>
                                            <a href="<?php echo esc_url($del_url); ?>" onclick="return confirm('Delete student and purge all associated records?');" style="color:#ef4444; font-weight:700; text-decoration:none; font-size:0.78rem; padding:4px 8px;">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div id="studentEditModal" class="ifs-modal">
            <div class="ifs-modal-content ifs-custom-scrollbar">
                <button type="button" class="ifs-modal-close" onclick="closeEditModal()">&times;</button>
                <h3 class="ifs-card-title" style="margin-bottom:18px;">Update Student Information</h3>
                <form method="POST">
                    <?php wp_nonce_field('ifs_edit_stu_nonce'); ?>
                    <input type="hidden" name="ifs_action" value="edit_student">
                    <input type="hidden" name="student_id" id="edit_stu_id">

                    <div class="ifs-field-group">
                        <label>Profile Picture</label>
                        <div class="ifs-media-box">
                            <img id="edit_stu_photo_preview" src="https://via.placeholder.com/100" class="ifs-media-preview">
                            <div>
                                <input type="hidden" name="student_photo_url" id="edit_stu_photo_url">
                                <button type="button" class="ifs-btn-ghost" onclick="openMediaUploader('edit_stu_photo_url', 'edit_stu_photo_preview')">Change Media Photo</button>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex; gap:12px;">
                        <div class="ifs-field-group" style="flex:1;">
                            <label>Student UID / Roll *</label>
                            <input type="text" name="student_uid" id="edit_stu_uid" class="ifs-input" required>
                        </div>
                        <div class="ifs-field-group" style="flex:1;">
                            <label>Full Name *</label>
                            <input type="text" name="student_name" id="edit_stu_name" class="ifs-input" required>
                        </div>
                    </div>

                    <div style="display:flex; gap:12px;">
                        <div class="ifs-field-group" style="flex:1;">
                            <label>Portal Email *</label>
                            <input type="email" name="student_email" id="edit_stu_email" class="ifs-input" required>
                        </div>
                        <div class="ifs-field-group" style="flex:1;">
                            <label>Reset Password (Leave blank to keep)</label>
                            <input type="password" name="student_password" id="edit_stu_password" class="ifs-input" placeholder="New password...">
                        </div>
                    </div>

                    <div style="display:flex; gap:12px;">
                        <div class="ifs-field-group" style="flex:1;">
                            <label>Phone Number</label>
                            <input type="text" name="student_phone" id="edit_stu_phone" class="ifs-input">
                        </div>
                        <div class="ifs-field-group" style="flex:1;">
                            <label>Guardian Phone</label>
                            <input type="text" name="guardian_phone" id="edit_stu_gphone" class="ifs-input">
                        </div>
                    </div>

                    <div style="display:flex; gap:12px;">
                        <div class="ifs-field-group" style="flex:1;">
                            <label>Institution *</label>
                            <select name="student_institution" id="edit_stu_inst" class="ifs-select" required>
                                <?php foreach ($available_insts as $inst): ?>
                                    <option value="<?php echo esc_attr($inst); ?>"><?php echo esc_html($inst); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="ifs-field-group" style="flex:1;">
                            <label>Batch *</label>
                            <select name="student_batch" id="edit_stu_batch" class="ifs-select" required>
                                <?php foreach ($available_batches as $b): ?>
                                    <option value="<?php echo esc_attr($b); ?>"><?php echo esc_html($b); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="ifs-field-group">
                        <label>Account Status</label>
                        <select name="student_status" id="edit_stu_status" class="ifs-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <button type="submit" class="ifs-btn" style="width:100%; margin-top:10px;">Save Profile Changes</button>
                </form>
            </div>
        </div>

        <script>
        function filterStudentDirectory() {
            const input = document.getElementById('liveSearchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('studentDirectoryTable');
            const tr = table.getElementsByTagName('tr');
            for (let i = 1; i < tr.length; i++) {
                let tdStudent = tr[i].getElementsByTagName('td')[0];
                let tdEmail = tr[i].getElementsByTagName('td')[1];
                let tdBatch = tr[i].getElementsByTagName('td')[3];
                if (tdStudent || tdEmail || tdBatch) {
                    let txtStudent = tdStudent ? (tdStudent.textContent || tdStudent.innerText) : '';
                    let txtEmail = tdEmail ? (tdEmail.textContent || tdEmail.innerText) : '';
                    let txtBatch = tdBatch ? (tdBatch.textContent || tdBatch.innerText) : '';
                    if (txtStudent.toLowerCase().indexOf(filter) > -1 || txtEmail.toLowerCase().indexOf(filter) > -1 || txtBatch.toLowerCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }

        function openEditModal(id, uid, name, email, phone, gphone, inst, batch, status, photoUrl) {
            document.getElementById('edit_stu_id').value = id;
            document.getElementById('edit_stu_uid').value = uid;
            document.getElementById('edit_stu_name').value = name;
            document.getElementById('edit_stu_email').value = email;
            document.getElementById('edit_stu_phone').value = phone;
            document.getElementById('edit_stu_gphone').value = gphone;
            document.getElementById('edit_stu_inst').value = inst;
            document.getElementById('edit_stu_batch').value = batch;
            document.getElementById('edit_stu_status').value = status;
            document.getElementById('edit_stu_password').value = '';
            document.getElementById('edit_stu_photo_url').value = photoUrl || '';
            document.getElementById('edit_stu_photo_preview').src = photoUrl || 'https://via.placeholder.com/100';
            document.getElementById('studentEditModal').classList.add('open');
        }

        function closeEditModal() {
            document.getElementById('studentEditModal').classList.remove('open');
        }

        window.onclick = function(event) {
            const modal = document.getElementById('studentEditModal');
            if (event.target == modal) {
                closeEditModal();
            }
        }
        </script>

    <?php elseif ($sub_tab === 'add'): ?>
        <div class="ifs-card" style="max-width: 680px; margin: 0 auto;">
            <div class="ifs-card-header">
                <div>
                    <h3 class="ifs-card-title">Enroll Student with Credentials & Admission Fee</h3>
                    <span style="font-size:0.8rem; color:#64748b; font-weight:500;">Create portal credentials and configure dynamic fee structures</span>
                </div>
            </div>
            <form method="POST">
                <?php wp_nonce_field('ifs_stu_nonce'); ?>
                <input type="hidden" name="ifs_action" value="add_student">

                <div class="ifs-field-group">
                    <label>Student Profile Picture</label>
                    <div class="ifs-media-box">
                        <img id="stu_photo_preview" src="https://via.placeholder.com/100" class="ifs-media-preview">
                        <div>
                            <input type="hidden" name="student_photo_url" id="stu_photo_url">
                            <button type="button" class="ifs-btn-ghost" onclick="openMediaUploader('stu_photo_url', 'stu_photo_preview')">Choose Photo from Media Library</button>
                        </div>
                    </div>
                </div>

                <div style="display:flex; gap:14px;">
                    <div class="ifs-field-group" style="flex:1;">
                        <label>Student ID / Roll *</label>
                        <input type="text" name="student_uid" class="ifs-input" placeholder="e.g. IFS-2026-01" required>
                    </div>
                    <div class="ifs-field-group" style="flex:1;">
                        <label>Full Name *</label>
                        <input type="text" name="student_name" class="ifs-input" placeholder="e.g. Mahfuzur Rahman" required>
                    </div>
                </div>

                <div style="display:flex; gap:14px;">
                    <div class="ifs-field-group" style="flex:1;">
                        <label>Portal Login Email *</label>
                        <input type="email" name="student_email" class="ifs-input" placeholder="student@example.com" required>
                    </div>
                    <div class="ifs-field-group" style="flex:1;">
                        <label>Set Login Password *</label>
                        <input type="password" name="student_password" class="ifs-input" placeholder="••••••••" required>
                    </div>
                </div>

                <div style="display:flex; gap:14px;">
                    <div class="ifs-field-group" style="flex:1;">
                        <label>Student Phone</label>
                        <input type="text" name="student_phone" class="ifs-input" placeholder="017XXXXXXXX">
                    </div>
                    <div class="ifs-field-group" style="flex:1;">
                        <label>Guardian WhatsApp / Phone</label>
                        <input type="text" name="guardian_phone" class="ifs-input" placeholder="018XXXXXXXX">
                    </div>
                </div>

                <div style="display:flex; gap:14px;">
                    <div class="ifs-field-group" style="flex:1;">
                        <label>Institution *</label>
                        <select name="student_institution" class="ifs-select" required>
                            <?php foreach ($available_insts as $inst): ?>
                                <option value="<?php echo esc_attr($inst); ?>"><?php echo esc_html($inst); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="ifs-field-group" style="flex:1;">
                        <label>Batch *</label>
                        <select name="student_batch" class="ifs-select" required>
                            <?php foreach ($available_batches as $b): ?>
                                <option value="<?php echo esc_attr($b); ?>"><?php echo esc_html($b); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:18px; margin-bottom:20px;">
                    <h4 style="margin:0 0 14px; font-size:0.95rem; color:#0f172a;">Admission Fee & Payment Structure</h4>
                    
                    <div style="display:flex; gap:14px;">
                        <div class="ifs-field-group" style="flex:1;">
                            <label>Total Admission Fee (<?php echo esc_html($currency_symbol); ?>)</label>
                            <input type="number" step="0.01" name="admission_fee" id="adm_fee" class="ifs-input" value="3000.00" oninput="calcAdmDue()">
                        </div>
                        <div class="ifs-field-group" style="flex:1;">
                            <label>Payment Mode</label>
                            <select name="admission_fee_type" id="adm_type" class="ifs-select" onchange="toggleAdmReminder()">
                                <option value="full">Full Payment</option>
                                <option value="partial">Partial Payment</option>
                            </select>
                        </div>
                    </div>

                    <div id="partial_fields_wrap" style="display:none; margin-top:10px;">
                        <div style="display:flex; gap:14px;">
                            <div class="ifs-field-group" style="flex:1;">
                                <label>Paid Amount Today</label>
                                <input type="number" step="0.01" name="admission_paid" id="adm_paid" class="ifs-input" value="1500.00" oninput="calcAdmDue()">
                            </div>
                            <div class="ifs-field-group" style="flex:1;">
                                <label>Calculated Remaining Due</label>
                                <input type="text" id="adm_due" class="ifs-input" readonly style="background:#ffffff; font-weight:800; color:#ef4444;">
                            </div>
                        </div>

                        <div class="ifs-field-group">
                            <label style="color:#d97706;">Reminder Date for Balance Payment *</label>
                            <input type="date" name="due_reminder_date" class="ifs-input" value="<?php echo date('Y-m-d', strtotime('+15 days')); ?>">
                        </div>
                    </div>
                </div>

                <button type="submit" class="ifs-btn" style="width:100%; padding: 12px;">Complete Enrollment</button>
            </form>
        </div>
        <script>
        function toggleAdmReminder() {
            const type = document.getElementById('adm_type').value;
            document.getElementById('partial_fields_wrap').style.display = (type === 'partial') ? 'block' : 'none';
            calcAdmDue();
        }
        function calcAdmDue() {
            const fee = parseFloat(document.getElementById('adm_fee').value) || 0;
            const type = document.getElementById('adm_type').value;
            if (type === 'partial') {
                const paid = parseFloat(document.getElementById('adm_paid').value) || 0;
                document.getElementById('adm_due').value = Math.max(0, fee - paid).toFixed(2);
            } else {
                document.getElementById('adm_paid').value = fee;
                document.getElementById('adm_due').value = '0.00';
            }
        }
        </script>
    <?php endif;
}

function ifs_erp_render_student_profile_view($wpdb, $student_id, $table_students, $table_attendance, $table_fees, $currency_symbol, $base_url) {
    $student = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_students WHERE id = %d", $student_id));
    
    if (!$student):
        echo '<div class="ifs-card"><p style="color:#ef4444; font-weight:700;">Student record could not be found. <a href="' . esc_url($base_url . '&page_view=students') . '">Return to Directory</a></p></div>';
    else:
        $total_classes = (int) $wpdb->get_var("SELECT COUNT(DISTINCT attendance_date) FROM $table_attendance");
        $att_stats = $wpdb->get_results($wpdb->prepare("SELECT status, COUNT(*) as cnt FROM $table_attendance WHERE student_id = %d GROUP BY status", $student_id), OBJECT_K);
        $p_cnt = isset($att_stats['Present']) ? (int)$att_stats['Present']->cnt : 0;
        $a_cnt = isset($att_stats['Absent']) ? (int)$att_stats['Absent']->cnt : 0;
        $l_cnt = isset($att_stats['Late']) ? (int)$att_stats['Late']->cnt : 0;
        $presence_pct = ($total_classes > 0) ? round(($p_cnt / $total_classes) * 100, 1) : 0;

        $total_paid = (float) $wpdb->get_var($wpdb->prepare("SELECT SUM(paid_amount) FROM $table_fees WHERE student_id = %d", $student_id));
        $fee_history = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_fees WHERE student_id = %d ORDER BY payment_date DESC", $student_id));
        $recent_att = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_attendance WHERE student_id = %d ORDER BY attendance_date DESC LIMIT 15", $student_id));
        ?>
        <div style="margin-bottom:20px;">
            <a href="<?php echo esc_url($base_url . '&page_view=students&sub_tab=all'); ?>" class="ifs-btn-ghost">← Back to Directory</a>
        </div>

        <div class="ifs-card" style="background: linear-gradient(135deg, #f0f9ff 0%, #ffffff 55%, #f8fafc 100%); border: 1.5px solid #bae6fd; padding: 26px;">
            <div style="display:flex; align-items:center; gap:22px; flex-wrap:wrap;">
                <img src="<?php echo esc_url($student->photo_url ?: 'https://via.placeholder.com/150'); ?>" style="width:84px; height:84px; border-radius:50%; object-fit:cover; border:3px solid #0284c7; box-shadow: 0 2px 8px rgba(2, 132, 199, 0.15);">
                <div style="flex-grow:1;">
                    <h2 style="margin:0 0 6px; font-size:1.6rem; color:#0f172a; font-weight:800;"><?php echo esc_html($student->name); ?></h2>
                    <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:8px;">
                        <span class="ifs-tag" style="background:#e0f2fe; color:#0369a1; border-color:#bae6fd; font-family:'JetBrains Mono', monospace;">#<?php echo esc_html($student->student_uid); ?></span>
                        <span class="ifs-tag" style="background:#f1f5f9; color:#475569; border-color:#cbd5e1;"><?php echo esc_html($student->batch); ?></span>
                        <span class="ifs-tag" style="background:#f1f5f9; color:#475569; border-color:#cbd5e1;"><?php echo esc_html($student->institution); ?></span>
                        <span class="ifs-tag" style="background:#f1f5f9; color:#475569; border-color:#cbd5e1;">📧 <?php echo esc_html($student->email); ?></span>
                        <?php if ($student->phone): ?>
                            <span class="ifs-tag" style="background:#f1f5f9; color:#475569; border-color:#cbd5e1;">📞 <?php echo esc_html($student->phone); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <span class="ifs-badge <?php echo ($student->status === 'active') ? 'badge-present' : 'badge-absent'; ?>" style="font-size:0.85rem; padding:6px 14px;">
                        <?php echo esc_html(ucfirst($student->status)); ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="ifs-kpi-grid">
            <div class="ifs-kpi-tile kpi-green">
                <span class="ifs-kpi-meta">Attendance Percentage</span>
                <div class="ifs-kpi-number" style="color:#10b981;"><?php echo esc_html($presence_pct); ?>%</div>
                <span class="ifs-kpi-desc"><?php echo esc_html($p_cnt); ?> Present, <?php echo esc_html($a_cnt); ?> Absent, <?php echo esc_html($l_cnt); ?> Late</span>
            </div>
            <div class="ifs-kpi-tile kpi-blue">
                <span class="ifs-kpi-meta">Total Fees Paid</span>
                <div class="ifs-kpi-number" style="color:#0284c7;"><?php echo esc_html($currency_symbol) . ' ' . esc_html(number_format($total_paid, 2)); ?></div>
                <span class="ifs-kpi-desc">Verified Invoices</span>
            </div>
            <div class="ifs-kpi-tile kpi-red">
                <span class="ifs-kpi-meta">Admission Balance Due</span>
                <div class="ifs-kpi-number" style="color:#ef4444;"><?php echo esc_html($currency_symbol) . ' ' . esc_html(number_format($student->admission_due, 2)); ?></div>
                <span class="ifs-kpi-desc">Reminder: <?php echo $student->due_reminder_date ? esc_html(date('M d, Y', strtotime($student->due_reminder_date))) : 'None'; ?></span>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px; align-items: start;">
            <div class="ifs-card" style="display: flex; flex-direction: column;">
                <div class="ifs-card-header" style="margin-bottom: 16px;">
                    <div>
                        <h3 class="ifs-card-title">Recent Attendance Record</h3>
                        <span style="font-size:0.8rem; color:#64748b; font-weight:500;">Class presence timeline</span>
                    </div>
                </div>
                <?php if (empty($recent_att)): ?>
                    <div style="background:#f8fafc; border:1px solid #e2e8f0; padding:32px 16px; border-radius:12px; text-align:center; color:#94a3b8; font-size:0.88rem;">
                        No attendance sessions logged for this student.
                    </div>
                <?php else: ?>
                    <div class="ifs-custom-scrollbar" style="max-height: 420px; overflow-y: auto; padding-right: 6px; border: 1.5px solid #f1f5f9; border-radius: 12px;">
                        <table class="ifs-table">
                            <thead style="position: sticky; top: 0; z-index: 2;">
                                <tr>
                                    <th style="background:#f8fafc;">Date</th>
                                    <th style="background:#f8fafc; text-align:center;">Status</th>
                                    <th style="background:#f8fafc; text-align:right;">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_att as $ra): ?>
                                    <tr>
                                        <td><?php echo esc_html(date('M d, Y', strtotime($ra->attendance_date))); ?></td>
                                        <td style="text-align:center;">
                                            <span class="ifs-badge badge-<?php echo esc_attr(strtolower($ra->status)); ?>">
                                                <?php echo esc_html($ra->status); ?>
                                            </span>
                                        </td>
                                        <td style="color:#64748b; font-size:0.85rem; text-align:right;"><?php echo esc_html($ra->remarks ?: '—'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="ifs-card" style="display: flex; flex-direction: column;">
                <div class="ifs-card-header" style="margin-bottom: 16px;">
                    <div>
                        <h3 class="ifs-card-title">Fee Receipts & Ledger</h3>
                        <span style="font-size:0.8rem; color:#64748b; font-weight:500;">Transaction logs and clearings</span>
                    </div>
                </div>
                <?php if (empty($fee_history)): ?>
                    <div style="background:#f8fafc; border:1px solid #e2e8f0; padding:32px 16px; border-radius:12px; text-align:center; color:#94a3b8; font-size:0.88rem;">
                        No fee payments logged for this student.
                    </div>
                <?php else: ?>
                    <div class="ifs-custom-scrollbar" style="max-height: 420px; overflow-y: auto; padding-right: 6px; border: 1.5px solid #f1f5f9; border-radius: 12px;">
                        <table class="ifs-table">
                            <thead style="position: sticky; top: 0; z-index: 2;">
                                <tr>
                                    <th style="background:#f8fafc;">Receipt</th>
                                    <th style="background:#f8fafc;">Title</th>
                                    <th style="background:#f8fafc;">Paid</th>
                                    <th style="background:#f8fafc; text-align:right;">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fee_history as $fh): ?>
                                    <tr>
                                        <td><span class="ifs-tag"><?php echo esc_html($fh->receipt_no); ?></span></td>
                                        <td><?php echo esc_html($fh->fee_title); ?></td>
                                        <td style="color:#10b981; font-weight:700;"><?php echo esc_html($currency_symbol) . ' ' . esc_html(number_format($fh->paid_amount, 2)); ?></td>
                                        <td style="text-align:right; color:#64748b; font-size:0.85rem;"><?php echo esc_html(date('M d, Y', strtotime($fh->payment_date))); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif;
}