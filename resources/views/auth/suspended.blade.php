@extends('layouts.app')

@section('title', 'Account Suspended')

@section('content')
<div class="login-page">
    <div class="login-card">
        <!-- <div class="suspended-icon">
            <i class="fas fa-ban"></i>
        </div> -->

        <h1 class="title">Account Suspended</h1>
        <p class="subtitle">Your account has been temporarily suspended due to a violation of our community guidelines. During this suspension, you cannot access most features of the platform.</p>

        <div class="contact-section">
            <h3><i class="fas fa-envelope"></i> Need Help?</h3>
            <p>If you believe this is a mistake or would like to appeal this decision, please contact our support team for assistance.</p>
        </div>

        <p class="footer">
            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i> Sign Out
            </a>
        </p>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
    </div>
</div>

<style>
.login-page {min-height: calc(100vh - 64px);display: flex;align-items: center;justify-content: center;padding: 20px;background: var(--bg);}
.login-card {width: 100%;max-width: 400px;background: var(--surface);border: 1px solid var(--border);border-radius: 16px;padding: 32px 28px;text-align: center;}
/* .suspended-icon {width: 80px;height: 80px;background: linear-gradient(135deg, var(--accent), #f97316);border-radius: 16px;display: flex;align-items: center;justify-content: center;margin: 0 auto 24px;font-size: 36px;color: white;} */
.title {font-size: 24px;font-weight: 700;color: var(--text);margin: 0 0 12px 0;text-align: center;}
.subtitle {font-size: 14px;color: var(--text-muted);margin: 0 0 24px 0;text-align: center;line-height: 1.6;}
.contact-section {padding: 20px;background: var(--bg);border: 1px solid var(--border);border-radius: 12px;margin-bottom: 24px;}
.contact-section h3 {font-size: 15px;font-weight: 600;margin: 0 0 8px 0;color: var(--text);}
.contact-section p {font-size: 13px;color: var(--text-muted);margin: 0;line-height: 1.5;}
.footer {text-align: center;padding-top: 20px;border-top: 1px solid var(--border);font-size: 14px;}
.footer a {color: var(--primary);text-decoration: none;font-weight: 600;}
.footer a:hover {text-decoration: underline;}
</style>
@endsection
