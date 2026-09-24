<?php
if (!defined('ABSPATH')) {
    exit;
}

function ifs_erp_render_fees_workspace($wpdb,$table_fees, $table_students,$table_batches, $sub_tab,$base_url, $currency_symbol,$portal_name) {
    ?>
    <nav class="ifs-subtabs-nav">
        <a href="<?php echo esc_url($base_url . '&page_view=fees&sub_tab=all'); ?>" class="ifs-subtab-btn <?php echo ($sub_tab === 'all') ? 'active' : ''; ?>">📜 All Collections</a>
        <a href="<?php echo esc_url($base_url . '&page_view=fees&sub_tab=add'); ?>" class="ifs-subtab-btn <?php echo ($sub_tab === 'add') ? 'active' : ''; ?>">➕ Add Payment</a>
    </nav>

    <?php if ($sub_tab === 'all'): ?>
        <?php
        $fee_logs =$wpdb->get_results("
            SELECT f.*, s.student_uid, s.name as student_name, s.batch 
            FROM {$table_fees} f 
            JOIN {$table_students} s ON f.student_id = s.id 
            ORDER BY f.payment_date DESC, f.id DESC
        ");
        $export_url = wp_nonce_url(admin_url('admin.php?page=ifs-attendance&ifs_export=fees'), 'ifs_export_nonce');
        ?>
        <div class="ifs-card" style="display: flex; flex-direction: column;">
            <div class="ifs-card-header" style="margin-bottom: 16px;">
                <div>
                    <h3 class="ifs-card-title">Fee Ledger (<?php echo count($fee_logs); ?>)</h3>
                    <span style="font-size:0.8rem; color:#64748b; font-weight:500;">Recorded transactions, dues, and verified receipts</span>
                </div>
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <input type="text" id="feeSearchInput" placeholder="🔍 Search receipt or student..." onkeyup="filterFeeTable()" style="padding: 7px 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.82rem; outline: none; font-family: inherit; width: 220px; background:#ffffff; color:#0f172a;">
                    <a href="<?php echo esc_url($export_url); ?>" class="ifs-btn-ghost" style="padding: 6px 14px; font-size: 0.82rem;">📥 Export CSV</a>
                </div>
            </div>

            <?php if (empty($fee_logs)): ?>
                <div style="background:#f8fafc; border:2px dashed #cbd5e1; padding:48px 24px; border-radius:14px; text-align:center; margin:10px 0;">
                    <p style="margin:0; font-weight:700; color:#64748b; font-size:0.95rem;">No payment transactions recorded yet.</p>
                </div>
            <?php else: ?>
                <!-- Page Size & Controls Toolbar -->
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:12px; padding: 0 4px;">
                    <div style="display:flex; align-items:center; gap:8px; font-size:0.85rem; color:#475569;">
                        <span>Show</span>
                        <select id="dtFeePageSize" onchange="changeFeePageSize(this.value)" style="padding:4px 8px; border:1.5px solid #cbd5e1; border-radius:6px; font-family:inherit; background:#ffffff;">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span>entries per page</span>
                    </div>
                </div>

                <!-- Clean Table (Natural Height, No Inner Scrollbars) -->
                <div style="border: 1.5px solid #f1f5f9; border-radius: 12px; overflow: hidden; margin-bottom: 16px;">
                    <table class="ifs-table" id="feeDirectoryTable" style="margin:0;">
                        <thead>
                            <tr>
                                <th style="background:#f8fafc; padding: 14px 16px;">Receipt</th>
                                <th style="background:#f8fafc; padding: 14px 16px;">Student</th>
                                <th style="background:#f8fafc; padding: 14px 16px;">Batch</th>
                                <th style="background:#f8fafc; padding: 14px 16px;">Fee Title</th>
                                <th style="background:#f8fafc; padding: 14px 16px;">Paid</th>
                                <th style="background:#f8fafc; padding: 14px 16px;">Due</th>
                                <th style="background:#f8fafc; padding: 14px 16px;">Date</th>
                                <th style="background:#f8fafc; text-align:right; padding: 14px 16px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="feeDirectoryBody">
                            <?php foreach ($fee_logs as$fl): ?>
                                <tr class="fee-row" data-search="<?php echo esc_attr(strtolower($fl->receipt_no . ' ' . $fl->student_name . ' ' .$fl->student_uid . ' ' . $fl->batch . ' ' .$fl->fee_title)); ?>" style="transition: background 0.15s ease;">
                                    <td>
                                        <span class="ifs-tag" style="font-family:'JetBrains Mono', monospace; font-size:0.75rem; background:#f0fdf4; border-color:#bbf7d0; color:#15803d; font-weight:700;">
                                            <?php echo esc_html($fl->receipt_no); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong style="color: #0f172a; font-size: 0.92rem;"><?php echo esc_html($fl->student_name); ?></strong><br>
                                        <span style="font-size:0.75rem; color:#64748b; font-family:'JetBrains Mono', monospace;">#<?php echo esc_html($fl->student_uid); ?></span>
                                    </td>
                                    <td><span class="ifs-tag"><?php echo esc_html($fl->batch); ?></span></td>
                                    <td>
                                        <span style="font-size: 0.88rem; color: #334155; font-weight: 600;"><?php echo esc_html($fl->fee_title); ?></span><br>
                                        <span style="font-size: 0.75rem; color: #94a3b8;"><?php echo esc_html($fl->payment_method); ?></span>
                                    </td>
                                    <td>
                                        <strong style="color:#10b981; font-size: 0.92rem;">
                                            <?php echo esc_html($currency_symbol) . ' ' . esc_html(number_format((float)$fl->paid_amount, 2)); ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <?php if ((float)$fl->due_amount > 0): ?>
                                            <strong style="color:#ef4444; font-size: 0.88rem;">
                                                <?php echo esc_html($currency_symbol) . ' ' . esc_html(number_format((float)$fl->due_amount, 2)); ?>
                                            </strong>
                                        <?php else: ?>
                                            <span style="color:#64748b; font-size: 0.82rem; font-weight:600;">Cleared</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="color:#64748b; font-size:0.85rem;">
                                        <?php echo esc_html(wp_date('M d, Y', strtotime($fl->payment_date))); ?>
                                    </td>
                                    <td style="text-align:right;">
                                        <button type="button" class="ifs-btn-ghost" style="padding:4px 10px; font-size:0.78rem;" onclick="printReceipt('<?php echo esc_js($fl->receipt_no); ?>', '<?php echo esc_js($fl->student_name); ?>', '<?php echo esc_js($fl->student_uid); ?>', '<?php echo esc_js($fl->batch); ?>', '<?php echo esc_js($fl->fee_title); ?>', '<?php echo esc_attr($fl->total_amount); ?>', '<?php echo esc_attr($fl->paid_amount); ?>', '<?php echo esc_attr($fl->due_amount); ?>', '<?php echo esc_js($fl->payment_method); ?>', '<?php echo esc_js(wp_date('M d, Y', strtotime($fl->payment_date))); ?>', '<?php echo esc_js($currency_symbol); ?>')">🖨️ Print</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-top:8px;">
                    <div id="feeDtInfoText" style="font-size:0.85rem; color:#64748b; font-weight:600;">Showing 1 to <?php echo min(25, count($fee_logs)); ?> of <?php echo count($fee_logs); ?> transactions</div>
                    
                    <div style="display:flex; align-items:center; gap:6px;">
                        <button type="button" class="ifs-btn-ghost" id="feeDtBtnPrev" onclick="prevFeePage()" style="padding:6px 12px; font-size:0.82rem;">« Previous</button>
                        <span id="feeDtCurrentPageDisplay" style="font-size:0.85rem; font-weight:700; color:#334155; padding:0 8px;">Page 1 of 1</span>
                        <button type="button" class="ifs-btn-ghost" id="feeDtBtnNext" onclick="nextFeePage()" style="padding:6px 12px; font-size:0.82rem;">Next »</button>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <script>
        var feeCurrentPage = 1;
        var feePageSize = 25;
        var feeVisibleRows = [];

        function filterFeeTable() {
            var input = document.getElementById('feeSearchInput');
            if (!input) return;
            var filter = input.value.toLowerCase().trim();
            var allRows = Array.from(document.querySelectorAll('.fee-row'));

            feeVisibleRows = allRows.filter(function(row) {
                var searchContext = row.getAttribute('data-search') || '';
                return searchContext.indexOf(filter) > -1;
            });

            feeCurrentPage = 1;
            renderFeePagination();
        }

        function renderFeePagination() {
            var allRows = Array.from(document.querySelectorAll('.fee-row'));
            var input = document.getElementById('feeSearchInput');
            var filter = input ? input.value.toLowerCase().trim() : '';

            if (filter === '') {
                feeVisibleRows = allRows;
            }

            var totalRows = feeVisibleRows.length;
            var totalPages = Math.ceil(totalRows / feePageSize) || 1;

            if (feeCurrentPage > totalPages) feeCurrentPage = totalPages;
            if (feeCurrentPage < 1) feeCurrentPage = 1;

            var startIndex = (feeCurrentPage - 1) * feePageSize;
            var endIndex = startIndex + feePageSize;

            allRows.forEach(function(row) {
                row.style.display = 'none';
            });

            for (var i = startIndex; i < endIndex && i < totalRows; i++) {
                if (feeVisibleRows[i]) {
                    feeVisibleRows[i].style.display = '';
                }
            }

            var startDisplay = totalRows > 0 ? (startIndex + 1) : 0;
            var endDisplay = Math.min(endIndex, totalRows);
            var infoEl = document.getElementById('feeDtInfoText');
            if (infoEl) {
                infoEl.innerText = 'Showing ' + startDisplay + ' to ' + endDisplay + ' of ' + totalRows + ' transactions';
            }

            var pageDisplayEl = document.getElementById('feeDtCurrentPageDisplay');
            if (pageDisplayEl) {
                pageDisplayEl.innerText = 'Page ' + feeCurrentPage + ' of ' + totalPages;
            }

            var btnPrev = document.getElementById('feeDtBtnPrev');
            var btnNext = document.getElementById('feeDtBtnNext');
            if (btnPrev) btnPrev.disabled = (feeCurrentPage === 1);
            if (btnNext) btnNext.disabled = (feeCurrentPage >= totalPages);
        }

        function changeFeePageSize(val) {
            feePageSize = parseInt(val, 10) || 25;
            feeCurrentPage = 1;
            renderFeePagination();
        }

        function prevFeePage() {
            if (feeCurrentPage > 1) {
                feeCurrentPage--;
                renderFeePagination();
            }
        }

        function nextFeePage() {
            var totalPages = Math.ceil(feeVisibleRows.length / feePageSize) || 1;
            if (feeCurrentPage < totalPages) {
                feeCurrentPage++;
                renderFeePagination();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            filterFeeTable();
        });

        function printReceipt(recNo, name, uid, batch, title, total, paid, due, method, date, cur) {
            var w = window.open('', '', 'width=680,height=720');
            w.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Receipt - ` + recNo + `</title>
                    <style>
                        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 32px; color: #1e293b; line-height: 1.5; }
                        .box { border: 2px solid #0284c7; padding: 28px; border-radius: 12px; }
                        .head { text-align: center; border-bottom: 2px dashed #cbd5e1; padding-bottom: 16px; margin-bottom: 20px; }
                        .row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; }
                        .total-box { background: #f8fafc; padding: 14px; border-radius: 8px; margin: 18px 0; border: 1px solid #e2e8f0; }
                        .foot { text-align: center; margin-top: 24px; font-size: 12px; color: #64748b; }
                    </style>
                </head>
                <body onload="window.print();window.close();">
                    <div class="box">
                        <div class="head">
                            <h2 style="margin:0; color:#0284c7; font-size: 20px;"><?php echo esc_js($portal_name); ?></h2>
                            <p style="margin:4px 0 0; font-size:12px; font-weight:bold; letter-spacing: 0.05em; color: #64748b;">OFFICIAL MONEY RECEIPT</p>
                        </div>
                        <div class="row"><span><strong>Receipt No:</strong> ` + recNo + `</span><span><strong>Date:</strong> ` + date + `</span></div>
                        <div class="row"><span><strong>Student:</strong> ` + name + ` (#` + uid + `)</span><span><strong>Batch:</strong> ` + batch + `</span></div>
                        <div class="row"><span><strong>Payment Mode:</strong> ` + method + `</span><span><strong>Description:</strong> ` + title + `</span></div>
                        <div class="total-box">
                            <div class="row"><span>Total Payable:</span> <span>` + cur + ` ` + Number(total).toFixed(2) + `</span></div>
                            <div class="row" style="font-size:15px; font-weight:bold; color:#10b981;"><span>Amount Paid:</span> <span>` + cur + ` ` + Number(paid).toFixed(2) + `</span></div>
                            <div class="row" style="font-weight:bold; color:#ef4444;"><span>Balance Due:</span> <span>` + cur + ` ` + Number(due).toFixed(2) + `</span></div>
                        </div>
                        <div class="foot">Generated automatically via <?php echo esc_js($portal_name); ?>. Thank you!</div>
                    </div>
                </body>
                </html>
            `);
            w.document.close();
        }
        </script>

    <?php elseif ($sub_tab === 'add'): ?>
        <?php 
        $students_autofill_data =$wpdb->get_results("
            SELECT s.id, s.student_uid, s.name, s.batch, s.admission_due, b.course_fee
            FROM {$table_students} s
            LEFT JOIN {$table_batches} b ON s.batch = b.batch_name
            WHERE s.status = 'active'
            ORDER BY s.name ASC
        ");
        ?>
        <div class="ifs-card" style="max-width: 660px; margin: 0 auto; display: flex; flex-direction: column;">
            <div class="ifs-card-header" style="margin-bottom: 18px;">
                <div>
                    <h3 class="ifs-card-title">Collect Student Fee</h3>
                    <span style="font-size:0.8rem; color:#64748b; font-weight:500;">Select student to auto-populate batch course fees and active dues</span>
                </div>
            </div>
            <form method="POST" action="<?php echo esc_url(admin_url('admin.php?page=ifs-attendance')); ?>">
                <?php wp_nonce_field('ifs_fee_nonce'); ?>
                <input type="hidden" name="ifs_action" value="save_fee">

                <div class="ifs-field-group">
                    <label>Select Enrolled Student *</label>
                    <select name="student_id" id="auto_student_select" class="ifs-select" onchange="runAutoFill()" required>
                        <option value="">-- Choose Student --</option>
                        <?php foreach ($students_autofill_data as$st): ?>
                            <option value="<?php echo esc_attr($st->id); ?>" 
                                data-batch="<?php echo esc_attr($st->batch); ?>" 
                                data-coursefee="<?php echo esc_attr($st->course_fee ?: 3500); ?>"
                                data-admdue="<?php echo esc_attr($st->admission_due); ?>">
                                #<?php echo esc_html($st->student_uid); ?> - <?php echo esc_html($st->name); ?> (<?php echo esc_html($st->batch); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="ifs-field-group">
                    <label>Fee Title / Description *</label>
                    <input type="text" name="fee_title" id="auto_fee_title" class="ifs-input" placeholder="e.g. Monthly Tuition Fee" required>
                </div>

                <div style="display:flex; gap:14px;">
                    <div class="ifs-field-group" style="flex:1;">
                        <label>Total Amount (<?php echo esc_html($currency_symbol); ?>) *</label>
                        <input type="number" step="0.01" name="total_amount" id="auto_total_amount" class="ifs-input" oninput="calcFeeBalance()" required>
                    </div>
                    <div class="ifs-field-group" style="flex:1;">
                        <label>Paid Amount (<?php echo esc_html($currency_symbol); ?>) *</label>
                        <input type="number" step="0.01" name="paid_amount" id="auto_paid_amount" class="ifs-input" oninput="calcFeeBalance()" required>
                    </div>
                </div>

                <div class="ifs-field-group">
                    <label>Remaining Balance (Calculated Due)</label>
                    <input type="text" id="auto_due_balance" class="ifs-input" value="0.00" readonly style="background:#f8fafc; font-weight:800; color:#ef4444; font-family:'JetBrains Mono', monospace;">
                </div>

                <div style="display:flex; gap:14px;">
                    <div class="ifs-field-group" style="flex:1;">
                        <label>Payment Method</label>
                        <select name="payment_method" class="ifs-select">
                            <option value="Cash">Cash</option>
                            <option value="bKash">bKash</option>
                            <option value="Nagad">Nagad</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                        </select>
                    </div>
                    <div class="ifs-field-group" style="flex:1;">
                        <label>Payment Date *</label>
                        <input type="date" name="payment_date" value="<?php echo esc_attr(current_time('Y-m-d')); ?>" class="ifs-input" required>
                    </div>
                </div>

                <div class="ifs-field-group">
                    <label>Payment Note / Remarks</label>
                    <input type="text" name="fee_remarks" class="ifs-input" placeholder="Optional transaction ID, bank slip, or note...">
                </div>

                <button type="submit" class="ifs-btn" style="width:100%; margin-top: 6px; padding: 11px;">Record Payment & Print Receipt</button>
            </form>
        </div>

        <script>
        function runAutoFill() {
            var sel = document.getElementById('auto_student_select');
            var opt = sel.options[sel.selectedIndex];
            if (opt && opt.value) {
                var fee = opt.getAttribute('data-coursefee');
                var batch = opt.getAttribute('data-batch');
                var currentMonth = new Date().toLocaleString('default', { month: 'long' });
                
                document.getElementById('auto_fee_title').value = 'Monthly Tuition Fee (' + currentMonth + ') - ' + batch;
                document.getElementById('auto_total_amount').value = parseFloat(fee).toFixed(2);
                document.getElementById('auto_paid_amount').value = parseFloat(fee).toFixed(2);
                calcFeeBalance();
            }
        }
        function calcFeeBalance() {
            var t = parseFloat(document.getElementById('auto_total_amount').value) || 0;
            var p = parseFloat(document.getElementById('auto_paid_amount').value) || 0;
            document.getElementById('auto_due_balance').value = Math.max(0, t - p).toFixed(2);
        }
        </script>
    <?php endif;
}