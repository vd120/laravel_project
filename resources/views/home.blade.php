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

<!-- GSAP Libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>

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
html { scroll-behavior: smooth; scroll-padding-top: 100px; }

body{
    font-family:'Inter',sans-serif;
    background:var(--bg-main);
    color:var(--text-primary);
    overflow-x:hidden;
    -webkit-font-smoothing:antialiased;
    transition: background 0.3s ease, color 0.3s ease;
}

html[lang="ar"] body {
    font-family: 'Cairo', 'Inter', sans-serif;
}

.menu-toggle {
    display: none;
    flex-direction: column;
    gap: 5px;
    cursor: pointer;
    padding: 5px;
    z-index: 1001;
}
.menu-toggle span {
    width: 25px;
    height: 2px;
    background: var(--text-primary);
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    transform-origin: center;
}
.menu-toggle.active span:nth-child(1) {
    transform: translateY(7px) rotate(45deg);
}
.menu-toggle.active span:nth-child(2) {
    opacity: 0;
    transform: scaleX(0);
}
.menu-toggle.active span:nth-child(3) {
    transform: translateY(-7px) rotate(-45deg);
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
    padding:12px 40px;
    backdrop-filter:blur(20px);
    -webkit-backdrop-filter:blur(20px);
    background:transparent;
    z-index:1000;
    display:flex;
    justify-content:center;
    border-bottom:none;
    will-change: auto;
    contain: layout style;
    transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    opacity: 0;
    transform: translateY(-20px);
    animation: navFadeInAfterIntro 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards 2s;
}
@keyframes navFadeInAfterIntro {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
nav.scrolled {
    padding: 10px 40px;
    background: transparent;
    backdrop-filter: blur(30px);
    -webkit-backdrop-filter: blur(30px);
}
nav.scrolled .nav-container {
    max-width: 1000px;
    padding: 10px 28px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.15);
    border-color: rgba(255,255,255,0.15);
    background: rgba(255,255,255,0.06);
}
.nav-container{
    max-width:1200px;
    width:100%;
    display:flex;
    justify-content:space-between;
    align-items:center;
    background: rgba(255,255,255,0.03);
    backdrop-filter: blur(40px) saturate(150%);
    -webkit-backdrop-filter: blur(40px) saturate(150%);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 100px;
    padding: 10px 24px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.2), inset 0 1px 0 rgba(255,255,255,0.1);
    will-change: auto;
    transform: translateZ(0);
    -webkit-transform: translateZ(0);
    contain: layout style;
    transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}
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

/* RTL Support */
/* Header/Nav always LTR for consistency */
nav, .nav-container, .nav-links, .language-switcher, .language-toggle, .language-dropdown, .language-option {
    direction: ltr !important;
}

html[lang="ar"] {
    direction: rtl;
}

/* Content sections - RTL for Arabic */
html[lang="ar"] .hero h2,
html[lang="ar"] .fade-content,
html[lang="ar"] .fade-content p,
html[lang="ar"] .cta-desc,
html[lang="ar"] .card,
html[lang="ar"] .card p,
html[lang="ar"] .footer-content,
html[lang="ar"] footer {
    direction: rtl !important;
}
html[lang="ar"] .list-effect {
    text-align: right;
    padding-right: 10%;
    padding-left: 0;
}
html[lang="ar"] .list-item {
    flex-direction: row-reverse;
}
html[lang="ar"] .feature-grid {
    direction: rtl;
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
    background:#030308;
    transition: background 0.3s ease;
}

/* Light Theme - Hero Background */
[data-theme="light"] .hero {
    background: #f5f5f7;
}

.hero-bg-video {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
    overflow: hidden;
    transform: translateZ(0);
    -webkit-transform: translateZ(0);
    backface-visibility: hidden;
    perspective: 1000px;
}

.hero-bg-video video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 1;
    will-change: auto;
    transform: translateZ(0);
    -webkit-transform: translateZ(0);
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
}

.hero-bg-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(180deg, rgba(3,3,8,0.85) 0%, rgba(3,3,8,0.65) 50%, rgba(3,3,8,0.9) 100%);
    z-index: 1;
    pointer-events: none;
    transition: background 0.3s ease;
}

