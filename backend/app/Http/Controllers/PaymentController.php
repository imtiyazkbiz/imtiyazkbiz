<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PaymentModel;
use App\Models\PaymentCronSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use App\User;
use Validator;
use Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\CommonTrait;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
   
    use CommonTrait;
    private $base_url;
    private $profile_id;
    private $profile_key;

    /**
     * Construct function
     */
    public function __construct()
    {
        $this->base_url = env('MARCHANTE_SOLUTION_URL');
        $this->profile_id = env('MARCHANTE_SOLUTION_PROFILE_ID');
        $this->profile_key = env('MARCHANTE_SOLUTION_PROFILE_KEY');
    }

    public function paymentWithStoreCard(Request $request)
    {
        $validator = Validator::make(
            $request->all(), [
                'cardholder_street_address' => 'required',
                'cardholder_zip' => 'required',
                'transaction_amount' => 'required',                      
                'card_number' => 'required',    
                'card_exp_date' => 'required'
               
            ]);
        if ($validator->fails()) {
            $errors = json_decode($validator->errors());
            foreach ($errors as $error) {
                $message[] = $error[0];
            }
            $message = implode(",", $message);

            return response()->json(['message' => $message], 422);
        }

        try {          
            $request_data =  $request->all();   
            $request_data['invoice_number'] = CommonTrait::generateUniqueId();
            $response_data = PaymentModel::storeCardofUserForPayment($this->base_url, $this->profile_id,  $this->profile_key, $request_data);            
            if ($response_data['status'] == 'error') {

                return response()->json(['message' => $response_data['data']], 422);         
            }
            if ($response_data['data']['error_code'] == '000') {

                return response()->json(['message' => $response_data['auth_response_text']], 422);     
            }            


            return response()->json(['message' => 'Card saved successfully.', 'data' => $response_format], 200);            
        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 422);            
        }
    }

    public function paymentByStoreCardId(Request $request)
    {
        $validator = Validator::make(
            $request->all(), [
                'transaction_amount' => 'required',
                'card_id' => 'required'                
            ]);
        if ($validator->fails()) {
            $errors = json_decode($validator->errors());
            foreach ($errors as $error) {
                $message[] = $error[0];
            }
            $message = implode(",", $message);

            return response()->json(['message' => $message], 422);
        }

        try {
            $request_data =  $request->all();  
            $request_data['invoice_number'] = CommonTrait::generateUniqueId();         
            $response_data = PaymentModel::userPaymentByStoreCardId($this->base_url, $this->profile_id,  $this->profile_key, $request_data);            
            $response_format = CommonTrait::planTextConvertIntoArray($response_data);           
            if ($response_format['error_code'] == "000") {

            }

            return response()->json(['message' => 'Card saved successfully.', 'data' => $response_format], 200);            
        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 422);            
        }
    }
    public function recurringPaymentCron(){
    
           
        $processData=PaymentCronSchedule::
         select('tbl_payment_cron_scheduling.user_id','tbl_payment_cron_scheduling.transaction_id','tbl_payment_cron_scheduling.recurring','tbl_payment_cron_scheduling.next_processing_date','tbl_payment_cron_scheduling.recurring_pmt_num','tbl_payment_cron_scheduling.recurring_pmt_count','tbl_user_transactions.response','tbl_user_transactions.amount','tbl_user_transactions.email','tbl_user_transactions.courses')
        ->leftjoin('tbl_user_transactions','tbl_user_transactions.transaction_id','=','tbl_payment_cron_scheduling.transaction_id')
        ->where('next_processing_date',Carbon::now('UTC')->format('Y-m-d'))
        ->whereRaw('recurring_pmt_num <= recurring_pmt_count')
        ->get();
        foreach($processData as $process){
            try{
            DB::beginTransaction();
            $invoice = CommonTrait::generateUniqueId();
            $process['invoice_number'] = $invoice;  
           $response_data= PaymentModel::userRecurringPayment($this->base_url, $this->profile_id,  $this->profile_key,$process);
           if ($response_data['data']['error_code'] === '000') {
            $paymentId = PaymentModel::insertGetId([
                'user_id' => $process->user_id,
                'email'=>$process->email,
                'event_type' => 'Company Sign UP Recurring',
                'transaction_id' => $response_data['data']['transaction_id'],
                'error_code' => $response_data['data']['error_code'],
                'payment_type' => $process->recurring,
                'payment_status' => isset($response_data['data']['auth_code'])?$response_data['data']['auth_code']:'',
                'auth_response_text' => $response_data['data']['auth_response_text'],
                'amount' => $process->amount,
                'courses' => $process->courses,
                'invoice_number' => $invoice,
                'response' => json_encode($response_data),
                'created_at' => Carbon::now('UTC')
            ]);
            if($process->recurring=== 1){
              $next_process_date=date('Y-m-d', strtotime(Carbon::now('UTC')->format('Y-m-d'). ' + 30 days'));
            }else{
             $next_process_date= date('Y-m-d', strtotime(Carbon::now('UTC')->format('Y-m-d'). ' + 365 days'));
            }

            PaymentCronSchedule::where('transaction_id',$process->transaction_id)->update(['next_processing_date'=>$next_process_date,'recurring_pmt_num'=>$process->recurring_pmt_num + 1]);
           }else{
               log::debug("payment not processed.");
           }
           DB::commit();
        }catch (Exception $th) {
            DB::rollback();
            return ['status' => 'error', 'data' => $th->getMessage()];
        }
        }
       
    }
}
