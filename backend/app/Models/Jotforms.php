<?php

namespace App\Models;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Jotforms extends Model
{
    //
    protected $table="jotforms";
    
    protected $fillable = [
        'id', 'formlink', 'company_id', 'employee_id', 'created_at' 

    ];
    public static function assignFormstoCompany($form_ids, $company_id) {
        try {
            foreach ($form_ids as $id) {
                //$UpdateDetails = DB::table('jotforms')->where('id', $id)->first();
                // $allcompanyies = [];
                 //$oldcompany = $UpdateDetails->company_id;
                // $allcompanyies[] = $company_id;

                //$user = DB::table('users')->where('name', 'John')->first();

                //var_dump($UpdateDetails->company_id);
                //$previd = $UpdateDetails->company_id;

                // $allcompanyies = [];
                // foreach ($UpdateDetails as $forms)
                //  {
                //     $allcompanyies[] = $UpdateDetails->company_id;
                //  }
                //  $allcompanyies[] = $company_id;

                // $isCompanyForm = DB::table('jotforms')->where([
                //     'id' => $id
                // ])->first();
                
                //DB::table('jotforms')->where('id', $id->update(['company_id' => $company_id]);

            }
            //return TRUE;
            
         
        } catch (Exception $th) {

            return FALSE;
         }
    }
}
