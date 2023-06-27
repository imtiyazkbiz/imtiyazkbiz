<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeCoursesModel extends Model
{
    protected $table="tbl_employee_courses";

    public function employee()
    {
        return $this->hasOne('App\Models\EmployeeModel','id','employee_id');
    }
    public function company()
    {
        return $this->hasOne('App\Models\CompanyModel','id','company_id');
    }
    public function course()
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
