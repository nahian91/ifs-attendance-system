<?php
if (!defined('ABSPATH')) exit;

add_action('admin_enqueue_scripts', 'ifs_erp_load_media_uploader');
function ifs_erp_load_media_uploader($hook) {
    if (strpos($hook, 'ifs-attendance') !== false) {
        wp_enqueue_media();
    }
}

function ifs_get_icon($name) {
    $icons = [
        'dashboard'  => '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>',
        'attendance' => '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>',
        'students'   => '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>',
        'batches'    => '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>',
        'teachers'   => '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
        'schedule'   => '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
        'fees'       => '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
        'reports'    => '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>',
        'notices'    => '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>',
        'settings'   => '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'
    ];
    return $icons[$name] ?? '';
}

function ifs_pro_inject_ultimate_styles() {
    ?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap');
        #adminmenumain, #adminmenuback, #wpadminbar, #wpfooter { display: none !important; }
        #wpcontent, #wpfooter { margin-left: 0 !important; padding: 0 !important; }
        html.wp-toolbar { padding-top: 0 !important; }
        :root {
            --ifs-bg: #090d16; --ifs-surface: #0f172a; --ifs-surface-border: #1e293b;
            --ifs-accent: #38bdf8; --ifs-accent-grad: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);
            --ifs-canvas: #f8fafc; --ifs-card-border: #e2e8f0; --ifs-text-primary: #0f172a; --ifs-text-muted: #64748b;
        }
        body { background: var(--ifs-bg) !important; font-family: 'Plus Jakarta Sans', sans-serif !important; color: var(--ifs-text-primary) !important; margin: 0 !important; }
        .ifs-shell { display: flex; min-height: 100vh; background: var(--ifs-canvas); }
        .ifs-sidebar { width: 260px; background: var(--ifs-bg); color: #94a3b8; display: flex; flex-direction: column; position: fixed; top: 0; bottom: 0; left: 0; z-index: 999; border-right: 1px solid var(--ifs-surface-border); box-shadow: 4px 0 24px rgba(0, 0, 0, 0.4); }
        .ifs-sidebar-brand { padding: 24px 22px; display: flex; align-items: center; gap: 14px; border-bottom: 1px solid var(--ifs-surface-border); background: rgba(15, 23, 42, 0.4); }
        .ifs-brand-icon { width: 40px; height: 40px; border-radius: 12px; background: var(--ifs-accent-grad); display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 4px 16px rgba(2, 132, 199, 0.4); }
        .ifs-brand-name { font-size: 1.1rem; font-weight: 800; color: #ffffff; line-height: 1.2; }
        .ifs-brand-sub { font-size: 0.7rem; color: var(--ifs-accent); text-transform: uppercase; font-weight: 700; letter-spacing: 0.08em; display: block; margin-top: 2px; }
        .ifs-menu-col { display: flex; flex-direction: column; gap: 4px; padding: 18px 12px; flex-grow: 1; overflow-y: auto; }
        .ifs-menu-heading { font-size: 0.68rem; font-weight: 800; text-transform: uppercase; color: #475569; letter-spacing: 0.08em; padding: 12px 14px 4px; }
        .ifs-menu-item { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; font-size: 0.9rem; font-weight: 600; color: #94a3b8; text-decoration: none; border-radius: 10px; transition: all 0.2s; }
        .ifs-menu-item-left { display: flex; align-items: center; gap: 12px; }
        .ifs-menu-item:hover { color: #ffffff; background: var(--ifs-surface); }
        .ifs-menu-item.active { color: #ffffff; background: #0284c7; box-shadow: 0 4px 16px rgba(2, 132, 199, 0.35); font-weight: 700; }
        .ifs-sidebar-footer { padding: 16px 20px; border-top: 1px solid var(--ifs-surface-border); background: #060911; }
        .ifs-sidebar-link { color: #ef4444; text-decoration: none; font-size: 0.85rem; font-weight: 700; display: flex; align-items: center; gap: 8px; }
        .ifs-content-wrapper { margin-left: 260px; flex-grow: 1; display: flex; flex-direction: column; min-height: 100vh; }
        .ifs-main-topbar { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); border-bottom: 1px solid var(--ifs-card-border); padding: 16px 40px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 90; }
        .ifs-topbar-title { font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0; }
        .ifs-date-pill { font-size: 0.85rem; font-weight: 700; color: #475569; background: #ffffff; border: 1.5px solid var(--ifs-card-border); padding: 7px 18px; border-radius: 30px; }
        .ifs-body { padding: 32px 40px; max-width: 1440px; width: 100%; box-sizing: border-box; margin: 0 auto; }
        .ifs-subtabs-nav { display: flex; gap: 8px; background: #e2e8f0; padding: 4px; border-radius: 10px; width: fit-content; margin-bottom: 24px; }
        .ifs-subtab-btn { padding: 8px 18px; font-size: 0.85rem; font-weight: 700; color: #475569; text-decoration: none; border-radius: 8px; transition: all 0.2s; }
        .ifs-subtab-btn:hover { color: #0284c7; }
        .ifs-subtab-btn.active { background: #ffffff; color: #0284c7; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05); }
        .ifs-card { background: #ffffff; border: 1px solid var(--ifs-card-border); border-radius: 16px; padding: 26px; box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.03); margin-bottom: 24px; }
        .ifs-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; }
        .ifs-card-title { font-size: 1.2rem; font-weight: 800; color: #0f172a; margin: 0; }
        .ifs-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-bottom: 26px; }
        .ifs-kpi-tile { background: #ffffff; border: 1px solid var(--ifs-card-border); border-radius: 14px; padding: 22px; position: relative; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.02); }
        .ifs-kpi-tile::before { content: ''; position: absolute; left: 0; top: 0; height: 100%; width: 4px; }
        .kpi-blue::before { background: #0284c7; } .kpi-green::before { background: #10b981; } .kpi-red::before { background: #ef4444; } .kpi-amber::before { background: #f59e0b; } .kpi-purple::before { background: #8b5cf6; }
        .ifs-kpi-meta { font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: #64748b; letter-spacing: 0.06em; }
        .ifs-kpi-number { font-size: 2.1rem; font-weight: 800; color: #0f172a; line-height: 1.1; margin: 8px 0 4px; font-family: 'JetBrains Mono', monospace; }
        .ifs-kpi-desc { font-size: 0.8rem; color: #94a3b8; font-weight: 600; }
        .ifs-pills-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 22px; }
        .ifs-status-counter { padding: 14px; border-radius: 12px; text-align: center; border: 1px solid transparent; }
        .sc-p { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
        .sc-a { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
        .sc-l { background: #fffbeb; border-color: #fde68a; color: #b45309; }
        .sc-count { font-size: 1.7rem; font-weight: 800; line-height: 1; margin-bottom: 4px; font-family: 'JetBrains Mono', monospace; }
        .sc-lbl { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
        .ifs-toolbar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; background: #f8fafc; padding: 14px 18px; border-radius: 12px; border: 1px solid var(--ifs-card-border); margin-bottom: 20px; }
        .ifs-field-group { margin-bottom: 18px; }
        .ifs-field-group label { display: block; font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 6px; }
        .ifs-input, .ifs-select, .ifs-textarea { width: 100%; padding: 11px 15px; border: 1.5px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; color: #0f172a; background: #ffffff; box-sizing: border-box; outline: none; transition: all 0.2s; font-family: inherit; }
        .ifs-input:focus, .ifs-select:focus, .ifs-textarea:focus { border-color: #0284c7; box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15); }
        .ifs-media-box { display: flex; align-items: center; gap: 16px; margin-bottom: 18px; }
        .ifs-media-preview { width: 72px; height: 72px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0; background: #f1f5f9; display: block; }
        .ifs-day-row { display: grid; grid-template-columns: 100px 1fr 1fr; gap: 12px; align-items: center; padding: 8px 12px; border-bottom: 1px solid #f1f5f9; }
        .ifs-btn { background: var(--ifs-accent-grad); color: #fff; border: none; padding: 10px 22px; font-size: 0.92rem; font-weight: 700; border-radius: 8px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.2s; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25); }
        .ifs-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(2, 132, 199, 0.35); color: #fff; }
        .ifs-btn-ghost { background: #ffffff; color: #475569; border: 1.5px solid #cbd5e1; padding: 7px 14px; border-radius: 8px; font-size: 0.85rem; font-weight: 700; cursor: pointer; text-decoration: none; transition: all 0.2s; }
        .ifs-btn-ghost:hover { background: #f1f5f9; color: #0f172a; border-color: #94a3b8; }
        .ifs-btn-wa { background: #25d366; color: #fff; border: none; padding: 6px 12px; border-radius: 6px; font-size: 0.78rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
        .ifs-btn-wa:hover { background: #1eb956; color: #fff; }
        .ifs-status-pill-group { display: inline-flex; background: #f1f5f9; padding: 4px; border-radius: 8px; gap: 4px; border: 1px solid #e2e8f0; }
        .ifs-status-pill-group label { padding: 6px 12px; font-size: 0.8rem; font-weight: 700; border-radius: 6px; cursor: pointer; margin: 0; user-select: none; }
        .ifs-status-pill-group input { display: none; }
        .ifs-status-pill-group input[value="Present"]:checked + span { background: #10b981; color: white; border-radius: 4px; padding: 4px 10px; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3); }
        .ifs-status-pill-group input[value="Absent"]:checked + span { background: #ef4444; color: white; border-radius: 4px; padding: 4px 10px; box-shadow: 0 2px 6px rgba(239, 68, 68, 0.3); }
        .ifs-status-pill-group input[value="Late"]:checked + span { background: #f59e0b; color: white; border-radius: 4px; padding: 4px 10px; box-shadow: 0 2px 6px rgba(245, 158, 11, 0.3); }
        .ifs-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .ifs-table th { background: #f8fafc; color: #475569; font-weight: 800; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; padding: 12px 16px; border-bottom: 2px solid #e2e8f0; text-align: left; }
        .ifs-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; font-size: 0.92rem; }
        .ifs-table tr:hover td { background: #fafcff; }
        .ifs-avatar-sm { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 1px solid #cbd5e1; vertical-align: middle; margin-right: 10px; }
        .ifs-tag { display: inline-block; padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
        .ifs-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; }
        .badge-present { background: #dcfce7; color: #15803d; }
        .badge-absent { background: #fee2e2; color: #b91c1c; }
        .badge-late { background: #fef3c7; color: #b45309; }
        .ifs-alert { padding: 12px 18px; border-radius: 10px; font-weight: 700; margin-bottom: 22px; font-size: 0.92rem; }
        .ifs-alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .ifs-alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .ifs-modal { display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); align-items: center; justify-content: center; }
        .ifs-modal.open { display: flex; }
        .ifs-modal-content { background: #fff; width: 100%; max-width: 580px; border-radius: 16px; padding: 28px; position: relative; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); max-height: 90vh; overflow-y: auto; }
        .ifs-modal-close { position: absolute; right: 20px; top: 20px; font-size: 1.4rem; color: #94a3b8; cursor: pointer; border: none; background: none; }
    </style>
    <?php
}