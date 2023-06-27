<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadModel extends Model
{  
    protected $table="tbl_leads";

    protected $fillable = [
        'first_name', 'last_name', 'company_name', 'number_of_locations', 'number_of_employees', 'email', 'course_ids', 'status', 'created_at'
    ];
}
