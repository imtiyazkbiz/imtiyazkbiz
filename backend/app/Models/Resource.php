<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model {
    protected $table = 'tbl_resources';

    protected $appends = [
        'course',
    ];

    public function getCourseAttribute() {
        return $this->courses()->count();
    }

    public function courses() {
        return $this->belongsToMany(CourseModel::class, 'tbl_course_resource', 'resource_id', 'course_id');
    }

}
