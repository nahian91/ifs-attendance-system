<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clean gray SVG default avatar data URI
 */
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

function ifs_erp_render_teachers_workspace($wpdb, $table_teachers, $currency_symbol) {
    $sub_tab           = isset($_GET['sub_tab']) ? sanitize_key($_GET['sub_tab']) : 'all';
    $base_url          = admin_url('admin.php?page=ifs-attendance&page_view=teachers');
    $all_teachers_data = $wpdb->get_results("SELECT * FROM {$table_teachers} ORDER BY id DESC");
    $default_avatar    = ifs_get_default_avatar();

    // Map batches & day-wise schedule to each teacher
    $table_batches = $wpdb->prefix . 'ifs_batches';
    $batches_raw   = $wpdb->get_results("SELECT id, batch_name, schedule_json, teacher_id FROM {$table_batches}");
    
    $teacher_batches_map = [];
    $teacher_slots_map   = [];
    $teacher_class_count = [];

    foreach ($batches_raw as $b) {
        $t_id = (int)$b->teacher_id;
        if (!empty($t_id)) {
            $teacher_batches_map[$t_id][] = $b->batch_name;
            $sched = json_decode($b->schedule_json ?? '', true) ?: [];
            
            foreach ($sched as $day => $times) {
                $teacher_slots_map[$t_id][] = [
                    'day'   => $day,
                    'batch' => $b->batch_name,
                    'start' => $times['start'] ?? '',
                    'end'   => $times['end'] ?? ''
                ];
                $teacher_class_count[$t_id] = ($teacher_class_count[$t_id] ?? 0) + 1;
            }
        }
    }
    ?>
    <style>
        .ifs-teacher-avatar {
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
        .ifs-inline-details-row {
            display: none;
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }
        .ifs-inline-details-box {
            padding: 20px 24px;
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 24px;
            align-items: start;
        }
        .ifs-detail-item {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px dashed #e2e8f0;
            font-size: 0.85rem;
        }
        .ifs-detail-item:last-child {
            border-bottom: none;
        }
        .ifs-detail-label {
            color: #64748b;
            font-weight: 600;
        }
        .ifs-detail-value {
            color: #0f172a;
            font-weight: 700;
        }
    </style>

    <!-- Sub-tab Navigation -->
    <nav class="ifs-subtabs-nav">
        <a href="<?php echo esc_url($base_url . '&sub_tab=all'); ?>" class="ifs-subtab-btn <?php echo ($sub_tab === 'all') ? 'active' : ''; ?>">
            👨‍🏫 Faculty Directory (<?php echo count($all_teachers_data); ?>)
        </a>
        <a href="<?php echo esc_url($base_url . '&sub_tab=add'); ?>" class="ifs-subtab-btn <?php echo ($sub_tab === 'add') ? 'active' : ''; ?>">
            ➕ Enroll Faculty Member
        </a>
    </nav>

    <?php if ($sub_tab === 'all'): ?>
        <!-- ALL TEACHERS TAB -->
        <div class="ifs-card" style="display: flex; flex-direction: column;">
            <div class="ifs-card-header" style="margin-bottom: 16px;">
                <div>
                    <h3 class="ifs-card-title">Faculty Directory</h3>
                    <span style="font-size:0.8rem; color:#64748b; font-weight:500;">Weekly routine schedule & instructional load</span>
                </div>
                <?php if (!empty($all_teachers_data)): ?>
                    <input type="text" id="facultySearchInput" placeholder="🔍 Search faculty..." onkeyup="filterFacultyTable()" style="padding: 7px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.82rem; outline: none; font-family: inherit; width: 230px;">
                <?php endif; ?>
            </div>

            <?php if (empty($all_teachers_data)): ?>
                <div style="background:#f8fafc; border:2px dashed #cbd5e1; padding:54px 24px; border-radius:14px; text-align:center; margin:10px 0;">
                    <p style="margin:0 0 10px; font-weight:700; color:#64748b; font-size:0.95rem;">No faculty members registered yet.</p>
                    <a href="<?php echo esc_url($base_url . '&sub_tab=add'); ?>" class="ifs-btn" style="padding: 8px 18px; font-size: 0.82rem;">+ Enroll First Instructor</a>
                </div>
            <?php else: ?>
                <div style="border: 1.5px solid #f1f5f9; border-radius: 12px; overflow: hidden;">
                    <table class="ifs-table" id="facultyDirectoryTable" style="margin:0;">
                        <thead>
                            <tr>
                                <th style="background:#f8fafc; padding: 14px 16px;">Faculty Member</th>
                                <th style="background:#f8fafc; padding: 14px 16px;">Designation</th>
                                <th style="background:#f8fafc; text-align:center; padding: 14px 16px;">Weekly Classes</th>
                                <th style="background:#f8fafc; padding: 14px 16px;">Salary</th>
                                <th style="background:#f8fafc; text-align:right; padding: 14px 16px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_teachers_data as $td): ?>
                                <?php 
                                $photo_url     = !empty($td->photo_url) ? esc_url($td->photo_url) : $default_avatar;
                                $batches_list  = $teacher_batches_map[$td->id] ?? [];
                                $slots_list    = $teacher_slots_map[$td->id] ?? [];
                                $total_classes = $teacher_class_count[$td->id] ?? 0;
                                ?>
                                <tr class="teacher-summary-row" data-search="<?php echo esc_attr(strtolower($td->name . ' ' . $td->designation . ' ' . $td->phone . ' ' . $td->subject)); ?>" style="transition: background 0.15s ease;">
                                    <td>
                                        <div style="display:flex; align-items:center;">
                                            <img src="<?php echo $photo_url; ?>" onerror="this.onerror=null;this.src='<?php echo $default_avatar; ?>';" class="ifs-teacher-avatar" alt="Avatar">
                                            <div>
                                                <strong style="color: #0f172a; font-size: 0.92rem;"><?php echo esc_html($td->name); ?></strong><br>
                                                <span style="font-size: 0.74rem; color: #64748b;"><?php echo esc_html($td->subject ?: 'General'); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="ifs-tag"><?php echo esc_html($td->designation); ?></span></td>
                                    <td style="text-align:center;">
                                        <span class="ifs-tag" style="background:#f0fdf4; border-color:#bbf7d0; color:#15803d; font-weight:800; font-size:0.8rem; padding:3px 10px;">
                                            <?php echo esc_html($total_classes); ?> Classes / Week
                                        </span>
                                    </td>
                                    <td>
                                        <strong style="color: #10b981; font-size: 0.9rem;">
                                            <?php echo esc_html($currency_symbol) . ' ' . esc_html(number_format((float)$td->salary, 2)); ?>
                                        </strong>
                                    </td>
                                    <td style="text-align:right;">
                                        <div style="display:flex; justify-content:flex-end; gap:6px; align-items:center;">
                                            <button type="button" class="ifs-btn-ghost" style="padding: 4px 10px; font-size: 0.78rem;" onclick="toggleInlineDetails(<?php echo (int)$td->id; ?>, this)">
                                                ▼ View Details
                                            </button>
                                            <?php $del_t_url = wp_nonce_url(add_query_arg(['page' => 'ifs-attendance', 'ifs_action' => 'del_teacher_entity', 'teacher_id' => $td->id], admin_url('admin.php')), 'ifs_del_teacher_nonce'); ?>
                                            <a href="<?php echo esc_url($del_t_url); ?>" onclick="return confirm('Delete this faculty member?');" style="color:#ef4444; font-weight:700; text-decoration:none; font-size:0.78rem; padding: 4px 8px; border-radius: 6px; background: #fef2f2; border: 1px solid #fecaca; transition: all 0.2s ease;">Delete</a>
                                        </div>
                                    </td>
                                </tr>

                                <!-- INLINE EXPANDABLE DETAILS ROW (NO POPUP) -->
                                <tr id="details-row-<?php echo (int)$td->id; ?>" class="ifs-inline-details-row">
                                    <td colspan="5" style="padding: 0;">
                                        <div class="ifs-inline-details-box">
                                            <!-- Basic Info Card -->
                                            <div style="background: #ffffff; padding: 16px; border-radius: 10px; border: 1px solid #e2e8f0;">
                                                <h4 style="margin: 0 0 10px; font-size: 0.88rem; color: #0f172a; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">Contact & Profile</h4>
                                                <div class="ifs-detail-item">
                                                    <span class="ifs-detail-label">Phone:</span>
                                                    <span class="ifs-detail-value"><?php echo esc_html($td->phone); ?></span>
                                                </div>
                                                <div class="ifs-detail-item">
                                                    <span class="ifs-detail-label">Email:</span>
                                                    <span class="ifs-detail-value"><?php echo esc_html($td->email ?: '—'); ?></span>
                                                </div>
                                                <div class="ifs-detail-item">
                                                    <span class="ifs-detail-label">Department:</span>
                                                    <span class="ifs-detail-value"><?php echo esc_html($td->subject ?: 'General'); ?></span>
                                                </div>
                                                <div class="ifs-detail-item">
                                                    <span class="ifs-detail-label">Monthly Salary:</span>
                                                    <span class="ifs-detail-value" style="color: #10b981;"><?php echo esc_html($currency_symbol . ' ' . number_format((float)$td->salary, 2)); ?></span>
                                                </div>
                                            </div>

                                            <!-- Day-Wise Routine Breakdown -->
                                            <div style="background: #ffffff; padding: 16px; border-radius: 10px; border: 1px solid #e2e8f0;">
                                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                                                    <h4 style="margin: 0; font-size: 0.88rem; color: #0f172a; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;">Weekly Day-Wise Routine Slots</h4>
                                                    <span class="ifs-tag" style="background:#e0f2fe; color:#0369a1; border-color:#bae6fd; font-weight:700;">
                                                        <?php echo count($batches_list); ?> Batches Assigned
                                                    </span>
                                                </div>

                                                <?php if (empty($slots_list)): ?>
                                                    <p style="margin:0; font-size:0.84rem; color:#94a3b8; font-style:italic;">No active day-wise routines scheduled for this faculty member.</p>
                                                <?php else: ?>
                                                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 8px;">
                                                        <?php foreach ($slots_list as $slot): ?>
                                                            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:8px 12px; display:flex; flex-direction:column; gap:2px;">
                                                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                                                    <strong style="color:#0284c7; font-size:0.82rem;"><?php echo esc_html($slot['day']); ?></strong>
                                                                    <span style="font-size:0.72rem; color:#64748b; font-weight:700;"><?php echo esc_html($slot['batch']); ?></span>
                                                                </div>
                                                                <span style="font-size:0.78rem; color:#334155; font-family:'JetBrains Mono', monospace;">
                                                                    🕒 <?php echo esc_html($slot['start']) . ' - ' . esc_html($slot['end']); ?>
                                                                </span>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    <?php elseif ($sub_tab === 'add'): ?>
        <!-- ENROLL TEACHER TAB -->
        <div class="ifs-card" style="max-width: 680px; margin: 0 auto; display: flex; flex-direction: column;">
            <div class="ifs-card-header" style="margin-bottom: 16px;">
                <div>
                    <h3 class="ifs-card-title">Enroll Faculty Member</h3>
                    <span style="font-size:0.8rem; color:#64748b; font-weight:500;">Add instructor profile, subject & salary details</span>
                </div>
            </div>

            <form method="POST" action="<?php echo esc_url(admin_url('admin.php?page=ifs-attendance')); ?>">
                <?php wp_nonce_field('ifs_teacher_nonce'); ?>
                <input type="hidden" name="ifs_action" value="add_teacher_entity">

                <div class="ifs-field-group">
                    <label>Profile Picture</label>
                    <div class="ifs-media-box">
                        <img id="teach_photo_preview" src="<?php echo $default_avatar; ?>" class="ifs-media-preview" style="width:58px; height:58px; border-radius:50%; object-fit:cover;">
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

                <button type="submit" class="ifs-btn" style="width: 100%; margin-top: 6px; padding: 12px;">Register Teacher</button>
            </form>
        </div>
    <?php endif; ?>

    <script>
        function filterFacultyTable() {
            var input = document.getElementById('facultySearchInput');
            if (!input) return;
            var filter = input.value.toLowerCase().trim();
            var rows = document.querySelectorAll('.teacher-summary-row');
            
            rows.forEach(function(row) {
                var searchContext = row.getAttribute('data-search') || '';
                var targetDetailsId = row.nextElementSibling ? row.nextElementSibling.id : '';
                var isMatch = searchContext.indexOf(filter) > -1;
                
                row.style.display = isMatch ? '' : 'none';
                if (!isMatch && targetDetailsId && targetDetailsId.indexOf('details-row-') > -1) {
                    document.getElementById(targetDetailsId).style.display = 'none';
                }
            });
        }

        function toggleInlineDetails(teacherId, btn) {
            var detailsRow = document.getElementById('details-row-' + teacherId);
            if (!detailsRow) return;

            if (detailsRow.style.display === 'table-row') {
                detailsRow.style.display = 'none';
                btn.innerText = '▼ View Details';
            } else {
                detailsRow.style.display = 'table-row';
                btn.innerText = '▲ Hide Details';
            }
        }
    </script>
    <?php
}