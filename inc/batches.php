<?php
if (!defined('ABSPATH')) exit;

function ifs_erp_render_batches_workspace($wpdb, $table_batches, $teachers_map, $currency_symbol) {
    $all_batches_data = $wpdb->get_results("SELECT * FROM $table_batches ORDER BY id DESC"); 
    $days_list = ['Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
    ?>
    <div style="display: grid; grid-template-columns: 1.1fr 1.9fr; gap: 24px;">
        <div class="ifs-card">
            <h3 class="ifs-card-title">Create Batch with Day-wise Routine</h3>
            <form method="POST" style="margin-top:16px;">
                <?php wp_nonce_field('ifs_batch_nonce'); ?>
                <input type="hidden" name="ifs_action" value="add_batch_entity">

                <div class="ifs-field-group">
                    <label>Batch Name *</label>
                    <input type="text" name="batch_name" class="ifs-input" placeholder="e.g. Batch-2026-Physics" required>
                </div>
                <div class="ifs-field-group">
                    <label>Monthly Course Fee (<?php echo esc_html($currency_symbol); ?>) *</label>
                    <input type="number" step="0.01" name="course_fee" class="ifs-input" placeholder="4500.00" required>
                </div>

                <div class="ifs-field-group">
                    <label>Assigned Lead Teacher</label>
                    <select name="teacher_id" class="ifs-select">
                        <option value="0">-- Select Faculty Staff --</option>
                        <?php foreach ($teachers_map as $t_id => $teacher): ?>
                            <option value="<?php echo esc_attr($t_id); ?>"><?php echo esc_html($teacher->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="ifs-field-group">
                    <label>Configure Day-wise Routine & Time Slots</label>
                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:10px;">
                        <?php foreach ($days_list as $day): ?>
                            <div class="ifs-day-row">
                                <label style="font-size:0.85rem; font-weight:700;">
                                    <input type="checkbox" name="day_active[<?php echo $day; ?>]" value="1" <?php checked(in_array($day, ['Sat', 'Mon', 'Wed'])); ?>>
                                    <?php echo $day; ?>
                                </label>
                                <input type="text" name="day_start[<?php echo $day; ?>]" value="10:00 AM" class="ifs-input" style="padding:6px; font-size:0.82rem;" placeholder="Start">
                                <input type="text" name="day_end[<?php echo $day; ?>]" value="11:30 AM" class="ifs-input" style="padding:6px; font-size:0.82rem;" placeholder="End">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" class="ifs-btn" style="width:100%;">Create Batch & Schedule</button>
            </form>
        </div>

        <div class="ifs-card">
            <h3 class="ifs-card-title">Registered Batches</h3>
            <table class="ifs-table">
                <thead><tr><th>Batch</th><th>Fee</th><th>Teacher</th><th>Day-wise Schedule</th><th style="text-align:right;">Action</th></tr></thead>
                <tbody>
                    <?php foreach ($all_batches_data as $bd): ?>
                        <?php 
                        $t_name = isset($teachers_map[$bd->teacher_id]) ? $teachers_map[$bd->teacher_id]->name : 'Unassigned';
                        $sched = json_decode($bd->schedule_json, true) ?: [];
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html($bd->batch_name); ?></strong></td>
                            <td><?php echo $currency_symbol . ' ' . number_format($bd->course_fee, 2); ?></td>
                            <td><?php echo esc_html($t_name); ?></td>
                            <td>
                                <?php foreach ($sched as $day => $times): ?>
                                    <span class="ifs-tag" style="margin-bottom:2px; display:inline-block;">
                                        <?php echo $day . ': ' . $times['start'] . ' - ' . $times['end']; ?>
                                    </span><br>
                                <?php endforeach; ?>
                            </td>
                            <td style="text-align:right;">
                                <?php $del_b_url = wp_nonce_url(add_query_arg(['page' => 'ifs-attendance', 'ifs_action' => 'del_batch_entity', 'batch_id' => $bd->id], admin_url('admin.php')), 'ifs_del_batch_nonce'); ?>
                                <a href="<?php echo esc_url($del_b_url); ?>" onclick="return confirm('Remove batch?');" style="color:#ef4444; font-weight:700; text-decoration:none; font-size:0.85rem;">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}