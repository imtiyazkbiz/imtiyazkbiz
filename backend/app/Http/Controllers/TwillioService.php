<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;
use Twilio\Jwt\ClientToken;
use Auth;
use Carbon\Carbon;
use App\Models\EmployeeModel;
use App\Models\EmployeeCoursesModel;
use App\Http\Traits\CommonTrait;

class TwillioService extends Controller
{ 
    
    public $sid;
    public $token;
    public $serviceId;
    /**
     * constructer for geting value from .ENV file.
     *
     */
    public function __construct(){
        $this->sid = getenv("TWILIO_ACCOUNT_SID");
        $this->token = getenv("TWILIO_AUTH_TOKEN");
        $this->serviceId = getenv("TWILIO_APP_SERVICE_ID");
        $this->country_code = getenv("COUNTRY_CODE");
    }
    /**
     * method for generating Twillio service.
     *
     * 
     */
    public function index()
    {
        try
        {
            $service = $this->twilio->verify->v2->services
            ->create("My First Verify Service");
        }
        catch (Exception $e)
        {
            echo "Error: " . $e->getMessage();
        }
    
    }

      public function autoReminderSmsCron(){
       $employee1 = EmployeeCoursesModel::select('tbl_employee.phone_num as employee_phone','tbl_employee_courses.*','tbl_company.sms_status')
       ->leftjoin('tbl_employee','tbl_employee.id','=','tbl_employee_courses.employee_id')
       ->leftjoin('tbl_employee_company_locations','tbl_employee.id','=','tbl_employee_company_locations.employee_id')
       ->leftjoin('tbl_company','tbl_company.id','=','tbl_employee_company_locations.company_id')

       ->where('employee_course_status', 2)
       ->where('tbl_company.sms_status', 1)
       ->where('tbl_employee.status', 1)
       ->whereDate('employee_course_due_date', '=', Carbon::today()->addDays( 1 ))
       ->groupby('employee_id')->get();
            if( $employee1->count() > 0){
                foreach($employee1 as $emp1){
                   $phone = $emp1->employee_phone;
                   $reminder = "1 day";
                   if($phone){
                   $sendSms = self::sendDueSms($phone,$emp1->employee_id);    
                   }
                }
            }
    }
    
    public function sendDueSms($phone,$employee_id){
      try{
        $phone_number = preg_replace('~\D~', '', $phone);
        $twilio_number = "+19545197689";

        $client = new Client($this->sid, $this->token);
        $client->messages->create(
            // Where to send a text message (your cell phone?)
             $this->country_code.$phone_number,
           array(
                'from' => $twilio_number,
                'body' => 'Your required course on ' . getenv('SITE_NAME') .' is due tomorrow. Login at: ' . getenv('SITE_URL'). '#/login</a>'
            )
     );
      CommonTrait::emailLog("Course 1 day sms Reminder", $phone_number, $employee_id);
    }  
      catch (Exception $e)
      {
       echo "Error: " . $e->getMessage();
      } 
    }
    /**
     * This method is for sending OTP by SMS or call on the phone no.
     *
     * 
     */
    public function sendOTP(REQUEST $request)
    {
        try
        {
            
           

            $phone_number = preg_replace('~\D~', '', $request->phone_no);
            if (strlen((string) $phone_number) != 10) {

                return response()->json(['message' => "Phone number is not currect, try again.", 'data' => []], 422);
            }
          
            $phone_number = $this->country_code.$phone_number;

            $twilio = new Client($this->sid, $this->token);                                                
            if (!$twilio->verify->v2->services($this->serviceId)->verifications->create($phone_number, $request->chanel_type)) {

                return response()->json(['message' => 'Something is wrong, try again.'], 422);                   
            } 

            $verification = $twilio->verify->v2->services($this->serviceId)->verifications->create($phone_number, $request->chanel_type);
            if ($verification->status =="approved") {               

                return response()->json(['message' => 'OTP sent successfully.', 'data' => $verification->toArray()], 200);            
            } else {
                $result = $verification->toArray();
                if ($result['status'] == 'pending' || $result['status'] == 'success') {

                    return response()->json(['message' => 'OTP sent successfully.', 'data' => $result], 200);            
                }
                return response()->json(['message' => 'OTP did not send, try again.', 'data' => $verification->toArray()], 422);   
            }
        }
        catch (Exception $e)
        {
            
            return response()->json(['message' => $e->getMessage(), 'data' => []], 422);                      
        }
    
    }
    /**
     * This method is for verify OTP which is recieved by cleint.
     *
     * 
     */
    public function VerifysendOTP(REQUEST $request)
    { 
        try
        {     

            $phone_number = preg_replace('~\D~', '', $request->phone_no);
            if (strlen((string) $phone_number) != 10) {

               return response()->json(['message' => "Phone number is not currect, try again.", 'data' => []], 422);
           }
            $phone_number = $this->country_code.$phone_number;
            $twilio = new Client($this->sid, $this->token);
            $verification_check = $twilio->verify->v2->services($this->serviceId)
                                                ->verificationChecks
                                                ->create($request->otp, // code
                                                        ["to" => $phone_number]
                                                );
            if($verification_check->status =="approved"){                
                $update = EmployeeModel::where('id', Auth::user()->id)->update(['phone_num'=>$phone_number, 'is_2f_authenticated'=>'1']);
                return response()->json(['message' => 'Phone number verified successfully.', 'data' => $verification_check->toArray()], 200);   
            }else{                

                return response()->json(['message' => 'OTP did not match, try again', 'data' => $verification_check->toArray()], 422);  
            }                                             
        }
        catch (Exception $e)
        {
            return response()->json(['message' => $e->getMessage(), 'data' => []], 422);        
        }                                      
    }
}
