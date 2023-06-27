<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HRFormModel extends Model
{
    protected $table="tbl_hr_form";


    public function assigned_employees()
    {
        return $this->hasMany('App\Models\HRFormAssignmentModel', 'hrform_id');
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
