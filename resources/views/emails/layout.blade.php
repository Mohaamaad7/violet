<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            text-align: center;
        }

        .header img {
            max-width: 200px;
            height: auto;
        }

        .content {
            padding: 40px 30px;
        }

        .preview-text {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
            font-style: italic;
        }

        .footer {
            background-color: #f9f9f9;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
        }

        .footer p {
            font-size: 12px;
            color: #999;
            margin: 5px 0;
        }

        .unsubscribe {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .unsubscribe a {
            color: #667eea;
            text-decoration: none;
        }

        .unsubscribe a:hover {
            text-decoration: underline;
        }

        /* RTL Support */
        [dir="rtl"] body {
            font-family: 'Tajawal', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        /* Mobile Responsive */
        @media only screen and (max-width: 600px) {
            .content {
                padding: 20px 15px;
            }

            .header {
                padding: 20px 15px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1 style="color: white; margin: 0; font-size: 28px;">{{ config('app.name') }}</h1>
        </div>

        <!-- Main Content -->
        <div class="content">
            @yield('content')
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('newsletter.all_rights_reserved') }}</p>
            <p>{{ \App\Models\Setting::get('site_address') }}</p>
            <p>{{ \App\Models\Setting::get('site_phone') }}</p>

            <div class="unsubscribe">
                <p>
                    {{ __('newsletter.dont_want_emails') }}
                    <a href="{{ $unsubscribeUrl ?? '#' }}">{{ __('newsletter.unsubscribe') }}</a>
                </p>
            </div>
        </div>
    </div>
</body>

</html>