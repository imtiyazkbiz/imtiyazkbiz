<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/style.css" />
</head>

<body style="text-align:center;">
    <div class="certificate-outer print" style="width:1150px; float:center;">
        <div style="width: 100%; max-width: 96%;padding-left: 15px;">

            <table
                style="width:100%; border: solid 4px #19437a; margin:0px; min-height: 614px; background: #fff; text-align: center;">
                <tr>
                    <td>
                        <table style="width:100%; position:relative; padding:10px; border-spacing: 15px;">
                            <tr>
                                <td colspan="3" class="certi-top-img">
                                    <img src="images/certi-top-bg.png"
                                        style="position: absolute; top: -26px; left: 32px;" />
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="certi-no" style="text-align:center;">
                                    <p
                                        style="font-size: 14px; color: #0f0f0f; font-weight: 600; margin: 20px 0; text-align: right; padding: 0 20px;">
                                        Certificate No. : {{ $certificate_no }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="certi-logo" style="text-align:center;padding: 0px 0 25px 0;">
                                    <img src="images/{{ env('SITE_LOGO') }}" />
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="certimain-head" style="text-align:center;">
                                    <h1 style="font-size: 24px; color: #33caf5; font-weight: 600; margin: 0 0 15px 0;">
                                        {{ $name_of_course }}</h1>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="certi-head" style="text-align:center;">
                                    <h2 style="font-size: 20px; color: #19447a; margin: 0 0 20px 0; font-weight: 600;">
                                        Certificate of Traning</h2>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="certi-issued" style="text-align:center;">
                                    <h2
                                        style="font-family: 'Times New Roman', Times, serif; font-size: 16px; color: #0f0f0f; margin: 0 0 26px 0;">
                                        This certificate is issued to</h2>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="certi-name" style="text-align:center;">
                                    <h3
                                        style="font-family: 'Dancing Script', cursive; font-size: 28px; color: #0f0f0f; margin: 0 0 20px 0; font-weight: 600;">
                                        {{ $employee_first_name }} {{ $employee_last_name }}</h3>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="certi-name" style="text-align:center;">
                                    <h3
                                        style="font-family: 'Dancing Script', cursive; font-size: 28px; color: #0f0f0f; margin: 0 0 20px 0; font-weight: 600;">
                                        {{ $date_of_birth }}</h3>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="certi-name" style="text-align:center;">
                                    <h3
                                        style="font-family: 'Dancing Script', cursive; font-size: 28px; color: #0f0f0f; margin: 0 0 20px 0; font-weight: 600;">
                                        {{ $current_employer }}</h3>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="certi-name" style="text-align:center;">
                                    <h3
                                        style="font-family: 'Dancing Script', cursive; font-size: 28px; color: #0f0f0f; margin: 0 0 20px 0; font-weight: 600;">
                                        {{ $student_home_address }}</h3>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="certi-name" style="text-align:center;">
                                    <h3
                                        style="font-family: 'Dancing Script', cursive; font-size: 28px; color: #0f0f0f; margin: 0 0 20px 0; font-weight: 600;">
                                        {{ $city_state_zip }}</h3>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="certi-issued" style="text-align:center;">
                                    <h2
                                        style="font-family: 'Times New Roman', Times, serif; font-size: 16px; color: #0f0f0f; margin: 0 0 26px 0;">
                                        For successfully completing the {{ $name_of_course }}</h2>
                                </td>
                            </tr>

                            <tr>
                                <td style="height:100px;"></td>
                            </tr>
                            <tr class="row signature-section"
                                style="padding: 10px 10px 10px 10px; margin-top:20px; margin-bottom: 40px;">

                                <td class="certi-sign"
                                    style="text-align:center; width:33%; font-size: 14px; color: #0f0f0f; padding: 4px 0; margin-top: 30px; border-bottom: solid 1px #19447a;">
                                    <img style="height: 84px;" src="images/signature-1-sample.png" />
                                    <p style="margin-top: 20px; margin-bottom: 1rem;">{{ $signature_title_1 }}</p>
                                </td>


                                <td class="signature-img" style="width:33%; text-align:center;">
                                    <img style="height: 84px;" src="images/signature-bg.png" />
                                </td>
                                <td class="certi-sign"
                                    style="text-align:center; width:33%;font-size: 14px; color: #0f0f0f; padding: 4px 0; margin-top: 30px; border-bottom: solid 1px #19447a;">
                                    <img style="height: 84px;" src="images/signature-2-sample.png" />
                                    <p style="margin-top: 20px; margin-bottom: 1rem;">{{ $signature_title_2 }}</p>
                                </td>
                            </tr>
                            <tr class="certi-footer" style="text-align: center;">
                                <td colspan="3">
                                    <p
                                        style="font-size: 11px; color: #0f0f0f; line-height: 20px; padding: 0 20px; margin: 0 0 10px 0;">
                                        {{ $text }}</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>
