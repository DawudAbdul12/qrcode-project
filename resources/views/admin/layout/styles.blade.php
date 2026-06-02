    <style>
        :root {
            --blue: #2563eb;
            --blue-light: #eff4ff;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-700: #374151;
            --gray-900: #111827;
            --red: #ef4444;
            --red-light: #fef2f2;
            --white: #fff;
            --sidebar-w: 220px;
            --radius: 10px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { height: 100%; }
        body {
            height: 100vh;
            min-height: 100vh;
            display: flex;
            overflow: hidden;
            background: var(--gray-50);
            color: var(--gray-900);
            font-family: "DM Sans", sans-serif;
            font-size: 14px;
            letter-spacing: 0;
        }
        a, button { text-decoration: none; }
        button, input, textarea, select { font: inherit; }
        button { border: 0; cursor: pointer; }
        svg { display: block; flex-shrink: 0; }

        .sidebar {
            width: var(--sidebar-w);
            min-width: var(--sidebar-w);
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: var(--white);
            border-right: 1px solid var(--gray-200);
            z-index: 10;
        }
        .sidebar-logo {
            display: flex;
            align-items: center;
            padding: 20px 20px 16px;
            border-bottom: 1px solid var(--gray-100);
        }
        .active8-logo { color: var(--gray-900); font-size: 22px; font-weight: 700; font-style: italic; letter-spacing: -1px; }
        .sidebar-icons { display: flex; align-items: center; gap: 10px; margin-left: auto; color: var(--gray-400); }
        .sidebar-nav { flex: 1; padding: 12px; overflow-y: auto; }
        .nav-item {
            width: 100%;
            min-height: 38px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 2px;
            padding: 9px 12px;
            border-radius: 8px;
            background: transparent;
            color: var(--gray-700);
            font-size: 14px;
            font-weight: 500;
            text-align: left;
            text-decoration: none;
            transition: background .15s, color .15s;
        }
        .nav-item:hover { background: var(--gray-100); }
        .nav-item.active { background: var(--blue); color: var(--white); }
        .sidebar-footer { display: flex; flex-direction: column; gap: 6px; padding: 12px; border-top: 1px solid var(--gray-100); }
        .btn-refer {
            width: 100%;
            min-height: 37px;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 14px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 600;
            transition: opacity .15s, background .15s;
        }
        .btn-refer { background: transparent; color: var(--gray-700); }
        .btn-refer:hover { background: var(--gray-100); }
        .btn-refer.active { background: var(--blue-light); color: var(--blue); }
        .user-row { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 8px; cursor: pointer; }
        .user-row:hover { background: var(--gray-100); }
        .user-avatar-sm, .profile-card-avatar, .phone-avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: 700;
        }
        .user-avatar-sm { width: 28px; height: 28px; background: var(--blue); color: var(--white); font-size: 11px; }
        .user-name-sm { flex: 1; overflow: hidden; color: var(--gray-900); font-size: 13px; font-weight: 500; text-overflow: ellipsis; white-space: nowrap; }

        .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; min-height: 0; height: 100vh; }
        .screen { display: none; flex: 1; flex-direction: column; overflow: hidden; min-height: 0; }
        .screen.active { display: flex; }
        .topbar {
            flex-shrink: 0;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 28px;
            background: var(--white);
            border-bottom: 1px solid var(--gray-200);
        }
        .topbar.center { align-items: center; }
        .page-title { color: var(--gray-900); font-size: 20px; font-weight: 700; line-height: 1.2; }
        .page-subtitle { margin-top: 2px; color: var(--gray-500); font-size: 13px; }
        .topbar-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .btn-learn, .btn-share, .btn-edit-profile, .btn-add-content, .date-filter-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 600;
        }
        .btn-learn, .date-filter-btn { padding: 8px 14px; background: var(--white); border: 1px solid var(--gray-200); color: var(--gray-900); }
        .btn-learn:hover, .date-filter-btn:hover { background: var(--gray-50); }
        .btn-share, .btn-edit-profile, .btn-add-content { background: var(--blue); color: var(--white); }
        .btn-share { padding: 8px 16px; }
        .btn-share:hover, .btn-edit-profile:hover, .btn-add-content:hover { opacity: .88; }

        .profile-home-layout { flex: 1; display: flex; overflow: hidden; min-height: 0; }
        .profile-home-main { flex: 1; overflow-y: auto; padding: 24px; min-width: 0; min-height: 0; -webkit-overflow-scrolling: touch; }
        .profile-home-sidebar { width: 280px; min-width: 280px; overflow-y: auto; padding: 24px 24px 24px 0; min-height: 0; -webkit-overflow-scrolling: touch; }
        .onboarding-bar, .profile-card, .analytics-mini-card, .share-card, .link-in-bio, .card, .section-block, .mini-card, .chart-card {
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius);
        }
        .onboarding-bar { margin-bottom: 20px; overflow: hidden; padding: 20px 20px 0; }
        .onboarding-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 16px; }
        .onboarding-title, .analytics-section-title { font-size: 18px; font-weight: 700; }
        .onboarding-sub { margin-top: 2px; color: var(--gray-500); font-size: 13px; }
        .onboarding-pct { color: var(--blue); font-size: 13px; font-weight: 600; text-align: right; }
        .progress-track { width: 120px; height: 6px; margin-top: 6px; overflow: hidden; background: var(--gray-200); border-radius: 999px; }
        .progress-fill { width: 40%; height: 100%; background: var(--blue); border-radius: 999px; }
        .onboarding-steps { display: grid; grid-template-columns: repeat(3, 1fr); margin: 0 -20px; border-top: 1px solid var(--gray-200); }
        .onboarding-step { display: flex; align-items: flex-start; gap: 10px; padding: 16px 20px; border-right: 1px solid var(--gray-200); }
        .onboarding-step:last-child { border-right: 0; }
        .step-num { width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px; border-radius: 50%; background: var(--blue); color: var(--white); font-size: 11px; font-weight: 700; }
        .step-num.done { background: var(--gray-300); }
        .step-title { color: var(--gray-400); font-size: 13px; font-weight: 600; text-decoration: line-through; }
        .step-title.active { color: var(--gray-900); text-decoration: none; }
        .step-sub { margin-top: 3px; color: var(--gray-400); font-size: 12px; }
        .step-check, .step-arrow { margin-left: auto; color: var(--gray-300); }
        .step-arrow { color: var(--gray-400); }

        .profile-card { margin-bottom: 20px; overflow: hidden; }
        .profile-card-bg {
            position: relative;
            height: 130px;
            background: linear-gradient(135deg, #c4b5fd 0%, #a5f3fc 40%, #818cf8 70%, #c4b5fd 100%);
        }
        .profile-card-avatar {
            position: absolute;
            bottom: -40px;
            left: 28px;
            width: 80px;
            height: 80px;
            border: 4px solid var(--white);
            background: var(--blue-light);
            color: var(--blue);
            font-size: 28px;
        }
        .profile-card-body { padding: 52px 28px 24px; }
        .profile-card-name { font-size: 20px; font-weight: 700; }
        .profile-card-job { margin-top: 2px; color: var(--gray-500); font-size: 14px; }
        .profile-card-meta { display: flex; flex-direction: column; gap: 4px; margin-top: 8px; }
        .profile-card-meta-item { display: flex; align-items: center; gap: 6px; color: var(--gray-500); font-size: 13px; }
        .btn-edit-profile { width: 100%; margin-top: 20px; padding: 13px; font-size: 14.5px; text-decoration: none; }

        .analytics-section { margin-bottom: 20px; }
        .analytics-section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
        .view-more { display: flex; align-items: center; gap: 4px; color: var(--blue); font-size: 13px; font-weight: 500; text-decoration: none; cursor: pointer; }
        .analytics-mini-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
        .analytics-mini-card { padding: 16px; }
        .analytics-mini-label { display: flex; align-items: center; gap: 6px; margin-bottom: 8px; color: var(--gray-700); font-size: 13px; font-weight: 600; }
        .analytics-mini-value { color: var(--gray-900); font-size: 20px; font-weight: 700; }
        .admin-stat-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-bottom: 20px; }
        .admin-stat-card { display: flex; align-items: center; gap: 12px; padding: 16px; border: 1px solid var(--gray-200); border-radius: var(--radius); background: var(--white); }
        .admin-stat-icon { width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; border-radius: 8px; background: var(--blue-light); color: var(--blue); }
        .admin-stat-label { color: var(--gray-500); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
        .admin-stat-value { margin-top: 2px; color: var(--gray-900); font-size: 22px; font-weight: 700; }
        .admin-section-grid { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(320px, .85fr); gap: 20px; }
        .admin-card { margin-bottom: 20px; padding: 20px; border: 1px solid var(--gray-200); border-radius: var(--radius); background: var(--white); box-shadow: 0 10px 28px rgba(17, 24, 39, .07); }
        .admin-card-header { position: relative; display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 16px; }
        .admin-card-title { color: var(--gray-900); font-size: 17px; font-weight: 700; }
        .admin-card-sub { margin-top: 3px; color: var(--gray-500); font-size: 13px; }
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th { padding: 10px 0; border-bottom: 1px solid var(--gray-200); color: var(--gray-400); font-size: 11px; font-weight: 700; text-align: left; text-transform: uppercase; letter-spacing: .05em; }
        .admin-table td { padding: 13px 0; border-bottom: 1px solid var(--gray-100); color: var(--gray-700); font-size: 13px; vertical-align: top; }
        .admin-table th:not(:last-child), .admin-table td:not(:last-child) { padding-right: 28px; }
        .admin-table tr:last-child td { border-bottom: 0; }
        .admin-table-wrap { width: 100%; overflow-x: auto; }
        .admin-table .fit { width: 1%; white-space: nowrap; }
        .admin-table .muted-cell { color: var(--gray-400); font-size: 12px; }
        .table-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin: -2px 0 14px; }
        .table-search { position: relative; flex: 1; min-width: 220px; }
        .table-search svg { position: absolute; left: 10px; top: 50%; color: var(--gray-400); transform: translateY(-50%); }
        .table-search input { width: 100%; height: 36px; padding: 8px 10px 8px 34px; border: 1px solid var(--gray-200); border-radius: 8px; background: var(--white); color: var(--gray-900); font-size: 13px; outline: none; }
        .table-filter-row { display: flex; align-items: center; gap: 8px; }
        .table-filter-row select { height: 36px; min-width: 120px; margin-bottom: 0; padding: 7px 30px 7px 10px; font-size: 12px; }
        .table-bulk-bar { display: none; align-items: center; justify-content: space-between; gap: 10px; margin: -4px 0 14px; padding: 10px 12px; border: 1px solid var(--gray-200); border-radius: 8px; background: var(--gray-50); }
        .table-bulk-bar.active { display: flex; }
        .table-bulk-count { color: var(--gray-700); font-size: 12px; font-weight: 700; }
        .table-bulk-actions { display: flex; gap: 8px; align-items: center; }
        .table-bulk-actions .font-select { height: 34px; min-width: 128px; margin-bottom: 0; font-size: 12px; }
        .row-check { width: 16px; height: 16px; accent-color: var(--blue); }
        .row-clickable { cursor: pointer; }
        .row-clickable:hover td { background: var(--gray-50); }
        .table-pagination { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding-top: 14px; border-top: 1px solid var(--gray-100); color: var(--gray-400); font-size: 12px; }
        .pager-actions { display: flex; align-items: center; gap: 6px; }
        .pager-btn { width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid var(--gray-200); border-radius: 8px; background: var(--white); color: var(--gray-700); font-size: 14px; font-weight: 700; }
        .pager-btn:disabled { color: var(--gray-300); cursor: not-allowed; }
        .status-pill { display: inline-flex; align-items: center; min-height: 24px; padding: 4px 9px; border-radius: 999px; background: var(--gray-100); color: var(--gray-700); font-size: 12px; font-weight: 700; text-transform: capitalize; }
        .status-pill.active, .status-pill.approved, .status-pill.printed, .status-pill.genuine, .status-pill.shipped, .status-pill.delivered { background: #ecfdf3; color: #067647; }
        .status-pill.invalid, .status-pill.suspicious, .status-pill.revoked, .status-pill.expired, .status-pill.recalled { background: var(--red-light); color: var(--red); }
        .status-pill.pending, .status-pill.draft, .status-pill.invited { background: #fffaeb; color: #b54708; }
        .status-pill.suspended, .status-pill.cancelled { background: var(--red-light); color: var(--red); }
        .quick-actions { display: grid; gap: 10px; }
        .quick-action { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 13px 14px; border: 1px solid var(--gray-200); border-radius: 8px; background: var(--white); color: var(--gray-900); font-weight: 600; text-decoration: none; }
        .quick-action span { color: var(--gray-400); }
        .company-preview { overflow: hidden; padding: 0; }
        .company-cover { height: 96px; background: linear-gradient(135deg, #c4b5fd 0%, #a5f3fc 44%, #818cf8 100%); position: relative; }
        .company-avatar { position: absolute; left: 20px; bottom: -28px; width: 58px; height: 58px; display: flex; align-items: center; justify-content: center; border: 4px solid var(--white); border-radius: 50%; background: var(--blue); color: var(--white); font-weight: 700; }
        .company-preview-body { padding: 38px 20px 20px; }
        .muted-line { color: var(--gray-500); font-size: 13px; line-height: 1.45; }
        .company-page-layout { flex: 1; display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 20px; overflow-y: auto; padding: 24px 28px; min-height: 0; -webkit-overflow-scrolling: touch; }
        .companies-layout { grid-template-columns: minmax(0, 1fr); }
        .settings-users-layout { grid-template-columns: minmax(0, 1fr); }
        .shop-orders-layout { grid-template-columns: minmax(0, 1fr); }
        .batches-layout { grid-template-columns: minmax(0, 1fr); }
        .products-layout { grid-template-columns: minmax(0, 1fr); }
        .categories-layout { grid-template-columns: minmax(0, 1fr); }
        .scan-logs-layout { grid-template-columns: minmax(0, 1fr); }
        .analytics-layout { grid-template-columns: minmax(0, 1fr); }
        .company-name-cell { display: flex; align-items: center; gap: 10px; }
        .company-logo-sm { width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: var(--blue-light); color: var(--blue); font-size: 12px; font-weight: 700; flex-shrink: 0; }
        .company-main-name { color: var(--gray-900); font-weight: 700; }
        .company-sub-name { margin-top: 2px; color: var(--gray-400); font-size: 12px; }
        .company-detail-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 16px; }
        .companies-layout .company-detail-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .settings-users-layout .company-detail-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .shop-orders-layout .company-detail-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .batches-layout .company-detail-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .products-layout .company-detail-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .categories-layout .company-detail-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .scan-logs-layout .company-detail-grid { grid-template-columns: repeat(6, minmax(0, 1fr)); }
        .analytics-layout .company-detail-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .company-detail-card { padding: 14px; border: 1px solid var(--gray-200); border-radius: var(--radius); background: var(--gray-50); box-shadow: 0 8px 20px rgba(17, 24, 39, .05); }
        .company-detail-label { color: var(--gray-500); font-size: 12px; font-weight: 600; }
        .company-detail-value { margin-top: 4px; color: var(--gray-900); font-size: 18px; font-weight: 700; }
        .form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .alert-success, .alert-error { position: fixed; top: 18px; right: 18px; z-index: 70; min-width: min(340px, calc(100vw - 36px)); max-width: 420px; padding: 13px 16px; border-radius: 10px; background: var(--white); font-size: 13px; font-weight: 700; box-shadow: 0 18px 40px rgba(17, 24, 39, .16); animation: toast-in .24s ease both; }
        .alert-success { border: 1px solid #bbf7d0; color: #067647; }
        .alert-error { border: 1px solid #fecaca; color: var(--red); }
        .alert-success::before, .alert-error::before { content: ""; display: inline-block; width: 8px; height: 8px; margin-right: 9px; border-radius: 50%; vertical-align: 1px; }
        .alert-success::before { background: #12b76a; }
        .alert-error::before { background: var(--red); }
        .alert-success.toast-hide, .alert-error.toast-hide { animation: toast-out .2s ease both; }
        @keyframes toast-in { from { opacity: 0; transform: translateY(-8px) scale(.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
        @keyframes toast-out { to { opacity: 0; transform: translateY(-8px) scale(.98); } }
        .inline-edit-grid { display: grid; grid-template-columns: repeat(4, minmax(130px, 1fr)); gap: 8px; align-items: end; margin-top: 10px; }
        .compact-field label { display: block; margin-bottom: 4px; color: var(--gray-400); font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
        .compact-field input, .compact-field select { width: 100%; padding: 7px 8px; border: 1px solid var(--gray-200); border-radius: 7px; background: var(--white); color: var(--gray-900); font-size: 12px; }
        .product-actions { display: flex; gap: 8px; align-items: center; justify-content: flex-end; margin-top: 10px; }
        .danger-link { color: var(--red); background: transparent; font-size: 12px; font-weight: 700; }
        .table-actions { display: flex; align-items: center; justify-content: flex-end; gap: 8px; }
        .table-actions form { margin: 0; }
        .user-create-drawer { position: fixed; inset: 0; z-index: 40; pointer-events: none; visibility: hidden; }
        .user-create-drawer.active { pointer-events: auto; visibility: visible; }
        .drawer-backdrop { position: absolute; inset: 0; background: rgba(17, 24, 39, .38); opacity: 0; transition: opacity .22s ease; }
        .user-create-drawer.active .drawer-backdrop { opacity: 1; }
        .drawer-panel { position: absolute; top: 0; right: 0; width: min(440px, 100%); height: 100%; display: flex; flex-direction: column; background: var(--white); box-shadow: -18px 0 40px rgba(17, 24, 39, .16); transform: translateX(100%); transition: transform .28s ease; }
        .user-create-drawer.active .drawer-panel { transform: translateX(0); }
        .drawer-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; padding: 22px 24px; border-bottom: 1px solid var(--gray-200); }
        .drawer-body { flex: 1; overflow-y: auto; padding: 22px 24px 24px; }
        .drawer-close { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid var(--gray-200); border-radius: 8px; background: var(--white); color: var(--gray-500); font-size: 20px; line-height: 1; }
        .drawer-guide { margin-top: 22px; padding-top: 18px; border-top: 1px solid var(--gray-200); }
        .drawer-user-form { display: none; }
        .drawer-user-form.active { display: block; }
        #company-drawer .drawer-panel { width: min(720px, 100%); }
        .company-wizard { display: grid; gap: 18px; }
        .wizard-progress { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 6px; padding-bottom: 16px; border-bottom: 1px solid var(--gray-200); }
        .wizard-progress-step { display: grid; justify-items: center; gap: 6px; min-width: 0; padding: 7px 3px; border-radius: 8px; background: transparent; color: var(--gray-400); text-align: center; }
        .wizard-progress-step span { width: 24px; height: 24px; display: grid; place-items: center; border: 1px solid var(--gray-200); border-radius: 50%; background: var(--white); font-size: 10px; font-weight: 900; }
        .wizard-progress-step strong { overflow: hidden; max-width: 100%; font-size: 10px; text-overflow: ellipsis; white-space: nowrap; }
        .wizard-progress-step.active, .wizard-progress-step.done { color: var(--blue); }
        .wizard-progress-step.active { background: var(--blue-light); }
        .wizard-progress-step.active span, .wizard-progress-step.done span { border-color: var(--blue); background: var(--blue); color: var(--white); }
        .wizard-step { display: none; }
        .wizard-step.active { display: block; }
        .wizard-step-title { color: var(--gray-900); font-size: 17px; font-weight: 800; }
        .wizard-step-copy { margin: 4px 0 18px; color: var(--gray-500); font-size: 12px; line-height: 1.5; }
        .wizard-layout-options { display: grid; gap: 8px; margin-bottom: 14px; }
        .wizard-layout-option, .wizard-check { display: flex; align-items: flex-start; gap: 10px; padding: 11px; border: 1px solid var(--gray-200); border-radius: 8px; background: var(--gray-50); }
        .wizard-layout-option:has(input:checked), .wizard-check:has(input:checked) { border-color: rgba(47, 123, 246, .45); background: var(--blue-light); }
        .wizard-layout-option input, .wizard-check input { margin-top: 2px; accent-color: var(--blue); }
        .wizard-layout-option strong, .wizard-layout-option small, .wizard-check strong, .wizard-check small { display: block; }
        .wizard-layout-option strong, .wizard-check strong { color: var(--gray-800); font-size: 12px; }
        .wizard-layout-option small, .wizard-check small { margin-top: 3px; color: var(--gray-500); font-size: 11px; line-height: 1.35; }
        .wizard-color { height: 42px; padding: 4px; }
        .wizard-upload-grid { display: grid; grid-template-columns: minmax(0, .8fr) minmax(0, 1.2fr); gap: 10px; margin-bottom: 16px; }
        .wizard-upload-zone { display: grid; justify-items: center; gap: 6px; min-height: 142px; padding: 14px; border: 1.5px dashed var(--gray-300); border-radius: 8px; background: var(--gray-50); color: var(--gray-500); text-align: center; cursor: pointer; transition: border-color .16s ease, background .16s ease; }
        .wizard-upload-zone:hover { border-color: var(--blue); background: var(--blue-light); }
        .wizard-upload-zone input { display: none; }
        .wizard-upload-zone strong { color: var(--gray-700); font-size: 12px; }
        .wizard-upload-zone small { color: var(--gray-400); font-size: 10px; line-height: 1.4; }
        .wizard-upload-preview { width: 58px; height: 58px; display: grid; place-items: center; overflow: hidden; border: 1px solid var(--gray-200); border-radius: 12px; background: var(--white); color: var(--gray-400); }
        .wizard-upload-zone.cover .wizard-upload-preview { width: 100%; height: 58px; }
        .wizard-upload-preview img { width: 100%; height: 100%; object-fit: cover; }
        .wizard-social-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; margin-top: 14px; }
        .wizard-social-head .form-label { margin-bottom: 3px; }
        .wizard-social-copy { color: var(--gray-400); font-size: 11px; line-height: 1.4; }
        .wizard-social-list { display: grid; gap: 8px; margin-top: 10px; }
        .wizard-social-row { display: grid; grid-template-columns: 132px minmax(0, 1fr) 30px; gap: 8px; align-items: center; }
        .wizard-social-row .font-select, .wizard-social-row .input-field { min-width: 0; margin-bottom: 0; }
        .wizard-social-row .icon-btn { width: 30px; height: 30px; }
        .wizard-review-hero { position: relative; display: flex; align-items: center; gap: 12px; overflow: hidden; padding: 94px 14px 14px; border: 1px solid var(--gray-200); border-radius: 8px; background: var(--gray-50); }
        .wizard-review-cover { position: absolute; inset: 0 0 auto; height: 76px; background: linear-gradient(135deg, #1d2939, #475467); background-position: center; background-size: cover; }
        .wizard-review-hero .company-logo-sm { width: 46px; height: 46px; overflow: hidden; font-size: 14px; }
        .wizard-review-hero .company-logo-sm img, .phone-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: inherit; }
        .wizard-review-hero .company-logo-sm, .wizard-review-hero div:last-child { position: relative; z-index: 1; }
        .wizard-review-hero strong, .wizard-review-hero span { display: block; }
        .wizard-review-hero strong { color: var(--gray-900); font-size: 14px; }
        .wizard-review-hero span { margin-top: 3px; color: var(--gray-500); font-size: 12px; }
        .wizard-review-list { display: grid; gap: 0; margin-top: 14px; border: 1px solid var(--gray-200); border-radius: 8px; background: var(--white); }
        .wizard-review-list div { display: flex; justify-content: space-between; gap: 12px; padding: 11px 12px; border-bottom: 1px solid var(--gray-100); color: var(--gray-500); font-size: 12px; }
        .wizard-review-list div:last-child { border-bottom: 0; }
        .wizard-review-list strong { color: #067647; }
        .wizard-actions { display: flex; align-items: center; justify-content: flex-end; gap: 8px; padding-top: 14px; border-top: 1px solid var(--gray-200); }
        .wizard-draft { margin-right: auto; color: var(--gray-500); background: transparent; font-size: 12px; font-weight: 800; }
        .wizard-submit { display: none; }
        .product-upload-zone { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; padding: 12px; border: 1.5px dashed var(--gray-300); border-radius: 8px; background: var(--gray-50); cursor: pointer; transition: border-color .16s ease, background .16s ease; }
        .product-upload-zone:hover { border-color: var(--blue); background: var(--blue-light); }
        .product-upload-preview { width: 72px; height: 72px; display: grid; place-items: center; flex-shrink: 0; overflow: hidden; border: 1px solid var(--gray-200); border-radius: 8px; background: var(--white); color: var(--gray-400); }
        .product-upload-preview img { width: 100%; height: 100%; object-fit: cover; }
        .product-upload-zone strong, .product-upload-zone small { display: block; }
        .product-upload-zone strong { color: var(--gray-700); font-size: 12px; }
        .product-upload-zone small { margin-top: 4px; color: var(--gray-400); font-size: 11px; }
        .product-media-note { display: flex; align-items: center; gap: 7px; margin-top: -4px; color: var(--gray-500); font-size: 11px; line-height: 1.4; }
        .product-media-note.valid { color: #067647; }
        .product-media-note.invalid { color: var(--red); }
        .product-gallery-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; margin-top: 16px; }
        .product-gallery-head .form-label { margin-bottom: 3px; }
        .product-gallery-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; margin-top: 10px; }
        .product-gallery-item { position: relative; overflow: hidden; aspect-ratio: 1; border: 1px solid var(--gray-200); border-radius: 8px; background: var(--gray-50); }
        .product-gallery-item img { width: 100%; height: 100%; object-fit: cover; }
        .product-gallery-item button { position: absolute; top: 4px; right: 4px; width: 22px; height: 22px; display: grid; place-items: center; border-radius: 50%; background: rgba(17,24,39,.78); color: #fff; font-size: 16px; line-height: 1; }
        .product-wizard .suitability-editor { margin-top: 0; padding-top: 0; border-top: 0; }
        .editor-company-context { display: flex; align-items: center; gap: 8px; }
        .editor-company-context .font-select { width: auto; min-width: 190px; margin: 0; padding: 8px 30px 8px 10px; font-size: 12px; }
        #product-drawer .drawer-panel { width: min(620px, 100%); }
        .suitability-editor { margin: 18px 0 4px; padding-top: 18px; border-top: 1px solid var(--gray-200); }
        .suitability-editor-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; margin-bottom: 12px; }
        .suitability-editor-title { color: var(--gray-900); font-size: 14px; font-weight: 800; }
        .suitability-editor-copy { max-width: 440px; margin-top: 4px; color: var(--gray-500); font-size: 12px; line-height: 1.45; }
        .suitability-add { display: inline-flex; align-items: center; gap: 6px; flex-shrink: 0; padding: 8px 10px; border: 1px solid var(--gray-200); border-radius: 8px; background: var(--white); color: var(--blue); font-size: 12px; font-weight: 800; }
        .suitability-empty { display: flex; align-items: center; gap: 10px; padding: 12px; border: 1px dashed var(--gray-300); border-radius: 8px; background: var(--gray-50); }
        .suitability-empty.hidden { display: none; }
        .suitability-empty-icon { width: 26px; height: 26px; display: grid; place-items: center; flex-shrink: 0; border-radius: 7px; background: var(--blue-light); color: var(--blue); font-size: 18px; line-height: 1; }
        .suitability-empty strong, .suitability-empty span { display: block; }
        .suitability-empty strong { color: var(--gray-700); font-size: 12px; }
        .suitability-empty span { margin-top: 2px; color: var(--gray-400); font-size: 11px; line-height: 1.4; }
        .suitability-list { display: grid; gap: 10px; }
        .suitability-item { overflow: hidden; border: 1px solid var(--gray-200); border-radius: 8px; background: var(--gray-50); }
        .suitability-item-head { display: flex; align-items: center; gap: 8px; padding: 8px 9px; border-bottom: 1px solid var(--gray-200); background: var(--white); }
        .suitability-item-number { width: 20px; height: 20px; display: grid; place-items: center; border-radius: 6px; background: var(--blue-light); color: var(--blue); font-size: 10px; font-weight: 900; }
        .suitability-item-head strong { flex: 1; min-width: 0; overflow: hidden; color: var(--gray-700); font-size: 12px; text-overflow: ellipsis; white-space: nowrap; }
        .suitability-item-actions { display: flex; gap: 5px; }
        .suitability-item-actions .icon-btn { width: 26px; height: 26px; font-size: 13px; }
        .suitability-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; padding: 10px; }
        .suitability-fields .wide { grid-column: 1 / -1; }
        .suitability-fields span { display: block; margin-bottom: 4px; color: var(--gray-400); font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; }
        .suitability-fields .input-field, .suitability-fields .textarea-field, .suitability-fields .font-select { margin-bottom: 0; padding: 8px 9px; font-size: 12px; }
        .suitability-fields .textarea-field { min-height: 58px; }
        .role-preview { margin: 2px 0 16px; padding: 12px; border: 1px solid var(--gray-200); border-radius: 8px; background: var(--gray-50); }
        .role-preview-title { color: var(--gray-900); font-size: 13px; font-weight: 800; }
        .role-preview-list { display: grid; gap: 7px; margin-top: 9px; color: var(--gray-600); font-size: 12px; }
        .role-preview-list span { display: flex; gap: 7px; align-items: center; }
        .role-preview-list span::before { content: "✓"; color: #067647; font-weight: 800; }
        .activity-panel { display: none; }
        .activity-panel.active { display: block; }
        .activity-hero { display: flex; gap: 12px; align-items: center; margin-bottom: 16px; padding: 14px; border: 1px solid var(--gray-200); border-radius: 10px; background: var(--gray-50); }
        .activity-meta { display: grid; gap: 10px; margin-bottom: 16px; }
        .activity-meta-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding-bottom: 10px; border-bottom: 1px solid var(--gray-100); color: var(--gray-500); font-size: 12px; }
        .activity-meta-row strong { color: var(--gray-900); font-size: 13px; font-weight: 700; text-align: right; }
        .insight-grid { display: grid; grid-template-columns: minmax(0, 1.25fr) minmax(280px, .75fr); gap: 20px; margin-bottom: 20px; }
        .insight-card { padding: 18px; border: 1px solid var(--gray-200); border-radius: var(--radius); background: var(--white); box-shadow: 0 10px 28px rgba(17, 24, 39, .07); }
        .insight-title { color: var(--gray-900); font-size: 17px; font-weight: 800; }
        .insight-copy { margin-top: 8px; color: var(--gray-500); font-size: 13px; line-height: 1.55; }
        .delta-pill { display: inline-flex; align-items: center; min-height: 24px; padding: 4px 9px; border-radius: 999px; background: #ecfdf3; color: #067647; font-size: 12px; font-weight: 800; }
        .delta-pill.down { background: var(--red-light); color: var(--red); }
        .analytics-panel-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; margin-top: 20px; }
        .analytics-list { display: grid; gap: 12px; }
        .analytics-list-item { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding-bottom: 12px; border-bottom: 1px solid var(--gray-100); }
        .analytics-list-item:last-child { padding-bottom: 0; border-bottom: 0; }
        .analytics-filter-form { margin-top: 18px; }
        .analytics-filter-grid { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)) auto; gap: 10px; align-items: end; }
        .premium-filter { position: relative; min-width: 0; }
        .premium-filter-label { display: block; margin-bottom: 6px; color: var(--gray-500); font-size: 10px; font-weight: 900; letter-spacing: .08em; text-transform: uppercase; }
        .premium-filter select { width: 100%; height: 38px; appearance: none; padding: 0 32px 0 12px; border: 1px solid #d8e4f8; border-radius: 10px; background: linear-gradient(180deg, #fff, #f8fbff); color: var(--gray-900); font: inherit; font-size: 12px; font-weight: 800; outline: none; box-shadow: 0 8px 20px rgba(47, 123, 246, .07); }
        .premium-filter select:focus { border-color: rgba(47, 123, 246, .55); box-shadow: 0 0 0 3px rgba(47, 123, 246, .12); }
        .premium-filter::after { content: ""; position: absolute; right: 12px; bottom: 14px; width: 7px; height: 7px; border-right: 2px solid var(--gray-400); border-bottom: 2px solid var(--gray-400); pointer-events: none; transform: rotate(45deg); }
        .analytics-filter-submit { height: 38px; min-width: 42px; display: inline-flex; align-items: center; justify-content: center; border: 0; border-radius: 10px; background: var(--blue); color: var(--white); cursor: pointer; box-shadow: 0 12px 24px rgba(47, 123, 246, .22); }
        .analytics-active-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
        .analytics-chip { display: inline-flex; align-items: center; gap: 7px; height: 28px; padding: 0 10px; border: 1px solid rgba(47, 123, 246, .16); border-radius: 999px; background: var(--blue-light); color: var(--blue); font-size: 11px; font-weight: 900; text-decoration: none; }
        .analytics-chip span { color: var(--gray-700); font-weight: 800; }
        .analytics-chip.clear { background: var(--white); color: var(--gray-600); border-color: var(--gray-200); }
        .health-pill { display: inline-flex; align-items: center; gap: 6px; padding: 5px 9px; border-radius: 999px; background: var(--gray-100); color: var(--gray-700); font-size: 11px; font-weight: 900; white-space: nowrap; }
        .health-pill::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
        .health-pill.ready { background: #ecfdf3; color: #067647; }
        .health-pill.warning { background: #fffbeb; color: #b45309; }
        .health-pill.danger { background: var(--red-light); color: var(--red); }
        .health-pill.info { background: var(--blue-light); color: var(--blue); }
        .row-warning td { background: #fffbeb; }
        .row-danger td { background: #fff5f5; }
        .suspicious-table-wrap { margin-top: 20px; }
        .filter-empty { display: none; padding: 24px; text-align: center; }
        .filter-empty.active { display: table-row; }
        .filter-empty-title { color: var(--gray-900); font-size: 14px; font-weight: 800; }
        .filter-empty-copy { margin: 5px 0 14px; color: var(--gray-500); font-size: 12px; }
        .delete-alert { position: fixed; inset: 0; z-index: 80; display: none; align-items: center; justify-content: center; padding: 20px; }
        .delete-alert.active { display: flex; }
        .delete-alert-backdrop { position: absolute; inset: 0; background: rgba(17, 24, 39, .42); animation: fade-in .18s ease both; }
        .delete-alert-card { position: relative; width: min(380px, 100%); padding: 22px; border: 1px solid var(--gray-200); border-radius: 12px; background: var(--white); box-shadow: 0 22px 50px rgba(17, 24, 39, .22); animation: alert-pop .2s ease both; }
        .delete-alert-icon { width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; margin-bottom: 14px; border-radius: 10px; background: var(--red-light); color: var(--red); }
        .delete-alert-title { color: var(--gray-900); font-size: 17px; font-weight: 800; }
        .delete-alert-copy { margin-top: 6px; color: var(--gray-500); font-size: 13px; line-height: 1.45; }
        .delete-alert-input { width: 100%; margin-top: 14px; padding: 9px 11px; border: 1px solid var(--gray-200); border-radius: 8px; outline: none; }
        .delete-alert-input:focus { border-color: var(--blue); }
        .delete-alert-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 18px; }
        @keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }
        @keyframes alert-pop { from { opacity: 0; transform: translateY(8px) scale(.97); } to { opacity: 1; transform: translateY(0) scale(1); } }

        .share-card { margin-bottom: 16px; overflow: hidden; }
        .share-card-header { padding: 20px 20px 4px; text-align: center; }
        .share-card-title { font-size: 17px; font-weight: 700; }
        .share-card-sub { margin-top: 4px; color: var(--gray-500); font-size: 13px; }
        .qr-area { display: flex; justify-content: center; padding: 16px 20px; }
        .share-actions { border-top: 1px solid var(--gray-200); }
        .share-action-item {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 20px;
            background: var(--white);
            border-bottom: 1px solid var(--gray-100);
            color: var(--gray-900);
            font-size: 13.5px;
            font-weight: 500;
            text-align: left;
            transition: background .15s;
        }
        .share-action-item:last-child { border-bottom: 0; }
        .share-action-item:hover { background: var(--gray-50); }
        .share-action-icon { color: var(--gray-500); }
        .link-in-bio { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 16px; }
        .lib-label { margin-bottom: 2px; color: var(--gray-400); font-size: 11px; }
        .lib-url { max-width: 160px; overflow: hidden; color: var(--gray-700); font-size: 12px; font-weight: 500; text-overflow: ellipsis; white-space: nowrap; }
        .btn-copy { padding: 6px 14px; border: 1px solid var(--gray-200); border-radius: 8px; background: var(--white); font-size: 13px; font-weight: 600; }

        .dashboard-shell { flex: 1; overflow-y: auto; padding: 24px; background: linear-gradient(180deg, #f8fbff 0%, var(--gray-50) 42%); -webkit-overflow-scrolling: touch; }
        .dashboard-hero {
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(320px, .9fr);
            gap: 28px;
            min-height: 248px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, .16);
            border-radius: 22px;
            background: radial-gradient(circle at 84% 10%, rgba(47, 123, 246, .44), transparent 32%), linear-gradient(135deg, #07111f 0%, #111827 52%, #1d4ed8 130%);
            color: var(--white);
            box-shadow: 0 24px 60px rgba(17, 24, 39, .22);
        }
        .dashboard-hero::after { content: ""; position: absolute; right: -120px; bottom: -150px; width: 360px; height: 360px; border-radius: 50%; background: rgba(255, 255, 255, .09); }
        .dashboard-hero > * { position: relative; z-index: 1; }
        .dashboard-eyebrow { display: inline-flex; align-items: center; gap: 8px; width: fit-content; padding: 7px 10px; border: 1px solid rgba(255, 255, 255, .14); border-radius: 999px; background: rgba(255, 255, 255, .08); color: rgba(255, 255, 255, .78); font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
        .dashboard-eyebrow::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 0 5px rgba(34, 197, 94, .14); }
        .dashboard-hero-title { max-width: 620px; margin-top: 18px; font-size: clamp(30px, 4vw, 48px); line-height: 1; font-weight: 800; letter-spacing: -.04em; }
        .dashboard-hero-copy { max-width: 560px; margin-top: 14px; color: rgba(255, 255, 255, .72); font-size: 15px; line-height: 1.65; }
        .dashboard-hero-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 24px; }
        .dashboard-primary, .dashboard-secondary {
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0 16px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 800;
        }
        .dashboard-primary { background: var(--white); color: #111827; box-shadow: 0 16px 30px rgba(0, 0, 0, .18); }
        .dashboard-secondary { border: 1px solid rgba(255, 255, 255, .18); background: rgba(255, 255, 255, .08); color: var(--white); }
        .dashboard-hero-panel { align-self: stretch; display: grid; gap: 12px; padding: 16px; border: 1px solid rgba(255, 255, 255, .14); border-radius: 18px; background: rgba(255, 255, 255, .09); backdrop-filter: blur(18px); }
        .dashboard-hero-metric { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 14px; border-radius: 14px; background: rgba(255, 255, 255, .1); }
        .dashboard-hero-label { color: rgba(255, 255, 255, .68); font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; }
        .dashboard-hero-value { margin-top: 5px; font-size: 28px; font-weight: 800; letter-spacing: -.03em; }
        .dashboard-hero-note { color: rgba(255, 255, 255, .62); font-size: 12px; font-weight: 700; text-align: right; }
        .dashboard-kpis { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 12px; margin-top: 18px; }
        .dashboard-kpi { min-width: 0; padding: 16px; border: 1px solid rgba(226, 232, 240, .9); border-radius: 16px; background: rgba(255, 255, 255, .82); box-shadow: 0 14px 36px rgba(17, 24, 39, .06); }
        .dashboard-kpi-top { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .dashboard-kpi-icon { width: 34px; height: 34px; display: grid; place-items: center; border-radius: 11px; background: var(--blue-light); color: var(--blue); }
        .dashboard-kpi-label { margin-top: 18px; color: var(--gray-500); font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: .07em; }
        .dashboard-kpi-value { margin-top: 5px; color: var(--gray-900); font-size: 25px; font-weight: 850; letter-spacing: -.04em; }
        .dashboard-grid { display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(320px, .6fr); gap: 18px; margin-top: 18px; align-items: start; }
        .dashboard-stack { display: grid; gap: 18px; }
        .premium-card { overflow: hidden; border: 1px solid rgba(226, 232, 240, .9); border-radius: 18px; background: var(--white); box-shadow: 0 16px 42px rgba(17, 24, 39, .07); }
        .premium-card-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; padding: 18px 20px; border-bottom: 1px solid var(--gray-100); }
        .premium-title { color: var(--gray-900); font-size: 16px; font-weight: 850; letter-spacing: -.02em; }
        .premium-subtitle { margin-top: 4px; color: var(--gray-500); font-size: 13px; line-height: 1.45; }
        .premium-link { color: var(--blue); font-size: 12px; font-weight: 850; white-space: nowrap; }
        .premium-table-wrap { overflow-x: auto; }
        .premium-table { width: 100%; border-collapse: collapse; }
        .premium-table th { padding: 12px 20px; border-bottom: 1px solid var(--gray-100); color: var(--gray-400); font-size: 10px; font-weight: 900; text-align: left; text-transform: uppercase; letter-spacing: .08em; white-space: nowrap; }
        .premium-table td { padding: 15px 20px; border-bottom: 1px solid var(--gray-100); color: var(--gray-700); font-size: 13px; vertical-align: middle; white-space: nowrap; }
        .premium-table tr:last-child td { border-bottom: 0; }
        .premium-table strong { display: block; color: var(--gray-900); font-size: 13px; }
        .premium-table small { display: block; margin-top: 3px; color: var(--gray-400); font-size: 12px; }
        .action-grid { display: grid; gap: 12px; padding: 16px; }
        .action-card { display: flex; gap: 12px; padding: 14px; border: 1px solid var(--gray-200); border-radius: 14px; background: linear-gradient(180deg, #fff, #fbfdff); color: inherit; transition: transform .16s ease, border-color .16s ease, box-shadow .16s ease; }
        .action-card:hover { border-color: rgba(47, 123, 246, .28); box-shadow: 0 14px 28px rgba(47, 123, 246, .08); transform: translateY(-1px); }
        .action-icon { width: 38px; height: 38px; display: grid; place-items: center; flex-shrink: 0; border-radius: 12px; background: #111827; color: var(--white); }
        .action-title { color: var(--gray-900); font-size: 13px; font-weight: 850; }
        .action-copy { margin-top: 4px; color: var(--gray-500); font-size: 12px; line-height: 1.45; }
        .widget-body { display: grid; gap: 14px; padding: 16px; }
        .widget-row { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding-bottom: 13px; border-bottom: 1px solid var(--gray-100); }
        .widget-row > div:first-child { flex: 1; min-width: 0; }
        .widget-row:last-child { padding-bottom: 0; border-bottom: 0; }
        .widget-label { color: var(--gray-500); font-size: 12px; font-weight: 800; }
        .widget-value { color: var(--gray-900); font-size: 18px; font-weight: 900; letter-spacing: -.03em; text-align: right; }
        .widget-progress { height: 7px; overflow: hidden; margin-top: 8px; border-radius: 999px; background: var(--gray-100); }
        .widget-progress span { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg, var(--blue), #38bdf8); }
        .widget-list { display: grid; gap: 10px; padding: 16px; }
        .widget-list-item { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px; border: 1px solid var(--gray-100); border-radius: 13px; background: #fbfdff; }
        .widget-list-title { color: var(--gray-900); font-size: 13px; font-weight: 850; }
        .widget-list-sub { margin-top: 3px; color: var(--gray-400); font-size: 12px; }
        .audit-page-scroll { flex: 1; min-height: 0; overflow-y: auto; padding: 22px 28px 34px; -webkit-overflow-scrolling: touch; }
        .audit-hero { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 18px; margin-bottom: 18px; align-items: stretch; }
        .audit-panel { overflow: hidden; border: 1px solid rgba(226, 232, 240, .9); border-radius: 20px; background: rgba(255,255,255,.96); box-shadow: 0 18px 42px rgba(15, 23, 42, .075); }
        .audit-panel.soft { position: relative; isolation: isolate; background: linear-gradient(145deg, #07111f 0%, #0f172a 54%, #1d4ed8 130%); color: var(--white); }
        .audit-panel.soft::before { content: ""; position: absolute; inset: -42% -10% auto auto; z-index: -1; width: 420px; height: 420px; border-radius: 999px; background: radial-gradient(circle, rgba(59,130,246,.42), transparent 62%); }
        .audit-panel.soft::after { content: ""; position: absolute; inset: 0; z-index: -1; background-image: linear-gradient(rgba(255,255,255,.055) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.055) 1px, transparent 1px); background-size: 42px 42px; mask-image: linear-gradient(120deg, rgba(0,0,0,.9), transparent 75%); }
        .audit-hero-copy { height: 100%; display: flex; flex-direction: column; justify-content: center; padding: 26px; }
        .audit-eyebrow { display: inline-flex; align-items: center; gap: 8px; padding: 7px 10px; border: 1px solid rgba(147,197,253,.26); border-radius: 999px; background: rgba(37,99,235,.16); color: #bfdbfe; font-size: 11px; font-weight: 950; letter-spacing: .12em; text-transform: uppercase; }
        .audit-eyebrow::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 0 5px rgba(34,197,94,.13); }
        .audit-title { max-width: 640px; margin-top: 16px; color: var(--white); font-size: clamp(30px, 3.2vw, 42px); font-weight: 950; letter-spacing: -.055em; line-height: 1; }
        .audit-copy { max-width: 650px; margin-top: 12px; color: rgba(255,255,255,.72); font-size: 13px; line-height: 1.65; }
        .audit-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; margin-top: 18px; }
        .audit-kpi { padding: 13px; border: 1px solid rgba(255,255,255,.13); border-radius: 15px; background: rgba(255,255,255,.09); box-shadow: inset 0 1px 0 rgba(255,255,255,.08); }
        .audit-kpi span { color: rgba(255,255,255,.6); font-size: 10px; font-weight: 900; text-transform: uppercase; }
        .audit-kpi strong { display: block; margin-top: 6px; color: var(--white); font-size: 22px; font-weight: 950; }
        .audit-filter-card { padding: 18px; background: linear-gradient(180deg, #fff, #f8fbff); }
        .audit-filter-title { color: var(--gray-900); font-size: 17px; font-weight: 950; letter-spacing: -.03em; }
        .audit-filter-copy { margin-top: 5px; color: var(--gray-500); font-size: 12px; line-height: 1.5; }
        .audit-filter-grid { display: grid; gap: 10px; margin-top: 14px; }
        .audit-filter-grid label { display: grid; gap: 6px; color: var(--gray-500); font-size: 11px; font-weight: 900; text-transform: uppercase; }
        .audit-filter-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
        .audit-selected { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; }
        .audit-selected span { display: inline-flex; align-items: center; gap: 7px; padding: 7px 10px; border: 1px solid rgba(147,197,253,.2); border-radius: 999px; background: rgba(255,255,255,.09); color: rgba(255,255,255,.78); font-size: 12px; font-weight: 850; }
        .audit-selected span::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: #93c5fd; }
        .audit-layout { display: grid; grid-template-columns: minmax(0, 1fr) 330px; gap: 18px; align-items: start; }
        .audit-card-header { margin: 0; padding: 18px 20px; border-bottom: 1px solid var(--gray-100); }
        .audit-timeline { position: relative; display: grid; gap: 12px; padding: 18px; }
        .audit-timeline::before { content: ""; position: absolute; top: 26px; bottom: 26px; left: 36px; width: 2px; background: linear-gradient(#bfdbfe, rgba(191,219,254,.1)); }
        .audit-item { position: relative; display: grid; grid-template-columns: 36px minmax(0, 1fr); gap: 12px; }
        .audit-dot { position: relative; z-index: 1; width: 36px; height: 36px; display: grid; place-items: center; border: 1px solid #dbeafe; border-radius: 50%; background: #eff6ff; color: var(--blue); font-size: 11px; font-weight: 950; box-shadow: 0 0 0 6px #fff; }
        .audit-dot.qr { border-color: #bfdbfe; background: #dbeafe; }
        .audit-dot.risk { border-color: #fecaca; background: #fef2f2; color: var(--red); }
        .audit-event-card { padding: 15px; border: 1px solid var(--gray-100); border-radius: 16px; background: linear-gradient(180deg, #fff, #fbfdff); box-shadow: 0 10px 24px rgba(15, 23, 42, .045); transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease; }
        .audit-event-card:hover { border-color: rgba(37,99,235,.2); box-shadow: 0 18px 36px rgba(37,99,235,.08); transform: translateY(-1px); }
        .audit-event-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
        .audit-event-actions { display: grid; justify-items: end; gap: 7px; flex-shrink: 0; }
        .audit-event-title { color: var(--gray-900); font-size: 14px; font-weight: 900; line-height: 1.4; }
        .audit-event-meta { margin-top: 6px; color: var(--gray-400); font-size: 12px; line-height: 1.55; }
        .audit-event-time { color: var(--gray-500); font-size: 11px; font-weight: 850; white-space: nowrap; }
        .audit-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
        .audit-chip { display: inline-flex; align-items: center; gap: 6px; padding: 5px 8px; border-radius: 999px; background: var(--gray-100); color: var(--gray-600); font-size: 11px; font-weight: 850; }
        .audit-chip.qr { background: #eff6ff; color: var(--blue); }
        .audit-chip.danger { background: var(--red-light); color: var(--red); }
        .audit-pagination { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 16px 20px; border-top: 1px solid var(--gray-100); background: linear-gradient(180deg, #fff, #fbfdff); }
        .audit-pagination-meta { color: var(--gray-500); font-size: 12px; font-weight: 800; }
        .audit-pagination-actions { display: flex; align-items: center; gap: 10px; }
        .audit-page-count { color: var(--gray-500); font-size: 12px; font-weight: 850; white-space: nowrap; }
        .pager-btn.disabled { color: var(--gray-300); pointer-events: none; opacity: .65; }
        .audit-sidebar { display: grid; gap: 14px; }
        .audit-breakdown { display: grid; gap: 10px; padding: 16px; }
        .audit-breakdown-row { display: grid; gap: 7px; }
        .audit-breakdown-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; color: var(--gray-600); font-size: 12px; font-weight: 800; }
        .audit-bar { overflow: hidden; height: 7px; border-radius: 999px; background: var(--gray-100); }
        .audit-bar span { display: block; height: 100%; border-radius: inherit; background: linear-gradient(90deg, var(--blue), #60a5fa); }
        .audit-note-card { padding: 16px; border: 1px solid rgba(37,99,235,.12); border-radius: 16px; background: linear-gradient(145deg, #eff6ff, #fff); box-shadow: 0 14px 32px rgba(37,99,235,.08); }
        .audit-note-title { color: var(--gray-900); font-size: 13px; font-weight: 950; }
        .audit-note-copy { margin-top: 6px; color: var(--gray-500); font-size: 12px; line-height: 1.55; }
        .audit-checklist { padding: 16px; margin: 0; }
        @media (max-width: 1100px) { .audit-hero, .audit-layout { grid-template-columns: 1fr; } .audit-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 760px) { .audit-page-scroll { padding: 16px; } .audit-kpis { grid-template-columns: 1fr; } .audit-pagination { align-items: flex-start; flex-direction: column; } }
        @media (max-width: 640px) { .audit-event-head { display: grid; } .audit-event-actions { justify-items: start; } }
        .alert-card { align-items: flex-start; color: inherit; }
        .alert-dot { width: 9px; height: 9px; flex-shrink: 0; margin-top: 5px; border-radius: 50%; background: var(--blue); box-shadow: 0 0 0 5px var(--blue-light); }
        .alert-card.danger .alert-dot { background: var(--red); box-shadow: 0 0 0 5px var(--red-light); }
        .alert-card.warning .alert-dot { background: #d97706; box-shadow: 0 0 0 5px #fffbeb; }
        .alert-card.success .alert-dot { background: #16a34a; box-shadow: 0 0 0 5px #ecfdf3; }
        .device-widget { padding: 16px; }
        .device-bar { display: grid; gap: 10px; }
        .device-item { display: grid; gap: 7px; }
        .device-head { display: flex; justify-content: space-between; gap: 12px; color: var(--gray-500); font-size: 12px; font-weight: 800; }
        .trend-card-body { padding: 18px 20px 20px; }
        .trend-chart { position: relative; height: 180px; overflow: hidden; border-radius: 16px; background: linear-gradient(180deg, #f8fbff, #fff); }
        .trend-chart svg { position: absolute; inset: 14px 12px 28px; width: calc(100% - 24px); height: calc(100% - 42px); }
        .trend-chart .grid-line { stroke: rgba(148, 163, 184, .24); stroke-width: 1; }
        .trend-chart .trend-area { fill: rgba(37, 99, 235, .1); }
        .trend-chart .trend-line { fill: none; stroke: var(--blue); stroke-width: 3; stroke-linecap: round; stroke-linejoin: round; }
        .trend-axis { position: absolute; right: 14px; bottom: 8px; left: 14px; display: flex; justify-content: space-between; gap: 10px; color: var(--gray-400); font-size: 10px; font-weight: 800; }
        .trend-stat-row { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-top: 14px; }
        .trend-stat { padding: 12px; border: 1px solid var(--gray-100); border-radius: 13px; background: #fbfdff; }
        .trend-stat span { color: var(--gray-500); font-size: 11px; font-weight: 800; }
        .trend-stat strong { display: block; margin-top: 4px; color: var(--gray-900); font-size: 18px; font-weight: 900; letter-spacing: -.03em; }
        .tabs { flex-shrink: 0; display: flex; gap: 4px; overflow-x: auto; padding: 0 28px; background: var(--white); border-bottom: 1px solid var(--gray-200); }
        .tab {
            display: flex;
            align-items: center;
            gap: 6px;
            min-height: 46px;
            padding: 12px 14px;
            border-bottom: 2px solid transparent;
            background: transparent;
            color: var(--gray-500);
            font-size: 13.5px;
            font-weight: 500;
            white-space: nowrap;
            transition: color .15s, border-color .15s;
        }
        .tab:hover { color: var(--gray-900); }
        .tab.active { color: var(--blue); border-bottom-color: var(--blue); }
        .editor-layout { flex: 1; display: flex; overflow: hidden; min-height: 0; }
        .editor-scroll { flex: 1; min-width: 0; min-height: 0; overflow-y: auto; padding: 24px 28px; -webkit-overflow-scrolling: touch; }
        .preview-panel { width: 290px; min-width: 290px; display: flex; justify-content: center; align-items: flex-start; flex-shrink: 0; overflow-y: auto; padding: 20px 20px 20px 0; min-height: 0; -webkit-overflow-scrolling: touch; }
        .editor-page { display: none; }
        .editor-page.active { display: block; }
        .account-page { display: none; }
        .account-page.active { display: block; }
        .card { margin-bottom: 16px; padding: 24px; }
        .card-title { margin-bottom: 4px; font-size: 16px; font-weight: 700; }
        .card-sub { margin-bottom: 18px; color: var(--gray-500); font-size: 13px; }
        .section-label { margin-bottom: 8px; color: var(--gray-500); font-size: 12px; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; }
        .input-field, .textarea-field, .font-select {
            width: 100%;
            margin-bottom: 14px;
            padding: 9px 12px;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            background: var(--white);
            color: var(--gray-900);
            outline: none;
        }
        .input-field:focus, .textarea-field:focus, .font-select:focus { border-color: var(--blue); }
        .input-field.disabled { background: var(--gray-50); color: var(--gray-400); cursor: not-allowed; }
        .textarea-field { min-height: 80px; resize: vertical; }
        .smart-select { position: relative; margin-bottom: 14px; }
        .smart-select-trigger { width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 12px; border: 1px solid var(--gray-200); border-radius: 8px; background: var(--white); color: var(--gray-900); text-align: left; }
        .smart-select-trigger span { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .smart-select-trigger::after { content: "⌄"; color: var(--gray-400); font-size: 14px; }
        .smart-select.open .smart-select-trigger { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(69, 100, 255, .12); }
        .smart-select-menu { position: absolute; left: 0; right: 0; top: calc(100% + 6px); z-index: 95; display: none; overflow: hidden; border: 1px solid var(--gray-200); border-radius: 10px; background: var(--white); box-shadow: 0 18px 36px rgba(17, 24, 39, .16); }
        .smart-select.open .smart-select-menu { display: block; }
        .smart-select-search { width: calc(100% - 20px); margin: 10px; padding: 9px 10px; border: 1px solid var(--gray-200); border-radius: 8px; outline: none; }
        .smart-select-search:focus { border-color: var(--blue); }
        .smart-select-options { max-height: 220px; overflow-y: auto; padding: 4px; }
        .smart-option { width: 100%; display: block; padding: 9px 10px; border-radius: 7px; background: transparent; color: var(--gray-700); font-size: 13px; text-align: left; }
        .smart-option:hover, .smart-option.active { background: var(--gray-50); color: var(--gray-900); }
        .smart-empty { display: none; padding: 10px 12px 14px; color: var(--gray-400); font-size: 12px; }
        .smart-empty.active { display: block; }
        .form-label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; }
        .btn-save { display: flex; justify-content: flex-end; margin-top: 16px; padding-top: 12px; border-top: 1px solid var(--gray-100); }
        .btn-save button { padding: 8px 18px; border: 1px solid var(--gray-200); border-radius: 8px; background: var(--white); font-size: 13.5px; font-weight: 500; }
        .upload-zone { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; padding: 18px; border: 1.5px dashed var(--gray-200); border-radius: 8px; cursor: pointer; }
        .upload-zone:hover { border-color: var(--blue); background: var(--blue-light); }
        .editor-upload-image { width: 100%; max-height: 112px; object-fit: cover; border-radius: 6px; }
        .upload-note { margin: -10px 0 16px; padding: 10px 16px; border: 1px solid var(--gray-200); border-top: 0; border-radius: 0 0 8px 8px; background: var(--gray-50); color: var(--gray-400); font-size: 12px; }
        .section-block { margin-bottom: 14px; overflow: hidden; }
        .section-header { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 16px 20px; border-bottom: 1px solid var(--gray-100); background: var(--gray-50); }
        .section-header-title { display: flex; align-items: center; gap: 8px; font-size: 15px; font-weight: 700; }
        .section-body { padding: 20px; }
        .drag-handle { color: var(--gray-300); cursor: grab; }
        .text-red { color: var(--red); cursor: pointer; }
        .item-row { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; padding: 10px 12px; border: 1px solid var(--gray-200); border-radius: 8px; background: var(--white); }
        .item-icon { width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border-radius: 6px; color: var(--white); }
        .item-icon.red { background: var(--red); }
        .item-icon.dark { background: var(--gray-900); }
        .item-icon.email { background: #4285f4; }
        .item-icon.phone { background: #22c55e; }
        .item-info { flex: 1; min-width: 0; }
        .item-name { font-size: 13.5px; font-weight: 600; }
        .item-url { overflow: hidden; color: var(--blue); font-size: 12px; text-overflow: ellipsis; white-space: nowrap; }
        .item-actions { display: flex; gap: 4px; }
        .icon-btn { width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border-radius: 6px; background: transparent; color: var(--gray-400); }
        .icon-btn:hover { background: var(--gray-100); color: var(--gray-700); }
        .icon-btn.del:hover { background: var(--red-light); color: var(--red); }
        .btn-add { width: 100%; display: flex; align-items: center; justify-content: center; gap: 6px; margin-top: 6px; padding: 8px 14px; border: 1.5px dashed var(--gray-300); border-radius: 8px; background: transparent; color: var(--gray-500); font-size: 13px; font-weight: 500; }
        .btn-add:hover { border-color: var(--blue); background: var(--blue-light); color: var(--blue); }
        .btn-add-content { width: 100%; margin-bottom: 16px; padding: 13px; font-size: 14.5px; }
        .images-grid { display: flex; gap: 10px; flex-wrap: wrap; }
        .image-thumb, .image-add { width: 80px; height: 60px; border-radius: 6px; border: 1px solid var(--gray-200); }
        .image-add { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 2px; border-style: dashed; color: var(--gray-400); font-size: 11px; }
        .char-count { margin-bottom: 6px; color: var(--gray-400); font-size: 12px; }
        .badge-upgrade { display: inline-flex; align-items: center; gap: 4px; margin-left: 6px; padding: 2px 8px; border-radius: 20px; background: var(--blue-light); color: var(--blue); font-size: 11px; font-weight: 600; }
        .flex-between { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        .layout-options { display: flex; gap: 12px; margin-bottom: 16px; }
        .layout-opt { flex: 1; padding: 12px; border: 2px solid var(--gray-200); border-radius: 10px; text-align: center; cursor: pointer; }
        .layout-opt.selected { border-color: var(--blue); }
        .layout-preview { height: 70px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 5px; border-radius: 6px; background: var(--gray-100); }
        .lp-circle { width: 24px; height: 24px; border-radius: 50%; background: var(--gray-300); }
        .lp-line { height: 5px; border-radius: 3px; background: var(--gray-300); }
        .layout-opt-label { margin-top: 8px; color: var(--gray-500); font-size: 12px; }
        .color-row { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; padding: 9px 12px; border: 1px solid var(--gray-200); border-radius: 8px; }
        .color-swatch { width: 18px; height: 18px; border-radius: 50%; border: 2px solid var(--gray-200); }
        .color-hex { color: var(--gray-700); font-family: "DM Mono", monospace; font-size: 13.5px; }
        .buttons-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 14px; }
        .btn-style-opt { padding: 10px; border: 1.5px solid var(--gray-200); border-radius: 8px; background: var(--white); color: var(--gray-700); font-size: 13px; font-weight: 500; text-align: center; }
        .btn-style-opt.selected, .btn-style-opt:hover { border-color: var(--blue); color: var(--blue); }
        .toggle { width: 40px; height: 22px; position: relative; border-radius: 20px; background: var(--gray-200); }
        .toggle.on { background: var(--blue); }
        .toggle::after { content: ""; position: absolute; top: 2px; left: 2px; width: 18px; height: 18px; border-radius: 50%; background: var(--white); box-shadow: 0 1px 3px rgba(0,0,0,.2); transition: left .2s; }
        .toggle.on::after { left: 20px; }
        .editor-save-state { padding: 6px 9px; border-radius: 999px; background: var(--gray-100); color: var(--gray-500); font-size: 11px; font-weight: 800; }
        .editor-save-state.dirty { background: #fffaeb; color: #b54708; }
        .editor-publish-strip { display: grid; grid-template-columns: minmax(0,1fr) 160px auto; gap: 12px; align-items: center; margin-bottom: 16px; padding: 13px 14px; border: 1px solid #d8e4f8; border-radius: 8px; background: #f8fbff; }
        .editor-publish-strip strong, .editor-publish-strip span { display: block; }
        .editor-publish-strip strong { color: var(--gray-900); font-size: 13px; }
        .editor-publish-strip span { margin-top: 3px; color: var(--gray-500); font-size: 11px; }
        .editor-publish-strip b { color: var(--blue); font-size: 11px; }
        .profile-meter { height: 6px; overflow: hidden; border-radius: 999px; background: var(--gray-200); }
        .profile-meter span { display: block; height: 100%; border-radius: inherit; background: var(--blue); }
        .repeat-row { display: grid; grid-template-columns: 20px 130px minmax(0,1fr) 30px; gap: 8px; align-items: center; margin-bottom: 8px; padding: 9px; border: 1px solid var(--gray-200); border-radius: 8px; background: var(--white); }
        .repeat-row.stacked { grid-template-columns: 20px minmax(0,1fr) 30px; }
        .repeat-row.stacked .textarea-field { grid-column: 2 / 3; }
        .repeat-row .input-field, .repeat-row .textarea-field, .repeat-row .font-select { min-width: 0; margin-bottom: 0; }
        .repeat-row .textarea-field { min-height: 58px; }
        .editor-media-grid { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 16px; }
        .editor-media-grid .upload-zone { min-height: 112px; overflow: hidden; }
        .editor-file-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px; border: 1px solid var(--gray-200); border-radius: 8px; color: var(--gray-500); font-size: 12px; }
        .editor-color-grid { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: 8px; }
        .editor-color { display: grid; grid-template-columns: 1fr auto; gap: 6px; align-items: center; padding: 9px; border: 1px solid var(--gray-200); border-radius: 8px; }
        .editor-color span { color: var(--gray-500); font-size: 11px; font-weight: 800; }
        .editor-color input { width: 30px; height: 26px; grid-row: span 2; padding: 0; border: 0; background: transparent; cursor: pointer; }
        .editor-color b { color: var(--gray-700); font-family: "DM Mono", monospace; font-size: 11px; }
        .editor-switch input { position: absolute; opacity: 0; pointer-events: none; }
        .editor-switch span { width: 40px; height: 22px; display: block; position: relative; border-radius: 20px; background: var(--gray-200); cursor: pointer; }
        .editor-switch span::after { content: ""; position: absolute; top: 2px; left: 2px; width: 18px; height: 18px; border-radius: 50%; background: var(--white); box-shadow: 0 1px 3px rgba(0,0,0,.2); transition: left .2s; }
        .editor-switch input:checked + span { background: var(--blue); }
        .editor-switch input:checked + span::after { left: 20px; }
        .preview-modes { width: 230px; display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 8px; }
        .preview-modes button { padding: 5px 7px; border-radius: 6px; background: var(--gray-100); color: var(--gray-500); font-size: 10px; font-weight: 800; }
        .preview-modes button.active { background: var(--blue); color: var(--white); }
        .phone-preview-message { margin: 0 14px 14px; color: var(--gray-500); font-size: 11px; line-height: 1.45; }
        .phone-shell.desktop { width: 270px; border-radius: 14px; }
        .phone-shell.desktop .phone-screen { height: 430px; border-radius: 8px; }
        .preview-device { width: 230px; display: grid; grid-template-columns: 1fr 1fr; gap: 4px; margin-bottom: 8px; }
        .preview-device button { padding: 6px; border-radius: 6px; background: var(--gray-100); color: var(--gray-500); font-size: 10px; font-weight: 800; }
        .preview-device button.active { background: var(--gray-900); color: var(--white); }

        .phone-shell { position: sticky; top: 0; width: 230px; padding: 10px; border-radius: 36px; background: #111; box-shadow: 0 20px 60px rgba(0,0,0,.25); }
        .phone-screen { height: 500px; overflow-x: hidden; overflow-y: auto; border-radius: 28px; background: #f4f7fa; scrollbar-width: thin; scrollbar-color: rgba(152,162,179,.6) transparent; }
        .phone-screen::-webkit-scrollbar { width: 4px; }
        .phone-screen::-webkit-scrollbar-track { background: transparent; }
        .phone-screen::-webkit-scrollbar-thumb { border-radius: 20px; background: rgba(152,162,179,.6); }
        .phone-public-page { position: relative; padding-bottom: 12px; color: #101828; font-family: "DM Sans", sans-serif; }
        .phone-public-banner { position: relative; height: 136px; overflow: hidden; background: #101828; }
        .phone-public-banner::after { content: ""; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(16,24,40,.08), rgba(16,24,40,.84)); }
        .phone-public-cover { position: absolute; inset: 0; background: #344054; background-position: center; background-size: cover; }
        .phone-public-hero { position: absolute; z-index: 1; right: 11px; bottom: 11px; left: 11px; display: flex; align-items: flex-end; gap: 8px; }
        .phone-avatar { width: 52px; height: 52px; display: grid; place-items: center; flex-shrink: 0; overflow: hidden; border: 2px solid rgba(255,255,255,.94); border-radius: 10px; background: var(--white); color: var(--blue); font-size: 14px; font-weight: 900; box-shadow: 0 7px 18px rgba(16,24,40,.26); }
        .phone-public-kicker { color: rgba(255,255,255,.72); font-size: 6px; font-weight: 900; text-transform: uppercase; }
        .phone-name { margin-top: 2px; color: var(--white); font-size: 15px; font-weight: 900; line-height: 1.05; overflow-wrap: anywhere; }
        .phone-public-company { margin-top: 3px; color: rgba(255,255,255,.78); font-size: 8px; font-weight: 700; }
        .phone-public-video { position: absolute; z-index: 2; top: 9px; right: 9px; width: 25px; height: 25px; display: grid; place-items: center; border: 1px solid rgba(255,255,255,.28); border-radius: 50%; background: rgba(16,24,40,.5); color: var(--white); font-size: 9px; backdrop-filter: blur(8px); }
        .phone-public-body { padding: 9px; }
        .phone-verification { padding: 9px; border: 1px solid #d1fadf; border-left: 3px solid #16a34a; border-radius: 7px; background: var(--white); }
        .phone-verification[data-tone="warning"] { border-color: #fedf89; border-left-color: #d97706; }
        .phone-verification[data-tone="danger"] { border-color: #fecdca; border-left-color: #dc2626; }
        .phone-public-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 5px; border-radius: 20px; background: #ecfdf3; color: #16a34a; font-size: 6px; font-weight: 900; text-transform: uppercase; }
        [data-tone="warning"] .phone-public-badge { background: #fffaeb; color: #b54708; }
        [data-tone="danger"] .phone-public-badge { background: #fef3f2; color: #b42318; }
        .phone-public-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
        .phone-verification h4 { margin: 5px 0 2px; color: #101828; font-size: 11px; line-height: 1.15; }
        .phone-verification p, .phone-public-copy { margin: 0; color: #667085; font-size: 8px; line-height: 1.4; }
        .phone-public-meta { display: flex; gap: 10px; padding: 7px 1px 0; color: #667085; font-size: 7px; }
        .phone-public-meta strong { color: #101828; }
        .phone-public-help { margin-top: 7px; padding: 7px; border: 1px solid #fedf89; border-radius: 7px; background: #fffaeb; }
        .phone-public-help strong { display: block; color: #101828; font-size: 8px; }
        .phone-public-help p { margin-top: 2px; }
        .phone-public-actions { display: flex; gap: 4px; margin-top: 5px; }
        .phone-public-action { padding: 4px 6px; border-radius: 20px; background: #101828; color: #fff; font-size: 6px; font-weight: 900; }
        .phone-public-nav { display: flex; gap: 4px; overflow: hidden; margin-top: 7px; white-space: nowrap; }
        .phone-public-nav span { padding: 4px 5px; border: 1px solid #eaecf0; border-radius: 20px; background: #fff; color: #344054; font-size: 6px; font-weight: 900; }
        .phone-public-section { margin-top: 7px; padding: 8px; border: 1px solid #eaecf0; border-radius: 8px; background: #fff; box-shadow: 0 3px 8px rgba(16,24,40,.035); }
        .phone-public-section h5 { margin: 0; color: #101828; font-size: 9px; }
        .phone-public-section p { margin-top: 3px; }
        .phone-public-guidance { display: flex; gap: 5px; align-items: center; margin-top: 5px; padding: 5px; border: 1px solid #eaecf0; border-radius: 6px; color: #475467; font-size: 7px; font-weight: 700; }
        .phone-public-guidance::before { content: ""; width: 5px; height: 5px; flex-shrink: 0; border-radius: 50%; background: #d97706; }
        .phone-public-company-card { display: flex; gap: 6px; align-items: center; margin-top: 5px; }
        .phone-public-company-logo { width: 23px; height: 23px; display: grid; place-items: center; flex-shrink: 0; overflow: hidden; border-radius: 6px; background: #eef4ff; color: var(--blue); font-size: 7px; font-weight: 900; }
        .phone-public-company-logo img { width: 100%; height: 100%; object-fit: cover; }
        .phone-public-company-card strong { display: block; color: #101828; font-size: 8px; }
        .phone-public-footer { padding: 8px 4px 0; color: #98a2b3; font-size: 6px; text-align: center; }
        .phone-public-whatsapp { position: absolute; right: 8px; bottom: 8px; width: 23px; height: 23px; display: grid; place-items: center; border-radius: 50%; background: #22c55e; color: #fff; font-size: 11px; font-weight: 900; box-shadow: 0 5px 12px rgba(34,197,94,.28); }
        .phone-public-whatsapp svg { fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .phone-public-hidden { display: none !important; }
        .phone-yt-icon { width: 36px; height: 24px; display: flex; align-items: center; justify-content: center; margin: 0 16px 10px; border-radius: 5px; background: #f00; }
        .phone-yt-icon::after { content: ""; width: 0; height: 0; border-style: solid; border-width: 5px 0 5px 10px; border-color: transparent transparent transparent var(--white); }
        .phone-video-thumb { height: 70px; display: flex; align-items: center; justify-content: center; position: relative; margin: 0 10px 10px; overflow: hidden; border-radius: 8px; background: #1a1a2e; }
        .phone-video-thumb::after { content: "▶"; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; padding-left: 2px; border-radius: 50%; background: rgba(255,0,0,.85); color: var(--white); font-size: 20px; }

        .scroll-panel { flex: 1; overflow-y: auto; padding: 24px 28px; min-height: 0; -webkit-overflow-scrolling: touch; }
        .analytics-topbar { flex-shrink: 0; display: flex; align-items: center; justify-content: space-between; padding: 18px 28px; background: var(--white); border-bottom: 1px solid var(--gray-200); }
        .analytics-title { font-size: 22px; font-weight: 700; }
        .topbar-dots { color: var(--gray-500); font-size: 20px; letter-spacing: 2px; }
        .analytics-date-wrap { position: relative; z-index: 30; width: max-content; max-width: 100%; margin-bottom: 20px; }
        .analytics-date-wrap.open { z-index: 120; }
        .date-filter-btn { min-height: 46px; padding: 7px 10px 7px 14px; gap: 10px; border-color: #d8e4f8; border-radius: 12px; background: linear-gradient(180deg, #fff, #f8fbff); box-shadow: 0 12px 30px rgba(47, 123, 246, .11), inset 0 1px 0 rgba(255,255,255,.9); cursor: pointer; }
        .date-filter-btn > svg:first-child { width: 32px; height: 32px; padding: 8px; border-radius: 9px; background: var(--blue-light); color: var(--blue); }
        .date-filter-copy { display: grid; gap: 1px; min-width: 116px; text-align: left; }
        .date-filter-label { color: var(--gray-500); font-size: 10px; font-weight: 800; letter-spacing: .08em; line-height: 1; text-transform: uppercase; }
        .date-filter-value { color: var(--gray-900); font-size: 13px; font-weight: 800; line-height: 1.2; }
        .date-filter-btn .chevron { width: 28px; height: 28px; padding: 7px; border-radius: 8px; background: var(--white); color: var(--gray-500); box-shadow: inset 0 0 0 1px var(--gray-200); transition: transform .18s ease, color .18s ease, background .18s ease; }
        .date-filter-btn:hover { border-color: rgba(47, 123, 246, .34); box-shadow: 0 16px 36px rgba(47, 123, 246, .15), inset 0 1px 0 rgba(255,255,255,.9); transform: translateY(-1px); }
        .date-filter-btn:hover .chevron { background: var(--blue-light); color: var(--blue); }
        .analytics-date-wrap.open .date-filter-btn .chevron { transform: rotate(180deg); }
        .date-dropdown { position: absolute; top: calc(100% + 12px); right: 0; z-index: 120; display: none; width: min(860px, calc(100vw - 360px)); min-width: 700px; overflow: visible; border: 1px solid rgba(216, 228, 248, .95); border-radius: 18px; background: linear-gradient(135deg, #fff 0%, #f8fbff 100%); box-shadow: 0 24px 70px rgba(15, 23, 42, .16), 0 2px 8px rgba(47, 123, 246, .08); }
        .analytics-date-wrap.open .date-dropdown { display: flex; }
        .date-dropdown::before { content: ""; position: absolute; top: -7px; right: 24px; width: 14px; height: 14px; border-top: 1px solid rgba(216, 228, 248, .95); border-left: 1px solid rgba(216, 228, 248, .95); background: var(--white); transform: rotate(45deg); }
        .range-list { width: 210px; flex-shrink: 0; padding: 20px 14px; border-right: 1px solid rgba(216, 228, 248, .85); background: rgba(255,255,255,.72); }
        .date-range-summary { margin-bottom: 14px; padding: 13px 14px; border: 1px solid rgba(47, 123, 246, .16); border-radius: 12px; background: linear-gradient(180deg, #f5f9ff, #fff); box-shadow: inset 0 1px 0 rgba(255,255,255,.86); }
        .date-range-label { color: var(--gray-500); font-size: 10px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
        .date-range-value { margin-top: 4px; color: var(--gray-900); font-size: 12px; font-weight: 800; line-height: 1.35; }
        .range-item { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 12px; border-radius: 10px; color: var(--gray-700); font-size: 13px; font-weight: 800; white-space: nowrap; cursor: pointer; text-decoration: none; transition: background .16s ease, color .16s ease, transform .16s ease; }
        .range-item + .range-item { margin-top: 6px; }
        .range-item:hover { background: var(--gray-100); color: var(--gray-900); transform: translateX(2px); }
        .range-item.active { background: var(--blue); color: var(--white); box-shadow: 0 10px 22px rgba(47, 123, 246, .22); }
        .range-item.active::after { content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; opacity: .9; }
        .calendars { display: grid; flex: 1; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 24px; padding: 24px 26px 26px; }
        .cal { min-width: 0; }
        .cal-header { display: grid; grid-template-columns: 30px 1fr 30px; align-items: center; margin-bottom: 16px; color: var(--gray-900); font-size: 15px; font-weight: 900; text-align: center; }
        .cal-nav { width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; border-radius: 9px; color: var(--gray-500); font-size: 20px; font-weight: 500; line-height: 1; }
        .cal-nav:not(:empty) { background: var(--white); box-shadow: inset 0 0 0 1px var(--gray-200); }
        .cal-grid { display: grid; grid-template-columns: repeat(7, 34px); gap: 7px; justify-content: center; }
        .cal-day-header { color: var(--gray-500); font-size: 11px; font-weight: 900; letter-spacing: .04em; text-align: center; text-transform: uppercase; }
        .cal-day { width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; border: 0; border-radius: 10px; background: transparent; color: var(--gray-400); font: inherit; font-size: 13px; font-weight: 800; cursor: pointer; transition: background .16s ease, color .16s ease, box-shadow .16s ease; }
        .cal-day:hover { background: var(--white); color: var(--gray-900); box-shadow: inset 0 0 0 1px var(--gray-200); }
        .cal-day.in-range { border-radius: 10px; background: #dbeafe; color: #2f7bf6; }
        .cal-day.range-start, .cal-day.range-end { border-radius: 10px; background: var(--blue); color: var(--white); box-shadow: 0 8px 18px rgba(47, 123, 246, .26); }
        .cal-day.empty { visibility: hidden; pointer-events: none; }
        .chart-card { position: relative; margin-bottom: 20px; overflow: hidden; }
        .contacts-card { position: absolute; top: 16px; right: 16px; z-index: 2; min-width: 160px; padding: 14px 18px; border: 1px solid var(--gray-200); border-radius: var(--radius); background: var(--white); box-shadow: 0 2px 8px rgba(0,0,0,.07); }
        .contacts-card-title { margin-bottom: 4px; font-size: 13px; font-weight: 700; }
        .contacts-card-sub { color: var(--gray-400); font-size: 12px; }
        .line-chart-canvas-area { height: 330px; display: grid; grid-template-columns: 38px minmax(0, 1fr); gap: 10px; overflow: hidden; padding: 42px 20px 0; }
        .chart-y-axis { position: relative; height: 100%; }
        .chart-y-label { position: absolute; right: 0; color: var(--gray-400); font-size: 11px; line-height: 1; transform: translateY(-50%); }
        .chart-svg { width: 100%; height: 100%; }
        .bottom-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .mini-card { position: relative; padding: 20px; }
        .mini-card-title { margin-bottom: 14px; font-size: 16px; font-weight: 700; }
        .download-icon { position: absolute; top: 16px; right: 16px; color: var(--gray-400); }
        .donut-wrap { display: flex; flex-direction: column; align-items: center; gap: 12px; }
        .donut-legend { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; font-size: 13px; }
        .legend-dot { width: 14px; height: 7px; border-radius: 2px; background: var(--blue); }
        .list-card-body { display: grid; gap: 12px; }
        .list-card-row { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding-bottom: 12px; border-bottom: 1px solid var(--gray-200); }
        .list-card-row:last-child { padding-bottom: 0; border-bottom: 0; }

        .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; flex: 1; padding: 40px; color: var(--gray-400); text-align: center; }
        .empty-state svg { margin-bottom: 12px; color: var(--gray-300); }
        .empty-state h2 { margin-bottom: 6px; color: var(--gray-500); font-size: 18px; font-weight: 600; }
        .empty-state p { font-size: 13px; }
        .empty-guide { display: flex; align-items: center; justify-content: center; gap: 14px; min-height: 150px; padding: 28px; color: var(--gray-500); text-align: left; }
        .empty-guide-icon { width: 42px; height: 42px; display: grid; place-items: center; flex-shrink: 0; border-radius: 13px; background: var(--blue-light); color: var(--blue); }
        .empty-guide-title { color: var(--gray-900); font-size: 15px; font-weight: 900; }
        .empty-guide-copy { max-width: 420px; margin: 5px 0 14px; color: var(--gray-500); font-size: 13px; line-height: 1.45; }

        @media (max-width: 1120px) {
            body { overflow: hidden; }
            .profile-home-layout, .editor-layout { flex-direction: column; }
            .profile-home-sidebar { width: auto; min-width: 0; padding: 0 24px 24px; flex-shrink: 0; }
            .company-page-layout { grid-template-columns: 1fr; }
            .dashboard-hero, .dashboard-grid { grid-template-columns: 1fr; }
            .dashboard-kpis { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .insight-grid, .analytics-panel-grid { grid-template-columns: 1fr; }
            .analytics-filter-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) auto; }
            .preview-panel { display: none; }
        }
        @media (max-width: 760px) {
            html, body { height: auto; min-height: 100%; }
            body { display: block; overflow: auto; }
            .main { height: auto; min-height: 100vh; overflow: visible; }
            .screen { overflow: visible; }
            .sidebar { width: 100%; height: auto; min-width: 0; }
            .sidebar-nav { display: grid; grid-template-columns: repeat(2, 1fr); }
            .sidebar-footer { display: none; }
            .topbar, .analytics-topbar { align-items: flex-start; flex-direction: column; padding: 16px; }
            .profile-home-layout, .editor-layout { overflow: visible; }
            .profile-home-main, .profile-home-sidebar, .editor-scroll, .scroll-panel, .company-page-layout { overflow: visible; }
            .profile-home-main, .editor-scroll, .scroll-panel { padding: 16px; }
            .profile-home-sidebar { padding: 0 16px 16px; }
            .company-page-layout { padding: 16px; }
            .dashboard-shell { overflow: visible; padding: 16px; }
            .dashboard-hero { padding: 22px; border-radius: 18px; }
            .dashboard-hero-panel { padding: 12px; }
            .dashboard-kpis { grid-template-columns: 1fr; }
            .table-toolbar, .table-filter-row { align-items: stretch; flex-direction: column; }
            .table-bulk-bar, .table-bulk-actions { align-items: stretch; flex-direction: column; }
            .table-search { min-width: 0; width: 100%; }
            .table-filter-row select, .table-bulk-actions .font-select { width: 100%; }
            .analytics-filter-grid { grid-template-columns: 1fr; }
            .analytics-filter-submit { width: 100%; }
            .onboarding-steps, .analytics-mini-grid, .bottom-row { grid-template-columns: 1fr; }
            .admin-stat-grid, .company-detail-grid, .form-row-2, .inline-edit-grid { grid-template-columns: 1fr; }
            .wizard-upload-grid { grid-template-columns: 1fr; }
            .wizard-social-head { align-items: stretch; flex-direction: column; }
            .wizard-social-row { grid-template-columns: 1fr 30px; }
            .wizard-social-row .font-select { grid-column: 1 / -1; }
            .onboarding-step { border-right: 0; border-bottom: 1px solid var(--gray-200); }
            .analytics-date-wrap { width: 100%; }
            .date-filter-btn { width: 100%; justify-content: flex-start; }
            .date-filter-copy { min-width: 0; }
            .date-dropdown { left: 0; right: auto; width: calc(100vw - 32px); min-width: 0; }
            .date-dropdown::before { left: 24px; right: auto; }
            .analytics-date-wrap.open .date-dropdown { display: block; }
            .range-list { width: auto; padding: 14px; border-right: 0; border-bottom: 1px solid var(--gray-200); }
            .calendars { grid-template-columns: 1fr; padding: 18px 14px; }
            .cal-grid { grid-template-columns: repeat(7, minmax(28px, 34px)); gap: 6px; }
            .cal-day { width: 100%; }
            .contacts-card { position: static; margin: 12px; }
            .layout-options { flex-direction: column; }
            .editor-publish-strip { grid-template-columns: 1fr; }
            .editor-media-grid, .editor-color-grid { grid-template-columns: 1fr; }
            .repeat-row, .repeat-row.stacked { grid-template-columns: 18px minmax(0,1fr) 28px; }
            .repeat-row .font-select { grid-column: 2 / 3; }
            .repeat-row .input-field { grid-column: 2 / 3; }
        }
    </style>
