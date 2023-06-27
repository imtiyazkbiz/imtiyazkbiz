
<!DOCTYPE html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>

<body
    style="font-family: Helvetica, sans-srif; box-sizing: border-box; background-color: #f2f2f2; color: #74787e; height: 100%; hyphens: auto; line-height: 1.4; margin: 0; -moz-hyphens: auto; -ms-word-break: break-all; width: 100% !important; -webkit-hyphens: auto; -webkit-text-size-adjust: none; word-break: break-word; margin-bottom: 20px;">
    <style>
        @media only screen and (max-width: 600px) {
            .inner-body {
                width: 100% !important;
            }

            .footer {
                width: 100% !important;
            }

            .wrapper {
                width: 100% !important;
            }
        }

        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }

            .wrapper {
                width: 100% !important;
            }
        }

    </style>
    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" style="font-family: Helvetica, sans-serif; box-sizing: border-box; background-color: transparent; margin: 0 auto; padding: 0; width: 800px; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
        <tr>
            <td align="center" style="font-family: Helvetica, sans-serif; box-sizing: border-box;">
                <table class="content" width="100%" cellpadding="0" cellspacing="0"
                    style="font-family: Helvetica, sans-serif; box-sizing: border-box; margin: 0; padding: 0; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
                    <tr>
                        <td class="header"
                            style="font-family: Helvetica, sans-serif; box-sizing: border-box; padding: 25px 0 0; text-align: center;">
                            <a href="#">
                                <img src="{{ url('images') }}/{{ env('EMAIL_LOGO') }}" alt="" style="width: 100%;">
                            </a>
                        </td>
                    </tr>
                    <!-- Email Body -->
                    <tr>
                        <td class="body" width="100%" cellpadding="0" cellspacing="0" style="font-family: Helvetica, sans-serif; box-sizing: border-box; background-color: #ffffff; border-bottom: 1px solid #edeff2; border-top: 1px solid #edeff2; margin: 0; padding: 0; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
                            <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0"
                                style="font-family: Helvetica, sans-serif; box-sizing: border-box; background-color: #ffffff; margin: 0 auto; padding: 0; width: 800px; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 800px;">
                                <!-- Body content -->
                                <tr>
                                    <td class="content-cell" style="font-family: Helvetica, sans-serif; box-sizing: border-box; padding: 35px;">
                                        <div class="table" style="font-family: Helvetica, sans-serif; box-sizing: border-box;">
											<img src="{{ url('images') }}/thumb-up.svg" style="width: 17%; margin: 0 auto; display: block;" />
                                            <h2 style="text-align: center; font-size: 35px; margin: 0; padding: 18px 0 0; font-style: italic; color: #474747; text-shadow: 0px 5px 6px #00000042;">{{$messagedata}}</h2>                                                
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                       
                            <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0"
                                style="font-family: Helvetica, sans-serif; box-sizing: border-box; margin: 0 auto; padding: 0; text-align: center; width: 800px; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 800px;">
                                <tr style="background-color: #9acc59;">
                                    <td class="content-cell" align="center"
                                        style="font-family: Helvetica, sans-serif; box-sizing: border-box; padding: 10px;">

                                        <p
                                        style="font-family: Helvetica, sans-serif; box-sizing: border-box; color: #ffffff; font-size: 13px; line-height: 1.5em; margin-top: 0; text-align: center; margin-bottom: 2px;">
                                        <strong>Note:</strong> Please do not reply to this email. Please send all
                                        responses to <a target="_blank"
                                            href="mailto: {{ env('MAIL_SUPPORT') }}" style="color: #fff;">
                                            {{ env('MAIL_SUPPORT') }}
                                        </a>
                                    </p>
                                    <p
                                        style="font-family: Helvetica, sans-serif; box-sizing: border-box; color: #ffffff; font-size: 13px; line-height: 1.5em; margin-top: 0; margin-bottom: 0; text-align: center;">
                                        <strong>E-mail sent on:</strong> 
                                        {{ date('m-d-Y') }}
                                    </p>

                                        <p
                                            style="margin-bottom: 0; font-family: Helvetica, sans-serif; box-sizing: border-box; line-height: 1.5em; margin-top: 5px; color: #fff; font-size: 12px; text-align: center;">
                                            © {{ date('Y') }} {{ env('SITE_NAME') }}. All rights reserved
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
