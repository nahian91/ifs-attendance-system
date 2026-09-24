<?php
if (!defined('ABSPATH')) {
    exit;
}

function ifs_erp_render_batches_workspace($wpdb, $table_batches, $teachers_map, $currency_symbol) {
    $sub_tab          = isset($_GET['sub_tab']) ? sanitize_key($_GET['sub_tab']) : 'all';
    $base_url         = admin_url('admin.php?page=ifs-attendance&page_view=batches');
    $all_batches_data = $wpdb->get_results("SELECT * FROM {$table_batches} ORDER BY id DESC"); 
    $days_list        = ['Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri'];

    // 1. Calculate Batch-Wise Total Classes Completed to Date
    $table_students   = $wpdb->prefix . 'ifs_students';
    $table_attendance = $wpdb->prefix . 'ifs_attendance';

    // Group distinct attendance dates per batch
    $batch_class_counts_raw = $wpdb->get_results("
        SELECT s.batch, COUNT(DISTINCT a.attendance_date) as completed_classes
        FROM {$table_attendance} a
        INNER JOIN {$table_students} s ON a.student_id = s.id
        WHERE a.status IN ('Present', 'Late', 'Absent')
        GROUP BY s.batch
    ");

    $batch_completed_map = [];
    foreach ($batch_class_counts_raw as $row) {
        $batch_completed_map[$row->batch] = (int)$row->completed_classes;
    }
    ?>
    <style>
        /* Modern iOS-Style Toggle Switch */
        .ifs-switch {
            position: relative;
            display: inline-block;
            width: 38px;
            height: 22px;
            flex-shrink: 0;
            margin: 0;
        }
        .ifs-switch input {
            opacity: 0;
            width: 0;
            height: 0;
            position: absolute;
        }
        .ifs-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cbd5e1;
            transition: 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 22px;
        }
        .ifs-slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 3px;
            bottom: 3px;
            background-color: #ffffff;
            transition: 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 50%;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }
        .ifs-switch input:checked + .ifs-slider {
            background-color: #0284c7;
        }
        .ifs-switch input:checked + .ifs-slider:before {
            transform: translateX(16px);
        }

        .ifs-day-row {
            display: grid;
            grid-template-columns: 110px 1fr 1fr;
            gap: 12px;
            align-items: center;
            padding: 10px 14px;
            border-bottom: 1px solid #edf2f7;
            transition: background 0.15s ease;
        }
        .ifs-day-row:last-child {
            border-bottom: none;
        }
        .ifs-day-row:hover {
            background: #f8fafc;
        }
        .ifs-day-label {
            font-size: 0.85rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            user-select: none;
            margin: 0;
            color: #334155;
        }
        .ifs-day-input {
            width: 100%;
            padding: 7px 12px;
            border: 1.5px solid #cbd5e1;
            border-radius: 7px;
            font-size: 0.82rem;
            color: #0f172a;
            background: #ffffff;
            box-sizing: border-box;
            outline: none;
            transition: all 0.2s;
            font-family: inherit;
        }
        .ifs-day-input:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.12);
        }
        .ifs-day-input:disabled {
            background: #f1f5f9;
            color: #94a3b8;
            border-color: #e2e8f0;
            cursor: not-allowed;
        }

        /* Top Completed Classes Metrics Grid */
        .ifs-batch-summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 14px;
            margin-bottom: 22px;
        }
        .ifs-batch-summary-tile {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 18px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            position: relative;
            overflow: hidden;
        }
        .ifs-batch-summary-tile::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: #0284c7;
        }
    </style>

    <!-- Top: Batch-Wise Total Classes Completed -->
    <?php if (!empty($all_batches_data)): ?>
        <div style="margin-bottom: 12px;">
            <h4 style="margin: 0 0 10px; font-size: 0.92rem; color: #0f172a; font-weight: 800; display: flex; align-items: center; gap: 6px;">
                <span>📊</span> Batch Progress & Completed Classes
            </h4>
            <div class="ifs-batch-summary-grid">
                <?php foreach ($all_batches_data as $bd): ?>
                    <?php $completed = $batch_completed_map[$bd->batch_name] ?? 0; ?>
                    <div class="ifs-batch-summary-tile">
                        <span style="font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">
                            <?php echo esc_html($bd->batch_name); ?>
                        </span>
                        <div style="font-size: 1.6rem; font-weight: 800; color: #0284c7; font-family: 'JetBrains Mono', monospace; line-height: 1.1;">
                            <?php echo esc_html($completed); ?>
                            <span style="font-size: 0.8rem; font-weight: 700; color: #10b981;">Classes</span>
                        </div>
                        <span style="font-size: 0.72rem; color: #94a3b8;">Distinct Sessions Held</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Sub-tab Navigation -->
    <nav class="ifs-subtabs-nav">
        <a href="<?php echo esc_url($base_url . '&sub_tab=all'); ?>" class="ifs-subtab-btn <?php echo ($sub_tab === 'all') ? 'active' : ''; ?>">
            📚 Configured Batches (<?php echo count($all_batches_data); ?>)
        </a>
        <a href="<?php echo esc_url($base_url . '&sub_tab=add'); ?>" class="ifs-subtab-btn <?php echo ($sub_tab === 'add') ? 'active' : ''; ?>">
            ➕ Create New Batch
        </a>
    </nav>

    <?php if ($sub_tab === 'all'): ?>
        <!-- ALL BATCHES TAB -->
        <div class="ifs-card" style="display: flex; flex-direction: column;">
            <div class="ifs-card-header" style="margin-bottom: 16px;">
                <div>
                    <h3 class="ifs-card-title">Configured Batches</h3>
                    <span style="font-size:0.8rem; color:#64748b; font-weight:500;">Active classes, fee pricing & assigned faculty routines</span>
                </div>
                <?php if (!empty($all_batches_data)): ?>
                    <input type="text" id="batchSearchInput" placeholder="🔍 Search batch or instructor..." onkeyup="filterBatchTable()" style="padding: 7px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.82rem; outline: none; font-family: inherit; background:#ffffff; color:#0f172a; width: 230px;">
                <?php endif; ?>
            </div>

            <?php if (empty($all_batches_data)): ?>
                <div style="background:#f8fafc; border:2px dashed #cbd5e1; padding:54px 24px; border-radius:14px; text-align:center; margin:10px 0;">
                    <p style="margin:0 0 10px; font-weight:700; color:#64748b; font-size:0.95rem;">No batches created yet.</p>
                    <a href="<?php echo esc_url($base_url . '&sub_tab=add'); ?>" class="ifs-btn" style="padding: 8px 18px; font-size: 0.82rem;">+ Create Your First Batch</a>
                </div>
            <?php else: ?>
                <div style="border: 1.5px solid #f1f5f9; border-radius: 12px; overflow: hidden;">
                    <table class="ifs-table" id="batchDirectoryTable" style="margin:0;">
                        <thead>
                            <tr>
                                <th style="background:#f8fafc; padding: 14px 16px;">Batch Name</th>
                                <th style="background:#f8fafc; padding: 14px 16px;">Course Fee</th>
                                <th style="background:#f8fafc; padding: 14px 16px;">Classes Done</th>
                                <th style="background:#f8fafc; padding: 14px 16px;">Lead Teacher</th>
                                <th style="background:#f8fafc; padding: 14px 16px;">Routine Slots</th>
                                <th style="background:#f8fafc; text-align:right; padding: 14px 16px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_batches_data as $bd): ?>
                                <?php 
                                $t_name          = isset($teachers_map[$bd->teacher_id]) ? $teachers_map[$bd->teacher_id]->name : 'Unassigned';
                                $sched           = json_decode($bd->schedule_json, true) ?: [];
                                $classes_done    = $batch_completed_map[$bd->batch_name] ?? 0;
                                ?>
                                <tr style="transition: background 0.15s ease;">
                                    <td>
                                        <strong style="color: #0f172a; font-size: 0.95rem;"><?php echo esc_html($bd->batch_name); ?></strong><br>
                                        <span style="font-size: 0.75rem; color: #64748b; font-family:'JetBrains Mono', monospace;">ID: #<?php echo esc_html($bd->id); ?></span>
                                    </td>
                                    <td>
                                        <strong style="color: #0284c7;"><?php echo esc_html($currency_symbol) . ' ' . esc_html(number_format((float)$bd->course_fee, 2)); ?></strong>
                                    </td>
                                    <td>
                                        <span class="ifs-tag" style="background:#f0fdf4; border-color:#bbf7d0; color:#15803d; font-weight:800; font-size:0.78rem;">
                                            ✓ <?php echo esc_html($classes_done); ?> Held
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($t_name !== 'Unassigned'): ?>
                                            <span style="font-weight: 600; color: #334155;">👨‍🏫 <?php echo esc_html($t_name); ?></span>
                                        <?php else: ?>
                                            <span style="color: #94a3b8; font-style: italic;">Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (empty($sched)): ?>
                                            <span style="color: #94a3b8; font-size: 0.8rem;">No routine slots configured</span>
                                        <?php else: ?>
                                            <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                                <?php foreach ($sched as $day => $times): ?>
                                                    <span class="ifs-tag" style="background:#f8fafc; border-color:#e2e8f0; color:#334155; font-size: 0.72rem; padding: 2px 7px;">
                                                        <strong><?php echo esc_html($day); ?>:</strong> <?php echo esc_html($times['start'] ?? '') . ' - ' . esc_html($times['end'] ?? ''); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:right;">
                                        <div style="display:flex; justify-content:flex-end; gap:6px; align-items:center;">
                                            <button type="button" class="ifs-btn-ghost" style="padding:4px 10px; font-size:0.78rem;"
                                                onclick='openEditBatchModal(<?php echo (int)$bd->id; ?>, <?php echo wp_json_encode($bd->batch_name); ?>, <?php echo (float)$bd->course_fee; ?>, <?php echo (int)$bd->teacher_id; ?>, <?php echo wp_json_encode($sched); ?>)'>
                                                Edit
                                            </button>
                                            <?php $del_b_url = wp_nonce_url(add_query_arg(['page' => 'ifs-attendance', 'ifs_action' => 'del_batch_entity', 'batch_id' => $bd->id], admin_url('admin.php')), 'ifs_del_batch_nonce'); ?>
                                            <a href="<?php echo esc_url($del_b_url); ?>" onclick="return confirm('Permanently delete this batch and its schedules?');" style="color:#ef4444; font-weight:700; text-decoration:none; font-size:0.78rem; padding: 4px 10px; border-radius: 6px; background: #fef2f2; border: 1px solid #fecaca; transition: all 0.2s ease;">Delete</a>
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
        <!-- ADD BATCH TAB -->
        <div class="ifs-card" style="max-width: 680px; margin: 0 auto; display: flex; flex-direction: column;">
            <div class="ifs-card-header" style="margin-bottom: 16px;">
                <div>
                    <h3 class="ifs-card-title">Create Batch & Day-wise Routine</h3>
                    <span style="font-size:0.8rem; color:#64748b; font-weight:500;">Set pricing, instructor and toggle active class days</span>
                </div>
            </div>

            <form method="POST" action="<?php echo esc_url(admin_url('admin.php?page=ifs-attendance')); ?>">
                <?php wp_nonce_field('ifs_batch_nonce'); ?>
                <input type="hidden" name="ifs_action" value="add_batch_entity">

                <div class="ifs-field-group">
                    <label>Batch Name *</label>
                    <input type="text" name="batch_name" class="ifs-input" placeholder="e.g. Batch-2026-Physics" required>
                </div>

                <div style="display: flex; gap: 14px;">
                    <div class="ifs-field-group" style="flex: 1;">
                        <label>Monthly Course Fee (<?php echo esc_html($currency_symbol); ?>) *</label>
                        <input type="number" step="0.01" name="course_fee" class="ifs-input" placeholder="4500.00" required>
                    </div>

                    <div class="ifs-field-group" style="flex: 1;">
                        <label>Assigned Lead Teacher</label>
                        <select name="teacher_id" class="ifs-select">
                            <option value="0">-- Select Faculty Staff --</option>
                            <?php foreach ($teachers_map as $t_id => $teacher): ?>
                                <option value="<?php echo esc_attr($t_id); ?>"><?php echo esc_html($teacher->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Day-wise Routine Config with Toggle Switches -->
                <div class="ifs-field-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <label style="margin: 0;">Configure Class Routine & Time Slots</label>
                        <span style="font-size: 0.75rem; color: #64748b;">Toggle switch to activate class days</span>
                    </div>

                    <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                        <?php foreach ($days_list as $day): 
                            $is_default_active = in_array($day, ['Sat', 'Mon', 'Wed'], true);
                        ?>
                            <div class="ifs-day-row">
                                <label class="ifs-day-label">
                                    <div class="ifs-switch">
                                        <input type="checkbox" name="day_active[<?php echo esc_attr($day); ?>]" value="1" class="day-toggle-cb" data-day="<?php echo esc_attr($day); ?>" <?php checked($is_default_active); ?>>
                                        <span class="ifs-slider"></span>
                                    </div>
                                    <span><?php echo esc_html($day); ?></span>
                                </label>
                                <input type="text" name="day_start[<?php echo esc_attr($day); ?>]" id="start-<?php echo esc_attr($day); ?>" value="10:00 AM" class="ifs-day-input" placeholder="Start Time" <?php echo !$is_default_active ? 'disabled' : ''; ?>>
                                <input type="text" name="day_end[<?php echo esc_attr($day); ?>]" id="end-<?php echo esc_attr($day); ?>" value="11:30 AM" class="ifs-day-input" placeholder="End Time" <?php echo !$is_default_active ? 'disabled' : ''; ?>>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" class="ifs-btn" style="width: 100%; margin-top: 6px; padding: 12px;">Create Batch & Routine</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Batch Edit Modal -->
    <div id="batchEditModal" class="ifs-modal">
        <div class="ifs-modal-content ifs-custom-scrollbar">
            <button type="button" class="ifs-modal-close" onclick="closeEditBatchModal()">&times;</button>
            <h3 class="ifs-card-title" style="margin-bottom:18px;">Update Batch & Routine</h3>
            <form method="POST" action="<?php echo esc_url(admin_url('admin.php?page=ifs-attendance')); ?>">
                <?php wp_nonce_field('ifs_edit_batch_nonce'); ?>
                <input type="hidden" name="ifs_action" value="edit_batch_entity">
                <input type="hidden" name="batch_id" id="edit_batch_id">

                <div class="ifs-field-group">
                    <label>Batch Name *</label>
                    <input type="text" name="batch_name" id="edit_batch_name" class="ifs-input" required>
                </div>

                <div style="display: flex; gap: 14px;">
                    <div class="ifs-field-group" style="flex: 1;">
                        <label>Monthly Course Fee (<?php echo esc_html($currency_symbol); ?>) *</label>
                        <input type="number" step="0.01" name="course_fee" id="edit_course_fee" class="ifs-input" required>
                    </div>

                    <div class="ifs-field-group" style="flex: 1;">
                        <label>Assigned Lead Teacher</label>
                        <select name="teacher_id" id="edit_teacher_id" class="ifs-select">
                            <option value="0">-- Select Faculty Staff --</option>
                            <?php foreach ($teachers_map as $t_id => $teacher): ?>
                                <option value="<?php echo esc_attr($t_id); ?>"><?php echo esc_html($teacher->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="ifs-field-group">
                    <label>Configure Class Routine & Time Slots</label>
                    <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                        <?php foreach ($days_list as $day): ?>
                            <div class="ifs-day-row">
                                <label class="ifs-day-label">
                                    <div class="ifs-switch">
                                        <input type="checkbox" name="day_active[<?php echo esc_attr($day); ?>]" value="1" class="edit-day-toggle-cb" data-day="<?php echo esc_attr($day); ?>" id="edit-cb-<?php echo esc_attr($day); ?>">
                                        <span class="ifs-slider"></span>
                                    </div>
                                    <span><?php echo esc_html($day); ?></span>
                                </label>
                                <input type="text" name="day_start[<?php echo esc_attr($day); ?>]" id="edit-start-<?php echo esc_attr($day); ?>" value="10:00 AM" class="ifs-day-input" placeholder="Start Time">
                                <input type="text" name="day_end[<?php echo esc_attr($day); ?>]" id="edit-end-<?php echo esc_attr($day); ?>" value="11:30 AM" class="ifs-day-input" placeholder="End Time">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" class="ifs-btn" style="width:100%; margin-top:10px;">Save Batch Changes</button>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll('.day-toggle-cb').forEach(function(cb) {
            cb.addEventListener('change', function() {
                var day = this.dataset.day;
                var startInput = document.getElementById('start-' + day);
                var endInput = document.getElementById('end-' + day);
                if (startInput && endInput) {
                    startInput.disabled = !this.checked;
                    endInput.disabled = !this.checked;
                    if (this.checked) {
                        startInput.focus();
                    }
                }
            });
        });

        document.querySelectorAll('.edit-day-toggle-cb').forEach(function(cb) {
            cb.addEventListener('change', function() {
                var day = this.dataset.day;
                var startInput = document.getElementById('edit-start-' + day);
                var endInput = document.getElementById('edit-end-' + day);
                if (startInput && endInput) {
                    startInput.disabled = !this.checked;
                    endInput.disabled = !this.checked;
                    if (this.checked) {
                        startInput.focus();
                    }
                }
            });
        });

        function filterBatchTable() {
            var input = document.getElementById('batchSearchInput');
            var filter = input.value.toLowerCase();
            var table = document.getElementById('batchDirectoryTable');
            if (!table) return;
            var tr = table.getElementsByTagName('tr');
            for (var i = 1; i < tr.length; i++) {
                var tdBatch = tr[i].getElementsByTagName('td')[0];
                var tdTeacher = tr[i].getElementsByTagName('td')[3];
                if (tdBatch || tdTeacher) {
                    var txtBatch = tdBatch ? (tdBatch.textContent || tdBatch.innerText) : '';
                    var txtTeacher = tdTeacher ? (tdTeacher.textContent || tdTeacher.innerText) : '';
                    if (txtBatch.toLowerCase().indexOf(filter) > -1 || txtTeacher.toLowerCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }

        function openEditBatchModal(id, name, fee, teacherId, schedule) {
            document.getElementById('edit_batch_id').value = id;
            document.getElementById('edit_batch_name').value = name;
            document.getElementById('edit_course_fee').value = parseFloat(fee).toFixed(2);
            document.getElementById('edit_teacher_id').value = teacherId;

            var days = ['Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
            days.forEach(function(day) {
                var cb = document.getElementById('edit-cb-' + day);
                var start = document.getElementById('edit-start-' + day);
                var end = document.getElementById('edit-end-' + day);
                if (schedule && schedule[day]) {
                    cb.checked = true;
                    start.value = schedule[day].start || '10:00 AM';
                    end.value = schedule[day].end || '11:30 AM';
                    start.disabled = false;
                    end.disabled = false;
                } else {
                    cb.checked = false;
                    start.value = '10:00 AM';
                    end.value = '11:30 AM';
                    start.disabled = true;
                    end.disabled = true;
                }
            });

            document.getElementById('batchEditModal').classList.add('open');
        }

        function closeEditBatchModal() {
            document.getElementById('batchEditModal').classList.remove('open');
        }

        window.addEventListener('click', function(event) {
            var modal = document.getElementById('batchEditModal');
            if (event.target === modal) {
                closeEditBatchModal();
            }
        });
    </script>
    <?php
}