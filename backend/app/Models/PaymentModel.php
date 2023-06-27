<?php

namespace App\Models;

use App\Http\Traits\CommonTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Stripe;

class PaymentModel extends Model {
    use CommonTrait;

    protected $table = "tbl_user_transactions";
    protected $fillable = [
        'user_id',
        'transaction_id',
        'error_code',
        'payment_type',
        'payment_status',
        'amount',
        'courses',
        'invoice_number',
        'response',
        'status',
        'created_at',
        'updated_at',
    ];

    public static function stripePayment($secret_key, $payment_request, $requestPayload = NULL) {
        try {
            log::debug($payment_request);
            if (!isset($payment_request['token']['id'])) {
                return [
                    'status' => 'error',
                    'message' => 'Token is required.',
                ];
            }
            if (!isset($payment_request['transaction_amount'])) {
                return [
                    'status' => 'error',
                    'message' => 'Amount is required.',
                ];
            }
            Stripe\Stripe::setApiKey($secret_key);

            $paymentDescription = '';
            $metaData = [];
            if ($requestPayload != NULL) {
                $paymentDescription = ' User details: First name: ' . $requestPayload->employee_first_name . ', Last name: ' . $requestPayload->employee_last_name . ', Email: ' . $requestPayload->employee_email;
                $metaData = [
                    'First name' => $requestPayload->employee_first_name,
                    'Last name' => $requestPayload->employee_last_name,
                    'Email' => $requestPayload->employee_email,
                    'Phone number' => $requestPayload->employee_phone_num,
                    'Address' => $requestPayload->employee_address,
                    'City' => $requestPayload->employee_city,
                    'State' => $requestPayload->employee_state,
                    'Zip Code' => $requestPayload->employee_zipcode,
                ];
            }


            $response_data = Stripe\Charge::create([
                "amount" => $payment_request['transaction_amount'] * 100,
                "currency" => env('PAYMENT_CURRENCY'),
                "source" => $payment_request['token']['id'],
                "description" => "Home of Training Signup Payment " . $paymentDescription,
                'metadata' => $metaData,
            ]);

            return [
                'status' => 'success',
                'data' => $response_data,
            ];
        } catch (\Stripe\Exception\CardException $e) {
            return [
                'status' => 'error',
                'message' => $e->getError()->message,
            ];
        } catch (\Stripe\Exception\RateLimitException $e) {
            // Too many requests made to the API too quickly
            return [
                'status' => 'error',
                'message' => $e->getError()->message,
            ];
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Invalid parameters were supplied to Stripe's API
            return [
                'status' => 'error',
                'message' => $e->getError()->message,
            ];
        } catch (\Stripe\Exception\AuthenticationException $e) {
            // Authentication with Stripe's API failed
            return [
                'status' => 'error',
                'message' => $e->getError()->message,
            ];
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            // Network communication with Stripe failed
            return [
                'status' => 'error',
                'message' => $e->getError()->message,
            ];
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return [
                'status' => 'error',
                'message' => $e->getError()->message,
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getError()->message,
            ];
            // Something else happened, completely unrelated to Stripe
        }
    }

    public static function stripeCustomer($company_name, $company_email, $secret_key, $customer_request) {
        try {
            log::debug($customer_request);
            if (!isset($customer_request['token']['id'])) {
                return [
                    'status' => 'error',
                    'message' => 'Token is required.',
                ];
            }
            $stripe = new \Stripe\StripeClient($secret_key);
            $response_data = $stripe->customers->create([
                "name" => $company_name,
                "email" => $company_email,
                "source" => $customer_request['token']['id'],
                "description" => "Home of Training Customer Create",
            ]);

            return [
                'status' => 'success',
                'data' => $response_data,
            ];
        } catch (\Stripe\Exception\CardException $e) {
            return [
                'status' => 'error',
                'message' => $e->getError()->message,
            ];
        } catch (\Stripe\Exception\RateLimitException $e) {
            // Too many requests made to the API too quickly
            return [
                'status' => 'error',
                'message' => $e->getError()->message,
            ];
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Invalid parameters were supplied to Stripe's API
            return [
                'status' => 'error',
                'message' => $e->getError()->message,
            ];
        } catch (\Stripe\Exception\AuthenticationException $e) {
            // Authentication with Stripe's API failed
            return [
                'status' => 'error',
                'message' => $e->getError()->message,
            ];
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            // Network communication with Stripe failed
            return [
                'status' => 'error',
                'message' => $e->getError()->message,
            ];
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return [
                'status' => 'error',
                'message' => $e->getError()->message,
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getError()->message,
            ];
            // Something else happened, completely unrelated to Stripe
        }
    }

    public static function getPaymentOfUser($base_url, $profile_id, $profile_key, $data) {
        try {
            $street_address = urlencode($data['cardholder_street_address']);
            $cardholder_zip = urlencode($data['cardholder_zip']);
            $transaction_amount = urlencode($data['transaction_amount']);
            $card_number = urlencode($data['card_number']);
            $card_exp_date = urlencode($data['card_exp_date']);
            $invoice_number = urlencode($data['invoice_number']);
            $payment_url = "$base_url/mes-api/tridentApi?profile_id=$profile_id&profile_key=$profile_key&transaction_type=D&cardholder_street_address=$street_address&cardholder_zip=$cardholder_zip&transaction_amount=$transaction_amount&card_number=$card_number&card_exp_date=$card_exp_date&invoice_number=$invoice_number";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $payment_url,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache",
                    "postman-token: cbeaec44-8764-dd96-1cfb-8122673ef25e",
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {

                return [
                    'status' => 'error',
                    'data' => $err,
                ];
            } else {
                $response_data = CommonTrait::planTextConvertIntoArray($response);

                return [
                    'status' => 'success',
                    'data' => $response_data,
                ];
            }

        } catch (Exception $th) {

            return [
                'status' => 'error',
                'data' => $th->getMessage(),
            ];
        }
    }

