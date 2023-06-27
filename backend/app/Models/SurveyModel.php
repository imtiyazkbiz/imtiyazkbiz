<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\CourseModel;

class SurveyModel extends Model
{
    protected $table="tbl_survey";

    protected $fillable = [
        'name', 'instruction', 'number_of_questions', 'status', 'status', 'updated_at'
    ];
    public function employeeSurvey()
    {
        return $this->hasMany('App\Models\EmployeeSurveyModel', 'survey_id');
    }
    public static function saveSurveyQuestion($surveyId, $data,$type) {          
        DB::beginTransaction();        
        try {                  
            foreach ($data as $value) {
                $dataInsert = array();                       
                $dataInsert['survey_id'] = $surveyId;
                $dataInsert['question'] = $value['question'];
                $dataInsert['question_type'] = $value['answer_type'];
                $dataInsert['validation'] = (int)$value['checked_validations'];
                $dataInsert['survey_type'] = $type;
                $dataInsert['created_at'] = Carbon::now('UTC');                 
                $questionId = DB::table('tbl_survey_questions')->insertGetId($dataInsert);              
                if ($questionId && isset($value['answers']) && is_array($value['answers']) && !empty($value['answers'])) {
                    $dataAnswer = array();
                    $dataAnswer['survey_question_id'] = $questionId;
                    foreach ($value['answers'] as $answerValue) {
                        $dataAnswer['answer'] = $answerValue['answer'];
                        $dataAnswer['correct_answer'] = isset($answerValue['correct_answer'])?$answerValue['correct_answer']:0;    
                        $dataAnswer['survey_type']= $type;
                        $dataAnswer['created_at'] = Carbon::now('UTC'); 
                        DB::table('tbl_survey_question_answers')->insert($dataAnswer);
                    }
                }                  
            }
            DB::commit();

            return TRUE;
        } catch  (Exception $ex) {
            DB::rollback();
           
            return FALSE;
        }   
    }

    public static function updateSurveyQuestion($surveyId, $data) 
    { 
        DB::beginTransaction();

      
        try {                                 
            foreach ($data as $value) {
             
                $dataUpdate = array();
                $questionRules = array();
                $questionRules = $value['checked_validations'];     
                $dataUpdate['survey_id'] = $surveyId;
                $dataUpdate['question'] = $value['question'];
                $dataUpdate['question_type'] = $value['answer_type'];
                $dataUpdate['validation'] = (int)$questionRules;  
                $dataUpdate['status'] = ($value['question_status'] == false)?0:1;  
                          
                if (isset($value['id']) && (int)$value['id'] > 0) {
                    $questionUpdate = DB::table('tbl_survey_questions')
                        ->where('id', $value['id'])
                        ->update($dataUpdate);
                    if (isset($value['answers']) && is_array($value['answers']) && !empty($value['answers'])) {
                        $dataAnswer = array();
                        $dataAnswer['survey_question_id'] = $value['id'];
                        foreach ($value['answers'] as $answerValue) {                     
                            $dataAnswer['answer'] = $answerValue['answer'];
                            $dataAnswer['correct_answer'] = isset($answerValue['correct_answer'])?$answerValue['correct_answer']:0;   
                            if (isset($answerValue['id']) && (int)$answerValue['id'] > 0) {
                                DB::table('tbl_survey_question_answers')
                                ->where('id', $answerValue['id'])
                                ->update($dataAnswer);
                            } else {
                                $dataAnswer['created_at'] = Carbon::now('UTC'); 
                                DB::table('tbl_survey_question_answers')->insert($dataAnswer);
                            }
                            
                        }
                    }
                } else {                  
                    $dataUpdate['created_at'] = Carbon::now('UTC');                 
                    $questionId = DB::table('tbl_survey_questions')->insertGetId($dataUpdate);    
                            
                    if ($questionId && isset($value['answers']) && is_array($value['answers']) && !empty($value['answers'])) {
                        $dataAnswer = array();
                        $dataAnswer['survey_question_id'] = $questionId;
                        foreach ($value['answers'] as $answerValue) {
                            $dataAnswer['answer'] = $answerValue['answer'];
                            $dataAnswer['correct_answer'] = isset($answerValue['correct_answer'])?$answerValue['correct_answer']:0;    
                            $dataAnswer['created_at'] = Carbon::now('UTC'); 
                            DB::table('tbl_survey_question_answers')->insert($dataAnswer);
                        }
                    }  
                }
            }

            DB::commit();

            return TRUE;
        } catch (Exception $ex) {
            DB::rollback();
        
            return FALSE;
        }
    }
    public static function getSurveyPreTestQuestion($courseId, $status)
    {
        $result = array();
        $getSurveys = CourseModel::leftJoin('tbl_course_survey', 'tbl_course_survey.course_id', '=', 'tbl_course.id')
            ->rightJoin('tbl_survey', 'tbl_survey.id', '=', 'tbl_course_survey.survey_id')
            ->where('tbl_course.id', $courseId)
            ->where('tbl_survey.status', 1)            
            ->select('tbl_survey.*')
            ->get();
         
        if ($getSurveys->count() > 0) {          
            foreach ($getSurveys as $key => $survey) {
                $result[$key]['id'] = $survey->id; 
                $result[$key]['course_id'] = $courseId;       
                $result[$key]['name'] = $survey->name;       
                $result[$key]['instruction'] = $survey->instruction;       
                $result[$key]['number_of_questions'] = $survey->number_of_questions;  
                $result[$key]['status'] = $survey->status;       
                $result[$key]['created_at'] = $survey->created_at;  
                $result[$key]['updated_at'] = $survey->updated_at;  
                $getSurveyTestQuestion = DB::table('tbl_survey_questions')
                ->where('survey_id',  $survey->id);
                if ($status == 1) {
                    $getSurveyTestQuestion->where('status', 1);  
                }    
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
                   
            return $result;
        }

        return [];
    }

    public static function surveyQuestionAnswers($question_id)
    {
        $answers =  DB::table('tbl_survey_question_answers')
                    ->where('survey_question_id',  $question_id)
                    ->get();     
        $result = array();
        foreach ($answers as $key => $value) {
            $result[$key]['id'] = $value->id;
            $result[$key]['survey_question_id'] = $value->survey_question_id;
            $result[$key]['answer'] = $value->answer;     
            $result[$key]['created_at'] = $value->created_at;
            $result[$key]['updated_at'] = $value->updated_at;
        }

        return $result;
    }
}

