<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseFolderModel extends Model
{
    protected $table="tbl_course_folder";

    public function courses()
    {
        return $this->hasMany('App\Models\CourseFolderAssignModel', 'course_folder_id');
       
    }

    public function activeCourses()
    {
        return $this->hasMany('App\Models\CourseFolderAssignModel', 'course_folder_id');
       
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
