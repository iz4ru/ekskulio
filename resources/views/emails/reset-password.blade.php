<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disablemessage-reformatting">
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
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap');

        @media only screen and (max-width: 600px) {
            table[class="email-container"] {
                width: 100% !important;
            }

            td[class="content-padding"] {
                padding: 30px 20px !important;
            }

            a[class="cta-button"] {
                display: block !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }
        }
    </style>
    <title>Reset Password | Ekskulio</title>
</head>

<body style="margin: 0; padding: 0; font-family: 'DM Sans', Arial, sans-serif; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 40px 0;">
        <tr>
            <td align="center">
                <table class="email-container" width="600" cellpadding="0" cellspacing="0"
                    style="background-color: #FAFAFA; border-radius: 24px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 40px 30px; background: #FAFAFA;">
                            @if (file_exists($logoPath))
                                <img src="{{ $message->embed($logoPath) }}" alt="Ekskulio"
                                    style="width: 150px; height: auto; display: block; border: 0;">
                            @else
                                <img src="{{ asset('images/ekskulio.png') }}" alt="Ekskulio"
                                    style="width: 150px; height: auto; display: block; border: 0;">
                            @endif
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td class="content-padding" style="padding: 50px 40px;">
                            <h1
                                style="margin: 0 0 20px 0; font-size: 28px; font-weight: 700; color: #1a1a1a; text-align: center;">
                                Reset Password
                            </h1>

                            <p
                                style="margin: 0 0 30px 0; font-size: 16px; line-height: 1.6; color: #666666; text-align: center;">
                                Kamu menerima email ini karena ada permintaan reset password untuk akun Ekskulio kamu.
                            </p>

                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 40px 0;">
                                <tr>
                                    <td align="center" style="padding: 0;">
                                        <a href="{{ route('password.reset', ['token' => $token, 'email' => $email]) }}"
                                            class="cta-button"
                                            style="display: inline-block; padding: 16px 40px; background-color: #0083E9; color: #fafafa; text-decoration: none; border-radius: 16px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 6px rgba(0, 131, 233, 0.3); text-align: center; min-width: 200px;">
                                            Reset Password
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Info Box -->
                            <table width="100%" cellpadding="0" cellspacing="0"
                                style="background-color: #E6F4FD; border-radius: 16px; padding: 20px; margin: 30px 0;">
                                <tr>
                                    <td style="font-size: 14px; color: #005A8C; text-align: center;">
                                        <strong>Link ini akan kadaluarsa dalam {{ $expireMinutes }} menit</strong>
                                    </td>
                                </tr>
                            </table>

                            <p
                                style="margin: 30px 0 0 0; font-size: 14px; line-height: 1.6; color: #999999; text-align: center;">
                                Jika kamu tidak meminta reset password, abaikan email ini atau hubungi tim support kami.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td align="center"
                            style="padding: 30px; background-color: #f9f9f9; border-top: 1px solid #eeeeee;">
                            <p style="margin: 0; font-size: 13px; color: #999999;">
                                © {{ date('Y') }} Ekskulio. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
