<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyCoursesModel extends Model
{
    protected $table="tbl_company_courses";
    protected $fillable = ['company_id','course_id'];

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

    public static function getCompanyOfCourse($id)
    {
        return CompanyCoursesModel::leftJoin('tbl_company', 'tbl_company.id', '=', 'tbl_company_courses.company_id')
                                            ->where('tbl_company_courses.course_id', $id)
                                            ->select('tbl_company.*')
                                            ->get();
    }

}
