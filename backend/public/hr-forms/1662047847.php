<?php
namespace App\Http\Controllers;
use App\Models\HRFormModel;
use App\Models\HRFormAssignmentModel;
use App\Models\EmployeeCompanyLocationsModel;
use App\Models\HRFormEmployeeUploadsModel;
use App\Models\EmployeeModel;
use App\Http\Traits\CommonTrait;
use App\Models\CompanyModel;
use Illuminate\Http\Request;
use http\Env\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Validator;
use Auth;
use Carbon\Carbon;

class HrformController extends Controller
{
    use CommonTrait;

   //Hr-Forms
   public function companyTypeEmployee(){
    $user = Auth::user();
    $companies = CompanyModel::getCompaniesByAdminUser($user->id);
    $company_id=[];
    $where='';
    if ($companies['isParent'] != 0)
    {
        if (is_array($companies['isParent']))
        {
            $company_id = $companies['isParent'];
        }
        else
        {
            $company_id = array(
                $companies['isParent']
            );
        }

        $where='tbl_employee_company_locations.company_id';
    }
    else
    {
        $company_id = $companies['isLocations'];
        $where='tbl_employee_company_locations.location_id';
    }
    
    try {                       
        $result = EmployeeCompanyLocationsModel::select('tbl_employee.first_name','tbl_employee.last_name','tbl_employee.id')->leftjoin('tbl_employee','tbl_employee.id','=','tbl_employee_company_locations.employee_id')->where('tbl_employee.status',1)->whereIn($where,$company_id)->get();
        
        return response()->json($result, 200);                        
    } catch (Exception $ex) {

        return response()->json(['message' => $ex->getMessage()],422);
    }
}
public function allCompanyHrForms(){
    try{
        $user = Auth::user();
        $adminCompany=EmployeeCompanyLocationsModel::select('company_id')->where('employee_id',$user->id)->first();
        $result= HRFormModel::select('tbl_hr_form.id','tbl_hr_form.name','tbl_hr_form.description','tbl_hr_form.file','tbl_hr_form.default_admin','tbl_hr_form.default_manager','tbl_hr_form.default_employee')
        ->with('assigned_employees')->where('company_id',$adminCompany->company_id)->orderby('tbl_hr_form.name','asc')->get();  
        return response()->json($result, 200);                
    } catch (Exception $ex) {
        return response()->json(['message' => $ex->getMessage()],422);
    }
}
public function saveHrFormData(Request $request){
    try{
    $user = Auth::user();
    $adminCompany=EmployeeCompanyLocationsModel::select('company_id')->where('employee_id',$user->id)->first();
    $file_name="";
    if ($request->hasFile('file')) {
            $file = $request->file;
            $fileExtension = $file->getClientOriginalExtension(); // getting image extension
            $fileName = time() . '.' . $fileExtension;
            $file->move('hr-forms', $fileName);
            $file_name =$fileName;
    }
      $hrformsave= new HrFormModel();
      $hrformsave->company_id=$adminCompany->company_id;
      $hrformsave->name=$request->file_name;
      $hrformsave->description=$request->file_description;
      $hrformsave->file=$file_name;
      $hrformsave->default_admin=$request->permissions_default_admin == "true" ? 1 : 0;
      $hrformsave->default_manager=$request->permissions_default_manager == "true" ? 1 : 0;
     $hrformsave->default_employee=$request->permissions_default_employee == "true" ? 1 : 0;
      $hrformsave->created_by= $user->id;
      $hrformsave->created_at= Carbon::now('UTC');
      $hrformsave->save();
      if($request->permissions_default_admin=="true"){
        $companyAdmins=EmployeeCompanyLocationsModel::select('tbl_employee.id','tbl_company.name')
        ->leftjoin('tbl_employee','tbl_employee.id','=','tbl_employee_company_locations.employee_id')
        ->leftjoin('tbl_company','tbl_company.id','=','tbl_employee_company_locations.company_id')
        ->where('tbl_employee.role_id',2)
        ->where('tbl_employee_company_locations.company_id',$adminCompany->company_id)
        ->where('tbl_employee.status',1)
        ->groupBy('tbl_employee_company_locations.employee_id')
        ->get();
        foreach($companyAdmins  as $companyAdmin){
            $this->sendHrFormNotificationEmail($companyAdmin->id,$request->file_name,$file_name, $companyAdmin->name);  
        }
      }
      if($request->permissions_default_manager=="true"){
        $companyManagers=EmployeeCompanyLocationsModel::select('tbl_employee.id','tbl_company.name')
        ->leftjoin('tbl_employee','tbl_employee.id','=','tbl_employee_company_locations.employee_id')
        ->leftjoin('tbl_company','tbl_company.id','=','tbl_employee_company_locations.company_id')
        ->where('tbl_employee.role_id',3)
        ->where('tbl_employee_company_locations.company_id',$adminCompany->company_id)
        ->where('tbl_employee.status',1)
        ->groupBy('tbl_employee_company_locations.employee_id')
        ->get();
        foreach($companyManagers  as $companyManager){
            $this->sendHrFormNotificationEmail($companyManager->id,$request->file_name,$file_name,$companyManager->name);    
        }
      }

      if($request->permissions_default_employee=="true"){
        $companyEmployees=EmployeeCompanyLocationsModel::select('tbl_employee.id','tbl_company.name')
        ->leftjoin('tbl_employee','tbl_employee.id','=','tbl_employee_company_locations.employee_id')
        ->leftjoin('tbl_company','tbl_company.id','=','tbl_employee_company_locations.company_id')
        ->where('tbl_employee.role_id',4)
        ->where('tbl_employee_company_locations.company_id',$adminCompany->company_id)
        ->where('tbl_employee.status',1)
        ->groupBy('tbl_employee_company_locations.employee_id')
        ->get();
        log::debug($companyEmployees);
        foreach($companyEmployees  as $companyEmployee){
            $this->sendHrFormNotificationEmail($companyEmployee->id,$request->file_name,$file_name,$companyEmployee->name);    
        }
      }
    
      return response()->json('File saved successfully.', 200);   
    }catch(Exception $ex){
        return response()->json(['message' => $ex->getMessage()],422);
    }
}
public function updateHrFormData(Request $request){
    try{
        $file_name="";
        if ($request->hasFile('file')) {
                $file = $request->file;
                $fileExtension = $file->getClientOriginalExtension(); // getting image extension
                $fileName = time() . '.' . $fileExtension;
                $file->move('hr-forms', $fileName);
                $file_name =$fileName;

                HrFormModel::where('id',$request->id)->update(['file'=>$file_name]);
        }
        HrFormModel::where('id',$request->id)
        ->update(['name'=>$request->file_name,
        'description'=>$request->file_description,
        'default_admin'=>$request->permissions_default_admin == "true" ? 1 : 0,
        'default_manager'=>$request->permissions_default_manager == "true" ? 1 : 0,   
        'default_employee'=>$request->permissions_default_employee == "true" ? 1 : 0]);       
          return response()->json('File updated successfully.', 200);   
        }catch(Exception $ex){
            return response()->json(['message' => $ex->getMessage()],422);
        }
}
public function assignHrForm(Request $request){
    try{
    $user = Auth::user();
    $adminCompany=EmployeeCompanyLocationsModel::select('company_id','tbl_company.name')->leftjoin('tbl_company','tbl_company.id','=','tbl_employee_company_locations.company_id')->where('tbl_employee_company_locations.employee_id',$user->id)->first();
    
    $existingassignedData=HRFormAssignmentModel::select('employee_id')->where('hrform_id',$request->hrform_id)->get();

    $exisitngEmployees=[];
    foreach($existingassignedData as $existingassignedDatas){
       array_push($exisitngEmployees, $existingassignedDatas->employee_id);
    }
 
    $newAdded=array_diff($request->assign_employee, $exisitngEmployees);
    $removedEmployees=array_diff($exisitngEmployees, $request->assign_employee);
    if($newAdded){
        foreach($newAdded as $assign_employees){
            $assignedEmployee= new HRFormAssignmentModel();
            $assignedEmployee->hrform_id=$request->hrform_id;
            $assignedEmployee->employee_id=$assign_employees;
            $assignedEmployee->company_id=$adminCompany->company_id;
            $assignedEmployee->save();

            $this->sendHrFormNotificationEmail($assign_employees,$request->file_name,$request->file,$adminCompany->name);  
        }
      }

      if($removedEmployees){
        HRFormAssignmentModel::where('hrform_id',$request->hrform_id)->whereIn('employee_id',$removedEmployees)->delete(); 
      }

      return response()->json('File assigned successfully.', 200);   
    }catch(Exception $ex){
        return response()->json(['message' => $ex->getMessage()],422);
    }
}
public function sendHrFormNotificationEmail($employee_id,$fileName,$file,$company_name){
    $getEmployeeDetail=EmployeeModel::where('id',$employee_id)->first();
    $email=$getEmployeeDetail->email;
    $data = array(
        'full_name' => $getEmployeeDetail->full_name,
        'file_name' => $fileName,
        'file'=>$file,
        'company_name'=>$company_name
    );   
    if($email){       
        Mail::send('hrform_notify', $data, function ($message) use ($email) {
            $message->to($email)->subject(env('SITE_NAME').' - HR Form Notification');                  
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        });
    }
    CommonTrait::emailLog("HR From Notification", $email, $employee_id);
}
public function deleteHrForm(Request $request){
    try{
        if($request->hrform_id){
        HRFormModel::where('id',$request->hrform_id)->delete(); 
        HRFormAssignmentModel::where('hrform_id',$request->hrform_id)->delete(); 
        HRFormEmployeeUploadsModel::where('hrform_id',$request->hrform_id)->delete();
        return response()->json('File deleted successfully.', 200);      
        }else{
            return response()->json('File not deleted.', 200);    
        }          
    } catch (Exception $ex) {
        return response()->json(['message' => $ex->getMessage()],422);
    }
}

public function allEmployeeHrForms(){
    try{
    $user=Auth::user();

    $result1= HRFormAssignmentModel::select('tbl_hr_form.id','tbl_hr_form.name','tbl_hr_form.description','tbl_hr_form.file')->leftjoin('tbl_hr_form','tbl_hr_form.id','=','tbl_hrform_assigned_employees.hrform_id')
        ->where('employee_id',$user->id)->get();
    $result2=[];
    if($user->role_id==2){
        $adminCompany=EmployeeCompanyLocationsModel::select('company_id')->where('employee_id',$user->id)->first();
        $result2= HRFormModel::select('tbl_hr_form.id','tbl_hr_form.name','tbl_hr_form.description','tbl_hr_form.file')
        ->where('company_id',$adminCompany->company_id)
        ->where('default_admin',1)->get(); 

    }
  
    if($user->role_id==3){
        $adminCompany=EmployeeCompanyLocationsModel::select('company_id')->where('employee_id',$user->id)->first();
        $result2= HRFormModel::select('tbl_hr_form.id','tbl_hr_form.name','tbl_hr_form.description','tbl_hr_form.file')
        ->where('company_id',$adminCompany->company_id)
        ->where('default_manager',1)->get(); 
    }

    if($user->role_id==4){
        $adminCompany=EmployeeCompanyLocationsModel::select('company_id')->where('employee_id',$user->id)->first();
        $result2= HRFormModel::select('tbl_hr_form.id','tbl_hr_form.name','tbl_hr_form.description','tbl_hr_form.file')
        ->where('company_id',$adminCompany->company_id)
        ->where('default_employee',1)->get(); 
    }

    $merged = $result1->merge($result2);
    $result = $merged->all();
   
    return response()->json($result, 200);   
    }catch(Exception $ex){
        return response()->json(['message' => $ex->getMessage()],422);
    }
}

public function uploadFilledHrForm(Request $request){
    try{
        $user = Auth::user();
        $userCompany=EmployeeCompanyLocationsModel::select('company_id')->where('employee_id',$user->id)->first();
        $file_name="";
        if ($request->hasFile('file')) {
                $file = $request->file;
                $fileExtension = $file->getClientOriginalExtension(); // getting image extension
                $fileName = time() . '.' . $fileExtension;
                $file->move('hr-forms/user-uploads', $fileName);
                $file_name =$fileName;
        }
          $hrformfilesave= new HRFormEmployeeUploadsModel();
          $hrformfilesave->company_id=$userCompany->company_id;
          $hrformfilesave->hrform_id=$request->hrform_id;
          $hrformfilesave->employee_id= $user->id;
          $hrformfilesave->upload=  $file_name;
          $hrformfilesave->created_at= Carbon::now('UTC');
          $hrformfilesave->save();
       
        
          return response()->json('File uploaded successfully.', 200);   
        }catch(Exception $ex){
            return response()->json(['message' => $ex->getMessage()],422);
        }
}
public function hrFormReport(Request $request){
    try{
        $where_data=[];
        $user = Auth::user();
        if (!empty($request->search))
            {
                $search = $request->search;
                $search = explode(" ", $search);
                foreach ($search as $key => $name)
                {
                    $where_data[] = ['tbl_employee.full_name', 'like', '%' . $name . '%'];
                }
            } 
            $where=[];
            $companyIds=[];
            if ($request->company_id != '' && $request->company_id != 0) {
                $companyId = $request->company_id;
                $companies = CompanyModel::where('id', $companyId)->first();
             
                $companyIds =
                    $request->company_id
                ;
                if ($companies->parent_id != 0) {
                    $where = 'tbl_employee_company_locations.location_id';
                } else {
                    $where = 'tbl_employee_company_locations.company_id';
                }
            }else{
                if($user->role_id != 1){
                    $companies = CompanyModel::getCompaniesByAdminUser($user->id);
                    if ($companies['isParent'] != 0) {
                      
                            $companyIds = 
                                $companies['isParent'];
                        $where = 'tbl_employee_company_locations.company_id';

                    } else {
                        $companyIds = $companies['isLocations'];
                        $where = 'tbl_employee_company_locations.location_id';

                    }
                }
            }
            $requestData = $request->all();
            $startFrom = "";
            $limit = "";           
            if (isset($requestData['page']) && isset($requestData['per_page'])) {
                $startFrom = ($requestData['page'] == 0)?($requestData['page'] * $requestData['per_page']):($requestData['page'] - 1) * $requestData['per_page'];
                $limit = $requestData['per_page'];       
            }
        $data=DB::table('tbl_hr_form_employee_uploads')
        ->select('tbl_company.name as location','tbl_hr_form_employee_uploads.*','tbl_hr_form.name','tbl_hr_form.file','tbl_employee.first_name','tbl_employee.last_name')
        ->leftjoin('tbl_employee','tbl_employee.id','=','tbl_hr_form_employee_uploads.employee_id')
        ->leftjoin('tbl_hr_form','tbl_hr_form.id','=','tbl_hr_form_employee_uploads.hrform_id')
        ->leftjoin('tbl_employee_company_locations','tbl_employee_company_locations.employee_id','=','tbl_hr_form_employee_uploads.employee_id')
        ->leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))
        ->where($where,$companyIds)
        ->where($where_data);
     
