<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php $brand = $siteName ?? config('app.name', 'One Stop'); @endphp
    <meta name="description" content="{{ $brand }} — Food order & delivery. Direct chef-to-customer: restaurant-quality meals from verified chefs to your doorstep. Order online, Dar es Salaam, Tanzania.">
    <title>{{ $brand }} — Food Order &amp; Delivery</title>
    <!-- Typography: Poppins (headlines) + Roboto (body) + Montserrat (accent) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --font-headline: 'Poppins', sans-serif, Arial, Helvetica;
            --font-body: 'Roboto', sans-serif, Arial, Helvetica;
            --font-accent: 'Montserrat', Arial, Helvetica, sans-serif;
            --primary-color: #ff6b35;
            --secondary-color: #2c3e50;
            --accent-color: #f39c12;
            /* Base for rem – scales with browser zoom */
            --base-font: 1rem;
            --space-unit: 0.25rem;
        }

        html {
            overflow-x: hidden;
            font-size: 100%;
            -webkit-text-size-adjust: 100%;
            transition: background-color 0.35s ease, color 0.35s ease;
        }
        body {
            font-family: var(--font-body);
            font-size: var(--base-font);
            font-weight: 400;
            line-height: 1.6;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
            -webkit-tap-highlight-color: rgba(0, 0, 0, 0.05);
            transition: background-color 0.35s ease, color 0.35s ease;
        }
        main.page-shell,
        .card,
        .card-body,
        .modal-content,
        .dropdown-menu,
        .input-group-text,
        .form-control {
            transition: background-color 0.35s ease, color 0.35s ease, border-color 0.35s ease;
        }
        #themeIcon {
            transition: transform 0.3s ease, opacity 0.3s ease;
        }
        #themeToggle:hover #themeIcon,
        #themeToggleMobile:hover #themeIconMobile {
            transform: rotate(15deg);
        }
        /* Dark theme overrides for custom elements */
        [data-bs-theme="dark"] .hero-unified-card {
            background: rgba(30, 41, 59, 0.6);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }
        [data-bs-theme="dark"] .hero-tagline,
        [data-bs-theme="dark"] .hero-stat-value,
        [data-bs-theme="dark"] .hero-stat-label {
            color: rgba(248, 250, 252, 0.9);
        }
        [data-bs-theme="dark"] .hero-badge-inline {
            background: rgba(51, 65, 85, 0.8);
            border-color: rgba(148, 163, 184, 0.2);
        }
        [data-bs-theme="dark"] .hero-badge-inline .hero-badge-top {
            color: #4ade80;
        }
        [data-bs-theme="dark"] .hero-badge-inline .hero-badge-sub {
            color: rgba(203, 213, 225, 0.8);
        }
        [data-bs-theme="dark"] .hero-title,
        [data-bs-theme="dark"] .hero-subtitle {
            color: var(--bs-body-color);
        }
        [data-bs-theme="dark"] .hero-title .text-success {
            color: #4ade80 !important;
        }
        [data-bs-theme="dark"] .hero-search-input-group .form-control,
        [data-bs-theme="dark"] .hero-search-input-group .hero-search-icon-btn {
            background-color: var(--bs-body-bg) !important;
            border-color: var(--bs-border-color);
            color: var(--bs-body-color);
        }
        [data-bs-theme="dark"] .navbar-search-input-group .form-control,
        [data-bs-theme="dark"] .navbar-search-input-group .navbar-search-icon-btn {
            background-color: var(--bs-body-bg) !important;
            border-color: var(--bs-border-color);
        }
        /* Meet Our Expert Chefs – dark theme */
        [data-bs-theme="dark"] .chefs-section .chef-card,
        [data-bs-theme="dark"] .chefs-section .chef-card-featured {
            background-color: var(--bs-body-bg);
            border-color: var(--bs-border-color);
            color: var(--bs-body-color);
        }
        [data-bs-theme="dark"] .chefs-section {
            background-color: rgba(15, 23, 42, 0.55);
            border: 1px solid rgba(148, 163, 184, 0.18);
        }
        [data-bs-theme="dark"] .chefs-section .chef-card .card-title {
            color: var(--bs-body-color);
        }
        [data-bs-theme="dark"] .chefs-section .chef-card .text-muted {
            color: rgba(248, 250, 252, 0.7) !important;
        }
        [data-bs-theme="dark"] .chefs-section h3 {
            color: var(--bs-body-color);
        }
        [data-bs-theme="dark"] .chefs-section .btn-view {
            border-color: rgba(74, 222, 128, 0.5);
            color: #4ade80;
        }
        [data-bs-theme="dark"] .chefs-section .btn-view:hover {
            background-color: rgba(74, 222, 128, 0.2);
            color: #4ade80;
        }

        /* Join Our Chef Community – dark theme */
        [data-bs-theme="dark"] .chef-community-card {
            background: rgba(15, 23, 42, 0.35);
        }
        [data-bs-theme="dark"] .chef-community-card-inner {
            background: rgba(30, 41, 59, 0.65);
            border-color: rgba(148, 163, 184, 0.2);
            box-shadow: 0 12px 34px rgba(0, 0, 0, 0.28);
        }
        [data-bs-theme="dark"] .footer-chef-cta-title {
            color: rgba(248, 250, 252, 0.95);
        }
        [data-bs-theme="dark"] .footer-chef-cta-tagline {
            color: #4ade80;
        }
        [data-bs-theme="dark"] .footer-chef-cta-desc,
        [data-bs-theme="dark"] .footer-chef-cta-list,
        [data-bs-theme="dark"] .footer-chef-cta-contact {
            color: rgba(226, 232, 240, 0.85) !important;
        }
        [data-bs-theme="dark"] .footer-chef-cta-contact a {
            color: rgba(248, 250, 252, 0.95) !important;
        }
        [data-bs-theme="dark"] .partner-community-pane-traveler .footer-traveler-cta-title {
            color: rgba(248, 250, 252, 0.95);
        }
        [data-bs-theme="dark"] .partner-community-pane-traveler .footer-traveler-cta-tagline {
            color: #4ade80;
        }
        [data-bs-theme="dark"] .partner-community-pane-traveler .footer-traveler-cta-desc,
        [data-bs-theme="dark"] .partner-community-pane-traveler .footer-traveler-cta-list,
        [data-bs-theme="dark"] .partner-community-pane-traveler .footer-traveler-cta-contact {
            color: rgba(226, 232, 240, 0.85) !important;
        }
        [data-bs-theme="dark"] .partner-community-pane-traveler .footer-traveler-cta-contact a {
            color: rgba(248, 250, 252, 0.95) !important;
        }
        [data-bs-theme="dark"] .partner-community-divider {
            border-color: rgba(148, 163, 184, 0.2);
        }
        @media (max-width: 991.98px) {
            a, button {
                -webkit-tap-highlight-color: rgba(0, 0, 0, 0.05);
            }
        }

        /* Links: no underline, hover effect */
        a {
            text-decoration: none;
            transition: color 0.2s ease, opacity 0.2s ease, transform 0.2s ease, font-size 0.2s ease;
        }
        a:hover {
            text-decoration: none;
        }
        a:not(.btn):not(.nav-link):hover {
            opacity: 0.9;
        }

        h1, h2, h3, h4, h5, h6,
        .headline-font {
            font-family: var(--font-headline);
            font-weight: 600;
            letter-spacing: -0.2px;
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
        .navbar-brand,
        .navbar-brand span {
            font-family: sans-serif;
            font-weight: 400;
            font-size: 1.5rem;
        }
        /* Top navigation bar – green links */
        .navbar.navbar-dark.bg-dark .nav-link {
            color: #22c55e;
            font-weight: 400;
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            transition: color 0.2s ease, background 0.2s ease, transform 0.2s ease, font-size 0.2s ease;
        }
        .navbar.navbar-dark.bg-dark .nav-link:hover,
        .navbar.navbar-dark.bg-dark .nav-link:focus {
            color: #fff;
            background: rgba(34, 197, 94, 0.25);
            text-decoration: none;
            transform: scale(1.08);
            font-size: 1.02em;
        }
        .navbar.navbar-dark.bg-dark .nav-link.active {
            color: #fff;
            background: transparent;
            font-weight: 400;
        }
        .navbar.navbar-dark.bg-dark .nav-link.active:hover {
            background: transparent;
            color: #fff;
        }
        .navbar-collapse {
            justify-content: center;
            align-items: center;
        }
        .navbar-nav {
            align-items: center;
        }
        .navbar-brand.navbar-brand-green,
        .navbar-brand.navbar-brand-green span {
            color: #22c55e !important;
            font-family: sans-serif;
        }
        .navbar-brand.navbar-brand-green:hover span {
            color: #4ade80 !important;
        }
        .btn-signin-white {
            background-color: #fff;
            color: #1e7e34;
            border: 1px solid rgba(255,255,255,0.5);
        }
        .btn-signin-white:hover {
            background-color: #f0f0f0;
            color: #166534;
            border-color: #fff;
        }
        .navbar-deliver-to-btn {
            color: rgba(255,255,255,0.9);
            background-color: transparent !important;
        }
        .navbar-deliver-to-btn:hover,
        .navbar-deliver-to-btn:focus,
        .navbar-deliver-to-btn.show {
            color: #fff;
            background-color: transparent !important;
        }
        .navbar-deliver-to-text .navbar-deliver-label {
            color: rgba(255,255,255,0.7);
            font-size: 0.7rem;
        }
        .navbar-deliver-to-text .navbar-location-text {
            font-size: 0.8125rem;
            font-weight: 500;
        }
        .navbar-brand {
            margin-inline-start: 1.5rem;
            padding-inline-start: 1rem;
            margin-inline-end: 0.25rem;
        }
        .navbar-brand .navbar-logo {
            height: 80px;
            width: auto;
            object-fit: contain;
            display: block;
        }
        .navbar > .container {
            gap: 0.5rem;
        }

        /* Mobile toolbar: keep key actions visible without opening the menu */
        .navbar-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            width: 100%;
        }
        .navbar-quick-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.25rem;
            margin-left: auto;
        }
        .navbar-quick-actions-desktop {
            margin-left: 0;
            padding-left: 0.25rem;
        }
        .navbar-quick-btn {
            min-width: 38px;
            min-height: 38px;
            padding: 0.35rem 0.45rem !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            gap: 0.35rem;
        }
        .navbar-signin-btn {
            min-width: auto;
            padding-inline: 0.55rem !important;
        }
        .navbar-signin-label {
            font-size: 0.8125rem;
            font-weight: 500;
            white-space: nowrap;
        }
        .navbar-quick-btn.active {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.35);
        }
        @media (max-width: 991.98px) {
            .navbar-toolbar {
                flex-wrap: wrap;
            }
            .navbar-brand {
                margin-inline-start: 0;
                padding-inline-start: 0;
                flex-shrink: 0;
            }
            .navbar-brand .navbar-logo {
                height: 48px !important;
            }
            .navbar-quick-home {
                flex-shrink: 0;
                margin-left: 0.25rem;
            }
            .navbar-quick-actions {
                margin-left: auto;
                margin-right: 0.35rem;
                flex-shrink: 0;
            }
            .navbar-toggler {
                margin-left: 0;
                padding: 0.35rem 0.5rem;
                flex-shrink: 0;
            }
            .navbar-collapse {
                flex-basis: 100%;
                width: 100%;
                margin-top: 0.5rem;
                order: 10;
            }
        }
        @media (max-width: 575.98px) {
            .navbar-quick-actions {
                gap: 0.25rem;
            }
            .navbar-quick-btn {
                min-width: 34px;
                min-height: 34px;
                padding: 0.25rem 0.35rem !important;
            }
            .navbar-signin-btn {
                padding-inline: 0.45rem !important;
            }
            .navbar-signin-label {
                font-size: 0.75rem;
            }
        }
        @media (min-width: 992px) {
            .navbar-toolbar {
                flex-wrap: nowrap;
            }
            .navbar-brand {
                order: 1;
                flex-shrink: 0;
            }
            .navbar-collapse {
                order: 2;
                flex: 1 1 auto;
                display: flex !important;
                flex-wrap: nowrap;
                align-items: center;
                justify-content: center;
                gap: 0;
            }
            .navbar-search-wrap {
                margin-right: 0 !important;
                margin-left: 0.5rem;
            }
            .navbar-quick-actions-desktop {
                flex-shrink: 0;
            }
            .navbar-quick-home {
                display: none !important;
            }
        }

        /* Navbar search: icon toggles to input */
        .navbar-search-wrap {
            display: flex;
            align-items: center;
            min-width: 40px;
        }
        .navbar-search-trigger {
            color: rgba(255,255,255,0.85);
            padding: 0.4rem 0.5rem;
        }
        .navbar-search-trigger:hover {
            color: #fff;
        }
        .navbar-search-form {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            max-width: 280px;
        }
        .navbar-search-input-group {
            border-radius: 0.375rem;
            overflow: hidden;
        }
        .navbar-search-input-group .form-control {
            border-radius: 0.375rem 0 0 0.375rem;
        }
        .navbar-search-input-group .navbar-search-icon-btn {
            border-radius: 0 0.375rem 0.375rem 0;
            padding: 0.25rem 0.5rem;
            cursor: pointer;
        }
        .navbar-search-input-group .navbar-search-icon-btn:hover {
            background-color: #f8f9fa !important;
        }
        .navbar-search-input {
            width: 220px;
            min-width: 160px;
        }
        .navbar-search-close {
            color: rgba(255,255,255,0.85);
            padding: 0.35rem 0.4rem;
        }
        .navbar-search-close:hover {
            color: #fff;
        }

        /* Floating actions: language switcher + WhatsApp (bottom-right stack) */
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
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.12), 0 1px 4px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(0, 0, 0, 0.06);
        }
        [data-bs-theme="dark"] .lang-fab-switcher {
            background: rgba(33, 37, 41, 0.96);
            border-color: rgba(255, 255, 255, 0.1);
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
            letter-spacing: 0.02em;
            text-decoration: none;
            color: #495057;
            background: transparent;
            transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }
        [data-bs-theme="dark"] .lang-fab-btn {
            color: rgba(255, 255, 255, 0.75);
        }
        .lang-fab-btn:hover {
            color: #22c55e;
            background: rgba(34, 197, 94, 0.12);
        }
        .lang-fab-btn.active {
            background: #22c55e;
            color: #fff;
        }
        .lang-fab-btn:active {
            transform: scale(0.96);
        }

        /* WhatsApp floating button – inside .floating-actions-stack */
        .whatsapp-fab {
            position: relative;
            bottom: auto;
            right: auto;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(145deg, #2ee66a 0%, #25d366 50%, #20bd5a 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(37, 211, 102, 0.4), 0 2px 6px rgba(0, 0, 0, 0.08);
            z-index: 1;
            text-decoration: none;
            border: none;
            outline: none;
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease, background 0.3s ease;
            overflow: visible;
        }
        .whatsapp-fab::before {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            border: 2px solid rgba(37, 211, 102, 0.4);
            opacity: 0;
            animation: whatsapp-fab-pulse 2.5s ease-in-out infinite;
            pointer-events: none;
        }
        @keyframes whatsapp-fab-pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.08); opacity: 0; }
        }
        .whatsapp-fab:hover {
            color: #fff;
            transform: scale(1.1);
            box-shadow: 0 8px 24px rgba(37, 211, 102, 0.5), 0 4px 12px rgba(0, 0, 0, 0.12);
            background: linear-gradient(145deg, #34ef78 0%, #2ee66a 50%, #25d366 100%);
        }
        .whatsapp-fab:hover::before {
            animation-duration: 1.5s;
        }
        .whatsapp-fab:active {
            transform: scale(0.96);
            transition-duration: 0.1s;
            box-shadow: 0 2px 10px rgba(37, 211, 102, 0.4);
        }
        .whatsapp-fab:focus-visible {
            outline: 2px solid rgba(37, 211, 102, 0.8);
            outline-offset: 4px;
            transition: outline-offset 0.2s ease, outline-color 0.2s ease;
        }
        .whatsapp-fab i {
            font-size: 1.75rem;
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            z-index: 1;
        }
        .whatsapp-fab:hover i {
            transform: scale(1.05);
        }
        .whatsapp-fab:active i {
            transform: scale(0.98);
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
            .whatsapp-fab i {
                font-size: 1.5rem;
            }
        }

        /* Hero background – fluid spacing and typography for resolution/zoom */
        .hero-section {
            background: #e6f8ec;
            color: #000000;
            padding: clamp(2rem, 5vw, 5rem) 0;
            margin-bottom: clamp(2rem, 4vw, 3.75rem);
        }
        .hero-title {
            font-size: clamp(1.85rem, 3.25vw + 0.6rem, 2.9rem);
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            transition: color 0.35s ease;
        }
        .hero-subtitle {
            font-size: clamp(1rem, 1.5vw + 0.5rem, 1.25rem);
            opacity: 0.9;
            margin-bottom: 2rem;
            transition: color 0.35s ease;
        }
        .search-box {
            max-width: min(600px, 90vw);
            margin: 0 auto;
        }
        .location-selector {
            background: white;
            border-radius: 50px;
            padding: 8px 20px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--secondary-color);
            font-weight: 500;
        }
        .chef-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            overflow: hidden;
        }
        .chef-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .chef-profile-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .chef-profile-image-small {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .chef-profile-image-medium {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .btn-gold {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            border: none;
            color: #000;
            font-weight: 600;
            padding: 0.5rem 1.25rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(255, 215, 0, 0.3);
        }
        .btn-gold:hover {
            background: linear-gradient(135deg, #ffed4e 0%, #ffd700 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 215, 0, 0.4);
            color: #000;
        }
        .btn-gold:active {
            transform: translateY(0);
        }
        .single-chef-card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            background: #ffffff;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .single-chef-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        }

        /* Mobile Responsive Styles */
        @media (max-width: 767.98px) {
            /* Hero Section Mobile */
            .hero-title {
                font-size: 2rem !important;
                line-height: 1.2;
            }
            .hero-subtitle {
                font-size: 1rem !important;
            }
            .hero-section {
                padding: 40px 0 !important;
            }

            /* Stats Cards Mobile */
            .stats-card {
                padding: 12px 8px !important;
            }
            .stats-number-mobile {
                font-size: 1.25rem !important;
            }
            .stats-label-mobile {
                font-size: 0.7rem !important;
            }
            .stats-desc-mobile {
                font-size: 0.65rem !important;
                display: none; /* Hide on very small screens */
            }

            /* Chef Cards Mobile */
            .chef-profile-image-small {
                width: 50px !important;
                height: 50px !important;
            }
            .chef-profile-image {
                width: 60px !important;
                height: 60px !important;
            }
            .chef-profile-image-medium {
                width: 60px !important;
                height: 60px !important;
            }
            .chef-name-mobile {
                font-size: 0.85rem !important;
                line-height: 1.2;
            }
            .chef-cuisine-mobile {
                font-size: 0.7rem !important;
            }
            .chef-orders-mobile {
                font-size: 0.65rem !important;
            }
            .chef-rating-badge {
                font-size: 0.7rem !important;
                padding: 0.25rem 0.5rem !important;
            }
            .chef-rating-value {
                font-size: 0.7rem !important;
            }
            .chef-reviews-mobile {
                font-size: 0.6rem !important;
            }
            .single-chef-card {
                padding: 0.75rem 0.5rem !important;
            }

            /* Buttons Mobile */
            .btn-gold, .btn-view-all {
                padding: 0.45rem 0.8rem !important;
                font-size: 0.9rem !important;
            }
            .btn-sm {
                padding: 0.32rem 0.6rem !important;
                font-size: 0.875rem !important;
            }
            .btn {
                font-size: 0.9375rem;
                padding: 0.4rem 0.65rem;
            }

            /* Container Padding Mobile */
            .container {
                padding-left: 16px !important;
                padding-right: 16px !important;
                max-width: 100%;
            }
            .page-shell {
                padding-left: 16px !important;
                padding-right: 16px !important;
            }

            /* Section Spacing Mobile */
            section {
                margin-bottom: 2rem !important;
            }

            /* Card Spacing Mobile */
            .card-body {
                padding: 1rem !important;
            }

            /* Search Box Mobile */
            .search-box .input-group-lg {
                flex-direction: column;
            }
            .search-box .input-group-lg .form-control {
                border-radius: 8px 8px 0 0 !important;
                margin-bottom: 0;
            }
            .search-box .input-group-lg .btn {
                border-radius: 0 0 8px 8px !important;
                width: 100%;
            }

            /* Meal Cards Mobile */
            .meal-card {
                margin-bottom: 1rem;
            }
            .meal-image {
                height: 180px !important;
            }

            /* Menu / Meals dropdown: open on hover, close when mouse leaves */
            @media (min-width: 992px) {
                .dropdown-hover .dropdown-menu {
                    margin-top: 0;
                    border-radius: 0 0 8px 8px;
                }
                .dropdown-hover.dropdown-open .dropdown-menu,
                .dropdown-hover.dropdown-open .dropdown-menu.show {
                    display: block !important;
                    visibility: visible !important;
                    opacity: 1 !important;
                }
            }

            /* Navigation Mobile */
            .navbar-nav {
                text-align: center;
                padding: 1rem 0;
            }
            .navbar-collapse {
                margin-top: 1rem;
            }
            .navbar-brand {
                font-size: 1.25rem !important;
            }
            .navbar-brand .navbar-logo {
                height: 52px !important;
            }
            .navbar .d-flex.align-items-center.gap-2 {
                flex-wrap: wrap;
                justify-content: center;
                gap: 0.5rem !important;
            }
            .navbar .btn-sm {
                padding: 0.4rem 0.6rem !important;
                font-size: 0.75rem !important;
            }
            .navbar .d-flex.align-items-center.gap-2 {
                min-height: 44px;
            }
            .navbar-search-wrap {
                order: 3;
                width: 100%;
                max-width: 100%;
                margin-top: 0.5rem;
                margin-left: 0 !important;
                margin-right: 0 !important;
            }
            .navbar-search-form {
                max-width: 100% !important;
                width: 100%;
            }
            .navbar-search-input {
                width: 100% !important;
                min-width: 0;
                flex: 1;
            }

            /* Footer mobile */
            .footer .row.mb-4 {
                text-align: center;
            }
            .footer .row.mb-4 > [class*="col-"] {
                margin-bottom: 1.5rem;
            }
            .footer h5, .footer h6 {
                font-size: 1rem;
            }
            .footer p, .footer .text-white-50 {
                font-size: 0.875rem;
            }
            .footer .d-flex.justify-content-between.align-items-center {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            .footer .d-flex.gap-3 {
                flex-wrap: wrap;
                justify-content: center;
            }

            /* Typography Mobile */
            h1, h2, h3 {
                font-size: 1.5rem !important;
            }
            h4, h5, h6 {
                font-size: 1.1rem !important;
            }

            /* Popular Delivery Areas Mobile */
            .card.text-center {
                padding: 1rem 0.5rem !important;
            }

            /* Professional Packaging Section Mobile */
            .packaging-image-container {
                padding: 10px !important;
                margin-top: 1.5rem;
            }

            /* Touch-friendly targets */
            .btn, a.btn {
                min-height: 44px;
                min-width: 44px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
            img, .img-fluid {
                max-width: 100%;
                height: auto;
            }
            .modal-dialog {
                margin: 0.5rem;
                max-width: calc(100% - 1rem);
            }
            .modal-fullscreen-sm-down .modal-dialog {
                max-width: 100%;
            }

            /* Meal Cards Mobile */
            .meal-card .card-body {
                padding: 0.75rem !important;
            }
        }

        @media (max-width: 575.98px) {
            /* Extra Small Mobile */
            .stats-desc-mobile {
                display: block !important;
                font-size: 0.6rem !important;
            }
            .hero-title {
                font-size: 1.75rem !important;
            }
            .single-chef-card {
                padding: 0.5rem !important;
            }
            .chef-profile-image-small {
                width: 45px !important;
                height: 45px !important;
            }
            .container {
                padding-left: 12px !important;
                padding-right: 12px !important;
            }
            .page-shell {
                padding-left: 12px !important;
                padding-right: 12px !important;
            }
            .footer .container {
                padding-left: 16px !important;
                padding-right: 16px !important;
            }
            .row.g-3, .row.g-4 {
                margin-left: -5px !important;
                margin-right: -5px !important;
            }
            .row.g-3 > *, .row.g-4 > * {
                padding-left: 5px !important;
                padding-right: 5px !important;
            }
        }

        /* Tablet adjustments */
        @media (min-width: 768px) and (max-width: 991.98px) {
            .single-chef-card {
                padding: 1rem !important;
            }
            .chef-profile-image-small {
                width: 55px !important;
                height: 55px !important;
            }
        }

        /* View buttons: meals, chefs, heritage – #e66220 */
        .btn-view {
            background: #e66220;
            border: none;
            color: #fff;
            font-weight: 600;
        }
        .btn-view:hover {
            background: #d45618;
            color: #fff;
        }
        .btn-view-outline {
            border-color: #e66220;
            color: #e66220;
        }
        .btn-view-outline:hover {
            background: #e66220;
            border-color: #e66220;
            color: #fff;
        }

        /* Pagination sizing (Previous / Next / page numbers) */
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
            padding-inline: 8px;
            font-size: 0.78rem;
        }
        /* View-all CTA (brand orange) */
        .btn-view-all {
            background: #e66220;
            border: none;
            color: #ffffff;
            font-weight: 600;
            padding: 0.65rem 1.4rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(230, 98, 32, 0.25);
        }
        .btn-view-all:hover {
            background: #d45618;
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(230, 98, 32, 0.32);
        }
        .btn-view-all:active {
            transform: translateY(0);
        }

        /* Cart modal – Sign In at end (Material Design) */
        .cart-signin-material {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            font-weight: 600;
            font-size: 0.9375rem;
            text-transform: none;
            letter-spacing: 0.02em;
            border: none;
            border-radius: 8px;
            background: #22c55e;
            color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.12), 0 2px 4px rgba(0,0,0,0.08);
            transition: box-shadow 0.28s cubic-bezier(0.4, 0, 0.2, 1), background 0.28s ease, transform 0.2s ease;
        }
        .cart-signin-material:hover {
            background: #16a34a;
            color: #fff;
            box-shadow: 0 4px 8px rgba(0,0,0,0.14), 0 3px 6px rgba(0,0,0,0.1);
            transform: translateY(-1px);
        }
        .cart-signin-material:active {
            box-shadow: 0 2px 4px rgba(0,0,0,0.12);
            transform: translateY(0);
        }
        .cart-signin-material i {
            font-size: 1.1rem;
        }
        .meal-card {
            border: none;
            overflow: hidden;
            transition: transform 0.3s;
        }
        .meal-card:hover {
            transform: translateY(-3px);
        }
        .meal-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        .badge-heritage {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        .badge-popular {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
        .meal-product-card {
            transition: all 0.3s ease;
            border: 1px solid #e9ecef !important;
        }
        .meal-product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
        }
        .meal-product-card .card-img-top {
            transition: transform 0.3s ease;
        }
        .meal-product-card:hover .card-img-top {
            transform: scale(1.05);
        }
        .footer {
            background: var(--secondary-color);
            color: white;
            padding: 60px 0 20px;
            margin-top: 2rem;
        }
        .footer > .container {
            padding-top: 2rem;
        }
        .footer a {
            text-decoration: none;
            transition: color 0.2s ease, transform 0.2s ease, font-size 0.2s ease;
        }
        .footer a:hover {
            color: #fff !important;
            text-decoration: none;
            transform: scale(1.05);
            font-size: 1.05em;
        }
        .footer h5 {
            font-family: sans-serif;
        }
        .footer-logo {
            height: 64px;
            width: auto;
            object-fit: contain;
            display: block;
        }
        /* Join Our Chef Community - separate card above footer */
        .chef-community-card {
            padding: 2rem 0;
            margin-top: 80px;
            background: #e8f5e9;
        }
        .chef-community-card .container {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        .chef-community-card-inner {
            background: #fff;
            border-radius: 20px;
            padding: 3rem 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08), 0 2px 8px rgba(46, 125, 50, 0.1);
            border: 1px solid rgba(46, 125, 50, 0.15);
        }
        .partner-community-pane {
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 100%;
        }
        .partner-community-footer {
            margin-top: auto;
            padding-top: 1rem;
        }
        @media (min-width: 992px) {
            .partner-community-pane-chef {
                border-right: 1px solid rgba(46, 125, 50, 0.15);
                padding-right: 2rem;
            }
            .partner-community-pane-traveler {
                padding-left: 2rem;
            }
        }
        @media (max-width: 991.98px) {
            .partner-community-divider {
                margin: 0.5rem 0 1.5rem;
                border-color: rgba(46, 125, 50, 0.15);
            }
        }
        @media (max-width: 575.98px) {
            .chef-community-card-inner {
                padding: 2rem 1.25rem;
            }
        }
        .footer-chef-cta-title {
            font-family: var(--font-headline);
            font-size: 1.625rem;
            font-weight: 600;
            color: #1b5e20;
            margin-bottom: 0.25rem;
            letter-spacing: -0.02em;
        }
        .footer-chef-cta-tagline {
            font-size: 1.0625rem;
            color: #2e7d32;
            font-weight: 600;
            letter-spacing: 0.01em;
        }
        .footer-chef-cta-desc {
            font-size: 0.9375rem;
            line-height: 1.65;
            color: #33691e;
        }
        .footer-chef-cta-list {
            font-size: 0.875rem;
            color: #2e7d32;
            line-height: 1.75;
        }
        .footer-chef-cta-list li {
            margin-bottom: 0.5rem;
        }
        .footer-chef-cta-list .bi-check-circle-fill {
            color: #2e7d32 !important;
        }
        .footer-chef-cta-actions .btn-success,
        .footer-traveler-cta-actions .btn-success {
            background-color: #2e7d32;
            border-color: #2e7d32;
        }
        .footer-chef-cta-actions .btn-success:hover,
        .footer-traveler-cta-actions .btn-success:hover {
            background-color: #1b5e20;
            border-color: #1b5e20;
        }
        .footer-chef-cta-actions .btn-outline-light {
            color: #2e7d32;
            border-color: #2e7d32;
        }
        .footer-chef-cta-actions .btn-outline-light:hover {
            background-color: rgba(46, 125, 50, 0.12);
            color: #1b5e20;
            border-color: #2e7d32;
        }
        .footer-chef-cta-contact {
            color: #33691e !important;
        }
        .footer-chef-cta-contact a {
            color: #1b5e20 !important;
            font-weight: 500;
        }
        .footer-chef-cta-contact a:hover {
            text-decoration: underline !important;
        }
        .footer-traveler-cta-title {
            font-family: var(--font-headline);
            font-size: 1.625rem;
            font-weight: 600;
            color: #1b5e20;
            margin-bottom: 0.25rem;
            letter-spacing: -0.02em;
        }
        .footer-traveler-cta-tagline {
            font-size: 1.0625rem;
            color: #2e7d32;
            font-weight: 600;
            letter-spacing: 0.01em;
        }
        .footer-traveler-cta-desc {
            font-size: 0.9375rem;
            line-height: 1.65;
            color: #33691e;
        }
        .footer-traveler-cta-list {
            font-size: 0.875rem;
            color: #2e7d32;
            line-height: 1.75;
        }
        .footer-traveler-cta-list li {
            margin-bottom: 0.5rem;
        }
        .footer-traveler-cta-list .bi-check-circle-fill {
            color: #2e7d32 !important;
        }
        .footer-traveler-cta-contact {
            color: #33691e !important;
        }
        .footer-traveler-cta-contact a {
            color: #1b5e20 !important;
            font-weight: 500;
        }
        .footer-traveler-cta-contact a:hover {
            text-decoration: underline !important;
        }
        .stats-card {
            text-align: center;
            padding: 16px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            min-height: 100px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .stats-number {
            font-weight: 700;
        }
        .packaging-image-container {
            position: relative;
            padding: 20px;
        }
        .packaging-image {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .packaging-image:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .stats-number {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        /* Light yellow background for Meet Our Expert Chefs section */
        .chefs-section {
            background-color: #fff9e6;
            border-radius: 16px;
            padding: 32px 24px;
        }
        .page-shell {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        @media (min-width: 768px) {
            .page-shell {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
        }
        
        /* Cart dropdown styles */
        .cart-dropdown {
            min-width: 320px;
            max-width: 400px;
            padding: 0;
        }
        .cart-dropdown-header {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
            font-size: 0.9rem;
            background: #f8f9fa;
        }
        .cart-dropdown-body {
            max-height: 400px;
            overflow-y: auto;
            padding: 0;
        }
        .cart-dropdown-item {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
            transition: background-color 0.2s ease;
        }
        .cart-dropdown-item:hover {
            background-color: #f8f9fa;
        }
        .cart-dropdown-item:last-child {
            border-bottom: none;
        }
        .cart-item-info {
            flex: 1;
            min-width: 0;
        }
        .cart-item-name {
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .cart-item-chef {
            font-size: 0.75rem;
            color: #6c757d;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .cart-item-details {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.25rem;
            flex-shrink: 0;
        }
        .cart-item-qty {
            font-size: 0.75rem;
            color: #6c757d;
        }
        .cart-item-total {
            font-size: 0.85rem;
            font-weight: 600;
            color: #22c55e;
        }
        .cart-item-remove {
            padding: 0.25rem 0.5rem;
            font-size: 0.7rem;
            line-height: 1;
        }
        .cart-dropdown-footer {
            padding: 0.75rem 1rem;
            border-top: 2px solid #dee2e6;
            background: #f8f9fa;
        }
        .cart-subtotal {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        .cart-subtotal-label {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .cart-subtotal-amount {
            font-size: 1rem;
            font-weight: 700;
            color: #22c55e;
        }
        .cart-dropdown-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .cart-dropdown-actions .btn {
            flex: 1;
            min-width: 0;
            font-size: 0.8rem;
            padding: 0.4rem 0.75rem;
        }
        .cart-dropdown-empty {
            padding: 2rem 1rem;
            text-align: center;
            color: #6c757d;
            font-size: 0.85rem;
        }
        .cart-dropdown-toggle::after {
            display: none;
        }
        .cart-dropdown--multi-chef {
            min-width: min(94vw, 620px);
            max-width: 720px;
        }
        .cart-dropdown-body--multi-chef {
            max-height: none;
            overflow: visible;
        }
        .cart-chef-column {
            background: #fafafa;
        }
        .cart-chef-column-name {
            line-height: 1.2;
        }
        @media (max-width: 575.98px) {
            .cart-dropdown {
                min-width: 280px;
                max-width: calc(100vw - 2rem);
            }
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/mobile-responsive.css') }}">
</head>
<body>
@php
    $cart = session('cart', []);
    $cartCount = is_array($cart) ? array_sum($cart) : 0;
@endphp
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container navbar-toolbar">
        <a class="navbar-brand navbar-brand-green d-flex align-items-center" href="{{ route('home') }}" aria-label="{{ $brand }} - {{ __('nav.home') }}">
            @if(file_exists(public_path('images/logo 01.webp')))
                <img src="{{ asset('images/logo 01.webp') }}" alt="{{ $brand }}" class="navbar-logo">
            @elseif(file_exists(public_path('images/logo 02.avif')))
                <img src="{{ asset('images/logo 02.avif') }}" alt="{{ $brand }}" class="navbar-logo">
            @elseif(file_exists(public_path('images/one stop food logo 01.jpeg')))
                <img src="{{ asset('images/one stop food logo 01.jpeg') }}" alt="{{ $brand }}" class="navbar-logo">
            @endif
        </a>

        <a class="btn btn-sm btn-outline-light navbar-quick-btn navbar-quick-home d-lg-none {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}" title="{{ __('nav.home') }}" aria-label="{{ __('nav.home') }}">
            <i class="bi bi-house-door-fill"></i>
        </a>

        <div class="navbar-quick-actions d-lg-none">
            <button class="btn btn-sm btn-outline-secondary navbar-quick-btn" id="themeToggleMobile" type="button" title="{{ __('common.toggle_theme') }}" aria-label="{{ __('common.toggle_theme') }}">
                <i class="bi bi-moon-stars" id="themeIconMobile"></i>
            </button>
            <div class="dropdown">
                <button type="button" class="btn btn-sm btn-success text-white position-relative dropdown-toggle cart-dropdown-toggle navbar-quick-btn" title="{{ __('nav.cart') }}" data-bs-toggle="dropdown" aria-expanded="false" id="cartIconBtnMobile" aria-label="{{ __('nav.cart') }}">
                    <i class="bi bi-cart-fill text-white"></i>
                    @if($cartCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 0.2em 0.45em;">
                            {{ $cartCount }}
                        </span>
                    @endif
                </button>
                <ul class="dropdown-menu dropdown-menu-end cart-dropdown @if(!empty($cartItems) && collect($cartItems)->pluck('meal.chef_id')->unique()->filter()->count() > 1) cart-dropdown--multi-chef @endif" aria-labelledby="cartIconBtnMobile">
                    <li class="cart-dropdown-header">
                        <i class="bi bi-cart"></i> {{ __('nav.cart') }} ({{ $cartCount }})
                    </li>
                    @if(empty($cartItems))
                        <li class="cart-dropdown-empty">
                            <i class="bi bi-cart-x" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                            {{ __('nav.cart_empty') }}
                        </li>
                    @else
                        <li>
                            @include('cart.partials.dropdown-body')
                        </li>
                        <li class="cart-dropdown-footer">
                            <div class="cart-subtotal">
                                <span class="cart-subtotal-label">{{ __('nav.subtotal') }}</span>
                                <span class="cart-subtotal-amount">{{ money($cartSubtotal) }}</span>
                            </div>
                            <div class="cart-dropdown-actions">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#cartModal">
                                    <i class="bi bi-eye"></i> {{ __('nav.show_full_cart') }}
                                </button>
                                @auth
                                    <a href="{{ route('orders.checkout') }}" class="btn btn-sm btn-success">{{ __('nav.checkout') }}</a>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-sm btn-success">{{ __('nav.place_order') }}</a>
                                @endauth
                            </div>
                        </li>
                    @endif
                </ul>
            </div>
            @guest
                <a class="btn btn-sm btn-signin-white navbar-quick-btn navbar-signin-btn" href="{{ route('login') }}" title="{{ __('nav.sign_in') }}">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span class="navbar-signin-label">{{ __('nav.sign_in') }}</span>
                </a>
            @else
                <a class="btn btn-sm btn-outline-success navbar-quick-btn navbar-signin-btn" href="{{ route('dashboard') }}" title="{{ __('nav.dashboard') }}">
                    <i class="bi bi-speedometer2"></i>
                    <span class="navbar-signin-label">{{ __('nav.dashboard') }}</span>
                </a>
            @endguest
        </div>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="{{ __('common.toggle_navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item d-none d-lg-block"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">{{ __('nav.home') }}</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('chefs.*') ? 'active' : '' }}" href="{{ route('chefs.index') }}">{{ __('nav.our_chefs') }}</a></li>
                <li class="nav-item dropdown dropdown-hover" id="menuMealsDropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('meals.index') ? 'active' : '' }}" href="{{ route('meals.index') }}" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true" title="{{ __('nav.menu') }}">{{ __('nav.menu') }}</a>
                    <ul class="dropdown-menu" aria-labelledby="menuMealsDropdown">
                        <li><a class="dropdown-item" href="{{ route('meals.index') }}">{{ __('nav.all_meals') }}</a></li>
                        <li><hr class="dropdown-divider"></li>
                        @foreach(\App\Models\Meal::getStandardCategories() as $catKey => $cat)
                            <li><a class="dropdown-item" href="{{ route('meals.index', ['category' => $catKey]) }}">{{ $catKey }}</a></li>
                        @endforeach
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('register') ? 'active' : '' }}" href="{{ route('register') }}">{{ __('nav.become_chef') }}</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('stories.*') ? 'active' : '' }}" href="{{ route('stories.index') }}">{{ __('nav.stories') }}</a></li>
            </ul>
            <div class="navbar-search-wrap">
                <form method="GET" action="{{ route('meals.index') }}" class="navbar-search-form d-flex" id="navbarSearchForm">
                    <div class="input-group input-group-sm navbar-search-input-group">
                        <input type="text" class="form-control form-control-sm navbar-search-input border-end-0" name="search" placeholder="{{ __('nav.search_placeholder') }}" value="{{ request('search') }}" aria-label="{{ __('nav.search_placeholder') }}">
                        <button type="submit" class="input-group-text navbar-search-icon-btn border-start-0 bg-white" title="{{ __('nav.search_placeholder') }}" aria-label="{{ __('nav.search_placeholder') }}">
                            <i class="bi bi-search text-muted"></i>
                        </button>
                    </div>
                </form>
            </div>
            <div class="navbar-quick-actions navbar-quick-actions-desktop d-none d-lg-flex">
                <div class="dropdown navbar-deliver-to d-flex align-items-center">
                    <button class="btn btn-sm btn-outline-light border-0 d-flex align-items-center gap-1 dropdown-toggle navbar-deliver-to-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="{{ __('nav.deliver_to') }}" id="deliverToDropdown">
                        <i class="bi bi-geo-alt"></i>
                        <span class="text-start navbar-deliver-to-text">
                            <span class="d-block navbar-deliver-label small lh-1">{{ __('nav.deliver_to') }}</span>
                            <span class="navbar-location-text">{{ __('nav.your_location') }}</span>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="deliverToDropdown">
                        <li class="px-3 py-2 small text-muted">{{ __('nav.your_location') }}</li>
                        @auth
                            <li><a class="dropdown-item" href="{{ route('locations.index') }}"><i class="bi bi-pencil me-2"></i>{{ __('nav.set_delivery_address') }}</a></li>
                        @else
                            <li><a class="dropdown-item" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right me-2"></i>{{ __('nav.sign_in_to_set_location') }}</a></li>
                        @endauth
                    </ul>
                </div>
                <button class="btn btn-sm btn-outline-secondary navbar-quick-btn" id="themeToggle" type="button" title="{{ __('common.toggle_theme') }}" aria-label="{{ __('common.toggle_theme') }}">
                    <i class="bi bi-moon-stars" id="themeIcon"></i>
                </button>
                <div class="dropdown">
                    <button type="button" class="btn btn-sm btn-success text-white position-relative dropdown-toggle cart-dropdown-toggle navbar-quick-btn" title="{{ __('nav.cart') }}" data-bs-toggle="dropdown" aria-expanded="false" id="cartIconBtn" aria-label="{{ __('nav.cart') }}">
                        <i class="bi bi-cart-fill text-white"></i>
                        @if($cartCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; padding: 0.2em 0.45em;">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end cart-dropdown @if(!empty($cartItems) && collect($cartItems)->pluck('meal.chef_id')->unique()->filter()->count() > 1) cart-dropdown--multi-chef @endif" aria-labelledby="cartIconBtn">
                        <li class="cart-dropdown-header">
                            <i class="bi bi-cart"></i> {{ __('nav.cart') }} ({{ $cartCount }})
                        </li>
                        @if(empty($cartItems))
                            <li class="cart-dropdown-empty">
                                <i class="bi bi-cart-x" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                                {{ __('nav.cart_empty') }}
                            </li>
                        @else
                            <li>
                                @include('cart.partials.dropdown-body')
                            </li>
                            <li class="cart-dropdown-footer">
                                <div class="cart-subtotal">
                                    <span class="cart-subtotal-label">{{ __('nav.subtotal') }}</span>
                                    <span class="cart-subtotal-amount">{{ money($cartSubtotal) }}</span>
                                </div>
                                <div class="cart-dropdown-actions">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#cartModal" id="viewFullCartBtn">
                                        <i class="bi bi-eye"></i> {{ __('nav.show_full_cart') }}
                                    </button>
                                    @auth
                                        <a href="{{ route('orders.checkout') }}" class="btn btn-sm btn-success">{{ __('nav.checkout') }}</a>
                                    @else
                                        <a href="{{ route('login') }}" class="btn btn-sm btn-success">{{ __('nav.place_order') }}</a>
                                    @endauth
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
                @guest
                    <a class="btn btn-sm btn-signin-white navbar-quick-btn navbar-signin-btn" href="{{ route('login') }}" title="{{ __('nav.sign_in') }}">
                        <i class="bi bi-box-arrow-in-right"></i>
                        <span class="navbar-signin-label">{{ __('nav.sign_in') }}</span>
                    </a>
                @else
                    <a class="btn btn-sm btn-outline-success navbar-quick-btn navbar-signin-btn" href="{{ route('dashboard') }}" title="{{ __('nav.dashboard') }}">
                        <i class="bi bi-speedometer2"></i>
                        <span class="navbar-signin-label">{{ __('nav.dashboard') }}</span>
                    </a>
                @endguest
            </div>
            <div class="d-lg-none w-100 mt-2 pt-2 border-top border-secondary border-opacity-25">
                <div class="dropdown navbar-deliver-to">
                    <button class="btn btn-sm btn-outline-light border-0 d-flex align-items-center gap-2 dropdown-toggle navbar-deliver-to-btn w-100 justify-content-start" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="{{ __('nav.deliver_to') }}" id="deliverToDropdownMobile">
                        <i class="bi bi-geo-alt"></i>
                        <span class="text-start navbar-deliver-to-text">
                            <span class="d-block navbar-deliver-label small lh-1">{{ __('nav.deliver_to') }}</span>
                            <span class="navbar-location-text">{{ __('nav.your_location') }}</span>
                        </span>
                    </button>
                    <ul class="dropdown-menu w-100" aria-labelledby="deliverToDropdownMobile">
                        <li class="px-3 py-2 small text-muted">{{ __('nav.your_location') }}</li>
                        @auth
                            <li><a class="dropdown-item" href="{{ route('locations.index') }}"><i class="bi bi-pencil me-2"></i>{{ __('nav.set_delivery_address') }}</a></li>
                        @else
                            <li><a class="dropdown-item" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right me-2"></i>{{ __('nav.sign_in_to_set_location') }}</a></li>
                        @endauth
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

@if ($errors->getBag('default')->any() && !request()->routeIs('login', 'login.2fa.show', 'password.request', 'password.reset'))
    <div class="alert alert-danger alert-dismissible fade show m-0">
        <ul class="mb-0">
            @foreach ($errors->getBag('default')->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<main class="page-shell">
@yield('content')
</main>

<!-- Full cart popup modal (no separate cart page link) -->
<div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title headline-font" id="cartModalLabel"><i class="bi bi-cart-check me-2"></i>{{ __('common.full_cart') }}</h5>
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
                                        <td class="text-end">{{ money($item['meal']->price) }}</td>
                                        <td class="text-end">{{ $item['quantity'] }}</td>
                                        <td class="text-end fw-bold">{{ money($item['line_total']) }}</td>
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
                            <div class="h5 mb-0">{{ money($cartSubtotal) }}</div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="modal-footer flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('common.continue_shopping') }}</button>
                @if(!empty($cartItems))
                    @auth
                        <a href="{{ route('orders.checkout') }}" class="btn btn-success">{{ __('nav.place_order') }}</a>
                    @endauth
                @endif
                @guest
                    <a href="{{ route('login') }}" class="btn cart-signin-material ms-auto" title="{{ !empty($cartItems) ? __('nav.place_order') : __('nav.sign_in') }}">
                        <i class="bi bi-box-arrow-in-right"></i>
                        {{ !empty($cartItems) ? __('nav.place_order') : __('nav.sign_in') }}
                    </a>
                @endguest
            </div>
        </div>
    </div>
</div>

@unless(request()->routeIs('login'))
@php
    $communityPhone = $supportPhone ?? '+255 651 490 677';
    $communityPhoneTel = preg_replace('/\D+/', '', $communityPhone);
    $communityEmail = $supportEmail ?? config('contacts.support_email', 'support@onestopfood.co.tz');
@endphp
<!-- Join Our Chef & Traveler Community - single card above footer -->
<section class="chef-community-card">
    <div class="container">
        <div class="chef-community-card-inner">
            <div class="row g-4 g-lg-0 align-items-stretch">
                <div class="col-lg-6 partner-community-pane partner-community-pane-chef">
                    <h2 class="footer-chef-cta-title mb-2">{{ __('community.chef_title') }}</h2>
                    <p class="footer-chef-cta-tagline mb-2">{{ __('community.chef_tagline') }}</p>
                    <p class="footer-chef-cta-desc mb-0">{{ __('community.chef_desc', ['brand' => $brand]) }}</p>
                    <ul class="footer-chef-cta-list list-unstyled mt-3 mb-0">
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i><strong>{{ __('community.chef_skill_title') }}</strong> — {{ __('community.chef_skill_desc') }}</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i><strong>{{ __('community.chef_revenue_title') }}</strong> — {{ __('community.chef_revenue_desc') }}</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i><strong>{{ __('community.chef_community_title') }}</strong> — {{ __('community.chef_community_desc') }}</li>
                    </ul>
                    <div class="footer-chef-cta-actions mt-4">
                        <a href="{{ route('register', ['role' => 'chef']) }}" class="btn btn-success me-2 mb-2">{{ __('community.become_chef_btn') }}</a>
                        <a href="{{ route('chefs.index') }}" class="btn btn-outline-light mb-2">{{ __('community.meet_chefs_btn') }}</a>
                    </div>
                    <p class="footer-chef-cta-contact small mt-3 mb-0">{!! __('community.chef_contact', ['email' => '<a href="mailto:'.$communityEmail.'" class="text-decoration-none">'.$communityEmail.'</a>', 'phone' => '<a href="tel:'.$communityPhoneTel.'" class="text-decoration-none">'.$communityPhone.'</a>']) !!}</p>
                </div>
                <div class="col-lg-6 partner-community-pane partner-community-pane-traveler">
                    <hr class="partner-community-divider d-lg-none">
                    <h2 class="footer-traveler-cta-title mb-2">{{ __('community.traveler_title') }}</h2>
                    <p class="footer-traveler-cta-tagline mb-2">{{ __('community.traveler_tagline') }}</p>
                    <p class="footer-traveler-cta-desc mb-0">{{ __('community.traveler_desc', ['brand' => $brand]) }}</p>
                    <ul class="footer-traveler-cta-list list-unstyled mt-3 mb-0">
                        <li><i class="bi bi-check-circle-fill me-2"></i><strong>{{ __('community.traveler_earnings_title') }}</strong> — {{ __('community.traveler_earnings_desc') }}</li>
                        <li><i class="bi bi-check-circle-fill me-2"></i><strong>{{ __('community.traveler_assign_title') }}</strong> — {{ __('community.traveler_assign_desc') }}</li>
                        <li><i class="bi bi-check-circle-fill me-2"></i><strong>{{ __('community.traveler_trust_title') }}</strong> — {{ __('community.traveler_trust_desc') }}</li>
                    </ul>
                    <div class="footer-traveler-cta-actions mt-4">
                        <a href="{{ route('register', ['role' => 'traveler']) }}" class="btn btn-success me-2 mb-2">{{ __('community.become_traveler_btn') }}</a>
                    </div>
                    <p class="footer-traveler-cta-contact small mt-3 mb-0">{!! __('community.traveler_contact', ['email' => '<a href="mailto:'.$communityEmail.'" class="text-decoration-none">'.$communityEmail.'</a>', 'phone' => '<a href="tel:'.$communityPhoneTel.'" class="text-decoration-none">'.$communityPhone.'</a>']) !!}</p>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-4 mb-4">
                <div class="mb-2">
                    @if(file_exists(public_path('images/logo 01.webp')))
                        <img src="{{ asset('images/logo 01.webp') }}" alt="{{ $brand }}" class="footer-logo">
                    @elseif(file_exists(public_path('images/logo 02.avif')))
                        <img src="{{ asset('images/logo 02.avif') }}" alt="{{ $brand }}" class="footer-logo">
                    @elseif(file_exists(public_path('images/one stop food logo 01.jpeg')))
                        <img src="{{ asset('images/one stop food logo 01.jpeg') }}" alt="{{ $brand }}" class="footer-logo">
                    @endif
                </div>
                <p class="small text-white-50 mb-1">{{ __('common.food_order_delivery') }}</p>
                <p class="mb-0">{{ __('common.footer_desc') }}</p>
            </div>
            <div class="col-md-2 mb-4">
                <h6 class="mb-3">{{ __('common.quick_links') }}</h6>
                <ul class="list-unstyled">
                    <li><a href="{{ route('home') }}" class="text-white-50 text-decoration-none">{{ __('nav.home') }}</a></li>
                    <li><a href="{{ route('chefs.index') }}" class="text-white-50 text-decoration-none">{{ __('nav.our_chefs') }}</a></li>
                    <li><a href="{{ route('meals.index') }}" class="text-white-50 text-decoration-none">{{ __('nav.menu') }}</a></li>
                    <li><a href="{{ route('stories.index') }}" class="text-white-50 text-decoration-none">{{ __('nav.stories') }}</a></li>
                </ul>
            </div>
            <div class="col-md-2 mb-4">
                <h6 class="mb-3">{{ __('common.about') }}</h6>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-white-50 text-decoration-none">{{ __('common.about_us') }}</a></li>
                    <li><a href="https://wa.me/{{ $communityPhoneTel }}" class="text-white-50 text-decoration-none" target="_blank" rel="noopener">{{ __('common.contact_whatsapp') }}</a></li>
                    <li><a href="{{ route('register', ['role' => 'chef']) }}" class="text-white-50 text-decoration-none">{{ __('nav.become_chef') }}</a></li>
                    <li><a href="{{ route('register', ['role' => 'traveler']) }}" class="text-white-50 text-decoration-none">{{ __('common.become_traveler') }}</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h6 class="mb-3">{{ __('common.contact_help') }}</h6>
                <p class="text-white-50 mb-1"><i class="bi bi-telephone"></i> {{ $supportPhone ?? '+255 651 490 677' }}</p>
                <p class="text-white-50 mb-1">
                    <i class="bi bi-envelope"></i>
                    <a href="mailto:{{ $supportEmail ?? config('contacts.support_email') }}" class="text-white-50 text-decoration-none">{{ $supportEmail ?? config('contacts.support_email') }}</a>
                    <span class="d-block small opacity-75">{{ __('common.assistance_help') }}</span>
                </p>
                <p class="text-white-50 mb-2 small">
                    <i class="bi bi-envelope-check"></i>
                    {{ $noreplyEmail ?? config('contacts.noreply_email') }}
                    <span class="d-block opacity-75">{{ __('common.noreply_note') }}</span>
                </p>
                <p class="text-white-50 mb-2"><i class="bi bi-geo-alt"></i> {{ __('common.dar_es_salaam') }}</p>
            </div>
        </div>
        <hr class="bg-white-50">
        <div class="d-flex justify-content-between align-items-center text-white-50">
            <p class="mb-0">&copy; {{ date('Y') }} {{ $brand }}. {{ __('common.rights_reserved') }}</p>
            <div class="d-flex gap-3">
                <a href="#" class="text-white-50 text-decoration-none">{{ __('common.privacy_policy') }}</a>
                <a href="#" class="text-white-50 text-decoration-none">{{ __('common.terms_of_service') }}</a>
                <a href="#" class="text-white-50 text-decoration-none">{{ __('common.cookie_policy') }}</a>
            </div>
        </div>
    </div>
</footer>
@endunless

@include('partials.app-toast')
@include('partials.floating-actions', ['brand' => $brand, 'supportPhone' => $supportPhone ?? null])

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/app-toast.js') }}"></script>
<script src="{{ asset('js/mobile-responsive.js') }}"></script>
<script>
    // Navbar search: icon toggles to input
    (function() {
        var trigger = document.getElementById('navbarSearchTrigger');
        var form = document.getElementById('navbarSearchForm');
        var input = form ? form.querySelector('.navbar-search-input') : null;
        var closeBtn = form ? form.querySelector('.navbar-search-close') : null;
        var wrap = trigger ? trigger.closest('.navbar-search-wrap') : null;
        if (!trigger || !form || !input || !wrap) return;
        function openSearch() {
            trigger.classList.add('d-none');
            form.classList.remove('d-none');
            input.focus();
        }
        function closeSearch() {
            form.classList.add('d-none');
            trigger.classList.remove('d-none');
        }
        trigger.addEventListener('click', openSearch);
        if (closeBtn) closeBtn.addEventListener('click', closeSearch);
        document.addEventListener('click', function(e) {
            if (wrap.contains(e.target)) return;
            if (form.classList.contains('d-none')) return;
            closeSearch();
        });
        if (input.value && input.value.trim() !== '') openSearch();
    })();
</script>
<script>
    // Menu / Meals dropdown: open on hover, close when mouse leaves (desktop only)
    document.addEventListener('DOMContentLoaded', function() {
        var el = document.getElementById('menuMealsDropdown');
        if (!el) return;
        var toggle = el.querySelector('.dropdown-toggle');
        var menu = el.querySelector('.dropdown-menu');
        var leaveTimer = null;
        var leaveDelay = 120;

        function isDesktop() {
            return window.matchMedia && window.matchMedia('(min-width: 992px)').matches;
        }
        function open() {
            if (!isDesktop()) return;
            if (leaveTimer) {
                clearTimeout(leaveTimer);
                leaveTimer = null;
            }
            el.classList.add('dropdown-open');
            if (toggle) toggle.setAttribute('aria-expanded', 'true');
            if (menu) {
                menu.classList.add('show');
                menu.style.display = 'block';
            }
        }
        function close() {
            if (!isDesktop()) return;
            leaveTimer = setTimeout(function() {
                leaveTimer = null;
                el.classList.remove('dropdown-open');
                if (toggle) toggle.setAttribute('aria-expanded', 'false');
                if (menu) {
                    menu.classList.remove('show');
                    menu.style.display = '';
                }
            }, leaveDelay);
        }

        el.addEventListener('mouseenter', open);
        el.addEventListener('mouseleave', close);
    });
</script>
<script>
    // Theme toggle functionality
    (function () {
        const html = document.documentElement;
        const toggles = [
            document.getElementById('themeToggle'),
            document.getElementById('themeToggleMobile'),
        ].filter(Boolean);

        function syncThemeIcons(isDark) {
            document.querySelectorAll('#themeIcon, #themeIconMobile').forEach(function (icon) {
                icon.classList.toggle('bi-sun', isDark);
                icon.classList.toggle('bi-moon-stars', ! isDark);
            });
        }

        function setTheme(theme) {
            html.setAttribute('data-bs-theme', theme);
            syncThemeIcons(theme === 'dark');
            localStorage.setItem('theme', theme);
        }

        if (localStorage.getItem('theme') === 'dark') {
            setTheme('dark');
        }

        toggles.forEach(function (toggle) {
            toggle.addEventListener('click', function () {
                setTheme(html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark');
            });
        });
    })();
</script>
<script>
    // Cart dropdown functionality
    (function() {
        const cartModal = document.getElementById('cartModal');
        const cartToggles = ['cartIconBtn', 'cartIconBtnMobile']
            .map(function (id) { return document.getElementById(id); })
            .filter(Boolean);

        cartToggles.forEach(function (cartToggle) {
            const dropdown = bootstrap.Dropdown.getOrCreateInstance(cartToggle);
            const menu = cartToggle.nextElementSibling;

            const viewFullCartBtn = menu ? menu.querySelector('[data-bs-target="#cartModal"]') : null;
            if (viewFullCartBtn && cartModal) {
                viewFullCartBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropdown.hide();
                    setTimeout(function() {
                        bootstrap.Modal.getOrCreateInstance(cartModal).show();
                    }, 150);
                });
            }

            if (menu) {
                menu.querySelectorAll('.cart-dropdown-actions a').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        dropdown.hide();
                    });
                });
            }
        });
    })();
</script>
@stack('scripts')
</body>
</html>
