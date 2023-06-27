<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeCertificateModel extends Model {
    protected $table = "tbl_employee_certificates";

    /**
     * The attributes that are mass assignable.
     * @var array
     */

    /**
     * The attributes excluded from the model's JSON form.
     * @var array
     */

    public function course() {
        return $this->hasOne('App\Models\CourseModel', 'id', 'course_id');
    }

    public function coursefolder() {
        return $this->hasOne('App\Models\CourseFolderModel', 'id', 'course_id');
    }

}
