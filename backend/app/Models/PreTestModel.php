<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PreTestModel extends Model
{
   
    protected $table="tbl_pre_test";
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'course_id', 'allowed_attempts', 'duration', 'instratuction', 'rate', 'number_of_questions', 'status', 'created_at', 'updated_at'
    ];

    public static function preTestQuestion($preTestId, $data) {  
        DB::beginTransaction();
        try {                  
            foreach ($data as $value) {
                $dataInsert = array();
                $questionRules = array();                  
                $dataInsert['pre_test_id'] = $preTestId;
                $dataInsert['question'] = $value['question'];
                $dataInsert['question_type'] = $value['answer_type'];
                $dataInsert['validation'] = (int)$value['checked_validations'];               
                $dataInsert['is_update_employee'] = isset($value['is_update_employee'])?(int)$value['is_update_employee']:0;      
                $dataInsert['status'] = $value['question_status']; 
                $dataInsert['created_at'] = Carbon::now('UTC'); 
                $questionId = DB::table('tbl_pre_test_questions')->insertGetId($dataInsert);
                if ($questionId && isset($value['answers']) && is_array($value['answers']) && !empty($value['answers'])) {
                    $dataAnswer = array();
                    $dataAnswer['pre_test_question_id'] = $questionId;
                    foreach ($value['answers'] as $key => $answerValue) {                      
                        $dataAnswer['answer'] = $answerValue['answer'];
                        $dataAnswer['correct_answer'] = 0;    
                        $dataAnswer['created_at'] = Carbon::now('UTC'); 
                        DB::table('tbl_pre_test_question_answers')->insert($dataAnswer);
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

    public static function updatePreTestQuestion($preTestId, $data) 
    {
        try {
            foreach ($data as $value) {
                $dataUpdate = array();
                $questionRules = array();                
                $dataUpdate['pre_test_id'] = $preTestId;
                $dataUpdate['question'] = $value['question'];
                $dataUpdate['question_type'] = $value['answer_type'];              
                $dataUpdate['is_update_employee'] = isset($value['is_update_employee'])?(int)$value['is_update_employee']:0;
                $dataUpdate['validation'] = (int)$value['checked_validations'];  
                $dataUpdate['status'] = $value['question_status'];
                if (isset($value['id']) && (int)$value['id'] > 0) {
                    $questionUpdate = DB::table('tbl_pre_test_questions')
                        ->where('id', $value['id'])
                        ->update($dataUpdate);
                    if ($questionUpdate && isset($value['answers']) && is_array($value['answers']) && !empty($value['answers'])) {
                        $dataAnswer = array();
                        $dataAnswer['pre_test_question_id'] = $value['id'];
                        foreach ($value['answers'] as $answerValue) {
                            $dataAnswer['answer'] = $answerValue['answer'];
                            $dataAnswer['correct_answer'] = isset($answerValue['correct_answer'])?$answerValue['correct_answer']:0;                      
                            if (isset($answerValue['id']) && (int)$answerValue['id'] > 0) {                               
                                DB::table('tbl_pre_test_question_answers')
                                    ->where('id', $answerValue['id'])
                                    ->update($dataAnswer);
                            } else {                              
                                $dataAnswer['created_at'] = Carbon::now('UTC'); 
                                DB::table('tbl_pre_test_question_answers')->insert($dataAnswer);
                            }
                        }
                    } 
                } else {
                    $dataUpdate['created_at'] = Carbon::now('UTC');
                    $questionId = DB::table('tbl_pre_test_questions')->insertGetId($dataUpdate);
                    if ($questionId && isset($value['answers']) && is_array($value['answers']) && !empty($value['answers'])) {
                        $dataAnswer = array();
                        $dataAnswer['pre_test_question_id'] = $questionId;
                        foreach ($value['answers'] as $key => $answerValue) {                        
                            $dataAnswer['answer'] = $answerValue['answer'];
                            $dataAnswer['correct_answer'] = 0;    
                            $dataAnswer['created_at'] = Carbon::now('UTC'); 
                            DB::table('tbl_pre_test_question_answers')->insert($dataAnswer);
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

    public static function  deletePreTestQuestion($id)
    {
        DB::beginTransaction();
        try {

            $preTestQuestion = DB::table('tbl_pre_test_questions')
                ->where('pre_test_id', $id)
                ->get();

            $questions = $preTestQuestion->toArray();
            $questionsId = array_column($questions, 'id');

            DB::table('tbl_pre_test_question_answers')
                ->whereIn('pre_test_question_id',  $questionsId)
                ->delete();

            DB::table('tbl_pre_test_questions')
                ->where('pre_test_id',  $id)
                ->delete();

            DB::commit();

            return TRUE;
        } catch (Exception $ex) {            
            DB::rollback();
          
            return FALSE;           
        }
    }

    public static function getPreTestQuestion($courseId, $status)
    {
        $result = array();
        $preTest = PreTestModel::where('tbl_pre_test.course_id', $courseId)  
                    ->where('status', 1)                 
                    ->first();
        if ($preTest == null) {

            return [];
        }
        $result['id'] = $preTest->id; 
        $result['course_id'] = $preTest->course_id;       
        $result['name'] = $preTest->name;       
        $result['instruction'] = $preTest->instruction;       
        $result['number_of_questions'] = $preTest->number_of_questions;         
        $result['status'] = $preTest->status;       
        $result['created_at'] = $preTest->created_at;  
        $result['updated_at'] = $preTest->updated_at;  
        $getPreTestQuestion = DB::table('tbl_pre_test_questions')
                    ->where('pre_test_id',  $preTest->id);
                    if ($status != 0) {
                        $getPreTestQuestion ->where('status', $status);   
                    }     
        $result['questions'] = array();             
        if ($getPreTestQuestion->count() > 0) {                   
            foreach ($getPreTestQuestion->get()  as $key => $value) {
                $answers = PreTestModel::preTestQuestionAnswers($value->id);
                $result['questions'][$key] = array(
                    'id' => $value->id,
                    'pre_test_id' => $value->pre_test_id,
                    'question' => $value->question,
                    'question_type' => $value->question_type,
                    'validation' => json_decode($value->validation, true),
                    'is_update_employee' => $value->is_update_employee,
                    'status' => $value->status,
                    'created_at' => $value->created_at,
                    'updated_at' => $value->updated_at,
                    'answers'   => $answers
                );      
            }
        }

        return $result;       
    }

    public static function preTestQuestionAnswers($question_id)
    {
         $answers =  DB::table('tbl_pre_test_question_answers')
                        ->where('pre_test_question_id',  $question_id)                      
                        ->get();      
         $result = array();
         foreach ($answers as $key => $value) {

            $result[$key]['id'] = $value->id;
            $result[$key]['pre_test_question_id'] = $value->pre_test_question_id;
            $result[$key]['answer'] = $value->answer;
            $result[$key]['correct_answer'] = $value->correct_answer;
            $result[$key]['created_at'] = $value->created_at;
            $result[$key]['updated_at'] = $value->updated_at;
         }

         return $result;
    }
}
