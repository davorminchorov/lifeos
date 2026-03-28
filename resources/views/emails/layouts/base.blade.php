<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="format-detection" content="telephone=no,address=no,email=no,date=no,url=no">
    <title>{{ $subject ?? 'LifeOS' }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        /* Reset */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        table { border-collapse: collapse !important; }
        body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; }

        /* Preheader */
        .preheader { display: none !important; visibility: hidden; mso-hide: all; font-size: 1px; line-height: 1px; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; }

        /* Typography */
        .body-text { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 26px; color: #1B1B18; }
        .heading { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-weight: 600; color: #1B1B18; }
        .subtext { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; line-height: 22px; color: #706F6C; }

        /* Responsive */
        @media only screen and (max-width: 620px) {
            .email-container { width: 100% !important; max-width: 100% !important; }
            .fluid { width: 100% !important; max-width: 100% !important; height: auto !important; }
            .stack-column { display: block !important; width: 100% !important; max-width: 100% !important; }
            .center-on-narrow { text-align: center !important; display: block !important; margin-left: auto !important; margin-right: auto !important; float: none !important; }
            .padding-mobile { padding-left: 20px !important; padding-right: 20px !important; }
        }

        /* Dark mode */
        @media (prefers-color-scheme: dark) {
            body, .email-bg { background-color: #161615 !important; }
            .email-body { background-color: #1B1B18 !important; }
            .body-text, .heading { color: #EDEDEC !important; }
            .subtext { color: #A1A09A !important; }
            .card-bg { background-color: #252521 !important; }
            .border-color { border-color: #3E3E3A !important; }
            .detail-label-dark { color: #A1A09A !important; }
            .detail-value-dark { color: #EDEDEC !important; }
            .footer-bg { background-color: #161615 !important; }
            .footer-text-dark { color: #706F6C !important; }
            .footer-muted-dark { color: #62605B !important; }
            .highlight-bg { background-color: #252521 !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #F5F4F0;">
    <!-- Preheader text -->
    <div class="preheader" style="display: none; font-size: 1px; line-height: 1px; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all;">
        @yield('preheader', 'LifeOS Notification')
        &#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;&#847;&zwnj;&nbsp;
    </div>

    <!-- Email wrapper -->
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #F5F4F0;" class="email-bg">
        <tr>
            <td style="padding: 32px 16px;" class="padding-mobile">
                <div style="max-width: 560px; margin: 0 auto;">

                    <!--[if mso]>
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="560" align="center">
                    <tr><td>
                    <![endif]-->

                    <!-- Header -->
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" class="email-container" style="max-width: 560px; margin: 0 auto;">
                        <tr>
                            <td style="padding: 28px 0; text-align: center;">
                                <span style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 22px; font-weight: 700; color: #1B1B18; letter-spacing: -0.5px;" class="heading">Life<span style="color: #F53003;">OS</span></span>
                            </td>
                        </tr>
                    </table>

                    <!-- Body card -->
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" class="email-container email-body" style="max-width: 560px; margin: 0 auto; background-color: #FDFDFC; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(27,27,24,0.08);">
                        <!-- Content -->
                        <tr>
                            <td style="padding: 40px 36px;" class="padding-mobile body-text">
                                @yield('content')
                            </td>
                        </tr>
                    </table>

                    <!-- Footer -->
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" class="email-container" style="max-width: 560px; margin: 0 auto;">
                        <tr>
                            <td style="padding: 28px 36px 8px;" class="padding-mobile">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                    <tr>
                                        <td style="text-align: center; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; line-height: 20px; color: #A1A09A;" class="footer-text-dark">
                                            <p style="margin: 0 0 12px;">
                                                You received this because you have an active LifeOS account.
                                            </p>
                                            <p style="margin: 0 0 12px;">
                                                <a href="{{ url('/settings/notifications') }}" style="color: #706F6C; text-decoration: underline;">Notification Settings</a>
                                                &nbsp;&middot;&nbsp;
                                                <a href="{{ url('/dashboard') }}" style="color: #706F6C; text-decoration: underline;">Dashboard</a>
                                                &nbsp;&middot;&nbsp;
                                                <a href="{{ url('/') }}" style="color: #706F6C; text-decoration: underline;">LifeOS</a>
                                            </p>
                                            <p style="margin: 0; color: #C4C4BE;" class="footer-muted-dark">
                                                &copy; {{ date('Y') }} LifeOS
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    <!--[if mso]>
                    </td></tr>
                    </table>
                    <![endif]-->

                </div>
            </td>
        </tr>
    </table>
</body>
</html>
