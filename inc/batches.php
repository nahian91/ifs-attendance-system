<?php
if (!defined('ABSPATH')) exit;

function ifs_erp_render_batches_workspace($wpdb, $table_batches, $teachers_map, $currency_symbol) {
    $all_batches_data = $wpdb->get_results("SELECT * FROM $table_batches ORDER BY id DESC"); 
    $days_list = ['Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
    ?>
    <style>
        .ifs-day-row {
            display: grid;
            grid-template-columns: 85px 1fr 1fr;
            gap: 10px;
            align-items: center;
            padding: 8px 10px;
            border-bottom: 1px solid #edf2f7;
            transition: background 0.15s ease;
        }
        .ifs-day-row:last-child {
            border-bottom: none;
        }
        .ifs-day-row:hover {
            background: #f1f5f9;
        }
        .ifs-day-label {
            font-size: 0.85rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
            margin: 0;
            color: #334155;
        }
        .ifs-day-input {
            width: 100%;
            padding: 6px 10px;
            border: 1.5px solid #cbd5e1;
            border-radius: 6px;
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

    <div style="display: grid; grid-template-columns: 1.15fr 1.85fr; gap: 24px; align-items: start;">
        <!-- বাম কলাম: ব্যাচ তৈরি ও ডে-ওয়াইজ শিডিউলার -->
        <div class="ifs-card" style="display: flex; flex-direction: column;">
            <div class="ifs-card-header" style="margin-bottom: 16px;">
                <div>
                    <h3 class="ifs-card-title">Create Batch & Routine</h3>
                    <span style="font-size:0.8rem; color:#64748b; font-weight:500;">Set pricing, instructor & custom day-wise slots</span>
                </div>
            </div>

            <form method="POST">
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

                <!-- ডে-ওয়াইজ রুটিন কনফিগারেশন -->
                <div class="ifs-field-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <label style="margin: 0;">Configure Class Routine & Time Slots</label>
                        <span style="font-size: 0.75rem; color: #64748b;">Tick active days</span>
                    </div>

                    <div class="ifs-custom-scrollbar" style="max-height: 250px; overflow-y: auto; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 4px 8px;">
                        <?php foreach ($days_list as $day): 
                            $is_default_active = in_array($day, ['Sat', 'Mon', 'Wed']);
                        ?>
                            <div class="ifs-day-row">
                                <label class="ifs-day-label">
                                    <input type="checkbox" name="day_active[<?php echo esc_attr($day); ?>]" value="1" class="day-toggle-cb" data-day="<?php echo esc_attr($day); ?>" <?php checked($is_default_active); ?> style="cursor: pointer;">
                                    <?php echo esc_html($day); ?>
                                </label>
                                <input type="text" name="day_start[<?php echo esc_attr($day); ?>]" id="start-<?php echo esc_attr($day); ?>" value="10:00 AM" class="ifs-day-input" placeholder="Start Time" <?php echo !$is_default_active ? 'disabled' : ''; ?>>
                                <input type="text" name="day_end[<?php echo esc_attr($day); ?>]" id="end-<?php echo esc_attr($day); ?>" value="11:30 AM" class="ifs-day-input" placeholder="End Time" <?php echo !$is_default_active ? 'disabled' : ''; ?>>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" class="ifs-btn" style="width: 100%; margin-top: 6px; padding: 11px;">Create Batch & Schedule</button>
            </form>
        </div>

        <!-- ডান কলাম: নিবন্ধিত ব্যাচসমূহের তালিকা -->
        <div class="ifs-card" style="display: flex; flex-direction: column;">
            <div class="ifs-card-header" style="margin-bottom: 16px;">
                <div>
                    <h3 class="ifs-card-title">Configured Batches (<?php echo count($all_batches_data); ?>)</h3>
                    <span style="font-size:0.8rem; color:#64748b; font-weight:500;">Active classes & assigned schedules</span>
                </div>
                <input type="text" id="batchSearchInput" placeholder="🔍 Search batch..." onkeyup="filterBatchTable()" style="padding: 6px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.82rem; outline: none; font-family: inherit; background:#ffffff; color:#0f172a;">
            </div>

            <?php if (empty($all_batches_data)): ?>
                <div style="background:#f8fafc; border:2px dashed #cbd5e1; padding:48px 24px; border-radius:14px; text-align:center; margin:10px 0;">
                    <p style="margin:0; font-weight:700; color:#64748b; font-size:0.95rem;">No batches created yet.</p>
                </div>
            <?php else: ?>
                <div class="ifs-custom-scrollbar" style="max-height: 520px; overflow-y: auto; padding-right: 6px; border: 1.5px solid #f1f5f9; border-radius: 12px;">
                    <table class="ifs-table" id="batchDirectoryTable">
                        <thead style="position: sticky; top: 0; z-index: 5; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                            <tr>
                                <th style="background:#f8fafc; padding: 14px 16px;">Batch</th>
                                <th style="background:#f8fafc; padding: 14px 16px;">Course Fee</th>
                                <th style="background:#f8fafc; padding: 14px 16px;">Teacher</th>
                                <th style="background:#f8fafc; padding: 14px 16px;">Routine Slots</th>
                                <th style="background:#f8fafc; text-align:right; padding: 14px 16px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_batches_data as $bd): ?>
                                <?php 
                                $t_name = isset($teachers_map[$bd->teacher_id]) ? $teachers_map[$bd->teacher_id]->name : 'Unassigned';
                                $sched  = json_decode($bd->schedule_json, true) ?: [];
                                ?>
                                <tr style="transition: background 0.15s ease;">
                                    <td>
                                        <strong style="color: #0f172a; font-size: 0.95rem;"><?php echo esc_html($bd->batch_name); ?></strong><br>
                                        <span style="font-size: 0.75rem; color: #64748b; font-family:'JetBrains Mono', monospace;">ID: #<?php echo esc_html($bd->id); ?></span>
                                    </td>
                                    <td>
                                        <strong style="color: #0284c7;"><?php echo esc_html($currency_symbol) . ' ' . esc_html(number_format($bd->course_fee, 2)); ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($t_name !== 'Unassigned'): ?>
                                            <span style="font-weight: 600; color: #334155;"><?php echo esc_html($t_name); ?></span>
                                        <?php else: ?>
                                            <span style="color: #94a3b8; font-style: italic;">Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (empty($sched)): ?>
                                            <span style="color: #94a3b8; font-size: 0.8rem;">No slots configured</span>
                                        <?php else: ?>
                                            <div style="display: flex; flex-direction: column; gap: 3px;">
                                                <?php foreach ($sched as $day => $times): ?>
                                                    <span class="ifs-tag" style="background:#f0fdf4; border-color:#bbf7d0; color:#15803d; font-size: 0.72rem; padding: 2px 6px; width: fit-content;">
                                                        <strong><?php echo esc_html($day); ?>:</strong> <?php echo esc_html($times['start']) . ' - ' . esc_html($times['end']); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:right;">
                                        <div style="display:flex; justify-content:flex-end; gap:6px; align-items:center;">
                                            <button type="button" class="ifs-btn-ghost" style="padding:4px 8px; font-size:0.78rem;"
                                                onclick='openEditBatchModal(<?php echo (int)$bd->id; ?>, <?php echo json_encode($bd->batch_name); ?>, <?php echo (float)$bd->course_fee; ?>, <?php echo (int)$bd->teacher_id; ?>, <?php echo json_encode($sched); ?>)'>
                                                Edit
                                            </button>
                                            <?php $del_b_url = wp_nonce_url(add_query_arg(['page' => 'ifs-attendance', 'ifs_action' => 'del_batch_entity', 'batch_id' => $bd->id], admin_url('admin.php')), 'ifs_del_batch_nonce'); ?>
                                            <a href="<?php echo esc_url($del_b_url); ?>" onclick="return confirm('Permanently delete this batch and its schedules?');" style="color:#ef4444; font-weight:700; text-decoration:none; font-size:0.78rem; padding: 4px 8px; border-radius: 6px; background: #fef2f2; border: 1px solid #fecaca; transition: all 0.2s ease;">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ব্যাচ এডিট Modal -->
    <div id="batchEditModal" class="ifs-modal">
        <div class="ifs-modal-content ifs-custom-scrollbar">
            <button type="button" class="ifs-modal-close" onclick="closeEditBatchModal()">&times;</button>
            <h3 class="ifs-card-title" style="margin-bottom:18px;">Update Batch & Routine</h3>
            <form method="POST">
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
                    <div class="ifs-custom-scrollbar" style="max-height: 220px; overflow-y: auto; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 4px 8px;">
                        <?php foreach ($days_list as $day): ?>
                            <div class="ifs-day-row">
                                <label class="ifs-day-label">
                                    <input type="checkbox" name="day_active[<?php echo esc_attr($day); ?>]" value="1" class="edit-day-toggle-cb" data-day="<?php echo esc_attr($day); ?>" id="edit-cb-<?php echo esc_attr($day); ?>">
                                    <?php echo esc_html($day); ?>
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
        document.querySelectorAll('.day-toggle-cb').forEach(cb => {
            cb.addEventListener('change', function() {
                const day = this.dataset.day;
                const startInput = document.getElementById('start-' + day);
                const endInput = document.getElementById('end-' + day);
                if (startInput && endInput) {
                    startInput.disabled = !this.checked;
                    endInput.disabled = !this.checked;
                    if (this.checked) {
                        startInput.focus();
                    }
                }
            });
        });

        document.querySelectorAll('.edit-day-toggle-cb').forEach(cb => {
            cb.addEventListener('change', function() {
                const day = this.dataset.day;
                const startInput = document.getElementById('edit-start-' + day);
                const endInput = document.getElementById('edit-end-' + day);
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
            const input = document.getElementById('batchSearchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('batchDirectoryTable');
            if (!table) return;
            const tr = table.getElementsByTagName('tr');
            for (let i = 1; i < tr.length; i++) {
                let tdBatch = tr[i].getElementsByTagName('td')[0];
                let tdTeacher = tr[i].getElementsByTagName('td')[2];
                if (tdBatch || tdTeacher) {
                    let txtBatch = tdBatch ? (tdBatch.textContent || tdBatch.innerText) : '';
                    let txtTeacher = tdTeacher ? (tdTeacher.textContent || tdTeacher.innerText) : '';
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

            const days = ['Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
            days.forEach(day => {
                const cb = document.getElementById('edit-cb-' + day);
                const start = document.getElementById('edit-start-' + day);
                const end = document.getElementById('edit-end-' + day);
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
            document.getElementById('batchEditModal').classList.remove('remove');
            document.getElementById('batchEditModal').classList.remove('open');
        }

        window.addEventListener('click', function(event) {
            const modal = document.getElementById('batchEditModal');
            if (event.target === modal) {
                closeEditBatchModal();
            }
        });
    </script>
    <?php
}