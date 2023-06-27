<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseLessonModel extends Model
{
    protected $table="tbl_course_lesson";

    public function gamificationData()
    {
        return $this->hasMany('App\Models\CourseLessonGamificationModel', 'lesson_id');
    }
    public function questions()
    {
        return $this->hasMany('App\Models\CourseQuizQuestionModel', 'parent_id')->where('parent','lesson');
    }

    public function answers()
    {
        return $this->hasManyThrough(
            'App\Models\CourseQuizQuestionAnswerModel',
            'App\Models\CourseQuizQuestionModel',
            'parent_id', // Foreign key on users table...
            'course_quiz_question_id', // Foreign key on posts table...
            'id', // Local key on countries table...
            'id' // Local key on users table...
        );
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
