<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseResourceModel extends Model
{
    protected $table="tbl_course_resource";

    public function courseInfo()
    {
        return $this->hasOne('App\Models\CourseModel','id','course_id'); 
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
