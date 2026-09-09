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

        /* যেকোনো ওয়ার্ডপ্রেস থিমে ফুল-উইডথ ব্রেকআউট */
        .ifs-fullscreen-wrapper {
            width: 100vw !important;
            position: relative !important;
            left: 50% !important;
            right: 50% !important;
            margin-left: -50vw !important;
            margin-right: -50vw !important;
            min-height: 100vh !important;
            background: #f8fafc !important;
            box-sizing: border-box !important;
            overflow-x: hidden !important;
            z-index: 10;
        }

        /* আল্ট্রা স্লিম স্ক্রোলবার */
        html { scroll-behavior: smooth; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #f8fafc; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .ifs-custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .ifs-custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .ifs-custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        .ifs-custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #0284c7; }

        * { scrollbar-width: thin; scrollbar-color: #cbd5e1 #f8fafc; }
        .ifs-custom-scrollbar { scrollbar-width: thin; scrollbar-color: #e2e8f0 transparent; }

        /* অ্যাডমিনবার হাইড */
        #adminmenumain, #adminmenuback, #wpadminbar, #wpfooter { display: none !important; }
        #wpcontent, #wpfooter { margin-left: 0 !important; padding: 0 !important; }
        html.wp-toolbar { padding-top: 0 !important; }

        :root {
            --ifs-bg: #f8fafc;
            --ifs-surface: #ffffff;
            --ifs-border: #e2e8f0;
            --ifs-border-hover: #cbd5e1;
            --ifs-border-focus: #0284c7;
            --ifs-focus-ring: rgba(2, 132, 199, 0.12);
            --ifs-accent: #0284c7;
            --ifs-accent-grad: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);
            --ifs-text-main: #0f172a;
            --ifs-text-sub: #334155;
            --ifs-text-muted: #64748b;
        }

        body {
            background: var(--ifs-bg) !important;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif !important;
            color: var(--ifs-text-main) !important;
            margin: 0 !important;
            -webkit-font-smoothing: antialiased;
        }

        .ifs-shell { display: flex; min-height: 100vh; background: var(--ifs-bg); }

        /* সাইডবার */
        .ifs-sidebar {
            width: 260px; background: #ffffff; color: var(--ifs-text-muted); display: flex; flex-direction: column;
            position: fixed; top: 0; bottom: 0; left: 0; z-index: 999;
            border-right: 1.5px solid var(--ifs-border);
            box-shadow: 1px 0 10px rgba(0, 0, 0, 0.02);
        }
        .ifs-sidebar-brand {
            padding: 22px 20px; display: flex; align-items: center; gap: 12px;
            border-bottom: 1.5px solid var(--ifs-border); background: #ffffff;
        }
        .ifs-brand-icon {
            width: 38px; height: 38px; border-radius: 10px; background: var(--ifs-accent-grad);
            display: flex; align-items: center; justify-content: center; color: white;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
        }
        .ifs-brand-name { font-size: 1.05rem; font-weight: 800; color: #0f172a; line-height: 1.2; }
        .ifs-brand-sub { font-size: 0.68rem; color: #0284c7; text-transform: uppercase; font-weight: 800; letter-spacing: 0.08em; display: block; margin-top: 2px; }

        .ifs-menu-col { display: flex; flex-direction: column; gap: 3px; padding: 16px 12px; flex-grow: 1; overflow-y: auto; }
        .ifs-menu-heading { font-size: 0.66rem; font-weight: 800; text-transform: uppercase; color: #94a3b8; letter-spacing: 0.08em; padding: 12px 12px 4px; }
        .ifs-menu-item {
            display: flex; align-items: center; justify-content: space-between; padding: 9px 12px; font-size: 0.88rem;
            font-weight: 600; color: #475569; text-decoration: none; border-radius: 8px; transition: all 0.15s ease;
        }
        .ifs-menu-item-left { display: flex; align-items: center; gap: 10px; }
        .ifs-menu-item:hover { color: #0284c7; background: #f1f5f9; }
        .ifs-menu-item.active { color: #0284c7; background: #e0f2fe; font-weight: 700; }

        .ifs-sidebar-footer { padding: 16px 20px; border-top: 1.5px solid var(--ifs-border); background: #ffffff; }
        .ifs-sidebar-link { color: #ef4444; text-decoration: none; font-size: 0.85rem; font-weight: 700; display: flex; align-items: center; gap: 8px; }

        /* কনটেন্ট এরিয়া */
        .ifs-content-wrapper { margin-left: 260px; flex-grow: 1; display: flex; flex-direction: column; min-height: 100vh; background: #f8fafc; }
        .ifs-main-topbar {
            background: #ffffff; border-bottom: 1.5px solid var(--ifs-border);
            padding: 16px 40px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 90;
        }
        .ifs-topbar-title { font-size: 1.3rem; font-weight: 800; color: #0f172a; margin: 0; }
        .ifs-date-pill { font-size: 0.82rem; font-weight: 700; color: #475569; background: #f8fafc; border: 1.5px solid var(--ifs-border); padding: 6px 14px; border-radius: 20px; }

        .ifs-body { padding: 30px 40px; max-width: 1440px; width: 100%; box-sizing: border-box; margin: 0 auto; }

        /* সাবট্যাব ও কার্ড */
        .ifs-subtabs-nav { display: flex; gap: 6px; background: #e2e8f0; padding: 4px; border-radius: 10px; width: fit-content; margin-bottom: 22px; }
        .ifs-subtab-btn { padding: 7px 16px; font-size: 0.82rem; font-weight: 700; color: #475569; text-decoration: none; border-radius: 8px; transition: all 0.2s; }
        .ifs-subtab-btn:hover { color: #0284c7; }
        .ifs-subtab-btn.active { background: #ffffff; color: #0284c7; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04); }

        .ifs-card { background: #ffffff; border: 1.5px solid var(--ifs-border); border-radius: 14px; padding: 24px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02); margin-bottom: 22px; }
        .ifs-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .ifs-card-title { font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0; }

        /* KPI কার্ড গ্রিড */
        .ifs-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .ifs-kpi-tile { background: #ffffff; border: 1.5px solid var(--ifs-border); border-radius: 12px; padding: 20px; position: relative; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.015); }
        .ifs-kpi-tile::before { content: ''; position: absolute; left: 0; top: 0; height: 100%; width: 4px; }
        .kpi-blue::before { background: #0284c7; } .kpi-green::before { background: #10b981; } .kpi-red::before { background: #ef4444; } .kpi-amber::before { background: #f59e0b; } .kpi-purple::before { background: #8b5cf6; }
        .ifs-kpi-meta { font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: #64748b; letter-spacing: 0.06em; }
        .ifs-kpi-number { font-size: 1.95rem; font-weight: 800; color: #0f172a; line-height: 1.1; margin: 6px 0 2px; font-family: 'JetBrains Mono', monospace; }
        .ifs-kpi-desc { font-size: 0.78rem; color: #94a3b8; font-weight: 600; }

        /* ড্যাশবোর্ড ও হাজিরা কাউন্টার পিলস */
        .ifs-pills-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 22px; }
        .ifs-status-counter { padding: 16px 20px; border-radius: 12px; text-align: center; border: 1.5px solid transparent; transition: all 0.2s ease; }
        .sc-p { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
        .sc-a { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
        .sc-l { background: #fffbeb; border-color: #fde68a; color: #b45309; }
        .sc-count { font-size: 2rem; font-weight: 800; line-height: 1; margin-bottom: 4px; font-family: 'JetBrains Mono', monospace; }
        .sc-lbl { font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; }

        /* ফিল্টার টুলবার */
        .ifs-toolbar {
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;
            background: #f8fafc; padding: 14px 18px; border-radius: 12px; border: 1.5px solid var(--ifs-border); margin-bottom: 20px;
        }

        /* হাজিরা সিলেকশন বাটন গ্রুপ */
        .ifs-status-pill-group { display: inline-flex; background: #f1f5f9; padding: 4px; border-radius: 10px; gap: 4px; border: 1.5px solid var(--ifs-border); }
        .ifs-status-pill-group label { padding: 6px 12px; font-size: 0.8rem; font-weight: 700; border-radius: 7px; cursor: pointer; margin: 0; user-select: none; display: flex; align-items: center; justify-content: center; }
        .ifs-status-pill-group input { display: none; }
        .ifs-status-pill-group label span { display: inline-block; padding: 2px 4px; }
        .ifs-status-pill-group input[value="Present"]:checked + span { background: #10b981; color: #fff; border-radius: 6px; padding: 4px 10px; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.35); }
        .ifs-status-pill-group input[value="Absent"]:checked + span { background: #ef4444; color: #fff; border-radius: 6px; padding: 4px 10px; box-shadow: 0 2px 6px rgba(239, 68, 68, 0.35); }
        .ifs-status-pill-group input[value="Late"]:checked + span { background: #f59e0b; color: #fff; border-radius: 6px; padding: 4px 10px; box-shadow: 0 2px 6px rgba(245, 158, 11, 0.35); }

        /* রিপোর্ট ও অ্যানালিটিক্স প্রগ্রেস বার */
        .ifs-progress { background: #e2e8f0; border-radius: 10px; overflow: hidden; height: 8px; width: 100px; display: inline-block; vertical-align: middle; margin-right: 8px; }
        .ifs-progress-bar { height: 100%; border-radius: 10px; transition: width 0.3s ease; }

        /* ========================================================
           প্রফেশনাল ফর্ম ইনপুট ফিল্ড আর্কিটেকচার (PRO FORM INPUTS)
           ======================================================== */
        .ifs-field-group {
            margin-bottom: 18px;
            position: relative;
        }
        .ifs-field-group label {
            display: block;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--ifs-text-sub);
            margin-bottom: 7px;
        }

        .ifs-input, 
        .ifs-select, 
        .ifs-textarea {
            width: 100%;
            height: 42px;
            padding: 9px 14px;
            font-size: 0.9rem;
            font-weight: 500;
            line-height: 1.5;
            color: var(--ifs-text-main);
            background-color: #ffffff;
            border: 1.5px solid var(--ifs-border);
            border-radius: 9px;
            outline: none;
            box-sizing: border-box;
            font-family: inherit;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
        }

        .ifs-textarea {
            height: auto;
            min-height: 95px;
            resize: vertical;
        }

        .ifs-input:hover, 
        .ifs-select:hover, 
        .ifs-textarea:hover {
            border-color: var(--ifs-border-hover);
        }

        .ifs-input:focus, 
        .ifs-select:focus, 
        .ifs-textarea:focus {
            border-color: var(--ifs-border-focus);
            background-color: #ffffff;
            box-shadow: 0 0 0 3.5px var(--ifs-focus-ring), 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .ifs-input::placeholder,
        .ifs-textarea::placeholder {
            color: #94a3b8;
            font-weight: 400;
            font-size: 0.88rem;
        }

        .ifs-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            padding-right: 36px;
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M4 6l4 4 4-4'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 14px;
        }

        input[type="date"].ifs-input,
        input[type="month"].ifs-input {
            cursor: pointer;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1e293b;
            font-weight: 600;
        }

        .ifs-input:disabled, 
        .ifs-select:disabled, 
        .ifs-input[readonly] {
            background-color: #f1f5f9 !important;
            border-color: #e2e8f0 !important;
            color: #64748b !important;
            cursor: not-allowed;
            box-shadow: none !important;
        }

        /* বাটন আর্কিটেকচার */
        .ifs-btn {
            background: var(--ifs-accent-grad); color: #fff; border: none; height: 42px; padding: 0 22px; font-size: 0.88rem; font-weight: 700;
            border-radius: 9px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;
            transition: all 0.2s; box-shadow: 0 2px 8px rgba(2, 132, 199, 0.25);
        }
        .ifs-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35); color: #fff; }
        .ifs-btn-ghost {
            background: #ffffff; color: #475569; border: 1.5px solid var(--ifs-border); height: 36px; padding: 0 14px; border-radius: 8px; font-size: 0.82rem;
            font-weight: 700; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; transition: all 0.15s;
        }
        .ifs-btn-ghost:hover { background: #f8fafc; color: #0f172a; border-color: var(--ifs-border-hover); }
        .ifs-btn-wa {
            background: #25d366; color: #ffffff; border: none; padding: 5px 12px; border-radius: 6px; font-size: 0.76rem;
            font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;
        }
        .ifs-btn-wa:hover { background: #1eb956; color: #ffffff; }

        /* টেবিল, ব্যাজ এবং ট্যাগ */
        .ifs-table { width: 100%; border-collapse: separate; border-spacing: 0; }
        .ifs-table th { background: #f8fafc; color: #475569; font-weight: 800; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; padding: 12px 14px; border-bottom: 1.5px solid var(--ifs-border); text-align: left; }
        .ifs-table td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; font-size: 0.88rem; color: #1e293b; }
        .ifs-table tr:hover td { background: #f8fafc; }
        .ifs-avatar-sm { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; border: 1.5px solid var(--ifs-border); vertical-align: middle; margin-right: 8px; }
        .ifs-tag { display: inline-block; padding: 2px 8px; border-radius: 6px; font-size: 0.72rem; font-weight: 700; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
        .ifs-badge { display: inline-block; padding: 3px 8px; border-radius: 16px; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; }
        .badge-present { background: #dcfce7; color: #15803d; }
        .badge-absent { background: #fee2e2; color: #b91c1c; }
        .badge-late { background: #fef3c7; color: #b45309; }

        /* অ্যালার্ট ও মোডাল */
        .ifs-alert { padding: 12px 16px; border-radius: 10px; font-weight: 700; margin-bottom: 20px; font-size: 0.88rem; }
        .ifs-alert-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .ifs-alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        .ifs-modal { display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.45); backdrop-filter: blur(4px); align-items: center; justify-content: center; }
        .ifs-modal.open { display: flex; }
        .ifs-modal-content { background: #ffffff; width: 100%; max-width: 580px; border-radius: 16px; padding: 28px; position: relative; box-shadow: 0 20px 45px rgba(0, 0, 0, 0.1); max-height: 90vh; overflow-y: auto; border: 1.5px solid var(--ifs-border); }
        .ifs-modal-close { position: absolute; right: 20px; top: 20px; font-size: 1.4rem; color: #94a3b8; cursor: pointer; border: none; background: none; line-height: 1; }
        .ifs-modal-close:hover { color: #ef4444; }

        /* মিডিয়া আপলোড প্রিভিউ */
        .ifs-media-box { display: flex; align-items: center; gap: 16px; margin-bottom: 18px; }
        .ifs-media-preview { width: 68px; height: 68px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0; background: #f1f5f9; display: block; }
    </style>
    <?php
}