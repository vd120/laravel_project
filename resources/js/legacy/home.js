/* Home Page (Landing Page) JavaScript */

(function() {
    'use strict';

    // Apply saved theme immediately (before page loads)
    (function() {
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    })();

    // Video Background Optimization - Pause when not visible
    (function() {
        const heroVideo = document.querySelector('.hero-bg-video video');
        if (!heroVideo) return;

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
    })();

    // Cache DOM queries and theme toggle (runs on load)
    window.addEventListener('load', function() {
        const navLinks = document.getElementById('navLinks');
        const themeToggle = document.getElementById('themeToggle');
        const sunIcon = themeToggle?.querySelector('.sun');
        const moonIcon = themeToggle?.querySelector('.moon');

        // Theme toggle functionality
        function updateThemeIcons(theme) {
            if (theme === 'light') {
                if (sunIcon) sunIcon.style.display = 'block';
                if (moonIcon) moonIcon.style.display = 'none';
                if (themeToggle) themeToggle.style.borderColor = 'rgba(0,0,0,0.2)';
            } else {
                if (sunIcon) sunIcon.style.display = 'none';
                if (moonIcon) moonIcon.style.display = 'block';
                if (themeToggle) themeToggle.style.borderColor = 'rgba(255,255,255,0.2)';
            }
            if (themeToggle) themeToggle.style.color = 'var(--text-primary)';
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

        // Header scroll animation
        const nav = document.querySelector('nav');
        let lastScroll = 0;

        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;

            if (currentScroll > 50) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }

            lastScroll = currentScroll;
        }, { passive: true });

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

        // Close menu when clicking a link
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
                    window.scrollTo({ top: targetPosition, behavior: 'smooth' });
                }
            });
        });
    });

    // GSAP Animations
    window.initGSAP = function() {
        if (typeof gsap === 'undefined') return;
        
        gsap.registerPlugin(ScrollTrigger);
        ScrollTrigger.refresh();

        // Section 1: "Built for Real Connections" - Fade
        const fadeSection = document.querySelector('#section-fade .fade-content');
        if (fadeSection) {
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
                duration: 1,
                ease: 'power3.out'
            }, 0)
            .to('#section-fade .fade-content p', {
                opacity: 1,
                y: 0,
                duration: 0.8,
                ease: 'power3.out'
            }, 0.4);
        }

        // Section 2: Word Carousel - Auto fade sequence
        const words = ['#word1','#word2','#word3','#word4'];

        if (words.every(sel => document.querySelector(sel))) {
            gsap.set(words, { opacity: 0, y: 30 });

            const tlWords = gsap.timeline({
                scrollTrigger: {
                    trigger: '#section-carousel',
                    start: 'top 75%',
                    once: true
                }
            });

            words.forEach((sel, i) => {
                tlWords.to(sel, {
                    opacity: 1,
                    y: 0,
                    duration: 0.6,
                    ease: 'power2.out'
                }, i * 0.4);
            });
        }

        // Feature Cards - Stagger Effect
        const sectionLabel = document.querySelector('#section-features .section-label');
        const sectionTitle = document.querySelector('#section-features .section-title');
        const cards = document.querySelectorAll('#section-features .card');

        if (sectionLabel && sectionTitle && cards.length > 0) {
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
                duration: 0.6,
                ease: 'power3.out'
            }, 0)
            .to(sectionTitle, {
                opacity: 1,
                y: 0,
                scale: 1,
                duration: 0.8,
                ease: 'power3.out'
            }, 0.2)
            .to(cards, {
                opacity: 1,
                y: 0,
                scale: 1,
                duration: 0.7,
                stagger: 0.1,
                ease: 'power3.out'
            }, 0.4);
        }

        // Section 3: Staggered List
        const listTitle = document.querySelector('#section-list .list-title');
        const listItems = document.querySelectorAll('#section-list .list-item');

        if (listTitle && listItems.length > 0) {
            gsap.set(listTitle, { opacity: 0, scale: 0.7, y: 40 });
            gsap.set(listItems, { opacity: 0, scale: 0.8, y: 30 });

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
                duration: 0.7,
                ease: 'power3.out'
            }, 0)
            .to(listItems, {
                opacity: 1,
                scale: 1,
                y: 0,
                duration: 0.6,
                stagger: 0.12,
                ease: 'power3.out'
            }, 0.3);
        }

        // Section 4: Blur Reveal
        const blurText = document.querySelector('#section-blur .e6-blur');
        const blurDesc = document.querySelector('#section-blur .blur-desc');

        if (blurText) {
            gsap.set(blurText, { opacity: 0, y: 40, scale: 0.9 });
            if (blurDesc) {
                gsap.set(blurDesc, { opacity: 0, y: 30 });
            }

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
                duration: 0.9,
                ease: 'power3.out'
            }, 0)
            .to(blurDesc, {
                opacity: 1,
                y: 0,
                duration: 0.7,
                ease: 'power3.out'
            }, 0.3);
        }

        // Section 5: Typewriter
        const typeSection = document.querySelector('#section-type .text-line');
        if (typeSection) {
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
                duration: 1.5,
                ease: 'power3.out'
            });
        }

        // Growing Section - Stats
        const growingTitle = document.querySelector('#section-growing .growing-title');
        const statRows = document.querySelectorAll('#section-growing .stat-row');
        if (growingTitle && statRows.length > 0) {
            gsap.set('#section-growing .growing-title', { opacity: 0, y: 40, scale: 0.95, filter: 'blur(10px)' });
            gsap.set('#section-growing .stat-row', { opacity: 0, y: 30, scale: 0.9 });

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
                scale: 1,
                filter: 'blur(0px)',
                duration: 0.9,
                ease: 'power3.out'
            }, 0)
            .to('#section-growing .stat-row', {
                opacity: 1,
                y: 0,
                scale: 1,
                stagger: 0.12,
                duration: 0.5,
                ease: 'power3.out'
            }, 0.3);
        }

        // CTA Section
        const ctaTitle = document.querySelector('#section-cta .cta-title');
        const ctaDesc = document.querySelector('#section-cta .cta-desc');
        const ctaButtons = document.querySelector('#section-cta .cta-buttons');
        if (ctaTitle || ctaDesc || ctaButtons) {
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
                duration: 1,
                ease: 'power3.out'
            }, 0)
            .to('.cta-desc', {
                opacity: 1,
                y: 0,
                duration: 0.8,
                ease: 'power3.out'
            }, 0.3)
            .to('.cta-buttons', {
                opacity: 1,
                y: 0,
                duration: 0.7,
                ease: 'power3.out'
            }, 0.5);
        }
    };

    // Scroll arrow click handler
    window.scrollToSection = function() {
        const firstSection = document.getElementById('section-fade');
        if (firstSection) {
            const navHeight = 80;
            const targetPosition = firstSection.offsetTop - navHeight;
            window.scrollTo({ top: targetPosition, behavior: 'smooth' });
        }
    };

    // Initialize GSAP when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof gsap !== 'undefined') {
            window.initGSAP();
        }
    });
})();
