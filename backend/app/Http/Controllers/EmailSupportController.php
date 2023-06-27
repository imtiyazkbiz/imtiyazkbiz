<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailSupportController extends Controller
{
    public function sendQuerySupport(Request $request)
    {
        try {
            $personName = $request->pesonName;
            $personEmail = $request->pesonEmail;
            $personQuery = $request->pesonQuery;
            $data = array(
                'personName' => $personName,
                'personEmail' => $personEmail,
                'personQuery' => $personQuery,
            );
            $mailSupport = env('MAIL_SUPPORT');
            Mail::send('support_mail', $data, function($message) use ($mailSupport) {
                $message->to($mailSupport)->subject(env('SITE_NAME') .' Email Support');
                $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            });
            return response()->json(['success' =>  "Support Email Sent Successfully."], 200);
        } catch (Exception $th) {
            return response()->json(['error' =>  $th->getMessage()], 422);
        }
    }
}
