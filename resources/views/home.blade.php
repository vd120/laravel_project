<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nexus — Your Social Platform</title>

<!-- Preconnect to external resources -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="dns-prefetch" href="https://fonts.googleapis.com">
<link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<!-- GSAP Libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔷</text></svg>">
<style>
:root{
    --primary:#5e60ce;
    --primary-light:#7400b8;
    --text-primary:#ffffff;
    --text-secondary:#98989f;
    --bg-main:#000000;
    --card-bg:rgba(30,30,32,0.6);
    --card-border:rgba(255,255,255,0.06);
    --nav-bg:rgba(0,0,0,0.1);
    --nav-border:rgba(255,255,255,0.05);
    --footer-text:#636366;
}

[data-theme="light"]{
    --primary:#5e60ce;
    --primary-light:#7400b8;
    --text-primary:#1a1a1a;
    --text-secondary:#555555;
    --bg-main:#f5f5f7;
    --card-bg:rgba(255,255,255,0.9);
    --card-border:rgba(0,0,0,0.08);
    --nav-bg:rgba(255,255,255,0.85);
    --nav-border:rgba(0,0,0,0.08);
    --footer-text:#86868b;
}

*{margin:0;padding:0;box-sizing:border-box;}
html { scroll-behavior: smooth; scroll-padding-top: 80px; }

body{
    font-family:'Inter',sans-serif;
    background:var(--bg-main);
    color:var(--text-primary);
    overflow-x:hidden;
    -webkit-font-smoothing:antialiased;
    transition: background 0.3s ease, color 0.3s ease;
}

.menu-toggle {
    display: none;
    flex-direction: column;
    gap: 5px;
    cursor: pointer;
    padding: 5px;
}
.menu-toggle span {
    width: 25px;
    height: 2px;
    background: #f5f5f7;
    transition: all 0.3s;
}

.animated-bg {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -1;
    background: var(--bg-main);
    will-change: transform;
}

nav{
    position:fixed;
    top:0;
    width:100%;
    padding:20px 40px;
    backdrop-filter:blur(20px);
    -webkit-backdrop-filter:blur(20px);
    background:var(--nav-bg);
    z-index:1000;
    display:flex;
    justify-content:center;
    border-bottom:1px solid var(--nav-border);
}
.nav-container{max-width:1200px;width:100%;display:flex;justify-content:space-between;align-items:center;}
nav a{color:var(--text-primary);text-decoration:none;font-size:14px;font-weight:500;opacity:0.8;transition:opacity 0.3s;}
nav a:hover{opacity:1}
.nav-brand{font-weight:700;font-size:20px;opacity:1;}
.nav-links { display: flex; gap: 30px; align-items: center; }

#themeToggle {
    background:transparent;
    border:1px solid rgba(255,255,255,0.2);
    border-radius:50%;
    width:40px;
    height:40px;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    transition:all 0.3s ease;
    color: var(--text-primary);
}
#themeToggle:hover {
    background: rgba(255,255,255,0.1);
    transform: scale(1.05);
}
[data-theme="light"] #themeToggle {
    border-color: rgba(0,0,0,0.2);
}
[data-theme="light"] #themeToggle:hover {
    background: rgba(0,0,0,0.05);
}

.lang-toggle {
    background:transparent;
    border:1px solid rgba(255,255,255,0.2);
    border-radius:20px;
    padding:8px 14px;
    cursor:pointer;
    display:flex;
    align-items:center;
    gap:6px;
    transition:all 0.3s ease;
    color: var(--text-primary);
    font-size:13px;
    font-weight:600;
}
.lang-toggle:hover {
    background: rgba(255,255,255,0.1);
    border-color: rgba(255,255,255,0.3);
}
[data-theme="light"] .lang-toggle {
    border-color: rgba(0,0,0,0.2);
}
[data-theme="light"] .lang-toggle:hover {
    background: rgba(0,0,0,0.05);
}
.lang-divider {
    opacity: 0.5;
    font-weight: 300;
}
.lang-alt {
    opacity: 0.7;
    font-family: 'Arial', sans-serif;
}

/* RTL Support */
html[lang="ar"] {
    direction: rtl;
    font-family: 'Cairo', 'Inter', sans-serif;
}
html[lang="ar"] body {
    font-family: 'Cairo', 'Inter', sans-serif;
}
html[lang="ar"] .nav-links {
    text-align: right;
}
html[lang="ar"] .hero h2,
html[lang="ar"] .fade-content p,
html[lang="ar"] .cta-desc,
html[lang="ar"] .card p,
html[lang="ar"] .typewriter,
html[lang="ar"] footer {
    direction: rtl;
    unicode-bidi: embed;
}
html[lang="ar"] .list-effect {
    text-align: right;
    padding-right: 10%;
    padding-left: 0;
}
html[lang="ar"] .list-item {
    flex-direction: row-reverse;
}

