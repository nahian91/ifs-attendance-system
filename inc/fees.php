<?php
if (!defined('ABSPATH')) exit;

function ifs_erp_render_fees_workspace($wpdb, $table_fees, $table_students, $table_batches, $sub_tab, $base_url, $currency_symbol, $portal_name) {
    ?>
    <nav class="ifs-subtabs-nav">
        <a href="<?php echo esc_url($base_url . '&page_view=fees&sub_tab=all'); ?>" class="ifs-subtab-btn <?php echo ($sub_tab === 'all') ? 'active' : ''; ?>">📜 All Collections</a>
        <a href="<?php echo esc_url($base_url . '&page_view=fees&sub_tab=add'); ?>" class="ifs-subtab-btn <?php echo ($sub_tab === 'add') ? 'active' : ''; ?>">➕ Add Payment</a>
    </nav>

    <?php if ($sub_tab === 'all'): ?>
        <?php
        $fee_logs = $wpdb->get_results("
            SELECT f.*, s.student_uid, s.name as student_name, s.batch 
            FROM $table_fees f 
            JOIN $table_students s ON f.student_id = s.id 
            ORDER BY f.payment_date DESC, f.id DESC
        ");
        $export_url = wp_nonce_url(admin_url('admin.php?ifs_export=fees'), 'ifs_export_nonce');
        ?>
        <div class="ifs-card">
            <div class="ifs-card-header">
                <h3 class="ifs-card-title">Fee Ledger</h3>
                <a href="<?php echo esc_url($export_url); ?>" class="ifs-btn-ghost">📥 Export CSV</a>
            </div>
            <table class="ifs-table">
                <thead><tr><th>Receipt</th><th>Student</th><th>Batch</th><th>Title</th><th>Paid</th><th>Date</th><th style="text-align:right;">Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($fee_logs as $fl): ?>
                        <tr>
                            <td><span class="ifs-tag"><?php echo esc_html($fl->receipt_no); ?></span></td>
                            <td><strong>#<?php echo esc_html($fl->student_uid); ?></strong> - <?php echo esc_html($fl->student_name); ?></td>
                            <td><?php echo esc_html($fl->batch); ?></td>
                            <td><?php echo esc_html($fl->fee_title); ?></td>
                            <td style="color:#10b981; font-weight:800;"><?php echo $currency_symbol . ' ' . number_format($fl->paid_amount, 2); ?></td>
                            <td><?php echo date('M d, Y', strtotime($fl->payment_date)); ?></td>
                            <td style="text-align:right;">
                                <button type="button" class="ifs-btn-ghost" style="padding:4px 8px; font-size:0.75rem;" onclick="printReceipt('<?php echo esc_js($fl->receipt_no); ?>', '<?php echo esc_js($fl->student_name); ?>', '<?php echo esc_js($fl->student_uid); ?>', '<?php echo esc_js($fl->batch); ?>', '<?php echo esc_js($fl->fee_title); ?>', '<?php echo $fl->total_amount; ?>', '<?php echo $fl->paid_amount; ?>', '<?php echo $fl->due_amount; ?>', '<?php echo esc_js($fl->payment_method); ?>', '<?php echo $fl->payment_date; ?>', '<?php echo esc_js($currency_symbol); ?>')">Print</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <script>
        function printReceipt(recNo, name, uid, batch, title, total, paid, due, method, date, cur) {
            const w = window.open('', '', 'width=680,height=720');
            w.document.write(`
                <html>
                <head>
                    <title>Money Receipt - ${recNo}</title>
                    <style>
                        body { font-family: sans-serif; padding: 30px; color: #1e293b; }
                        .box { border: 2px solid #0284c7; padding: 28px; border-radius: 12px; }
                        .head { text-align: center; border-bottom: 2px dashed #cbd5e1; padding-bottom: 14px; margin-bottom: 18px; }
                        .row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
                        .total-box { background: #f8fafc; padding: 12px; border-radius: 8px; margin: 16px 0; border: 1px solid #e2e8f0; }
                        .foot { text-align: center; margin-top: 28px; font-size: 12px; color: #64748b; }
                    </style>
                </head>
                <body onload="window.print();window.close();">
                    <div class="box">
                        <div class="head">
                            <h2 style="margin:0; color:#0284c7;"><?php echo esc_js($portal_name); ?></h2>
                            <p style="margin:4px 0 0; font-size:13px; font-weight:bold;">OFFICIAL MONEY RECEIPT</p>
                        </div>
                        <div class="row"><span><strong>Receipt No:</strong> ` + recNo + `</span><span><strong>Date:</strong> ` + date + `</span></div>
                        <div class="row"><span><strong>Student:</strong> ` + name + ` (#` + uid + `)</span><span><strong>Batch:</strong> ` + batch + `</span></div>
                        <div class="row"><span><strong>Payment Mode:</strong> ` + method + `</span><span><strong>Description:</strong> ` + title + `</span></div>
                        <div class="total-box">
                            <div class="row"><span>Total Payable:</span> <span>` + cur + ` ` + total + `</span></div>
                            <div class="row" style="font-size:16px; font-weight:bold; color:#10b981;"><span>Amount Paid:</span> <span>` + cur + ` ` + paid + `</span></div>
                            <div class="row" style="font-weight:bold; color:#ef4444;"><span>Balance Due:</span> <span>` + cur + ` ` + due + `</span></div>
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
        $students_autofill_data = $wpdb->get_results("
            SELECT s.id, s.student_uid, s.name, s.batch, s.admission_due, b.course_fee
            FROM $table_students s
            LEFT JOIN $table_batches b ON s.batch = b.batch_name
            WHERE s.status = 'active'
            ORDER BY s.name ASC
        ");
        ?>
        <div class="ifs-card" style="max-width: 620px; margin: 0 auto;">
            <div class="ifs-card-header">
                <h3 class="ifs-card-title">Collect Student Fee (Auto-Fill)</h3>
            </div>
            <form method="POST">
                <?php wp_nonce_field('ifs_fee_nonce'); ?>
                <input type="hidden" name="ifs_action" value="save_fee">

                <div class="ifs-field-group">
                    <label>Select Enrolled Student (Auto Data Fill) *</label>
                    <select name="student_id" id="auto_student_select" class="ifs-select" onchange="runAutoFill()" required>
                        <option value="">-- Choose Student --</option>
                        <?php foreach ($students_autofill_data as $st): ?>
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
                        <label>Total Fee Amount (<?php echo esc_html($currency_symbol); ?>) *</label>
                        <input type="number" step="0.01" name="total_amount" id="auto_total_amount" class="ifs-input" oninput="calcFeeBalance()" required>
                    </div>
                    <div class="ifs-field-group" style="flex:1;">
                        <label>Paid Amount (<?php echo esc_html($currency_symbol); ?>) *</label>
                        <input type="number" step="0.01" name="paid_amount" id="auto_paid_amount" class="ifs-input" oninput="calcFeeBalance()" required>
                    </div>
                </div>

                <div class="ifs-field-group">
                    <label>Remaining Due Balance (Auto)</label>
                    <input type="text" id="auto_due_balance" class="ifs-input" value="0.00" readonly style="background:#f8fafc; font-weight:800; color:#ef4444;">
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
                        <input type="date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" class="ifs-input" required>
                    </div>
                </div>

                <button type="submit" class="ifs-btn" style="width:100%;">Record & Print Receipt</button>
            </form>
        </div>

        <script>
        function runAutoFill() {
            const sel = document.getElementById('auto_student_select');
            const opt = sel.options[sel.selectedIndex];
            if (opt.value) {
                const fee = opt.getAttribute('data-coursefee');
                const batch = opt.getAttribute('data-batch');
                const currentMonth = new Date().toLocaleString('default', { month: 'long' });
                
                document.getElementById('auto_fee_title').value = `Monthly Tuition Fee (${currentMonth}) - ${batch}`;
                document.getElementById('auto_total_amount').value = fee;
                document.getElementById('auto_paid_amount').value = fee;
                calcFeeBalance();
            }
        }
        function calcFeeBalance() {
            const t = parseFloat(document.getElementById('auto_total_amount').value) || 0;
            const p = parseFloat(document.getElementById('auto_paid_amount').value) || 0;
            document.getElementById('auto_due_balance').value = Math.max(0, t - p).toFixed(2);
        }
        </script>
    <?php endif;
}