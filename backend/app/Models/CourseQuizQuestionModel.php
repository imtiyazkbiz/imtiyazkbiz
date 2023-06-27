<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseQuizQuestionModel extends Model
{
    protected $table="tbl_course_quiz_questions";

    public function lessonAnswers()
    {
        return $this->hasMany('App\Models\CourseQuizQuestionAnswerModel', 'course_quiz_question_id');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */

}
