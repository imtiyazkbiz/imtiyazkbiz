<?php

namespace App;

use App\Http\Controllers\TestingDebuggingController;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Traits\CommonTrait;
use Auth;


class User extends Model implements AuthenticatableContract, AuthorizableContract {
    use HasApiTokens, Authenticatable, Authorizable;

    protected $table = "tbl_employee";

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
        'api_token',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function getUserByUsernamePassword($request) {

        $pass = Hash::make($request->get('password'));
        $user = User::where('email', $request->email)->first();
        if ($user) {
            if (Hash::check($request->get('password'), $user->password, [])) {

                return $user;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    public static function sendLeadToSupport($data) {
        $user_id = 0;
        $email = config('mail.support');
        CommonTrait::emailLog("User Lead", $email, $user_id);

        return Mail::send('user_leads', $data, function($message) use ($email) {
            $message->to($email)->subject(env('SITE_NAME') . ' - New Lead');
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        });
    }

    public static function contact($data) {
        $user_id = 0;
        $data['content'] = $data['message'];
        unset($data['message']);
        $email = config('mail.support');
        CommonTrait::emailLog("User Contact", $email, $user_id);

        return Mail::send('user_contact', $data, function($message) use ($email) {
            $message->to($email)->subject(env('SITE_NAME') . ' - New User Contact us');
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        });
    }

    public static function sendSurveyToSupport($data) {
        $email = config('mail.support');
        CommonTrait::emailLog("User Survey", $email, 0);
        Mail::send('user_surveys', $data, function($message) use ($email) {
            $message->to($email)->subject(env('SITE_NAME') . ' - User Survey');
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        });
    }

    public static function sendPreTestToSupport($data) {
        $email = config('mail.support');
        CommonTrait::emailLog("User Pre test", $email, 0);
        Mail::send('user_pretest', $data, function($message) use ($email) {
            $message->to($email)->subject(env('SITE_NAME') . ' - User Pre Test');
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        });
    }

    public static function calculateCorporateDiscount($totalUser, $totalCost) {
        $discount = DB::table('tbl_corporate_discount_rules')->where('calculation_min_value', '<=', $totalUser)->where('calculation_type', '=', 'user')->select('discount_value', DB::raw("(select dr_corporate.discount_value from tbl_corporate_discount_rules as dr_corporate where dr_corporate.calculation_type = 'corporate') as corporate_discount"))->orderBy('calculation_min_value')->get();

        $discountValues = array();
        if ($discount->count() > 0) {
            $discount[0]->corporate_discount;
            $discountValues[] = $discount[0]->corporate_discount;
            $discountValueApply = $totalCost - ($totalCost * $discount[0]->corporate_discount) / 100;

            foreach ($discount as $key => $value) {
                $discountValueApply = $discountValueApply - ($discountValueApply * $value->discount_value) / 100;
                $discountValues[] = $value->discount_value;
            }
        } else {
            $isCorporateDiscount = DB::table('tbl_corporate_discount_rules')->where('calculation_type', '=', 'corporate')->select('discount_value')->first();

            $discountValues = array();
            $discountValueApply = $totalCost;
            if ($isCorporateDiscount != NULL) {
                $discountValues[] = $isCorporateDiscount->discount_value;
                $discountValueApply = $totalCost - ($totalCost * $isCorporateDiscount->discount_value) / 100;
            }
        }

        return round($discountValueApply, 2);
    }

    public static function getDiscountOffer($totalCourse, $totalUser, $subTatal, $userType) {

        $TotalCourseCountDiscount = DB::table('tbl_discount_rules')->where('calculation_type', $userType)->where('calculation_value_type', 'course count')->where('calculation_value', '<=', $totalCourse)->where('status', 1)->orderBy('tbl_discount_rules.calculation_value', 'desc')->first();

        $TotalCostAmountDiscount = DB::table('tbl_discount_rules')->where('calculation_type', $userType)->where('calculation_value_type', 'total amount')->where('calculation_value', '<=', $subTatal)->where('status', 1)->orderBy('tbl_discount_rules.calculation_value', 'desc')->first();

        if ($TotalCourseCountDiscount != NULL && $TotalCostAmountDiscount == NULL) {

            return $TotalCourseCountDiscount;
        } else if ($TotalCourseCountDiscount == NULL && $TotalCostAmountDiscount != NULL) {

            return $TotalCostAmountDiscount;
        } else if ($TotalCourseCountDiscount != NULL && $TotalCostAmountDiscount != NULL) {
            $discountCountCourse = 0;
            $discountAmountCost = 0;

            if ($TotalCourseCountDiscount->discount_type == 1) {

                $discountCountCourse = ($subTatal * $TotalCourseCountDiscount->discount_value) / 100;
            } else {

                $discountCountCourse = $TotalCourseCountDiscount->discount_value;
            }

            if ($TotalCostAmountDiscount->discount_type == 1) {

                $discountAmountCost = ($subTatal * $TotalCostAmountDiscount->discount_value) / 100;
            } else {

                $discountAmountCost = $TotalCostAmountDiscount->discount_value;
            }

            if ($discountCountCourse <= $discountAmountCost) {

                return $TotalCourseCountDiscount;
            }

            return $TotalCostAmountDiscount;
        } else {

            return NULL;
        }
    }

    public static function getPerLocationCost($subtotal, $employees, $locations) {
        $quetient = ($subtotal / $locations);

        if ($quetient < 19) {

            return 19;
        } else {
            return $quetient;
        }

        //       return 0;

    }

    public static function calculateDiscountPercentage($amount, $discount, $sign) {
        if ($sign == 1) {

            return ($amount * $discount) / 100;
        }

        return $discount;
    }
}