    public static function storeCardofUserForPayment($base_url, $profile_id, $profile_key, $data) {
        try {
            if (!isset($data['cardholder_street_address'])) {

                return [
                    'status' => 'error',
                    'data' => 'Cardholder Street Address is required',
                ];
            }
            if (!isset($data['cardholder_zip'])) {

                return [
                    'status' => 'error',
                    'data' => 'Zipcode is required.',
                ];
            }
            if (!isset($data['transaction_amount'])) {

                return [
                    'status' => 'error',
                    'data' => 'Amount is required.',
                ];
            }

            if (!isset($data['card_number'])) {

                return [
                    'status' => 'error',
                    'data' => 'Card number is required.',
                ];
            }

            if (!isset($data['card_exp_date'])) {

                return [
                    'status' => 'error',
                    'data' => 'Card expire date is required.',
                ];
            }
            if (!isset($data['invoice_number'])) {

                return [
                    'status' => 'error',
                    'data' => 'Invoice number is required.',
                ];
            }

            $street_address = urlencode($data['cardholder_street_address']);
            $cardholder_zip = urlencode($data['cardholder_zip']);
            $transaction_amount = urlencode($data['transaction_amount']);
            $card_number = urlencode($data['card_number']);
            $card_exp_date = urlencode($data['card_exp_date']);
            $invoice_number = urlencode($data['invoice_number']);
            $payment_type = $data['payment_type'] == 'monthly' ? 2 : 1;
            $interface = FALSE;
            if (isset($data['interface'])) {
                if ($data['interface'] == 'company_signup') {
                    $interface = TRUE;
                }
            }
            $paymentType = ($payment_type == 2 && $interface) ? '&moto_ecommerce_ind=' . $payment_type : '';

            $payment_url = "$base_url/mes-api/tridentApi?profile_id=$profile_id&profile_key=$profile_key&transaction_type=D&card_number=$card_number&card_exp_date=$card_exp_date&transaction_amount=$transaction_amount&cardholder_street_address=$street_address&cardholder_zip=$cardholder_zip&invoice_number=$invoice_number&store_card=y$paymentType";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $payment_url,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache",
                    "postman-token: a2ee1eb5-a571-53b5-1115-beb8f93cdeaf",
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);


            if ($err) {

                return [
                    'status' => 'error',
                    'data' => $err,
                ];
            } else {
                if ($response == NULL) {

                    return [
                        'status' => 'error',
                        'data' => 'Something is wrong, Please try again.',
                    ];
                }
                $response_data = CommonTrait::planTextConvertIntoArray($response);

                return [
                    'status' => 'success',
                    'data' => $response_data,
                ];
            }
        } catch (Exception $th) {

            return [
                'status' => 'error',
                'data' => $th->getMessage(),
            ];
        }
    }

    public static function userPaymentByStoreCardId($base_url, $profile_id, $profile_key, $data) {
        try {
            $transaction_amount = urlencode($data['transaction_amount']);
            $card_id = urlencode($data['card_id']);
            $invoice_number = urlencode($data['invoice_number']);
            $payment_url = "$base_url/mes-api/tridentApi?profile_id=$profile_id&profile_key=$profile_key&transaction_type=D&transaction_amount=$transaction_amount&invoice_number=$invoice_number&card_id=$card_id";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $payment_url,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache",
                    "postman-token: 4dad5b05-d83f-b86e-cae7-ebf26a28f6b5",
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {

                return [
                    'status' => 'error',
                    'data' => $err,
                ];
            } else {
                $response_data = CommonTrait::planTextConvertIntoArray($response);

                return [
                    'status' => 'success',
                    'data' => $response_data,
                ];
            }
        } catch (Exception $th) {

            return [
                'status' => 'error',
                'data' => $th->getMessage(),
            ];
        }
    }

    public static function userRecurringPayment($base_url, $profile_id, $profile_key, $data) {
        try {
            $card = json_decode($data['response'], TRUE);
            $card_id = $card['data']['card_id'];
            $transaction_amount = $data['amount'];
            $invoice_number = urlencode($data['invoice_number']);
            $recurring_pmt_num = $data['recurring_pmt_num'];
            $recurring_pmt_count = $data['recurring_pmt_count'];
            $payment_url = "$base_url/mes-api/tridentApi?profile_id=$profile_id&profile_key=$profile_key&transaction_type=D&transaction_amount=$transaction_amount&invoice_number=$invoice_number&card_id=$card_id&merchant_initiated=Y&moto_ecommerce_ind=2&recurring_pmt_num=$recurring_pmt_num&recurring_pmt_count=$recurring_pmt_count&echo_invoice_number";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $payment_url,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache",
                    "postman-token: 4dad5b05-d83f-b86e-cae7-ebf26a28f6b5",
                ),
            ));
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {

                return [
                    'status' => 'error',
                    'data' => $err,
                ];
            } else {
                $response_data = CommonTrait::planTextConvertIntoArray($response);

                return [
                    'status' => 'success',
                    'data' => $response_data,
                ];
            }
        } catch (Exception $th) {

            return [
                'status' => 'error',
                'data' => $th->getMessage(),
            ];
        }
    }
}