/* Light Theme - Adjust overlay */
[data-theme="light"] .hero-bg-overlay {
    background: linear-gradient(180deg, rgba(245,245,247,0.85) 0%, rgba(245,245,247,0.65) 50%, rgba(245,245,247,0.9) 100%);
}

/* Light Theme - Video opacity */
[data-theme="light"] .hero-bg-video video {
    opacity: 1;
}

/* Mobile optimization - reduce video opacity */
@media (max-width: 768px) {
    .hero-bg-video video {
        opacity: 1;
    }
    [data-theme="light"] .hero-bg-video video {
        opacity: 1;
    }
}

/* Low power mode - further reduce */
@media (prefers-reduced-motion: reduce) {
    .hero-bg-video video {
        opacity: 1;
    }
    [data-theme="light"] .hero-bg-video video {
        opacity: 1;
    }
}

/* Ultra low-end devices - minimal video impact */
@media (max-width: 480px) {
    .hero-bg-video video {
        opacity: 1;
    }
    [data-theme="light"] .hero-bg-video video {
        opacity: 1;
    }
}

.hero-content{
    position:relative;
    z-index:1;
    max-width:900px;
}
.hero-content h2{
    opacity:0;
    transform:translateY(30px);
    animation: heroSubtitleFade 1.5s cubic-bezier(0.16, 1, 0.3, 1) forwards 2s;
}
#nexus-title{
    opacity:0;
    transform:translateY(40px);
    animation: heroTitleFade 1.8s cubic-bezier(0.16, 1, 0.3, 1) forwards 1.5s;
}
.hero h1{
    font-size:clamp(4rem, 10vw, 8rem);
    font-weight:700;
    letter-spacing:-0.03em;
    line-height:1;
    margin-bottom:20px;
    color:var(--text-primary);
    background: linear-gradient(135deg, var(--text-primary) 0%, var(--text-secondary) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: heroTitleFade 1.8s cubic-bezier(0.16, 1, 0.3, 1) forwards 1.5s;
}
.hero h2{
    font-size:clamp(1.2rem,2.5vw,1.8rem);
    font-weight:400;
    color:var(--text-secondary);
    margin-bottom:40px;
}
.hero-cta{
    display:flex;
    gap:20px;
    justify-content:center;
    opacity:0;
    transform:translateY(20px);
    animation: heroCtaFade 1.5s cubic-bezier(0.16, 1, 0.3, 1) forwards 2.3s;
}
.hero-cta-btn{
    padding:16px 32px;
    border-radius:980px;
    font-size:16px;
    font-weight:600;
    text-decoration:none;
    transition:all 0.3s cubic-bezier(0.2, 0.8, 0.2, 1);
}
.hero-cta-btn-primary{
    background:#fff;
    color:#000;
    box-shadow:0 10px 30px rgba(255,255,255,0.15);
}
.hero-cta-btn-primary:hover{
    transform:scale(1.05);
    box-shadow:0 15px 40px rgba(255,255,255,0.25);
}
.hero-cta-btn-secondary{
    background:rgba(255,255,255,0.1);
    color:#fff;
    border:1px solid rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}
.hero-cta-btn-secondary:hover{
    background:rgba(255,255,255,0.15);
    border-color: rgba(255,255,255,0.3);
    transform:translateY(-2px);
}

@keyframes heroTitleFade {
    0% {
        opacity: 0;
        transform: translateY(40px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes heroSubtitleFade {
    0% {
        opacity: 0;
        transform: translateY(30px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes heroCtaFade {
    0% {
        opacity: 0;
        transform: translateY(20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Scroll Arrow */
.scroll-arrow {
    position: absolute;
    bottom: 60px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    opacity: 0;
    animation: fadeInArrow 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards 2.5s;
    cursor: pointer;
    z-index: 10;
}

.scroll-arrow svg {
    width: 28px;
    height: 28px;
    color: var(--text-primary);
    opacity: 0.7;
    animation: bounceArrow 2s infinite;
}

.scroll-arrow span {
    font-size: 12px;
    color: var(--text-secondary);
    opacity: 0.6;
    font-weight: 500;
    letter-spacing: 0.5px;
}

@keyframes fadeInArrow {
    to { opacity: 1; }
}

@keyframes bounceArrow {
    0%, 100% { transform: translateY(0); opacity: 0.5; }
    50% { transform: translateY(8px); opacity: 1; }
}

/* Intro Shape Animation */
.intro-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    background: var(--bg-main);
    animation: introFadeOut 0.8s ease forwards 1.5s;
    pointer-events: none;
}

.intro-shape {
    position: relative;
    width: 500px;
    height: 180px;
    -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 726 252.17'%3E%3Cpath d='M483.92 0S481.38 24.71 466 40.11c-11.74 11.74-24.09 12.66-40.26 15.07-9.42 1.41-29.7 3.77-34.81-.79-2.37-2.11-3-21-3.22-27.62-.21-6.92-1.36-16.52-2.82-18-.75 3.06-2.49 11.53-3.09 13.61S378.49 34.3 378 36a85.13 85.13 0 0 0-30.09 0c-.46-1.67-3.17-11.48-3.77-13.56s-2.34-10.55-3.09-13.61c-1.45 1.45-2.61 11.05-2.82 18-.21 6.67-.84 25.51-3.22 27.62-5.11 4.56-25.38 2.2-34.8.79-16.16-2.47-28.51-3.39-40.21-15.13C244.57 24.71 242 0 242 0H0s69.52 22.74 97.52 68.59c16.56 27.11 14.14 58.49 9.92 74.73C170 140 221.46 140 273 158.57c69.23 24.93 83.2 76.19 90 93.6 6.77-17.41 20.75-68.67 90-93.6 51.54-18.56 103-18.59 165.56-15.25-4.21-16.24-6.63-47.62 9.93-74.73C656.43 22.74 726 0 726 0z'/%3E%3C/svg%3E") no-repeat center;
    -webkit-mask-size: contain;
    mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 726 252.17'%3E%3Cpath d='M483.92 0S481.38 24.71 466 40.11c-11.74 11.74-24.09 12.66-40.26 15.07-9.42 1.41-29.7 3.77-34.81-.79-2.37-2.11-3-21-3.22-27.62-.21-6.92-1.36-16.52-2.82-18-.75 3.06-2.49 11.53-3.09 13.61S378.49 34.3 378 36a85.13 85.13 0 0 0-30.09 0c-.46-1.67-3.17-11.48-3.77-13.56s-2.34-10.55-3.09-13.61c-1.45 1.45-2.61 11.05-2.82 18-.21 6.67-.84 25.51-3.22 27.62-5.11 4.56-25.38 2.2-34.8.79-16.16-2.47-28.51-3.39-40.21-15.13C244.57 24.71 242 0 242 0H0s69.52 22.74 97.52 68.59c16.56 27.11 14.14 58.49 9.92 74.73C170 140 221.46 140 273 158.57c69.23 24.93 83.2 76.19 90 93.6 6.77-17.41 20.75-68.67 90-93.6 51.54-18.56 103-18.59 165.56-15.25-4.21-16.24-6.63-47.62 9.93-74.73C656.43 22.74 726 0 726 0z'/%3E%3C/svg%3E") no-repeat center;
    mask-size: contain;
    transform: scale(0.8);
    opacity: 0;
    animation: introScale 1.5s ease forwards 0.3s;
}

.intro-shape::before {
    content: '';
    position: absolute;
    width: 0%;
    height: 100%;
    background: var(--text-primary);
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    animation: introFill 1.5s ease forwards 0.3s;
}

.intro-shape::after {
    content: '';
    position: absolute;
    inset: 0;
    box-shadow: 0 0 0 0 var(--text-primary);
    animation: introGlow 2s ease forwards 0.5s;
}

@keyframes introScale {
    0% {
        transform: scale(0.8);
        opacity: 0;
    }
    70% {
        transform: scale(1.2);
        opacity: 1;
    }
    100% {
        transform: scale(1.2) translateY(-100vh);
        opacity: 1;
    }
}

@keyframes introFill {
    to {
        width: 100%;
    }
}

@keyframes introGlow {
    0% {
        box-shadow: 0 0 0 0 var(--text-primary);
    }
    100% {
        box-shadow: 0 -13px 56px 12px rgba(255,255,255,0.67);
    }
}

@keyframes introFadeOut {
    to {
        opacity: 0;
        visibility: hidden;
    }
}

@media (max-width: 768px) {
    .intro-shape {
        width: 250px;
        height: 90px;
    }
}


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
.fade-content h2 { 
    font-size: clamp(3rem, 6vw, 5rem); 
    font-weight: 700; 
    margin-bottom: 20px; 
    background: linear-gradient(135deg, var(--text-primary), #888); 
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    opacity: 0;
    transform: translateY(40px) scale(0.95);
}
.fade-content p { 
    font-size: clamp(1.2rem, 2vw, 1.5rem); 
    color: var(--text-secondary); 
    max-width: 600px; 
    line-height: 1.5;
    opacity: 0;
    transform: translateY(30px);
}
.carousel { 
    position: relative; 
    height: auto;
    min-height: 120px;
    width: 100%; 
    display: flex; 
    flex-direction: column;
    justify-content: center; 
    align-items: center; 
    gap: 10px;
    padding: 20px 0;
}
.word-line {
    position: relative;
    font-size: clamp(3rem, 8vw, 6rem);
    font-weight: 700;
    opacity: 0;
    color: var(--text-primary);
    transform: translateY(30px);
    background: linear-gradient(135deg, var(--text-primary), #888);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
@media (max-width: 768px) {
    .word-line {
        font-size: clamp(2rem, 10vw, 4rem);
        transform: translateY(20px);
    }
}
.list-effect { 
    display: flex; 
    flex-direction: column; 
    gap: 24px; 
    width: 100%; 
    max-width: 700px;
    text-align: center;
    padding: 20px;
}
.list-title { 
    font-size: clamp(2rem, 4vw, 3rem); 
    font-weight: 700; 
    color: var(--text-primary); 
    margin-bottom: 20px;
    text-align: center;
    width: 100%;
    opacity: 0;
    transform: translateY(40px) scale(0.95);
    background: linear-gradient(135deg, var(--text-primary), #888);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.list-item { 
    display: flex; 
    align-items: center; 
    justify-content: center;
    gap: 16px; 
    font-size: clamp(1.3rem, 2.5vw, 1.8rem); 
    font-weight: 500; 
    color: var(--text-primary); 
    opacity: 1;
    transform: scale(0.9);
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 16px;
    padding: 20px 28px;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.list-item:hover {
    transform: scale(1.02);
    background: rgba(255,255,255,0.05);
    border-color: rgba(255,255,255,0.15);
}
.list-bullet { 
    width: 10px; 
    height: 10px; 
    background: var(--primary); 
    border-radius: 50%; 
    box-shadow: 0 0 20px var(--primary);
    flex-shrink: 0;
}
.blur-section-wrapper {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 24px;
}
.blur-text { 
    font-size: clamp(2.5rem, 6vw, 4.5rem); 
    font-weight: 800; 
    opacity: 0;
    color: var(--text-primary); 
    text-align: center; 
    line-height: 1.2;
    letter-spacing: -0.02em;
    transform: translateY(40px) scale(0.9);
    background: linear-gradient(135deg, var(--text-primary), #888);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.blur-glow {
    display: none;
}
.blur-desc {
    font-size: clamp(1rem, 2vw, 1.3rem);
    color: var(--text-secondary);
    max-width: 500px;
    text-align: center;
    opacity: 0;
    transform: translateY(30px);
}

/* Growing Section Styles */
#section-growing .text-line {
    font-size: clamp(2rem, 5vw, 4rem);
    font-weight: 700;
    color: var(--text-primary);
    opacity: 0;
    margin: 8px 0;
    text-align: center;
    background: linear-gradient(135deg, var(--text-primary), #888);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
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

/* Section Type (Typewriter) Styles */
#section-type .text-line {
    font-size: clamp(1.4rem, 2.5vw, 2rem);
    font-weight: 400;
    color: var(--text-primary);
    opacity: 0;
    margin: 8px 0;
    text-align: center;
    max-width: 800px;
    line-height: 1.8;
    transform: translateY(40px) scale(0.95);
    background: linear-gradient(135deg, var(--text-primary) 0%, var(--text-secondary) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.features-grid-section {
    padding: 120px 20px;
    background: transparent;
    position: relative;
    z-index: 2;
}
.section-label { 
    font-size: 18px; 
    font-weight: 600; 
    color: var(--primary-light); 
    margin-bottom: 10px; 
    text-transform: uppercase; 
    letter-spacing: 0.1em; 
    text-align: center;
    opacity: 0;
    transform: translateY(20px);
}
.section-title { 
    font-size: clamp(2.5rem, 5vw, 4rem); 
    font-weight: 700; 
    margin-bottom: 60px; 
    color: var(--text-primary);
    opacity: 0;
    transform: translateY(30px) scale(0.95);
    background: linear-gradient(135deg, var(--text-primary), #888);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

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
    opacity: 0;
    transform: translateY(40px) scale(0.95);
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
/* CTA Section */
.cta-section {
    min-height: 80vh;
    display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;
    background: transparent;
    padding: 100px 20px; position: relative;
    z-index: 5;
}
.cta-content { position: relative; z-index: 2; }
.cta-section h2 { 
    font-size: clamp(3rem, 6vw, 5rem); 
    font-weight: 700; 
    margin-bottom: 20px; 
    opacity: 0; 
    transform: translateY(40px) scale(0.95);
    color: var(--text-primary);
    background: linear-gradient(135deg, var(--text-primary), #888);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.cta-section p { 
    font-size: clamp(1.2rem, 2vw, 1.5rem); 
    color: var(--text-secondary); 
    margin-bottom: 50px; 
    opacity: 0; 
    transform: translateY(30px);
    max-width: 600px;
}
.cta-buttons { 
    display: flex; 
    gap: 20px; 
    justify-content: center; 
    flex-wrap: wrap; 
    opacity: 0;
    transform: translateY(20px);
}
.cta-btn { padding: 18px 40px; border-radius: 980px; font-size: 18px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; }
.cta-btn-primary { background: var(--primary); color: #fff; box-shadow: 0 10px 30px rgba(94, 96, 206, 0.3); }
.cta-btn-primary:hover { transform: translateY(-3px); box-shadow: 0 15px 40px rgba(94, 96, 206, 0.5); }
.cta-btn-secondary { background: transparent; border: 2px solid var(--text-primary); color: var(--text-primary); opacity: 0.7; }
.cta-btn-secondary:hover { background: var(--card-bg); border-color: var(--text-primary); opacity: 1; }

footer{
    background:transparent;
    padding:50px 20px;
    font-size:15px;
    color:var(--text-secondary);
    text-align:center;
    border-top:1px solid var(--nav-border);
}
footer a { color: var(--text-secondary); transition: color 0.3s ease; }
footer a:hover { color: var(--primary); }

@media(max-width:768px){
    nav{padding:10px 16px;}
    nav.scrolled {
        padding: 10px 16px;
    }
    .nav-container {
        width: 95%;
        padding: 8px 16px;
        border-radius: 80px;
        background: rgba(255,255,255,0.05);
        backdrop-filter: blur(40px) saturate(150%);
        -webkit-backdrop-filter: blur(40px) saturate(150%);
        border: 1px solid rgba(255,255,255,0.08);
        transform: translateZ(0);
        -webkit-transform: translateZ(0);
        contain: layout style;
        box-shadow: 0 8px 32px rgba(0,0,0,0.2), inset 0 1px 0 rgba(255,255,255,0.1);
    }
    nav.scrolled .nav-container {
        width: 95%;
        padding: 8px 16px;
        border-radius: 80px;
        background: rgba(255,255,255,0.05);
        border-color: rgba(255,255,255,0.08);
        box-shadow: 0 8px 32px rgba(0,0,0,0.2), inset 0 1px 0 rgba(255,255,255,0.1);
    }
    .hero-cta{flex-direction:column;align-items:center}
    .list-effect { padding: 0 20px; }
    .cta-buttons { flex-direction: column; align-items: center; }
    .menu-toggle { display: flex; }
    #themeToggle { width: 36px; height: 36px; }
    #themeToggle svg { width: 18px; height: 18px; }
    .scroll-arrow {
        bottom: 110px;
    }
    .scroll-arrow svg {
        width: 24px;
        height: 24px;
    }
    .scroll-arrow span {
        font-size: 11px;
    }
    .nav-links {
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        flex-direction: column;
        background: rgba(0,0,0,0.92);
        backdrop-filter: blur(20px) saturate(150%);
        -webkit-backdrop-filter: blur(20px) saturate(150%);
        padding: 25px 20px;
        gap: 15px;
        opacity: 0;
        pointer-events: none;
        transform: translateY(-10px);
        transition: all 0.3s ease;
        border-top: 1px solid var(--nav-border);
        border-bottom: 1px solid var(--nav-border);
        will-change: auto;
    }
    [data-theme="light"] .nav-links {
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(20px) saturate(150%);
        -webkit-backdrop-filter: blur(20px) saturate(150%);
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

/* Language Switcher - Landing Page Style */
.language-switcher {
    position: relative;
    display: inline-block;
}

.language-toggle {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 20px;
    padding: 8px 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
    color: #ffffff;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
}

.language-toggle:hover {
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.3);
}

[data-theme="light"] .language-toggle {
    background: rgba(0, 0, 0, 0.05);
    border: 1px solid rgba(0, 0, 0, 0.1);
    color: #111111;
}

[data-theme="light"] .language-toggle:hover {
    background: rgba(0, 0, 0, 0.1);
    border-color: rgba(0, 0, 0, 0.15);
}

.language-dropdown {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 8px;
    min-width: 180px;
    background: rgba(22, 22, 22, 0.98);
    backdrop-filter: blur(40px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    z-index: 1000;
    overflow: hidden;
    padding: 8px;
}

[data-theme="light"] .language-dropdown {
    background: rgba(249, 250, 251, 0.98);
    border: 1px solid rgba(0, 0, 0, 0.1);
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
}

.language-dropdown.show {
    display: block !important;
}

.language-option {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 8px;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s;
    margin-bottom: 4px;
    cursor: pointer;
}

[data-theme="light"] .language-option {
    color: #111111;
}

.language-option:hover {
    background: rgba(255, 255, 255, 0.05);
}

[data-theme="light"] .language-option:hover {
    background: rgba(0, 0, 0, 0.05);
}

.language-option.active {
    background: rgba(94, 96, 206, 0.1);
    color: #5e60ce;
    font-weight: 600;
}

.language-option.active:hover {
    background: rgba(94, 96, 206, 0.15);
}

.language-header {
    padding: 8px 12px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 4px;
}

[data-theme="light"] .language-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.language-header span {
    font-size: 12px;
    font-weight: 600;
    color: #86868b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    /* Header/Nav Always LTR */
    nav,
    .nav-container,
    .nav-container * {
        direction: ltr !important;
        text-align: left !important;
    }

    .language-toggle {
        padding: 6px 10px;
        font-size: 12px;
    }

    .language-toggle .current-locale,
    .language-toggle .lang-divider,
    .language-toggle .lang-alt {
        display: none;
    }

    .language-toggle span:first-child {
        font-size: 16px;
    }

    .language-dropdown {
        min-width: 160px;
    }

    .language-option {
        padding: 8px 12px;
        gap: 10px;
    }

    .language-option span:first-child {
        font-size: 16px;
    }

    .language-option div span {
        font-size: 13px;
    }
}
</style>
</head>
<body>

<!-- Intro Animation -->
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
            <!-- <p class="blur-desc">{{ __('home.your_privacy_protected_desc') }}</p> -->
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

<script>
// Apply saved theme immediately (before page loads)
(function() {
    const savedTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
})();

// Video Background Optimization - Pause when not visible
(function() {
    const heroVideo = document.querySelector('.hero-bg-video video');
    if (heroVideo) {
        // Pause video when not in viewport
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    heroVideo.play();
                } else {
                    heroVideo.pause();
                }
            });
        }, { threshold: 0.1 });
        
        observer.observe(heroVideo);
        
        // Pause video when tab is not active
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                heroVideo.pause();
            } else {
                heroVideo.play();
            }
        });
    }
})();

window.addEventListener('load', function() {
gsap.registerPlugin(ScrollTrigger);

// Cache DOM queries
const navLinks = document.getElementById('navLinks');
const themeToggle = document.getElementById('themeToggle');
const sunIcon = themeToggle.querySelector('.sun');
const moonIcon = themeToggle.querySelector('.moon');

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

// Refresh ScrollTrigger to ensure all triggers are calculated
ScrollTrigger.refresh();

// Header scroll animation
const nav = document.querySelector('nav');
let lastScroll = 0;

window.addEventListener('scroll', () => {
    const currentScroll = window.pageYOffset;
    
    // Add/remove scrolled class for transform effect
    if (currentScroll > 50) {
        nav.classList.add('scrolled');
    } else {
        nav.classList.remove('scrolled');
    }
    
    lastScroll = currentScroll;
}, { passive: true });


// Section 1: "Built for Real Connections" - Framer Motion Style Fade
gsap.timeline({
    scrollTrigger: {
        trigger: '#section-fade',
        start: 'top 80%',
        once: true
    }
})
.to('#section-fade .fade-content h2', {
    opacity: 1,
    y: 0,
    scale: 1,
    duration: 1.5,
    ease: 'back.out(1.7)'
}, 0)
.to('#section-fade .fade-content p', {
    opacity: 1,
    y: 0,
    duration: 1.2,
    ease: 'power2.out'
}, 0.5);

// Section 2: Word Carousel - Auto fade sequence (no pin, no scrub)
const words = ['#word1','#word2','#word3','#word4'];

// Set initial state - hide all words
gsap.set(words, { opacity: 0, y: 30 });

// Create timeline
const tlWords = gsap.timeline({
    scrollTrigger: {
        trigger: '#section-carousel',
        start: 'top 75%',
        once: true
    }
});

// Each word fades in from bottom and stays visible
words.forEach((sel, i) => {
    tlWords.to(sel, {
        opacity: 1,
        y: 0,
        duration: 0.8,
        ease: 'power2.out'
    }, i * 0.6);
});

// Feature Cards - Framer Motion Style Stagger Effect
const sectionLabel = document.querySelector('#section-features .section-label');
const sectionTitle = document.querySelector('#section-features .section-title');
const cards = document.querySelectorAll('#section-features .card');

// Set initial state
gsap.set(sectionLabel, { opacity: 0, y: 20 });
gsap.set(sectionTitle, { opacity: 0, y: 30 });
gsap.set(cards, { opacity: 0, y: 40, scale: 0.95 });

gsap.timeline({
    scrollTrigger: {
        trigger: '#section-features',
        start: 'top 75%',
        once: true
    }
})
.to(sectionLabel, {
    opacity: 1,
    y: 0,
    duration: 0.8,
    ease: 'power2.out'
}, 0)
.to(sectionTitle, {
    opacity: 1,
    y: 0,
    scale: 1,
    duration: 1.2,
    ease: 'power2.out'
}, 0.2)
.to(cards, {
    opacity: 1,
    y: 0,
    scale: 1,
    duration: 1,
    stagger: 0.15,
    ease: 'back.out(1.7)'
}, 0.5);

// Section 3: Staggered List - Framer Motion Style Effect (auto fade)
const listTitle = document.querySelector('.list-title');
const listItems = document.querySelectorAll('.list-item');

// Set initial state
gsap.set(listTitle, { opacity: 0, scale: 0.7, y: 40 });
gsap.set(listItems, { 
    opacity: 0, 
    scale: 0.8, 
    y: 30 
});

gsap.timeline({ 
    scrollTrigger: { 
        trigger: '#section-list', 
        start: 'top 75%',
        once: true
    } 
})
.to(listTitle, { 
    opacity: 1, 
    scale: 1, 
    y: 0, 
    duration: 1, 
    ease: 'back.out(1.7)' 
}, 0)
.to(listItems, { 
    opacity: 1, 
    scale: 1, 
    y: 0, 
    duration: 0.8, 
    stagger: 0.2, 
    ease: 'back.out(1.7)' 
}, 0.4);

// Section 4: Blur Reveal - Auto Fade on View (no pin, no scrub)
const blurText = document.querySelector('.e6-blur');
const blurDesc = document.querySelector('.blur-desc');

// Set initial state
gsap.set(blurText, { opacity: 0, y: 40, scale: 0.9 });
gsap.set(blurDesc, { opacity: 0, y: 30 });

gsap.timeline({ 
    scrollTrigger: { 
        trigger: '#section-blur', 
        start: 'top 75%',
        once: true
    } 
})
.to(blurText, { 
    opacity: 1, 
    y: 0, 
    scale: 1, 
    duration: 1.2, 
    ease: 'back.out(1.7)' 
}, 0)
.to(blurDesc, { 
    opacity: 1, 
    y: 0, 
    duration: 1, 
    ease: 'power2.out' 
}, 0.5);

// Section 5: Typewriter - Framer Motion Style Fade (no pin, no scrub)
gsap.timeline({
    scrollTrigger: {
        trigger: '#section-type',
        start: 'top 75%',
        once: true
    }
}).to('#section-type .text-line', {
    opacity: 1,
    y: 0,
    scale: 1,
    duration: 2.5,
    ease: 'power2.out'
});

// Growing Section - Stats - Auto fade with stagger (no pin, no scrub)
gsap.timeline({
    scrollTrigger: {
        trigger: '#section-growing',
        start: 'top 75%',
        once: true
    }
})
.to('#section-growing .growing-title', {
    opacity: 1,
    y: 0,
    duration: 1,
    ease: 'power2.out'
}, 0)
.to('#section-growing .stat-row', {
    opacity: 1,
    y: 0,
    stagger: 0.2,
    duration: 0.8,
    ease: 'power2.out'
}, 0.4);

// CTA Section - Framer Motion Style Fade (no pin, no scrub)
gsap.timeline({ 
    scrollTrigger: { 
        trigger: '#section-cta', 
        start: 'top 80%',
        once: true
    } 
})
.to('.cta-title', { 
    opacity: 1, 
    y: 0, 
    scale: 1,
    duration: 1.5, 
    ease: 'power3.out' 
}, 0)
.to('.cta-desc', { 
    opacity: 1, 
    y: 0, 
    duration: 1.2, 
    ease: 'power3.out' 
}, 0.4)
.to('.cta-buttons', { 
    opacity: 1, 
    y: 0,
    duration: 1, 
    ease: 'power3.out' 
}, 0.8);

// Mobile menu - attach event listener
const menuToggle = document.getElementById('menuToggle');
if (menuToggle && navLinks) {
    menuToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        navLinks.classList.toggle('active');
        menuToggle.classList.toggle('active');
    });

    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!navLinks.contains(e.target) && !menuToggle.contains(e.target)) {
            navLinks.classList.remove('active');
            menuToggle.classList.remove('active');
        }
    });
}

// Close menu when clicking a link - Simple scroll without ScrollTrigger interference
document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        navLinks.classList.remove('active');
        menuToggle.classList.remove('active');

        const targetId = link.getAttribute('href');
        const targetSection = document.querySelector(targetId);

        if (targetSection) {
            const navHeight = 80;
            const targetPosition = targetSection.offsetTop - navHeight;

            // Simple smooth scroll - let GSAP handle itself
            window.scrollTo({ top: targetPosition, behavior: 'smooth' });
        }
    });
});

// Scroll arrow click handler
function scrollToSection() {
    const firstSection = document.getElementById('section-fade');
    if (firstSection) {
        const navHeight = 80;
        const targetPosition = firstSection.offsetTop - navHeight;
        window.scrollTo({ top: targetPosition, behavior: 'smooth' });
    }
}

});
</script>
</body>
</html>
