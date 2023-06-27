<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class ContactUsController extends Controller
{

    /**
     * Construct function
     */
    public function __construct()
    {
        
    }

    public function sendMail(Request $request){
        $name = $request->input('name');
        $email = $request->input('email');
        $subject = $request->input('subject');
        $message = $request->input('message');
        return $message;
    }
        
}
