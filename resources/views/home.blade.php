<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Nexus - A modern social platform to connect with friends, share moments, and discover new stories. Share photos, videos, and stories in a safe environment.">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Nexus - Share Your Story">
    <meta property="og:description" content="Connect with friends, share moments, and discover new stories on the modern social platform.">
    <meta property="og:url" content="{{ url('/') }}">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Nexus - Share Your Story">
    <meta name="twitter:description" content="Connect with friends, share moments, and discover new stories on the modern social platform.">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌐</text></svg>">
    
    <title>Nexus - Share Your Story</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, sans-serif; color: #fff; background: #000; overflow-x: hidden; }

        /* Fallback background in case video doesn't load */
        .bg-fallback {
            position: fixed;
            inset: 0;
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
            z-index: -3;
        }

        .video-bg { position: fixed; inset: 0; width: 100%; height: 100%; object-fit: cover; z-index: -2; }
        .bg-overlay { position: fixed; inset: 0; background: linear-gradient(to bottom, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0.8) 100%); z-index: -1; }

        /* Header */
        nav { position: fixed; top: 0; left: 0; right: 0; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; z-index: 100; background: rgba(0,0,0,0.2); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); }
        .logo { font-size: 20px; font-weight: 700; color: #fff; text-decoration: none; letter-spacing: -0.5px; }
        .logo span { color: #60a5fa; }

        /* Skip link for accessibility */
        .skip-link {
            position: absolute;
            top: -40px;
            left: 0;
            background: #60a5fa;
            color: #fff;
            padding: 8px 16px;
            z-index: 1000;
            text-decoration: none;
            font-weight: 600;
        }
        .skip-link:focus {
            top: 0;
        }

        /* Hero */
        .hero { min-height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; padding: 100px 20px; }
        .hero h1 { font-size: clamp(2.5rem, 8vw, 5rem); font-weight: 800; letter-spacing: -2px; margin-bottom: 16px; line-height: 1.1; }
        .hero h1 span { background: linear-gradient(135deg, #60a5fa, #a78bfa); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .hero p { font-size: clamp(1rem, 2vw, 1.25rem); color: rgba(255,255,255,0.6); max-width: 500px; margin-bottom: 40px; font-weight: 400; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 14px 28px; font-size: 15px; font-weight: 600; border-radius: 50px; text-decoration: none; cursor: pointer; border: none; }
        .btn-primary { background: #fff; color: #000; }
        .btn-ghost { background: transparent; color: #fff; border: 1px solid rgba(255,255,255,0.3); }
        .btn-ghost:hover { background: rgba(255,255,255,0.1); border-color: #fff; }

        /* About */
        .about { padding: 100px 20px; background: rgba(0,0,0,0.5); text-align: center; }
        .about-text { color: rgba(255,255,255,0.7); font-size: clamp(1rem, 2vw, 1.125rem); max-width: 700px; margin: 0 auto; line-height: 1.8; }

        /* Features */
        .features { padding: 100px 20px; background: rgba(0,0,0,0.7); }
        .container { max-width: 1100px; margin: 0 auto; }
        .section-title { font-size: clamp(1.75rem, 4vw, 2.5rem); font-weight: 700; text-align: center; margin-bottom: 50px; letter-spacing: -1px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; }
        .card { padding: 32px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 20px; }
        .card:hover { background: rgba(255,255,255,0.05); }
        .card-icon { width: 48px; height: 48px; background: linear-gradient(135deg, #60a5fa, #a78bfa); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 20px; }
        .card h3 { font-size: 1.125rem; font-weight: 600; margin-bottom: 8px; }
        .card p { color: rgba(255,255,255,0.5); font-size: 0.875rem; line-height: 1.6; }

        /* Join Now */
        .join-now { padding: 100px 20px; background: linear-gradient(180deg, rgba(0,0,0,0.7) 0%, rgba(30,30,60,0.3) 50%, rgba(0,0,0,0.9) 100%); text-align: center; }
        .section-subtitle { color: rgba(255,255,255,0.5); text-align: center; margin-bottom: 40px; max-width: 500px; margin-left: auto; margin-right: auto; }

        /* Footer */
        footer { padding: 40px 20px; text-align: center; border-top: 1px solid rgba(255,255,255,0.05); }
        footer p { color: rgba(255,255,255,0.4); font-size: 0.875rem; }
        footer a { color: #60a5fa; text-decoration: none; }

        @media (max-width: 768px) {
            nav { padding: 16px 20px; }
            .hero { padding: 80px 20px; }
            .features { padding: 60px 20px; }
            .grid { grid-template-columns: 1fr; }
        }

        /* Reduce motion for accessibility */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
            .video-bg {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Skip link for keyboard navigation -->
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <!-- Fallback background -->
    <div class="bg-fallback" aria-hidden="true"></div>
    
    <video class="video-bg" autoplay muted loop playsinline aria-hidden="true" poster="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1920 1080'%3E%3Crect fill='%230f0f23' width='1920' height='1080'/%3E%3C/svg%3E">
        <source src="https://cdn.pixabay.com/video/2021/12/10/100221-657132594_medium.mp4" type="video/mp4">
    </video>
    <div class="bg-overlay" aria-hidden="true"></div>

    <nav role="navigation" aria-label="Main navigation">
        <a href="/" class="logo">Nexus</a>
    </nav>

    <main id="main-content">
        <section class="hero" aria-labelledby="hero-title">
            <h1 id="hero-title">Share Your <span>Story</span></h1>
            <p>Connect with friends, share moments, and discover new stories on the modern social platform.</p>
            <div style="display: flex; gap: 12px; flex-wrap: wrap; justify-content: center;">
                <a href="{{ route('register') }}" class="btn btn-primary">Get Started</a>
                <a href="{{ route('login') }}" class="btn btn-ghost">Sign In</a>
            </div>
        </section>

        <section class="about" aria-labelledby="about-title">
            <div class="container">
                <h2 class="section-title" id="about-title">What is Nexus?</h2>
                <p class="about-text">Nexus is a modern social platform designed to bring people together. Share your life's best moments through photos, videos, and stories. Connect with friends, discover new content, and express yourself in a safe and welcoming environment. Our AI-powered features help you create better content while keeping your privacy in focus.</p>
            </div>
        </section>

        <section class="features" aria-labelledby="features-title">
            <div class="container">
                <h2 class="section-title" id="features-title">Everything You Need</h2>
                <p class="section-subtitle">Powerful features designed to enhance your social experience and keep you connected with what matters most.</p>
                <div class="grid" role="list">
                    <article class="card" role="listitem">
                        <div class="card-icon" aria-hidden="true"><i class="fas fa-photo-film"></i></div>
                        <h3>Photos & Videos</h3>
                        <p>Share your best moments with beautiful photos and videos. Upload high-quality media, create albums, and let your creativity shine with instant editing tools.</p>
                    </article>
                    <article class="card" role="listitem">
                        <div class="card-icon" aria-hidden="true"><i class="fas fa-bolt"></i></div>
                        <h3>Stories</h3>
                        <p>Share ephemeral moments that last 24 hours. Express yourself spontaneously with photos, videos, and text. Add filters, stickers, and effects to make your stories unique.</p>
                    </article>
                    <article class="card" role="listitem">
                        <div class="card-icon" aria-hidden="true"><i class="fas fa-message"></i></div>
                        <h3>Messages</h3>
                        <p>Private conversations with friends and family. Send text, photos, videos, and voice messages. Create group chats and stay connected with people who matter most.</p>
                    </article>
                    <article class="card" role="listitem">
                        <div class="card-icon" aria-hidden="true"><i class="fas fa-wand-magic-sparkles"></i></div>
                        <h3>(Menu-based) <br> AI Assistant</h3>
                        <p>Smart help for creating better content. Get AI-powered suggestions for captions, hashtags, and content ideas. Improve your posts with intelligent recommendations.</p>
                    </article>
                    <article class="card" role="listitem">
                        <div class="card-icon" aria-hidden="true"><i class="fas fa-globe"></i></div>
                        <h3>Explore</h3>
                        <p>Discover and connect with new people. Search for friends, find interesting profiles, and expand your network by exploring user accounts from around the world.</p>
                    </article>
                    <article class="card" role="listitem">
                        <div class="card-icon" aria-hidden="true"><i class="fas fa-lock"></i></div>
                        <h3>Privacy First</h3>
                        <p>You control who sees your content. Advanced privacy settings let you manage your audience with private accounts, close friends lists, and content controls.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="join-now" aria-labelledby="join-title">
            <div class="container">
                <h2 class="section-title" id="join-title">Join Now</h2>
                <p class="section-subtitle">Create your free account today and start connecting with friends.</p>
                <div style="display: flex; gap: 16px; flex-wrap: wrap; justify-content: center;">
                    <a href="{{ route('register') }}" class="btn btn-primary" aria-label="Create your free account"><i class="fas fa-user-plus" aria-hidden="true"></i> Create Account</a>
                    <a href="{{ route('login') }}" class="btn btn-ghost" aria-label="Sign in to your account"><i class="fas fa-sign-in-alt" aria-hidden="true"></i> Sign In</a>
                </div>
            </div>
        </section>
    </main>

    <footer role="contentinfo">
        <p>© {{ date('Y') }} <a href="/">Nexus</a>. All rights reserved.</p>
    </footer>
</body>
</html>