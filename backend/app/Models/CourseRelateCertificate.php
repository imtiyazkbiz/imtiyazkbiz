<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseRelateCertificate extends Model
{
    protected $table="tbl_course_certificate";


    public function certificates()
    {
        return $this->belongsToMany('App\Models\CourseModel','tbl_course_certificate', 'certificate_id','course_id');

    }
    public function resources()
    {
        return $this->hasMany('App\Models\CourseResourceModel', 'course_id');
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
