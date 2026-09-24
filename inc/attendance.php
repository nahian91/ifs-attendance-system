<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Clean gray SVG default avatar data URI
 */
function ifs_get_default_avatar() {
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" fill="none">'
         . '<circle cx="32" cy="32" r="32" fill="#f1f5f9"/>'
         . '<circle cx="32" cy="24" r="11" fill="#94a3b8"/>'
         . '<path d="M12 52c0-9 8-15 20-15s20 6 20 15v2H12v-2z" fill="#94a3b8"/>'
         . '</svg>';
    return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

function ifs_erp_render_attendance_view($wpdb, $table_students, $table_attendance, $available_batches, $portal_name, $base_url) {
    $today_date = current_time('Y-m-d');
    $sel_date   = isset($_GET['filter_date']) ? sanitize_text_field($_GET['filter_date']) : $today_date;

    // Disallow future date selection
    if ($sel_date > $today_date) {
        $sel_date = $today_date;
    }

    $sel_batch = isset($_GET['filter_batch']) ? sanitize_text_field($_GET['filter_batch']) : '';

    $table_batches  = $wpdb->prefix . 'ifs_batches';
    $table_holidays = $wpdb->prefix . 'ifs_holidays';

    $is_no_class   = false;
    $no_class_msg  = '';
    $students      = [];
    $att_records   = [];
    $p = 0; $a = 0; $l = 0; $unmarked = 0;

    $default_avatar = ifs_get_default_avatar();

    if ($sel_batch !== '') {
        // 1. Check if the date is an official holiday
        $holiday = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_holidays} WHERE holiday_date = %s",
            $sel_date
        ));

        if ($holiday) {
            $is_no_class  = true;
            $no_class_msg = sprintf(__('Holiday: %s (%s)', 'ifs-attendance'), esc_html($holiday->title), esc_html($holiday->holiday_type));
        } else {
            // 2. Check if batch has class on this day of the week
            $batch_row = $wpdb->get_row($wpdb->prepare(
                "SELECT schedule_json FROM {$table_batches} WHERE batch_name = %s",
                $sel_batch
            ));

            $day_of_week = date('D', strtotime($sel_date));
            $routine     = json_decode($batch_row->schedule_json ?? '', true) ?: [];

            if (!isset($routine[$day_of_week])) {
                $is_no_class  = true;
                $no_class_msg = sprintf(__('No class scheduled for %s on %s according to weekly routine.', 'ifs-attendance'), esc_html($sel_batch), date('l', strtotime($sel_date)));
            }
        }

        // Only query active students if class is scheduled
        if (!$is_no_class) {
            $students = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$table_students} WHERE status = 'active' AND batch = %s ORDER BY student_uid ASC",
                $sel_batch
            ));

            if (!empty($students)) {
                $att_records = $wpdb->get_results($wpdb->prepare(
                    "SELECT student_id, status, remarks FROM {$table_attendance} WHERE attendance_date = %s", 
                    $sel_date
                ), OBJECT_K);

                foreach ($students as $stu) {
                    if (isset($att_records[$stu->id])) {
                        $st = $att_records[$stu->id]->status;
                        if ($st === 'Present') {
                            $p++;
                        } elseif ($st === 'Absent') {
                            $a++;
                        } elseif ($st === 'Late') {
                            $l++;
                        } else {
                            $unmarked++;
                        }
                    } else {
                        $unmarked++;
                    }
                }
            }
        }
    }
    ?>
    <style>
        .ifs-student-avatar {
            width: 36px !important;
            height: 36px !important;
            min-width: 36px !important;
            max-width: 36px !important;
            border-radius: 50% !important;
            object-fit: cover !important;
            border: 1.5px solid #e2e8f0;
            background-color: #f1f5f9;
            display: inline-block;
            vertical-align: middle;
            margin-right: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
    </style>

    <div class="ifs-card" style="display: flex; flex-direction: column;">
        <!-- Filter Toolbar -->
        <div class="ifs-toolbar">
            <form method="GET" action="<?php echo esc_url(admin_url('admin.php')); ?>" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin:0;">
                <input type="hidden" name="page" value="ifs-attendance">
                <input type="hidden" name="page_view" value="attendance">
                
                <label style="font-weight:700; font-size:0.85rem; color:#334155;">Session Date:</label>
                <input type="date" name="filter_date" value="<?php echo esc_attr($sel_date); ?>" max="<?php echo esc_attr($today_date); ?>" onchange="this.form.submit()" style="padding:8px 14px; border-radius:8px; border:1.5px solid #cbd5e1; font-family:inherit; background:#ffffff; color:#0f172a;">

                <label style="font-weight:700; font-size:0.85rem; margin-left:8px; color:#334155;">Batch *:</label>
                <select name="filter_batch" onchange="this.form.submit()" style="padding:8px 14px; border-radius:8px; border:1.5px solid #0284c7; font-family:inherit; background:#ffffff; color:#0f172a; font-weight:600;">
                    <option value="">-- Select a Batch to Load Students --</option>
                    <?php foreach ($available_batches as $b): ?>
                        <option value="<?php echo esc_attr($b); ?>" <?php selected($sel_batch, $b); ?>><?php echo esc_html($b); ?></option>
                    <?php endforeach; ?>
                </select>
            </form>

            <?php if ($sel_batch !== '' && !$is_no_class && !empty($students)): ?>
                <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                    <button type="button" class="ifs-btn-ghost" onclick="setAllAttendance('Present')">Mark All Present</button>
                    <button type="button" class="ifs-btn-ghost" onclick="setAllAttendance('Absent')">Mark All Absent</button>
                    <button type="button" class="ifs-btn-ghost" onclick="clearAllAttendance()">Clear All</button>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($sel_batch === ''): ?>
            <!-- Initial State (No Students Loaded Until Selected) -->
            <div style="background:#f8fafc; border:2px dashed #cbd5e1; padding:60px 24px; border-radius:14px; text-align:center; margin: 10px 0;">
                <div style="font-size: 2.2rem; margin-bottom: 8px;">📋</div>
                <h3 style="margin:0 0 6px; font-size:1.1rem; color:#0f172a; font-weight:800;">No Batch Selected</h3>
                <p style="margin:0; font-weight:500; color:#64748b; font-size: 0.9rem;">Please choose a batch from the dropdown above to load the schedule and students.</p>
            </div>
        <?php elseif ($is_no_class): ?>
            <!-- No Class Notice -->
            <div style="background:#fffbeb; border:2px dashed #fde68a; padding:60px 24px; border-radius:14px; text-align:center; margin: 10px 0;">
                <div style="font-size: 2.5rem; margin-bottom: 10px;">🏖️</div>
                <h3 style="margin:0 0 8px; font-size:1.25rem; color:#b45309; font-weight:800;">No Class Scheduled</h3>
                <p style="margin:0; font-weight:600; color:#78350f; font-size:0.95rem;"><?php echo esc_html($no_class_msg); ?></p>
                <div style="margin-top:16px;">
                    <span class="ifs-tag" style="background:#ffffff; border-color:#fde68a; color:#b45309; padding:6px 14px; font-size:0.82rem;">
                        Date: <?php echo esc_html(wp_date('l, F j, Y', strtotime($sel_date))); ?>
                    </span>
                </div>
            </div>
        <?php elseif (empty($students)): ?>
            <!-- No Students in Batch -->
            <div style="background:#f8fafc; border:2px dashed #cbd5e1; padding:48px 24px; border-radius:14px; text-align:center; margin: 10px 0;">
                <p style="margin:0; font-weight:700; color:#64748b; font-size: 0.95rem;">No active students found in batch "<?php echo esc_html($sel_batch); ?>".</p>
            </div>
        <?php else: ?>
            <!-- Status Counter Pills -->
            <div class="ifs-pills-row" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 20px;">
                <div class="ifs-status-counter sc-p">
                    <div class="sc-count" id="count-present"><?php echo esc_html($p); ?></div>
                    <div class="sc-lbl">Present</div>
                </div>
                <div class="ifs-status-counter sc-a">
                    <div class="sc-count" id="count-absent"><?php echo esc_html($a); ?></div>
                    <div class="sc-lbl">Absent</div>
                </div>
                <div class="ifs-status-counter sc-l">
                    <div class="sc-count" id="count-late"><?php echo esc_html($l); ?></div>
                    <div class="sc-lbl">Late</div>
                </div>
                <div class="ifs-status-counter" style="background:#f1f5f9; border-color:#cbd5e1; color:#475569;">
                    <div class="sc-count" id="count-unmarked"><?php echo esc_html($unmarked); ?></div>
                    <div class="sc-lbl">Unmarked</div>
                </div>
            </div>

            <form id="attendanceBatchForm" method="POST" action="<?php echo esc_url(admin_url('admin.php?page=ifs-attendance')); ?>">
                <?php wp_nonce_field('ifs_att_save_nonce'); ?>
                <input type="hidden" name="ifs_action" value="save_attendance">
                <input type="hidden" name="attendance_date" value="<?php echo esc_attr($sel_date); ?>">
                <input type="hidden" name="current_batch_filter" value="<?php echo esc_attr($sel_batch); ?>">

                <!-- Pagination & Search Controls -->
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:14px; padding: 0 4px;">
                    <div style="display:flex; align-items:center; gap:8px; font-size:0.85rem; color:#475569;">
                        <span>Show</span>
                        <select id="dtPageSize" onchange="changePageSize(this.value)" style="padding:4px 8px; border:1.5px solid #cbd5e1; border-radius:6px; font-family:inherit; background:#ffffff;">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span>students per page</span>
                    </div>

                    <div style="display:flex; align-items:center; gap:8px;">
                        <input type="text" id="dtSearchBox" placeholder="🔍 Search in this batch..." onkeyup="filterDataTable()" style="padding:6px 12px; border:1.5px solid #cbd5e1; border-radius:8px; font-size:0.82rem; outline:none; font-family:inherit; width:220px;">
                    </div>
                </div>

                <!-- Table Container -->
                <div style="border: 1.5px solid #f1f5f9; border-radius: 12px; overflow: hidden; margin-bottom: 16px;">
                    <table class="ifs-table" id="attendanceGridTable" style="margin: 0;">
                        <thead>
                            <tr>
                                <th style="background:#f8fafc; padding: 14px 16px;">Student</th>
                                <th style="background:#f8fafc; padding: 14px 16px;">Batch</th>
                                <th style="background:#f8fafc; text-align:center; padding: 14px 16px;">Status</th>
                                <th style="background:#f8fafc; text-align:center; padding: 14px 16px;">Notify</th>
                                <th style="background:#f8fafc; text-align:right; padding: 14px 16px;">Remarks</th>
                            </tr>
                        </thead>
                        <tbody id="attendanceGridBody">
                            <?php foreach ($students as $stu): ?>
                                <?php 
                                $saved_status = isset($att_records[$stu->id]) ? $att_records[$stu->id]->status : ''; 
                                $rem          = isset($att_records[$stu->id]) ? $att_records[$stu->id]->remarks : '';
                                $phone_notify = !empty($stu->guardian_phone) ? $stu->guardian_phone : $stu->phone;
                                $photo_url    = !empty($stu->photo_url) ? esc_url($stu->photo_url) : $default_avatar;
                                ?>
                                <tr class="att-row" data-search="<?php echo esc_attr(strtolower($stu->name . ' ' . $stu->student_uid . ' ' . $stu->batch)); ?>">
                                    <td>
                                        <div style="display:flex; align-items:center;">
                                            <img src="<?php echo $photo_url; ?>" onerror="this.onerror=null;this.src='<?php echo $default_avatar; ?>';" class="ifs-student-avatar" alt="Avatar">
                                            <div>
                                                <a href="<?php echo esc_url($base_url . '&page_view=profile&student_id=' . $stu->id); ?>" style="color:#0f172a; text-decoration:none; font-weight:700;">
                                                    <?php echo esc_html($stu->name); ?>
                                                </a><br>
                                                <span style="font-size:0.75rem; color:#64748b; font-family: 'JetBrains Mono', monospace;">#<?php echo esc_html($stu->student_uid); ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="ifs-tag"><?php echo esc_html($stu->batch); ?></span></td>
                                    <td style="text-align:center;">
                                        <div class="ifs-status-pill-group">
                                            <label><input type="radio" class="att-radio" name="status[<?php echo esc_attr($stu->id); ?>]" value="Present" <?php checked($saved_status, 'Present'); ?>><span>Present</span></label>
                                            <label><input type="radio" class="att-radio" name="status[<?php echo esc_attr($stu->id); ?>]" value="Absent" <?php checked($saved_status, 'Absent'); ?>><span>Absent</span></label>
                                            <label><input type="radio" class="att-radio" name="status[<?php echo esc_attr($stu->id); ?>]" value="Late" <?php checked($saved_status, 'Late'); ?>><span>Late</span></label>
                                        </div>
                                    </td>
                                    <td style="text-align:center;">
                                        <?php if (!empty($phone_notify)): ?>
                                            <a href="https://wa.me/88<?php echo esc_attr(preg_replace('/[^0-9]/', '', $phone_notify)); ?>?text=<?php echo urlencode('Attendance Notice: ' . $stu->name . ' attendance status updated for date ' . $sel_date . '. - ' . $portal_name); ?>" target="_blank" class="ifs-btn-wa" style="font-size: 0.74rem; padding: 5px 10px;">WhatsApp</a>
                                        <?php else: ?>
                                            <span style="color:#cbd5e1; font-size:0.75rem;">No Phone</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:right;">
                                        <input type="text" name="remarks[<?php echo esc_attr($stu->id); ?>]" value="<?php echo esc_attr($rem); ?>" placeholder="Reason..." style="width: 130px; padding: 6px 10px; border-radius: 6px; border: 1.5px solid #e2e8f0; font-size: 0.82rem; font-family: inherit;">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Footer with Manual Save Button -->
                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; margin-top:8px;">
                    <div id="dtInfoText" style="font-size:0.85rem; color:#64748b; font-weight:600;">Showing 1 to <?php echo min(25, count($students)); ?> of <?php echo count($students); ?> students</div>
                    
                    <div style="display:flex; align-items:center; gap:6px;">
                        <button type="button" class="ifs-btn-ghost" id="dtBtnPrev" onclick="prevPage()" style="padding:6px 12px; font-size:0.82rem;">« Previous</button>
                        <span id="dtCurrentPageDisplay" style="font-size:0.85rem; font-weight:700; color:#334155; padding:0 8px;">Page 1 of 1</span>
                        <button type="button" class="ifs-btn-ghost" id="dtBtnNext" onclick="nextPage()" style="padding:6px 12px; font-size:0.82rem;">Next »</button>
                    </div>

                    <div>
                        <button type="submit" class="ifs-btn" style="padding: 10px 24px;">Save Attendance Session</button>
                    </div>
                </div>
            </form>

            <script>
                var currentPage = 1;
                var pageSize = 25;
                var visibleRows = [];

                function updateCounters() {
                    var totalActiveStudents = document.querySelectorAll('.att-row').length;
                    var p = 0, a = 0, l = 0;
                    
                    document.querySelectorAll('.att-radio:checked').forEach(function(r) {
                        if (r.value === 'Present') p++;
                        else if (r.value === 'Absent') a++;
                        else if (r.value === 'Late') l++;
                    });

                    var unmarked = Math.max(0, totalActiveStudents - (p + a + l));

                    var elP = document.getElementById('count-present');
                    var elA = document.getElementById('count-absent');
                    var elL = document.getElementById('count-late');
                    var elU = document.getElementById('count-unmarked');

                    if (elP) elP.innerText = p;
                    if (elA) elA.innerText = a;
                    if (elL) elL.innerText = l;
                    if (elU) elU.innerText = unmarked;
                }

                function setAllAttendance(status) {
                    document.querySelectorAll('.att-row').forEach(function(row) {
                        if (row.style.display !== 'none') {
                            var targetRadio = row.querySelector('.att-radio[value="' + status + '"]');
                            if (targetRadio) {
                                targetRadio.checked = true;
                            }
                        }
                    });
                    updateCounters();
                }

                function clearAllAttendance() {
                    document.querySelectorAll('.att-row').forEach(function(row) {
                        if (row.style.display !== 'none') {
                            row.querySelectorAll('.att-radio').forEach(function(r) {
                                r.checked = false;
                            });
                        }
                    });
                    updateCounters();
                }

                function filterDataTable() {
                    var filter = document.getElementById('dtSearchBox').value.toLowerCase().trim();
                    var allRows = Array.from(document.querySelectorAll('.att-row'));
                    
                    visibleRows = allRows.filter(function(row) {
                        var searchContext = row.getAttribute('data-search') || '';
                        return searchContext.indexOf(filter) > -1;
                    });

                    currentPage = 1;
                    renderPagination();
                }

                function renderPagination() {
                    var allRows = Array.from(document.querySelectorAll('.att-row'));
                    var filter = document.getElementById('dtSearchBox') ? document.getElementById('dtSearchBox').value.toLowerCase().trim() : '';

                    if (filter === '') {
                        visibleRows = allRows;
                    }

                    var totalRows = visibleRows.length;
                    var totalPages = Math.ceil(totalRows / pageSize) || 1;

                    if (currentPage > totalPages) {
                        currentPage = totalPages;
                    }
                    if (currentPage < 1) {
                        currentPage = 1;
                    }

                    var startIndex = (currentPage - 1) * pageSize;
                    var endIndex = startIndex + pageSize;

                    allRows.forEach(function(row) {
                        row.style.display = 'none';
                    });

                    for (var i = startIndex; i < endIndex && i < totalRows; i++) {
                        if (visibleRows[i]) {
                            visibleRows[i].style.display = '';
                        }
                    }

                    var startDisplay = totalRows > 0 ? (startIndex + 1) : 0;
                    var endDisplay = Math.min(endIndex, totalRows);
                    var dtInfoText = document.getElementById('dtInfoText');
                    if (dtInfoText) {
                        dtInfoText.innerText = 'Showing ' + startDisplay + ' to ' + endDisplay + ' of ' + totalRows + ' students';
                    }

                    var dtCurrentPageDisplay = document.getElementById('dtCurrentPageDisplay');
                    if (dtCurrentPageDisplay) {
                        dtCurrentPageDisplay.innerText = 'Page ' + currentPage + ' of ' + totalPages;
                    }

                    var btnPrev = document.getElementById('dtBtnPrev');
                    var btnNext = document.getElementById('dtBtnNext');
                    if (btnPrev) btnPrev.disabled = (currentPage === 1);
                    if (btnNext) btnNext.disabled = (currentPage >= totalPages);
                }

                function changePageSize(val) {
                    pageSize = parseInt(val, 10) || 25;
                    currentPage = 1;
                    renderPagination();
                }

                function prevPage() {
                    if (currentPage > 1) {
                        currentPage--;
                        renderPagination();
                    }
                }

                function nextPage() {
                    var totalPages = Math.ceil(visibleRows.length / pageSize) || 1;
                    if (currentPage < totalPages) {
                        currentPage++;
                        renderPagination();
                    }
                }

                document.addEventListener('DOMContentLoaded', function() {
                    document.querySelectorAll('.att-radio').forEach(function(r) {
                        r.addEventListener('change', updateCounters);
                    });
                    filterDataTable();
                    updateCounters();
                });
            </script>
        <?php endif; ?>
    </div>
    <?php
}