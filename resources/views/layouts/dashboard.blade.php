<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php $brand = $siteName ?? config('app.name', 'One Stop'); @endphp
    <title>Dashboard - {{ $brand }}</title>
    
    <!-- Typography: Poppins (headlines) + Roboto (body) + Montserrat (accent) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Font Awesome (for additional icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @stack('styles')
    <style>
        :root {
            --font-headline: 'Poppins', sans-serif, Arial, Helvetica;
            --font-body: 'Roboto', sans-serif, Arial, Helvetica;
            --font-accent: 'Montserrat', Arial, Helvetica, sans-serif;
            --text-primary: #0d0d0d;
            --text-secondary: #6e6e80;
            --bg-page: #f7f7f8;
            --bg-card: #ffffff;
            --border-subtle: #e5e5e5;
            --primary-green: #28a745;
            --dark-green: #1e7e34;
            --light-green: #d4edda;
            --primary-blue: #007bff;
            --dark-blue: #0056b3;
            --light-blue: #e8f4fd;
            --white: #ffffff;
            --black: #0d0d0d;
            --dark-gray: #0d0d0d;
            --light-gray: #f7f7f8;
            --border-gray: #e5e5e5;
            --text-gray: #6e6e80;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.04);
            --shadow-md: 0 2px 8px rgba(0,0,0,0.06);
            --shadow-lg: 0 4px 16px rgba(0,0,0,0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-body);
            font-weight: 400;
            background-color: var(--bg-page);
            color: var(--text-primary);
            font-size: 14px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        h1, h2, h3, h4, h5, h6,
        .headline-font,
        .page-header h2,
        .card-title {
            font-family: var(--font-headline);
            font-weight: 600;
            letter-spacing: -0.02em;
            color: var(--text-primary);
        }
        .accent-font {
            font-family: var(--font-accent);
        }

        /* Prices / sales emphasis */
        .price,
        .price-text,
        .money,
        .amount,
        .sales-amount {
            font-family: var(--font-body);
            font-weight: 700;
        }

        /* Scrollbar styling */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: var(--border-gray);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: var(--text-secondary);
        }

        /* Summary boxes (e.g. earnings) */
        .dashboard-summary-box {
            background: var(--light-gray);
            border: 1px solid var(--border-subtle);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }

        .dashboard-summary-value {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            letter-spacing: -0.02em;
            margin-bottom: 4px;
        }

        .dashboard-summary-label {
            font-size: 0.8125rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: var(--bg-card);
            border-right: 1px solid var(--border-subtle);
            z-index: 1000;
            transition: width 0.25s ease, box-shadow 0.25s ease;
            overflow: visible;
            overflow-y: auto;
            box-shadow: var(--shadow-sm);
        }

        .sidebar-header {
            padding: 20px 20px;
            background: transparent;
            color: var(--black);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .sidebar-header h4,
        .sidebar-brand-text {
            font-family: sans-serif;
        }
        .sidebar-header h4 {
            margin: 0;
            font-weight: 600;
            font-size: 1.25rem;
            letter-spacing: -0.02em;
            color: var(--black);
        }

        .sidebar-header .bi-shop {
            color: var(--black);
        }

        .sidebar-header p {
            margin: 4px 0 0 0;
            font-size: 0.8125rem;
            color: var(--text-secondary);
            font-weight: 400;
        }

        .sidebar-menu {
            padding: 12px 0;
        }

        .sidebar-menu .nav-item {
            margin: 2px 8px;
        }

        .sidebar-menu .nav-link {
            color: var(--black);
            padding: 10px 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: background 0.15s ease, color 0.15s ease;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .sidebar-menu .nav-link .sidebar-label {
            white-space: nowrap;
            overflow: hidden;
            transition: opacity 0.2s ease, max-width 0.25s ease;
        }

        .sidebar-menu .nav-link:hover {
            background-color: var(--primary-green);
            color: #fff;
        }

        .sidebar-menu .nav-link:hover i {
            color: #fff;
        }

        .sidebar-menu .nav-link.active {
            background-color: var(--dark-green);
            color: #fff;
            font-weight: 600;
        }

        .sidebar-menu .nav-link.active i {
            color: #fff;
        }

        .sidebar-menu .nav-link:hover .sidebar-submenu-chevron,
        .sidebar-menu .nav-link.active .sidebar-submenu-chevron {
            color: #fff;
            opacity: 0.95;
        }

        .sidebar-menu .nav-link i {
            width: 20px;
            min-width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        /* Menu / Meals submenu (under Menu in side panel) */
        .sidebar-has-submenu .sidebar-menu-toggle {
            cursor: pointer;
        }
        .sidebar-has-submenu .sidebar-menu-toggle .sidebar-submenu-chevron {
            font-size: 0.75rem;
            opacity: 0.8;
        }
        .sidebar-has-submenu:has(.sidebar-submenu.show) .sidebar-submenu-chevron {
            transform: rotate(180deg);
        }
        .sidebar-submenu {
            padding-left: 0;
            margin-left: 0;
        }
        .sidebar-submenu-item {
            margin: 0;
        }
        .sidebar-submenu-link {
            padding: 8px 14px 8px 2.5rem !important;
            font-size: 0.8125rem;
            color: var(--black);
            border-radius: 6px;
            margin: 0 8px;
            display: flex;
            align-items: center;
        }
        .sidebar-submenu-link:hover {
            background-color: var(--primary-green);
            color: #fff;
        }

        .sidebar-submenu-link:hover i {
            color: #fff;
        }
        .sidebar-submenu-link i {
            width: auto;
            min-width: auto;
            font-size: 0.9rem;
        }
        .sidebar.collapsed .sidebar-submenu,
        .sidebar.collapsed .sidebar-submenu-chevron {
            display: none !important;
        }

        /* Sidebar collapsed: icons only; expand on hover */
        .sidebar.collapsed {
            width: 72px;
        }

        .sidebar.collapsed .sidebar-brand-text,
        .sidebar.collapsed .sidebar-brand-desc {
            max-width: 0;
            min-width: 0;
            opacity: 0;
            overflow: hidden;
            padding: 0;
            margin: 0;
        }

        /* Collapsed: icons only; labels show as flyout tooltip on link hover */
        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding-left: 16px;
            position: relative;
        }

        .sidebar.collapsed .nav-link .sidebar-label {
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            margin-left: 10px;
            background: var(--primary-green);
            color: #fff;
            padding: 6px 12px;
            border-radius: 8px;
            white-space: nowrap;
            font-size: 0.85rem;
            font-weight: 500;
            z-index: 1002;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity 0.2s ease, visibility 0.2s ease;
            box-shadow: var(--shadow-md);
        }

        .sidebar.collapsed .nav-link:hover .sidebar-label {
            opacity: 1;
            visibility: visible;
        }

        .sidebar.collapsed .nav-link .badge {
            position: absolute;
            top: 4px;
            right: 4px;
            min-width: 18px;
            height: 18px;
            font-size: 0.7rem;
            padding: 0 5px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar.collapsed .sidebar-header > div:first-child {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar.collapsed .sidebar-header h4 .bi-shop {
            margin: 0;
        }

        .sidebar.collapsed:hover {
            width: 260px;
            box-shadow: var(--shadow-lg);
        }

        .sidebar.collapsed:hover .sidebar-brand-text,
        .sidebar.collapsed:hover .sidebar-brand-desc {
            max-width: 180px;
            opacity: 1;
        }

        .sidebar.collapsed:hover .nav-link .sidebar-label {
            position: static;
            transform: none;
            margin-left: 0;
            background: transparent !important;
            color: inherit !important;
            padding: 0;
            box-shadow: none;
            opacity: 1;
            visibility: visible;
            max-width: 180px;
        }

        .sidebar.collapsed:hover .sidebar-header > div:first-child {
            justify-content: flex-start;
        }

        .sidebar.collapsed:hover .nav-link {
            justify-content: flex-start;
        }

        .sidebar.collapsed:hover .nav-link .badge {
            position: static;
            margin-left: auto;
        }

        /* Main Content Area */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
            transition: margin-left 0.25s ease;
        }

        body.sidebar-collapsed .main-content {
            margin-left: 72px;
        }

        body.sidebar-collapsed.sidebar-hover-expanded .main-content {
            margin-left: 260px;
        }

        /* Top Navbar */
        .top-navbar {
            background: var(--bg-card);
            padding: 14px 24px;
            border-bottom: 1px solid var(--border-subtle);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: none;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .top-navbar .navbar-brand {
            font-family: sans-serif;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 1.125rem;
            letter-spacing: -0.02em;
        }

        .top-navbar .navbar-brand .navbar-logo {
            display: block;
            flex-shrink: 0;
        }

        .sidebar-header .sidebar-logo {
            flex-shrink: 0;
            object-fit: contain;
        }

        .top-navbar .user-menu {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        /* Floating actions: language switcher above WhatsApp */
        .floating-actions-stack {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
        }
        .lang-fab-switcher {
            display: flex;
            gap: 4px;
            padding: 4px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.12);
            border: 1px solid rgba(0, 0, 0, 0.06);
        }
        .lang-fab-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.25rem;
            height: 2rem;
            padding: 0 0.55rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
            text-decoration: none;
            color: #495057;
            transition: background 0.2s ease, color 0.2s ease;
        }
        .lang-fab-btn:hover {
            color: var(--primary-green, #22c55e);
            background: rgba(34, 197, 94, 0.12);
        }
        .lang-fab-btn.active {
            background: var(--primary-green, #22c55e);
            color: #fff;
        }
        .whatsapp-fab {
            position: relative;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(145deg, #2ee66a 0%, #25d366 50%, #20bd5a 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(37, 211, 102, 0.4);
            text-decoration: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .whatsapp-fab:hover {
            color: #fff;
            transform: scale(1.08);
        }
        .whatsapp-fab i {
            font-size: 1.75rem;
        }
        @media (max-width: 575.98px) {
            .floating-actions-stack {
                bottom: max(16px, env(safe-area-inset-bottom));
                right: max(16px, env(safe-area-inset-right));
            }
            .whatsapp-fab {
                width: 52px;
                height: 52px;
            }
        }

        /* Admin: top bar uses Bootstrap primary blue (falls back to --primary-blue in :root) */
        .top-navbar--admin-blue {
            background: var(--bs-primary, var(--primary-blue));
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        .top-navbar--admin-blue .navbar-brand {
            color: #fff;
        }
        .top-navbar--admin-blue .dashboard-title {
            color: #fff !important;
        }
        .top-navbar--admin-blue .navbar-brand .bi-speedometer2 {
            color: rgba(255, 255, 255, 0.95) !important;
        }
        .top-navbar--admin-blue .hamburger-btn {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.35);
            color: #fff;
        }
        .top-navbar--admin-blue .hamburger-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            color: #fff;
        }
        .top-navbar--admin-blue .hamburger-btn:focus {
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.35);
        }
        .top-navbar--admin-blue .user-info:hover {
            background: rgba(255, 255, 255, 0.12);
        }
        .top-navbar--admin-blue .user-avatar {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            border-color: rgba(255, 255, 255, 0.35);
        }
        .top-navbar--admin-blue .user-info .d-none.d-md-block > div:first-child {
            color: #fff !important;
        }
        .top-navbar--admin-blue .user-info .d-none.d-md-block > div:last-child {
            color: rgba(255, 255, 255, 0.85) !important;
        }
        .top-navbar--admin-blue .profile-dropdown .dropdown-toggle::after {
            border-top-color: #fff;
        }
        .top-navbar--admin-blue a.user-info {
            color: #fff;
        }
        .top-navbar--admin-blue a.user-info:hover,
        .top-navbar--admin-blue a.user-info:focus {
            color: #fff;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 6px 12px;
            border-radius: 8px;
            transition: background 0.2s ease;
        }

        .user-info:hover {
            background: var(--light-gray);
        }

        .profile-dropdown .dropdown-toggle::after {
            margin-left: 0.35rem;
        }
        .profile-dropdown .dropdown-menu {
            min-width: 180px;
        }

        .dashboard-cart-icon {
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        .dashboard-cart-icon:hover {
            background: var(--light-gray) !important;
            color: var(--text-primary, #212529) !important;
        }
        .dashboard-cart-icon:hover i {
            color: var(--text-primary, #212529) !important;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--light-gray);
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
            border: 1px solid var(--border-subtle);
        }

        /* Content Wrapper */
        .content-wrapper {
            padding: 28px 24px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Dashboard Cards */
        .dashboard-card {
            background: var(--bg-card);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: none;
            border: 1px solid var(--border-subtle);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .dashboard-card:hover {
            border-color: var(--border-gray);
            box-shadow: var(--shadow-sm);
        }

        .dashboard-card .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border-subtle);
            padding-bottom: 14px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dashboard-card .card-title {
            font-size: 1.0625rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
            letter-spacing: -0.02em;
        }

        .dashboard-card .card-footer {
            background: var(--light-gray);
            border-top: 1px solid var(--border-subtle);
            padding: 14px 24px;
            margin: 0 -24px -24px;
            border-radius: 0 0 12px 12px;
        }

        /* Stats Cards */
        .stat-card {
            background: var(--bg-card);
            border-radius: 12px;
            padding: 22px 20px;
            text-align: center;
            box-shadow: none;
            border: 1px solid var(--border-subtle);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            border-color: var(--border-gray);
            box-shadow: var(--shadow-sm);
        }

        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px;
            font-size: 1.25rem;
            color: var(--white);
        }

        .stat-card.stat-green .stat-icon {
            background: var(--primary-green);
        }

        .stat-card.stat-blue .stat-icon {
            background: var(--primary-blue);
        }

        .stat-card .stat-value {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 4px;
            letter-spacing: -0.02em;
        }

        .stat-card .stat-label {
            color: var(--text-secondary);
            font-size: 0.8125rem;
            font-weight: 500;
        }
        .stat-card .stat-change {
            font-size: 0.75rem;
            opacity: 0.75;
            margin-top: 8px;
        }

        /* Clickable stat cards (dashboard) */
        a.stat-card-link {
            display: block;
            text-decoration: none;
            color: inherit;
            height: 100%;
            cursor: pointer;
            border-radius: 12px;
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }
        a.stat-card-link:hover {
            box-shadow: var(--shadow-sm);
            border-color: var(--border-gray);
        }
        a.stat-card-link:hover .stat-card-link-hint {
            opacity: 1;
        }
        .stat-card-link-hint {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--primary-green);
            margin-top: 8px;
            opacity: 0.7;
            transition: opacity 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        a.stat-card-link.stat-card-link-blue:hover .stat-card-link-hint {
            color: var(--primary-blue);
        }
        a.stat-card-link .stat-card:hover {
            transform: none;
        }

        /* Buttons */
        .btn-primary, .btn-success {
            background: var(--primary-green);
            border-color: var(--primary-green);
            color: var(--white);
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.2s ease;
            border: none;
            font-size: 0.9rem;
        }

        .btn-primary:hover, .btn-success:hover {
            background: var(--dark-green);
            border-color: var(--dark-green);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.25);
        }

        .btn-outline-primary {
            border-color: var(--primary-blue);
            color: var(--primary-blue);
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.2s ease;
            background: transparent;
            font-size: 0.9rem;
        }

        .btn-outline-primary:hover {
            background: var(--primary-blue);
            border-color: var(--primary-blue);
            color: var(--white);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.25);
        }

        .btn-outline-success {
            border-color: var(--primary-green);
            color: var(--primary-green);
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.2s ease;
            background: transparent;
            font-size: 0.9rem;
        }

        .btn-outline-success:hover {
            background: var(--primary-green);
            border-color: var(--primary-green);
            color: var(--white);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.25);
        }

        /* Tables */
        .table {
            background: var(--white);
            border-radius: 0;
            overflow: hidden;
            margin-bottom: 0;
        }

        .table thead {
            background: var(--light-gray);
            color: var(--text-secondary);
        }

        .table thead th {
            border: none;
            padding: 12px 16px;
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.02em;
            color: var(--text-secondary);
            border-bottom: 1px solid var(--border-subtle);
        }

        .table tbody tr {
            transition: background 0.15s ease;
            border-bottom: 1px solid var(--border-subtle);
        }

        .table tbody tr:last-child {
            border-bottom: none;
        }

        .table tbody tr:hover {
            background-color: var(--light-gray) !important;
        }
        .table tbody tr:hover td,
        .table-striped tbody tr:nth-of-type(odd) td {
            background-color: var(--light-gray) !important;
            box-shadow: none !important;
        }

        .table tbody td {
            padding: 14px 16px;
            vertical-align: middle;
            color: var(--text-primary);
        }

        /* Badges */
        .badge {
            padding: 5px 10px;
            font-weight: 600;
            border-radius: 6px;
            font-size: 0.75rem;
            letter-spacing: 0.3px;
        }

        .badge-success {
            background-color: var(--primary-green);
            color: var(--white);
        }

        .badge-primary {
            background-color: var(--primary-blue);
            color: var(--white);
        }

        .badge-warning {
            background-color: #ffc107;
            color: #000;
        }

        .badge-danger {
            background-color: #dc3545;
            color: var(--white);
        }

        /* Alerts */
        .alert {
            border-radius: 8px;
            border: none;
            padding: 15px 20px;
        }

        .alert-success {
            background-color: var(--light-green);
            color: var(--dark-green);
            border-left: 4px solid var(--primary-green);
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        /* Hamburger menu button */
        .hamburger-btn {
            display: inline-flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 44px;
            height: 44px;
            padding: 0;
            border: 1px solid var(--border-gray);
            border-radius: 8px;
            background: var(--white);
            color: #495057;
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }
        .hamburger-btn:hover {
            background: #f8f9fa;
            color: var(--primary-green);
            border-color: var(--primary-green);
        }
        .hamburger-btn:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.25);
        }
        .hamburger-btn .hamburger-line {
            width: 20px;
            height: 2px;
            background: currentColor;
            border-radius: 1px;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }
        .hamburger-btn .hamburger-line + .hamburger-line {
            margin-top: 5px;
        }
        .hamburger-btn[aria-expanded="true"] .hamburger-line:nth-child(1) {
            transform: translateY(7px) rotate(45deg);
        }
        .hamburger-btn[aria-expanded="true"] .hamburger-line:nth-child(2) {
            opacity: 0;
        }
        .hamburger-btn[aria-expanded="true"] .hamburger-line:nth-child(3) {
            transform: translateY(-7px) rotate(-45deg);
        }
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.25);
            z-index: 999;
            transition: opacity 0.3s ease;
            pointer-events: auto;
        }
        .sidebar-overlay.active {
            display: block;
        }
        /* Never let overlay hide body content: on desktop no overlay; on mobile overlay only for tap-to-close, content stays visible when sidebar closed */
        @media (min-width: 993px) {
            .sidebar-overlay.active {
                display: none !important;
            }
        }
        /* Hamburger visible on mobile and tablet; sidebar pushes content right when open */
        @media (max-width: 992px) {
            .sidebar {
                margin-left: -260px;
                transform: translateX(0);
                transition: margin-left 0.3s ease;
            }
            .sidebar.active {
                margin-left: 0;
            }
            .main-content {
                margin-left: 0 !important;
                transition: margin-left 0.3s ease;
            }
            /* When sidebar is open: push content to the right and reduce content width */
            body.sidebar-open .main-content {
                margin-left: 260px !important;
            }
        }
        /* Desktop: sidebar visible by default; hamburger toggles collapse (narrow/wide) */
        @media (min-width: 993px) {
            .sidebar {
                margin-left: 0;
            }
        }
        @media (max-width: 768px) {
            .content-wrapper {
                padding: 16px 12px;
            }
            .top-navbar {
                padding: 12px 16px;
            }
            .navbar-brand {
                font-size: 1rem;
            }
            .page-header h2 {
                font-size: 1.25rem !important;
            }
            .page-header p {
                font-size: 0.8125rem;
            }
            .stat-card {
                padding: 18px 16px;
            }
            .stat-card .stat-value {
                font-size: 1.5rem;
            }
            .stat-card .stat-icon {
                width: 44px;
                height: 44px;
                font-size: 1.1rem;
            }
            .dashboard-card {
                padding: 18px 16px;
            }
            .dashboard-card .card-header {
                padding-bottom: 12px;
                margin-bottom: 16px;
            }
            .dashboard-card .card-title {
                font-size: 1rem;
            }
            .table-responsive {
                margin-left: -16px;
                margin-right: -16px;
                padding-left: 16px;
                padding-right: 16px;
                -webkit-overflow-scrolling: touch;
                overflow-x: auto;
            }
            .table {
                font-size: 0.875rem;
            }
            .table thead th,
            .table tbody td {
                padding: 10px 12px;
            }
            .table thead th {
                white-space: nowrap;
            }
            .nav-pills {
                flex-wrap: wrap;
                gap: 0.35rem;
            }
            .nav-pills .nav-link {
                padding: 6px 12px;
                font-size: 0.8125rem;
            }
        }
        @media (max-width: 575.98px) {
            .content-wrapper {
                padding: 12px 10px;
            }
            .table-responsive {
                margin-left: -10px;
                margin-right: -10px;
                padding-left: 10px;
                padding-right: 10px;
            }
        }

        /* Page Header */
        .page-header {
            margin-bottom: 28px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--border-subtle);
        }

        .page-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 6px;
            letter-spacing: -0.02em;
        }

        .page-header p {
            color: var(--text-secondary);
            margin: 0;
            font-size: 0.875rem;
        }

        /* Filter pills in card headers */
        .nav-pills .nav-link {
            padding: 6px 14px;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 8px;
            color: var(--text-secondary);
            transition: background 0.2s ease, color 0.2s ease;
        }
        .nav-pills .nav-link:hover {
            background: var(--light-gray);
            color: var(--text-primary);
        }
        .nav-pills .nav-link.active {
            background: var(--text-primary);
            color: var(--white);
        }
        .nav-pills.nav-pills-blue .nav-link.active {
            background: var(--primary-blue);
        }

        /* Breadcrumb */
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 16px;
        }

        .breadcrumb-item a {
            color: var(--text-secondary);
            text-decoration: none;
        }

        .breadcrumb-item a:hover {
            color: var(--text-primary);
        }

        .breadcrumb-item.active {
            color: var(--text-secondary);
        }

        /* Pagination sizing */
        .pagination {
            margin-top: 1rem;
        }

        .pagination .page-link {
            padding: 4px 10px;
            font-size: 0.8rem;
            border-radius: 999px;
        }

        .pagination .page-item:first-child .page-link,
        .pagination .page-item:last-child .page-link {
            font-size: 0.75rem;
            padding-inline: 8px;
        }

        /* Top-right cart added toast (aligned below top nav / Sign In) */
        .cart-toast {
            position: fixed;
            top: 4.5rem;
            right: 1rem;
            z-index: 9999;
            padding: 0.75rem 1.25rem;
            background: var(--primary-green, #28a745);
            color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            font-size: 0.9375rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            opacity: 0;
            transform: translateX(120%);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        .cart-toast.show {
            opacity: 1;
            transform: translateX(0);
        }
        .cart-toast i { font-size: 1.25rem; }
    </style>
    @php
        $isApproved = auth()->check() && (auth()->user()->status === 'approved' || auth()->user()->role === 'admin' || auth()->user()->role === 'customer');
    @endphp
</head>
<body class="@if(!$isApproved) sidebar-collapsed @endif">
    <!-- Overlay when sidebar is open on mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>

    <!-- Sidebar -->
    <div class="sidebar @if(!$isApproved) collapsed @endif" id="sidebar">
        <div class="sidebar-header d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center flex-grow-1 min-w-0">
                <h4 class="mb-0 d-flex align-items-center flex-grow-1 min-w-0">
                    @if(file_exists(public_path('images/logo 01.webp')))
                        <img src="{{ asset('images/logo 01.webp') }}" alt="{{ $brand }}" height="60" class="me-2 sidebar-logo flex-shrink-0">
                    @elseif(file_exists(public_path('images/logo 02.avif')))
                        <img src="{{ asset('images/logo 02.avif') }}" alt="{{ $brand }}" height="60" class="me-2 sidebar-logo flex-shrink-0">
                    @elseif(file_exists(public_path('images/one stop food logo 01.jpeg')))
                        <img src="{{ asset('images/one stop food logo 01.jpeg') }}" alt="{{ $brand }}" height="60" class="me-2 sidebar-logo flex-shrink-0">
                    @else
                        <i class="bi bi-shop me-2 flex-shrink-0"></i>
                    @endif
                    <span class="sidebar-brand-text text-nowrap">{{ $brand }}</span>
                </h4>
            </div>
            <button type="button" class="btn btn-link text-white d-md-none p-0 ms-2 align-self-start" id="sidebarClose" aria-label="Close menu">
                <i class="bi bi-x-lg fs-4"></i>
            </button>
        </div>
        <nav class="sidebar-menu">
            <ul class="nav flex-column">
                @if(auth()->user()->status === 'approved' || auth()->user()->role === 'admin' || auth()->user()->role === 'customer')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" title="Dashboard">
                            <i class="bi bi-speedometer2"></i>
                            <span class="sidebar-label">{{ __('dashboard.dashboard') }}</span>
                        </a>
                    </li>
                @endif
                @auth
                    @if(auth()->user()->status === 'approved' || auth()->user()->role === 'admin' || auth()->user()->role === 'customer')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('chefs.*') ? 'active' : '' }}" href="{{ route('chefs.index') }}" title="Our Chefs">
                                <i class="bi bi-person-badge"></i>
                                <span class="sidebar-label">{{ __('dashboard.chefs') }}</span>
                            </a>
                        </li>
                    @endif
                    @if(auth()->user()->role === 'admin')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}" title="Manage Users">
                                <i class="bi bi-people"></i>
                                <span class="sidebar-label">{{ __('dashboard.users') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.verifications.*') ? 'active' : '' }}" href="{{ route('admin.verifications.index') }}" title="Verifications">
                                <i class="bi bi-patch-check"></i>
                                <span class="sidebar-label">{{ __('dashboard.verifications') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}" title="Orders">
                                <i class="bi bi-list-check"></i>
                                <span class="sidebar-label">{{ __('dashboard.orders') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.finance.*') ? 'active' : '' }}" href="{{ route('admin.finance.index') }}" title="Finance">
                                <i class="bi bi-cash-stack"></i>
                                <span class="sidebar-label">{{ __('dashboard.finance') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->routeIs('admin.billing.*') || request()->routeIs('admin.invoices.*') || request()->routeIs('invoices.*')) ? 'active' : '' }}" href="{{ route('admin.invoices.index') }}" title="Billing && Invoice">
                                <i class="bi bi-receipt"></i>
                                <span class="sidebar-label">{{ __('dashboard.billing_invoice') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.logistics.*') ? 'active' : '' }}" href="{{ route('admin.logistics.index') }}" title="{{ __('dashboard.logistics') }}">
                                <i class="bi bi-truck"></i>
                                <span class="sidebar-label">{{ __('dashboard.logistics') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.disputes.*') ? 'active' : '' }}" href="{{ route('admin.disputes.index') }}" title="Disputes">
                                <i class="bi bi-exclamation-octagon"></i>
                                <span class="sidebar-label">{{ __('dashboard.disputes') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}" href="{{ route('admin.notifications.index') }}" title="Notifications">
                                <i class="bi bi-megaphone"></i>
                                <span class="sidebar-label">{{ __('dashboard.notifications') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}" href="{{ route('admin.analytics.index') }}" title="Analytics">
                                <i class="bi bi-graph-up"></i>
                                <span class="sidebar-label">{{ __('dashboard.analytics') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.config.*') ? 'active' : '' }}" href="{{ route('admin.config.index') }}" title="Configuration">
                                <i class="bi bi-gear"></i>
                                <span class="sidebar-label">{{ __('dashboard.config') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.zones.*') ? 'active' : '' }}" href="{{ route('admin.zones.index') }}" title="Zones">
                                <i class="bi bi-geo-alt"></i>
                                <span class="sidebar-label">{{ __('dashboard.zones') }}</span>
                            </a>
                        </li>
                    @endif
                    
                    @if(auth()->user()->role === 'chef' && auth()->user()->status === 'approved')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('chef.meals.*') ? 'active' : '' }}" href="{{ route('chef.meals.index') }}" title="My Meals">
                                <i class="bi bi-egg-fried"></i>
                                <span class="sidebar-label">{{ __('dashboard.my_meals') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('chef.orders.*') ? 'active' : '' }}" href="{{ route('chef.orders.index') }}" title="Orders">
                                <i class="bi bi-cart-check"></i>
                                <span class="sidebar-label">{{ __('dashboard.orders') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('chef.logistics.*') ? 'active' : '' }}" href="{{ route('chef.logistics.index') }}" title="{{ __('dashboard.logistics_travelers') }}">
                                <i class="bi bi-truck"></i>
                                <span class="sidebar-label">{{ __('dashboard.logistics_travelers') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('chef.earnings') ? 'active' : '' }}" href="{{ route('chef.earnings') }}" title="Earnings">
                                <i class="bi bi-cash-coin"></i>
                                <span class="sidebar-label">{{ __('dashboard.earnings') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->routeIs('billing.index') || request()->routeIs('invoices.*')) ? 'active' : '' }}" href="{{ route('invoices.index') }}" title="Billing && Invoice">
                                <i class="bi bi-receipt"></i>
                                <span class="sidebar-label">{{ __('dashboard.billing_invoice') }}</span>
                            </a>
                        </li>
                    @endif

                    @if(auth()->user()->role === 'traveler' && auth()->user()->status === 'approved')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('traveler.deliveries') ? 'active' : '' }}" href="{{ route('traveler.deliveries') }}" title="Deliveries">
                                <i class="bi bi-truck"></i>
                                <span class="sidebar-label">{{ __('dashboard.deliveries') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('traveler.earnings') ? 'active' : '' }}" href="{{ route('traveler.earnings') }}" title="Earnings">
                                <i class="bi bi-cash-coin"></i>
                                <span class="sidebar-label">{{ __('dashboard.earnings') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->routeIs('billing.index') || request()->routeIs('invoices.*')) ? 'active' : '' }}" href="{{ route('invoices.index') }}" title="Billing && Invoice">
                                <i class="bi bi-receipt"></i>
                                <span class="sidebar-label">{{ __('dashboard.billing_invoice') }}</span>
                            </a>
                        </li>
                    @endif

                    @if(auth()->user()->role === 'customer')
                        <li class="nav-item sidebar-has-submenu">
                            <button type="button" class="nav-link sidebar-menu-toggle w-100 text-start border-0 bg-transparent d-flex align-items-center {{ request()->routeIs('meals.index') ? 'active' : '' }}" data-bs-toggle="collapse" data-bs-target="#sidebarMenuSubmenu" aria-expanded="{{ request()->routeIs('meals.index') ? 'true' : 'false' }}" id="sidebarMenuSubmenuToggle" aria-controls="sidebarMenuSubmenu" title="Menu / Meals">
                                <i class="bi bi-menu-button-wide"></i>
                                <span class="sidebar-label">{{ __('dashboard.menu_meals') }}</span>
                                <i class="bi bi-chevron-down sidebar-submenu-chevron ms-auto transition-transform"></i>
                            </button>
                            <ul class="collapse sidebar-submenu list-unstyled mb-0 {{ request()->routeIs('meals.index') ? 'show' : '' }}" id="sidebarMenuSubmenu">
                                <li class="sidebar-submenu-item">
                                    <a class="nav-link sidebar-submenu-link" href="{{ route('meals.index') }}"><i class="bi bi-grid-3x3-gap me-2"></i>{{ __('dashboard.all_meals') }}</a>
                                </li>
                                @foreach(\App\Models\Meal::getStandardCategories() as $catKey => $cat)
                                    <li class="sidebar-submenu-item">
                                        <a class="nav-link sidebar-submenu-link" href="{{ route('meals.index', ['category' => $catKey]) }}">{{ $catKey }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('cart.index') ? 'active' : '' }}" href="{{ route('cart.index') }}" title="Cart">
                                <i class="bi bi-cart"></i>
                                <span class="sidebar-label">{{ __('dashboard.cart') }}</span>
                                @php $cartCount = array_sum(session('cart', [])); @endphp
                                @if($cartCount > 0)
                                    <span class="badge bg-success ms-auto">{{ $cartCount }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('customer.orders') ? 'active' : '' }}" href="{{ route('customer.orders') }}" title="My Orders">
                                <i class="bi bi-bag-check"></i>
                                <span class="sidebar-label">{{ __('dashboard.my_orders') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ (request()->routeIs('billing.index') || request()->routeIs('invoices.*')) ? 'active' : '' }}" href="{{ route('invoices.index') }}" title="Billing && Invoice">
                                <i class="bi bi-receipt"></i>
                                <span class="sidebar-label">{{ __('dashboard.billing_invoice') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('locations.*') ? 'active' : '' }}" href="{{ route('locations.index') }}" title="My Addresses">
                                <i class="bi bi-geo-alt"></i>
                                <span class="sidebar-label">{{ __('dashboard.my_addresses') }}</span>
                            </a>
                        </li>
                    @endif

                    @if(auth()->user()->status === 'approved' || auth()->user()->role === 'admin' || auth()->user()->role === 'customer')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('home') }}" title="Back to Home">
                                <i class="bi bi-house"></i>
                                <span class="sidebar-label">{{ __('dashboard.back_to_home') }}</span>
                            </a>
                        </li>
                    @endif
                @endauth
            </ul>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar @if(auth()->check() && auth()->user()->role === 'admin') top-navbar--admin-blue @endif">
            <div class="d-flex align-items-center">
                @if($isApproved)
                    <button class="hamburger-btn me-3" id="sidebarToggle" type="button" aria-label="Open menu" aria-expanded="false" aria-controls="sidebar" title="Menu">
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                        <span class="hamburger-line"></span>
                    </button>
                @endif
                @php
                    $isApproved = auth()->user()->status === 'approved' || auth()->user()->role === 'admin' || auth()->user()->role === 'customer';
                @endphp
                <a class="navbar-brand d-flex align-items-center gap-2 text-decoration-none" href="{{ $isApproved ? route('dashboard') : '#' }}">
                    <i class="bi bi-speedometer2" style="font-size: 1.25rem; color: var(--primary-green);"></i>
                    <span class="dashboard-title fw-bold" style="color: var(--dark-gray);">
                        @auth
                            @if(auth()->user()->role === 'chef')
                                {{ $isApproved ? __('dashboard.chef_dashboard') : __('dashboard.chef_verification') }}
                            @elseif(auth()->user()->role === 'admin')
                                {{ __('dashboard.admin_dashboard') }}
                            @elseif(auth()->user()->role === 'traveler')
                                {{ $isApproved ? __('dashboard.traveler_dashboard') : __('dashboard.traveler_verification') }}
                            @else
                                {{ __('dashboard.customer_dashboard') }}
                            @endif
                        @else
                            {{ __('dashboard.dashboard') }}
                        @endauth
                    </span>
                </a>
            </div>
            <div class="user-menu d-flex align-items-center gap-3">
                @auth
                    @if(auth()->user()->role === 'customer')
                        @php $dashboardCartCount = array_sum(session('cart', [])); @endphp
                        <button type="button" class="dashboard-cart-icon position-relative border-0 bg-transparent p-2 rounded-circle d-flex align-items-center justify-content-center" data-bs-toggle="modal" data-bs-target="#cartModal" title="{{ __('nav.cart') }}" aria-label="{{ __('nav.cart') }}">
                            <i class="bi bi-cart3" style="font-size: 1.35rem; color: var(--text-secondary, #6c757d);"></i>
                            @if($dashboardCartCount > 0)
                                <span class="position-absolute top-0 end-0 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; min-width: 1.1rem;">{{ $dashboardCartCount }}</span>
                            @endif
                        </button>
                    @endif
                    <div class="dropdown profile-dropdown" id="profileDropdown">
                        <a href="#" class="user-info text-decoration-none d-flex align-items-center gap-2 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Profile menu">
                            <div class="user-avatar">
                                @if(auth()->user()->avatar_url)
                                    <img src="{{ auth()->user()->avatar_url }}" alt="" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                @else
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                @endif
                            </div>
                            <div class="d-none d-md-block text-start">
                                <div style="font-weight: 600; color: var(--black);">{{ auth()->user()->name }}</div>
                                <div style="font-size: 0.85rem; color: var(--text-gray); text-transform: capitalize;">{{ auth()->user()->role }}</div>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 py-2" aria-labelledby="profileDropdown">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('profile.show') }}">
                                    <i class="bi bi-person-circle"></i> {{ __('dashboard.profile') }}
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" class="m-0">
                                    @csrf
                                    <button type="submit" class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger w-100 border-0 bg-transparent text-start">
                                        <i class="bi bi-box-arrow-right"></i> {{ __('dashboard.logout') }}
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endauth
            </div>
        </div>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            @if(session()->has('impersonator_id') && session()->has('impersonated_user_id'))
                <div class="alert alert-warning d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <strong>{{ __('dashboard.impersonation_mode') }}</strong>
                        {{ __('dashboard.impersonation_message') }}
                    </div>
                    <form method="POST" action="{{ route('impersonation.stop') }}" class="ms-3">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-dark">
                            <i class="bi bi-person-x"></i> {{ __('dashboard.stop_impersonating') }}
                        </button>
                    </form>
                </div>
            @endif
            @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif


            @yield('content')
        </div>
    </div>

    <!-- Full cart popup modal (for customers viewing meals in dashboard) -->
    @auth
    @if(auth()->user()->role === 'customer')
    <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cartModalLabel"><i class="bi bi-cart-check me-2"></i>{{ __('dashboard.full_cart') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if(empty($cartItems))
                        <p class="text-muted mb-0">{{ __('nav.cart_empty') }}</p>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>{{ __('common.meal') }}</th>
                                        <th>{{ __('common.chef') }}</th>
                                        <th class="text-end">{{ __('common.price') }}</th>
                                        <th class="text-end">{{ __('nav.qty') }}</th>
                                        <th class="text-end">{{ __('common.total') }}</th>
                                        <th class="text-end"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cartItems as $item)
                                        <tr>
                                            <td>{{ $item['meal']->name }}</td>
                                            <td class="text-muted small">{{ $item['meal']->chef?->name }}</td>
                                            <td class="text-end">TZS {{ number_format((float)$item['meal']->price, 2) }}</td>
                                            <td class="text-end">{{ $item['quantity'] }}</td>
                                            <td class="text-end fw-bold">TZS {{ number_format((float)$item['line_total'], 2) }}</td>
                                            <td class="text-end">
                                                <form method="POST" action="{{ route('cart.remove', $item['meal']) }}" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-danger" type="submit">{{ __('common.remove') }}</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end mt-3 pt-3 border-top">
                            <div class="text-end">
                                <div class="text-muted small">{{ __('nav.subtotal') }}</div>
                                <div class="h5 mb-0">TZS {{ number_format((float)$cartSubtotal, 2) }}</div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('common.continue_shopping') }}</button>
                    @if(!empty($cartItems))
                        <a href="{{ route('orders.checkout') }}" class="btn btn-success">{{ __('nav.place_order') }}</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
    @endauth

    @php $cartAddedQty = session()->pull('cart_added_qty'); @endphp
    @if($cartAddedQty)
    <div id="cartAddedToast" class="cart-toast" role="alert" aria-live="polite">
        <i class="bi bi-cart-check-fill"></i>
        <span>
            @if($cartAddedQty == 1)1 item added to cart
            @elseif($cartAddedQty == 2)2 items added to cart
            @elseif($cartAddedQty == 3)3 items added to cart
            @else{{ $cartAddedQty }} items added to cart
            @endif
        </span>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var toast = document.getElementById('cartAddedToast');
            if (toast) {
                requestAnimationFrame(function() { toast.classList.add('show'); });
                setTimeout(function() {
                    toast.classList.remove('show');
                    setTimeout(function() { toast.remove(); }, 350);
                }, 4000);
                toast.addEventListener('click', function() {
                    toast.classList.remove('show');
                    setTimeout(function() { toast.remove(); }, 350);
                });
            }
        });
    </script>
    @endif

    @include('partials.floating-actions', ['brand' => $brand])

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            var sidebar = document.getElementById('sidebar');
            var toggle = document.getElementById('sidebarToggle');
            var overlay = document.getElementById('sidebarOverlay');
            var closeBtn = document.getElementById('sidebarClose');
            var STORAGE_KEY = 'dashboard-sidebar-collapsed';

            function openSidebar() {
                sidebar.classList.add('active');
                document.body.classList.add('sidebar-open');
                var isSmall = window.innerWidth < 993;
                if (isSmall) {
                    overlay.classList.add('active');
                    overlay.setAttribute('aria-hidden', 'false');
                }
                if (toggle) {
                    toggle.setAttribute('aria-expanded', 'true');
                    toggle.setAttribute('aria-label', 'Close menu');
                }
            }
            function closeSidebar() {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                document.body.classList.remove('sidebar-open');
                overlay.setAttribute('aria-hidden', 'true');
                if (toggle) {
                    toggle.setAttribute('aria-expanded', 'false');
                    toggle.setAttribute('aria-label', 'Open menu');
                }
                document.body.style.overflow = '';
            }

            toggle?.addEventListener('click', function() {
                if (window.innerWidth >= 993) {
                    setCollapsed(!isCollapsed());
                } else {
                    if (sidebar.classList.contains('active')) closeSidebar();
                    else openSidebar();
                }
            });
            overlay?.addEventListener('click', closeSidebar);
            closeBtn?.addEventListener('click', closeSidebar);

            function setCollapsed(collapsed) {
                if (collapsed) {
                    sidebar.classList.add('collapsed');
                    document.body.classList.add('sidebar-collapsed');
                    try { localStorage.setItem(STORAGE_KEY, '1'); } catch (e) {}
                } else {
                    sidebar.classList.remove('collapsed');
                    document.body.classList.remove('sidebar-collapsed');
                    document.body.classList.remove('sidebar-hover-expanded');
                    try { localStorage.removeItem(STORAGE_KEY); } catch (e) {}
                }
            }
            function isCollapsed() {
                return sidebar.classList.contains('collapsed');
            }

            sidebar?.addEventListener('mouseenter', function() {
                if (isCollapsed()) document.body.classList.add('sidebar-hover-expanded');
            });
            sidebar?.addEventListener('mouseleave', function() {
                document.body.classList.remove('sidebar-hover-expanded');
            });

            if (window.innerWidth >= 993) {
                try {
                    if (localStorage.getItem(STORAGE_KEY) === '1') setCollapsed(true);
                    else document.body.classList.add('sidebar-open');
                } catch (e) {
                    document.body.classList.add('sidebar-open');
                }
            }
        })();

        (function() {
            var el = document.getElementById('profileDropdown');
            if (!el) return;
            var toggle = el.querySelector('[data-bs-toggle="dropdown"]');
            var menu = el.querySelector('.dropdown-menu');
            if (!toggle || !menu) return;
            var leaveTimer;
            function openMenu() {
                clearTimeout(leaveTimer);
                var d = bootstrap.Dropdown.getOrCreateInstance(toggle);
                d.show();
            }
            function closeMenu() {
                leaveTimer = setTimeout(function() {
                    var d = bootstrap.Dropdown.getInstance(toggle);
                    if (d) d.hide();
                }, 150);
            }
            el.addEventListener('mouseenter', openMenu);
            el.addEventListener('mouseleave', closeMenu);
        })();
    </script>
    @stack('scripts')
</body>
</html>
