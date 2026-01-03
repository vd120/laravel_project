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
            min-width: 220px;
            z-index: 1000;
            padding: 12px 0;
            backdrop-filter: blur(10px);
            animation: dropdownSlideIn 0.2s ease-out;
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

        /* Responsive design */
        @media (max-width: 1200px) {
            .app-layout {
                grid-template-columns: 275px 1fr;
                gap: 20px;
            }

            .right-sidebar {
                display: none;
            }
        }

        @media (max-width: 900px) {
            .app-layout {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .left-sidebar {
                display: none;
            }

            .right-sidebar {
                display: none;
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

            .admin-link {
                display: none; /* Hide admin link on mobile */
            }

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
            </div>
            @endauth
            <div class="nav-links">
                @auth
                <a href="{{ route('home') }}">Home</a>

                <a href="{{ route('stories.index') }}">Stories</a>
                <a href="{{ route('chat.index') }}">Messages</a>
                <a href="{{ route('explore') }}">Explore</a>
                <a href="{{ route('search') }}">Search</a>
                <a href="{{ route('users.saved-posts') }}">Saved Posts</a>
                <a href="{{ route('users.show', auth()->user()) }}">Profile</a>
                <a href="{{ route('password.change') }}">Change Password</a>
                <a href="{{ route('logout') }}" onclick="confirmLogout(event)">Logout</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                @else
                <a href="{{ route('login') }}">Login</a>
                <a href="{{ route('register') }}">Register</a>
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


                
                <div class="user-profile-card">
                    <div class="user-info">
                        @if(auth()->user()->profile && auth()->user()->profile->avatar)
                            <img src="{{ asset('storage/' . auth()->user()->profile->avatar) }}" alt="{{ auth()->user()->name }}" class="user-avatar-small">
                        @else
                            <div class="user-avatar-small user-avatar-placeholder">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <div class="user-name">{{ auth()->user()->name }}</div>
                            <div class="user-handle">{{ '@' . auth()->user()->name }}</div>
                        </div>
                    </div>
                </div>
            </nav>
        </aside>
        @endauth

        
        <div class="main-content">
            @yield('content')
        </div>

        @auth
        
        <aside class="right-sidebar">
            <div style="background: var(--twitter-light); border: 1px solid var(--border-color); border-radius: 16px; padding: 16px; margin-bottom: 16px;">
                <h4 style="margin: 0 0 12px 0; font-size: 18px; color: var(--twitter-dark);">What's happening</h4>
                <p style="margin: 0; color: var(--twitter-gray); font-size: 14px;">Stay tuned for trending topics and updates!</p>
            </div>
            <div style="background: var(--twitter-light); border: 1px solid var(--border-color); border-radius: 16px; padding: 16px;">
                <h4 style="margin: 0 0 12px 0; font-size: 18px; color: var(--twitter-dark);">Who to follow</h4>
                <p style="margin: 0; color: var(--twitter-gray); font-size: 14px;">Discover new people to follow!</p>
            </div>
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

            // Create notification element
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${isError ? '#dc3545' : '#28a745'};
                color: white;
                padding: 12px 16px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                z-index: 10000;
                font-size: 14px;
                max-width: 300px;
                word-wrap: break-word;
            `;
            notification.textContent = message;

            // Add to page
            document.body.appendChild(notification);

            // Auto remove after 3 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 3000);
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
                background: white;
                border-radius: 16px;
                padding: 24px;
                max-width: 400px;
                width: 90%;
                box-shadow: 0 20px 40px rgba(0,0,0,0.3);
                text-align: center;
                animation: dialogFadeIn 0.2s ease-out;
            `;

            const title = document.createElement('h3');
            title.textContent = 'Confirm Logout';
            title.style.cssText = `
                margin: 0 0 12px 0;
                color: #14171A;
                font-size: 20px;
                font-weight: 600;
            `;

            const message = document.createElement('p');
            message.textContent = 'Are you sure you want to log out of your account?';
            message.style.cssText = `
                margin: 0 0 20px 0;
                color: #657786;
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
                border: 2px solid #E1E8ED;
                background: white;
                color: #657786;
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
                background: #dc3545;
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
                cancelButton.style.background = '#F7F9FA';
                cancelButton.style.borderColor = '#AAB8C2';
            };
            cancelButton.onmouseout = () => {
                cancelButton.style.background = 'white';
                cancelButton.style.borderColor = '#E1E8ED';
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

        // Add fade-in animation
        const style = document.createElement('style');
        style.textContent = `
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
        // Powerful toast notification system
        function showToast(message, type = 'info', duration = 5000) {
            const toastContainer = document.getElementById('toast-container');

            const toast = document.createElement('div');
            toast.style.cssText = `
                position: relative;
                margin-bottom: 12px;
                min-width: 320px;
                max-width: 420px;
                padding: 16px 20px;
                border-radius: 16px;
                box-shadow: 0 8px 32px rgba(0,0,0,0.4);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255,255,255,0.1);
                transform: translateX(100%);
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                cursor: pointer;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                overflow: hidden;
            `;

            // Powerful gradient backgrounds based on type
            const gradients = {
                info: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                success: 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
                warning: 'linear-gradient(135deg, #fcb045 0%, #fd1d1d 100%)',
                error: 'linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%)',
                message: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
            };

            toast.style.background = gradients[type] || gradients.info;

            // Add inner glow effect
            toast.innerHTML = `
                <div style="position: relative; z-index: 2;">
                    <div style="display: flex; align-items: center; margin-bottom: 6px;">
                        <i class="fas ${type === 'message' ? 'fa-envelope' : type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-triangle' : 'fa-info-circle'}" style="font-size: 16px; margin-right: 8px; opacity: 0.9;"></i>
                        <span style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.8;">${type === 'message' ? 'New Message' : type.toUpperCase()}</span>
                    </div>
                    <p style="font-size: 14px; font-weight: 500; margin: 0; color: white; line-height: 1.4;">${message}</p>
                </div>
                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 50%); pointer-events: none;"></div>
            `;

            toastContainer.appendChild(toast);

            // Powerful slide-in animation
            setTimeout(() => {
                toast.style.transform = 'translateX(0) scale(1)';
                toast.style.opacity = '1';
            }, 50);

            // Add hover effects
            toast.onmouseenter = () => {
                toast.style.transform = 'translateX(-4px) scale(1.02)';
                toast.style.boxShadow = '0 12px 40px rgba(0,0,0,0.5)';
            };

            toast.onmouseleave = () => {
                toast.style.transform = 'translateX(0) scale(1)';
                toast.style.boxShadow = '0 8px 32px rgba(0,0,0,0.4)';
            };

            // Auto remove with powerful exit animation
            if (duration > 0) {
                setTimeout(() => {
                    if (toast.parentElement) {
                        toast.style.transform = 'translateX(100%) scale(0.95)';
                        toast.style.opacity = '0';
                        setTimeout(() => {
                            if (toast.parentElement) toast.remove();
                        }, 400);
                    }
                }, duration);
            }

            return toast;
        }

        // Real-time message notifications for all pages
        document.addEventListener('DOMContentLoaded', function() {
            // Only run if user is authenticated
            const currentUserId = {{ auth()->id() ?? 'null' }};
            if (!currentUserId) {
                console.log('User not authenticated, skipping message notifications');
                return;
            }

            console.log('Initializing global message notifications for user:', currentUserId);

            // Check for new messages every 5 seconds (reduced for testing)
            setInterval(checkForNewMessages, 5000);

            // Initial check after 1 second
            setTimeout(checkForNewMessages, 1000);
        });

        let lastNotificationCheck = new Date();

        function checkForNewMessages() {
            const currentUserId = {{ auth()->id() ?? 'null' }};
            if (!currentUserId) return;

            console.log('Checking for new messages...');

            fetch('/api/user/new-messages', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                console.log('API response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('API response data:', data);
                if (data.success && data.messages && data.messages.length > 0) {
                    console.log('Found', data.messages.length, 'new messages');
                    data.messages.forEach(message => {
                        console.log('Processing message:', message);
                        // Check if we're not currently in the chat with this user
                        const currentPath = window.location.pathname;
                        const isInChat = currentPath.includes('/chat/') && currentPath.includes(message.conversation_id);

                        console.log('Current path:', currentPath, 'Is in chat:', isInChat);

                        if (!isInChat) {
                            showMessageNotification(message);
                        } else {
                            console.log('Skipping notification - already in chat');
                        }
                    });
                } else {
                    console.log('No new messages found');
                }
            })
            .catch(error => {
                console.error('Error checking for new messages:', error);
            });
        }

        function showMessageNotification(message) {
            console.log('Showing message notification for:', message);

            const messageText = message.content.length > 50
                ? message.content.substring(0, 50) + '...'
                : message.content;

            const notificationMessage = `<strong>${message.sender.name}</strong> sent: ${messageText}`;

            console.log('Creating toast with message:', notificationMessage);

            try {
                const toast = showToast(notificationMessage, 'message', 3000);
                console.log('Toast created:', toast);

                // Add click handler to go to chat
                toast.style.cursor = 'pointer';
                toast.onclick = function() {
                    console.log('Toast clicked, navigating to chat');
                    window.location.href = `/chat/${message.conversation_id}`;
                };

                console.log('Toast click handler added');

                // Try to show browser notification if permission granted
                if ('Notification' in window && Notification.permission === 'granted') {
                    console.log('Showing browser notification');
                    new Notification(`New message from ${message.sender.name}`, {
                        body: messageText,
                        icon: message.sender.avatar ? `/storage/${message.sender.avatar}` : null,
                        tag: `message-${message.conversation_id}`
                    });
                } else {
                    console.log('Browser notification permission not granted');
                }
            } catch (error) {
                console.error('Error creating toast notification:', error);
            }
        }

        // Toast notifications are ready - no browser permissions needed
    </script>
</body>
</html>
