@extends('layouts.app')

@section('title', 'Welcome to Laravel Social - Connect, Share, Discover')

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-container">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">Welcome to <span class="highlight">Laravel Social</span></h1>
                <p class="hero-subtitle">Connect with friends, share your moments, and discover amazing content from creators around the world.</p>

                <!-- Feature highlights -->
                <div class="hero-features">
                    <div class="feature-item">
                        <i class="fas fa-users"></i>
                        <span>Connect with Friends</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-share"></i>
                        <span>Share Your Stories</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-heart"></i>
                        <span>Discover Content</span>
                    </div>
                </div>

                <!-- CTA Buttons -->
                <div class="hero-buttons">
                    <a href="{{ route('register') }}" class="btn btn-primary">
                        <i class="fas fa-rocket"></i>
                        Get Started Free
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-secondary">
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In
                    </a>
                </div>
            </div>

            <div class="hero-visual">
                <div class="floating-cards">
                    <div class="card card-1">
                        <div class="card-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="card-content">
                            <div class="card-title">John Doe</div>
                            <div class="card-text">Just shared an amazing photo! 📸</div>
                        </div>
                    </div>
                    <div class="card card-2">
                        <div class="card-avatar">
                            <i class="fas fa-user-friends"></i>
                        </div>
                        <div class="card-content">
                            <div class="card-title">Jane Smith</div>
                            <div class="card-text">Connected with 50+ friends this week!</div>
                        </div>
                    </div>
                    <div class="card card-3">
                        <div class="card-avatar">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="card-content">
                            <div class="card-title">Mike Johnson</div>
                            <div class="card-text">Loved this new feature! ❤️</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Animated background elements -->
    <div class="hero-bg-elements">
        <div class="bg-circle circle-1"></div>
        <div class="bg-circle circle-2"></div>
        <div class="bg-circle circle-3"></div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section">
    <div class="container">
        <div class="section-header">
            <h2>Why Choose Laravel Social?</h2>
            <p>Experience the next generation of social networking with our powerful features</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-edit"></i>
                </div>
                <h3>Share Your Story</h3>
                <p>Post photos, videos, and text to share your moments with friends and followers.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-user-friends"></i>
                </div>
                <h3>Connect & Network</h3>
                <p>Build meaningful relationships and expand your network with like-minded people.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <h3>Real-time Chat</h3>
                <p>Stay connected with instant messaging and group conversations.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-camera"></i>
                </div>
                <h3>Stories & Moments</h3>
                <p>Share temporary moments that disappear after 24 hours.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>Discover Content</h3>
                <p>Explore trending posts and discover new content from around the world.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Privacy First</h3>
                <p>Your privacy matters. Control who sees your posts and personal information.</p>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number" data-target="10000">0</div>
                <div class="stat-label">Active Users</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" data-target="50000">0</div>
                <div class="stat-label">Posts Shared</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" data-target="25000">0</div>
                <div class="stat-label">Connections Made</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" data-target="99">0</div>
                <div class="stat-label">Uptime</div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials-section">
    <div class="container">
        <div class="section-header">
            <h2>What Our Users Say</h2>
            <p>Join thousands of satisfied users who love Laravel Social</p>
        </div>

        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <i class="fas fa-quote-left"></i>
                    <p>"Laravel Social has completely changed how I connect with my friends. The interface is intuitive and the features are amazing!"</p>
                </div>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="author-info">
                        <div class="author-name">Sarah Johnson</div>
                        <div class="author-role">Content Creator</div>
                    </div>
                </div>
            </div>

            <div class="testimonial-card">
                <div class="testimonial-content">
                    <i class="fas fa-quote-left"></i>
                    <p>"The real-time chat feature makes staying in touch so easy. I love how seamless everything works!"</p>
                </div>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="author-info">
                        <div class="author-name">Mike Chen</div>
                        <div class="author-role">Business Owner</div>
                    </div>
                </div>
            </div>

            <div class="testimonial-card">
                <div class="testimonial-content">
                    <i class="fas fa-quote-left"></i>
                    <p>"Discovering new content has never been easier. The algorithm really understands what I like!"</p>
                </div>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="author-info">
                        <div class="author-name">Emily Davis</div>
                        <div class="author-role">Student</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Ready to Join the Community?</h2>
            <p>Create your free account today and start connecting with people who share your interests.</p>
            <div class="cta-buttons">
                <a href="{{ route('register') }}" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i>
                    Sign Up Now
                </a>
                <a href="{{ route('login') }}" class="btn btn-outline">
                    <i class="fas fa-sign-in-alt"></i>
                    Already Have Account?
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

<style>
/* Hero Section */
.hero-section {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    overflow: hidden;
    color: white;
}

.hero-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    width: 100%;
}

.hero-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: center;
    min-height: 80vh;
}

.hero-text {
    padding-right: 40px;
}

.hero-title {
    font-size: clamp(2.5rem, 5vw, 4rem);
    font-weight: 700;
    margin-bottom: 1.5rem;
    line-height: 1.1;
}

.hero-title .highlight {
    background: linear-gradient(45deg, #ffd700, #ffed4e);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-subtitle {
    font-size: 1.25rem;
    line-height: 1.6;
    margin-bottom: 2rem;
    opacity: 0.9;
}

.hero-features {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    margin-bottom: 2.5rem;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 500;
}

.feature-item i {
    color: #ffd700;
    font-size: 1.25rem;
}

.hero-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 2rem;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: linear-gradient(45deg, #ffd700, #ffed4e);
    color: #333;
    box-shadow: 0 8px 25px rgba(255, 215, 0, 0.3);
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(255, 215, 0, 0.4);
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(10px);
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.5);
}