@media(max-width:768px){
    #themeToggle:hover {
        background: transparent !important;
        transform: none !important;
    }
}

.menu-toggle {
    display: none;
    flex-direction: column;
    gap: 5px;
    cursor: pointer;
    padding: 5px;
}
.menu-toggle span {
    width: 25px;
    height: 2px;
    background: var(--text-primary);
    transition: all 0.3s;
}

.hero{
    height:100vh;
    position:relative;
    overflow:hidden;
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    text-align:center;
    padding:0 20px;
    background:transparent;
}

.hero-content{position:relative;z-index:1;max-width:900px;}
.hero-content h2{opacity:0;transform:translateY(20px);}
#nexus-title{opacity:0;transform:translateY(20px);}
.hero h1{font-size:clamp(4rem, 10vw, 8rem);font-weight:700;letter-spacing:-0.03em;line-height:1;margin-bottom:20px;color:var(--text-primary);}
.hero h2{font-size:clamp(1.2rem,2.5vw,1.8rem);font-weight:400;color:var(--text-secondary);margin-bottom:40px;}
.hero-cta{display:flex;gap:20px;justify-content:center;}
.hero-cta-btn{
    padding:16px 32px;border-radius:980px;font-size:16px;font-weight:600;text-decoration:none;
    transition:all 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
}
.hero-cta-btn-primary{background:#fff;color:#000;box-shadow:0 10px 30px rgba(255,255,255,0.15);}
.hero-cta-btn-primary:hover{transform:scale(1.05);box-shadow:0 15px 40px rgba(255,255,255,0.2);}
.hero-cta-btn-secondary{background:rgba(255,255,255,0.1);color:#fff;border:1px solid rgba(255,255,255,0.2);}
.hero-cta-btn-secondary:hover{background:rgba(255,255,255,0.15);}
.pin-section {
    height: 100vh;
    width: 100%;
    position: relative;
    overflow: hidden;
}
.pin-container {
    height: 100%; width: 100%; display: flex; flex-direction: column;
    justify-content: center; align-items: center; text-align: center;
    background: transparent; padding: 0 20px; position: relative;
}
.fade-content h2 { font-size: clamp(3rem, 6vw, 5rem); font-weight: 700; margin-bottom: 20px; background: linear-gradient(135deg, var(--text-primary), #888); -webkit-background-clip: text; -webkit-text-fill-color: transparent; opacity: 0; transform: translateY(50px); }
.fade-content p { font-size: clamp(1.2rem, 2vw, 1.5rem); color: var(--text-secondary); max-width: 600px; line-height: 1.5; opacity: 0; transform: translateY(30px); }
.carousel { position: relative; height: 120px; width: 100%; display: flex; justify-content: center; align-items: center; }
.word-line { position: absolute; font-size: clamp(3rem, 8vw, 6rem); font-weight: 700; opacity: 0.2; color: var(--text-primary); }
.list-effect { display: flex; flex-direction: column; gap: 40px; width: 100%; max-width: 800px; text-align: left; padding-left: 10%; }
.list-title { font-size: clamp(2rem, 4vw, 3rem); font-weight: 700; color: var(--text-primary); margin-bottom: 30px; text-align: center; width: 100%; }
.list-item { display: flex; align-items: center; gap: 20px; font-size: clamp(1.5rem, 3vw, 2rem); font-weight: 500; color: var(--text-primary); opacity: 0; transform: translateX(-30px); }
.list-bullet { width: 12px; height: 12px; background: var(--primary); border-radius: 50%; box-shadow: 0 0 15px var(--primary); }
.blur-text { font-size: clamp(3rem, 7vw, 5.5rem); font-weight: 700; filter: blur(15px); opacity: 0; color: var(--text-primary); text-align: center; line-height: 1.1; }
.typewriter {
    font-size: clamp(1.4rem, 2.5vw, 2rem);
    font-weight: 400;
    color: var(--text-primary);
    font-family: 'Inter', monospace;
    letter-spacing: 0.01em;
    max-width: 800px;
    line-height: 1.6;
    text-align: center;
}
.typewriter span {
    display: inline;
}

/* Growing Section Styles */
#section-growing .text-line {
    font-size: clamp(2rem, 5vw, 4rem);
    font-weight: 700;
    color: var(--text-primary);
    opacity: 0;
    margin: 8px 0;
    text-align: center;
}
#section-growing .growing-title {
    font-size: clamp(1.5rem, 3vw, 2.5rem) !important;
    color: var(--text-primary) !important;
    margin-bottom: 20px !important;
}
#section-growing .stat-row {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}
#section-growing .stat-number {
    font-size: clamp(2.5rem, 6vw, 5rem);
    font-weight: 700;
    color: var(--text-primary);
    font-variant-numeric: tabular-nums;
}
#section-growing .stat-label {
    font-size: clamp(1rem, 2vw, 1.5rem);
    font-weight: 500;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.1em;
}

