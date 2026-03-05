<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta name="theme-color" content="#111111">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Nexus')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        /* ============================================
           UNIFIED DESIGN SYSTEM - Nexus
           ============================================ */
        
        :root {
            /* Core Colors - Apple-style Dark */
            --bg: #0d0d0d;
            --surface: #161616;
            --surface-hover: #1c1c1e;
            --border: #2a2a2a;
            --text: #f5f5f7;
            --text-muted: #86868b;

            /* Brand Colors - Purple (matches landing page) */
            --primary: #5e60ce;
            --primary-hover: #7400b8;
            --primary-glow: rgba(94, 96, 206, 0.3);
            
            --secondary: #4ea8de;
            --accent: #5e60ce;
            
            --success: #30d158;
            --warning: #ffd60a;

            /* Spacing Scale (4px base) */
            --space-1: 4px;
            --space-2: 8px;
            --space-3: 12px;
            --space-4: 16px;
            --space-5: 20px;
            --space-6: 24px;
            --space-8: 32px;
            --space-10: 40px;
            --space-12: 48px;

            /* Border Radius */
            --radius-sm: 8px;
            --radius: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --radius-full: 9999px;

            /* Shadows */
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
            --shadow-lg: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            --shadow-glow: 0 0 20px var(--primary-glow);

            /* Transitions - Removed for performance */
            --transition-fast: 0ms;
            --transition: 0ms;
            --transition-slow: 0ms;

            /* Z-Index Scale */
            --z-dropdown: 100;
            --z-sticky: 200;
            --z-fixed: 300;
            --z-modal-backdrop: 400;
            --z-modal: 500;
            --z-popover: 600;
            --z-tooltip: 700;
            --z-toast: 800;

            /* Legacy Compatibility (deprecated - use core variables above) */
            --twitter-blue: var(--primary);
            --twitter-dark: var(--text);
            --twitter-gray: var(--text-muted);
            --twitter-light: var(--surface);
            --card-bg: var(--surface);
            --border-color: var(--border);
            --input-bg: var(--surface);
            --hover-bg: var(--surface-hover);
            --focus-border: var(--primary);
            --error-color: var(--accent);
            --success-color: var(--success);
        }

        [data-theme="light"] {
            --bg: #ffffff;
            --surface: #f9fafb;
            --surface-hover: #f3f4f6;
            --border: #e5e7eb;
            --text: #111111;
            --text-muted: #6b7280;

            --primary: #5e60ce;
            --primary-hover: #7400b8;
            --primary-glow: rgba(94, 96, 206, 0.2);
            --secondary: #4ea8de;
            --accent: #5e60ce;
            --success: #30d158;
            --warning: #ffd60a;

            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            --shadow-glow: 0 0 20px rgba(94, 96, 206, 0.15);

            /* Legacy Compatibility - Light */
            --twitter-blue: var(--primary);
            --twitter-dark: var(--text);
            --twitter-gray: var(--text-muted);
            --twitter-light: var(--surface);
            --card-bg: #ffffff;
            --border-color: var(--border);
            --input-bg: #ffffff;
            --hover-bg: var(--surface-hover);
            --focus-border: var(--primary);
            --error-color: var(--accent);
            --success-color: var(--success);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Noto Sans Arabic', 'Tahoma', 'Arial', sans-serif; 
            -webkit-font-smoothing: antialiased; 
        }
        body { 
            font-size: 14px; 
            line-height: 1.6; 
            color: var(--text); 
            background: var(--bg); 
            min-height: 100vh; 
            overflow-x: hidden; 
        }

        /* Header — Same as Login/Register Pages */
        .header {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 10px 40px;
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            background: rgba(13, 13, 13, 0.8);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: center;
            z-index: var(--z-fixed);
        }

        /* Light Theme Header */
        [data-theme="light"] .header {
            background: rgba(255, 255, 255, 0.8);
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .header-inner {
            max-width: 1200px;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 56px;
            padding: 0 8px;
        }

        .logo {
            font-weight: 700;
            font-size: 20px;
            text-decoration: none;
            color: #ffffff;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            letter-spacing: -0.02em;
        }

        /* Light Theme Logo */
        [data-theme="light"] .logo {
            color: #000000;
        }

        .nav-links { 
            display: flex; 
            align-items: center; 
            gap: 8px;
            background: rgba(255, 255, 255, 0.05);
            padding: 4px 8px;
            border-radius: 9999px;
        }
        .nav-links a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            font-size: 14px;
            font-weight: 500;
            color: #86868b;
            text-decoration: none;
            transition: 0.3s;
            border-radius: 9999px;
        }
        .nav-links a:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.08);
        }
        .nav-links a.active {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.1);
        }
        .nav-links a i {
            font-size: 15px;
        }

        /* Light Theme Nav Links */
        [data-theme="light"] .nav-links {
            background: rgba(0, 0, 0, 0.05);
        }
        [data-theme="light"] .nav-links a {
            color: #6b7280;
        }
        [data-theme="light"] .nav-links a:hover {
            color: #000000;
            background: rgba(0, 0, 0, 0.08);
        }
        [data-theme="light"] .nav-links a.active {
            color: #000000;
            background: rgba(0, 0, 0, 0.1);
        }

        .user-actions { display: flex; align-items: center; gap: 8px; }

        /* Navigation Action Buttons - Updated */
        .nav-action-btn {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: #86868b;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        /* Desktop only hover effects */
        @media (hover: hover) and (min-width: 901px) {
            .nav-action-btn:hover {
                background: rgba(255, 255, 255, 0.1);
                border-color: rgba(255, 255, 255, 0.2);
                color: #ffffff;
            }
            .nav-links a:hover {
                color: #ffffff;
                background: rgba(255, 255, 255, 0.08);
            }
            .nav-user-btn:hover {
                background: rgba(255, 255, 255, 0.1);
                border-color: rgba(255, 255, 255, 0.2);
            }
        }

        /* Mobile - Remove hover from theme and notification buttons */
        @media (max-width: 900px) {
            .nav-action-btn:not(.primary):hover {
                background: rgba(255, 255, 255, 0.06) !important;
                border-color: rgba(255, 255, 255, 0.1) !important;
                color: #86868b !important;
            }
            .nav-links a:hover {
                color: #86868b !important;
                background: transparent !important;
            }
            .nav-user-btn:hover {
                background: rgba(255, 255, 255, 0.06) !important;
                border-color: rgba(255, 255, 255, 0.1) !important;
            }
        }

        .nav-action-btn.primary {
            width: auto;
            padding: 8px 18px;
            background: #fff;
            border: none;
            color: #000;
            font-weight: 600;
            font-size: 14px;
            border-radius: 980px;
            height: 40px;
        }

        .nav-action-btn.primary:hover {
            background: #f5f5f5;
            transform: scale(1.02);
        }

        .nav-action-btn .badge {
            position: absolute;
            top: 6px;
            right: 6px;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            background: #ef4444;
            color: white;
            font-size: 10px;
            font-weight: 700;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(13, 13, 13, 0.8);
            line-height: 1;
        }

        /* Light Theme Badge */
        [data-theme="light"] .nav-action-btn .badge {
            border: 2px solid rgba(255, 255, 255, 0.8);
        }

        /* Light Theme Action Buttons */
        [data-theme="light"] .nav-action-btn {
            background: rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(0, 0, 0, 0.1);
            color: #6b7280;
        }

        /* Desktop only hover effects - Light Theme */
        @media (hover: hover) and (min-width: 901px) {
            [data-theme="light"] .nav-action-btn:hover {
                background: rgba(0, 0, 0, 0.1);
                border-color: rgba(0, 0, 0, 0.15);
                color: #000000;
            }
            [data-theme="light"] .nav-links a:hover {
                color: #000000;
                background: rgba(0, 0, 0, 0.08);
            }
            [data-theme="light"] .nav-user-btn:hover {
                background: rgba(0, 0, 0, 0.1);
                border-color: rgba(0, 0, 0, 0.15);
            }
        }

        /* Mobile - Light Theme - Remove hover */
        @media (max-width: 900px) {
            [data-theme="light"] .nav-action-btn:not(.primary):hover {
                background: rgba(0, 0, 0, 0.06) !important;
                border-color: rgba(0, 0, 0, 0.1) !important;
                color: #6b7280 !important;
            }
            [data-theme="light"] .nav-links a:hover {
                color: #6b7280 !important;
                background: transparent !important;
            }
            [data-theme="light"] .nav-user-btn:hover {
                background: rgba(0, 0, 0, 0.06) !important;
                border-color: rgba(0, 0, 0, 0.1) !important;
            }
        }

        [data-theme="light"] .nav-action-btn:active {
            background: rgba(0, 0, 0, 0.06);
        }

        /* Mobile - Header */
        @media (max-width: 480px) {
            .header {
                padding: 10px 16px;
            }
            .header-inner {
                height: 52px;
            }
            .nav-links {
                gap: 4px;
                padding: 4px 4px;
            }
            .nav-links a {
                padding: 6px 10px;
                font-size: 13px;
            }
            .nav-action-btn {
                width: 36px;
                height: 36px;
            }
            .nav-action-btn .badge {
                top: 2px;
                right: 2px;
                min-width: 16px;
                height: 16px;
                font-size: 9px;
                padding: 0 4px;
            }
            .nav-user-btn {
                height: 36px;
                padding: 4px 8px 4px 4px;
            }
            .nav-user-btn .user-avatar {
                width: 28px;
                height: 28px;
            }
            .nav-user-btn span {
                font-size: 12px;
                max-width: 80px;
            }
            .nav-user-btn .fa-chevron-down {
                font-size: 9px;
            }
        }

        /* Ensure body has proper min-height to prevent content from hiding behind header */
        body {
            padding-top: 72px;
        }

        /* Tablet - Adjust header height calculation */
        @media (max-width: 900px) and (min-width: 481px) {
            body {
                padding-top: 72px;
            }
        }

        /* Laptop/Desktop - Adjust header height calculation */
        @media (min-width: 901px) {
            body {
                padding-top: 76px;
            }
        }

        /* Navigation User Button - Updated */
        .nav-user-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 12px 5px 5px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 980px;
            cursor: pointer;
            transition: 0.3s;
            height: 38px;
        }

        .nav-user-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .nav-user-btn .user-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0;
            border: 2px solid rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .nav-user-btn .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .nav-user-btn span {
            font-size: 13px;
            font-weight: 500;
            color: #ffffff;
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Light Theme User Button */
        [data-theme="light"] .nav-user-btn {
            background: rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        [data-theme="light"] .nav-user-btn:hover {
            background: rgba(0, 0, 0, 0.1);
            border-color: rgba(0, 0, 0, 0.15);
        }

        [data-theme="light"] .nav-user-btn span {
            color: #000000;
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        [data-theme="light"] .nav-user-btn .user-avatar {
            border-color: rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        [data-theme="light"] .nav-user-btn .fa-chevron-down {
            color: #6b7280;
        }

        .dropdown-overlay {
            display: none; position: fixed; inset: 0; z-index: 9998;
            background: rgba(0, 0, 0, 0.5);
        }
        .dropdown-overlay.active { display: block; }

        /* Light Theme Dropdown Overlay */
        [data-theme="light"] .dropdown-overlay {
            background: rgba(0, 0, 0, 0.3);
        }

        .dropdown-wrapper {
            position: relative;
        }

        .dropdown-menu {
            display: none !important;
            position: fixed !important;
            min-width: 240px !important;
            background: rgba(22, 22, 22, 0.95) !important;
            backdrop-filter: blur(40px) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 14px !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4) !important;
            z-index: 9999 !important;
            padding: 8px !important;
        }
        .dropdown-menu.show {
            display: block !important;
        }

        /* User Menu Dropdown - Specific styles */
        #userMenu {
            background: rgba(22, 22, 22, 0.95) !important;
            backdrop-filter: blur(40px) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 14px !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4) !important;
            padding: 8px !important;
            min-width: 240px !important;
        }

        #userMenu a, #userMenu button {
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            width: 100% !important;
            padding: 12px 14px !important;
            border: none !important;
            background: none !important;
            color: #f5f5f7 !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            text-decoration: none !important;
            border-radius: 10px !important;
            cursor: pointer !important;
            transition: 0.3s !important;
            margin: 0 !important;
            text-align: left !important;
            justify-content: flex-start !important;
        }
        #userMenu a:hover, #userMenu button:hover {
            background: rgba(255, 255, 255, 0.05) !important;
            text-decoration: none !important;
        }
        #userMenu a i, #userMenu button i {
            width: 18px !important;
            text-align: center !important;
            color: #86868b !important;
            font-size: 16px !important;
            flex-shrink: 0 !important;
        }
        #userMenu .divider {
            height: 1px !important;
            margin: 8px 0 !important;
            background: rgba(255, 255, 255, 0.08) !important;
            display: block !important;
        }
        #userMenu .danger {
            color: #ff6b6b !important;
        }
        #userMenu .danger:hover {
            background: rgba(255, 107, 107, 0.1) !important;
            color: #ff6b6b !important;
        }

        /* Light Theme Dropdown */
        [data-theme="light"] #userMenu {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(40px) !important;
            border: 1px solid rgba(0, 0, 0, 0.08) !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15) !important;
        }

        [data-theme="light"] #userMenu a,
        [data-theme="light"] #userMenu button {
            color: #111111 !important;
        }

        [data-theme="light"] #userMenu a:hover,
        [data-theme="light"] #userMenu button:hover {
            background: rgba(0, 0, 0, 0.05) !important;
        }

        [data-theme="light"] #userMenu a i,
        [data-theme="light"] #userMenu button i {
            color: #6b7280 !important;
        }

        [data-theme="light"] #userMenu .divider {
            background: rgba(0, 0, 0, 0.08) !important;
        }

        /* Notification Panel Styles - Same as Login/Register Pages */
        .notif-panel {
            width: 380px !important;
            max-width: calc(100vw - 32px) !important;
            background: rgba(22, 22, 22, 0.95) !important;
            backdrop-filter: blur(40px) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 16px !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4) !important;
            overflow: hidden !important;
            padding: 0 !important;
        }
        .notif-header {
            padding: 18px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(22, 22, 22, 0.95);
        }
        .notif-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #f5f5f7;
        }
        .notif-header-actions {
            display: flex;
            gap: 8px;
        }
        .notif-action-btn {
            background: transparent;
            border: none;
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 8px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .notif-action-btn:hover {
            background: rgba(139, 92, 246, 0.1);
            color: var(--primary);
        }
        .notif-action-btn.danger:hover {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }
        .notif-list {
            max-height: 450px;
            overflow-y: auto;
            background: var(--bg);
        }
        .notif-list::-webkit-scrollbar {
            width: 6px;
        }
        .notif-list::-webkit-scrollbar-track {
            background: transparent;
        }
        .notif-list::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 3px;
        }
        .notif-item {
            padding: 16px 20px;
            cursor: pointer;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            transition: 0.3s;
            position: relative;
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }
        .notif-item:last-child {
            border-bottom: none;
        }
        .notif-item:hover {
            background: rgba(255, 255, 255, 0.03);
        }
        .notif-item:hover .notif-item-actions {
            opacity: 1;
        }
        .notif-item.unread {
            background: rgba(94, 96, 206, 0.08);
        }
        .notif-item.unread::after {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 8px;
            height: 8px;
            background: var(--primary);
            border-radius: 50%;
        }
        .notif-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 18px;
        }
        .notif-icon.follow { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
        .notif-icon.like { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
        .notif-icon.comment { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
        .notif-icon.mention { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; }
        .notif-icon.message { background: rgba(6, 182, 212, 0.12); color: #06b6d4; }
        .notif-icon.group { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
        .notif-icon.default { background: rgba(156, 163, 175, 0.12); color: #9ca3af; }
        .notif-content {
            flex: 1;
            min-width: 0;
        }
        .notif-content p {
            margin: 0 0 6px 0;
            font-size: 14px;
            color: #f5f5f7;
            line-height: 1.5;
            font-weight: 400;
        }
        .notif-content.unread p {
            font-weight: 600;
        }
        .notif-time {
            font-size: 12px;
            color: #86868b;
            display: block;
        }
        .notif-item-actions {
            display: flex;
            gap: 6px;
            flex-shrink: 0;
            opacity: 1;
            transition: 0.3s;
        }
        .notif-item:hover .notif-item-actions {
            opacity: 1;
        }
        .notif-item-btn {
            width: 32px;
            height: 32px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.03);
            color: #86868b;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
            font-size: 14px;
        }
        .notif-item-btn:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary);
            color: #fff;
        }
        .notif-item-btn.delete:hover {
            border-color: #ef4444;
            color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
        }

        /* Light Theme Notification Panel */
        [data-theme="light"] .notif-panel {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(40px) !important;
            border: 1px solid rgba(0, 0, 0, 0.08) !important;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15) !important;
        }

        [data-theme="light"] .notif-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            background: rgba(255, 255, 255, 0.95);
        }

        [data-theme="light"] .notif-header h3 {
            color: #111111;
        }

        [data-theme="light"] .notif-item {
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        }

        [data-theme="light"] .notif-item:hover {
            background: rgba(0, 0, 0, 0.03);
        }

        [data-theme="light"] .notif-item.unread {
            background: rgba(94, 96, 206, 0.08);
        }

        [data-theme="light"] .notif-content p {
            color: #111111;
        }

        [data-theme="light"] .notif-content.unread p {
            font-weight: 600;
        }

        [data-theme="light"] .notif-time {
            color: #6b7280;
        }

        [data-theme="light"] .notif-item-btn {
            border: 1px solid rgba(0, 0, 0, 0.08);
            background: rgba(0, 0, 0, 0.03);
            color: #6b7280;
        }

        [data-theme="light"] .notif-item-btn:hover {
            background: rgba(0, 0, 0, 0.08);
            border-color: var(--primary);
            color: #ffffff;
        }

        [data-theme="light"] .notif-empty {
            color: #6b7280;
        }
        
        /* Mobile - always show actions */
        @media (max-width: 768px) {
            .notif-item-actions {
                opacity: 1 !important;
            }
            .notif-item-btn {
                width: 36px;
                height: 36px;
            }
        }
        .notif-empty {
            padding: 60px 20px;
            text-align: center;
            color: #86868b;
        }
        .notif-empty i {
            font-size: 56px;
            margin-bottom: 16px;
            display: block;
            opacity: 0.3;
        }
        .notif-empty p {
            margin: 0;
            font-size: 15px;
        }

        @media (max-width: 480px) {
            .notif-panel {
                width: calc(100vw - 16px);
                max-height: 75vh;
                border-radius: 12px;
            }
            .notif-header {
                padding: 14px 16px;
            }
            .notif-header h3 {
                font-size: 16px;
            }
            .notif-action-btn span {
                display: none;
            }
            .notif-item {
                padding: 14px 16px;
            }
            .notif-icon {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }
            .notif-content p {
                font-size: 13px;
            }
            .notif-item-actions {
                opacity: 1;
            }
            .notif-item.unread::after {
                right: 60px;
            }
        }

        .app-layout { max-width: 1200px; margin: 0 auto; padding: 0 24px 32px; }
        .main-content { max-width: 800px; margin: 0 auto; }

        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 10px 20px; font-size: 14px; font-weight: 600;
            border-radius: var(--radius); border: 1px solid var(--border);
            background: var(--surface); color: var(--text);
            cursor: pointer; text-decoration: none; transition: all var(--transition);
        }
        .btn:hover { background: var(--surface-hover); border-color: var(--primary); }

        .btn-primary {
            background: var(--primary);
            color: #ffffff; border: none;
            font-weight: 700;
        }
        .btn-primary:hover { 
            background: var(--primary-hover);
        }
        
        body.light-theme .btn-primary {
            color: #ffffff;
        }
        body.light-theme .btn-primary:hover {
            background: var(--primary-hover);
        }

        .btn-ghost { border-color: transparent; background: transparent; }
        .btn-ghost:hover { background: var(--surface); border-color: var(--border); }

        .card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius-lg); padding: 20px;
        }

        .form-input {
            width: 100%; padding: 12px 16px; font-size: 14px;
            border: 1px solid var(--border); border-radius: var(--radius);
            background: var(--surface); color: var(--text); transition: all var(--transition);
        }
        .form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); }
        .form-input::placeholder { color: var(--text-muted); }

        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--text); font-size: 14px; }

        #toast-container {
            position: fixed; top: 80px; right: 20px; left: 20px;
            max-width: 400px; margin: 0 auto; z-index: 10000;
            display: flex; flex-direction: column; gap: 12px; pointer-events: none;
        }
        .toast {
            padding: 12px 16px;
            background: rgba(26, 26, 26, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text);
            font-size: 14px; font-weight: 500;
            border-radius: 12px;
            display: flex; align-items: center; gap: 10px; pointer-events: auto;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        }
        .toast i:first-child {
            font-size: 18px;
            width: 28px; height: 28px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .toast.success { 
            background: rgba(34, 197, 94, 0.15);
            border-color: rgba(34, 197, 94, 0.3);
            box-shadow: 0 8px 32px rgba(34, 197, 94, 0.3), 0 0 0 1px rgba(34, 197, 94, 0.1) inset;
        }
        .toast.success i:first-child {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
        }
        .toast.error { 
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.3);
            box-shadow: 0 8px 32px rgba(239, 68, 68, 0.3), 0 0 0 1px rgba(239, 68, 68, 0.1) inset;
        }
        .toast.error i:first-child {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }
        .toast.info {
            background: rgba(59, 130, 246, 0.15);
            border-color: rgba(59, 130, 246, 0.3);
            box-shadow: 0 8px 32px rgba(59, 130, 246, 0.2), 0 0 0 1px rgba(59, 130, 246, 0.1) inset;
        }
        .toast.info i:first-child {
            background: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
        }
        body.light-theme .toast {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(0, 0, 0, 0.08);
            color: #1e293b;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12), 0 0 0 1px rgba(255, 255, 255, 0.5) inset;
        }
        body.light-theme .toast::before {
            background: linear-gradient(90deg, transparent, rgba(0,0,0,0.05), transparent);
        }
        body.light-theme .toast.success {
            background: rgba(34, 197, 94, 0.1);
            border-color: rgba(34, 197, 94, 0.2);
        }
        body.light-theme .toast.error {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.2);
        }
        body.light-theme .toast.info {
            background: rgba(59, 130, 246, 0.08);
            border-color: rgba(59, 130, 246, 0.2);
        }
        @keyframes toastIn { 
            from { opacity: 0; transform: translateY(-20px) scale(0.95); } 
            to { opacity: 1; transform: translateY(0) scale(1); } 
        }
        @keyframes toastOut { 
            from { opacity: 1; transform: translateY(0) scale(1); } 
            to { opacity: 0; transform: translateY(-10px) scale(0.95); } 
        }

        /* Mobile Navigation - Landing Page Style */
        .mobile-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(13, 13, 13, 0.9);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            padding: 8px 0 calc(12px + env(safe-area-inset-bottom));
            z-index: var(--z-fixed);
        }

        .mobile-nav-inner {
            display: flex;
            justify-content: space-around;
            align-items: center;
        }

        .mobile-nav a {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 8px 12px;
            color: rgba(245, 245, 247, 0.8);
            text-decoration: none;
            font-size: 11px;
            font-weight: 500;
            border-radius: var(--radius-full);
            transition: all 0.2s ease;
            min-width: 60px;
            opacity: 0.8;
        }

        .mobile-nav a i {
            font-size: 22px;
            margin-bottom: 4px;
        }

        .mobile-nav a:hover {
            opacity: 1;
            background: rgba(255, 255, 255, 0.1);
        }

        .mobile-nav a.active {
            opacity: 1;
            color: #5e60ce;
            background: rgba(94, 96, 206, 0.15);
        }

        /* Light Theme Mobile Nav */
        [data-theme="light"] .mobile-nav {
            background: rgba(255, 255, 255, 0.9);
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        [data-theme="light"] .mobile-nav a {
            color: rgba(17, 17, 17, 0.8);
        }

        [data-theme="light"] .mobile-nav a:hover {
            background: rgba(0, 0, 0, 0.08);
        }

        [data-theme="light"] .mobile-nav a.active {
            color: #5e60ce;
            background: rgba(94, 96, 206, 0.15);
        }

        @media (max-width: 900px) {
            .nav-links { display: none; }
            .mobile-nav { display: block; }
            .app-layout { padding: 0 16px 100px; }
            .nav-links a:hover, .nav-links a.active { background: transparent; }
            .dropdown-menu a:hover, .dropdown-menu button:hover { background: transparent; }
            .notif-item:hover { background: transparent; }
            .notif-action-btn:hover { background: var(--surface-hover); color: var(--text-muted); border-color: var(--border); }
            .notif-action-btn.danger:hover { background: var(--surface-hover); border-color: var(--border); }
            .notif-item-btn:hover { background: var(--surface-hover); color: var(--text-muted); border-color: var(--border); }
            .btn:hover { background: var(--surface); border-color: var(--border); }
            .btn-primary:hover { background: var(--primary); }
        }
        @media (max-width: 480px) {
            .header-inner { padding: 0 12px; height: 48px; }
            .logo { font-size: 17px; }
            .nav-user-btn span { max-width: 80px; font-size: 12px; }
            .nav-user-btn { padding: 4px 8px 4px 4px; }
            .nav-action-btn { width: 36px; height: 36px; font-size: 16px; }
            #toast-container { right: 10px; left: 10px; max-width: none; bottom: 80px; }
            #backToTopBtn { width: 40px; height: 40px; bottom: 90px; right: 15px; font-size: 18px; }
        }

        /* Toast Notifications */
        #toast-container {
            position: fixed;
            bottom: 100px;
            right: 20px;
            z-index: var(--z-toast);
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 400px;
            pointer-events: none;
        }
        .toast {
            padding: 12px 16px;
            border-radius: var(--radius);
            background: var(--surface);
            border: 1px solid var(--border);
            color: var(--text);
            font-size: 14px;
            box-shadow: var(--shadow);
            pointer-events: auto;
            animation: slideInRight 0.3s ease-out;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .toast.success { border-left: 4px solid var(--success); }
        .toast.error { border-left: 4px solid #ff3b30; }
        .toast.warning { border-left: 4px solid var(--warning); }
        .toast.info { border-left: 4px solid var(--primary); }
        .toast i { font-size: 16px; }
        @keyframes slideInRight {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(400px); opacity: 0; }
        }
        .toast.hide { animation: slideOutRight 0.3s ease-out forwards; }

        /* Back to Top Button */
        #backToTopBtn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            border: none;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 500;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            font-size: 20px;
        }
        #backToTopBtn:hover { background: var(--primary-hover); transform: translateY(-4px); box-shadow: var(--shadow-lg); }
        #backToTopBtn.show { display: flex; }
    </style>
