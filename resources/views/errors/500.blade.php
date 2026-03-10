<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('errors.server_error_title') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: #f5f5f5;
            padding: 20px;
        }

        .error-icon {
            font-size: 5rem;
            margin-bottom: 24px;
        }

        h1 {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 12px;
            text-align: center;
        }

        p {
            color: #666;
            margin-bottom: 24px;
            text-align: center;
            max-width: 400px;
            line-height: 1.6;
        }

        button {
            padding: 12px 24px;
            font-size: 1rem;
            color: #fff;
            background: #333;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.2s;
        }

        button:hover {
            background: #555;
        }

        @media (max-width: 480px) {
            h1 { font-size: 1.25rem; }
            p { font-size: 0.875rem; }
            .error-icon { font-size: 4rem; }
        }
    </style>
</head>
<body>
    <div class="error-icon">⚠️</div>
    <h1>{{ __('errors.server_error_title') }}</h1>
    <p>{{ __('errors.server_error_message') }}</p>
    <button onclick="history.back()">{{ __('errors.go_back') }}</button>
</body>
</html>
