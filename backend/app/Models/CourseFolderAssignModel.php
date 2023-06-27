<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseFolderAssignModel extends Model
{
    protected $table="tbl_course_coursefolder";

    public function courses()
    {
        return $this->hasOne('App\Models\CourseModel','id','course_id'); 
    }

    public function activeCourses()
    {
        return $this->hasOne('App\Models\CourseModel','id','course_id')  
                ->with('courses')
                ->where(['in_store' => 1, 'status' => 1])
                ->where('tbl_course.company_specific', '>', 0)
                ->where('company_specific', '>', 0);; 
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
