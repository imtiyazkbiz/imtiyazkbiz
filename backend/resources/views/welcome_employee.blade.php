<!DOCTYPE html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>

<body
        style="font-family: Avenir, Helvetica, sans-srif; box-sizing: border-box; background-color: #f5f8fa; color: #74787e; height: 100%; hyphens: auto; line-height: 1.4; margin: 0; -moz-hyphens: auto; -ms-word-break: break-all; width: 100% !important; -webkit-hyphens: auto; -webkit-text-size-adjust: none; word-break: break-word;">
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
<table class="wrapper" width="100%" cellpadding="0" cellspacing="0"
       style="font-family: Helvetica, sans-serif; box-sizing: border-box; background-color: #f9f8fd; margin: 0 auto; padding: 0; width: 800px; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
    <tr>
        <td align="center" style="font-family: Helvetica, sans-serif; box-sizing: border-box;">
            <table class="content" width="100%" cellpadding="0" cellspacing="0"
                   style="font-family: Helvetica, sans-serif; box-sizing: border-box; margin: 0; padding: 0; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
                <tr>
                    <td class="header"
                        style="font-family: Helvetica, sans-serif; box-sizing: border-box; padding: 25px 0 0; text-align: center;">
                        <a href="#"
                           style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #000; font-size: 19px; font-weight: bold; text-decoration: none; text-shadow: 0 1px 0 #ffffff;">
                            <img src="{{ url('images') }}/{{ env('EMAIL_LOGO') }}" style="width: 100%;">
                        </a>
                    </td>
                </tr>
                <!-- Email Body -->
                <tr>
                    <td class="body" width="100%" cellpadding="0" cellspacing="0"
                        style="font-family: Helvetica, sans-serif; box-sizing: border-box; background-color: #ffffff; border-bottom: 1px solid #edeff2; border-top: 1px solid #edeff2; margin: 0; padding: 0; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
                        <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0"
                               style="font-family: Helvetica, sans-serif; box-sizing: border-box; background-color: #ffffff; margin: 0 auto; padding: 0; width: 800px; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 800px;">
                            <!-- Body content -->
                            <tr>
                                <td class="content-cell"
                                    style="font-family: Helvetica, sans-serif; box-sizing: border-box; padding: 35px;">
                                    <h1
                                            style="font-family: Helvetica, sans-serif; box-sizing: border-box; color: #2F3133; font-size: 18px; font-weight: 500; margin-top: 0; text-align: left;">
                                        Hi
                                        {{ ucfirst($first_name) }} {{ ucfirst($last_name) }},
                                    </h1>
                                    <p
                                            style="font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 1.5em; margin-top: 0; text-align: left;">
                                        Welcome to {{ env('SITE_NAME') }}! Your employer,
                                        {{ ucwords($company_name) }}, has
                                        created an online training account for you that can be accessed
                                        from any device connected to the internet (including smartphones
                                        and tablets).
                                    </p>
                                    <h3
                                            style="margin-bottom: 5px; font-family: Helvetica, sans-serif; box-sizing: border-box; color: #000; margin-top: 5px; text-align: left;">
                                        Your Login Information:
                                    </h3>

                                    <div class="table"
                                         style="font-family: Helvetica, sans-serif; box-sizing: border-box; margin-top: 0px;">
                                        <table
                                                style="font-family: Helvetica, sans-serif; box-sizing: border-box; margin: 0px auto; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
                                            <tbody
                                                    style="font-family: Helvetica, sans-serif; box-sizing: border-box;">

                                            <tr style="text-align: center; background: #f7ffee;">
                                                <td
                                                        style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; font-weight: 600; text-align: left; width: 20%;">
                                                    Site for training
                                                </td>
                                                <td
                                                        style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: left;">
                                                    <a href="https://www.homeoftraining.com"
                                                       style="font-family: Helvetica, sans-serif; box-sizing:border-box; color: #3869d4;">
                                                        {{ env('SITE_NAME') }}
                                                    </a>
                                                </td>
                                            </tr>

                                            <tr style="text-align: center;">
                                                <td
                                                        style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; font-weight: 600; text-align: left;">
                                                    Username
                                                </td>
                                                <td
                                                        style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: left;">
                                                    {{ $user_name }}
                                                </td>
                                            </tr>

                                            <tr style="text-align: center; background: #f7ffee;">
                                                <td
                                                        style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; font-weight: 600; text-align: left;">
                                                    Password
                                                </td>
                                                <td
                                                        style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: left;">
                                                    {{ $access_code }}
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <h3
                                            style="margin-bottom: 5px; font-family: Helvetica, sans-serif; box-sizing: border-box; color: #2F3133; font-size: 16px; font-weight: bold; line-height: 23px;">
                                        Getting Started:
                                    </h3>
                                    <ul
                                            style="font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 1.5em; margin-top: 0; text-align: left; padding-left: 20px;">
                                        <li style="margin-bottom: 10px;">Login with your Username and Password.</li>
                                        <li style="margin-bottom: 10px;">Watch our <span
                                                    style="font-weight: 600;">Tutorial Video</span>
                                            (located in
                                            the left-side menu bar) that gives you step-by-step
                                            instructions on taking your assigned online training
                                            courses, accessing your certificates, and much more.
                                        </li>
                                        <li style="margin-bottom: 10px;">
                                            Click "<span style="font-weight: 600;">Take Course</span>"
                                        </li>
                                    </ul>
                                    <p
                                            style="font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 23px;margin-left: 4px;">
                                        Please contact your employer or email us at <a target="_blank"
                                                                                       style="font-family: Helvetica, sans-serif; box-sizing:border-box; color: #3869d4;"
                                                                                       href="mailto: {{ env('MAIL_SUPPORT') }}">{{ env('MAIL_SUPPORT') }}</a>
                                        if you have any questions or concerns. <span
                                                style="display: block;margin-top: 8px;  color: #2F3133; font-size: 17px; font-weight: 600;">Good
                                                Luck!</span>

                                    </p>
                                    <div style="margin-top: 20px;">
                                        <p
                                                style="font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 23px;margin-left: 4px;">
                                            Train Wise,<br>The {{ env('SITE_NAME') }} Team<br>

                                            <a href="{{ env('FRONT_END_BASE_URL') }}"
                                               style="font-family: Helvetica, sans-serif; box-sizing: border-box; color: #3869d4;">
                                                {{ env('FRONT_END_BASE_URL') }}

                                            </a>
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="font-family: Helvetica, sans-serif; box-sizing: border-box;">
                        <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0"
                               style="font-family: Helvetica, sans-serif; box-sizing: border-box; margin: 0 auto; padding: 0; text-align: center; width: 800px; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 800px;">
                            <tr style="background-color: #9acc59;">
                                <td class="content-cell" align="center"
                                    style="font-family: Helvetica, sans-serif; box-sizing: border-box; padding: 10px;">

                                    <p
                                            style="font-family: Helvetica, sans-serif; box-sizing: border-box; color: #ffffff; font-size: 13px; line-height: 1.2em; margin-top: 0; text-align: center; margin-bottom: 2px;">
                                        <strong>Note:</strong> Please do not reply to this email. Please send all
                                        responses to <a target="_blank" href="mailto: {{ env('MAIL_SUPPORT') }}"
                                                        style="color: #fff;">
                                            {{ env('MAIL_SUPPORT') }}

                                        </a>
                                    </p>
                                    <p
                                            style="font-family: Helvetica, sans-serif; box-sizing: border-box; color: #ffffff; font-size: 13px; line-height: 1.5em; margin-top: 0; margin-bottom: 0; text-align: center;">

                                        Mail sent on:

                                        {{ date('m-d-Y') }}
                                    </p>
                                    <p
                                            style="margin-bottom: 0; font-family: Helvetica, sans-serif; box-sizing: border-box; line-height: 1.5em; margin-top: 2px; color: #fff; font-size: 12px; text-align: center;">

                                        © {{ date('Y') }} {{ env('SITE_NAME') }}. All rights reserved

                                    </p>
                                    @isset($userId)
                                        <p style="margin-bottom: 0; font-family: Helvetica, sans-serif; box-sizing: border-box; line-height: 1.5em; margin-top: 2px; color: #fff; font-size: 12px; text-align: center;">
                                            <a href="{{ url('/unsubscribe?userId=' . $userId) }}">Click here to unsubscribe</a>
                                        </p>
                                    @endisset
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>

</html>