.hero-visual {
    position: relative;
}

.floating-cards {
    position: relative;
    height: 400px;
}

.card {
    position: absolute;
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
    animation: float 6s ease-in-out infinite;
}

.card-1 {
    top: 20%;
    left: 10%;
    animation-delay: 0s;
}

.card-2 {
    top: 40%;
    right: 10%;
    animation-delay: 2s;
}

.card-3 {
    bottom: 20%;
    left: 20%;
    animation-delay: 4s;
}

.card-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(45deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

.card-content {
    flex: 1;
}

.card-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.25rem;
}

.card-text {
    font-size: 0.9rem;
    color: #666;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-20px);
    }
}

.hero-bg-elements {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    overflow: hidden;
    z-index: -1;
}

.bg-circle {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    animation: pulse 4s ease-in-out infinite;
}

.circle-1 {
    width: 300px;
    height: 300px;
    top: 10%;
    right: 10%;
    animation-delay: 0s;
}

.circle-2 {
    width: 200px;
    height: 200px;
    bottom: 20%;
    left: 15%;
    animation-delay: 2s;
}

.circle-3 {
    width: 150px;
    height: 150px;
    top: 50%;
    right: 20%;
    animation-delay: 1s;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        opacity: 0.3;
    }
    50% {
        transform: scale(1.1);
        opacity: 0.6;
    }
}

/* Features Section */
.features-section {
    padding: 80px 0;
    background: #f8fafc;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.section-header {
    text-align: center;
    margin-bottom: 60px;
}

.section-header h2 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 1rem;
}

.section-header p {
    font-size: 1.2rem;
    color: #64748b;
    max-width: 600px;
    margin: 0 auto;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
}

.feature-card {
    background: white;
    padding: 2.5rem;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

.feature-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    background: linear-gradient(45deg, #667eea, #764ba2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
}

.feature-card h3 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 1rem;
}

.feature-card p {
    color: #64748b;
    line-height: 1.6;
}

/* Stats Section */
.stats-section {
    padding: 60px 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 2rem;
    text-align: center;
}

.stat-item {
    padding: 2rem;
}

.stat-number {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    background: linear-gradient(45deg, #ffd700, #ffed4e);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.stat-label {
    font-size: 1.1rem;
    opacity: 0.9;
}

/* Testimonials Section */
.testimonials-section {
    padding: 80px 0;
    background: white;
}

.testimonials-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
}

.testimonial-card {
    background: #f8fafc;
    padding: 2rem;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
}

.testimonial-content {
    margin-bottom: 1.5rem;
}

.testimonial-content i {
    color: #667eea;
    font-size: 1.5rem;
    margin-bottom: 1rem;
    display: block;
}

.testimonial-content p {
    color: #475569;
    font-size: 1.1rem;
    line-height: 1.6;
    font-style: italic;
}

.testimonial-author {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.author-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(45deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.author-info .author-name {
    font-weight: 600;
    color: #1e293b;
}

.author-info .author-role {
    font-size: 0.9rem;
    color: #64748b;
}

/* CTA Section */
.cta-section {
    padding: 80px 0;
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    color: white;
}

.cta-content {
    text-align: center;
    max-width: 600px;
    margin: 0 auto;
}

.cta-content h2 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.cta-content p {
    font-size: 1.2rem;
    opacity: 0.9;
    margin-bottom: 2rem;
}

.cta-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-outline {
    background: transparent;
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.btn-outline:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.5);
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-content {
        grid-template-columns: 1fr;
        gap: 40px;
        text-align: center;
    }

    .hero-text {
        padding-right: 0;
    }

    .hero-visual {
        order: -1;
    }

    .floating-cards {
        height: 300px;
    }

    .features-grid,
    .testimonials-grid {
        grid-template-columns: 1fr;
    }

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }

    .hero-buttons,
    .cta-buttons {
        flex-direction: column;
        align-items: center;
    }

    .btn {
        width: 100%;
        max-width: 300px;
        justify-content: center;
    }

    .section-header h2 {
        font-size: 2rem;
    }

    .feature-card,
    .testimonial-card {
        padding: 1.5rem;
    }
}

@media (max-width: 480px) {
    .hero-title {
        font-size: 2rem;
    }

    .hero-subtitle {
        font-size: 1rem;
    }

    .hero-features {
        justify-content: center;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }

    .stat-number {
        font-size: 2.5rem;
    }
}
</style>

<script>
// Animate stats counter
function animateCounter(element) {
    const target = parseInt(element.getAttribute('data-target'));
    const duration = 2000;
    const step = target / (duration / 16);
    let current = 0;

    const timer = setInterval(() => {
        current += step;
        if (current >= target) {
            element.textContent = target;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 16);
}

// Intersection Observer for stats animation
const observerOptions = {
    threshold: 0.5,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const counters = entry.target.querySelectorAll('.stat-number');
            counters.forEach(counter => {
                if (!counter.classList.contains('animated')) {
                    counter.classList.add('animated');
                    animateCounter(counter);
                }
            });
        }
    });
}, observerOptions);

// Observe stats section
document.addEventListener('DOMContentLoaded', () => {
    const statsSection = document.querySelector('.stats-section');
    if (statsSection) {
        observer.observe(statsSection);
    }
});
</script>