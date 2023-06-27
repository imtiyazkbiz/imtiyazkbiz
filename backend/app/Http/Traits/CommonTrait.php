<?php

namespace App\Http\Traits;

use App\Http\Controllers\UnsubscribeController;
use Intervention\Image\Facades\Image;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailLogsModel;

trait CommonTrait {

    static function storeBaseEncodeImage($path, $image) {
        $logoname = time() . '.' . explode('/', explode(':', substr($image, 0, strpos($image, ';'))) [1]) [1];
        $save = Image::make($image)->save(public_path($path) . $logoname);
        if ($save) {

            return $logoname;
        } else {

            return FALSE;
        }
    }

    public static function planTextConvertIntoArray($string) {
        $stringInArray = explode("&", $string);

        $totalFields = count($stringInArray);
        if (is_array($stringInArray)) {
            $result = array();
            for ($i = 0; $i < $totalFields; $i++) {
                $value = explode("=", $stringInArray[$i]);
                $result[$value[0]] = $value[1];
            }

            return $result;
        }

        return FALSE;
    }

    public static function generateUniqueId() {
        return mt_rand(000000, 999999);
    }

    public static function sendEmailToAssginCourseEmployee($dataCourses, $employee_emails, $employee_id) {
        if((new UnsubscribeController)->wantToSendTheEmail($employee_id) == FALSE) {
            return true;
        }
        $result = Mail::send('course_assigned', $dataCourses, function($message) use ($employee_emails) {
            $message->to($employee_emails)->subject(env('SITE_NAME') . ' - Course(s) Now Available!');
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        });
        self::emailLog("Course Assigned", $employee_emails, $employee_id);

        return $result;
    }

    public static function sendWelcomeEmailToUser($data, $email, $employee_id, $template) {
        $sendEmailToUser = (new UnsubscribeController())->wantToSendTheEmail($employee_id);
        if ($sendEmailToUser === FALSE) {
            return FALSE;
        }

        $result = Mail::send($template, $data, function($message) use ($email) {
            $message->to($email)->subject('Welcome to ' . env('SITE_NAME'));
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        });
        self::emailLog($template, $email, $employee_id);

        return $result;
    }

    public static function emailLog($event, $email, $employee_id) {
        $emaillog = new EmailLogsModel();
        $emaillog->event = $event;
        $emaillog->email = $email;
        $emaillog->user_id = $employee_id;
        $emaillog->save();
    }

    public static function isValidEmail($email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === FALSE) {

            return FALSE;
        }

        return TRUE;
    }

    public static function isValidPhone($phone) {

        $phone_number = preg_replace('~\D~', '', $phone);
        if (strlen((string)$phone_number) != 10) {

            return FALSE;
        }

        return TRUE;

    }

    public static function hideEmail($email) {
        $mail_parts = explode("@", $email);
        $length = strlen($mail_parts[0]);

        if ($length <= 4 & $length > 1) {
            $show = 1;
        } else {
            $show = floor($length / 2);
        }
        $hide = $length - $show;
        $replace = str_repeat("*", $hide);

        return substr_replace($mail_parts[0], $replace, $show, $hide) . "@" . $mail_parts[1];
    }

    public static function hideCardNumber($cardnumber) {
        return str_replace(range(0, 9), "*", substr($cardnumber, 0, -4)) . substr($cardnumber, -4);
    }

    public static function logInactiveEmployeeInfo($infoDataArray) {
        $myfile = fopen(storage_path() . '/logs/' . "inactive-employee.log", "a");
        $data = implode(' ', $infoDataArray);
        fwrite($myfile, $data . "\n");
        fclose($myfile);
    }
}