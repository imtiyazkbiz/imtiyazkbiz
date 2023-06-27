<?php
namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Auth;


class EmployeeModel extends Model implements AuthenticatableContract, AuthorizableContract
{
    use HasApiTokens, Authenticatable, Authorizable;

    protected $table="tbl_employee";

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role_id', 'first_name', 'last_name', 'full_name', 'user_name', 'job_title_id', 'type', 'email', 'phone_num', 'access_code', 'password',
        'added_on', 'created_by', 'created_at'

    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    

    public function location()
    {
        return $this->belongsToMany('App\Models\CompanyModel', 'tbl_employee_company_locations', 'employee_id', 'company_id');
    }
    public function company()
    {
        return $this->belongsToMany('App\Models\CompanyModel', 'tbl_employee_company_locations', 'employee_id', 'company_id');
    }

    public function courses()
    {
        return $this->belongsToMany('App\Models\CourseModel', 'tbl_employee_courses', 'employee_id', 'course_id');
    }

    public function course_pass()
    {
        return $this->belongsToMany('App\Models\CourseModel', 'tbl_employee_courses', 'employee_id', 'course_id')
            ->where('employee_course_status', 1);
    }

    public function course_fail()
    {
        return $this->belongsToMany('App\Models\CourseModel', 'tbl_employee_courses', 'employee_id', 'course_id')
            ->where('employee_course_status', 0);
    }
    public function course_expired()
    {
        return $this->belongsToMany('App\Models\CourseModel', 'tbl_employee_courses', 'employee_id', 'course_id')
            ->where('employee_course_status', 3);
    }

    public function course_open()
    {
        return $this->belongsToMany('App\Models\CourseModel', 'tbl_employee_courses', 'employee_id', 'course_id')
            ->where('employee_course_status', 2);
    }

    public function courses_due()
    {
        $today = date('Y-m-d');
        $next_date = date('Y-m-d', strtotime('+7 days'));
        return $this->belongsToMany('App\Models\CourseModel', 'tbl_employee_courses', 'employee_id', 'course_id')
            ->whereBetween('employee_course_due_date', [$today, $next_date]);
    }
    public function certificates()
    {
        return $this->hasMany('App\Models\EmployeeCertificateModel', 'employee_id', 'id');
    }
    public function locations()
    {
        return $this->hasMany('App\Models\EmployeeCompanyLocationsModel', 'employee_id', 'id');
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

