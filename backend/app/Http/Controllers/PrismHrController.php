<?php
namespace App\Http\Controllers;
use App\Http\Traits\CommonTrait;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\EmployeeModel;
use Validator;
class PrismHrController extends Controller {

    public function createPeoSession(){
        // createPeoSession API
        $url = "https://api.engagepeo.com/api-1.27/services/rest/login/createPeoSession";
        $data = "username=HOTLMS&password=%24QYCx%5EaGDH2aJuNKvfoK&peoId=420*TEST";
        $curl = curl_init($url);
        $headers = array(
            "Content-Type: application/x-www-form-urlencoded",
            "Accept: application/json",
        );
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => $headers,
        ));

        $login_resp = curl_exec($curl);
        curl_close($curl);
        return json_decode($login_resp, TRUE);
    }
    
    public function getEmployeeList(){
        $session=$this->createPeoSession();
        // getEmployeeList
        $url = "https://api.engagepeo.com/api-1.27/services/rest/employee/getEmployeeList?clientId=210330&statusClass=A";
        $headers = array(
            "Accept: application/json",
            "sessionId: ".$session['sessionId'],
            );
            $curl = curl_init($url);
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HTTPHEADER => $headers,
            ));
            $emplist_resp = curl_exec($curl);
            curl_close($curl);

            return json_decode($emplist_resp, TRUE); 
        }
        public function getEmployeesData(){
            $session=$this->createPeoSession();
            $employeesList=$this->getEmployeeList();

            $employeeQueryParm="";
            foreach($employeesList as $key=>$employee){
                if(isset($employee['employeeId'])){
                    $employeeId=$employee['employeeId'];
                    if(gettype($employeeId)== "array"){
                        for($i=0; $i < sizeof($employeeId); $i++ ){
                        
                            if(strlen($employeeQueryParm) > 0){
                            $employeeQueryParm .= "&employeeId=".$employeeId[$i];
                            }else{
                            $employeeQueryParm= "employeeId=".$employeeId[$i];
                            }
                        }
                    }
                }
            }
            // getEmployeesData
            $url = "https://api.engagepeo.com/api-1.27/services/rest/employee/getEmployee?".$employeeQueryParm."&clientId=210330&options=Client";
            $headers = array(
                "Accept: application/json",
                "sessionId: ".$session['sessionId'],
             );
            $curl = curl_init($url);
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HTTPHEADER => $headers,
            ));
            $emp_resp = curl_exec($curl);
             curl_close($curl);   
             return json_decode($emp_resp, TRUE); 
        }
       public function registerPrismHrUser(){
            $employeesList=$this->getEmployeesData();
            $access_code="Password@123";
           foreach($employeesList as $key=>$employeeData){
                if(gettype($employeeData)== "array"){
                    foreach($employeeData as $employees) { 
                 
                        $company_name = $employees['company'];
                        $location = $employees['location'];
                        $data = CompanyModel::select('id', 'name')->whereRaw('LOWER(name) = ?', [strtolower($company_name) ])->first();
                        $company_id="";
                        if ($data) {
                            $company_id = $data->id;
                        }
                        if ($company_id) {
                            $token = 'Bearer' . sha1(time());
                            $employee = new EmployeeModel;
                            $employee->role_id = 4;
                            $employee->first_name = $employees['firstName'];
                            $employee->last_name = $employees['lastName'];
                            $employee->full_name = $employees['firstName'] . ' ' . $employees['lastName'];
                            $employee->type = 'employee';
                            $employee->email = $employees['emailAddress'];
                            $employee->phone_num = preg_replace('~\D~', '', $employees['mobilePhone']);
                            $employee->access_code = $access_code;
                            $employee->password = md5($access_code);
                            $employee->user_name = $employees['userId'] ? $employees['userId']:$employees['emailAddress'];
                            $employee->api_token = $token;
                            $employee->city = $employees['city'];
                            $employee->state = $employees['state'];
                            $employee->zipcode = $employees['zipcode'];
                            $employee->address = $employees['addressLine1'];
                            $employee->dob = $employees['birthDate'];
                            $employee->payroll_id = $employees['id'];
                            $employee->prism_response = json_encode($employees);
                            $employee->added_on = date('Y-m-d');
                            $employee->save();
                            $userAuthToken = $employee->createToken('MyApp')->accessToken;
                            $employee->api_token = $userAuthToken;
                            $employee_id = $employee->id;  
                            log::debug($employee->full_name);

                            
                        }else{
                            return response()->json(['error' => ['code' => '422', 'message' => 'No Company found with this name.']], 422);
                        }
                    }
                }
            }
       }
}

?>