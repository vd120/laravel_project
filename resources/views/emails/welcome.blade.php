<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('emails.welcome_subject', ['app_name' => config('app.name')]) }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f3f4f6; padding: 20px; }
        .container { max-width: 650px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #5e60ce 0%, #7400b8 100%); padding: 40px 24px; text-align: center; }
        .header h1 { color: #ffffff; font-size: 28px; margin-bottom: 8px; font-weight: 700; }
        .header p { color: rgba(255, 255, 255, 0.95); font-size: 16px; }
        .content { padding: 32px 24px; }
        .welcome-box { background: linear-gradient(135deg, rgba(94, 96, 206, 0.1), rgba(116, 0, 184, 0.1)); border: 2px solid #5e60ce; padding: 20px; border-radius: 12px; margin-bottom: 24px; text-align: center; }
        .welcome-box h2 { color: #5e60ce; font-size: 20px; margin-bottom: 8px; font-weight: 600; }
        .welcome-box p { color: #4b5563; font-size: 14px; line-height: 1.6; }
        .greeting { font-size: 18px; color: #1f2937; margin-bottom: 16px; font-weight: 600; }
        .message { font-size: 15px; color: #4b5563; line-height: 1.8; margin-bottom: 24px; }
        .features { background: #f9fafb; border-radius: 12px; padding: 20px; margin-bottom: 24px; }
        .features h3 { color: #1f2937; font-size: 16px; margin-bottom: 16px; font-weight: 600; }
        .feature-item { display: flex; align-items: flex-start; gap: 12px; padding: 12px 0; border-bottom: 1px solid #e5e7eb; }
        .feature-item:last-child { border-bottom: none; }
        .feature-icon { width: 36px; height: 36px; background: linear-gradient(135deg, #5e60ce, #7400b8); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .feature-icon i { color: #ffffff; font-size: 16px; }
        .feature-text { color: #4b5563; font-size: 14px; line-height: 1.6; }
        .cta-button { display: inline-block; padding: 16px 32px; background: linear-gradient(135deg, #5e60ce, #7400b8); color: #ffffff; text-decoration: none; border-radius: 12px; font-weight: 600; font-size: 16px; text-align: center; margin: 20px 0; box-shadow: 0 4px 12px rgba(94, 96, 206, 0.3); transition: all 0.2s; }
        .cta-button:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(94, 96, 206, 0.4); }
        .footer { background: #f9fafb; padding: 24px; text-align: center; border-top: 1px solid #e5e7eb; }
        .footer p { color: #6b7280; font-size: 13px; line-height: 1.6; margin-bottom: 8px; }
        .footer a { color: #5e60ce; text-decoration: none; font-weight: 600; }
        .footer a:hover { text-decoration: underline; }
        .social-links { margin-top: 16px; }
        .social-links a { display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; background: #5e60ce; color: #ffffff; border-radius: 50%; margin: 0 6px; text-decoration: none; transition: all 0.2s; }
        .social-links a:hover { background: #7400b8; transform: translateY(-2px); }

        @media (max-width: 600px) {
            .header { padding: 30px 20px; }
            .header h1 { font-size: 24px; }
            .content { padding: 24px 16px; }
            .welcome-box { padding: 16px; }
            .cta-button { width: 100%; padding: 14px 24px; }
        }

        [dir="rtl"] { text-align: right; }
        [dir="rtl"] .feature-item { flex-direction: row-reverse; }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="header">
            <h1>🎉 {{ __('emails.welcome_title') }}</h1>
            <p>{{ __('emails.welcome_subtitle') }}</p>
        </div>

        {{-- Content --}}
        <div class="content">
            {{-- Welcome Box --}}
            <div class="welcome-box">
                <h2>{{ __('emails.welcome_to_nexus') }}</h2>
                <p>{{ __('emails.welcome_message') }}</p>
            </div>

            {{-- Greeting --}}
            <div class="greeting">
                {{ __('emails.hello') }} {{ $user->name ?? $user->username }}!
            </div>

            <p class="message">{{ __('emails.getting_started_message') }}</p>

            {{-- CTA Button --}}
            <div style="text-align: center;">
                <a href="{{ $verificationUrl }}" class="cta-button">{{ __('emails.verify_email_button') }}</a>
            </div>

            <p class="message" style="text-align: center; color: #6b7280; font-size: 13px;">
                {{ __('emails.verify_email_note') }}
            </p>

            {{-- Features --}}
            <div class="features">
                <h3>{{ __('emails.what_you_can_do') }}</h3>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="feature-text">
                        <strong>{{ __('emails.feature_stories_title') }}</strong><br>
                        {{ __('emails.feature_stories_desc') }}
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-message"></i>
                    </div>
                    <div class="feature-text">
                        <strong>{{ __('emails.feature_chat_title') }}</strong><br>
                        {{ __('emails.feature_chat_desc') }}
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="feature-text">
                        <strong>{{ __('emails.feature_ai_title') }}</strong><br>
                        {{ __('emails.feature_ai_desc') }}
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="feature-text">
                        <strong>{{ __('emails.feature_community_title') }}</strong><br>
                        {{ __('emails.feature_community_desc') }}
                    </div>
                </div>
            </div>

            {{-- Security Note --}}
            <p class="message" style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 16px; border-radius: 8px;">
                <strong>🔒 {{ __('emails.security_note_title') }}</strong><br>
                {{ __('emails.security_note_text') }}
            </p>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p>{{ __('emails.footer_welcome_text') }}</p>
            <p>{{ __('emails.footer_need_help') }} <a href="{{ route('home') }}">{{ __('emails.footer_contact_us') }}</a></p>
            
            <div class="social-links">
                <a href="{{ route('home') }}" title="Home"><i class="fas fa-home"></i></a>
                <a href="{{ route('stories.index') }}" title="Stories"><i class="fas fa-newspaper"></i></a>
                <a href="{{ route('chat.index') }}" title="Chat"><i class="fas fa-message"></i></a>
                <a href="{{ route('ai.index') }}" title="AI"><i class="fas fa-robot"></i></a>
            </div>
            
            <p style="margin-top: 16px;">
                <a href="{{ route('home') }}">{{ config('app.name') }}</a>
            </p>
        </div>
    </div>
</body>
</html>