.features-grid-section {
    padding: 120px 20px;
    background: transparent;
    position: relative;
    z-index: 2;
}
.section-label { font-size: 18px; font-weight: 600; color: var(--primary-light); margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.1em; text-align:center; }
.section-title { font-size: clamp(2.5rem, 5vw, 4rem); font-weight: 700; margin-bottom: 60px; color: var(--text-primary); }

.feature-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    max-width: 1200px;
    margin: 0 auto;
}

.card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 24px;
    padding: 40px;
    text-align: left;
    position: relative;
    overflow: hidden;
    transition: transform 0.4s ease, border-color 0.4s ease, background 0.3s ease;
}
.card:hover {
    border-color: rgba(255,255,255,0.15);
    transform: translateY(-5px);
}
.card-glow {
    position: absolute; top: 0; left: 0; width: 100%; height: 100%;
    background: radial-gradient(circle at 50% 0%, rgba(94,96,206,0.15) 0%, transparent 70%);
    opacity: 0; transition: opacity 0.4s ease;
}
.card:hover .card-glow { opacity: 1; }

.card-icon {
    width: 56px; height: 56px; border-radius: 16px; display: flex;
    align-items: center; justify-content: center; margin-bottom: 25px; position: relative; z-index: 2;
}
.card h3 { font-size: 24px; font-weight: 600; margin-bottom: 12px; color: var(--text-primary); position: relative; z-index: 2; }
.card p { font-size: 16px; color: var(--text-secondary); line-height: 1.6; position: relative; z-index: 2; }
.cta-section {
    min-height: 80vh;
    display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;
    background: transparent;
    padding: 100px 20px; position: relative; overflow: hidden;
}
.cta-content { position: relative; z-index: 2; }
.cta-section h2 { font-size: clamp(3rem, 6vw, 5rem); font-weight: 700; margin-bottom: 20px; opacity: 0; transform: translateY(30px); color: var(--text-primary); }
.cta-section p { font-size: clamp(1.2rem, 2vw, 1.5rem); color: var(--text-secondary); margin-bottom: 50px; opacity: 0; transform: translateY(20px); }
.cta-buttons { display: flex; gap: 20px; justify-content: center; flex-wrap: wrap; opacity: 0; }
.cta-btn { padding: 18px 40px; border-radius: 980px; font-size: 18px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; }
.cta-btn-primary { background: var(--primary); color: #fff; box-shadow: 0 10px 30px rgba(94, 96, 206, 0.3); }
.cta-btn-primary:hover { transform: translateY(-3px); box-shadow: 0 15px 40px rgba(94, 96, 206, 0.5); }
.cta-btn-secondary { background: transparent; border: 2px solid var(--text-primary); color: var(--text-primary); opacity: 0.7; }
.cta-btn-secondary:hover { background: var(--card-bg); border-color: var(--text-primary); opacity: 1; }

footer{background:transparent;padding:50px 20px;font-size:15px;color:var(--text-secondary);text-align:center;border-top:1px solid var(--nav-border);}
footer a { color: var(--text-secondary); transition: color 0.3s ease; }
footer a:hover { color: var(--primary); }

@media(max-width:768px){
    nav{padding:15px 20px}
    .hero-cta{flex-direction:column;align-items:center}
    .list-effect { padding: 0 20px; }
    .cta-buttons { flex-direction: column; align-items: center; }
    .menu-toggle { display: flex; }
    #themeToggle { width: 36px; height: 36px; }
    #themeToggle svg { width: 18px; height: 18px; }
    .lang-toggle { padding: 6px 10px; font-size: 12px; }
    .nav-links {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        flex-direction: column;
        background: rgba(0,0,0,0.85);
        backdrop-filter: blur(30px) saturate(180%);
        -webkit-backdrop-filter: blur(30px) saturate(180%);
        padding: 25px 20px;
        gap: 15px;
        opacity: 0;
        pointer-events: none;
        transform: translateY(-10px);
        transition: all 0.3s ease;
        border-top: 1px solid var(--nav-border);
        border-bottom: 1px solid var(--nav-border);
    }
    [data-theme="light"] .nav-links {
        background: rgba(255,255,255,0.85);
    }
    html[lang="ar"] .nav-links {
        text-align: right;
        direction: rtl;
    }
    .nav-links.active {
        opacity: 1;
        pointer-events: all;
        transform: translateY(0);
    }
    .nav-links a {
        display: block;
        padding: 12px 16px;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    .nav-links a:hover {
        background: rgba(255,255,255,0.05);
    }
    [data-theme="light"] .nav-links a:hover {
        background: rgba(0,0,0,0.05);
    }
}
</style>
</head>
<body>

<div class="animated-bg"></div>
<nav>
    <div class="nav-container">
        <a href="/" class="nav-brand">Nexus</a>
        <div style="display:flex;align-items:center;gap:20px;">
            <button id="langToggle" class="lang-toggle" title="Switch language">
                <span class="lang-current">EN</span>
                <span class="lang-divider">|</span>
                <span class="lang-alt">ع</span>
            </button>
            <button id="themeToggle" title="Toggle theme">
                <svg class="theme-icon sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                <svg class="theme-icon moon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
            </button>
            <div class="nav-links" id="navLinks">
                <a href="#section-features" data-en="Features" data-ar="المميزات">Features</a>
                <a href="#section-cta" data-en="Join" data-ar="انضم">Join</a>
            </div>
            <div class="menu-toggle" id="menuToggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </div>
</nav>
<section class="hero">
    <div class="hero-content">
        <h1 id="nexus-title">Nexus</h1>
        <h2>Connect. Share. Belong. Experience social media as it should be—authentic, private, and yours.</h2>
    </div>
</section>
<section class="pin-section" id="section-fade">
    <div class="pin-container">
        <div class="fade-content">
            <h2 class="e1-title">Built for Real Connections</h2>
            <p class="e1-desc">Nexus brings together everything you need to connect, share, and build community. No algorithms feeding you outrage. No ads selling your attention. Just genuine interaction with the people who matter.</p>
        </div>
    </div>
</section>
<section class="features-grid-section" id="section-features">
    <p class="section-label">Features</p>
    <h2 class="section-title">Everything you need. Nothing you don't.</h2>

    <div class="feature-grid">
        <div class="card">
            <div class="card-glow"></div>
            <div class="card-icon" style="background:linear-gradient(135deg,#a855f7 0%,#d946ef 100%);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
            </div>
            <h3 class="feature-card-title">Posts</h3>
            <p class="feature-card-desc">Share your thoughts, updates, and moments with your network. A chronological feed that shows what matters to you—no manipulation, no hidden agendas.</p>
        </div>
        <div class="card">
            <div class="card-glow"></div>
            <div class="card-icon" style="background:linear-gradient(135deg,#10b981 0%,#34d399 100%);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
            </div>
            <h3 class="feature-card-title">Stories</h3>
            <p class="feature-card-desc">Capture fleeting moments with ephemeral content that disappears after 24 hours. Share freely without the pressure of permanence.</p>
        </div>
        <div class="card">
            <div class="card-glow"></div>
            <div class="card-icon" style="background:linear-gradient(135deg,#10b981 0%,#34d399 100%);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <h3 class="feature-card-title">Private Chat</h3>
            <p class="feature-card-desc">Real-time messaging with end-to-end encryption. Your conversations stay between you and your recipients—secure, fast, and private.</p>
        </div>
        <div class="card">
            <div class="card-glow"></div>
            <div class="card-icon" style="background:linear-gradient(135deg,#3b82f6 0%,#60a5fa 100%);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <h3 class="feature-card-title">Groups</h3>
            <p class="feature-card-desc">Create or join communities around your interests. From hobby groups to professional networks, build spaces that bring people together.</p>
        </div>
        <div class="card">
            <div class="card-glow"></div>
            <div class="card-icon" style="background:linear-gradient(135deg,#ec4899 0%,#f472b6 100%);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M12 2a2 2 0 0 1 2 2c0 .74-.4 1.39-1 1.73V7h1a7 7 0 0 1 7 7h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h1a7 7 0 0 1 7-7h1V5.73c-.6-.34-1-.99-1-1.73a2 2 0 0 1 2-2z"/><path d="M9 12a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/><path d="M15 12a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/></svg>
            </div>
            <h3 class="feature-card-title">AI Assistant</h3>
            <p class="feature-card-desc">Smart tools to enhance your experience. Get help drafting posts, summarizing conversations, and discovering content that matters to you.</p>
        </div>
        <div class="card">
            <div class="card-glow"></div>
            <div class="card-icon" style="background:linear-gradient(135deg,#f59e0b 0%,#fbbf24 100%);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <h3 class="feature-card-title">Privacy First</h3>
            <p class="feature-card-desc">Your data stays yours. No tracking, no selling to advertisers, no manipulation. We're funded by users, not by selling your attention.</p>
        </div>
    </div>
</section>
<section class="pin-section" id="section-carousel">
    <div class="pin-container">
        <div class="carousel">
            <div class="word-line" id="word1">Post.</div>
            <div class="word-line" id="word2">Share.</div>
            <div class="word-line" id="word3">Connect.</div>
            <div class="word-line" id="word4">Belong.</div>
        </div>
    </div>
</section>
<section class="pin-section" id="section-list">
    <div class="pin-container">
        <h2 class="list-title" data-en="Why Choose Nexus?" data-ar="لماذا تختار Nexus؟">Why Choose Nexus?</h2>
        <div class="list-effect">
            <div class="list-item e4-item1"><span class="list-bullet"></span><span>No Ads. Ever.</span></div>
            <div class="list-item e4-item2"><span class="list-bullet"></span><span>Chronological Feed</span></div>
            <div class="list-item e4-item3"><span class="list-bullet"></span><span>Your Data, Your Control</span></div>
            <div class="list-item e4-item4"><span class="list-bullet"></span><span>End-to-End Encryption</span></div>
            <div class="list-item e4-item5"><span class="list-bullet"></span><span>Community-Driven</span></div>
        </div>
    </div>
</section>
<section class="pin-section" id="section-blur">
    <div class="pin-container">
        <div class="blur-text e6-blur">Your Privacy.<br>Protected.</div>
    </div>
</section>
<section class="pin-section" id="section-type">
    <div class="pin-container">
        <div class="typewriter e12-type">Nexus is built on a simple belief: social media should bring people together, not drive them apart. No algorithms deciding what you see. No ads interrupting your moments. Just you, your posts, your stories, and your community. Welcome to social media as it should be.</div>
    </div>
</section>
<section class="pin-section" id="section-growing">
    <div class="pin-container">
        <div class="text-line growing-title" data-en="Join Our Rapidly Growing Community" data-ar="انضم إلى مجتمعنا سريع النمو">Join Our Rapidly Growing Community</div>
        <div class="text-line stat-row">
            <span class="stat-number" data-en="+50K" data-ar="+50 ألف">+50K</span>
            <span class="stat-label" data-en="Users" data-ar="مستخدم">Users</span>
        </div>
        <div class="text-line stat-row">
            <span class="stat-number" data-en="+120K" data-ar="+120 ألف">+120K</span>
            <span class="stat-label" data-en="Posts" data-ar="منشور">Posts</span>
        </div>
        <div class="text-line stat-row">
            <span class="stat-number" data-en="+85K" data-ar="+85 ألف">+85K</span>
            <span class="stat-label" data-en="Stories" data-ar="قصة">Stories</span>
        </div>
    </div>
</section>
<section class="cta-section" id="section-cta">
    <div class="cta-content">
        <h2 class="cta-title">Your community awaits.</h2>
        <p class="cta-desc">Join Nexus today and experience social media done right.</p>
        <div class="cta-buttons">
            <a href="/register" class="cta-btn cta-btn-primary">Get Started Free</a>
            <a href="/login" class="cta-btn cta-btn-secondary">Sign In</a>
        </div>
    </div>
</section>

<footer>
    <div class="footer-content">
        <p>© 2026 Nexus. Built for authentic connections.</p>
        <div style="display:flex;justify-content:center;gap:20px;margin-top:20px;flex-wrap:wrap;">
            <a href="https://github.com/vd120/laravel_project" target="_blank" rel="noopener noreferrer" style="color:var(--text-secondary);text-decoration:none;font-size:14px;display:flex;align-items:center;gap:8px;font-weight:500;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                GitHub
            </a>
            <a href="mailto:socialapp.noreply@gmail.com" style="color:var(--text-secondary);text-decoration:none;font-size:14px;display:flex;align-items:center;gap:8px;font-weight:500;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 6l-10 7L2 6"/></svg>
                Contact Support
            </a>
        </div>
    </div>
</footer>

<script>
// Apply saved theme immediately (before page loads)
(function() {
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
})();

window.addEventListener('load', function() {
gsap.registerPlugin(ScrollTrigger);

// Cache DOM queries
const navLinks = document.getElementById('navLinks');
const themeToggle = document.getElementById('themeToggle');
const sunIcon = themeToggle.querySelector('.sun');
const moonIcon = themeToggle.querySelector('.moon');
const langToggle = document.getElementById('langToggle');
const langCurrent = langToggle?.querySelector('.lang-current');
let currentLang = localStorage.getItem('lang') || 'en';

const englishContent = {
    hero: { title: 'Nexus', subtitle: 'Connect. Share. Belong. Experience social media as it should be—authentic, private, and yours.' },
    fade: { title: 'Built for Real Connections', desc: 'Nexus brings together everything you need to connect, share, and build community. No algorithms feeding you outrage. No ads selling your attention. Just genuine interaction with the people who matter.' },
    carousel: { word1: 'Post.', word2: 'Share.', word3: 'Connect.', word4: 'Belong.' },
    features: {
        label: 'Features',
        title: 'Everything you need. Nothing you don\'t.',
        cards: [
            { title: 'Posts', desc: 'Share your thoughts, updates, and moments with your network. A chronological feed that shows what matters to you—no manipulation, no hidden agendas.' },
            { title: 'Stories', desc: 'Capture fleeting moments with ephemeral content that disappears after 24 hours. Share freely without the pressure of permanence.' },
            { title: 'Private Chat', desc: 'Real-time messaging with end-to-end encryption. Your conversations stay between you and your recipients—secure, fast, and private.' },
            { title: 'Groups', desc: 'Create or join communities around your interests. From hobby groups to professional networks, build spaces that bring people together.' },
            { title: 'AI Assistant', desc: 'Smart tools to enhance your experience. Get help drafting posts, summarizing conversations, and discovering content that matters to you.' },
            { title: 'Privacy First', desc: 'Your data stays yours. No tracking, no selling to advertisers, no manipulation. We\'re funded by users, not by selling your attention.' }
        ]
    },
    listTitle: 'Why Choose Nexus?',
    list: ['No Ads. Ever.', 'Chronological Feed', 'Your Data, Your Control', 'End-to-End Encryption', 'Community-Driven'],
    blur: 'Your Privacy.<br>Protected.',
    type: 'Nexus is built on a simple belief: social media should bring people together, not drive them apart. No algorithms deciding what you see. No ads interrupting your moments. Just you, your posts, your stories, and your community. Welcome to social media as it should be.',
    cta: { title: 'Your community awaits.', desc: 'Join Nexus today and experience social media done right.', btn1: 'Get Started Free', btn2: 'Sign In' },
    footer: { copyright: '© 2026 Nexus. Built for authentic connections.' }
};

const arabicContent = {
    hero: { title: 'Nexus', subtitle: 'تواصل. شارك. انتمِ. اختبر وسائل التواصل كما يجب أن تكون—أصيلة، خاصة، ولك وحدك.' },
    fade: { title: 'صُمم للتواصل الحقيقي', desc: 'Nexus يجمع كل ما تحتاجه للتواصل وبناء المجتمع. لا خوارزميات تثير غضبك. لا إعلانات تبيع انتباهك. فقط تفاعل حقيقي مع الأشخاص الذين يهتمون لأمرهم.' },
    carousel: { word1: 'انشر.', word2: 'شارك.', word3: 'تواصل.', word4: 'انتمِ.' },
    features: {
        label: 'المميزات',
        title: 'كل ما تحتاجه. بدون أي زوائد.',
        cards: [
            { title: 'المنشورات', desc: 'شارك أفكارك وتحديثاتك ولحظاتك مع شبكتك. خلاصة زمنية تعرض ما يهمك حقًا—بدون تلاعب أو أجندات خفية.' },
            { title: 'القصص', desc: 'التقط اللحظات العابرة بمحتوى مؤقت يختفي بعد 24 ساعة. شارك بحرية بدون ضغط الديمومة.' },
            { title: 'الدردشة الخاصة', desc: 'مراسلة فورية مع تشفير كامل. محادثاتك تبقى بينك وبين المستلمين—آمنة، سريعة، وخاصة.' },
            { title: 'المجموعات', desc: 'أنشئ أو انضم لمجتمعات حول اهتماماتك. من مجموعات الهوايات إلى الشبكات المهنية، ابنِ مساحات تجمع الناس معًا.' },
            { title: 'مساعد الذكاء الاصطناعي', desc: 'أدوات ذكية لتعزيز تجربتك. احصل على مساعدة في صياغة المنشورات، وتلخيص المحادثات، واكتشاف المحتوى الذي يهمك.' },
            { title: 'الخصوصية أولاً', desc: 'بياناتك تبقى لك. لا تتبع، لا بيع للمعلنين، لا تلاعب. نحن ممولون من المستخدمين، لا من بيع انتباهك.' }
        ]
    },
    listTitle: 'لماذا تختار Nexus؟',
    list: ['بدون إعلانات. أبدًا.', 'خلاصة زمنية', 'بياناتك، تحت سيطرتك', 'تشفير كامل', 'مدعوم من المجتمع'],
    blur: 'خصوصيتك.<br>محمية.',
    type: 'Nexus مبني على اعتقاد بسيط: وسائل التواصل يجب أن تجمع الناس، لا أن تفرقهم. لا خوارزميات تقرر ما ترى. لا إعلانات تقاطع لحظاتك. فقط أنت، منشوراتك، قصصك، ومجتمعك. مرحبًا بك في وسائل التواصل كما يجب أن تكون.',
    cta: { title: 'مجتمعك ينتظرك.', desc: 'انضم إلى Nexus اليوم واختبر وسائل التواصل كما يجب أن تكون.', btn1: 'ابدأ مجانًا', btn2: 'تسجيل الدخول' },
    footer: { copyright: '© 2026 Nexus. صُمم للتواصل الأصيل.' }
};

// Theme toggle functionality
function updateThemeIcons(theme) {
    if (theme === 'light') {
        sunIcon.style.display = 'block';
        moonIcon.style.display = 'none';
        themeToggle.style.borderColor = 'rgba(0,0,0,0.2)';
    } else {
        sunIcon.style.display = 'none';
        moonIcon.style.display = 'block';
        themeToggle.style.borderColor = 'rgba(255,255,255,0.2)';
    }
    themeToggle.style.color = 'var(--text-primary)';
}

// Initialize theme icons
updateThemeIcons(document.documentElement.getAttribute('data-theme'));

// Theme toggle click handler
if (themeToggle) {
    themeToggle.addEventListener('click', function() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';

        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcons(newTheme);
    });
}

// Language toggle click handler
if (langToggle) {
    langToggle.addEventListener('click', function() {
        const newLang = currentLang === 'en' ? 'ar' : 'en';
        // Save current scroll position and language preference
        localStorage.setItem('lang', newLang);
        localStorage.setItem('scrollPosition', window.scrollY);
        // Reload page to apply language change with fresh animations
        window.location.reload();
    });
}

function updateLanguage(lang) {
    currentLang = lang;
    localStorage.setItem('lang', lang);
    document.documentElement.setAttribute('lang', lang);

    // Update toggle button
    if (langCurrent) {
        if (lang === 'ar') {
            langCurrent.textContent = 'ع';
            langToggle.querySelector('.lang-alt').textContent = 'EN';
        } else {
            langCurrent.textContent = 'EN';
            langToggle.querySelector('.lang-alt').textContent = 'ع';
        }
    }

    // Update all elements with data-en/data-ar attributes
    document.querySelectorAll('[data-en][data-ar]').forEach(el => {
        el.textContent = el.getAttribute(`data-${lang}`);
    });

    // Update specific sections
    updateContentForLanguage(lang);
}

function updateContentForLanguage(lang) {
    const content = lang === 'ar' ? arabicContent : englishContent;
    document.querySelector('#nexus-title').textContent = content.hero.title;
    document.querySelector('.hero-content h2').textContent = content.hero.subtitle;
    document.querySelector('.e1-title').textContent = content.fade.title;
    document.querySelector('.e1-desc').textContent = content.fade.desc;
    document.getElementById('word1').textContent = content.carousel.word1;
    document.getElementById('word2').textContent = content.carousel.word2;
    document.getElementById('word3').textContent = content.carousel.word3;
    document.getElementById('word4').textContent = content.carousel.word4;
    document.querySelector('.section-label').textContent = content.features.label;
    document.querySelector('.section-title').textContent = content.features.title;
    document.querySelectorAll('.feature-card-title').forEach((el, i) => {
        el.textContent = content.features.cards[i].title;
    });
    document.querySelectorAll('.feature-card-desc').forEach((el, i) => {
        el.textContent = content.features.cards[i].desc;
    });
    document.querySelector('.list-title').textContent = content.listTitle;
    document.querySelectorAll('.list-item span:last-child').forEach((el, i) => {
        el.textContent = content.list[i];
    });
    document.querySelector('.e6-blur').innerHTML = content.blur;
    document.querySelector('.e12-type').textContent = content.type;
    document.querySelector('.cta-title').textContent = content.cta.title;
    document.querySelector('.cta-desc').textContent = content.cta.desc;
    document.querySelector('.cta-btn-primary').textContent = content.cta.btn1;
    document.querySelector('.cta-btn-secondary').textContent = content.cta.btn2;
    document.querySelector('footer p').textContent = content.footer.copyright;
}

// Initialize language
updateLanguage(currentLang);

// Restore scroll position after language change
const savedScrollPosition = localStorage.getItem('scrollPosition');
if (savedScrollPosition) {
    setTimeout(() => {
        window.scrollTo(0, parseInt(savedScrollPosition));
        localStorage.removeItem('scrollPosition');
    }, 100);
}

// Refresh ScrollTrigger to ensure all triggers are calculated
ScrollTrigger.refresh();


// Hero fade in - Nexus title first, then subtitle
gsap.to('#nexus-title', { opacity: 1, y: 0, duration: 1, ease: 'power3.out', delay: 0.2 });
gsap.to('.hero-content h2', { opacity: 1, y: 0, duration: 1, ease: 'power3.out', delay: 0.6 });

// Section 1: Fade & Slide - Works in both languages
gsap.timeline({
    scrollTrigger: { trigger:'#section-fade', start:'top top', end:'+=100%', pin:true, scrub:0.5, anticipatePin:1 }
}).to('.e1-title', { opacity:1, y:0, duration:1 }, 0)
  .to('.e1-desc', { opacity:1, y:0, duration:1 }, 0.3);

// Section 2: Word Carousel - Works in both languages
const words = ['#word1','#word2','#word3','#word4'];
gsap.set(words, { opacity:0.15 });
const tlWords = gsap.timeline({ scrollTrigger: { trigger:'#section-carousel', start:'top top', end:'+=150%', pin:true, scrub:0.5, anticipatePin:1 } });
words.forEach((sel, i) => {
    tlWords.to(sel, { opacity: 1, duration: 0.5 }, i * 0.8)
           .to(sel, { opacity: 0.15, duration: 0.5 }, i * 0.8 + 0.6);
});

// Feature Cards - Hover effects only, no scroll animation
// Cards are visible by default in both languages

// Section 3: Staggered List - Works in both languages (RTL handled by CSS)
gsap.timeline({ scrollTrigger: { trigger:'#section-list', start:'top top', end:'+=100%', pin:true, scrub:0.5, anticipatePin:1 } })
    .to('.e4-item1', { opacity:1, x:0, duration:0.5 }, 0)
    .to('.e4-item2', { opacity:1, x:0, duration:0.5 }, 0.2)
    .to('.e4-item3', { opacity:1, x:0, duration:0.5 }, 0.4)
    .to('.e4-item4', { opacity:1, x:0, duration:0.5 }, 0.6)
    .to('.e4-item5', { opacity:1, x:0, duration:0.5 }, 0.8);

// Section 4: Blur Reveal - Works in both languages
gsap.timeline({ scrollTrigger: { trigger:'#section-blur', start:'top top', end:'+=100%', pin:true, scrub:0.5, anticipatePin:1 } })
    .to('.e6-blur', { opacity:1, filter:'blur(0px)', duration:1, ease:'power2.out' }, 0);

// Section 5: Typewriter - Works in both languages
let typewriterTl = null;

function initTypewriter() {
    let typeEl = document.querySelector('.e12-type');
    if (!typeEl) return;
    
    // Get current language and text
    const currentLang = document.documentElement.getAttribute('lang') || 'en';
    const content = currentLang === 'ar' ? arabicContent : englishContent;
    const originalText = content.type;
    
    // Clear and rebuild spans
    typeEl.innerHTML = '';
    let chars = originalText.split('');
    chars.forEach((c, index) => {
        let span = document.createElement('span');
        span.innerText = c;
        span.style.opacity = '0';
        span.style.display = 'inline';
        typeEl.appendChild(span);
    });
}

// Create typewriter animation
function createTypewriterAnimation() {
    // Kill existing animation if any
    if (typewriterTl) {
        typewriterTl.kill();
    }
    
    typewriterTl = gsap.timeline({ 
        scrollTrigger: { 
            trigger:'#section-type', 
            start:'top top', 
            end:'+=300%', 
            pin:true, 
            scrub:0.5, 
            anticipatePin:1 
        } 
    }).to('.e12-type span', { 
        opacity:1, 
        duration:0.05, 
        stagger:0.02, 
        ease:'none' 
    }, 0);
}

// Initialize on page load
initTypewriter();
createTypewriterAnimation();

// Growing Section - Stats - Works in both languages
let growingTl = gsap.timeline({
    scrollTrigger: {
        trigger: '#section-growing',
        start: 'top top',
        end: '+=300%',
        scrub: true,
        pin: true
    }
});

growingTl.to('#section-growing .text-line', {
    opacity: 1,
    y: -20,
    stagger: 1
});

// CTA Section - Works in both languages
gsap.timeline({ scrollTrigger: { trigger: '#section-cta', start: 'top 80%', end: 'bottom bottom', toggleActions: 'play none none reverse' } })
    .to('.cta-title', { opacity: 1, y: 0, duration: 0.8, ease: 'power3.out' }, 0)
    .to('.cta-desc', { opacity: 1, y: 0, duration: 0.8, ease: 'power3.out' }, 0.2)
    .to('.cta-buttons', { opacity: 1, duration: 0.8, ease: 'power3.out' }, 0.4);

// Mobile menu - attach event listener
const menuToggle = document.getElementById('menuToggle');
if (menuToggle && navLinks) {
    menuToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        navLinks.classList.toggle('active');
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!navLinks.contains(e.target) && !menuToggle.contains(e.target)) {
            navLinks.classList.remove('active');
        }
    });
}

// Close menu when clicking a link
document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        navLinks.classList.remove('active');

        const targetId = link.getAttribute('href');

        // Disable ScrollTrigger to prevent animations during scroll
        ScrollTrigger.getAll().forEach(trigger => trigger.disable());

        // Scroll to target section
        const targetSection = document.querySelector(targetId);
        if (targetSection) {
            // Get nav height dynamically (changes on mobile vs desktop)
            const nav = document.querySelector('nav');
            const navHeight = nav ? nav.offsetHeight : 80;
            
            // Calculate position with offset for nav
            const targetPosition = targetSection.offsetTop - navHeight;

            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        }

        // Re-enable ScrollTrigger after scroll completes
        setTimeout(() => {
            ScrollTrigger.getAll().forEach(trigger => trigger.enable());
            ScrollTrigger.refresh();
        }, 1000);
    });
});

});
</script>
</body>
</html>