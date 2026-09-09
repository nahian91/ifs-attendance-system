<?php
if (!defined('ABSPATH')) exit;

function ifs_erp_render_notices_workspace($wpdb, $table_notices, $available_batches) {
    $all_notices = $wpdb->get_results("SELECT * FROM $table_notices ORDER BY id DESC");
    ?>
    <div style="display: grid; grid-template-columns: 1.15fr 1.85fr; gap: 24px; align-items: start;">
        <!-- বাম কলাম: নোটিশ পাবলিশ ফর্ম -->
        <div class="ifs-card" style="display: flex; flex-direction: column;">
            <div class="ifs-card-header" style="margin-bottom: 16px;">
                <div>
                    <h3 class="ifs-card-title">Publish Notice</h3>
                    <span style="font-size: 0.8rem; color: #64748b; font-weight: 500;">Broadcast announcements to batches or portal-wide</span>
                </div>
            </div>

            <form method="POST">
                <?php wp_nonce_field('ifs_notice_nonce'); ?>
                <input type="hidden" name="ifs_action" value="add_notice_entity">

                <div class="ifs-field-group">
                    <label>Notice Subject / Title *</label>
                    <input type="text" name="notice_title" class="ifs-input" placeholder="e.g. Schedule Change / Upcoming Exam" required>
                </div>

                <div class="ifs-field-group">
                    <label>Target Audience (Batch)</label>
                    <select name="target_batch" class="ifs-select">
                        <option value="All">All Batches (Public Broadcast)</option>
                        <?php foreach ($available_batches as $b): ?>
                            <option value="<?php echo esc_attr($b); ?>"><?php echo esc_html($b); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="ifs-field-group">
                    <label>Notice Message Body *</label>
                    <textarea name="notice_desc" rows="5" class="ifs-textarea" placeholder="Write announcement details here..." style="line-height: 1.5; resize: vertical;" required></textarea>
                </div>

                <button type="submit" class="ifs-btn" style="width: 100%; margin-top: 6px; padding: 11px;">Broadcast Notice</button>
            </form>
        </div>

        <!-- ডান কলাম: নোটিশ ফিড (স্লিম কাস্টম স্ক্রলবার ও ফিল্টার সহ) -->
        <div class="ifs-card" style="display: flex; flex-direction: column;">
            <div class="ifs-card-header" style="margin-bottom: 16px;">
                <div>
                    <h3 class="ifs-card-title">Notice Feed (<?php echo count($all_notices); ?>)</h3>
                    <span style="font-size: 0.8rem; color: #64748b; font-weight: 500;">Active broadcasts and updates</span>
                </div>
                <input type="text" id="noticeSearchInput" placeholder="🔍 Search notices..." onkeyup="filterNoticeFeed()" style="padding: 6px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.82rem; outline: none; font-family: inherit;">
            </div>

            <?php if (empty($all_notices)): ?>
                <div style="background: #f8fafc; border: 2px dashed #cbd5e1; padding: 48px 24px; border-radius: 14px; text-align: center; margin: 10px 0;">
                    <p style="margin: 0; font-weight: 700; color: #64748b; font-size: 0.95rem;">No notices broadcasted yet.</p>
                </div>
            <?php else: ?>
                <div id="noticeFeedContainer" class="ifs-custom-scrollbar" style="max-height: 520px; overflow-y: auto; padding-right: 6px; display: flex; flex-direction: column; gap: 14px;">
                    <?php foreach ($all_notices as $not): 
                        $is_all = $not->target_batch === 'All';
                        $batch_badge_style = $is_all ? 'background:#e0f2fe; border-color:#bae6fd; color:#0369a1;' : 'background:#fef3c7; border-color:#fde68a; color:#b45309;';
                    ?>
                        <div class="notice-feed-card" style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 18px; transition: transform 0.2s ease, box-shadow 0.2s ease;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 14px rgba(0, 0, 0, 0.05)';" onmouseout="this.style.transform='none';this.style.boxShadow='none';">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px;">
                                <div style="flex: 1;">
                                    <h4 class="notice-item-title" style="margin: 0 0 6px; font-size: 1.05rem; color: #0f172a; font-weight: 800; line-height: 1.3;">
                                        <?php echo esc_html($not->title); ?>
                                    </h4>
                                    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                                        <span class="ifs-tag notice-item-batch" style="<?php echo esc_attr($batch_badge_style); ?> font-size: 0.72rem; padding: 2px 8px;">
                                            <?php echo esc_html($not->target_batch); ?>
                                        </span>
                                        <span style="font-size: 0.75rem; color: #64748b; font-family: 'JetBrains Mono', monospace;">
                                            📅 <?php echo esc_html(date('M d, Y · h:i A', strtotime($not->created_at))); ?>
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <?php $del_n_url = wp_nonce_url(add_query_arg(['page' => 'ifs-attendance', 'ifs_action' => 'del_notice_entity', 'notice_id' => $not->id], admin_url('admin.php')), 'ifs_del_notice_nonce'); ?>
                                    <a href="<?php echo esc_url($del_n_url); ?>" onclick="return confirm('Permanently delete this notice broadcast?');" style="color: #ef4444; font-weight: 700; text-decoration: none; font-size: 0.78rem; padding: 4px 8px; border-radius: 6px; background: #fef2f2; border: 1px solid #fecaca; transition: all 0.2s ease;">Delete</a>
                                </div>
                            </div>
                            <p class="notice-item-desc" style="margin: 12px 0 0; color: #334155; line-height: 1.6; font-size: 0.9rem; white-space: pre-line; border-top: 1px solid #edf2f7; padding-top: 10px;">
                                <?php echo esc_html($not->description); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function filterNoticeFeed() {
            const input = document.getElementById('noticeSearchInput');
            const filter = input.value.toLowerCase();
            const cards = document.querySelectorAll('.notice-feed-card');
            
            cards.forEach(card => {
                const title = card.querySelector('.notice-item-title')?.innerText.toLowerCase() || '';
                const batch = card.querySelector('.notice-item-batch')?.innerText.toLowerCase() || '';
                const desc = card.querySelector('.notice-item-desc')?.innerText.toLowerCase() || '';

                if (title.indexOf(filter) > -1 || batch.indexOf(filter) > -1 || desc.indexOf(filter) > -1) {
                    card.style.display = "";
                } else {
                    card.style.display = "none";
                }
            });
        }
    </script>
    <?php
}