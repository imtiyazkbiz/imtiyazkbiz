<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\CourseModel;
use App\Models\SurveyModel;
use App\Models\EmployeeSurveyModel;
use App\Models\EmployeeCompanyLocationsModel;
use App\Models\CompanySurveyModel;
use Illuminate\Support\Facades\Log;
use App\Http\Traits\CommonTrait;
use App\User;
use Validator;
use Illuminate\Support\Facades\DB;
use Auth;
class SurveyController extends Controller
{
    private $status;
    private $sucess;
    private $fail;
    private $survey_type;

    /**
     * Construct function
     */
    public function __construct()
    {
        $this->status = config('constant.status');
        $this->sucess = config('constant.success');
        $this->fail = config('constant.fail');
        $this->survey_type = config('constant.survey_type');
    }
    
    public function createSurvey(Request $request) 
    {
        $validator = Validator::make(
            $request->all(), [
                    'name' => 'required',
                    'instructions' => 'required',                                  
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
            $dataSurvey = array(              
                'name' =>  $request->name,              
                'instruction' =>  $request->instructions,            
                'number_of_questions' =>  0,
                'survey_type' =>  $request->type,
                'created_at' =>  Carbon::now('UTC')              
            );
            $surveyId = SurveyModel::insertGetId($dataSurvey);
            if($request->course_id){
            DB::table('tbl_course_survey')->insertGetId([
                'course_id' => $request->course_id,
                'survey_id' => $surveyId,
                'created_at' =>  Carbon::now('UTC')
            ]); 
            }   
            if($request->type=='post-login'){
                $user = Auth::user();
                $companies = EmployeeCompanyLocationsModel::where('employee_id',$user->id)->first();
                $company_id=$companies->company_id;
                $dataCompanySurvey = array(              
                    'company_id' =>  $company_id,              
                    'survey_id' =>  $surveyId,            
                    'created_at' =>  Carbon::now('UTC')              
                );
                DB::table('tbl_company_survey')->insertGetId($dataCompanySurvey);
            }      
            if ($surveyId) {
                if (is_array($request->surveytest_questions) && !empty($request->surveytest_questions)) {
                    $insertQuestionAnswer = SurveyModel::saveSurveyQuestion($surveyId, $request->surveytest_questions, $request->type);
                   
                    if ($insertQuestionAnswer) {

                        return response()->json(['message' => 'Survey created successfully.'], 200);    
                    }

                    return response()->json(['message' => 'Something is wrong, try again'], 422);    
                }

                return response()->json(['message' => 'Survey created successfully.'], 200);     
            } else {

                return response()->json(['message' => 'Something is wrong, try again'], 422);
            }
                           
        } catch (Exception $ex) {

            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }

    public function editSurvey(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(), [
                'course_id' => 'required',
                'name' => 'required',
                'instructions' => 'required',                    
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
            $dataSurvey = array(                
                'name' =>  $request->name,
                'instruction' =>  $request->instructions,               
                'number_of_questions' =>  isset($request->number_of_questions) ?$request->number_of_questions:0
            );
            $update = SurveyModel::where('id', $id)->update($dataSurvey);
            DB::table('tbl_course_survey')->where('survey_id', $id)->update(['course_id' => $request->course_id]);   
            SurveyModel::updateSurveyQuestion($id, $request->surveytest_questions);
            
            return response()->json(['message' => 'Survey updated successfully.'], 200);    
                            
        } catch (Exception $ex) {

            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }

    public function getSurvey()
    {
        $data = SurveyModel::all()->where('status', 1);

        return response()->json($data, 200);
    }

    public function deleteSurvey($id,$course_id) {        
        //$deleteSurvey = SurveyModel::where('id', $id)->update(['status' => 0]);
        $deleteSurvey = DB::table('tbl_course_survey')->where(['course_id' => $course_id, 'survey_id' => $id])->delete();
        if ($deleteSurvey) {

            return response()->json(['message' => 'Survey deleted successfully.'], 200); 
        }

        return response()->json(['message' => 'Survey has not been deleted, Please try again.'], 422); 
    }
    
    public function getPostLoginSurvey(){
        $user = Auth::user();
        $companies = EmployeeCompanyLocationsModel::where('employee_id',$user->id)->first();
        $company_id=$companies->company_id;
        $data = SurveyModel::withCount('employeeSurvey')->leftjoin('tbl_company_survey','tbl_company_survey.survey_id','=','tbl_survey.id')->where('status', 1)->where('tbl_company_survey.company_id', $company_id)->where('survey_type', $this->survey_type['post_login_survey'])->get(); 
        return response()->json($data, 200);
    }
    public function deletePostLoginSurvey(Request $request){
        SurveyModel::where('id',$request->id)->delete();
        DB::table('tbl_survey_questions')->where('survey_id',$request->id)->delete();
        EmployeeSurveyModel::where('survey_id',$request->id)->delete();
        CompanySurveyModel::where('survey_id',$request->id)->delete();
    }
   public function getPostLoginSurveyEmployees($id){
    $data = EmployeeSurveyModel::select('tbl_employee.id','tbl_employee.first_name','tbl_employee.last_name')->leftjoin('tbl_employee','tbl_employee.id','=','tbl_employee_survey.employee_id')->where('status', 1)->where('survey_id', $id)->get(); 
    return response()->json($data, 200);
   }
   public function getPostLoginSurveySubmissions($survey_id,$employee_id){
      
    $data = DB::table('tbl_employee_post_survey_submissions')->where('employee_id',$employee_id )->where('survey_id', $survey_id)->get(); 
    return response()->json($data, 200);
   }
   public function editPostLoginSurvey($id){
    $result = array();
    $getSurveyDetail=SurveyModel::where('id',$id)->first();
    $result['id'] = $getSurveyDetail->id;      
    $result['name'] = $getSurveyDetail->name;       
    $result['instruction'] = $getSurveyDetail->instruction;        
    $result['status'] = $getSurveyDetail->status;       
    $result['created_at'] = $getSurveyDetail->created_at;  
    $result['updated_at'] = $getSurveyDetail->updated_at;  
    $getSurveyTestQuestion = DB::table('tbl_survey_questions')
    ->where('survey_id',  $id); 
      
    $result['questions'] = array();     
    if ($getSurveyTestQuestion->count() > 0) {                    
        foreach ($getSurveyTestQuestion->get() as $questionKey => $value) {
            $answers = SurveyModel::surveyQuestionAnswers($value->id);
            $result['questions'][$questionKey] = array(
                'id' => $value->id,
                'survey_id' => $value->survey_id,
                'question' => $value->question,
                'question_type' => $value->question_type,
                'validation' => $value->validation,                          
                'status' => $value->status,
                'created_at' => $value->created_at,
                'updated_at' => $value->updated_at,
                'answers'   => $answers
            );      
        }
    }
    return response()->json($result, 200);
   }
  public function updatePostLoginSurvey(Request $request){
    $validator = Validator::make(
        $request->all(), [
            'name' => 'required',
            'instructions' => 'required',                    
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
        $dataSurvey = array(                
            'name' =>  $request->name,
            'instruction' =>  $request->instructions,               
        );
        $update = SurveyModel::where('id', $request->id)->update($dataSurvey);
        SurveyModel::updateSurveyQuestion($request->id, $request->surveytest_questions);
        
        return response()->json(['message' => 'Survey updated successfully.'], 200);    
                        
    } catch (Exception $ex) {

        return response()->json(['message' => $ex->getMessage()], 422);
    }
  }
  public function assignPostLoginSurvey(Request $request){
    $user = Auth::user();
    $companies = EmployeeCompanyLocationsModel::where('employee_id',$user->id)->first();
    $company_id=$companies->company_id;
    DB::table('tbl_company_survey')->where('company_id',$company_id)->where('survey_id',$request->survey_id)->update(['for_admins'=>$request->for_admins,'for_managers'=>$request->for_managers,'for_employees'=>$request->for_employees]);
   
    $existingassignedData=EmployeeSurveyModel::select('employee_id')->where('survey_id',$request->survey_id)->get();

    $exisitngEmployees=[];
    foreach($existingassignedData as $existingassignedDatas){
       array_push($exisitngEmployees, $existingassignedDatas->employee_id);
    }
    $newAdded=array_diff($request->employee_ids, $exisitngEmployees);
    $removedEmployees=array_diff($exisitngEmployees, $request->employee_ids);
    if($newAdded){
        foreach($newAdded as $employee_id){
        $employeeSurvey = new EmployeeSurveyModel();
        $employeeSurvey->employee_id=$employee_id;
        $employeeSurvey->survey_id=$request->survey_id;
        $employeeSurvey->save();
        }
    }
    if($removedEmployees){
        EmployeeSurveyModel::where('survey_id',$request->survey_id)->whereIn('employee_id',$removedEmployees)->delete(); 
      }
    return response()->json(['message' => 'Survey Assigned successfully.'], 200);
  }

  public function getAssignedPostSurveyEmployees($id){
    $user = Auth::user();
    $companies = EmployeeCompanyLocationsModel::where('employee_id',$user->id)->first();
    $company_id=$companies->company_id;
    $companySurvey=CompanySurveyModel::where('company_id',$company_id)->where('survey_id',$id)->first();
    
    $companySurvey['employees']=EmployeeSurveyModel::leftjoin('tbl_employee_company_locations','tbl_employee_company_locations.employee_id','=','tbl_employee_survey.employee_id')
                                ->leftjoin('tbl_employee','tbl_employee.id','=','tbl_employee_company_locations.employee_id')
                                ->where('survey_id',$id)
                                ->where('tbl_employee_company_locations.company_id',$company_id)
                                ->select('tbl_employee.id','tbl_employee.first_name','tbl_employee.last_name')
                                ->get();

                                return response()->json([$companySurvey], 200);
}

public function getEmployeePostLoginSurvey(){
    $user = Auth::user();
    $employee_id=$user->id;
    
    if($user->role_id==4){
        $getSurveys = SurveyModel::
        whereIn('tbl_survey.id', function($query) use ($employee_id) {
            $query->select('survey_id')->from('tbl_employee_survey')->where('employee_id',$employee_id);
        })->whereNotIn('tbl_survey.id',DB::table('tbl_employee_post_survey_submissions')
        ->where('employee_id',$employee_id)->select('tbl_employee_post_survey_submissions.survey_id'))
        ->limit(1)->get();
    }else{
        if($user->role_id==2){
          $role="for_admins";
        }
        if($user->role_id==3){
          $role="for_managers";
        }
        $companies = EmployeeCompanyLocationsModel::where('employee_id',$user->id)->first();
        if($companies){
        $company_id=$companies->company_id;
        $getSurveys = SurveyModel::
        whereIn('tbl_survey.id', function($query) use ($company_id,$role,$employee_id) {
            $query->select('survey_id')->from('tbl_company_survey')->where('company_id',$company_id)->where($role,1);
        })->whereNotIn('tbl_survey.id',DB::table('tbl_employee_post_survey_submissions')
        ->where('tbl_employee_post_survey_submissions.employee_id',$employee_id)
        ->select('tbl_employee_post_survey_submissions.survey_id'))
        ->limit(1)->get();
        }

    }
    if ($getSurveys->count() > 0) {          
        foreach ($getSurveys as $key => $survey) {
            $result[$key]['id'] = $survey->id;       
            $result[$key]['name'] = $survey->name;       
            $result[$key]['instruction'] = $survey->instruction;             
            $getSurveyTestQuestion = DB::table('tbl_survey_questions')
            ->where('survey_id',  $survey->id);   
            $result[$key]['questions'] = array();     
            if ($getSurveyTestQuestion->count() > 0) {                    
                foreach ($getSurveyTestQuestion->get() as $questionKey => $value) {
                    $answers = SurveyModel::surveyQuestionAnswers($value->id);
                    $result[$key]['questions'][$questionKey] = array(
                        'id' => $value->id,
                        'survey_id' => $value->survey_id,
                        'question' => $value->question,
                        'question_type' => $value->question_type,
                        'validation' => $value->validation,                          
                        'status' => $value->status,
                        'created_at' => $value->created_at,
                        'updated_at' => $value->updated_at,
                        'answers'   => $answers
                    );      
                }
            }
        }
               
        return response()->json($result, 200);
    }
  
}

public function postLoginSurveySubmissions(Request $request){
    $user = Auth::user();
    $user_id=$user->id;
    $question_answers = array();  
    $questionIds = array();
    $answersForPretest = array();
    $total_question = count($request->questions);
    foreach ($request->questions as $key => $value) {
        $answer_id = 0;
        $answer_title = 0;
        if (isset($value['answer_id'])) {
            $answer_id = $value['answer_id'];
        }
        if (isset($value['answer'])) {
            $answer_title = $value['answer'];
        }
         
            $get_validation_data = DB::table('tbl_survey_questions')->where('survey_id', $request->test_id)
                ->where('id', $value['question_id'])->first();
      

        if ($get_validation_data->validation == 1) {
            if ($answer_title == '') {
                return response()->json(['message' => "Phone number is required."], 422);
                exit;  
            }
            if (!empty($answer_title)) {
                if (CommonTrait::isValidPhone($answer_title) == FALSE) {                
                    return response()->json(['message' => "Phone number is not correct,  Phone number should be 10 digit."], 422);
                    exit;
                }
            }  
          
        }
        if ($get_validation_data->validation == 2) {
            if ($answer_title == '') {
                return response()->json(['message' => "Email is required."], 422);
                exit;  
            }
            if (CommonTrait::isValidEmail($answer_title) == FALSE) {            
                return response()->json(['message'=> "Email is not valid, Enter correct email."], 422);
                exit;
            }

        }
        if ($get_validation_data->validation == 3 && $answer_title == '') {                   
                return response()->json(['message'=> "Text is required."], 422);
                exit;
        }
        if ($get_validation_data->validation == 4 && $answer_title == '') {                   
            return response()->json(['message'=> "Date is required."], 422);
            exit;
        }
        if ($get_validation_data->validation == 5 && $answer_title == '') {   
            return response()->json(['message'=> "SSN is required."], 422);
            exit;
        }
      
        $question_answers[$key]['question_id'] = $value['question_id'];
        $question_answers[$key]['answer_id'] = $answer_id;
        $question_answers[$key]['employee_id'] = $user_id;
        $question_answers[$key]['survey_id'] = $request->test_id;
        $question_answers[$key]['question'] = $value['question'];                       
        $question_answers[$key]['answer'] = $answer_title;  
        $question_answers[$key]['created_at'] = Carbon::now('UTC');  
    }                    
    $insert = DB::table('tbl_employee_post_survey_submissions')->insert($question_answers);  
    return response()->json(['message' => 'Survey submit successfully.'], 200);
}


}