</head>
<body id="app-body">
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>

    <header class="header">
        <div class="header-inner">
            <a href="{{ route('home') }}" class="logo">Nexus</a>

            @auth
            <nav class="nav-links">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}"><i class="fas fa-home"></i> Home</a>
                <a href="{{ route('stories.index') }}" class="{{ request()->routeIs('stories.*') ? 'active' : '' }}"><i class="fas fa-circle-play"></i> Stories</a>
                <a href="{{ route('chat.index') }}" class="{{ request()->routeIs('chat.*') ? 'active' : '' }}"><i class="fas fa-message"></i> Messages</a>
                <a href="{{ route('ai.index') }}" class="{{ request()->routeIs('ai.*') ? 'active' : '' }}"><i class="fas fa-robot"></i> AI</a>
            </nav>
            @endauth

            <div class="user-actions">
                <button class="nav-action-btn" onclick="toggleTheme()" title="Toggle theme">
                    <i class="fas fa-sun" id="theme-icon"></i>
                </button>

                @auth
                <div style="position: relative;">
                    <button class="nav-action-btn" id="notifBtn" onclick="toggleNotifications(event)">
                        <i class="fas fa-bell"></i>
                        <span class="badge" id="notif-badge" style="display: none;">0</span>
                    </button>
                </div>

                <div style="position: relative;">
                    <button class="nav-user-btn" id="userBtn" onclick="toggleUserMenu(event)">
                        <div class="user-avatar">
                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->username }}">
                        </div>
                        <span>{{ auth()->user()->username }}</span>
                        <i class="fas fa-chevron-down" style="font-size: 10px; color: var(--text-muted);"></i>
                    </button>
                </div>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
                @else
                <a href="{{ route('login') }}" class="nav-action-btn">Sign In</a>
                <a href="{{ route('register') }}" class="nav-action-btn primary">Sign Up</a>
                @endauth
            </div>
        </div>
    </header>

    <div class="dropdown-overlay" id="dropdownOverlay" onclick="closeAllDropdowns()"></div>

    @auth
    <!-- Notification Dropdown - Simple Modern Design -->
    <div class="dropdown-menu notif-panel" id="notifMenu">
        <div class="notif-header">
            <h3>Notifications</h3>
            <div class="notif-header-actions">
                <button class="notif-action-btn" onclick="markAllRead(); return false;" title="Mark all as read">
                    <i class="fas fa-check"></i>
                    <span>Read all</span>
                </button>
                <button class="notif-action-btn danger" onclick="clearAllNotifications(); return false;" title="Clear all">
                    <i class="fas fa-trash"></i>
                    <span>Clear</span>
                </button>
            </div>
        </div>
        <div class="notif-list" id="notif-list">
            <div class="notif-empty">
                <i class="fas fa-bell-slash"></i>
                <p>No notifications</p>
            </div>
        </div>
    </div>

    <!-- User Menu Dropdown - outside header for proper z-index -->
    <div class="dropdown-menu" id="userMenu">
        <a href="{{ route('users.show', auth()->user()) }}"><i class="fas fa-user"></i> Profile</a>
        <a href="{{ route('users.saved-posts') }}"><i class="fas fa-bookmark"></i> Saved</a>
        <a href="{{ route('explore') }}"><i class="fas fa-compass"></i> Explore Users</a>
        <a href="{{ route('ai.index') }}"><i class="fas fa-robot"></i> AI Assistant</a>
        @if(auth()->user()->is_admin)
        <a href="{{ route('admin.dashboard') }}"><i class="fas fa-shield-alt"></i> Admin</a>
        @endif
        <div class="divider"></div>
        <a href="{{ route('password.change') }}"><i class="fas fa-key"></i> Change Password</a>
        <button onclick="logout()" class="danger"><i class="fas fa-sign-out-alt"></i> Logout</button>
    </div>
    @endauth

    <main class="app-layout">
        <div class="main-content">
            @yield('content')
        </div>
    </main>

    @auth
            <nav class="mobile-nav">
                <div class="mobile-nav-inner">
                    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}"><i class="fas fa-home"></i> Home</a>
                    <a href="{{ route('stories.index') }}" class="{{ request()->routeIs('stories.*') ? 'active' : '' }}"><i class="fas fa-circle-play"></i> Stories</a>
                    <a href="{{ route('chat.index') }}" class="{{ request()->routeIs('chat.*') ? 'active' : '' }}"><i class="fas fa-message"></i> Chat</a>
                    <a href="{{ route('users.show', auth()->user()) }}" class="{{ request()->routeIs('users.show') ? 'active' : '' }}"><i class="fas fa-user"></i> Profile</a>
                </div>
            </nav>
    @endauth

    <div id="toast-container"></div>

    @vite(['resources/js/app.js'])
    @auth
        <script>
            window.currentUserId = {{ auth()->id() }};
        </script>
        <script src="{{ asset('js/realtime.js') }}?v={{ time() }}"></script>
    @endauth
    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const icon = document.getElementById('theme-icon');
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', newTheme);
            icon.className = newTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            localStorage.setItem('theme', newTheme);
        }

        function toggleUserMenu(event) {
            event.stopPropagation();
            event.preventDefault();
            const menu = document.getElementById('userMenu');
            const btn = event.currentTarget;
            const isOpen = menu.classList.contains('show');
            closeAllDropdowns();
            if (!isOpen) {
                const rect = btn.getBoundingClientRect();
                menu.style.top = (rect.bottom + 8) + 'px';
                menu.style.right = (window.innerWidth - rect.right) + 'px';
                menu.classList.add('show');
                document.getElementById('dropdownOverlay').classList.add('active');
            }
        }

        function toggleNotifications(event) {
            event.stopPropagation();
            event.preventDefault();
            const menu = document.getElementById('notifMenu');
            const btn = document.getElementById('notifBtn');
            const isOpen = menu.classList.contains('show');
            closeAllDropdowns();
            if (!isOpen) {
                const rect = btn.getBoundingClientRect();
                const menuWidth = 380;
                const padding = 16;

                let top = rect.bottom + 8;
                let right = window.innerWidth - rect.right;

                if (right + menuWidth > window.innerWidth - padding) {
                    right = padding;
                }

                menu.style.top = top + 'px';
                menu.style.right = right + 'px';
                menu.style.left = 'auto';
                menu.classList.add('show');
                document.getElementById('dropdownOverlay').classList.add('active');
                loadNotifications();
            }
        }

        function closeAllDropdowns() {
            document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.remove('show'));
            document.getElementById('dropdownOverlay').classList.remove('active');
        }

        function logout() { if (confirm('Sign out?')) document.getElementById('logout-form').submit(); }

        function showToast(message, type = 'info', duration = 3000) {
            const container = document.getElementById('toast-container');

            if (!container) {
                return;
            }

            const toast = document.createElement('div');
            toast.className = 'toast ' + type;
            const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
            toast.innerHTML = `<i class="fas ${icon}"></i> <span>${message}</span>`;

            container.appendChild(toast);
            toast.style.animation = 'toastIn 0.3s ease forwards';

            setTimeout(() => {
                toast.style.animation = 'toastOut 0.3s ease forwards';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }

        function loadNotifications() {
            fetch('/api/notifications', { 
                credentials: 'include',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                const list = document.getElementById('notif-list');
                const badge = document.getElementById('notif-badge');
                if (data.unread_count > 0) { badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count; badge.style.display = 'flex'; }
                else { badge.style.display = 'none'; }
                if (!data.notifications || data.notifications.length === 0) {
                    list.innerHTML = `<div class="notif-empty"><i class="fas fa-bell-slash"></i><p>No notifications</p></div>`;
                    return;
                }
                list.innerHTML = data.notifications.map(n => {
                    const iconClass = getNotificationIconClass(n.type);
                    const notifIcon = getNotificationIcon(n.type);
                    const timeAgo = getTimeAgo(n.created_at);
                    const truncatedMessage = n.message.length > 60 ? n.message.substring(0, 60) + '...' : n.message;
                    return `
                    <div class="notif-item ${n.read_at ? '' : 'unread'}" id="notif-${n.id}" data-id="${n.id}">
                        <div class="notif-icon ${iconClass}" onclick="handleNotifClick(${n.id}, '${n.link || ''}')">
                            <i class="fas ${notifIcon}"></i>
                        </div>
                        <div class="notif-content ${n.read_at ? '' : 'unread'}" onclick="handleNotifClick(${n.id}, '${n.link || ''}')">
                            <p>${escapeHtml(truncatedMessage)}</p>
                            <span class="notif-time">${timeAgo}</span>
                        </div>
                        <div class="notif-item-actions">
                            ${!n.read_at ? `<button class="notif-item-btn" onclick="markAsRead(${n.id}); return false;" title="Mark as read"><i class="fas fa-check"></i></button>` : ''}
                            <button class="notif-item-btn delete" onclick="dismissNotification(${n.id}); return false;" title="Delete"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                `}).join('');
            }).catch(() => {});
        }

        function markAsRead(id) {
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }
            
            // Update UI immediately
            const notifItem = document.querySelector(`.notif-item[data-id="${id}"]`);
            if (notifItem) {
                // Remove unread class (this hides the dot)
                notifItem.classList.remove('unread');
                notifItem.querySelector('.notif-content')?.classList.remove('unread');

                // Update actions to show only delete button
                const actionsDiv = notifItem.querySelector('.notif-item-actions');
                if (actionsDiv) {
                    actionsDiv.innerHTML = `<button class="notif-item-btn delete" onclick="dismissNotification(${id}); return false;" title="Delete"><i class="fas fa-trash"></i></button>`;
                }

                // Update badge immediately
                const badge = document.getElementById('notif-badge');
                if (badge && badge.style.display !== 'none') {
                    const count = parseInt(badge.textContent) || 0;
                    if (count > 1) {
                        badge.textContent = count - 1;
                    } else {
                        badge.style.display = 'none';
                    }
                }
            }

            // Send API request (fire and forget with CSRF)
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch('/api/notifications/' + id + '/read', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token },
                keepalive: true
            }).catch(() => {});
        }

        function markAllRead() {
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }
            
            // Update UI immediately
            const notifItems = document.querySelectorAll('.notif-item.unread');
            notifItems.forEach(item => {
                // Remove unread class (this hides the dot)
                item.classList.remove('unread');
                item.querySelector('.notif-content')?.classList.remove('unread');

                const id = item.getAttribute('data-id');
                const actionsDiv = item.querySelector('.notif-item-actions');
                if (actionsDiv && id) {
                    actionsDiv.innerHTML = `<button class="notif-item-btn delete" onclick="dismissNotification(${id}); return false;" title="Delete"><i class="fas fa-trash"></i></button>`;
                }
            });
            document.getElementById('notif-badge').style.display = 'none';

            // Send API request (fire and forget with CSRF)
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch('/api/notifications/mark-all-read', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token },
                keepalive: true
            }).catch(() => {});
        }

        function getNotificationIconClass(type) {
            const classes = {
                'follow': 'follow',
                'like': 'like',
                'comment': 'comment',
                'mention': 'mention',
                'message': 'message',
                'group_invite': 'group'
            };
            return classes[type] || 'default';
        }

        function getNotificationIcon(type) {
            const icons = {
                'follow': 'fa-user-plus',
                'like': 'fa-heart',
                'comment': 'fa-comment',
                'mention': 'fa-at',
                'message': 'fa-envelope',
                'group_invite': 'fa-users',
                'post': 'fa-newspaper',
                'story': 'fa-circle-play'
            };
            return icons[type] || 'fa-bell';
        }

        function getTimeAgo(dateStr) {
            const date = new Date(dateStr);
            const now = new Date();
            const seconds = Math.floor((now - date) / 1000);
            if (seconds < 60) return 'Just now';
            const minutes = Math.floor(seconds / 60);
            if (minutes < 60) return minutes + 'm ago';
            const hours = Math.floor(minutes / 60);
            if (hours < 24) return hours + 'h ago';
            const days = Math.floor(hours / 24);
            if (days < 7) return days + 'd ago';
            return date.toLocaleDateString();
        }

        function handleNotifClick(id, link) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            // Update badge immediately
            const badge = document.getElementById('notif-badge');
            if (badge && badge.style.display !== 'none') {
                const count = parseInt(badge.textContent) || 0;
                if (count > 1) {
                    badge.textContent = count - 1;
                } else {
                    badge.style.display = 'none';
                }
            }

            // Mark as read (fire and forget with CSRF)
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch('/api/notifications/' + id + '/read', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': token },
                keepalive: true
            }).catch(() => {});

            // Navigate if link exists
            if (link) {
                closeAllDropdowns();
                window.location.href = link;
            } else {
                closeAllDropdowns();
                loadNotifications();
            }
        }

        function dismissNotification(id) {
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }
            
            // Update UI immediately
            const notifItem = document.querySelector(`.notif-item[data-id="${id}"]`);
            if (notifItem) {
                notifItem.remove();

                // Update badge immediately
                const badge = document.getElementById('notif-badge');
                if (badge && badge.style.display !== 'none') {
                    const count = parseInt(badge.textContent) || 0;
                    if (count > 1) {
                        badge.textContent = count - 1;
                    } else {
                        badge.style.display = 'none';
                    }
                }
            }

            // Send API request (fire and forget with CSRF)
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch('/api/notifications/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': token },
                keepalive: true
            }).catch(() => {});
        }

        function clearAllNotifications() {
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }
            
            // Clear UI immediately
            const notifList = document.getElementById('notif-list');
            if (notifList) {
                notifList.innerHTML = '<div class="notif-empty"><i class="fas fa-bell-slash"></i><p>No notifications</p></div>';
            }
            document.getElementById('notif-badge').style.display = 'none';

            // Send API request (fire and forget with CSRF)
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch('/api/notifications', {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': token },
                keepalive: true
            }).catch(() => {});
        }

        document.addEventListener('DOMContentLoaded', () => {
            if ({{ auth()->check() ? 'true' : 'false' }}) {
                window.currentUserId = {{ auth()->id() }};
            }
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeAllDropdowns(); });
            
            // Auto-detect Arabic text and apply RTL direction
            applyRTLToArabicText();
        });
        
        /**
         * Detect Arabic/Persian/Hebrew text and apply RTL direction
         */
        function applyRTLToArabicText() {
            // Arabic Unicode range: \u0600-\u06FF, Arabic Supplement: \u0750-\u077F
            // Persian/Arabic Extended: \u08A0-\u08FF
            // Hebrew: \u0590-\u05FF
            const arabicPattern = /[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\u0590-\u05FF]/;
            
            // Apply to post content
            document.querySelectorAll('.post-content p, .comment-content p, .message-content .text').forEach(el => {
                const text = el.textContent || el.innerText || '';
                if (arabicPattern.test(text)) {
                    el.setAttribute('dir', 'rtl');
                    el.style.direction = 'rtl';
                    el.style.textAlign = 'right';
                }
            });
        }
    </script>
</body>
</html>
