<?php
if (!defined('ABSPATH')) exit;

function ifs_erp_render_students_workspace($wpdb, $table_students, $available_batches, $available_insts, $sub_tab, $base_url, $currency_symbol) {
    ?>
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
                <h3 class="ifs-card-title">Enrolled Students (<?php echo count($list); ?>)</h3>
                <form method="GET" action="<?php echo esc_url(admin_url('admin.php')); ?>" style="margin:0;">
                    <input type="hidden" name="page" value="ifs-attendance">
                    <input type="hidden" name="page_view" value="students">
                    <input type="hidden" name="sub_tab" value="all">
                    <select name="batch_filter" onchange="this.form.submit()" style="padding:8px 14px; border-radius:8px; border:1.5px solid #cbd5e1;">
                        <option value="">-- All Batches --</option>
                        <?php foreach ($available_batches as $b): ?>
                            <option value="<?php echo esc_attr($b); ?>" <?php selected($sel_batch, $b); ?>><?php echo esc_html($b); ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <table class="ifs-table">
                <thead>
                    <tr><th>Student</th><th>Email</th><th>Guardian Phone</th><th>Batch</th><th>Admission Due</th><th>Reminder</th><th style="text-align:right;">Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($list as $stu): ?>
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
                            <td><?php echo esc_html($stu->email); ?></td>
                            <td><?php echo esc_html($stu->guardian_phone ?: '—'); ?></td>
                            <td><span class="ifs-tag"><?php echo esc_html($stu->batch); ?></span></td>
                            <td>
                                <?php if ($stu->admission_due > 0): ?>
                                    <strong style="color:#ef4444;"><?php echo $currency_symbol . ' ' . number_format($stu->admission_due, 2); ?></strong>
                                <?php else: ?>
                                    <span style="color:#10b981; font-weight:700;">Cleared</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($stu->due_reminder_date): ?>
                                    <span class="ifs-tag" style="background:#fffbeb; color:#b45309; border-color:#fde68a;"><?php echo date('M d, Y', strtotime($stu->due_reminder_date)); ?></span>
                                <?php else: ?>
                                    <span style="color:#cbd5e1;">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:right;">
                                <a href="<?php echo esc_url($base_url . '&page_view=profile&student_id=' . $stu->id); ?>" class="ifs-btn-ghost" style="padding:4px 8px; font-size:0.8rem;">Profile</a>
                                <?php $del_url = wp_nonce_url(add_query_arg(['page' => 'ifs-attendance', 'ifs_action' => 'del_student', 'student_id' => $stu->id], admin_url('admin.php')), 'ifs_del_stu_nonce'); ?>
                                <a href="<?php echo esc_url($del_url); ?>" onclick="return confirm('Delete student?');" style="color:#ef4444; font-weight:700; margin-left:6px; text-decoration:none; font-size:0.8rem;">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($sub_tab === 'add'): ?>
        <div class="ifs-card" style="max-width: 680px; margin: 0 auto;">
            <div class="ifs-card-header">
                <h3 class="ifs-card-title">Enroll Student with Password & Admission Fee</h3>
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

                <button type="submit" class="ifs-btn" style="width:100%;">Complete Enrollment</button>
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