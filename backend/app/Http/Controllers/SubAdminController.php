<?php
namespace App\Http\Controllers;
use App\Http\Traits\CommonTrait;
use http\Env\Response;
use Illuminate\Http\Request;
use App\Models\EmployeeModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Validator;
use Carbon\Carbon;
use Auth;
class SubAdminController extends Controller 
{
    public function getRights(){
        //get all rights
        $rights=DB::table('tbl_rights')->select('id','right_name')->get(); 
        return response()
        ->json($rights, 200);
    }
    public function saveSubAdmin(Request $request)
    {
        $validator = Validator::make(
            $request->all(), [
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'password' => 'required'
                    ]
        );
        if ($validator->fails()) {
            $errors = json_decode($validator->errors());
            foreach ($errors as $error) {
                $message[] = $error[0];
            }
            $message = implode("<br/>", $message);
    
            return response()->json(['message'=> $message],422);
        }
        try {
            // save subadmin admin
            DB::beginTransaction();
            if (!empty($request->email)) {
                if (CommonTrait::isValidEmail($request->email) == FALSE) {
                  
                    return response()->json(['message'=> "Email is not valid, Enter correct email."], 422);
                }
                $isEmailExist = EmployeeModel::where('email', $request->email)
                            ->orWhere('user_name', $request->email)->first();
                if ($isEmailExist != null) {
    
                    return response()->json(['message'=> "Email already exists, Try another."], 422);
                }
            }   
            if (!empty($request->phone_no)) {
                if (CommonTrait::isValidPhone($request->phone_no) == FALSE) {
    
                    return response()->json(['message' => "Phone number is not correct,  Phone number should be 10 digit."], 422);
                }
            }        
            $role=5;
            $user_type="sub-admin";   
            $token = 'Bearer' . sha1(time());    
            $employee = new EmployeeModel;
            if ($request->password) 
            {
                $access_code = $request->password;
            } else 
            {
                $access_code = rand(100000, 999999);
            }
            $employee->role_id = $role;
            $employee->first_name = $request->first_name;
            $employee->last_name = $request->last_name;
            $employee->full_name = $request->first_name . ' ' . $request->last_name;
            
            $employee->type = $user_type;
            if (!empty($request->email))
            {
                $employee->email = $request->email;
            }
            $employee->phone_num = preg_replace('~\D~', '', $request->phone_no);
            $employee->access_code = $access_code;
            $employee->password = md5($access_code);
            $employee->api_token = $token;
            $employee->city = $request->city;
            $employee->state = $request->state;
            $employee->address = $request->address;
            $employee->zipcode = $request->zipcode;
            $employee->added_on = date('Y-m-d');
            $employee->save();
            $userAuthToken = $employee->createToken('MyApp')->accessToken;
            $employee->api_token = $userAuthToken;
            $employee_id = $employee->id;  
            if (!empty($request->employee_email)) 
            {
                EmployeeModel::where('id', $employee_id)->update(['user_name' => $request->email]);
            } else if (!empty($request->phone_no)) 
            {
                $phone_num = preg_replace('~\D~', '', $request->phone_no);
                $username = preg_replace('~\D~', '', $request->phone_no);
                $isUsername = EmployeeModel::Where('user_name', $phone_num)->first();
                if ($isUsername != null) 
                {
                    $username = $request->first_name . '_' . $employee_id;
                }
                EmployeeModel::where('id', $employee_id)->update(['user_name' => $username]);
            } else 
            {
                EmployeeModel::where('id', $employee_id)->update(['user_name' => $request->first_name . '_' . $employee_id]);
            }
            if($request->permissions)
            {
                // save permissions of subadmin
                foreach($request->permissions as $permission)
                {
                    $data=[];
                    $module_id=$permission['module_id'];
                    $view=$permission['view']?'v':'';
                    $create=$permission['create']?'c':'';
                    $edit=$permission['edit']?'e':'';
                    $delete=$permission['delete']?'d':'';
                    if($view){
                        array_push($data,$view);
                    }
                    if($create){
                        array_push($data,$create);
                    }
                    if($edit){
                        array_push($data,$edit);
                    }
                    if($delete){
                        array_push($data,$delete);
                    }
                    DB::table('tbl_subadmin_permissions')->insert([
                        'employee_id' => $employee_id,
                        'right_id' => $module_id,
                        'permissions'=> json_encode($data),
                        'created_at' =>Carbon::now('UTC'),
                    ]);
                }
            }
            $employee->message = "Sub-admin created successfully.";
            DB::commit();
            return response()->json($employee, 200);
        } catch (Exception $ex) {
            DB::rollback();
            return response()->json(['message'=> $ex->getMessage()], 422);
        }
    } 

    public function edit($id)
    {
       $subadmin= EmployeeModel::where('id',$id)->first();
       $subAdminPermissions= DB::table('tbl_subadmin_permissions')
       ->select('tbl_rights.right_name','tbl_subadmin_permissions.right_id','tbl_subadmin_permissions.permissions')
       ->leftjoin('tbl_rights','tbl_rights.id','=','tbl_subadmin_permissions.right_id')
       ->where('employee_id',$id)
       ->get();

       $subadmin['permissions']=$subAdminPermissions;
        
       return response()->json($subadmin, 200);

    }
    public function updateSubAdmin(Request $request){
    try{
       $subadmin_id=$request->subadmin_id;
       $access_code=$request->password;
       EmployeeModel::where('id',$subadmin_id)
       ->update([
            'first_name'=>$request->first_name,
            'last_name'=>$request->last_name,
            'email'=>$request->email,
            'phone_num'=>$request->phone_no,
            'address'=>$request->address,
            'city'=>$request->city,
            'state'=>$request->state,
            'zipcode'=>$request->zipcode,
            'access_code' => $access_code,
            'password' => md5($access_code),
         ]); 

        if($request->permissions){
            foreach($request->permissions as $permission)
            {
                $data=[];
                $module_id=$permission['module_id'];
                $view=$permission['view']?'v':'';
                $create=$permission['create']?'c':'';
                $edit=$permission['edit']?'e':'';
                $delete=$permission['delete']?'d':'';
                if($view){
                    array_push($data,$view);
                }
                if($create){
                    array_push($data,$create);
                }
                if($edit){
                    array_push($data,$edit);
                }
                if($delete){
                    array_push($data,$delete);
                }
                DB::table('tbl_subadmin_permissions')->where('employee_id',$subadmin_id)
                ->where('right_id',$module_id)
                ->update(['permissions'=> json_encode($data)]);
            }
        }
       return response()->json(['message'=> 'Employee updated successfully.'], 200);

    } catch (Exception $ex) {
        return response()->json(['message'=> $ex->getMessage()], 422);
    }
    }

    public function subadminRights($type){
        $userId=Auth::user()->id;
        if($type!="All"){
       $details= DB::table('tbl_subadmin_permissions')->select('tbl_rights.right_name','tbl_subadmin_permissions.permissions')->leftjoin('tbl_rights','tbl_subadmin_permissions.right_id','=','tbl_rights.id')
       ->where('employee_id',$userId)->where('tbl_rights.right_name',urldecode($type))->get(); 
        }else{
        $details= DB::table('tbl_subadmin_permissions')->select('tbl_rights.right_name','tbl_subadmin_permissions.permissions')->leftjoin('tbl_rights','tbl_subadmin_permissions.right_id','=','tbl_rights.id')
            ->where('employee_id',$userId)->get(); 
        }
       return response()->json($details, 200);
    }
    
}

?>