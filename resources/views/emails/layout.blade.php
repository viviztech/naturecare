<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Nature Care Products')</title>
</head>
<body style="margin:0;padding:0;background-color:#f3fbfc;font-family:Arial,Helvetica,sans-serif;color:#1c4040;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3fbfc;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #dff5f6;">
                    <tr>
                        <td style="background-color:#ffffff;padding:20px 32px;border-bottom:3px solid #f06b4a;">
                            <img src="{{ asset('images/nature-care-logo.jpeg') }}" alt="Nature Care Products" width="187" height="48" style="display:block;height:32px;width:auto;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#f3fbfc;padding:16px 32px;color:#245a5b;font-size:12px;">
                            &copy; {{ date('Y') }} Nature Care Products. Naturally Clean, Naturally Safe.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
