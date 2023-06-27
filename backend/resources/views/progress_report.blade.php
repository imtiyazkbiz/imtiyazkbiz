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
    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0"
        style="font-family: Helvetica, sans-serif; box-sizing: border-box; background-color: transparent; margin: 0 auto; padding: 0; width: 800px; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
        <tr>
            <td align="center" style="font-family: Helvetica, sans-serif; box-sizing: border-box;">
                <table class="content" width="100%" cellpadding="0" cellspacing="0"
                    style="font-family: Helvetica, sans-serif; box-sizing: border-box; margin: 0; padding: 0; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
                    <tr>
                        <td width="100%" cellpadding="0" cellspacing="0"
                            style="font-family: Helvetica, sans-serif; background:white; box-sizing: border-box; padding: 20px; ">

                            <img src="https://api.homeoftraining.com/images/homeoftraininglogo.png"
                                style="text-align: left;width: 20%;">



                        </td>

                    </tr>

                    <!-- Email Body -->
                    <tr>


                        <td class="body" width="100%" cellpadding="0" cellspacing="0"
                            style="font-family: Helvetica, sans-serif; box-sizing: border-box; background-color: #ffffff; border-bottom: 1px solid #edeff2; border-top: 3px solid #edeff2; margin: 0; padding: 0; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
                            <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0"
                                style="font-family: Helvetica, sans-serif; box-sizing: border-box; background-color: #ffffff; margin: 0 auto; padding: 0; width: 800px; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 800px;">
                                <!-- Body content -->
                                <tr>
                                    <td class="content-cell"
                                        style="font-family: Helvetica, sans-serif; box-sizing: border-box;">

                                        <img src="https://api.homeoftraining.com/images/weeklyreport.png"
                                            style="width: 100%; height:50%">
                                    </td>
                                </tr>
                                <tr>

                                    <td class="content-cell"
                                        style="font-family: Helvetica, sans-serif; box-sizing: border-box; padding: 0 20px;">

                                        <table
                                            style="border-spacing:0px;font-family: Helvetica, sans-serif; box-sizing: border-box; margin: 10px auto;  width: 100%;  -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
                                            <tbody style="font-family: Helvetica, sans-serif; box-sizing: border-box;">
                                                <tr>
                                                    <td
                                                        style="padding: 8px 10px;font-family: Helvetica, sans-serif; box-sizing: border-box; color:#AAD376;  font-size: 20px; font-weight: 400; margin: 0; text-align: left;    line-height: 15px; border-bottom:2px solid #AAD376;">
                                                        Recent Completions
                                                    </td>
                                                </tr>

                                            </tbody>
                                        </table>



                                        <table
                                            style="border-spacing:0px;font-family: Helvetica, sans-serif; box-sizing: border-box; margin: 10px auto; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
                                            <tbody style="font-family: Helvetica, sans-serif; box-sizing: border-box;">
                                                <tr style="background-color: #444c57; ">
                                                    <td
                                                        style="border-spacing:2px;border-collapse: collapse;white-space: nowrap; width: 25%;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #ffffff; font-size: 15px;margin: 0; line-height: 15px; padding: 10px 10px;text-align: left; font-weight: 400;">
                                                        Employee
                                                    </td>
                                                    <td
                                                        style="border-collapse: collapse;white-space: nowrap; width: 25%;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #ffffff; font-size: 15px;margin: 0; line-height: 15px; padding: 8px 10px;text-align: left; font-weight: 400;">
                                                        Location
                                                    </td>
                                                    <td
                                                        style="border-collapse: collapse;white-space: nowrap; width: 25%;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #ffffff; font-size: 15px;margin: 0; line-height: 15px; padding: 8px 10px;text-align: left; font-weight: 400;">
                                                        Course
                                                    </td>
                                                    <td
                                                        style="border-collapse: collapse;white-space: nowrap; width: 25%;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #ffffff; font-size: 15px;margin: 0; line-height: 15px; padding: 8px 10px;text-align: left; font-weight: 400;">
                                                        Completion Date
                                                    </td>
                                                </tr>

                                                <?php if (count($recent_completions) > 0) { ?>
                                                @foreach ($recent_completions as $completions)
                                                    <tr class="courses-row">
                                                        <td
                                                            style="border-collapse: collapse;white-space: nowrap;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 15px; padding: 12px 5px; border-bottom: 1px dashed #b2b2b2;">
                                                            {{ $completions['full_name'] }}
                                                        </td>
                                                        <td
                                                            style="border-collapse: collapse;white-space: nowrap;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 15px; padding: 12px 5px; border-bottom: 1px dashed #b2b2b2;">
                                                            {{ $completions['company_name'] }}
                                                        </td>
                                                        <td
                                                            style="border-collapse: collapse;white-space: nowrap;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 15px; padding: 12px 5px; border-bottom: 1px dashed #b2b2b2;">
                                                            {{ $completions['name'] }}
                                                        </td>
                                                        <td
                                                            style="border-collapse: collapse;white-space: nowrap;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 15px; padding: 12px 5px; border-bottom: 1px dashed #b2b2b2;">
                                                            {{ date('m/d/Y', strtotime($completions['employee_course_date_completed'])) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                <?php } else { ?>
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td style="color: #868686;">None</td>
                                                    <td></td>
                                                </tr>

                                                <?php } ?>



                                            </tbody>
                                        </table>



                                        <table
                                            style="border-spacing:0px;font-family: Helvetica, sans-serif; box-sizing: border-box; margin: 10px auto;  width: 100%;  -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
                                            <tbody style="font-family: Helvetica, sans-serif; box-sizing: border-box;">
                                                <tr>
                                                    <td
                                                        style="padding: 8px 10px;font-family: Helvetica, sans-serif; box-sizing: border-box; color:#AAD376;  font-size: 20px; font-weight: 400; margin: 0; text-align: left;    line-height: 15px; border-bottom:2px solid #AAD376;">
                                                        Courses due within 7 days
                                                    </td>
                                                </tr>

                                            </tbody>
                                        </table>

                                        <table
                                            style="border-spacing:0px;font-family: Helvetica, sans-serif; box-sizing: border-box; margin: 10px auto; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
                                            <tbody style="font-family: Helvetica, sans-serif; box-sizing: border-box;">
                                                <tr style="background-color: #444c57;">
                                                    <td
                                                        style="border-spacing:2px;border-collapse: collapse;white-space: nowrap; width: 25%;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #ffffff; font-size: 15px;margin: 0; line-height: 15px; padding: 10px 10px;text-align: left; font-weight: 400;">
                                                        Employee
                                                    </td>
                                                    <td
                                                        style="border-collapse: collapse;white-space: nowrap; width: 25%;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #ffffff; font-size: 15px;margin: 0; line-height: 15px; padding: 8px 10px;text-align: left; font-weight: 400;">
                                                        Location
                                                    </td>
                                                    <td
                                                        style="border-collapse: collapse;white-space: nowrap; width: 25%;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #ffffff; font-size: 15px;margin: 0; line-height: 15px; padding: 8px 10px;text-align: left; font-weight: 400;">
                                                        Course
                                                    </td>
                                                    <td
                                                        style="border-collapse: collapse;white-space: nowrap; width: 25%;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #ffffff; font-size: 15px;margin: 0; line-height: 15px; padding: 8px 10px;text-align: left; font-weight: 400;">
                                                        Exp Date
                                                    </td>
                                                </tr>

                                                <?php if (count($employee_course_due) > 0) { ?>
                                                @foreach ($employee_course_due as $course_due)
                                                    <tr class="courses-row">
                                                        <td
                                                            style="border-collapse: collapse;white-space: nowrap;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 15px; padding: 12px 5px; border-bottom: 1px dashed #b2b2b2;">
                                                            {{ $course_due['full_name'] }}
                                                        </td>
                                                        <td
                                                            style="border-collapse: collapse;white-space: nowrap;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 15px; padding: 12px 5px; border-bottom: 1px dashed #b2b2b2;">
                                                            {{ $course_due['company_name'] }}
                                                        </td>
                                                        <td
                                                            style="border-collapse: collapse;white-space: nowrap;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 15px; padding: 12px 5px; border-bottom: 1px dashed #b2b2b2;">
                                                            {{ $course_due['name'] }}
                                                        </td>
                                                        <td
                                                            style="border-collapse: collapse;white-space: nowrap;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 15px; padding: 12px 5px; border-bottom: 1px dashed #b2b2b2;">
                                                            {{ date('m/d/Y', strtotime($course_due['employee_course_due_date'])) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                <?php } else { ?>
                                                <tr>
                                                    <td></td>
                                                    <td style="color: #868686; text-align:right;">None</td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>

                                                <?php } ?>



                                            </tbody>
                                        </table>


                                        <div class="table"
                                            style="font-family: Helvetica, sans-serif; box-sizing: border-box; margin-top: 20px;">
                                            <table
                                                style="font-family: Helvetica, sans-serif; box-sizing: border-box; margin: 0px auto; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
                                                <tbody
                                                    style="font-family: Helvetica, sans-serif; box-sizing: border-box;">
                                                    <tr>
                                                        <td
                                                            style="font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 23px;">
                                                            <a href="{{ env('LMS_URL') . '/#/login' }}"
                                                                style="font-family: Helvetica, sans-serif; box-sizing:border-box; color: #3869d4;">
                                                                Click here</a> for full report including non-compliant
                                                            users.

                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

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
                                <tr style="background-color: #AAD376;">
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
