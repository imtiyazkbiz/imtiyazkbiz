<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PromoCodeModel;
use App\Models\PromoCodeReportModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Validator;

class PromoCodeController extends Controller
{

    public function getPromoCodes(Request $request)
    {
        try {
            $where_data = [];
            $startFrom = "";
            $limit = "";
            $requestData = $request->all();
            if (isset($requestData['page']) && isset($requestData['per_page'])) {
                $startFrom = ($requestData['page'] == 0) ? ($requestData['page'] * $requestData['per_page']) : ($requestData['page'] - 1) * $requestData['per_page'];
                $limit = $requestData['per_page'];
            }
            if (!empty($request->search)) {
                $search = $request->search;
                $search = explode(" ", $search);
                foreach ($search as $key => $name) {
                    $where_data[] = ['name', 'like', '%' . $name . '%'];
                }
            }
            if (!empty($request->status)) {
                $status = strtolower($request->status);
                if ($status == "inactive") {
                    array_push($where_data, ['status', 0]);
                } elseif ($status == "active") {
                    array_push($where_data, ['status', 1]);
                }
            }
            $result = PromoCodeModel::where($where_data);

            $total = $result->count();
            if ($limit != '') {
                $result->skip($startFrom);
                $result->take($limit);
            }
            $result->orderby('name', 'asc');
            return response()->json(['data' => $result->get(), 'total' => $total], 200);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }
    public function savePromoCode(Request $request)
    {
        try {
            $promocode = new PromoCodeModel();
            $promocode->name = $request->promo_code;
            $promocode->description = $request->description;

            $promocode->percentage = $request->percentage;
            $promocode->validity = $request->valid_upto;
            $promocode->status = $request->status;
            $promocode->created_at = Carbon::now('UTC');
            $promocode->save();
            return response()->json(['data' => []], 200);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }
    public function editPromoCode($id)
    {
        try {
            $result = PromoCodeModel::where('id', $id)->first();
            return response()->json($result, 200);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }

    public function updatePromoCode(Request $request, $id)
    {
        try {
            $result = PromoCodeModel::find($id);
            if (empty($result)) {
                return response()->json(['message' => 'Invalid id no Promo code found.!'], 422);
            }

            PromoCodeModel::where('id', $id)->update([
                'name' => $request->promo_code,
                'description' => $request->description,
                'percentage' => $request->percentage,
                'validity' => $request->valid_upto,
                'status' => $request->status
            ]);

            return response()->json(['message' => 'Promo code updated successfully.'], 200);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $promocode = PromoCodeModel::find($id);
            if (empty($promocode)) {
                return response()->json(['Promocode' => 'Invalid id no promocode found.!'], 422);
            }
            PromoCodeModel::where('id', $id)->update(['status' => $request->status]);
            return response()->json(['Status' => 'Update Successfully..!'], 200);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }

    public function deletePromoCode($id)
    {
        try {
            $promo = PromoCodeModel::find($id);
            if (empty($promo)) {
                return response()->json(['course' => 'Invaild id no course found.!'], 422);
            }
            PromoCodeModel::where('id', $id)->delete();
            return response()->json([], 200);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }

    public function applyPromocode(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'promocode' => 'required',
            ]
        );

        if ($validator->fails()) {
            $errors = json_decode($validator->errors());
            foreach ($errors as $error) {
                $message[] = $error[0];
            }
            $message = implode("<br/>", $message);
            return response()->json(['message' => $message], 422);
        }

        try {
            $today = date('Y-m-d');
            $promocode = $request->promocode;
            $promoExist = PromoCodeModel::where('name', $promocode)
                ->where('status', 1)
                ->where('validity', '>=', $today)
                ->first();

           if($request->amount){
            $amount = $request->amount;
            
            if ($promoExist) {
                $discountAmount = ($amount * $promoExist->percentage) / 100;
                $finalAmount = $amount - $discountAmount;
            } else {
                return response()->json(['message' => 'Invalid Promotional Code.'], 422);
            }
            $data =
                [
                    'previous_amount' => $amount,
                    'final_amount' => $finalAmount,
                    'discounted_amount' => $discountAmount,
                    'discount_percentage' => $promoExist->percentage
                ];
            }else{
                $monthlyAmount = $request->monthlyAmount;
                $yearlyAmount = $request->yearlyAmount;
                if ($promoExist) {
                    $discountMonthlyAmount = ($monthlyAmount * $promoExist->percentage) / 100;
                    $finalMonthlyAmount = $monthlyAmount - $discountMonthlyAmount;
                    $discountYearlyAmount = ($yearlyAmount * $promoExist->percentage) / 100;
                    $finalYearlyAmount = $yearlyAmount - $discountYearlyAmount;
                    $discountLocationAmount = ($request->locationAmount * $promoExist->percentage) / 100;
                    $finalLocationAmount = $request->locationAmount - $discountLocationAmount;
                    $discountUserAmount = ($request->userAmount * $promoExist->percentage) / 100;
                    $finalUserAmount = $request->userAmount - $discountUserAmount;
                } else {
                    return response()->json(['message' => 'Invalid Promotional Code.'], 422);
                }
                $data =
                    [
                        'previous_amount_monthly' => $monthlyAmount,
                        'previous_amount_yearly' => $yearlyAmount,
                        'final_amount_monthly' => $finalMonthlyAmount,
                        'final_amount_yearly' => $finalYearlyAmount,
                        'discounted_monthly_amount' => $discountMonthlyAmount,
                        'discounted_yearly_amount' => $discountYearlyAmount,
                        'discount_percentage' => $promoExist->percentage,
                        'final_location_amount' => $finalLocationAmount,
                        'final_user_amount' => $finalUserAmount,
                    ];
            }
            return response()->json($data, 200);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }

    public function getPromoCodeReports(Request $request){

        try {
            $columnName = [0 => 'tbl_employee.first_name', 1 => 'tbl_employee.last_name'];
            $where_data = [];
            $startFrom = "";
            $limit = "";
            $requestData = $request->all();
            if (isset($requestData['page']) && isset($requestData['per_page'])) {
                $startFrom = ($requestData['page'] == 0) ? ($requestData['page'] * $requestData['per_page']) : ($requestData['page'] - 1) * $requestData['per_page'];
                $limit = $requestData['per_page'];
            }
            $orderBy = '';
            $orderColumn = "";
            if (isset($requestData['order']) && isset($requestData['column']))  {
                $orderBy = $request->order;            
                $orderColumn = ($request->column < 3)?$columnName[$request->column]:'tbl_employee.first_name';
            }
            if (!empty($request->search)) {
                $search = $request->search;
                $search = explode(" ", $search);
                foreach ($search as $key => $name) {
                    $where_data[] = ['tbl_promo_codes.name', 'like', '%' . $name . '%'];
                }
            }
            $result = PromoCodeReportModel::select('tbl_employee.first_name','tbl_employee.last_name','tbl_promo_codes.name as promocode','tbl_promo_code_report.*')
                                          ->leftjoin('tbl_employee','tbl_employee.id','=','tbl_promo_code_report.user_id')
                                          ->leftjoin('tbl_promo_codes','tbl_promo_codes.id','=','tbl_promo_code_report.promocode_id')
                                          ->where($where_data);

            $total = $result->count();
            if ($orderColumn != '' && $orderBy != '') {
                $result->orderBy($orderColumn,  $orderBy);
            }
            if ($limit != '') {
                $result->skip($startFrom);
                $result->take($limit);
            }

            $getSheet = array();
            foreach ($result->get() as $key => $value) {
                $getSheet[$key]['First Name'] = ucfirst($value->first_name);
                $getSheet[$key]['Last Name'] = ucfirst($value->last_name);
                $getSheet[$key]['Promo Code'] = $value->promocode;
                $getSheet[$key]['Course Cost'] = $value->total_amount;
                $getSheet[$key]['Paid Cost'] = $value->amount_paid;
            }


            return response()->json(['data' => $result->get(), 'download' => $getSheet, 'total' => $total], 200);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 422);
        }

    }
}
