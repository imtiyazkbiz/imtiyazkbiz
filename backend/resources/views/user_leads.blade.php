<!DOCTYPE html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>

<body
    style="font-family: Helvetica, sans-srif; box-sizing: border-box; background-color: #f5f8fa; color: #74787e; height: 100%; hyphens: auto; line-height: 1.4; margin: 0; -moz-hyphens: auto; -ms-word-break: break-all; width: 100% !important; -webkit-hyphens: auto; -webkit-text-size-adjust: none; word-break: break-word;">
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
                    style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; margin: 0; padding: 0; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
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
                                            Hi Team,</h1>
                                        <p
                                            style="font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 1.5em; margin-top: 0; text-align: left;">
                                            {{ ucfirst($first_name) }}
                                            has requested a quote with the following
                                            information:
                                        </p>
                                        <div class="table"
                                            style="font-family: Helvetica, sans-serif; box-sizing: border-box;">
                                            <table
                                                style="font-family: Helvetica, sans-serif; box-sizing: border-box; margin: 0px auto; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">
                                                <tbody
                                                    style="font-family: Helvetica, sans-serif; box-sizing: border-box;">

                                                    <tr style="text-align: center; background: #f7ffee;">
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; font-weight: 600; text-align: left; width: 25%;">
                                                            First Name
                                                        </td>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: left;">
                                                            {{ ucwords($first_name) }}
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; font-weight: 600; text-align: left;">
                                                            Last Name
                                                        </td>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: left;">
                                                            {{ ucwords($last_name) }}
                                                        </td>
                                                    </tr>

                                                    <tr style="text-align: center; background: #f7ffee;">
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; font-weight: 600; text-align: left;">
                                                            Email Address
                                                        </td>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: left;">
                                                            {{ $email }}
                                                        </td>
                                                    </tr>
                                                    <tr >
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; font-weight: 600; text-align: left;">
                                                            Phone Number
                                                        </td>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: left;">
                                                            {{ $phone_num }}
                                                        </td>
                                                    </tr>

                                                    <tr style="text-align: center; background: #f7ffee;">
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; font-weight: 600; text-align: left;">
                                                            Company Name
                                                        </td>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: left;">
                                                            {{ ucwords($company_name) }}
                                                        </td>
                                                    </tr>

                                                    <tr >
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; font-weight: 600; text-align: left;">
                                                            Number of Locations
                                                        </td>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: left;">
                                                            {{ $number_of_locations }}
                                                        </td>
                                                    </tr>

                                                    <tr style="text-align: center; background: #f7ffee;">
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; font-weight: 600; text-align: left;">
                                                            Number of Employees
                                                        </td>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: left;">
                                                            {{ $number_of_employees }}
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>


                                        <div class="table"
                                            style="font-family: Helvetica, sans-serif; box-sizing: border-box; margin-top: 20px;">
                                            <table
                                                style="font-family: Helvetica, sans-serif; box-sizing: border-box; margin: 0px auto; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">

                                                <tbody
                                                    style="font-family: Helvetica, sans-serif; box-sizing: border-box;">
                                                    <tr style="background-color: #9acc59;">
                                                        <th
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #ffffff; font-size: 15px; line-height: 10px; padding: 10px 5px;text-align: left;">
                                                            <strong>Courses</strong>
                                                        </th>
                                                    </tr>
                                                    @foreach ($courses as $value)
                                                        <tr>
                                                            <td
                                                                style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: right; font-weight: 600; text-align: left;">
                                                                {{ ucfirst($value->name) }}
                                                            </td>
                                                        </tr>

                                                    @endforeach
                                                    @if ($special_courses)

                                                        @foreach ($special_courses as $value1)
                                                            <tr>
                                                                <td
                                                                    style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: right; font-weight: 600; text-align: left;">
                                                                    {{ $value1['course_name'] }}
                                                                    ( {{ $value1['users'] }}
                                                                    Users)
                                                                </td>
                                                            </tr>

                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="table"
                                            style="font-family: Helvetica, sans-serif; box-sizing: border-box;">
                                            <table
                                                style="font-family: Helvetica, sans-serif; box-sizing: border-box; margin: 0px auto; width: 100%; -premailer-cellpadding: 0; -premailer-cellspacing: 0; -premailer-width: 100%;">

                                                <tbody
                                                    style="font-family: Helvetica, sans-serif; box-sizing: border-box;">
                                                    <tr style="background-color: #9acc59;">
                                                        <th colspan="2"
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #ffffff; font-size: 15px; line-height: 10px; padding: 10px 5px;text-align: left;">
                                                            <strong>Price</strong>
                                                        </th>
                                                    </tr>
                                                    @if($promo_code)
                                                    <tr style="text-align: center; background: #f7ffee;">
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; font-weight: 600; text-align: left;">
                                                            Promo Code
                                                        </td>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: left;">
                                                            {{ $promo_code }}
                                                        </td>
                                                    </tr>
                                                   @if ($user_type == 'individual')
                                                    <tr style="text-align: center; background: #f7ffee;">
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; font-weight: 600; text-align: left;">
                                                            Course Cost
                                                        </td>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: left;">
                                                            ${{ $course_cost }}
                                                        </td>
                                                    </tr>
                                                    <tr style="text-align: center; background: #f7ffee;">
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; font-weight: 600; text-align: left;">
                                                            Discounted Cost
                                                        </td>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: left;">
                                                            ${{ $discounted_cost }}
                                                        </td>
                                                    </tr>
                                                   @else
                                                   <tr>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: right; font-weight: 600; text-align: left;">
                                                            Original Amount (per month)
                                                        </td>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: right;">
                                                            ${{ number_format($original_cost, 2) }}
                                                        </td>
                                                    </tr>
                                                   <tr>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: right; font-weight: 600; text-align: left;">
                                                            Final Amount (per month)
                                                        </td>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: right;">
                                                            ${{ number_format($costPerMonth, 2) }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: right; font-weight: 600; text-align: left;">
                                                            Amount (per year 10% off)
                                                        </td>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: right;">
                                                            ${{ number_format($costPerYear, 2) }}
                                                        </td>
                                                    </tr>

                                                    <tr style="text-align: center; background: #f7ffee;">
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: right; text-align: left;">
                                                            Per location cost (For {{ $number_of_locations }}
                                                            locations)
                                                        </td>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: right;">
                                                            ${{ number_format($costPerLocation, 2) }}
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: right; text-align: left;">
                                                            Per employee cost (For {{ $number_of_employees }}
                                                            employees)
                                                        </td>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: right;">
                                                            ${{ number_format($costPerUser, 2) }}
                                                        </td>
                                                    </tr>

                                                    @endif

                                                    @else
                                                    <tr>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: right; font-weight: 600; text-align: left;">
                                                            <?php if ($user_type != 'individual' &&
                                                            !$onlySpecialCourse) { ?> Sub Total (per
                                                            month) <?php } else { ?>Total
                                                            <?php } ?>
                                                        </td>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: right;">
                                                            <?php if (!$onlySpecialCourse) { ?> ${{ number_format($sub_total, 2) }} <?php } else { ?>
                                                            ${{ number_format($perYearCost, 2) }} <?php } ?>
                                                        </td>
                                                    </tr>
                                                   
                                                    <?php if ($user_type != 'individual' &&
                                                    !$onlySpecialCourse) { ?>
                                                    <?php if (isset($discount->title)) { ?>
                                                    <tr style="text-align: center; background: #f7ffee;">
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: right; font-weight: 600; text-align: left;">
                                                            Discount <small>({{ @$discount->title }})</small>
                                                        </td>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: right;">
                                                            ${{ number_format($discount_value, 2) }}
                                                        </td>
                                                    </tr>
                                                    <?php } ?>
                                                    <tr>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: right; font-weight: 600; text-align: left;">
                                                            Final Amount (per month)
                                                        </td>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: right;">
                                                            ${{ number_format($total_discounted, 2) }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: right; font-weight: 600; text-align: left;">
                                                            Amount (per year 10% off)
                                                        </td>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: right;">
                                                            ${{ number_format($perYearCost, 2) }}
                                                        </td>
                                                    </tr>

                                                    <tr style="text-align: center; background: #f7ffee;">
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: right; text-align: left;">
                                                            Per location cost (For {{ $number_of_locations }}
                                                            locations)
                                                        </td>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: right;">
                                                            ${{ number_format($per_location, 2) }}
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: right; text-align: left;">
                                                            Per employee cost (For {{ $number_of_employees }}
                                                            employees)
                                                        </td>
                                                        <td
                                                            style="border-collapse: collapse;font-family: Helvetica, sans-serif; box-sizing: border-box; color: #74787e; font-size: 15px; line-height: 10px; padding: 12px 10px; border-bottom: 1px solid #efefef; text-align: right;">
                                                            ${{ number_format($per_employee, 2) }}
                                                        </td>
                                                    </tr>
                                                    <?php } ?>
                                                    @endif
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

