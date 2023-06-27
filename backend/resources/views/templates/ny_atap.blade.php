<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700,900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700&display=swap"
        rel="stylesheet">
    <title>{{ env('SITE_NAME') }}</title>
    <style>
        @page {
            margin: 0px;
        }

        body {
            margin: 0px;
        }

    </style>
</head>

<body style="margin: 0;">
    <table cellpadding="0" cellspacing="0" style="width:100%">
        <tr>
            <td align="center">
                <table cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td style="background-size:100% 100%;background: url(images/certificate-bg.jpg) no-repeat top; padding: 20px;"
                            align="center">
                            <table cellpadding="0" cellspacing="0" style="width:100%">
                                <tr>
                                    <td style="width: 30%;"></td>

                                    <td style="text-align:center; width: 40%;">
                                        <img src="images/logo.jpg" alt="" width="40%" />
                                    </td>

                                    <td style="width: 30%;">
                                        <table style="width: 83%;" cellpadding="0" cellspacing="0">
                                            <tr
                                                style="color: #01154F;margin: 0;font-size: 13px;font-family: 'Montserrat', sans-serif;font-weight: 600;letter-spacing: 0.5px; line-height: 1;">
                                                <td style="text-align: left;">Expiration Date:</td>
                                                <td style="text-align: right;">{{ $expiration_date }}</td>
                                            </tr>
                                            <tr
                                                style="color: #01154F;margin: 0;font-size: 13px;font-family: 'Montserrat', sans-serif;letter-spacing: 0.5px; line-height: 1;">
                                                <td style="text-align: left;">Date Completed:</td>
                                                <td style="text-align: right;">{{ $completion_date }}</td>
                                            </tr>
                                            <tr
                                                style="color: #01154F;margin: 0;font-size: 13px;font-family: 'Montserrat', sans-serif;letter-spacing: 0.5px; line-height: 1;">
                                                <td style="text-align: left;">Certificate #:</td>
                                                <td style="text-align: right;">{{ $certificate_no }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr style="text-align: center;">
                                    <td colspan="3" style="position: relative; padding-top:40px;">
                                        <p
                                            style="color: #01154F; margin: auto; font-size: 20px; font-family: 'Montserrat', sans-serif; font-weight: bold; letter-spacing: 1px;">
                                            CERTIFICATE</p>
                                        <hr
                                            style="position: absolute; width: 50px; top: 65px; left: 315px; background: #000;">
                                        <hr
                                            style="position: absolute; width: 50px; top: 57px; right: 323px; background: #000;">
                                    </td>
                                </tr>
                                <tr style="text-align: center;">
                                    <td colspan="3">
                                        <p
                                            style="color: #01154F; margin: auto; font-size: 15px; font-family: 'Montserrat', sans-serif; letter-spacing: 3px; line-height: 2.7;">
                                            OF COMPLETION</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        <table style="text-align: center; width: 100%;" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding: 25px 0 10px 0;">
                                                    <p
                                                        style="color: #01154F;margin: 40px 0 0 0;font-size: 16px;font-family: 'Montserrat', sans-serif;font-weight: 500;letter-spacing: 2px;">
                                                        THIS ACKNOWLEDGES THAT:
                                                    </p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 10px 0 10px 0;">
                                                    <p
                                                        style="color: #01154F; margin: 0; font-size: 40px; font-family: 'Montserrat', sans-serif;font-weight: 500;">
                                                        {{ ucfirst($employee_first_name) }}
                                                        {{ ucfirst($employee_last_name) }}
                                                    </p>
                                                    <hr
                                                        style="width: 60%;bottom: 0;background: #000;height: 0.5px;margin: 0 auto;">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 10px 0 5px 0;">
                                                    <p
                                                        style="color: #01154F;margin: 0;font-size: 16px;font-family: 'Montserrat', sans-serif;font-weight: 600;letter-spacing: 2px;">
                                                        HAS SUCCESSFULLY COMPLETED THE
                                                    </p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 10px 0 10px 0;">
                                                    <p
                                                        style="color: #01154F;margin: 0;font-size: 30px;font-family: 'Montserrat', sans-serif;font-weight: bold;letter-spacing: 1px;">
                                                        {{ ucfirst($name_of_course) }}
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        <table
                                            style="text-align: center; width: 90%; margin: auto; padding: 52px 0 5px 0;"
                                            cellpadding="0" cellspacing="0">
                                            <tr valign="top">
                                                <td style="width: 30%;">
                                                    <table style="width: 100%;" cellpadding="0" cellspacing="0">
                                                        <tr
                                                            style="color: #01154F;margin: 0;font-size: 13px;font-family: 'Montserrat', sans-serif;letter-spacing: 0.5px; line-height: 1.8;">
                                                            <td style="text-align: left;">Date Of Birth:</td>
                                                            <td style="text-align: right;">{{ $date_of_birth }}</td>
                                                        </tr>
                                                        <tr
                                                            style="color: #01154F;margin: 0;font-size: 13px;font-family: 'Montserrat', sans-serif;letter-spacing: 0.5px; line-height: 1.8;">
                                                            <td style="text-align: left;">Home Address:</td>
                                                            <td style="text-align: right;">{{ $student_home_address }}
                                                            </td>
                                                        </tr>
                                                        <tr
                                                            style="color: #01154F;margin: 0;font-size: 13px;font-family: 'Montserrat', sans-serif;letter-spacing: 0.5px; line-height: 1.8;">
                                                            <td style="text-align: left;">City/State/Zip:</td>
                                                            <td style="text-align: right;">{{ $city_state_zip }}</td>
                                                        </tr>
                                                        <tr
                                                            style="color: #01154F;margin: 0;font-size: 13px;font-family: 'Montserrat', sans-serif;letter-spacing: 0.5px; line-height: 1.8;">
                                                            <td style="text-align: left;">Current Employer:</td>
                                                            <td style="text-align: right;">{{ $current_employer }}
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <hr style="background: #000;height: 0.5px;margin: 0 auto;">
                                                </td>
                                                <td style="width: 40%;">
                                                    <img style="width: 115px;" src="images/stamp.png">
                                                </td>
                                                <td style="width: 30%; text-align: left;">
                                                    <p
                                                        style="padding: 0; margin: 0; font-family:segoesc;width:250px; font-size: 22px; line-height: 0.5;">
                                                        {{ ucfirst($signature_title_1) }}</p>
                                                    <hr style="background: #000;height: 0.5px;margin: 0 0 0 auto;">
                                                    <p
                                                        style="color: #01154F;margin: 0;font-size: 11px;font-family: 'Montserrat', sans-serif;width:230px;font-weight: 600;letter-spacing: 1px; padding-top: 7px;">
                                                        {{ ucfirst($signature_title_2) }}
                                                    </p>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="3">
                                                    <p
                                                        style="color: #000000;margin: 20px 0 0 0;font-size: 9px;font-family: 'Montserrat', sans-serif; line-height: 1.6;text-align: justify;font-weight: 500;">
                                                        {{ $text }}</p>
                                                </td>
                                            </tr>
                                        </table>
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
