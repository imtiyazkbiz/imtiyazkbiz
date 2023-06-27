<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseTestModel extends Model
{
    protected $table="tbl_course_test";


    public function questions()
    {
        return $this->hasMany('App\Models\CourseQuizQuestionModel', 'parent_id')->where('parent','test');
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