        $data->orderby('tbl_employee.first_name','asc');
        if ($limit != '') {                    
            $data->skip($startFrom);
            $data->take($limit);
        } 
        $records=array();
        $total =  0;
        $records=  $data->get();
       
        if (!empty($records->toArray())) {
            $total = count($records->toArray());
        }
        return response()->json(['report'=>$records,'total'=>$total], 200);
    }catch(Exception $ex) {
        return response()->json(['message'=> $ex->getMessage()], 422);
    }
}
public function downloadFile($file)
{
    $complete_path = public_path() . '/hr-forms/' . $file  . ".pdf";
    $headers = ['Access-Control-Allow-Origin' => '*', 'Access-Control-Allow-Methods' => 'GET, PUT, POST, DELETE, HEAD, OPTIONS', 'Access-Control-Allow-Credentials' => 'true', 'Access-Control-Max-Age' => '86400', 'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With'];
    return response()->download($complete_path, $file, $headers);
}
public function downloadUploadFile($file){
    $complete_path = public_path() . '/hr-forms/user-uploads/' . $file  . ".pdf";
    $headers = ['Access-Control-Allow-Origin' => '*', 'Access-Control-Allow-Methods' => 'GET, PUT, POST, DELETE, HEAD, OPTIONS', 'Access-Control-Allow-Credentials' => 'true', 'Access-Control-Max-Age' => '86400', 'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With'];
    return response()->download($complete_path, $file, $headers);
}
}
