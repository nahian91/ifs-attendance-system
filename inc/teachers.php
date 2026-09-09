<?php
if (!defined('ABSPATH')) exit;

function ifs_erp_render_teachers_workspace($wpdb, $table_teachers, $currency_symbol) {
    $all_teachers_data = $wpdb->get_results("SELECT * FROM $table_teachers ORDER BY id DESC");
    ?>
    <div style="display: grid; grid-template-columns: 1.15fr 1.85fr; gap: 24px; align-items: start;">
        <!-- বাম কলাম: শিক্ষক এনরোলমেন্ট ফর্ম -->
        <div class="ifs-card" style="display: flex; flex-direction: column;">
            <div class="ifs-card-header" style="margin-bottom: 16px;">
                <div>
                    <h3 class="ifs-card-title">Enroll Faculty Member</h3>
                    <span style="font-size:0.8rem; color:#64748b; font-weight:500;">Add instructor profile, subject & salary</span>
                </div>
            </div>

            <form method="POST">
                <?php wp_nonce_field('ifs_teacher_nonce'); ?>
                <input type="hidden" name="ifs_action" value="add_teacher_entity">

                <div class="ifs-field-group">
                    <label>Profile Picture</label>
                    <div class="ifs-media-box">
                        <img id="teach_photo_preview" src="https://via.placeholder.com/100" class="ifs-media-preview" style="box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                        <div>
                            <input type="hidden" name="teacher_photo_url" id="teach_photo_url">
                            <button type="button" class="ifs-btn-ghost" style="padding: 7px 12px; font-size: 0.82rem;" onclick="openMediaUploader('teach_photo_url', 'teach_photo_preview')">Select Photo</button>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 14px;">
                    <div class="ifs-field-group" style="flex: 1;">
                        <label>Full Name *</label>
                        <input type="text" name="teacher_name" class="ifs-input" placeholder="e.g. Dr. Mahbubur Rahman" required>
                    </div>
                    <div class="ifs-field-group" style="flex: 1;">
                        <label>Designation *</label>
                        <input type="text" name="teacher_designation" class="ifs-input" placeholder="e.g. Senior Lecturer" required>
                    </div>
                </div>

                <div style="display: flex; gap: 14px;">
                    <div class="ifs-field-group" style="flex: 1;">
                        <label>Phone Number *</label>
                        <input type="text" name="teacher_phone" class="ifs-input" placeholder="017XXXXXXXX" required>
                    </div>
                    <div class="ifs-field-group" style="flex: 1;">
                        <label>Email Address</label>
                        <input type="email" name="teacher_email" class="ifs-input" placeholder="teacher@domain.com">
                    </div>
                </div>

                <div style="display: flex; gap: 14px;">
                    <div class="ifs-field-group" style="flex: 1;">
                        <label>Subject / Department</label>
                        <input type="text" name="teacher_subject" class="ifs-input" placeholder="e.g. Higher Mathematics">
                    </div>
                    <div class="ifs-field-group" style="flex: 1;">
                        <label>Monthly Salary (<?php echo esc_html($currency_symbol); ?>)</label>
                        <input type="number" step="0.01" name="teacher_salary" class="ifs-input" placeholder="30000.00">
                    </div>
                </div>

                <button type="submit" class="ifs-btn" style="width: 100%; margin-top: 6px; padding: 11px;">Register Teacher</button>
            </form>
        </div>

        <!-- ডান কলাম: ফ্যাকাল্টি ডিরেক্টরি টেবিল (স্লিম কাস্টম স্ক্রলবার সহ) -->
        <div class="ifs-card" style="display: flex; flex-direction: column;">
            <div class="ifs-card-header" style="margin-bottom: 16px;">
                <div>
                    <h3 class="ifs-card-title">Faculty Directory (<?php echo count($all_teachers_data); ?>)</h3>
                    <span style="font-size:0.8rem; color:#64748b; font-weight:500;">Active mentors & instructional staff</span>
                </div>
                <input type="text" id="facultySearchInput" placeholder="🔍 Search faculty..." onkeyup="filterFacultyTable()" style="padding: 6px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.82rem; outline: none; font-family: inherit;">
            </div>

            <?php if (empty($all_teachers_data)): ?>
                <div style="background:#f8fafc; border:2px dashed #cbd5e1; padding:48px 24px; border-radius:14px; text-align:center; margin:10px 0;">
                    <p style="margin:0; font-weight:700; color:#64748b; font-size:0.95rem;">No faculty members registered yet.</p>
                </div>
            <?php else: ?>
                <div class="ifs-custom-scrollbar" style="max-height: 520px; overflow-y: auto; padding-right: 6px; border: 1px solid #f1f5f9; border-radius: 12px;">
                    <table class="ifs-table" id="facultyDirectoryTable">
                        <thead style="position: sticky; top: 0; z-index: 5; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                            <tr>
                                <th style="background:#f8fafc; padding: 14px 16px;">Faculty Member</th>
                                <th style="background:#f8fafc; padding: 14px 16px;">Designation</th>
                                <th style="background:#f8fafc; padding: 14px 16px;">Contact</th>
                                <th style="background:#f8fafc; padding: 14px 16px;">Salary</th>
                                <th style="background:#f8fafc; text-align:right; padding: 14px 16px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_teachers_data as $td): ?>
                                <tr style="transition: background 0.15s ease;">
                                    <td>
                                        <div style="display:flex; align-items:center;">
                                            <img src="<?php echo esc_url($td->photo_url ?: 'https://via.placeholder.com/60'); ?>" class="ifs-avatar-sm" style="box-shadow: 0 2px 5px rgba(0,0,0,0.06);">
                                            <div>
                                                <strong style="color: #0f172a; font-size: 0.92rem;"><?php echo esc_html($td->name); ?></strong><br>
                                                <span style="font-size: 0.74rem; color: #64748b;"><?php echo esc_html($td->subject ?: 'General'); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="ifs-tag"><?php echo esc_html($td->designation); ?></span></td>
                                    <td>
                                        <div style="display: flex; flex-direction: column;">
                                            <span style="font-size: 0.85rem; font-weight: 600; color: #334155;"><?php echo esc_html($td->phone); ?></span>
                                            <?php if ($td->email): ?>
                                                <span style="font-size: 0.75rem; color: #94a3b8;"><?php echo esc_html($td->email); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <strong style="color: #10b981; font-size: 0.9rem;">
                                            <?php echo esc_html($currency_symbol) . ' ' . esc_html(number_format($td->salary, 2)); ?>
                                        </strong>
                                    </td>
                                    <td style="text-align:right;">
                                        <?php $del_t_url = wp_nonce_url(add_query_arg(['page' => 'ifs-attendance', 'ifs_action' => 'del_teacher_entity', 'teacher_id' => $td->id], admin_url('admin.php')), 'ifs_del_teacher_nonce'); ?>
                                        <a href="<?php echo esc_url($del_t_url); ?>" onclick="return confirm('Delete this faculty member?');" style="color:#ef4444; font-weight:700; text-decoration:none; font-size:0.8rem; padding: 4px 8px; border-radius: 6px; background: #fef2f2; border: 1px solid #fecaca; transition: all 0.2s ease;">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function filterFacultyTable() {
            const input = document.getElementById('facultySearchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('facultyDirectoryTable');
            if (!table) return;
            const tr = table.getElementsByTagName('tr');
            for (let i = 1; i < tr.length; i++) {
                let tdName = tr[i].getElementsByTagName('td')[0];
                let tdDesig = tr[i].getElementsByTagName('td')[1];
                let tdContact = tr[i].getElementsByTagName('td')[2];
                if (tdName || tdDesig || tdContact) {
                    let txtName = tdName ? (tdName.textContent || tdName.innerText) : '';
                    let txtDesig = tdDesig ? (tdDesig.textContent || tdDesig.innerText) : '';
                    let txtContact = tdContact ? (tdContact.textContent || tdContact.innerText) : '';
                    if (txtName.toLowerCase().indexOf(filter) > -1 || txtDesig.toLowerCase().indexOf(filter) > -1 || txtContact.toLowerCase().indexOf(filter) > -1) {
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