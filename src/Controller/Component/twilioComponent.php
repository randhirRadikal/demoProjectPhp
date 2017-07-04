<?php

/*
 * Author: Ravi S. Singh
 * Date : 11-30-2016
 * Dependency : Twilio PHP SDK used : composer require twilio/sdk
 *
 */

namespace App\Controller\Component;

use Cake\Controller\Component;
use Twilio\Rest\Client;
class twilioComponent extends Component {
    /*
     * Constructor function to set Braintree Configuration
     */

    public function __construct() {
        
    }

    public function sendSms($to,$message){
        try {
            $this->client->account->messages->create(
                    $to,[
                        'from'=>$this->from,
                        'body'=>$message
                    ]
                );
            return TRUE;
        } catch (\Twilio\Exceptions\RestException $ex) {
            //pr($ex->getMessage());
            return FALSE;
        }
    }

    public function lookUp($phone){
        $res=[];
        try{
            $number = $this->client->lookups->phoneNumbers($phone)->fetch([
                'type'=>'carrier'
            ]);
            if($number){
                $res['countryCode']=$number->countryCode;
                $res['phoneNumber']=$number->phoneNumber;
                $res['carrier']=$number->carrier;
            }
        } catch (\Twilio\Exceptions\RestException $ex) {
            //pr($ex->getMessage());
            return FALSE;
        }

        return $res;
    }


}
