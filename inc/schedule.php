<?php
if (!defined('ABSPATH')) {
    exit;
}

function ifs_erp_render_schedule_workspace($wpdb, $table_holidays) {
    $holidays_list = $wpdb->get_results("SELECT * FROM {$table_holidays} ORDER BY holiday_date DESC");
    $today_str     = current_time('Y-m-d');
    ?>
    <div style="display: grid; grid-template-columns: 1.1fr 1.9fr; gap: 24px; align-items: start;">
        <!-- Left Column: Add Holiday Form -->
        <div class="ifs-card" style="display: flex; flex-direction: column;">
            <div class="ifs-card-header" style="margin-bottom: 16px;">
                <div>
                    <h3 class="ifs-card-title">Mark Holiday / Break</h3>
                    <span style="font-size: 0.8rem; color: #64748b; font-weight: 500;">Add official days off to sync academic calendar</span>
                </div>
            </div>

            <form method="POST" action="<?php echo esc_url(admin_url('admin.php?page=ifs-attendance')); ?>">
                <?php wp_nonce_field('ifs_holiday_nonce'); ?>
                <input type="hidden" name="ifs_action" value="add_holiday_entity">

                <div class="ifs-field-group">
                    <label>Holiday Date *</label>
                    <input type="date" name="holiday_date" class="ifs-input" value="<?php echo esc_attr($today_str); ?>" required>
                </div>

                <div class="ifs-field-group">
                    <label>Holiday Occasion / Title *</label>
                    <input type="text" name="holiday_title" class="ifs-input" placeholder="e.g. Eid Vacation / National Holiday" required>
                </div>

                <div class="ifs-field-group">
                    <label>Holiday Classification</label>
                    <select name="holiday_type" class="ifs-select">
                        <option value="Official">Official Public Holiday</option>
                        <option value="Weekend">Weekend Break</option>
                        <option value="Special">Special Academy Break</option>
                    </select>
                </div>

                <button type="submit" class="ifs-btn" style="width: 100%; margin-top: 6px; padding: 11px;">Add to Calendar</button>
            </form>
        </div>

        <!-- Right Column: Official Holidays Directory -->
        <div class="ifs-card" style="display: flex; flex-direction: column;">
            <div class="ifs-card-header" style="margin-bottom: 16px;">
                <div>
                    <h3 class="ifs-card-title">Official Holidays (<?php echo count($holidays_list); ?>)</h3>
                    <span style="font-size: 0.8rem; color: #64748b; font-weight: 500;">Recorded non-academic dates</span>
                </div>
                <input type="text" id="holidaySearchInput" placeholder="🔍 Search holiday..." onkeyup="filterHolidayTable()" style="padding: 6px 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.82rem; outline: none; font-family: inherit;">
            </div>

            <?php if (empty($holidays_list)): ?>
                <div style="background: #f8fafc; border: 2px dashed #cbd5e1; padding: 48px 24px; border-radius: 14px; text-align: center; margin: 10px 0;">
                    <p style="margin: 0; font-weight: 700; color: #64748b; font-size: 0.95rem;">No holidays marked in the calendar yet.</p>
                </div>
            <?php else: ?>
                <div class="ifs-custom-scrollbar" style="max-height: 480px; overflow-y: auto; padding-right: 6px; border: 1px solid #f1f5f9; border-radius: 12px;">
                    <table class="ifs-table" id="holidayDirectoryTable">
                        <thead style="position: sticky; top: 0; z-index: 5; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                            <tr>
                                <th style="background: #f8fafc; padding: 14px 16px;">Date</th>
                                <th style="background: #f8fafc; padding: 14px 16px;">Occasion / Event</th>
                                <th style="background: #f8fafc; padding: 14px 16px;">Type</th>
                                <th style="background: #f8fafc; text-align: right; padding: 14px 16px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($holidays_list as $hl): 
                                $is_past = strtotime($hl->holiday_date) < strtotime($today_str);
                                $type_class = ($hl->holiday_type === 'Official') ? 'background:#fee2e2; border-color:#fecaca; color:#b91c1c;' : 'background:#e0f2fe; border-color:#bae6fd; color:#0369a1;';
                            ?>
                                <tr style="transition: background 0.15s ease;">
                                    <td>
                                        <strong style="color: <?php echo $is_past ? '#64748b' : '#0f172a'; ?>; font-size: 0.92rem;">
                                            <?php echo esc_html(wp_date('M d, Y', strtotime($hl->holiday_date))); ?>
                                        </strong><br>
                                        <span style="font-size: 0.75rem; color: #94a3b8; font-family: 'JetBrains Mono', monospace;">
                                            <?php echo esc_html(wp_date('l', strtotime($hl->holiday_date))); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong style="color: #1e293b; font-size: 0.92rem;"><?php echo esc_html($hl->title); ?></strong>
                                    </td>
                                    <td>
                                        <span class="ifs-tag" style="<?php echo esc_attr($type_class); ?>">
                                            <?php echo esc_html($hl->holiday_type); ?>
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <?php $del_h_url = wp_nonce_url(add_query_arg(['page' => 'ifs-attendance', 'ifs_action' => 'del_holiday_entity', 'holiday_id' => $hl->id], admin_url('admin.php')), 'ifs_del_holiday_nonce'); ?>
                                        <a href="<?php echo esc_url($del_h_url); ?>" onclick="return confirm('Delete this holiday entry?');" style="color: #ef4444; font-weight: 700; text-decoration: none; font-size: 0.8rem; padding: 4px 8px; border-radius: 6px; background: #fef2f2; border: 1px solid #fecaca; transition: all 0.2s ease;">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function filterHolidayTable() {
            var input = document.getElementById('holidaySearchInput');
            var filter = input.value.toLowerCase();
            var table = document.getElementById('holidayDirectoryTable');
            if (!table) return;
            var tr = table.getElementsByTagName('tr');
            for (var i = 1; i < tr.length; i++) {
                var tdDate  = tr[i].getElementsByTagName('td')[0];
                var tdTitle = tr[i].getElementsByTagName('td')[1];
                var tdType  = tr[i].getElementsByTagName('td')[2];
                if (tdDate || tdTitle || tdType) {
                    var txtDate  = tdDate ? (tdDate.textContent || tdDate.innerText) : '';
                    var txtTitle = tdTitle ? (tdTitle.textContent || tdTitle.innerText) : '';
                    var txtType  = tdType ? (tdType.textContent || tdType.innerText) : '';
                    if (txtDate.toLowerCase().indexOf(filter) > -1 || txtTitle.toLowerCase().indexOf(filter) > -1 || txtType.toLowerCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>
    <?php
}