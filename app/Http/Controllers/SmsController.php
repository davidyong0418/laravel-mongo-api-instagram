<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
// use GuzzleHttp\Client;

// https://www.bulksmsnigeria.net/

class SmsController extends Controller
{
    public function sendMessage()
    {

    $sid    = "";
    $token  = "";
    $twilio = new Client($sid, $token);

    $message = $twilio->messages
                    ->create("", // to
                            array("from" => "+", "body" => "body")
                    );

    print($message->sid);
    return $message;

  }

}