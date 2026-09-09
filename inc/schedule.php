<?php
if (!defined('ABSPATH')) exit;

function ifs_erp_render_schedule_workspace($wpdb, $table_holidays) {
    $holidays_list = $wpdb->get_results("SELECT * FROM $table_holidays ORDER BY holiday_date DESC");
    ?>
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
        <div class="ifs-card">
            <h3 class="ifs-card-title">Mark Holiday</h3>
            <form method="POST" style="margin-top:16px;">
                <?php wp_nonce_field('ifs_holiday_nonce'); ?>
                <input type="hidden" name="ifs_action" value="add_holiday_entity">
                <div class="ifs-field-group">
                    <label>Holiday Date *</label>
                    <input type="date" name="holiday_date" class="ifs-input" required>
                </div>
                <div class="ifs-field-group">
                    <label>Title *</label>
                    <input type="text" name="holiday_title" class="ifs-input" placeholder="e.g. Eid Vacation" required>
                </div>
                <div class="ifs-field-group">
                    <label>Type</label>
                    <select name="holiday_type" class="ifs-select">
                        <option value="Official">Official Holiday</option>
                        <option value="Weekend">Weekend Break</option>
                    </select>
                </div>
                <button type="submit" class="ifs-btn" style="width:100%;">Add to Calendar</button>
            </form>
        </div>

        <div class="ifs-card">
            <h3 class="ifs-card-title">Official Holidays</h3>
            <table class="ifs-table">
                <thead><tr><th>Date</th><th>Occasion</th><th>Type</th><th style="text-align:right;">Action</th></tr></thead>
                <tbody>
                    <?php foreach ($holidays_list as $hl): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($hl->holiday_date)); ?></td>
                            <td><strong><?php echo esc_html($hl->title); ?></strong></td>
                            <td><span class="ifs-tag"><?php echo esc_html($hl->holiday_type); ?></span></td>
                            <td style="text-align:right;">
                                <?php $del_h_url = wp_nonce_url(add_query_arg(['page' => 'ifs-attendance', 'ifs_action' => 'del_holiday_entity', 'holiday_id' => $hl->id], admin_url('admin.php')), 'ifs_del_holiday_nonce'); ?>
                                <a href="<?php echo esc_url($del_h_url); ?>" onclick="return confirm('Delete holiday?');" style="color:#ef4444; font-weight:700; text-decoration:none; font-size:0.85rem;">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}