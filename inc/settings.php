<?php
if (!defined('ABSPATH')) exit;

function ifs_erp_render_settings_workspace($wpdb, $table_meta, $sub_tab, $portal_name, $currency_symbol) {
    $all_insts = $wpdb->get_results("SELECT * FROM $table_meta WHERE meta_type = 'institution' ORDER BY id DESC");
    ?>
    <nav class="ifs-subtabs-nav">
        <a href="<?php echo esc_url(admin_url('admin.php?page=ifs-attendance&page_view=settings&sub_tab=institutions')); ?>" class="ifs-subtab-btn <?php echo ($sub_tab === 'institutions') ? 'active' : ''; ?>">🏛️ Institutions</a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=ifs-attendance&page_view=settings&sub_tab=preferences')); ?>" class="ifs-subtab-btn <?php echo ($sub_tab === 'preferences') ? 'active' : ''; ?>">⚙️ System Config</a>
    </nav>

    <?php if ($sub_tab === 'institutions'): ?>
        <div class="ifs-card" style="max-width: 680px;">
            <h3 class="ifs-card-title">Manage Registered Institutions</h3>
            <form method="POST" style="margin-bottom: 24px;">
                <?php wp_nonce_field('ifs_setting_nonce'); ?>
                <input type="hidden" name="ifs_action" value="add_setting_meta">
                <input type="hidden" name="meta_type" value="institution">
                <div style="display:flex; gap:10px;">
                    <input type="text" name="meta_value" class="ifs-input" placeholder="e.g. Comilla Victoria College" required>
                    <button type="submit" class="ifs-btn" style="white-space:nowrap;">+ Add</button>
                </div>
            </form>
            <table class="ifs-table">
                <thead><tr><th>Institution Name</th><th style="text-align:right;">Action</th></tr></thead>
                <tbody>
                    <?php foreach ($all_insts as $ins): ?>
                        <tr>
                            <td><strong><?php echo esc_html($ins->meta_value); ?></strong></td>
                            <td style="text-align:right;">
                                <?php $del_i_url = wp_nonce_url(add_query_arg(['page' => 'ifs-attendance', 'ifs_action' => 'del_setting_meta', 'meta_id' => $ins->id], admin_url('admin.php')), 'ifs_del_setting_nonce'); ?>
                                <a href="<?php echo esc_url($del_i_url); ?>" onclick="return confirm('Delete institution?');" style="color:#ef4444; font-weight:700; text-decoration:none; font-size:0.85rem;">Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php elseif ($sub_tab === 'preferences'): ?>
        <div class="ifs-card" style="max-width: 580px;">
            <h3 class="ifs-card-title">System Configurations</h3>
            <form method="POST">
                <?php wp_nonce_field('ifs_gen_setting_nonce'); ?>
                <input type="hidden" name="ifs_action" value="save_general_settings">
                <div class="ifs-field-group">
                    <label>System Portal Name</label>
                    <input type="text" name="ifs_portal_name" value="<?php echo esc_attr($portal_name); ?>" class="ifs-input" required>
                </div>
                <div class="ifs-field-group">
                    <label>Currency Symbol (e.g. ৳, $, €)</label>
                    <input type="text" name="ifs_currency_symbol" value="<?php echo esc_attr($currency_symbol); ?>" class="ifs-input" style="max-width:120px;" required>
                </div>
                <button type="submit" class="ifs-btn">Save Configurations</button>
            </form>
        </div>
    <?php endif;
}