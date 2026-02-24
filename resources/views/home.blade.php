<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nexus — Your Social Platform</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
:root{
    --primary:#5e60ce;
    --primary-light:#7400b8;
    --primary-dark:#4ea8de;
    --secondary:#5390d9;
    --accent:#4ea8de;
    --bg-dark:#0d0d0d;
    --bg-card:#161616;
    --bg-elevated:#1c1c1e;
    --text-primary:#ffffff;
    --text-secondary:#98989f;
    --text-muted:#636366;
    --gradient-1:#5e60ce;
    --gradient-2:#7400b8;
}

*{margin:0;padding:0;box-sizing:border-box;scroll-behavior:smooth}
body{
    font-family:'Inter',sans-serif;
    background:var(--bg-dark);
    color:var(--text-primary);
    overflow-xhidden;
    -webkit-font-smoothing:antialiased;
    -moz-osx-font-smoothing:grayscale;
}

/* FADE ANIMATIONS */
.fade-in{opacity:0;transform:translateY(30px);transition:opacity 0.8s ease,transform 0.8s ease}
.fade-in.visible{opacity:1;transform:translateY(0)}
.hero .fade-in{opacity:0;transform:translateY(40px);animation:fadeInUp 1s ease forwards}
.hero .fade-in:nth-child(1){animation-delay:0.3s}
.hero .fade-in:nth-child(2){animation-delay:0.5s}
.hero .fade-in:nth-child(3){animation-delay:0.7s}
@keyframes fadeInUp{to{opacity:1;transform:translateY(0)}}
.fade-in-delay-1{transition-delay:0.1s}
.fade-in-delay-2{transition-delay:0.2s}
.fade-in-delay-3{transition-delay:0.3s}

