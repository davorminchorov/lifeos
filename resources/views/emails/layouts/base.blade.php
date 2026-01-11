<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subject ?? 'LifeOS Notification' }}</title>

    <!-- Instrument Sans Font from Bunny Fonts (privacy-friendly) -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet">

    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #1B1B18;
            background-color: #F8F7F4;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        /* Container styles */
        .email-wrapper {
            width: 100%;
            padding: 24px 16px;
            background-color: #F8F7F4;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #FDFDFC;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(27, 27, 24, 0.1), 0 2px 4px -1px rgba(27, 27, 24, 0.06);
            overflow: hidden;
        }

        /* Header styles */
        .email-header {
            background: linear-gradient(135deg, #FDFDFC 0%, #F8F7F4 100%);
            padding: 40px 24px 32px;
            text-align: center;
            border-bottom: 2px solid #E3E3E0;
        }

        .logo {
            font-size: 28px;
            font-weight: 600;
            color: #F53003;
            text-decoration: none;
            margin-bottom: 8px;
            display: inline-block;
            letter-spacing: -0.5px;
        }

        .tagline {
            font-size: 14px;
            color: #706F6C;
            font-weight: 400;
            letter-spacing: 0.1px;
        }

        /* Content styles */
        .email-content {
            padding: 32px 24px;
        }

        .greeting {
            font-size: 18px;
            font-weight: 500;
            color: #1B1B18;
            margin-bottom: 16px;
        }

        .content-text {
            font-size: 16px;
            color: #1B1B18;
            margin-bottom: 16px;
            line-height: 1.7;
            letter-spacing: 0.1px;
        }

        .content-text:last-child {
            margin-bottom: 0;
        }

        .content-text strong {
            font-weight: 600;
            color: #1B1B18;
        }

        .highlight {
            background-color: #FFF2F2;
            padding: 16px;
            border-radius: 8px;
            border-left: 4px solid #F53003;
            margin: 24px 0;
            font-size: 16px;
            line-height: 1.5;
        }

        .highlight-warning {
            background-color: #FFFBEB;
            border-left-color: #F59E0B;
        }

        .highlight-success {
            background-color: #F0FDF4;
            border-left-color: #22C55E;
        }

        .highlight-info {
            background-color: #EFF6FF;
            border-left-color: #3B82F6;
        }

        .details-list {
            background-color: #F8F7F4;
            padding: 24px;
            border-radius: 8px;
            margin: 24px 0;
            border: 1px solid #E3E3E0;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #E3E3E0;
            font-size: 15px;
        }

        .detail-item:first-child {
            padding-top: 0;
        }

        .detail-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .detail-label {
            font-weight: 500;
            color: #706F6C;
            letter-spacing: 0.1px;
        }

        .detail-value {
            font-weight: 600;
            color: #1B1B18;
            text-align: right;
            letter-spacing: 0.1px;
        }

        /* Button styles */
        .button-container {
            text-align: center;
            margin: 32px 0;
        }

        .button {
            display: inline-block;
            background: linear-gradient(135deg, #F53003 0%, #E02B02 100%);
            color: white !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 16px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px -1px rgba(245, 48, 3, 0.3);
            letter-spacing: 0.1px;
        }

        .button:hover {
            background: linear-gradient(135deg, #E02B02 0%, #CC2602 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px -1px rgba(245, 48, 3, 0.4);
        }

        .button-secondary {
            background: #FDFDFC;
            color: #F53003 !important;
            border: 2px solid #F53003;
            box-shadow: 0 2px 4px -1px rgba(27, 27, 24, 0.1);
        }

        .button-secondary:hover {
            background-color: #FFF2F2;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px -1px rgba(27, 27, 24, 0.15);
        }

        /* Footer styles */
        .email-footer {
            background-color: #F8F7F4;
            padding: 32px 24px;
            text-align: center;
            border-top: 2px solid #E3E3E0;
            font-size: 14px;
            color: #706F6C;
            line-height: 1.6;
        }

        .footer-links {
            margin: 20px 0;
            padding: 0;
        }

        .footer-link {
            color: #706F6C;
            text-decoration: none;
            margin: 0 12px;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .footer-link:hover {
            color: #F53003;
        }

        .footer-text {
            margin-bottom: 12px;
            font-size: 14px;
            line-height: 1.6;
        }

        .footer-copyright {
            margin-top: 20px;
            font-size: 13px;
            color: #A1A09A;
        }

        /* Responsive styles */
        @media only screen and (max-width: 600px) {
            .email-wrapper {
                padding: 16px 8px;
            }

            .email-header {
                padding: 24px 16px 20px;
            }

            .email-content {
                padding: 24px 16px;
            }

            .email-footer {
                padding: 20px 16px;
            }

            .button {
                padding: 14px 28px;
                font-size: 15px;
            }

            .detail-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #161615 !important;
                color: #EDEDEC !important;
            }

            .email-wrapper {
                background-color: #161615 !important;
            }

            .email-container {
                background-color: #0A0A0A !important;
            }

            .email-header {
                background: linear-gradient(135deg, #0A0A0A 0%, #161615 100%) !important;
                border-bottom-color: #3E3E3A !important;
            }

            .email-footer {
                background-color: #161615 !important;
                border-top-color: #3E3E3A !important;
                color: #A1A09A !important;
            }

            .footer-text {
                color: #A1A09A !important;
            }

            .footer-copyright {
                color: #62605B !important;
            }

            .details-list {
                background-color: #161615 !important;
                border-color: #3E3E3A !important;
            }

            .detail-item {
                border-bottom-color: #3E3E3A !important;
            }

            .detail-label {
                color: #A1A09A !important;
            }

            .detail-value {
                color: #EDEDEC !important;
            }

            .greeting,
            .content-text {
                color: #EDEDEC !important;
            }

            .tagline {
                color: #A1A09A !important;
            }

            .footer-link {
                color: #A1A09A !important;
            }

            .footer-link:hover {
                color: #F53003 !important;
            }

            .highlight-warning {
                background-color: rgba(251, 191, 36, 0.1) !important;
            }

            .highlight-success {
                background-color: rgba(34, 197, 94, 0.1) !important;
            }

            .highlight-info {
                background-color: rgba(59, 130, 246, 0.1) !important;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="email-header">
                <div class="logo">LifeOS</div>
                <div class="tagline">Your Personal Life Management System</div>
            </div>

            <!-- Content -->
            <div class="email-content">
                @yield('content')
            </div>

            <!-- Footer -->
            <div class="email-footer">
                <div class="footer-text">
                    This email was sent to you because you have an active LifeOS account.<br>
                    If you no longer wish to receive these notifications, you can update your preferences.
                </div>

                <div class="footer-links">
                    <a href="{{ url('/settings/notifications') }}" class="footer-link">Notification Settings</a>
                    <a href="{{ url('/dashboard') }}" class="footer-link">Dashboard</a>
                    <a href="{{ url('/') }}" class="footer-link">LifeOS</a>
                </div>

                <div class="footer-copyright">
                    © {{ date('Y') }} LifeOS. All rights reserved.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
