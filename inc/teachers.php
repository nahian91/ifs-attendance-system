<?php
if (!defined('ABSPATH')) exit;

function ifs_erp_render_teachers_workspace($wpdb, $table_teachers, $currency_symbol) {
    $all_teachers_data = $wpdb->get_results("SELECT * FROM $table_teachers ORDER BY id DESC");
    ?>
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
        <div class="ifs-card">
            <h3 class="ifs-card-title">Enroll Faculty Member</h3>
            <form method="POST" style="margin-top:16px;">
                <?php wp_nonce_field('ifs_teacher_nonce'); ?>
                <input type="hidden" name="ifs_action" value="add_teacher_entity">

                <div class="ifs-field-group">
                    <label>Profile Picture</label>
                    <div class="ifs-media-box">
                        <img id="teach_photo_preview" src="https://via.placeholder.com/100" class="ifs-media-preview">
                        <div>
                            <input type="hidden" name="teacher_photo_url" id="teach_photo_url">
                            <button type="button" class="ifs-btn-ghost" onclick="openMediaUploader('teach_photo_url', 'teach_photo_preview')">Select Photo</button>
                        </div>
                    </div>
                </div>

                <div class="ifs-field-group">
                    <label>Full Name *</label>
                    <input type="text" name="teacher_name" class="ifs-input" placeholder="e.g. Dr. Mahbubur Rahman" required>
                </div>
                <div class="ifs-field-group">
                    <label>Designation *</label>
                    <input type="text" name="teacher_designation" class="ifs-input" placeholder="e.g. Senior Lecturer" required>
                </div>
                <div class="ifs-field-group">
                    <label>Phone *</label>
                    <input type="text" name="teacher_phone" class="ifs-input" placeholder="017XXXXXXXX" required>
                </div>
                <div class="ifs-field-group">
                    <label>Salary (<?php echo esc_html($currency_symbol); ?>)</label>
                    <input type="number" step="0.01" name="teacher_salary" class="ifs-input" placeholder="30000.00">
                </div>
                <button type="submit" class="ifs-btn" style="width:100%;">Register Teacher</button>
            </form>
        </div>

        <div class="ifs-card">
            <h3 class="ifs-card-title">Faculty Directory (<?php echo count($all_teachers_data); ?>)</h3>
            <table class="ifs-table">
                <thead><tr><th>Faculty Member</th><th>Designation</th><th>Contact</th><th>Salary</th><th style="text-align:right;">Action</th></tr></thead>
                <tbody>
                    <?php foreach ($all_teachers_data as $td): ?>
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center;">
                                    <img src="<?php echo esc_url($td->photo_url ?: 'https://via.placeholder.com/60'); ?>" class="ifs-avatar-sm">
                                    <strong><?php echo esc_html($td->name); ?></strong>
                                </div>
                            </td>
                            <td><span class="ifs-tag"><?php echo esc_html($td->designation); ?></span></td>
                            <td><?php echo esc_html($td->phone); ?></td>
                            <td><?php echo $currency_symbol . ' ' . number_format($td->salary, 2); ?></td>
                            <td style="text-align:right;">
                                <?php $del_t_url = wp_nonce_url(add_query_arg(['page' => 'ifs-attendance', 'ifs_action' => 'del_teacher_entity', 'teacher_id' => $td->id], admin_url('admin.php')), 'ifs_del_teacher_nonce'); ?>
                                <a href="<?php echo esc_url($del_t_url); ?>" onclick="return confirm('Delete teacher?');" style="color:#ef4444; font-weight:700; text-decoration:none; font-size:0.85rem;">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}