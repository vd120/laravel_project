<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Nexus — {{ __('home.your_social_platform') }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="dns-prefetch" href="https://fonts.googleapis.com">
<link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/landing.css') }}">
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔷</text></svg>">
</head>
<body>
<div class="intro-wrapper">
    <div class="intro-shape"></div>
</div>
<div class="animated-bg"></div>
<nav>
    <div class="nav-container">
        <a href="/" class="nav-brand">Nexus</a>
        <div style="display:flex;align-items:center;gap:20px;">
            @include('partials.language-switcher')
            <button id="themeToggle" title="{{ __('home.toggle_theme') }}">
                <svg class="theme-icon sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                <svg class="theme-icon moon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
            </button>
            <div class="nav-links" id="navLinks">
                <a href="#section-features" data-en="Features" data-ar="المميزات">{{ app()->getLocale() === 'ar' ? 'المميزات' : 'Features' }}</a>
                <a href="#section-cta" data-en="Join" data-ar="انضم">{{ app()->getLocale() === 'ar' ? 'انضم' : 'Join' }}</a>
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
    <div class="hero-bg-video">
        <video autoplay muted loop playsinline preload="metadata" loading="lazy" decoding="async" poster="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1920 1080'%3E%3Crect fill='%23030308' width='1920' height='1080'/%3E%3C/svg%3E">
            <source src="{{ asset('vid.mp4') }}" type="video/mp4">
        </video>
        <div class="hero-bg-overlay"></div>
    </div>
    <div class="hero-content">
        <h1 id="nexus-title">{{ __('home.nexus') }}</h1>
        <h2>{{ __('home.connect_share_belong') }}</h2>
    </div>
    <div class="scroll-arrow" onclick="scrollToSection()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 5v14"/>
            <path d="M19 12l-7 7-7-7"/>
        </svg>
        <span>{{ __('home.scroll_to_explore') }}</span>
    </div>
</section>
<section class="pin-section" id="section-fade">
    <div class="pin-container">
        <div class="fade-content">
            <h2 class="e1-title">{{ __('home.built_for_real_connections') }}</h2>
            <p class="e1-desc">{{ __('home.nexus_brings_together') }}</p>
        </div>
    </div>
</section>
<section class="pin-section" id="section-carousel">
    <div class="pin-container">
        <div class="carousel">
            <div class="word-line" id="word1">{{ __('home.post') }}</div>
            <div class="word-line" id="word2">{{ __('home.share') }}</div>
            <div class="word-line" id="word3">{{ __('home.connect') }}</div>
            <div class="word-line" id="word4">{{ __('home.belong') }}</div>
        </div>
    </div>
</section>
<section class="features-grid-section" id="section-features">
    <p class="section-label">{{ __('home.features') }}</p>
    <h2 class="section-title">{{ __('home.everything_you_need') }}</h2>
    <div class="feature-grid">
        <div class="card">
            <div class="card-glow"></div>
            <div class="card-icon" style="background:linear-gradient(135deg,#a855f7 0%,#d946ef 100%);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
            </div>
            <h3 class="feature-card-title">{{ __('home.posts') }}</h3>
            <p class="feature-card-desc">{{ __('home.posts_desc') }}</p>
        </div>
        <div class="card">
            <div class="card-glow"></div>
            <div class="card-icon" style="background:linear-gradient(135deg,#10b981 0%,#34d399 100%);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
            </div>
            <h3 class="feature-card-title">{{ __('home.stories') }}</h3>
            <p class="feature-card-desc">{{ __('home.stories_desc') }}</p>
        </div>
        <div class="card">
            <div class="card-glow"></div>
            <div class="card-icon" style="background:linear-gradient(135deg,#10b981 0%,#34d399 100%);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <h3 class="feature-card-title">{{ __('home.private_chat') }}</h3>
            <p class="feature-card-desc">{{ __('home.private_chat_desc') }}</p>
        </div>
        <div class="card">
            <div class="card-glow"></div>
            <div class="card-icon" style="background:linear-gradient(135deg,#3b82f6 0%,#60a5fa 100%);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <h3 class="feature-card-title">{{ __('home.groups') }}</h3>
            <p class="feature-card-desc">{{ __('home.groups_desc') }}</p>
        </div>
        <div class="card">
            <div class="card-glow"></div>
            <div class="card-icon" style="background:linear-gradient(135deg,#ec4899 0%,#f472b6 100%);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M12 2a2 2 0 0 1 2 2c0 .74-.4 1.39-1 1.73V7h1a7 7 0 0 1 7 7h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h1a7 7 0 0 1 7-7h1V5.73c-.6-.34-1-.99-1-1.73a2 2 0 0 1 2-2z"/><path d="M9 12a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/><path d="M15 12a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/></svg>
            </div>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                <h3 class="feature-card-title" style="margin:0;">{{ __('home.ai_assistant') }}</h3>
                <span id="ai-badge" style="background:rgba(236,72,153,0.2);color:#f472b6;font-size:10px;padding:4px 8px;border-radius:12px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">{{ __('home.menu_based') }}</span>
            </div>
            <p class="feature-card-desc">{{ __('home.ai_assistant_desc') }}</p>
        </div>
        <div class="card">
            <div class="card-glow"></div>
            <div class="card-icon" style="background:linear-gradient(135deg,#f59e0b 0%,#fbbf24 100%);">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <h3 class="feature-card-title">{{ __('home.privacy_first') }}</h3>
            <p class="feature-card-desc">{{ __('home.privacy_first_desc') }}</p>
        </div>
    </div>
</section>
<section class="pin-section" id="section-list">
    <div class="pin-container">
        <h2 class="list-title">{{ __('home.why_choose_nexus') }}</h2>
        <div class="list-effect">
            <div class="list-item"><span class="list-bullet"></span><span>{{ __('home.no_ads') }}</span></div>
            <div class="list-item"><span class="list-bullet"></span><span>{{ __('home.chronological_feed') }}</span></div>
            <div class="list-item"><span class="list-bullet"></span><span>{{ __('home.your_data_your_control') }}</span></div>
            <div class="list-item"><span class="list-bullet"></span><span>{{ __('home.end_to_end_encryption') }}</span></div>
            <div class="list-item"><span class="list-bullet"></span><span>{{ __('home.community_driven') }}</span></div>
        </div>
    </div>
</section>
<section class="pin-section" id="section-blur">
    <div class="pin-container">
        <div class="blur-section-wrapper">
            <div class="blur-glow"></div>
            <div class="blur-text e6-blur">{!! __('home.your_privacy_protected') !!}</div>
        </div>
    </div>
</section>
<section class="pin-section" id="section-type">
    <div class="pin-container">
        <div class="text-line e12-type">{{ __('home.nexus_is_built') }}</div>
    </div>
</section>
<section class="pin-section" id="section-growing">
    <div class="pin-container">
        <div class="text-line growing-title" data-en="Join Our Rapidly Growing Community" data-ar="انضم إلى مجتمعنا سريع النمو">{{ __('home.join_our_rapidly_growing_community') }}</div>
        <div class="text-line stat-row">
            <span class="stat-number" data-en="+50K" data-ar="+50 ألف">+50K</span>
            <span class="stat-label" data-en="Users" data-ar="مستخدم">{{ __('home.users') }}</span>
        </div>
        <div class="text-line stat-row">
            <span class="stat-number" data-en="+120K" data-ar="+120 ألف">+120K</span>
            <span class="stat-label" data-en="Posts" data-ar="منشور">{{ __('home.posts') }}</span>
        </div>
        <div class="text-line stat-row">
            <span class="stat-number" data-en="+85K" data-ar="+85 ألف">+85K</span>
            <span class="stat-label" data-en="Stories" data-ar="قصة">{{ __('home.stories') }}</span>
        </div>
    </div>
</section>
<section class="cta-section" id="section-cta">
    <div class="cta-content">
        <h2 class="cta-title">{{ __('home.your_community_awaits') }}</h2>
        <p class="cta-desc">{{ __('home.join_nexus_today') }}</p>
        <div class="cta-buttons">
            <a href="/register" class="cta-btn cta-btn-primary">{{ __('home.get_started_free') }}</a>
            <a href="/login" class="cta-btn cta-btn-secondary">{{ __('home.sign_in') }}</a>
        </div>
    </div>
</section>
<footer>
    <div class="footer-content">
        <p>© 2026 Nexus. {{ __('home.built_for_authentic_connections') }}</p>
        <div style="display:flex;justify-content:center;gap:20px;margin-top:20px;flex-wrap:wrap;">
            <a href="https://github.com/vd120/nexus" target="_blank" rel="noopener noreferrer" style="color:var(--text-secondary);text-decoration:none;font-size:14px;display:flex;align-items:center;gap:8px;font-weight:500;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                GitHub
            </a>
            <a href="mailto:socialapp.noreply@gmail.com" style="color:var(--text-secondary);text-decoration:none;font-size:14px;display:flex;align-items:center;gap:8px;font-weight:500;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 6l-10 7L2 6"/></svg>
                {{ __('home.contact_support') }}
            </a>
        </div>
    </div>
</footer>
@vite(['resources/js/legacy/home.js'])
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js" defer></script>
<script defer>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof gsap !== 'undefined') {
        initGSAP();
    }
});
</body>
</html>
