<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Laravel Social')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="current-user-id" content="{{ auth()->id() }}">
    <script>
        window.currentUserId = {{ auth()->id() ?? 'null' }};
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Twitter/X Dark Mode colors - Enhanced */
        :root {
            --twitter-blue: #1D9BF0;
            --twitter-dark: #FFFFFF;
            --twitter-light: #0F1419;
            --twitter-gray: #71767B;
            --twitter-light-gray: #16181C;
            --border-color: #2F3336;
            --shadow: 0 4px 12px rgba(0,0,0,0.4);
            --hover-bg: #1C1F23;
            --divider: #2F3336;
            --card-bg: #16181C;
            --input-bg: #202327;
            --header-bg: #000000;
            --focus-border: #1D9BF0;
            --success-color: #00BA7C;
            --error-color: #F4212E;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: var(--twitter-light);
            color: var(--twitter-dark);
            margin: 0;
            padding: 0;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .header {
            background-color: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            padding: 12px 16px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow);
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo {
            font-size: 20px;
            font-weight: bold;
            color: var(--twitter-blue);
            text-decoration: none;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-name {
            font-size: 16px;
            font-weight: 600;
            color: var(--twitter-dark);
        }

        .admin-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .admin-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
            background: linear-gradient(135deg, #c82333, #bd2130);
        }

        .admin-link i {
            font-size: 10px;
        }

        .admin-link span {
            font-size: 11px;
        }

            .user-avatar-small {
                width: 32px;
                height: 32px;
                border-radius: 50%;
                border: 2px solid white;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            /* Lazy loading for images */
            img {
                loading: lazy;
            }

        /* Navigation - always show as dropdown on all screen sizes */
            .nav-links {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 100%;
                right: 0;
                background: var(--card-bg);
                border: 2px solid var(--border-color);
                border-radius: 20px;
                box-shadow: 0 8px 32px rgba(0,0,0,0.5), 0 4px 16px rgba(29, 161, 242, 0.1);
                min-width: 280px;
                z-index: 1000;
                padding: 20px 0;
                backdrop-filter: blur(10px);
                animation: dropdownSlideIn 0.2s ease-out;
                max-height: 80vh;
                overflow-y: auto;
                max-width: 90vw;
            }

            /* Optimized for wide laptops (1200px+) */
            @media (min-width: 1200px) {
                .nav-links {
                    min-width: 420px;
                    max-width: 500px;
                    padding: 32px 0;
                    right: 0;
                    transform: translateX(0);
                    box-shadow: 0 12px 40px rgba(0,0,0,0.7), 0 6px 20px rgba(29, 161, 242, 0.25);
                    border-radius: 16px;
                    position: absolute;
                    top: 100%;
                    left: auto;
                    z-index: 1000;
                }
            }

            /* Standard laptops (1025px-1199px) */
            @media (min-width: 1025px) and (max-width: 1199px) {
                .nav-links {
                    min-width: 280px;
                    max-width: 320px;
                    padding: 20px 0;
                    right: 0;
                    box-shadow: 0 10px 35px rgba(0,0,0,0.65), 0 5px 18px rgba(29, 161, 242, 0.22);
                    border-radius: 14px;
                }
            }

            /* Compact laptops (769px-1024px) */
            @media (min-width: 769px) and (max-width: 1024px) {
                .nav-links {
                    min-width: 260px;
                    max-width: 300px;
                    padding: 18px 0;
                    right: 0;
                    box-shadow: 0 8px 30px rgba(0,0,0,0.6), 0 4px 16px rgba(29, 161, 242, 0.2);
                    border-radius: 12px;
                }
            }

            .nav-links.mobile-show {
                display: flex;
            }

        @keyframes dropdownSlideIn {
            from {
                opacity: 0;
                transform: translateY(-10px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .nav-links.mobile-show {
            display: flex;
        }

        .nav-links a {
            padding: 16px 24px;
            border-radius: 0;
            text-align: left;
            white-space: nowrap;
            border-bottom: 1px solid var(--border-color);
            color: var(--twitter-dark);
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .nav-links a:last-child {
            border-bottom: none;
        }

        .nav-links a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--twitter-blue);
            border-radius: 0 2px 2px 0;
            transform: scaleY(0);
            transition: transform 0.2s ease;
            transform-origin: center;
        }

        .nav-links a:hover {
            background: linear-gradient(135deg, var(--hover-bg) 0%, rgba(29, 161, 242, 0.05) 100%);
            color: var(--twitter-blue);
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(29, 161, 242, 0.1);
        }

        .nav-links a:hover::before {
            transform: scaleY(1);
        }

        .nav-links a i {
            font-size: 16px;
            width: 20px;
            text-align: center;
            opacity: 0.8;
        }

        .nav-links a:hover i {
            opacity: 1;
            transform: scale(1.1);
        }

        /* Special styling for logout link */
        .nav-links a[href*="logout"] {
            color: var(--error-color);
            font-weight: 600;
        }

        .nav-links a[href*="logout"]:hover {
            background: linear-gradient(135deg, rgba(244, 33, 46, 0.1) 0%, rgba(244, 33, 46, 0.05) 100%);
            color: var(--error-color);
        }

        /* Navigation menu toggle button - always visible */
        .mobile-menu-toggle {
            display: flex !important;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            background: none;
            border: none;
            font-size: 20px;
            color: var(--twitter-gray);
            cursor: pointer;
            border-radius: 50%;
            transition: all 0.2s ease;
        }

        .mobile-menu-toggle:hover {
            background-color: var(--twitter-light);
            color: var(--twitter-dark);
        }

        .mobile-menu-toggle:active {
            transform: scale(0.95);
        }

        /* Twitter-like layout */
        .app-layout {
            display: grid;
            grid-template-columns: 275px 1fr 350px;
            gap: 30px;
            max-width: 1300px;
            margin: 0 auto;
            padding: 16px;
            min-height: calc(100vh - 60px);
        }

        /* Auth pages - full width centering */
        .auth-page .app-layout {
            display: block;
        }

        .auth-page .main-content {
            width: 100%;
            max-width: none;
            margin: 0;
        }

        .left-sidebar {
            position: sticky;
            top: 80px;
            height: fit-content;
        }

        .main-content {
            min-width: 0; /* Prevent flex shrinking */
        }

        .right-sidebar {
            position: sticky;
            top: 80px;
            height: fit-content;
        }

        /* Navigation sidebar */
        .nav-sidebar {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 12px 16px;
            border-radius: 24px;
            text-decoration: none;
            color: var(--twitter-dark);
            font-weight: 500;
            font-size: 18px;
            transition: all 0.2s ease;
            position: relative;
        }

        .nav-item:hover {
            background-color: var(--hover-bg);
            color: var(--twitter-blue);
        }

        .nav-item.active {
            background-color: var(--twitter-blue);
            color: white;
        }

        .nav-item i {
            font-size: 24px;
            width: 24px;
            text-align: center;
        }

        .compose-btn {
            background-color: var(--twitter-blue);
            color: white;
            border: none;
            padding: 16px 24px;
            border-radius: 24px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 16px;
            transition: all 0.2s ease;
        }

        .compose-btn:hover {
            background-color: #1A91DA;
            transform: translateY(-1px);
        }

        .user-profile-card {
            background-color: var(--twitter-light);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 16px;
            margin-top: 16px;
        }

        .user-profile-card .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-profile-card .user-avatar-small {
            width: 40px;
            height: 40px;
        }

        .user-profile-card .user-name {
            font-size: 14px;
            font-weight: 600;
        }

        .user-profile-card .user-handle {
            font-size: 14px;
            color: var(--twitter-gray);
        }

        /* Responsive design - Perfect centering for all screen sizes */
        @media (min-width: 1025px) {
            /* All laptops and desktops - perfect centering system */
            .app-layout {
                display: grid;
                grid-template-columns: 250px 1fr 250px;
                gap: 25px;
                max-width: 1400px;
                margin: 0 auto;
                padding: 25px;
                place-items: start;
            }

            .left-sidebar {
                width: 250px;
                grid-column: 1;
            }

            .main-content {
                width: 100%;
                max-width: none;
                grid-column: 2;
            }

            .right-sidebar {
                width: 250px;
                grid-column: 3;
            }
        }

        /* Ultra-wide monitors (1921px+) */
        @media (min-width: 1921px) {
            .app-layout {
                gap: 30px;
                padding: 30px;
            }

            .left-sidebar, .right-sidebar {
                width: 320px;
                max-width: 320px;
                min-width: 240px;
            }

            .main-content {
                max-width: 900px;
            }
        }

        /* Large monitors (1441px-1920px) */
        @media (max-width: 1920px) and (min-width: 1441px) {
            .app-layout {
                gap: 25px;
                padding: 25px;
            }

            .left-sidebar, .right-sidebar {
                width: 300px;
                max-width: 300px;
                min-width: 220px;
            }

            .main-content {
                max-width: 850px;
            }
        }

        /* Standard large laptops (1281px-1440px) */
        @media (max-width: 1440px) and (min-width: 1281px) {
            .left-sidebar, .right-sidebar {
                width: 260px;
                max-width: 260px;
                min-width: 200px;
            }

            .main-content {
                max-width: 800px;
            }
        }

        /* Standard laptops (1025px-1280px) */
        @media (max-width: 1280px) and (min-width: 1025px) {
            .left-sidebar, .right-sidebar {
                width: 240px;
                max-width: 240px;
                min-width: 180px;
            }

            .main-content {
                max-width: 750px;
                min-width: 450px;
            }
        }

        @media (max-width: 1024px) and (min-width: 769px) {
            /* Smaller laptops and tablets - full width centering */
            .app-layout {
                grid-template-columns: 60px 1fr 100px;
                gap: 20px;
                max-width: 95vw;
                margin: 0 auto;
                padding: 16px;
            }

            .left-sidebar {
                width: 60px;
            }

            .right-sidebar {
                width: 100px;
            }
        }

        @media (max-width: 1024px) {
            /* Standard tablets and smaller laptops */
            .app-layout {
                grid-template-columns: 1fr;
                gap: 0;
                max-width: 900px;
                padding: 16px;
            }

            .left-sidebar {
                display: none;
            }

            .right-sidebar {
                display: none;
            }

            .main-content {
                max-width: none;
                margin: 0;
            }
        }

        @media (max-width: 900px) {
            .app-layout {
                grid-template-columns: 1fr;
                gap: 0;
                padding: 12px;
            }

            .main-content {
                max-width: 600px;
                margin: 0 auto;
            }
        }

        /* Mobile specific improvements */
        @media (max-width: 768px) {
            .header {
                padding: 8px 16px;
            }

            .nav {
                max-width: none;
            }

            .logo {
                font-size: 18px;
            }

            .user-info {
                gap: 8px;
            }

            .user-name {
                display: block; /* Show username on mobile screens */
            }

            /* Admin link moved to mobile menu */

            .app-layout {
                padding: 8px;
            }

            .main-content {
                max-width: none;
                margin: 0;
            }

            /* Improve post spacing on mobile */
            .post {
                margin-bottom: 8px;
                border-radius: 12px;
                padding: 12px;
            }

            /* Better button sizing */
            .btn {
                padding: 6px 12px;
                font-size: 13px;
                min-height: 32px;
            }

            /* Improve form inputs */
            .form-group input,
            .form-group textarea {
                padding: 10px 12px;
                font-size: 16px; /* Prevents zoom on iOS */
            }

            /* Better navigation on mobile */
            .nav-sidebar {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                top: auto;
                background: white;
                border-top: 1px solid var(--border-color);
                box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
                z-index: 1000;
                padding: 8px 0;
                height: auto;
                display: flex;
                flex-direction: row;
                gap: 0;
                justify-content: space-around;
            }

            .nav-item {
                flex: 1;
                flex-direction: column;
                gap: 4px;
                padding: 8px 4px;
                min-height: 60px;
                font-size: 10px;
                border-radius: 8px;
                margin: 0 2px;
            }

            .nav-item span {
                font-size: 10px;
            }

            .nav-item i {
                font-size: 20px;
            }

            .compose-btn {
                display: none; /* Hide compose button on mobile bottom nav */
            }

            .user-profile-card {
                display: none; /* Hide user profile card on mobile */
            }

            /* Mobile post form improvements */
            .post-form-container {
                background: var(--twitter-light);
                border: 1px solid var(--border-color);
                border-radius: 12px;
                padding: 12px;
                margin-bottom: 16px;
            }

            .post-form-container .form-group textarea {
                min-height: 60px;
            }

            /* Add bottom padding for mobile navigation */
            .main-content {
                padding-bottom: 80px; /* Space for bottom nav */
            }



            /* Mobile story improvements */
            .stories-container {
                padding: 8px 0;
                gap: 8px;
            }

            .story-item {
                width: 56px;
            }

            .story-avatar {
                width: 56px;
                height: 56px;
            }

            .story-preview {
                width: 48px;
                height: 48px;
                top: 4px;
                left: 4px;
            }
        }

        /* Very small mobile screens */
        @media (max-width: 480px) {
            .header {
                padding: 6px 12px;
            }

            .logo {
                font-size: 16px;
            }

            .user-avatar-small {
                width: 28px;
                height: 28px;
            }

            .mobile-menu-toggle {
                width: 36px;
                height: 36px;
                font-size: 16px;
            }

            .app-layout {
                padding: 4px;
            }

            .post {
                padding: 8px;
                margin-bottom: 6px;
            }

            .btn {
                padding: 5px 10px;
                font-size: 12px;
                min-height: 30px;
            }

            .nav-item {
                padding: 6px 2px;
                min-height: 50px;
            }

            .nav-item i {
                font-size: 18px;
            }

            .nav-item span {
                font-size: 9px;
            }
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 16px;
            min-height: calc(100vh - 60px);
        }
        .post {
            background-color: white;
            border: 1px solid #E1E8ED;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
        }
        .post .user {
            font-weight: bold;
            color: var(--twitter-dark);
        }
        .post .content {
            margin: 10px 0;
        }

        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .user-profile {
            text-align: center;
            margin-bottom: 20px;
        }

        .user-profile .bio {
            font-style: italic;
            color: #666;
            margin: 10px 0;
        }

        .users-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .user-card {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 1px solid #E1E8ED;
            border-radius: 10px;
            background-color: white;
        }

        .user-avatar, .user-avatar-placeholder {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .user-avatar-placeholder {
            background-color: #E1E8ED;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #657786;
            font-size: 24px;
        }

        .user-info h3 {
            margin: 0 0 5px 0;
            font-size: 16px;
        }

        .user-info h3 a {
            color: var(--twitter-dark);
            text-decoration: none;
        }

        .user-info h3 a:hover {
            color: var(--twitter-blue);
        }

        .user-bio {
            margin: 5px 0;
            color: #657786;
            font-size: 14px;
        }

        .user-stats {
            display: flex;
            gap: 15px;
            font-size: 12px;
            color: #657786;
            margin-top: 5px;
        }

        .following-btn {
            background-color: #28a745 !important;
        }

        .follow-btn {
            background-color: var(--twitter-blue) !important;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #657786;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 20px;
            display: block;
        }

        .empty-state h3 {
            margin: 0 0 10px 0;
            color: var(--twitter-dark);
        }

        .back-link {
            margin-bottom: 20px;
        }

        .back-link a {
            color: var(--twitter-blue);
            text-decoration: none;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .privacy-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            margin-left: 8px;
            vertical-align: middle;
        }

        .privacy-badge.private {
            background-color: #dc3545;
            color: white;
            border: 1px solid #dc3545;
        }

        .privacy-badge.public {
            background-color: var(--twitter-blue);
            color: white;
            border: 1px solid var(--twitter-blue);
        }

        .privacy-badge i {
            font-size: 10px;
        }

        .block-indicator {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 8px;
            vertical-align: middle;
            border: 1px solid;
        }

        .block-indicator.blocked-by-you {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        .block-indicator.blocked-you {
            background-color: #fff3cd;
            color: #856404;
            border-color: #ffeaa7;
        }

        .block-indicator i {
            font-size: 9px;
        }

        .unblock-btn {
            background-color: #6c757d !important;
        }

        .text-muted {
            color: #6c757d;
        }

        .block-info {
            margin-top: 5px;
        }
        .btn {
            background-color: var(--twitter-blue);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background-color: #1991DB;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #E1E8ED;
            border-radius: 5px;
        }
        /* Mobile Navigation Menu */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 20px;
            color: var(--twitter-gray);
            cursor: pointer;
            padding: 10px;
            border-radius: 50%;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            position: relative;
            z-index: 1001;
        }

        .mobile-menu-toggle:hover {
            background-color: var(--twitter-light);
            color: var(--twitter-dark);
        }

        .mobile-menu-toggle:active {
            background-color: rgba(29, 161, 242, 0.1);
            transform: scale(0.95);
        }

        .nav-links.mobile-hidden {
            display: none;
        }

        /* Mobile menu overlay */
        .mobile-menu-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .mobile-menu-overlay.active {
            display: block;
        }

        /* Post Styles */
        .post {
            background-color: white;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 12px;
            box-shadow: var(--shadow);
            transition: all 0.2s ease;
        }

        .post:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .post .user {
            font-weight: 600;
            color: var(--twitter-dark);
            margin-bottom: 8px;
            font-size: 15px;
        }

        .post .content {
            margin: 12px 0;
            line-height: 1.6;
            font-size: 15px;
        }

        .post .user small {
            color: var(--twitter-gray);
            font-weight: 400;
            margin-left: 8px;
        }

        /* Button Styles */
        .btn {
            background-color: var(--twitter-blue);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            min-height: 36px; /* Touch target */
        }

        .btn:hover {
            background-color: #1991DB;
            transform: translateY(-1px);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn i {
            margin-right: 6px;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--twitter-dark);
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid var(--border-color);
            border-radius: 16px;
            font-size: 16px; /* Prevents zoom on iOS */
            font-family: inherit;
            background-color: var(--input-bg);
            color: var(--twitter-dark);
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--focus-border);
            background-color: var(--card-bg);
            box-shadow: 0 0 0 4px rgba(29, 161, 242, 0.15), 0 4px 16px rgba(29, 161, 242, 0.1);
            transform: translateY(-1px);
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: var(--twitter-gray);
            opacity: 0.7;
        }

        .form-group textarea {
            min-height: 80px;
            resize: vertical;
            line-height: 1.4;
        }

        /* Grid Layouts */
        .users-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }

        .user-card {
            display: flex;
            align-items: flex-start;
            padding: 16px;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            background-color: white;
            box-shadow: var(--shadow);
            transition: all 0.2s ease;
        }

        .user-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }

        /* Media Display */
        .post-media {
            margin: 12px 0;
            border-radius: 12px;
            overflow: hidden;
            background: #000;
            position: relative;
        }

        .post-media img {
            width: 100%;
            height: auto;
            display: block;
            border-radius: 12px;
        }

        /* Video Styling - Full Coverage */
        .post-media video {
            width: 100% !important;
            height: auto;
            max-width: 100%;
            display: block;
            border-radius: 12px;
            background: #000;
            object-fit: cover;
        }

        /* Video container responsive - Full Coverage */
        .video-container {
            width: 100%;
            position: relative;
            background: #000;
            border-radius: 12px;
            overflow: hidden;
        }

        .video-container video {
            width: 100% !important;
            height: auto !important;
            max-width: 100% !important;
            display: block;
            border-radius: 12px;
            background: #000;
            object-fit: cover;
        }

        /* Custom Video Controls */
        .video-container {
            position: relative;
            background: #000;
            border-radius: 12px;
            overflow: hidden;
        }

        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.3);
            opacity: 1;
            transition: opacity 0.3s ease;
            cursor: pointer;
            z-index: 2;
        }

        .video-container:hover .video-overlay {
            opacity: 0;
        }

        .play-button {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .play-button:hover {
            transform: scale(1.1);
            background: rgba(255, 255, 255, 1);
        }

        .play-button i {
            color: #000;
            font-size: 20px;
            margin-left: 3px; /* Center the play icon */
        }

        .video-container video:focus + .video-overlay,
        .video-container.playing .video-overlay {
            opacity: 0;
        }



        /* Loading State */
        .media-loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 16px;
            z-index: 3;
        }

        .media-loading i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Media Grids */
        .media-grid {
            display: grid;
            gap: 4px;
            border-radius: 12px;
            overflow: hidden;
        }

        .media-item {
            position: relative;
            background: #000;
            overflow: hidden;
        }

        .media-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.3s ease;
        }

        .media-item:hover img {
            transform: scale(1.05);
        }

        .media-item video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* Video controls styling */
        .media-item video::-webkit-media-controls-panel {
            background: rgba(0, 0, 0, 0.8);
        }

        .media-item video::-webkit-media-controls {
            background: rgba(0, 0, 0, 0.8);
        }

        /* Profile Styles */
        .user-profile {
            text-align: center;
            margin-bottom: 24px;
        }

        .avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin-bottom: 16px;
            border: 4px solid white;
            box-shadow: var(--shadow);
        }

        .cover-image {
            position: relative;
            height: 200px;
            overflow: hidden;
            border-radius: 0 0 16px 16px;
            margin-bottom: 20px;
        }

        .cover-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-info h2 {
            font-size: 24px;
            margin-bottom: 8px;
            word-break: break-word;
        }

        .bio {
            font-style: normal;
            color: var(--twitter-gray);
            margin: 12px 0;
            font-size: 15px;
            line-height: 1.5;
        }

        /* Stats */
        .stats {
            margin: 16px 0;
            padding: 16px;
            background-color: var(--twitter-light);
            border-radius: 12px;
        }

        .stats a {
            color: var(--twitter-gray);
            text-decoration: none;
            margin-right: 16px;
            font-size: 14px;
        }

        .stats a:hover {
            color: var(--twitter-blue);
        }

        /* Ultra-Responsive Design System */

        /* Extra Large Screens (1440px+) */
        @media (min-width: 1440px) {
            .container {
                max-width: 800px;
            }

            .users-grid {
                grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            }

            .logo {
                font-size: 24px;
            }

            .nav-links a {
                font-size: 16px;
                padding: 10px 16px;
            }
        }

        /* Large Screens (1200px - 1439px) */
        @media (min-width: 1200px) and (max-width: 1439px) {
            .container {
                max-width: 700px;
            }

            .users-grid {
                grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            }
        }

        /* Medium-Large Screens (992px - 1199px) */
        @media (min-width: 992px) and (max-width: 1199px) {
            .container {
                max-width: 650px;
            }

            .users-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            }

            .logo {
                font-size: 22px;
            }
        }

        /* Medium Screens (768px - 991px) */
        @media (min-width: 768px) and (max-width: 991px) {
            .container {
                max-width: 90%;
                padding: 20px 16px;
            }

            .users-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 14px;
            }

            .post {
                padding: 18px;
            }

            .user-card {
                padding: 18px;
            }

            .avatar {
                width: 110px;
                height: 110px;
            }

            .cover-image {
                height: 180px;
            }

            .profile-info h2 {
                font-size: 22px;
            }

            .media-grid {
                grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            }
        }

        /* Small-Medium Screens (600px - 767px) */
        @media (min-width: 600px) and (max-width: 767px) {
            .container {
                padding: 16px 12px;
            }

            .users-grid {
                grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            }

            .post {
                padding: 16px;
                margin-bottom: 12px;
            }

            .user-card {
                padding: 16px;
            }

            .avatar {
                width: 100px;
                height: 100px;
            }

            .cover-image {
                height: 160px;
            }

            .profile-info h2 {
                font-size: 20px;
            }

            .media-grid {
                grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            }
        }

        /* Mobile Large (480px - 599px) */
        @media (min-width: 480px) and (max-width: 599px) {
            .container {
                padding: 12px 10px;
            }

            .header {
                padding: 10px 14px;
            }

            .logo {
                font-size: 16px;
            }

            .nav-links.mobile-show {
                padding: 14px;
            }

            .users-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .post {
                padding: 14px;
                margin-bottom: 10px;
                border-radius: 12px;
            }

            .user-card {
                padding: 14px;
                flex-direction: column;
                text-align: center;
            }

            .user-avatar,
            .user-avatar-placeholder {
                margin-right: 0;
                margin-bottom: 10px;
                width: 55px;
                height: 55px;
                align-self: center;
            }

            .avatar {
                width: 90px;
                height: 90px;
            }

            .cover-image {
                height: 140px;
            }

            .profile-info h2 {
                font-size: 19px;
            }

            .stats {
                padding: 14px;
            }

            .media-grid {
                grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
            }

            .btn {
                padding: 9px 14px;
                font-size: 13px;
                min-height: 38px;
            }
        }

        /* Mobile Small (360px - 479px) */
        @media (min-width: 360px) and (max-width: 479px) {
            .container {
                padding: 8px 6px;
            }

            .header {
                padding: 8px 10px;
            }

            .logo {
                font-size: 15px;
            }

            .nav-links.mobile-show {
                padding: 12px;
                gap: 10px;
            }

            .nav-links.mobile-show a {
                padding: 10px 8px;
                font-size: 14px;
            }

            .users-grid {
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .post {
                padding: 12px;
                margin-bottom: 8px;
                border-radius: 10px;
            }

            .user-card {
                padding: 12px;
            }

            .user-avatar,
            .user-avatar-placeholder {
                width: 50px;
                height: 50px;
                margin-bottom: 8px;
            }

            .avatar {
                width: 80px;
                height: 80px;
            }

            .cover-image {
                height: 120px;
            }

            .profile-info h2 {
                font-size: 17px;
            }

            .stats {
                padding: 12px;
            }

            .stats a {
                margin-bottom: 6px;
            }

            .media-grid {
                grid-template-columns: repeat(auto-fit, minmax(95px, 1fr));
            }

            .btn {
                padding: 8px 12px;
                font-size: 13px;
                min-height: 36px;
            }

            .form-group input,
            .form-group textarea {
                padding: 12px 14px;
            }
        }

        /* Mobile Extra Small (320px - 359px) */
        @media (max-width: 359px) {
            .container {
                padding: 6px 4px;
            }

            .header {
                padding: 6px 8px;
            }

            .logo {
                font-size: 14px;
            }

            .nav-links.mobile-show {
                padding: 10px;
            }

            .nav-links.mobile-show a {
                padding: 8px 6px;
                font-size: 13px;
            }

            .users-grid {
                gap: 6px;
            }

            .post {
                padding: 10px;
                margin-bottom: 6px;
            }

            .user-card {
                padding: 10px;
            }

            .user-avatar,
            .user-avatar-placeholder {
                width: 45px;
                height: 45px;
            }

            .avatar {
                width: 70px;
                height: 70px;
            }

            .cover-image {
                height: 100px;
            }

            .profile-info h2 {
                font-size: 16px;
            }

            .stats {
                padding: 10px;
            }

            .media-grid {
                grid-template-columns: repeat(auto-fit, minmax(85px, 1fr));
            }

            .btn {
                padding: 7px 10px;
                font-size: 12px;
                min-height: 34px;
            }

            .form-group input,
            .form-group textarea {
                padding: 10px 12px;
                font-size: 16px;
            }

            .form-group label {
                font-size: 13px;
                margin-bottom: 6px;
            }
        }

        /* Landscape Orientation Adjustments */
        @media (max-height: 500px) and (orientation: landscape) {
            .header {
                padding: 6px 12px;
            }

            .logo {
                font-size: 16px;
            }

            .nav-links.mobile-show {
                padding: 8px 12px;
                max-height: 40vh;
                overflow-y: auto;
            }

            .container {
                padding: 8px 6px;
            }

            .post {
                padding: 10px;
            }

            .user-card {
                padding: 10px;
            }

            .avatar {
                width: 60px;
                height: 60px;
            }

            .cover-image {
                height: 80px;
            }
        }

        /* Ultra-wide aspect ratios */
        @media (min-aspect-ratio: 21/9) {
            .container {
                max-width: 900px;
            }

            .users-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            }
        }

        /* Square screens (like some tablets) */
        @media (aspect-ratio: 1/1) {
            .avatar {
                width: 100px;
                height: 100px;
            }

            .cover-image {
                height: 150px;
            }

            .users-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Touch-friendly interactions */
        @media (hover: none) and (pointer: coarse) {
            .btn:hover {
                transform: none;
            }

            .user-card:hover,
            .post:hover {
                transform: none;
                box-shadow: var(--shadow);
            }
        }

        /* High contrast mode support */
        @media (prefers-contrast: high) {
            :root {
                --border-color: #000;
                --twitter-gray: #333;
            }
        }


        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            * {
                transition: none !important;
                animation: none !important;
            }
        }

        /* Ultra-Responsive Comment System for All Mobile Devices */

        /* Base mobile comment styles */
        .comment {
            margin-bottom: 12px;
            padding: 10px;
            border-radius: 8px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .comment-avatar {
            width: 32px;
            height: 32px;
            flex-shrink: 0;
        }

        .comment-user-avatar,
        .comment-user-avatar-placeholder {
            width: 32px;
            height: 32px;
            font-size: 14px;
            border-radius: 50%;
        }

        .comment-user-avatar-placeholder {
            background-color: #E1E8ED;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #657786;
        }

        .comment-content-wrapper {
            margin-left: 10px;
            flex: 1;
            min-width: 0; /* Allow flex shrinking */
        }

        .comment-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 6px;
            flex-wrap: wrap;
            gap: 4px;
        }

        .comment-user-info {
            display: flex;
            align-items: center;
            flex: 1;
            min-width: 0;
            overflow: hidden;
        }

        .comment-user-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--twitter-dark);
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: calc(100vw - 120px);
            flex-shrink: 1;
        }

        .comment-time {
            display: inline;
            font-size: 11px;
            color: var(--twitter-gray);
            margin-left: 4px;
            opacity: 0.8;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .comment-actions {
            flex-shrink: 0;
            margin-left: 8px;
        }

        .comment-text {
            font-size: 14px;
            line-height: 1.4;
            margin-top: 4px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
        }

        .comment-interactions {
            display: flex;
            gap: 12px;
            margin-top: 8px;
            flex-wrap: wrap;
        }

        .comment-like-btn,
        .comment-reply-btn {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 16px;
            border: none;
            background: none;
            color: var(--twitter-gray);
            cursor: pointer;
            transition: all 0.2s ease;
            min-height: 28px;
        }

        .comment-like-btn:hover,
        .comment-reply-btn:hover {
            background: var(--hover-bg);
        }

        .comment-like-btn.liked {
            color: var(--error-color);
            background: rgba(244, 33, 46, 0.1);
        }

        .comment-like-btn .comment-like-count {
            font-weight: 600;
            min-width: 8px;
        }

        /* Nested comments with progressive indentation */
        .nested-comment {
            margin-left: 16px;
            border-left: 2px solid var(--border-color);
            padding-left: 10px;
            margin-bottom: 10px;
            position: relative;
        }

        /* Level 3 nested comments - minimal indentation */
        .level-3,
        .nested-comment .nested-comment {
            margin-left: 12px;
            border-left-width: 1px;
            padding-left: 8px;
            opacity: 0.9;
        }

        /* Reply forms optimized for mobile */
        .comment-reply-form {
            margin-top: 10px;
            padding: 10px;
            background: var(--hover-bg);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .reply-form-container {
            display: flex;
            gap: 8px;
            align-items: flex-start;
        }

        .reply-avatar {
            width: 28px;
            height: 28px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .reply-avatar img,
        .reply-avatar-placeholder {
            width: 28px;
            height: 28px;
            font-size: 12px;
            border-radius: 50%;
        }

        .reply-input-container {
            flex: 1;
            min-width: 0;
        }

        .reply-textarea {
            width: 100%;
            min-height: 60px;
            padding: 8px 10px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            resize: vertical;
            box-sizing: border-box;
        }

        .reply-actions {
            display: flex;
            gap: 8px;
            margin-top: 8px;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .reply-submit-btn,
        .reply-cancel-btn {
            padding: 6px 12px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
            min-height: 32px;
        }

        .reply-submit-btn {
            background: var(--twitter-blue);
            color: white;
        }

        .reply-submit-btn:hover {
            background: #1991DB;
            transform: translateY(-1px);
        }

        .reply-cancel-btn {
            background: var(--hover-bg);
            color: var(--twitter-gray);
            border: 1px solid var(--border-color);
        }

        .reply-cancel-btn:hover {
            background: var(--border-color);
            color: var(--twitter-dark);
        }

        /* Nested replies management */
        .comment-replies {
            margin-top: 8px;
        }

        .show-more-nested-replies-btn,
        .hide-nested-replies-btn {
            background: none;
            border: none;
            color: var(--twitter-blue);
            font-size: 12px;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 12px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .show-more-nested-replies-btn:hover,
        .hide-nested-replies-btn:hover {
            background: rgba(29, 161, 242, 0.1);
            transform: translateY(-1px);
        }

        /* Comment delete button */
        .comment-delete-btn {
            background: none;
            border: none;
            color: var(--twitter-gray);
            font-size: 12px;
            cursor: pointer;
            padding: 4px;
            border-radius: 50%;
            transition: all 0.2s ease;
            min-width: 24px;
            min-height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .comment-delete-btn:hover {
            background: rgba(244, 33, 46, 0.1);
            color: var(--error-color);
            transform: scale(1.1);
        }

        /* Mobile-optimized notification system */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--success-color);
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            z-index: 10000;
            font-size: 14px;
            max-width: 300px;
            word-wrap: break-word;
            animation: notificationSlideIn 0.3s ease-out;
        }

        @keyframes notificationSlideIn {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .notification.show {
            animation: notificationFadeIn 0.3s ease-out;
        }

        @keyframes notificationFadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Improved mobile and small laptop notification system */
        @media (max-width: 1024px) {
            .notification {
                top: 10px;
                left: 10px;
                right: 10px;
                width: calc(100vw - 20px);
                max-width: none;
                padding: 14px 16px;
                border-radius: 8px;
                font-size: 15px;
                font-weight: 600;
                text-align: center;
                line-height: 1.4;
                box-shadow: 0 4px 16px rgba(0,0,0,0.2);
                z-index: 10001;
            }

            /* Make mobile notifications more prominent but not overwhelming */
            .notification.show {
                animation: mobileNotificationSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            }

            @keyframes mobileNotificationSlideIn {
                from {
                    opacity: 0;
                    transform: translateY(-20px) scale(0.95);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            /* Adjust icon size for mobile */
            .notification i {
                font-size: 18px !important;
            }
        }

        /* Extra small mobile devices */
        @media (max-width: 480px) {
            .notification {
                top: 8px;
                left: 8px;
                right: 8px;
                width: calc(100vw - 16px);
                padding: 12px 14px;
                font-size: 14px;
                border-radius: 6px;
            }

            .notification i {
                font-size: 16px !important;
            }
        }

        /* Extra large tablets (769px - 1200px) */
        @media (min-width: 769px) and (max-width: 1200px) {
            .notification {
                padding: 14px 18px;
                font-size: 15px;
            }
        }

        /* Standard tablets (601px - 768px) */
        @media (min-width: 601px) and (max-width: 768px) {
            .notification {
                padding: 12px 16px;
                font-size: 14px;
            }
        }

        /* Large phones (481px - 600px) */
        @media (min-width: 481px) and (max-width: 600px) {
            .notification {
                padding: 11px 15px;
                font-size: 13px;
            }
        }

        /* Medium phones (414px - 480px) */
        @media (min-width: 414px) and (max-width: 480px) {
            .notification {
                padding: 10px 14px;
                font-size: 13px;
            }
        }

        /* Small phones (376px - 413px) */
        @media (min-width: 376px) and (max-width: 413px) {
            .notification {
                padding: 9px 13px;
                font-size: 12px;
            }
        }

        /* Extra small phones (361px - 375px) */
        @media (min-width: 361px) and (max-width: 375px) {
            .notification {
                padding: 9px 12px;
                font-size: 12px;
            }
        }

        /* iPhone SE and similar (321px - 360px) */
        @media (min-width: 321px) and (max-width: 360px) {
            .notification {
                padding: 8px 11px;
                font-size: 11px;
                line-height: 1.3;
            }
        }

        /* Very small phones (up to 320px) */
        @media (max-width: 320px) {
            .notification {
                padding: 7px 10px;
                font-size: 10px;
                line-height: 1.3;
            }
        }

        /* Ultra-wide aspect ratios - cinema screens */
        @media (min-aspect-ratio: 21/9) {
            .notification {
                bottom: 35px;
                max-width: calc(100vw - 80px);
                padding: 16px 20px;
                font-size: 15px;
            }
        }

        /* Square screens - some tablets */
        @media (aspect-ratio: 1/1) and (max-width: 768px) {
            .notification {
                bottom: 25px;
                max-width: calc(100vw - 50px);
                padding: 12px 16px;
            }
        }

        /* Very tall screens - phones in portrait */
        @media (max-aspect-ratio: 9/16) and (max-width: 480px) {
            .notification {
                bottom: 18px;
            }
        }

        /* Landscape orientation - phones and small tablets */
        @media (orientation: landscape) and (max-height: 500px) {
            .notification {
                bottom: 8px;
                max-width: calc(100vw - 60px);
                padding: 6px 10px;
                font-size: 11px;
                border-radius: 8px;
                line-height: 1.2;
            }
        }

        /* Landscape orientation - tablets */
        @media (orientation: landscape) and (min-height: 501px) and (max-width: 1024px) {
            .notification {
                bottom: 15px;
                max-width: calc(100vw - 70px);
                padding: 10px 14px;
                font-size: 13px;
            }
        }

        /* Touch-friendly interaction areas - all mobile devices */
        @media (hover: none) and (pointer: coarse) {
            .notification {
                min-height: 44px; /* iOS Human Interface Guidelines */
                padding: 12px 16px;
                font-size: max(12px, 3.5vw); /* Fluid font sizing */
            }
        }

        /* High contrast mode support */
        @media (prefers-contrast: high) {
            .notification {
                border: 2px solid white;
                box-shadow: 0 4px 16px rgba(0,0,0,0.9);
                font-weight: 600;
            }
        }

        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            .notification {
                animation: none !important;
            }
        }

        /* Dark mode adjustments */
        @media (prefers-color-scheme: dark) {
            .notification {
                background: var(--success-color);
                color: white;
            }
        }

        /* Print styles - hide notifications */
        @media print {
            .notification {
                display: none !important;
            }
        }

        /* Enhanced mobile breakpoints */

        /* Very small phones (320px - 360px) */
        @media (max-width: 360px) {
            .comment {
                padding: 8px;
                margin-bottom: 10px;
            }

            .comment-avatar {
                width: 28px;
                height: 28px;
            }

            .comment-user-avatar,
            .comment-user-avatar-placeholder {
                width: 28px;
                height: 28px;
                font-size: 12px;
            }

            .comment-content-wrapper {
                margin-left: 8px;
            }

            .comment-user-name {
                font-size: 12px;
                max-width: calc(100vw - 140px); /* Increased space for delete button */
            }

            .comment-time {
                font-size: 10px;
                margin-left: 2px;
            }

            .comment-actions {
                margin-left: 4px; /* Ensure delete button has space */
            }

            .comment-delete-btn {
                width: 20px; /* Smaller but still visible */
                height: 20px;
                font-size: 10px;
                min-width: 20px;
                min-height: 20px;
            }

            .comment-text {
                font-size: 13px;
            }

            .comment-interactions {
                gap: 8px;
                margin-top: 6px;
            }

            .comment-like-btn,
            .comment-reply-btn {
                font-size: 11px;
                padding: 3px 6px;
                min-height: 24px;
            }

            .nested-comment {
                margin-left: 12px;
                padding-left: 8px;
            }

            .level-3,
            .nested-comment .nested-comment {
                margin-left: 8px;
                padding-left: 6px;
            }

            .reply-avatar {
                width: 24px;
                height: 24px;
            }

            .reply-avatar img,
            .reply-avatar-placeholder {
                width: 24px;
                height: 24px;
                font-size: 10px;
            }

            .reply-textarea {
                font-size: 13px;
                min-height: 50px;
            }

            .reply-submit-btn,
            .reply-cancel-btn {
                font-size: 11px;
                padding: 5px 10px;
                min-height: 28px;
            }
        }

        /* Small phones (361px - 480px) */
        @media (min-width: 361px) and (max-width: 480px) {
            .comment-user-name {
                max-width: calc(100vw - 140px);
            }

            .comment-text {
                font-size: 13px;
            }
        }

        /* Larger mobile devices (481px - 768px) */
        @media (min-width: 481px) and (max-width: 768px) {
            .comment {
                padding: 12px;
                margin-bottom: 14px;
            }

            .comment-avatar {
                width: 36px;
                height: 36px;
            }

            .comment-user-avatar,
            .comment-user-avatar-placeholder {
                width: 36px;
                height: 36px;
                font-size: 16px;
            }

            .comment-user-name {
                font-size: 14px;
                max-width: calc(100vw - 180px);
            }

            .comment-time {
                font-size: 12px;
            }

            .comment-text {
                font-size: 15px;
            }

            .nested-comment {
                margin-left: 20px;
                padding-left: 12px;
            }

            .level-3,
            .nested-comment .nested-comment {
                margin-left: 16px;
                padding-left: 10px;
            }
        }

        /* Header notification button */
        .header-notification-btn {
            position: relative;
            background: none;
            border: none;
            padding: 10px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--twitter-dark);
            font-size: 18px;
            width: 44px;
            height: 44px;
            margin: 0 4px;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .header-notification-btn:hover {
            background: rgba(29, 161, 242, 0.1);
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(29, 161, 242, 0.2);
        }

        .header-notification-btn:active {
            transform: scale(0.95);
        }

        .header-notification-btn i {
            transition: all 0.3s ease;
        }

        .header-notification-btn:hover i {
            transform: rotate(15deg);
        }

        .notification-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            background: linear-gradient(135deg, #ff4757 0%, #ff3838 100%);
            color: white;
            border-radius: 50%;
            font-size: 10px;
            font-weight: 700;
            padding: 3px 7px;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid var(--card-bg);
            box-shadow: 0 2px 8px rgba(255, 71, 87, 0.4);
            animation: badgePulse 2s ease-in-out infinite;
            z-index: 10;
            letter-spacing: -0.5px;
        }

        @keyframes badgePulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 2px 8px rgba(255, 71, 87, 0.4);
            }
            50% {
                transform: scale(1.1);
                box-shadow: 0 4px 12px rgba(255, 71, 87, 0.6);
            }
        }

        .notification-badge.pulse {
            animation: badgePulse 0.6s ease-in-out;
        }

        /* Dark mode adjustments */
        @media (prefers-color-scheme: dark) {
            .header-notification-btn {
                color: #e0e0e0;
            }

            .header-notification-btn:hover {
                background: rgba(29, 161, 242, 0.15);
            }
        }

        /* Mobile responsive adjustments */
        @media (max-width: 768px) {
            .header-notification-btn {
                padding: 8px;
                width: 40px;
                height: 40px;
                font-size: 16px;
            }

            .notification-badge {
                top: 1px;
                right: 1px;
                font-size: 9px;
                padding: 2px 6px;
                min-width: 16px;
                height: 16px;
                border-width: 2px;
            }
        }

        /* Lightweight Notifications dropdown - optimized for performance */
        .notifications-dropdown-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 10000;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding-top: 60px;
        }

        .notifications-dropdown-content {
            background: rgba(30, 30, 30, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 380px;
            max-height: 70vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: quickFadeIn 0.15s ease-out;
            position: relative;
        }

        @keyframes quickFadeIn {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .notifications-dropdown-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: linear-gradient(135deg, rgba(30, 30, 30, 0.95) 0%, rgba(25, 25, 25, 0.95) 100%);
        }

        .notifications-dropdown-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #ffffff;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.8);
        }

        .notifications-dropdown-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .notifications-dropdown-delete-all {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(244, 33, 46, 0.2);
            border: 1px solid rgba(244, 33, 46, 0.3);
            color: #ff6b6b;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .notifications-dropdown-delete-all:hover {
            background: rgba(244, 33, 46, 0.4);
            border-color: rgba(244, 33, 46, 0.6);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(244, 33, 46, 0.3);
        }

        .notifications-dropdown-delete-all i {
            font-size: 11px;
        }

        .notifications-dropdown-close {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 16px;
            color: #cccccc;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: all 0.2s ease;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .notifications-dropdown-close:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.4);
            color: #ffffff;
            transform: scale(1.05);
        }

        .notifications-dropdown-body {
            flex: 1;
            overflow-y: auto;
            max-height: calc(70vh - 80px);
        }

        .notifications-loading,
        .notifications-empty,
        .notifications-error {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            text-align: center;
            color: var(--twitter-gray);
        }

        .notifications-loading p,
        .notifications-empty p,
        .notifications-error p {
            margin: 16px 0 0 0;
            font-size: 14px;
        }

        .notifications-empty small {
            font-size: 12px;
            color: var(--twitter-gray);
            opacity: 0.8;
            margin-top: 4px;
        }

        .notifications-list {
            display: flex;
            flex-direction: column;
        }

        .notification-dropdown-item {
            border-bottom: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }

        .notification-dropdown-item:hover {
            background: var(--hover-bg);
        }

        .notification-dropdown-item:last-child {
            border-bottom: none;
        }

        .notification-dropdown-item.unread {
            background: linear-gradient(135deg, rgba(29, 161, 242, 0.03) 0%, rgba(29, 161, 242, 0.01) 100%);
            border-left: 3px solid var(--twitter-blue);
        }

        .notification-dropdown-content {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 16px;
        }

        .notification-dropdown-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .notification-dropdown-text {
            flex: 1;
            min-width: 0;
        }

        .notification-dropdown-message {
            font-size: 14px;
            line-height: 1.4;
            color: #e0e0e0;
            margin-bottom: 2px;
            word-wrap: break-word;
        }

        .notification-dropdown-time {
            font-size: 11px;
            color: #a0a0a0;
        }

        .notification-dropdown-actions {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .btn-mark-read,
        .btn-delete {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            transition: all 0.2s ease;
        }

        .btn-mark-read {
            background: var(--twitter-blue);
            color: white;
        }

        .btn-mark-read:hover {
            background: #1991DB;
            transform: scale(1.1);
        }

        .btn-delete {
            background: rgba(244, 33, 46, 0.1);
            color: var(--error-color);
        }

        .btn-delete:hover {
            background: var(--error-color);
            color: white;
            transform: scale(1.1);
        }

        /* Mobile responsiveness for notifications dropdown */
        @media (max-width: 480px) {
            .notifications-dropdown-overlay {
                padding-top: 50px;
            }

            .notifications-dropdown-content {
                width: 95%;
                max-height: 75vh;
            }

            .notifications-dropdown-header {
                padding: 14px 16px;
            }

            .notifications-dropdown-header h3 {
                font-size: 16px;
            }

            .notification-dropdown-content {
                padding: 12px 14px;
                gap: 10px;
            }

            .notification-dropdown-icon {
                width: 32px;
                height: 32px;
                font-size: 14px;
            }

            .notification-dropdown-message {
                font-size: 13px;
            }

            .notification-dropdown-time {
                font-size: 10px;
            }
        }

    </style>
</head>
<body class="{{ request()->routeIs(['login', 'register']) ? 'auth-page' : '' }}">
    <header class="header">
        <nav class="nav">
            <a href="{{ route('home') }}" class="logo">Laravel Social</a>
            @auth
            <div class="user-info">
                @if(auth()->user()->is_admin)
                <a href="{{ route('admin.dashboard') }}" class="admin-link" title="Admin Panel">
                    <i class="fas fa-crown"></i>
                    <span>Admin</span>
                </a>
                @endif
                <span class="user-name">{{ auth()->user()->name }}</span>
                @if(auth()->user()->profile && auth()->user()->profile->avatar)
                    <img src="{{ asset('storage/' . auth()->user()->profile->avatar) }}" alt="Your avatar" class="user-avatar-small" loading="lazy">
                @else
                    <div class="user-avatar-small user-avatar-placeholder">
                        <i class="fas fa-user"></i>
                    </div>
                @endif
                <button type="button" class="header-notification-btn" onclick="toggleNotificationsDropdown()" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge" id="header-notification-badge" style="display: none;">0</span>
                </button>
            </div>
            @endauth
            <div class="nav-links">
                @auth
                <a href="{{ route('home') }}"><i class="fas fa-home"></i>Home</a>
                <a href="{{ route('stories.index') }}"><i class="fas fa-circle-play"></i>Stories</a>
                <a href="{{ route('chat.index') }}"><i class="fas fa-envelope"></i>Messages</a>
                <a href="{{ route('ai.index') }}"><i class="fas fa-robot"></i>AI Assistant</a>
                <a href="{{ route('explore') }}"><i class="fas fa-hashtag"></i>Explore</a>
                <a href="{{ route('search') }}"><i class="fas fa-search"></i>Search</a>
                <a href="{{ route('users.saved-posts') }}"><i class="fas fa-bookmark"></i>Saved Posts</a>
                <a href="{{ route('users.show', auth()->user()) }}"><i class="fas fa-user"></i>Profile</a>
                <a href="{{ route('password.change') }}"><i class="fas fa-key"></i>Change Password</a>
                <a href="{{ route('logout') }}" onclick="confirmLogout(event)"><i class="fas fa-sign-out-alt"></i>Logout</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                @else
                <a href="{{ route('login') }}"><i class="fas fa-sign-in-alt"></i>Login</a>
                <a href="{{ route('register') }}"><i class="fas fa-user-plus"></i>Register</a>
                @endauth
            </div>
            <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </button>
        </nav>
    </header>
    <div class="mobile-menu-overlay" onclick="closeMobileMenu()"></div>
    <main class="app-layout">
        @auth
        
        <aside class="left-sidebar">
            <nav class="nav-sidebar">
                <a href="{{ route('home') }}" class="nav-item {{ request()->routeIs('home', 'posts.*') ? 'active' : '' }}">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
                <a href="{{ route('stories.index') }}" class="nav-item {{ request()->routeIs('stories.*') ? 'active' : '' }}">
                    <i class="fas fa-circle-play"></i>
                    <span>Stories</span>
                </a>
                <a href="{{ route('chat.index') }}" class="nav-item {{ request()->routeIs('chat.*') ? 'active' : '' }}">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                </a>
                <a href="{{ route('explore') }}" class="nav-item {{ request()->routeIs('explore') ? 'active' : '' }}">
                    <i class="fas fa-hashtag"></i>
                    <span>Explore</span>
                </a>
                <a href="{{ route('users.saved-posts') }}" class="nav-item {{ request()->routeIs('users.saved-posts') ? 'active' : '' }}">
                    <i class="fas fa-bookmark"></i>
                    <span>Bookmarks</span>
                </a>
                <a href="{{ route('users.show', auth()->user()) }}" class="nav-item {{ request()->routeIs('users.show') && request()->route('user') == auth()->user()->name ? 'active' : '' }}">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>

                <a href="{{ route('ai.index') }}" class="nav-item {{ request()->routeIs('ai.index') ? 'active' : '' }}">
                    <i class="fas fa-robot"></i>
                    <span>AI Assistant</span>
                </a>



            </nav>
        </aside>
        @endauth

        
        <div class="main-content">
            @yield('content')
        </div>

        @auth

        <aside class="right-sidebar">
            <!-- Sidebar content can be added here in the future -->
        </aside>
        @endauth
    </main>



    <script>
        function toggleMobileMenu() {
            const navLinks = document.querySelector('.nav-links');
            const overlay = document.querySelector('.mobile-menu-overlay');
            const toggleBtn = document.querySelector('.mobile-menu-toggle i');

            if (navLinks.classList.contains('mobile-show')) {
                closeMobileMenu();
            } else {
                navLinks.classList.add('mobile-show');
                overlay.classList.add('active');
                toggleBtn.classList.remove('fa-bars');
                toggleBtn.classList.add('fa-times');

                // Prevent body scroll when menu is open
                document.body.style.overflow = 'hidden';
            }
        }

        function closeMobileMenu() {
            const navLinks = document.querySelector('.nav-links');
            const overlay = document.querySelector('.mobile-menu-overlay');
            const toggleBtn = document.querySelector('.mobile-menu-toggle i');

            navLinks.classList.remove('mobile-show');
            overlay.classList.remove('active');
            toggleBtn.classList.remove('fa-times');
            toggleBtn.classList.add('fa-bars');

            // Restore body scroll
            document.body.style.overflow = '';
        }

        // Close mobile menu when clicking outside or on overlay
        document.addEventListener('click', function(event) {
            const nav = document.querySelector('.nav');
            const navLinks = document.querySelector('.nav-links');
            const overlay = document.querySelector('.mobile-menu-overlay');

            if ((overlay.contains(event.target) || (!nav.contains(event.target) && navLinks.classList.contains('mobile-show')))) {
                closeMobileMenu();
            }
        });

        // Close mobile menu when clicking on menu links
        document.addEventListener('click', function(event) {
            if (event.target.matches('.nav-links a')) {
                closeMobileMenu();
            }
        });

        // Close mobile menu on window resize if desktop size
        window.addEventListener('resize', function() {
            const navLinks = document.querySelector('.nav-links');
            if (window.innerWidth > 768 && navLinks.classList.contains('mobile-show')) {
                closeMobileMenu();
            }
        });

        // Handle escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeMobileMenu();
            }
        });

        // Copy post link to clipboard
        function copyPostLink(postId) {
            const postUrl = window.location.origin + '/posts/' + postId;

            if (navigator.clipboard && window.isSecureContext) {
                // Use the Clipboard API when available
                navigator.clipboard.writeText(postUrl).then(function() {
                    showNotification('Post link copied to clipboard!');
                }).catch(function(err) {
                    console.error('Failed to copy: ', err);
                    fallbackCopyTextToClipboard(postUrl);
                });
            } else {
                // Fallback for older browsers
                fallbackCopyTextToClipboard(postUrl);
            }
        }

        function fallbackCopyTextToClipboard(text) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.left = "-999999px";
            textArea.style.top = "-999999px";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    showNotification('Post link copied to clipboard!');
                } else {
                    showNotification('Failed to copy link. Please copy manually: ' + text, true);
                }
            } catch (err) {
                showNotification('Failed to copy link. Please copy manually: ' + text, true);
            }

            document.body.removeChild(textArea);
        }

        function showNotification(message, isError = false) {
            // Remove any existing notifications
            const existingNotifications = document.querySelectorAll('.notification');
            existingNotifications.forEach(notification => notification.remove());

            // Check if mobile device
            const isMobile = window.innerWidth <= 1024;

            // Create notification element
            const notification = document.createElement('div');
            notification.className = 'notification';

            // Responsive styling based on device type
            if (isMobile) {
                // Mobile/tablet styling
                notification.style.cssText = `
                    position: fixed;
                    top: 10px;
                    left: 10px;
                    right: 10px;
                    width: calc(100vw - 20px);
                    background: ${isError ? '#dc3545' : '#28a745'};
                    color: white;
                    padding: 14px 16px;
                    border-radius: 8px;
                    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
                    z-index: 10001;
                    font-size: 15px;
                    font-weight: 600;
                    word-wrap: break-word;
                    line-height: 1.4;
                    text-align: center;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    letter-spacing: 0.3px;
                    max-height: 120px;
                    overflow-y: auto;
                `;
            } else {
                // Desktop styling
                notification.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: ${isError ? '#dc3545' : '#28a745'};
                    color: white;
                    padding: 14px 18px;
                    border-radius: 8px;
                    box-shadow: 0 6px 20px rgba(0,0,0,0.25), 0 0 0 1px rgba(255,255,255,0.1);
                    z-index: 10000;
                    font-size: 15px;
                    font-weight: 600;
                    max-width: 340px;
                    word-wrap: break-word;
                    line-height: 1.5;
                    text-align: center;
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    letter-spacing: 0.3px;
                    backdrop-filter: blur(8px);
                    -webkit-backdrop-filter: blur(8px);
                `;
            }

            // Create notification content with better structure
            notification.innerHTML = `
                <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <i class="${isError ? 'fas fa-exclamation-triangle' : 'fas fa-check-circle'}" style="font-size: ${isMobile ? '18px' : '16px'}; opacity: 0.9;"></i>
                    <span style="flex: 1; font-weight: 600;">${message}</span>
                </div>
            `;

            // Add to page
            document.body.appendChild(notification);

            // Enhanced animation and auto removal
            if (isMobile) {
                // Mobile slide-in animation
                notification.style.transform = 'translateY(-20px)';
                notification.style.opacity = '0';
                setTimeout(() => {
                    notification.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
                    notification.style.transform = 'translateY(0)';
                    notification.style.opacity = '1';
                }, 50);
            }

            setTimeout(() => {
                if (notification.parentNode) {
                    notification.style.transform = isMobile ? 'translateY(-20px)' : 'translateY(-10px) scale(0.95)';
                    notification.style.opacity = '0';
                    notification.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
                    setTimeout(() => {
                        if (notification.parentNode) notification.remove();
                    }, 400);
                }
            }, isMobile ? 4000 : 3500);
        }

        // Logout confirmation function
        function confirmLogout(event) {
            event.preventDefault();

            // Create custom confirmation dialog
            const overlay = document.createElement('div');
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.6);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
                backdrop-filter: blur(2px);
            `;

            const dialog = document.createElement('div');
            dialog.style.cssText = `
                background: var(--card-bg);
                border: 1px solid var(--border-color);
                border-radius: 16px;
                padding: 24px;
                max-width: 400px;
                width: 90%;
                box-shadow: var(--shadow);
                text-align: center;
                animation: dialogFadeIn 0.2s ease-out;
            `;

            const title = document.createElement('h3');
            title.textContent = 'Confirm Logout';
            title.style.cssText = `
                margin: 0 0 12px 0;
                color: var(--twitter-dark);
                font-size: 20px;
                font-weight: 600;
            `;

            const message = document.createElement('p');
            message.textContent = 'Are you sure you want to log out of your account?';
            message.style.cssText = `
                margin: 0 0 20px 0;
                color: var(--twitter-gray);
                font-size: 16px;
                line-height: 1.4;
            `;

            const buttonContainer = document.createElement('div');
            buttonContainer.style.cssText = `
                display: flex;
                gap: 12px;
                justify-content: center;
            `;

            const cancelButton = document.createElement('button');
            cancelButton.textContent = 'Cancel';
            cancelButton.style.cssText = `
                padding: 10px 20px;
                border: 2px solid var(--border-color);
                background: var(--card-bg);
                color: var(--twitter-gray);
                border-radius: 20px;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s ease;
                min-width: 80px;
            `;

            const logoutButton = document.createElement('button');
            logoutButton.textContent = 'Logout';
            logoutButton.style.cssText = `
                padding: 10px 20px;
                border: none;
                background: var(--error-color);
                color: white;
                border-radius: 20px;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s ease;
                min-width: 80px;
            `;

            // Add hover effects
            cancelButton.onmouseover = () => {
                cancelButton.style.background = 'var(--hover-bg)';
                cancelButton.style.borderColor = 'var(--twitter-gray)';
            };
            cancelButton.onmouseout = () => {
                cancelButton.style.background = 'var(--card-bg)';
                cancelButton.style.borderColor = 'var(--border-color)';
            };

            logoutButton.onmouseover = () => {
                logoutButton.style.background = '#c82333';
                logoutButton.style.transform = 'translateY(-1px)';
            };
            logoutButton.onmouseout = () => {
                logoutButton.style.background = '#dc3545';
                logoutButton.style.transform = 'translateY(0)';
            };

            // Event handlers
            cancelButton.onclick = () => {
                document.body.removeChild(overlay);
            };

            logoutButton.onclick = () => {
                document.getElementById('logout-form').submit();
            };

            // Close on overlay click
            overlay.onclick = (e) => {
                if (e.target === overlay) {
                    document.body.removeChild(overlay);
                }
            };

            // Close on escape key
            const handleEscape = (e) => {
                if (e.key === 'Escape') {
                    document.body.removeChild(overlay);
                    document.removeEventListener('keydown', handleEscape);
                }
            };
            document.addEventListener('keydown', handleEscape);

            // Assemble dialog
            buttonContainer.appendChild(cancelButton);
            buttonContainer.appendChild(logoutButton);
            dialog.appendChild(title);
            dialog.appendChild(message);
            dialog.appendChild(buttonContainer);
            overlay.appendChild(dialog);
            document.body.appendChild(overlay);

            // Focus management
            setTimeout(() => cancelButton.focus(), 100);
        }

        // Powerful login modal for unlogged users
        function showLoginModal(action, message) {
            // Remove any existing modals
            const existingModal = document.getElementById('login-modal');
            if (existingModal) {
                existingModal.remove();
            }

            // Create modal overlay
            const modalOverlay = document.createElement('div');
            modalOverlay.id = 'login-modal';
            modalOverlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.8);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10000;
                animation: modalFadeIn 0.3s ease-out;
            `;

            // Create modal content
            modalOverlay.innerHTML = `
                <div style="
                    background: var(--card-bg);
                    border: 2px solid var(--border-color);
                    border-radius: 20px;
                    padding: 0;
                    max-width: 450px;
                    width: 90%;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.1);
                    position: relative;
                    overflow: hidden;
                    animation: modalSlideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                ">
                    <!-- Header with gradient -->
                    <div style="
                        background: linear-gradient(135deg, var(--twitter-blue) 0%, #1A91DA 100%);
                        padding: 24px 24px 20px 24px;
                        text-align: center;
                        position: relative;
                    ">
                        <div style="
                            position: absolute;
                            top: 0;
                            left: 0;
                            right: 0;
                            bottom: 0;
                            background: linear-gradient(45deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.02) 50%, rgba(255,255,255,0.08) 100%);
                            opacity: 0.6;
                        "></div>
                        <i class="fas ${action === 'like' ? 'fa-heart' : action === 'save' ? 'fa-bookmark' : 'fa-star'}" style="
                            font-size: 48px;
                            color: white;
                            margin-bottom: 12px;
                            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
                            animation: iconBounce 0.6s ease-out;
                        "></i>
                        <h2 style="
                            color: white;
                            margin: 0;
                            font-size: 24px;
                            font-weight: 700;
                            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
                        ">Join the Community</h2>
                        <p style="
                            color: rgba(255,255,255,0.9);
                            margin: 8px 0 0 0;
                            font-size: 16px;
                            font-weight: 500;
                        ">${message}</p>
                    </div>

                    <!-- Content -->
                    <div style="padding: 24px;">
                        <!-- Action buttons -->
                        <div style="
                            display: grid;
                            grid-template-columns: 1fr 1fr;
                            gap: 12px;
                        ">
                            <a href="{{ route('login') }}" style="
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                gap: 8px;
                                padding: 14px 20px;
                                background: var(--twitter-blue);
                                color: white;
                                text-decoration: none;
                                border-radius: 12px;
                                font-weight: 600;
                                font-size: 15px;
                                transition: all 0.2s ease;
                                box-shadow: 0 4px 12px rgba(29, 161, 242, 0.3);
                            " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(29, 161, 242, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(29, 161, 242, 0.3)';">
                                <i class="fas fa-sign-in-alt"></i>
                                Login
                            </a>

                            <a href="{{ route('register') }}" style="
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                gap: 8px;
                                padding: 14px 20px;
                                background: transparent;
                                color: var(--twitter-blue);
                                text-decoration: none;
                                border: 2px solid var(--twitter-blue);
                                border-radius: 12px;
                                font-weight: 600;
                                font-size: 15px;
                                transition: all 0.2s ease;
                            " onmouseover="this.style.background='var(--twitter-blue)'; this.style.color='white'; this.style.transform='translateY(-2px)';" onmouseout="this.style.background='transparent'; this.style.color='var(--twitter-blue)'; this.style.transform='translateY(0)';">
                                <i class="fas fa-user-plus"></i>
                                Register
                            </a>
                        </div>

                        <!-- Close button -->
                        <button onclick="closeLoginModal()" style="
                            position: absolute;
                            top: 12px;
                            right: 12px;
                            width: 32px;
                            height: 32px;
                            border: none;
                            border-radius: 50%;
                            background: rgba(255,255,255,0.2);
                            color: white;
                            cursor: pointer;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 14px;
                            transition: all 0.2s ease;
                        " onmouseover="this.style.background='rgba(255,255,255,0.3)'; this.style.transform='scale(1.1)';" onmouseout="this.style.background='rgba(255,255,255,0.2)'; this.style.transform='scale(1)';">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;

            // Add modal to page
            document.body.appendChild(modalOverlay);

            // Close on overlay click
            modalOverlay.addEventListener('click', function(e) {
                if (e.target === modalOverlay) {
                    closeLoginModal();
                }
            });

            // Close on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeLoginModal();
                }
            });
        }

        function closeLoginModal() {
            const modal = document.getElementById('login-modal');
            if (modal) {
                modal.style.animation = 'modalFadeOut 0.3s ease-out';
                setTimeout(() => {
                    if (modal.parentNode) {
                        modal.remove();
                    }
                }, 300);
            }
        }

        // Add fade-in animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes modalFadeIn {
                from {
                    opacity: 0;
                }
                to {
                    opacity: 1;
                }
            }

            @keyframes modalSlideUp {
                from {
                    opacity: 0;
                    transform: scale(0.9) translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: scale(1) translateY(0);
                }
            }

            @keyframes modalFadeOut {
                from {
                    opacity: 1;
                }
                to {
                    opacity: 0;
                }
            }

            @keyframes iconBounce {
                0%, 100% {
                    transform: scale(1);
                }
                50% {
                    transform: scale(1.1);
                }
            }

            @keyframes dialogFadeIn {
                from {
                    opacity: 0;
                    transform: scale(0.9) translateY(-20px);
                }
                to {
                    opacity: 1;
                    transform: scale(1) translateY(0);
                }
            }
        `;
        document.head.appendChild(style);
    </script>

    
    <div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999; pointer-events: none; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;"></div>

    
    @vite(['resources/js/bootstrap.js'])
    <script src="{{ asset('js/realtime.js') }}"></script>

    <script>
        // Ultra-simple toast notification system
        function showToast(message, type = 'info', duration = 3000) {
            // Remove any existing toasts
            const existingToasts = document.querySelectorAll('.toast-notification');
            existingToasts.forEach(toast => toast.remove());

            const toastContainer = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = 'toast-notification';

            // Simple solid colors
            const colors = {
                success: '#28a745',
                error: '#dc3545',
                warning: '#ffc107',
                info: '#17a2b8',
                message: '#007bff'
            };

            toast.style.cssText = `
                background: ${colors[type] || colors.info};
                color: white;
                padding: 12px 16px;
                border-radius: 4px;
                margin-bottom: 8px;
                font-size: 14px;
                font-weight: 500;
                max-width: 350px;
                word-wrap: break-word;
                position: relative;
                opacity: 1;
                transition: opacity 0.3s ease;
            `;

            toast.textContent = message;

            // Close button removed as requested

            toastContainer.appendChild(toast);

            // Auto remove after duration
            if (duration > 0) {
                setTimeout(() => {
                    if (toast.parentElement) {
                        toast.style.opacity = '0';
                        setTimeout(() => toast.remove(), 300);
                    }
                }, duration);
            }

            return toast;
        }

        // Optimized real-time message notifications for mobile performance
        document.addEventListener('DOMContentLoaded', function() {
            // Only run if user is authenticated
            const currentUserId = {{ auth()->id() ?? 'null' }};
            if (!currentUserId) {
                console.log('User not authenticated, skipping message notifications');
                return;
            }

            console.log('Initializing optimized message notifications for user:', currentUserId);

            // Initialize notification badge
            initializeNotificationBadge();

            // Use different polling intervals based on device type for better mobile performance
            const isMobile = window.innerWidth <= 768;
            const notificationPollInterval = isMobile ? 30000 : 15000; // 30s mobile, 15s desktop
            const messagePollInterval = isMobile ? 15000 : 8000; // 15s mobile, 8s desktop

            // Poll for notification updates
            setInterval(updateNotificationBadgeFromServer, notificationPollInterval);

            // Check for new messages with optimized frequency
            setInterval(checkForNewMessages, messagePollInterval);

            // Initial check after 2 seconds (delayed for better page load)
            setTimeout(checkForNewMessages, 2000);
        });

        // Optimized function to update notification badge
        function updateNotificationBadgeFromServer() {
            const currentUserId = {{ auth()->id() ?? 'null' }};
            if (!currentUserId) return;

            // Use AbortController for better performance
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 5000); // 5s timeout

            fetch('/api/notifications/unread-count', {
                signal: controller.signal,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                clearTimeout(timeoutId);
                return response.json();
            })
            .then(data => {
                if (data.unread_count !== undefined) {
                    updateNotificationBadge(data.unread_count);
                }
            })
            .catch(error => {
                clearTimeout(timeoutId);
                if (error.name === 'AbortError') {
                    console.log('Notification polling timed out');
                } else {
                    console.log('Notification polling failed:', error.message);
                }
            });
        }

        function checkForNewMessages() {
            const currentUserId = {{ auth()->id() ?? 'null' }};
            if (!currentUserId) return;

            // Skip if user is currently in a chat to reduce unnecessary polling
            const currentPath = window.location.pathname;
            if (currentPath.includes('/chat/')) {
                return; // Don't poll when user is actively chatting
            }

            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 3000); // 3s timeout

            fetch('/api/user/new-messages', {
                signal: controller.signal,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                clearTimeout(timeoutId);
                return response.json();
            })
            .then(data => {
                if (data.success && data.messages && data.messages.length > 0) {
                    // Process messages more efficiently
                    data.messages.forEach(message => {
                        // Use requestAnimationFrame for smoother UI updates
                        requestAnimationFrame(() => showMessageNotification(message));
                    });
                }
            })
            .catch(error => {
                clearTimeout(timeoutId);
                if (error.name !== 'AbortError') {
                    console.error('Error checking messages:', error.message);
                }
            });
        }

        function showMessageNotification(message) {
            const messageText = message.content.length > 35
                ? message.content.substring(0, 35) + '...'
                : message.content;

            const notificationMessage = `${message.sender.name}: ${messageText}`;

            // Just use the simple toast notification
            showToast(notificationMessage, 'message', 4000);
        }

        // Toast notifications are ready - no browser permissions needed

        // Initialize notification badge on page load
        function initializeNotificationBadge() {
            const currentUserId = {{ auth()->id() ?? 'null' }};
            if (!currentUserId) return;

            fetch('/api/notifications/unread-count', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.unread_count !== undefined) {
                    updateNotificationBadge(data.unread_count);
                }
            })
            .catch(error => console.error('Error loading notification count:', error));
        }

        // Update notification badge function
        function updateNotificationBadge(count) {
            const headerBadge = document.getElementById('header-notification-badge');
            if (headerBadge) {
                headerBadge.textContent = count > 99 ? '99+' : count;
                headerBadge.style.display = count > 0 ? 'inline-flex' : 'none';

                if (count > 0) {
                    headerBadge.classList.add('pulse');
                    setTimeout(() => headerBadge.classList.remove('pulse'), 1000);
                }
            }
        }

        // Header notifications dropdown functionality
        let notificationsDropdown = null;
        let isLoadingNotifications = false;

        // Helper functions for notifications
        function getNotificationIcon(type) {
            const iconMap = {
                'like': 'like',
                'comment': 'comment',
                'follow': 'follow',
                'message': 'message',
                'mention': 'mention'
            };
            return iconMap[type] || 'default';
        }

        function getNotificationIconClass(type) {
            const iconMap = {
                'like': 'fas fa-heart',
                'comment': 'fas fa-comment',
                'follow': 'fas fa-user-plus',
                'message': 'fas fa-envelope',
                'mention': 'fas fa-at'
            };
            return iconMap[type] || 'fas fa-bell';
        }

        function getTimeAgo(dateString) {
            const now = new Date();
            const date = new Date(dateString);
            const diffInSeconds = Math.floor((now - date) / 1000);

            if (diffInSeconds < 60) return 'Just now';
            if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
            if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
            if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`;

            return date.toLocaleDateString();
        }

        function toggleNotificationsDropdown() {
            if (notificationsDropdown) {
                closeNotificationsDropdown();
                return;
            }

            showNotificationsDropdown();
        }

        function showNotificationsDropdown() {
            // Create dropdown container
            notificationsDropdown = document.createElement('div');
            notificationsDropdown.className = 'notifications-dropdown-overlay';
            notificationsDropdown.onclick = (e) => {
                if (e.target === notificationsDropdown) {
                    closeNotificationsDropdown();
                }
            };

            // Create dropdown content
            const dropdownContent = document.createElement('div');
            dropdownContent.className = 'notifications-dropdown-content';

            dropdownContent.innerHTML = `
                <div class="notifications-dropdown-header">
                    <h3>Notifications</h3>
                    <div class="notifications-dropdown-actions">
                        <button class="notifications-dropdown-delete-all" onclick="deleteAllNotifications()" title="Delete All Notifications">
                            <i class="fas fa-trash-alt"></i>
                            <span>Delete All</span>
                        </button>
                        <button class="notifications-dropdown-close" onclick="closeNotificationsDropdown()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="notifications-dropdown-body">
                    <div class="notifications-loading">
                        <div class="loading-spinner"></div>
                        <p>Loading notifications...</p>
                    </div>
                </div>
            `;

            notificationsDropdown.appendChild(dropdownContent);
            document.body.appendChild(notificationsDropdown);

            // Prevent body scroll
            document.body.style.overflow = 'hidden';

            // Load notifications
            loadNotificationsForDropdown();

            // Handle escape key
            document.addEventListener('keydown', handleDropdownEscape);
        }

        function closeNotificationsDropdown() {
            if (notificationsDropdown) {
                notificationsDropdown.remove();
                notificationsDropdown = null;
                document.body.style.overflow = '';
                document.removeEventListener('keydown', handleDropdownEscape);
            }
        }

        function handleDropdownEscape(e) {
            if (e.key === 'Escape') {
                closeNotificationsDropdown();
            }
        }

        function loadNotificationsForDropdown() {
            if (isLoadingNotifications) return;
            isLoadingNotifications = true;

            fetch("/api/notifications", {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                const body = notificationsDropdown.querySelector('.notifications-dropdown-body');
                body.innerHTML = '';

                if (!data.notifications || data.notifications.length === 0) {
                    body.innerHTML = `
                        <div class="notifications-empty">
                            <div class="empty-icon">
                                <i class="fas fa-bell-slash"></i>
                            </div>
                            <p>No notifications yet</p>
                            <small>When someone interacts with your posts, you'll see them here.</small>
                        </div>
                    `;
                } else {
                    const notificationsList = document.createElement('div');
                    notificationsList.className = 'notifications-list';

                    data.notifications.forEach(notification => {
                        const notificationElement = createDropdownNotificationElement(notification);
                        notificationsList.appendChild(notificationElement);
                    });

                    body.appendChild(notificationsList);
                }
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                console.error('Error details:', error.message, error.status, error.response);

                const body = notificationsDropdown.querySelector('.notifications-dropdown-body');
                body.innerHTML = `
                    <div class="notifications-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p>Failed to load notifications</p>
                        <small style="color: #666; font-size: 11px;">${error.message || 'Unknown error'}</small>
                    </div>
                `;
            })
            .finally(() => {
                isLoadingNotifications = false;
            });
        }

        function createDropdownNotificationElement(notification) {
            const item = document.createElement('div');
            item.className = `notification-dropdown-item ${!notification.read_at ? 'unread' : ''}`;

            // Get notification type icon
            const iconClass = getNotificationIcon(notification.type);
            const timeAgo = getTimeAgo(notification.created_at);

            item.innerHTML = `
                <div class="notification-dropdown-content">
                    <div class="notification-dropdown-icon ${iconClass}">
                        <i class="${getNotificationIconClass(notification.type)}"></i>
                    </div>
                    <div class="notification-dropdown-text">
                        <div class="notification-dropdown-message">${notification.message}</div>
                        <div class="notification-dropdown-time">${timeAgo}</div>
                    </div>
                    <div class="notification-dropdown-actions">
                        ${!notification.read_at ? `<button onclick="markNotificationAsRead(${notification.id}, this)" class="btn-mark-read" title="Mark as read"><i class="fas fa-check"></i></button>` : ''}
                        <button onclick="deleteNotificationFromDropdown(${notification.id}, this)" class="btn-delete" title="Delete"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            `;

            return item;
        }

        function markNotificationAsRead(notificationId, buttonElement) {
            fetch(`/api/notifications/${notificationId}/read`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector("meta[name=\"csrf-token\"]").getAttribute("content"),
                    "Content-Type": "application/json"
                }
            })
            .then(() => {
                // Update UI
                const item = buttonElement.closest('.notification-dropdown-item');
                item.classList.remove('unread');
                buttonElement.remove();

                // Update badge - fetch current count
                fetch('/api/notifications/unread-count', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.unread_count !== undefined) {
                        updateNotificationBadge(data.unread_count);
                    }
                })
                .catch(error => console.error('Error updating badge:', error));
            })
            .catch(error => console.error("Error marking notification as read:", error));
        }

        function deleteNotificationFromDropdown(notificationId, buttonElement) {
            if (!confirm("Are you sure you want to delete this notification?")) return;

            fetch(`/api/notifications/${notificationId}`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector("meta[name=\"csrf-token\"]").getAttribute("content"),
                    "Content-Type": "application/json"
                }
            })
            .then(() => {
                // Remove from UI
                const item = buttonElement.closest('.notification-dropdown-item');
                item.remove();

                // Check if empty
                const body = notificationsDropdown.querySelector('.notifications-dropdown-body');
                if (body.querySelectorAll('.notification-dropdown-item').length === 0) {
                    body.innerHTML = `
                        <div class="notifications-empty">
                            <div class="empty-icon">
                                <i class="fas fa-bell-slash"></i>
                            </div>
                            <p>No notifications yet</p>
                            <small>When someone interacts with your posts, you'll see them here.</small>
                        </div>
                    `;
                }

                // Update badge - fetch current count
                fetch('/api/notifications/unread-count', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.unread_count !== undefined) {
                        updateNotificationBadge(data.unread_count);
                    }
                })
                .catch(error => console.error('Error updating badge:', error));
            })
            .catch(error => console.error("Error deleting notification:", error));
        }

        function deleteAllNotifications() {
            if (!confirm("Are you sure you want to delete ALL notifications? This action cannot be undone.")) return;

            fetch('/api/notifications', {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector("meta[name=\"csrf-token\"]").getAttribute("content"),
                    "Content-Type": "application/json"
                }
            })
            .then(() => {
                // Update UI - show empty state
                const body = notificationsDropdown.querySelector('.notifications-dropdown-body');
                body.innerHTML = `
                    <div class="notifications-empty">
                        <div class="empty-icon">
                            <i class="fas fa-bell-slash"></i>
                        </div>
                        <p>No notifications yet</p>
                        <small>When someone interacts with your posts, you'll see them here.</small>
                    </div>
                `;

                // Update badge - should be 0 after deleting all
                updateNotificationBadge(0);
            })
            .catch(error => console.error("Error deleting all notifications:", error));
        }

        function updateNotificationBadge(count) {
            const headerBadge = document.getElementById('header-notification-badge');
            if (headerBadge) {
                headerBadge.textContent = count > 99 ? '99+' : count;
                headerBadge.style.display = count > 0 ? 'inline-flex' : 'none';

                if (count > 0) {
                    headerBadge.classList.add('pulse');
                    setTimeout(() => headerBadge.classList.remove('pulse'), 1000);
                }
            }
        }
    </script>
</body>
</html>
</html>