/* NAV - Transparent with Blur */
nav{
    position:fixed;
    top:0;
    width:100%;
    padding:14px 40px;
    backdrop-filter:blur(30px);
    -webkit-backdrop-filter:blur(30px);
    background:rgba(0,0,0,0.15);
    z-index:100;
    display:flex;
    justify-content:center;
    border-bottom:1px solid rgba(255,255,255,0.05);
}
.nav-container{
    max-width:980px;
    width:100%;
    display:flex;
    justify-content:space-between;
    align-items:center;
}
nav a{color:#f5f5f7;text-decoration:none;font-size:12px;font-weight:400;opacity:0.8;transition:opacity 0.3s}
nav a:hover{opacity:1}
.nav-brand{font-weight:600;font-size:18px}

/* HERO - Apple Style */
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

.hero video{
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
    object-fit:cover;
    z-index:-2;
    opacity:0.85;
    filter:saturate(0.9) contrast(1.05);
    transition:transform 0.1s ease-out, opacity 0.3s ease-out, filter 0.3s ease-out;
    will-change: transform, opacity, filter;
}

.hero video.scrolling{
    transform:scale(1.5);
    opacity:0;
    filter:blur(30px) saturate(0);
}

.hero::before{
    content:"";
    position:absolute;
    inset:0;
    background:linear-gradient(180deg,rgba(0,0,0,0.3) 0%,rgba(0,0,0,0.6) 100%);
    z-index:-1;
    transition:opacity 0.3s ease-out;
}

.hero::before.fading{
    opacity:0;
}

.hero-content{position:relative;z-index:1;max-width:800px}

.hero h1{
    font-size:clamp(3rem,7vw,5.5rem);
    font-weight:700;
    line-height:1.1;
    margin-bottom:15px;
    letter-spacing:-0.02em;
    background:linear-gradient(135deg,#fff 0%,#a1a1a6 100%);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    background-clip:text;
}

.hero h2{
    font-size:clamp(1.2rem,2.5vw,1.8rem);
    font-weight:500;
    line-height:1.3;
    color:#f5f5f7;
    margin-bottom:25px;
    opacity:0.9;
}

.hero-cta{display:flex;gap:15px;justify-content:center;flex-wrap:wrap}
.hero-cta-btn{
    padding:12px 24px;
    border-radius:980px;
    font-size:17px;
    font-weight:500;
    text-decoration:none;
    transition:all 0.3s;
}
.hero-cta-btn-primary{background:#fff;color:#000}
.hero-cta-btn-primary:hover{background:#f5f5f5;transform:scale(1.02)}
.hero-cta-btn-secondary{background:rgba(79,140,255,0.2);color:#fff;border:1px solid var(--primary)}
.hero-cta-btn-secondary:hover{background:var(--primary)}

/* SECTIONS - Apple Style */
section{padding:120px 20px;text-align:center;background:var(--bg-dark)}

.section-label{
    font-size:21px;
    font-weight:600;
    color:#f5f5f7;
    margin-bottom:10px;
}

.section-title{
    font-size:clamp(2.5rem,5vw,4rem);
    font-weight:700;
    letter-spacing:-0.02em;
    margin-bottom:15px;
    color:#f5f5f7;
}

.section-desc{
    font-size:21px;
    line-height:1.4;
    color:#86868b;
    max-width:700px;
    margin:0 auto 60px;
}

/* FEATURES - Apple Grid */
.features{background:var(--bg-dark)}

.feature-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
    gap:20px;
    max-width:1200px;
    margin:0 auto;
}

.feature-card{
    background:linear-gradient(180deg,#1d1d1f 0%,#000 100%);
    border-radius:30px;
    padding:50px 40px;
    text-align:left;
    transition:transform 0.4s ease,box-shadow 0.4s ease;
    position:relative;
    overflow:hidden;
}

.feature-card:hover{
    transform:translateY(-5px);
    box-shadow:0 20px 50px rgba(0,0,0,0.5);
}

.feature-card h3{
    font-size:28px;
    font-weight:600;
    margin-bottom:15px;
    color:#f5f5f7;
}

.feature-card p{
    font-size:17px;
    line-height:1.5;
    color:#86868b;
}

/* CTA SECTION - Apple Style */
.cta-section{
    background:var(--bg-dark);
    padding:180px 20px;
}

.cta-content{max-width:900px;margin:0 auto}

.cta-section h2{
    font-size:clamp(2.5rem,5vw,4rem);
    font-weight:700;
    margin-bottom:20px;
    color:#f5f5f7;
}

.cta-section p{
    font-size:21px;
    color:#86868b;
    margin-bottom:30px;
}

.cta-buttons{display:flex;gap:15px;justify-content:center}
.cta-btn{
    padding:14px 28px;
    border-radius:980px;
    font-size:17px;
    font-weight:500;
    text-decoration:none;
    transition:all 0.3s;
}
.cta-btn-primary{background:#4f8cff;color:#000}
.cta-btn-primary:hover{background:#6b9fff;transform:scale(1.02)}
.cta-btn-secondary{background:transparent;border:1px solid #86868b;color:#f5f5f7}
.cta-btn-secondary:hover{background:rgba(255,255,255,0.1)}

/* FOOTER - Apple Style */
footer{
    background:#0d0d0d;
    padding:40px 20px;
    font-size:16px;
    color:#86868b;
    border-top:1px solid rgba(255,255,255,0.08);
}
.footer-content{
    max-width:980px;
    margin:0 auto;
}
.footer-content p{margin-bottom:10px}
footer a{color:#86868b;text-decoration:none}
footer a:hover{text-decoration:underline}

/* RESPONSIVE */
@media(max-width:768px){
    nav{padding:12px 20px}
    .hero-cta{flex-direction:column;align-items:center}
    .hero-cta-btn{width:100%;max-width:200px}
    .cta-buttons{flex-direction:column;align-items:center}
    .cta-btn{width:100%;max-width:200px}
    .feature-card{padding:35px 25px}
}
</style>
</head>
<body>

<!-- NAV -->
<nav>
    <div class="nav-container">
        <a href="/" class="nav-brand">Nexus</a>
        <div style="display:flex;gap:30px;">
            <a href="#features">Features</a>
            <a href="#join-now">Join</a>
        </div>
    </div>
</nav>

<!-- HERO -->
<section class="hero">
    <video autoplay muted loop playsinline preload="auto" poster="">
        <source src="https://cdn.pixabay.com/video/2021/12/10/100221-657132594_small.mp4" type="video/mp4">
    </video>
    <div class="hero-content">
        <h1 class="fade-in">Nexus</h1>
        <h2 class="fade-in fade-in-delay-1">Your space. Your people. Your story.</h2>
        <div class="hero-cta fade-in fade-in-delay-2">
            <a href="#join-now" class="hero-cta-btn hero-cta-btn-primary">Join Now</a>
            <a href="#features" class="hero-cta-btn hero-cta-btn-secondary">Learn More</a>
        </div>
    </div>
</section>

<!-- WHAT IS NEXUS -->
<section class="what-is-nexus" style="padding:140px 20px;">
    <div style="max-width:1000px;margin:0 auto;">
        <p class="section-label fade-in" style="color:var(--primary-light);">Discover</p>
        <h2 class="section-title fade-in fade-in-delay-1" style="font-size:clamp(2rem,4vw,3.5rem);">What is Nexus?</h2>
        
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:30px;margin-top:60px;">
            <!-- Card 1 -->
            <div class="fade-in" style="background:linear-gradient(145deg,rgba(30,30,32,0.8) 0%,rgba(18,18,20,0.9) 100%);border:1px solid rgba(255,255,255,0.08);border-radius:24px;padding:40px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-20px;right:-20px;width:100px;height:100px;background:var(--primary);filter:blur(60px);opacity:0.3;"></div>
                <div style="width:56px;height:56px;background:linear-gradient(135deg,#5e60ce 0%,#7400b8 100%);border-radius:16px;display:flex;align-items:center;justify-content:center;margin-bottom:24px;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                </div>
                <h3 style="font-size:24px;font-weight:600;margin-bottom:12px;color:#fff;">All-in-One Platform</h3>
                <p style="font-size:16px;color:#98989f;line-height:1.6;">Nexus brings together the best social features in one place. Share stories, chat privately, join communities, and connect with people who matter to you.</p>
            </div>
            
            <!-- Card 2 -->
            <div class="fade-in fade-in-delay-1" style="background:linear-gradient(145deg,rgba(30,30,32,0.8) 0%,rgba(18,18,20,0.9) 100%);border:1px solid rgba(255,255,255,0.08);border-radius:24px;padding:40px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-20px;right:-20px;width:100px;height:100px;background:var(--primary-dark);filter:blur(60px);opacity:0.3;"></div>
                <div style="width:56px;height:56px;background:linear-gradient(135deg,#4ea8de 0%,#5390d9 100%);border-radius:16px;display:flex;align-items:center;justify-content:center;margin-bottom:24px;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                </div>
                <h3 style="font-size:24px;font-weight:600;margin-bottom:12px;color:#fff;">Built for Connection</h3>
                <p style="font-size:16px;color:#98989f;line-height:1.6;">Experience meaningful interactions with an AI-powered assistant, private encrypted messaging, and communities that align with your interests.</p>
            </div>
            
            <!-- Card 3 -->
            <div class="fade-in fade-in-delay-2" style="background:linear-gradient(145deg,rgba(30,30,32,0.8) 0%,rgba(18,18,20,0.9) 100%);border:1px solid rgba(255,255,255,0.08);border-radius:24px;padding:40px;position:relative;overflow:hidden;">
                <div style="position:absolute;top:-20px;right:-20px;width:100px;height:100px;background:var(--accent);filter:blur(60px);opacity:0.3;"></div>
                <div style="width:56px;height:56px;background:linear-gradient(135deg,#4ea8de 0%,#5e60ce 100%);border-radius:16px;display:flex;align-items:center;justify-content:center;margin-bottom:24px;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <h3 style="font-size:24px;font-weight:600;margin-bottom:12px;color:#fff;">Privacy First</h3>
                <p style="font-size:16px;color:#98989f;line-height:1.6;">Your data belongs to you. Full control over what you share, who sees it, and complete transparency in how your information is used.</p>
            </div>
        </div>
        
        <!-- Bottom highlight -->
        <div class="fade-in" style="margin-top:60px;padding:30px;background:rgba(94,96,206,0.1);border:1px solid rgba(94,96,206,0.2);border-radius:20px;text-align:center;">
            <p style="font-size:18px;color:#d1d1d6;line-height:1.6;">Join thousands of users who've already discovered a better way to <span style="color:#fff;font-weight:500;">connect</span>, <span style="color:#fff;font-weight:500;">share</span>, and <span style="color:#fff;font-weight:500;">belong</span>.</p>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="features" id="features">
    <p class="section-label fade-in">Nexus</p>
    <h2 class="section-title fade-in fade-in-delay-1">Think different.<br>Connect different.</h2>
    <p class="section-desc fade-in fade-in-delay-2">A new way to share, connect, and belong.</p>
    
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:30px;max-width:1100px;margin:0 auto;">
        <!-- Stories -->
        <div class="fade-in" style="background:linear-gradient(145deg,rgba(30,30,32,0.8) 0%,rgba(18,18,20,0.9) 100%);border:1px solid rgba(255,255,255,0.08);border-radius:24px;padding:40px;position:relative;overflow:hidden;text-align:left;">
            <div style="position:absolute;top:-20px;right:-20px;width:100px;height:100px;background:#e879f9;filter:blur(60px);opacity:0.3;"></div>
            <div style="width:56px;height:56px;background:linear-gradient(135deg,#a855f7 0%,#d946ef 100%);border-radius:16px;display:flex;align-items:center;justify-content:center;margin-bottom:24px;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
            </div>
            <h3 style="font-size:24px;font-weight:600;margin-bottom:12px;color:#fff;">Stories</h3>
            <p style="font-size:16px;color:#98989f;line-height:1.6;">Share moments that matter. Express yourself with photos, videos, and more. Disappear after 24 hours.</p>
        </div>
        
        <!-- Private Chat -->
        <div class="fade-in fade-in-delay-1" style="background:linear-gradient(145deg,rgba(30,30,32,0.8) 0%,rgba(18,18,20,0.9) 100%);border:1px solid rgba(255,255,255,0.08);border-radius:24px;padding:40px;position:relative;overflow:hidden;text-align:left;">
            <div style="position:absolute;top:-20px;right:-20px;width:100px;height:100px;background:#34d399;filter:blur(60px);opacity:0.3;"></div>
            <div style="width:56px;height:56px;background:linear-gradient(135deg,#10b981 0%,#34d399 100%);border-radius:16px;display:flex;align-items:center;justify-content:center;margin-bottom:24px;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <h3 style="font-size:24px;font-weight:600;margin-bottom:12px;color:#fff;">Private Chat</h3>
            <p style="font-size:16px;color:#98989f;line-height:1.6;">Encrypted conversations with friends and family. Your messages, your rules.</p>
        </div>
        
        <!-- Communities -->
        <div class="fade-in fade-in-delay-2" style="background:linear-gradient(145deg,rgba(30,30,32,0.8) 0%,rgba(18,18,20,0.9) 100%);border:1px solid rgba(255,255,255,0.08);border-radius:24px;padding:40px;position:relative;overflow:hidden;text-align:left;">
            <div style="position:absolute;top:-20px;right:-20px;width:100px;height:100px;background:#60a5fa;filter:blur(60px);opacity:0.3;"></div>
            <div style="width:56px;height:56px;background:linear-gradient(135deg,#3b82f6 0%,#60a5fa 100%);border-radius:16px;display:flex;align-items:center;justify-content:center;margin-bottom:24px;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <h3 style="font-size:24px;font-weight:600;margin-bottom:12px;color:#fff;">Communities</h3>
            <p style="font-size:16px;color:#98989f;line-height:1.6;">Find your people. Join groups based on interests and passions.</p>
        </div>
        
        <!-- AI Assistant -->
        <div class="fade-in fade-in-delay-1" style="background:linear-gradient(145deg,rgba(30,30,32,0.8) 0%,rgba(18,18,20,0.9) 100%);border:1px solid rgba(255,255,255,0.08);border-radius:24px;padding:40px;position:relative;overflow:hidden;text-align:left;">
            <div style="position:absolute;top:-20px;right:-20px;width:100px;height:100px;background:#f472b6;filter:blur(60px);opacity:0.3;"></div>
            <div style="width:56px;height:56px;background:linear-gradient(135deg,#ec4899 0%,#f472b6 100%);border-radius:16px;display:flex;align-items:center;justify-content:center;margin-bottom:24px;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M12 2a2 2 0 0 1 2 2c0 .74-.4 1.39-1 1.73V7h1a7 7 0 0 1 7 7h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h1a7 7 0 0 1 7-7h1V5.73c-.6-.34-1-.99-1-1.73a2 2 0 0 1 2-2z"/><path d="M9 12a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/><path d="M15 12a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/></svg>
            </div>
            <h3 style="font-size:24px;font-weight:600;margin-bottom:12px;color:#fff;">AI Assistant</h3>
            <p style="font-size:16px;color:#98989f;line-height:1.6;">Smart help when you need it. Create, connect, and discover with AI.</p>
        </div>
        
        <!-- Privacy -->
        <div class="fade-in fade-in-delay-2" style="background:linear-gradient(145deg,rgba(30,30,32,0.8) 0%,rgba(18,18,20,0.9) 100%);border:1px solid rgba(255,255,255,0.08);border-radius:24px;padding:40px;position:relative;overflow:hidden;text-align:left;">
            <div style="position:absolute;top:-20px;right:-20px;width:100px;height:100px;background:#fbbf24;filter:blur(60px);opacity:0.3;"></div>
            <div style="width:56px;height:56px;background:linear-gradient(135deg,#f59e0b 0%,#fbbf24 100%);border-radius:16px;display:flex;align-items:center;justify-content:center;margin-bottom:24px;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <h3 style="font-size:24px;font-weight:600;margin-bottom:12px;color:#fff;">Privacy</h3>
            <p style="font-size:16px;color:#98989f;line-height:1.6;">Your data stays yours. Full control over what you share and who sees it.</p>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section" id="join-now">
    <div class="cta-content">
        <h2 class="fade-in">Ready to join?</h2>
        <p class="fade-in fade-in-delay-1">Start your journey with Nexus today.</p>
        <div class="cta-buttons fade-in fade-in-delay-2">
            <a href="/register" class="cta-btn cta-btn-primary">Create Account</a>
            <a href="/login" class="cta-btn cta-btn-secondary">Sign In</a>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <div class="footer-content">
        <p>2026 Copyright © 2026 Nexus Team. All rights reserved.</p>
    </div>
</footer>

<script>
const observer=new IntersectionObserver((entries)=>{entries.forEach(entry=>{if(entry.isIntersecting){entry.target.classList.add('visible')}})},{threshold:0.1});document.querySelectorAll('.fade-in').forEach(el=>observer.observe(el));

// Optimized hero fade animation - prevents lag on devices
const heroVideo = document.querySelector('.hero video');
const heroContent = document.querySelector('.hero-content');

let ticking = false;

function updateHeroFade() {
    const scrollY = window.scrollY;
    const windowHeight = window.innerHeight;
    
    if (scrollY < windowHeight) {
        const progress = Math.min(scrollY / windowHeight, 1);
        const opacity = 1 - progress;
        
        heroVideo.style.opacity = opacity;
        heroContent.style.opacity = opacity;
    } else {
        heroVideo.style.opacity = 0;
        heroContent.style.opacity = 0;
    }
    
    ticking = false;
}

window.addEventListener('scroll', () => {
    if (!ticking) {
        window.requestAnimationFrame(updateHeroFade);
        ticking = true;
    }
});

updateHeroFade();
</script>

</body>
</html>
