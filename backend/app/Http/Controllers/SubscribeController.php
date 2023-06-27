<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\NewsLetterSubscribeModel;

class SubscribeController extends Controller
{
    //
    public function newsLetterSubscribe(Request $request)
    {
        try {
            
            
            $issubscribe = NewsLetterSubscribeModel::where('email_id',$request->subscribeEmail)->first();
            if($issubscribe != null)
            {
                return response()->json(['success' =>  "Email already registered !"], 200);
            }
           
            $subscibe = new NewsLetterSubscribeModel;
            $subscibe->email_id = $request->subscribeEmail;
            $subscibe->save();
            $subscribemail = $request->subscribeEmail;
            $data = array(
                'messagedata' => "Thank you for subscribing!",
            );

            $data_support = array(
                'messagedata' => "Subscribed: ".$request->subscribeEmail,
            );
            
            
            Mail::send('subscribe_mail', $data, function($message) use ($subscribemail) {
                $message->to($subscribemail)->subject(env('SITE_NAME') .'Subscribed');
                $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            });

            Mail::send('subscribe_mail', $data_support, function($message) use ($subscribemail) {
                $message->to(env('MAIL_FROM_ADDRESS'))->subject('Subscribed');
                $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            });
            

            return response()->json(['success' =>  "Subscribed Successfully"], 200);
        } catch (Exception $th) {
           
            return response()->json(['error' =>  $th->getMessage()], 422);
        }


    }
}