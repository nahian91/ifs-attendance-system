<?php
if (!defined('ABSPATH')) exit;

function ifs_erp_render_notices_workspace($wpdb, $table_notices, $available_batches) {
    $all_notices = $wpdb->get_results("SELECT * FROM $table_notices ORDER BY id DESC");
    ?>
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
        <div class="ifs-card">
            <h3 class="ifs-card-title">Publish Notice</h3>
            <form method="POST" style="margin-top:16px;">
                <?php wp_nonce_field('ifs_notice_nonce'); ?>
                <input type="hidden" name="ifs_action" value="add_notice_entity">
                <div class="ifs-field-group">
                    <label>Subject *</label>
                    <input type="text" name="notice_title" class="ifs-input" required>
                </div>
                <div class="ifs-field-group">
                    <label>Batch</label>
                    <select name="target_batch" class="ifs-select">
                        <option value="All">All Batches</option>
                        <?php foreach ($available_batches as $b): ?>
                            <option value="<?php echo esc_attr($b); ?>"><?php echo esc_html($b); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="ifs-field-group">
                    <label>Description *</label>
                    <textarea name="notice_desc" rows="4" class="ifs-textarea" required></textarea>
                </div>
                <button type="submit" class="ifs-btn" style="width:100%;">Broadcast</button>
            </form>
        </div>

        <div class="ifs-card">
            <h3 class="ifs-card-title">Notice Feed</h3>
            <?php if (empty($all_notices)): ?>
                <p style="color:#94a3b8; text-align:center; padding:30px;">No notices published yet.</p>
            <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:16px;">
                    <?php foreach ($all_notices as $not): ?>
                        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:18px;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                                <div>
                                    <h4 style="margin:0 0 4px; font-size:1.05rem; color:#0f172a;"><?php echo esc_html($not->title); ?></h4>
                                    <span class="ifs-tag"><?php echo esc_html($not->target_batch); ?></span>
                                    <span style="font-size:0.8rem; color:#64748b; margin-left:8px;"><?php echo date('M d, Y h:i A', strtotime($not->created_at)); ?></span>
                                </div>
                                <div>
                                    <?php $del_n_url = wp_nonce_url(add_query_arg(['page' => 'ifs-attendance', 'ifs_action' => 'del_notice_entity', 'notice_id' => $not->id], admin_url('admin.php')), 'ifs_del_notice_nonce'); ?>
                                    <a href="<?php echo esc_url($del_n_url); ?>" onclick="return confirm('Delete notice?');" style="color:#ef4444; font-weight:700; text-decoration:none; font-size:0.8rem;">Delete</a>
                                </div>
                            </div>
                            <p style="margin:12px 0 0; color:#334155; line-height:1.6; font-size:0.92rem;"><?php echo nl2br(esc_html($not->description)); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}