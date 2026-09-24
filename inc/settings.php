<?php
if (!defined('ABSPATH')) {
    exit;
}

function ifs_erp_render_settings_workspace($wpdb, $table_meta, $sub_tab, $portal_name, $currency_symbol) {
    $receipt_prefix = get_option('ifs_receipt_prefix', 'REC-');
    $all_insts      = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$table_meta} WHERE meta_type = %s ORDER BY id DESC",
        'institution'
    ));
    ?>
    <nav class="ifs-subtabs-nav">
        <a href="<?php echo esc_url(admin_url('admin.php?page=ifs-attendance&page_view=settings&sub_tab=institutions')); ?>" class="ifs-subtab-btn <?php echo ($sub_tab === 'institutions') ? 'active' : ''; ?>">🏛️ Institutions</a>
        <a href="<?php echo esc_url(admin_url('admin.php?page=ifs-attendance&page_view=settings&sub_tab=preferences')); ?>" class="ifs-subtab-btn <?php echo ($sub_tab === 'preferences') ? 'active' : ''; ?>">⚙️ System Config</a>
    </nav>

    <?php if ($sub_tab === 'institutions'): ?>
        <div class="ifs-card" style="max-width: 720px; display: flex; flex-direction: column;">
            <div class="ifs-card-header" style="margin-bottom: 16px;">
                <div>
                    <h3 class="ifs-card-title">Manage Registered Institutions (<?php echo count($all_insts); ?>)</h3>
                    <span style="font-size: 0.8rem; color: #64748b; font-weight: 500;">Institutions displayed across student registration profiles</span>
                </div>
            </div>

            <form method="POST" action="<?php echo esc_url(admin_url('admin.php?page=ifs-attendance')); ?>" style="margin-bottom: 20px;">
                <?php wp_nonce_field('ifs_setting_nonce'); ?>
                <input type="hidden" name="ifs_action" value="add_setting_meta">
                <input type="hidden" name="meta_type" value="institution">
                
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="text" name="meta_value" class="ifs-input" placeholder="e.g. Comilla Victoria Govt College" style="flex: 1;" required>
                    <button type="submit" class="ifs-btn" style="padding: 10px 20px; white-space: nowrap;">+ Add Institution</button>
                </div>
            </form>

            <?php if (empty($all_insts)): ?>
                <div style="background: #f8fafc; border: 2px dashed #cbd5e1; padding: 40px 24px; border-radius: 12px; text-align: center;">
                    <p style="margin: 0; font-weight: 700; color: #64748b; font-size: 0.92rem;">No institutions registered yet.</p>
                </div>
            <?php else: ?>
                <div class="ifs-custom-scrollbar" style="max-height: 480px; overflow-y: auto; padding-right: 6px; border: 1px solid #f1f5f9; border-radius: 12px;">
                    <table class="ifs-table">
                        <thead style="position: sticky; top: 0; z-index: 5; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                            <tr>
                                <th style="background: #f8fafc; padding: 14px 16px;">Institution Name</th>
                                <th style="background: #f8fafc; text-align: right; padding: 14px 16px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_insts as $ins): ?>
                                <tr style="transition: background 0.15s ease;">
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span style="font-size: 1rem;">🏛️</span>
                                            <strong style="color: #1e293b; font-size: 0.92rem;"><?php echo esc_html($ins->meta_value); ?></strong>
                                        </div>
                                    </td>
                                    <td style="text-align: right;">
                                        <?php $del_i_url = wp_nonce_url(add_query_arg(['page' => 'ifs-attendance', 'ifs_action' => 'del_setting_meta', 'meta_id' => $ins->id], admin_url('admin.php')), 'ifs_del_setting_nonce'); ?>
                                        <a href="<?php echo esc_url($del_i_url); ?>" onclick="return confirm('Remove this institution? Enrolled students will retain their existing value.');" style="color: #ef4444; font-weight: 700; text-decoration: none; font-size: 0.78rem; padding: 4px 8px; border-radius: 6px; background: #fef2f2; border: 1px solid #fecaca; transition: all 0.2s ease;">Remove</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    <?php elseif ($sub_tab === 'preferences'): ?>
        <div class="ifs-card" style="max-width: 620px; display: flex; flex-direction: column;">
            <div class="ifs-card-header" style="margin-bottom: 18px;">
                <div>
                    <h3 class="ifs-card-title">System Configurations</h3>
                    <span style="font-size: 0.8rem; color: #64748b; font-weight: 500;">General portal branding, receipt numbering & global currency</span>
                </div>
            </div>

            <form method="POST" action="<?php echo esc_url(admin_url('admin.php?page=ifs-attendance')); ?>">
                <?php wp_nonce_field('ifs_gen_setting_nonce'); ?>
                <input type="hidden" name="ifs_action" value="save_general_settings">

                <div class="ifs-field-group">
                    <label>System / Academy Portal Name *</label>
                    <input type="text" name="ifs_portal_name" value="<?php echo esc_attr($portal_name); ?>" class="ifs-input" placeholder="e.g. IFS Academic ERP" required>
                    <span style="font-size: 0.75rem; color: #64748b; margin-top: 4px; display: block;">Displays on printed receipts, login forms, and top navigational headers.</span>
                </div>

                <div style="display: flex; gap: 14px;">
                    <div class="ifs-field-group" style="flex: 1;">
                        <label>Currency Symbol *</label>
                        <input type="text" name="ifs_currency_symbol" value="<?php echo esc_attr($currency_symbol); ?>" class="ifs-input" placeholder="৳, $, €" required>
                    </div>

                    <div class="ifs-field-group" style="flex: 1;">
                        <label>Receipt Number Prefix *</label>
                        <input type="text" name="ifs_receipt_prefix" value="<?php echo esc_attr($receipt_prefix); ?>" class="ifs-input" placeholder="REC-" required>
                    </div>
                </div>

                <button type="submit" class="ifs-btn" style="width: 100%; margin-top: 8px; padding: 11px;">Save Configurations</button>
            </form>
        </div>
    <?php endif;
}