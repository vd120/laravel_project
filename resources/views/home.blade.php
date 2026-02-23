<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Nexus - A modern social platform to connect with friends, share moments, and discover new stories.">
    <title>Nexus - Share Your Story</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; }
        body { 
            font-family: 'Inter', -apple-system, sans-serif; 
            color: #fff; 
            background: #0a0a1a;
            overflow-x: hidden; 
        }

        /* Video background container */
        .video-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .video-container video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .video-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(10,10,26,0.7) 0%, rgba(10,10,26,0.5) 50%, rgba(10,10,26,0.8) 100%);
        }

        /* Header */
        nav { 
            position: fixed; 
            top: 0; 
            left: 0; 
            right: 0; 
            padding: 20px 40px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            z-index: 100; 
            background: linear-gradient(to bottom, rgba(0,0,0,0.5), transparent);
            transition: background 0.3s ease;
        }
        nav.scrolled {
            background: rgba(10, 10, 26, 0.95);
            backdrop-filter: blur(10px);
        }
        .logo { font-size: 20px; font-weight: 700; color: #fff; text-decoration: none; letter-spacing: -0.5px; opacity: 0; transform: translateY(-10px); transition: opacity 0.4s ease, transform 0.4s ease; }
        .logo.visible { opacity: 1; transform: translateY(0); }
        .logo span { color: #60a5fa; }

        /* Hero Title - Big Nexus */
        .hero-title-big {
            font-size: clamp(3.5rem, 12vw, 8rem);
            font-weight: 700;
            letter-spacing: -2px;
            margin-bottom: 16px;
            line-height: 1;
            background: linear-gradient(135deg, #60a5fa 0%, #a78bfa 50%, #f472b6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            transition: opacity 0.4s ease, transform 0.4s ease;
            text-shadow: 0 0 60px rgba(96, 165, 250, 0.3);
        }
        .hero-title-big.hidden {
            opacity: 0;
            transform: translateY(-20px) scale(0.95);
        }

        /* Hero */
        .hero { 
            min-height: 100vh; 
            display: flex; 
            flex-direction: column; 
            justify-content: center; 
            align-items: center; 
            text-align: center; 
            padding: 100px 20px; 
            position: relative;
            z-index: 1;
        }
        .hero h1 { font-size: clamp(1.5rem, 4vw, 2.5rem); font-weight: 600; letter-spacing: -0.5px; margin-bottom: 20px; line-height: 1.2; color: rgba(255,255,255,0.95); }
        .hero h1 span { background: linear-gradient(135deg, #60a5fa, #a78bfa); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .hero p { font-size: clamp(1rem, 2vw, 1.25rem); color: rgba(255,255,255,0.8); max-width: 500px; margin-bottom: 40px; font-weight: 400; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 14px 28px; font-size: 15px; font-weight: 600; border-radius: 50px; text-decoration: none; cursor: pointer; border: none; }
        .btn-primary { background: #fff; color: #000; }
        .btn-primary:hover { background: #f0f0f0; transform: scale(1.02); }
        .btn-ghost { background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.3); backdrop-filter: blur(10px); }
        .btn-ghost:hover { background: rgba(255,255,255,0.2); border-color: #fff; }

        /* About */
        .about { padding: 100px 20px; background: rgba(10,10,26,0.95); text-align: center; position: relative; z-index: 1; }
        .about-text { color: rgba(255,255,255,0.8); font-size: clamp(1rem, 2vw, 1.125rem); max-width: 700px; margin: 0 auto; line-height: 1.8; }

        /* Features */
        .features { padding: 100px 20px; background: rgba(15,15,35,0.98); position: relative; z-index: 1; }
        .container { max-width: 1100px; margin: 0 auto; }
        .section-title { font-size: clamp(1.75rem, 4vw, 2.5rem); font-weight: 700; text-align: center; margin-bottom: 50px; letter-spacing: -1px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; }
        .card { padding: 32px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 20px; transition: all 0.3s; }
        .card:hover { background: rgba(255,255,255,0.08); transform: translateY(-5px); }
        .card-icon { width: 48px; height: 48px; background: linear-gradient(135deg, #60a5fa, #a78bfa); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 20px; }
        .card h3 { font-size: 1.125rem; font-weight: 600; margin-bottom: 8px; }
        .card p { color: rgba(255,255,255,0.6); font-size: 0.875rem; line-height: 1.6; }

        /* Join Now */
        .join-now { padding: 100px 20px; background: linear-gradient(180deg, rgba(10,10,26,0.95) 0%, rgba(20,20,50,0.98) 100%); text-align: center; position: relative; z-index: 1; }
        .section-subtitle { color: rgba(255,255,255,0.6); text-align: center; margin-bottom: 40px; max-width: 500px; margin-left: auto; margin-right: auto; }

        /* Footer */
        footer { padding: 40px 20px; text-align: center; border-top: 1px solid rgba(255,255,255,0.05); background: #0a0a1a; position: relative; z-index: 1; }
        footer p { color: rgba(255,255,255,0.4); font-size: 0.875rem; }
        footer a { color: #60a5fa; text-decoration: none; }

        @media (max-width: 768px) {
            nav { padding: 16px 20px; }
            .hero { padding: 80px 20px; }
            .features { padding: 60px 20px; }
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <!-- Video Background -->
    <div class="video-container">
        <video autoplay muted loop playsinline preload="none" id="bgVideo" poster="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1920 1080'%3E%3Crect fill='%230a0a1a' width='1920' height='1080'/%3E%3C/svg%3E">
            <source src="https://cdn.pixabay.com/video/2021/12/10/100221-657132594_small.mp4" type="video/mp4">
        </video>
        <div class="video-overlay"></div>
    </div>

    <nav role="navigation" aria-label="Main navigation">
        <a href="/" class="logo">Nexus</a>
    </nav>

    <main id="main-content">
        <section class="hero" aria-labelledby="hero-title">
            <h2 class="hero-title-big" id="nexus-title">Nexus</h2>
            <h1 id="hero-title">Where <span>Stories</span> Come to Life</h1>
            <p>Your space to connect, create, and share the moments that matter most. Join a community where every story finds its audience.</p>
            <div style="display: flex; gap: 12px; flex-wrap: wrap; justify-content: center;">
                <a href="{{ route('register') }}" class="btn btn-primary">Start Your Journey</a>
                <a href="{{ route('login') }}" class="btn btn-ghost">Welcome Back</a>
            </div>
        </section>

        <section class="about" aria-labelledby="about-title">
            <div class="container">
                <h2 class="section-title" id="about-title">More Than Just a Platform</h2>
                <p class="about-text">Nexus is where real connections happen. Whether you're sharing everyday moments or life's biggest milestones, we give you the tools to express yourself authentically. Built for creators, dreamers, and everyone in between — your story deserves to be told.</p>
            </div>
        </section>

        <section class="features" aria-labelledby="features-title">
            <div class="container">
                <h2 class="section-title" id="features-title">Built for You</h2>
                <p class="section-subtitle">Everything you need to share, connect, and grow — all in one place.</p>
                <div class="grid" role="list">
                    <article class="card" role="listitem">
                        <div class="card-icon"><i class="fas fa-photo-film"></i></div>
                        <h3>Rich Media Sharing</h3>
                        <p>Post stunning photos and videos that capture your world in vivid detail.</p>
                    </article>
                    <article class="card" role="listitem">
                        <div class="card-icon"><i class="fas fa-bolt"></i></div>
                        <h3>24-Hour Stories</h3>
                        <p>Share spontaneous moments that disappear after a day. Fun, fast, and fleeting.</p>
                    </article>
                    <article class="card" role="listitem">
                        <div class="card-icon"><i class="fas fa-message"></i></div>
                        <h3>Direct Messages</h3>
                        <p>Private, secure conversations with the people who matter most to you.</p>
                    </article>
                    <article class="card" role="listitem">
                        <div class="card-icon"><i class="fas fa-wand-magic-sparkles"></i></div>
                        <h3>AI-Powered Creation</h3>
                        <p>Let our smart assistant help you craft the perfect post and find the right words.</p>
                    </article>
                    <article class="card" role="listitem">
                        <div class="card-icon"><i class="fas fa-globe"></i></div>
                        <h3>Discover & Explore</h3>
                        <p>Find inspiring content and connect with people who share your passions.</p>
                    </article>
                    <article class="card" role="listitem">
                        <div class="card-icon"><i class="fas fa-lock"></i></div>
                        <h3>Your Privacy, Your Rules</h3>
                        <p>Full control over your content. Share with the world or keep it close.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="join-now" aria-labelledby="join-title">
            <div class="container">
                <h2 class="section-title" id="join-title">Ready to Begin?</h2>
                <p class="section-subtitle">Join millions already sharing their stories. Your next chapter starts here.</p>
                <div style="display: flex; gap: 16px; flex-wrap: wrap; justify-content: center;">
                    <a href="{{ route('register') }}" class="btn btn-primary"><i class="fas fa-rocket"></i> Create Free Account</a>
                    <a href="{{ route('login') }}" class="btn btn-ghost"><i class="fas fa-sign-in-alt"></i> Sign In</a>
                </div>
            </div>
        </section>
    </main>

    <footer role="contentinfo">
        <p>&copy; {{ date('Y') }} <a href="/">Nexus</a>. All rights reserved.</p>
    </footer>

    <script>
        // Scroll effect for title transition
        const nav = document.querySelector('nav');
        const logo = document.querySelector('.logo');
        const nexusTitle = document.getElementById('nexus-title');
        
        // Threshold for when to show/hide elements (in pixels)
        const scrollThreshold = 100;
        
        function handleScroll() {
            const scrollY = window.scrollY;
            
            if (scrollY > scrollThreshold) {
                // User has scrolled down - show header logo, hide big title
                nav.classList.add('scrolled');
                logo.classList.add('visible');
                nexusTitle.classList.add('hidden');
            } else {
                // User is at top - hide header logo, show big title
                nav.classList.remove('scrolled');
                logo.classList.remove('visible');
                nexusTitle.classList.remove('hidden');
            }
        }
        
        // Listen for scroll events with throttling for performance
        let ticking = false;
        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(function() {
                    handleScroll();
                    ticking = false;
                });
                ticking = true;
            }
        });
        
        // Initial check on page load
        handleScroll();
    </script>

</body>
</html>
