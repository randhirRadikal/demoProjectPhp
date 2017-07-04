<?php

/*
 * Author: Ravi S. Singh
 * Date : 01-04-2016
 * Dependency : Braintree PHP API
 *
 */

namespace App\Controller\Component;

use Cake\Controller\Component;
use Braintree;

class btComponent extends Component {
    /*
     * Constructor function to set Braintree Configuration
     */

    public function __construct() {
        
    }

    /*
     * The function to generate Braintree Token for Client
     */

    public function generate_client() {
        $client_token = \Braintree_ClientToken::generate();
        return $client_token;
    }

    /*
     * This function accepts amaount and client nounce for direct sale
     */

    public function sale($amount, $nonce) {
        $result = \Braintree_Transaction::sale([
                    'amount' => $amount,
                    'paymentMethodNonce' => $nonce,
                    'options' => [
                        'submitForSettlement' => True
                    ]
        ]);

        return $result;
    }

    /*
     * This function accepts the funnd in sub merchant account with service fees & direc sale
     */
    public function saleForMerchant($merchantAccountId, $amount, $serviceFee, $nounce) {
        $result = \Braintree_Transaction::sale([
                    'merchantAccountId' => $merchantAccountId,
                    'amount' => $amount,
                    'paymentMethodNonce' => $nounce,
                    'serviceFeeAmount' => $serviceFee,
                    'options' => [
                        'submitForSettlement' => True
                    ]
        ]);
        return $result;
    }

    /*
     * This function accepts the funnd in sub merchant account with service fees & hold in Escrow
     */
    public function escrowForMerchant($merchantAccountId, $amount, $serviceFee, $nounce) {
        $res=[];
        $result = \Braintree_Transaction::sale([
                    'merchantAccountId' => $merchantAccountId,
                    'amount' => $amount,
                    'paymentMethodNonce' => $nounce,
                    'serviceFeeAmount' => $serviceFee,
                    'options' => [
                        'submitForSettlement' => True,
                        'holdInEscrow' => true
                    ]
        ]);
        //pr($result);
        if($result->success){
            $res['success']=TRUE;
            $res['transaction_id']=$result->transaction->id;
            $res['status']=$result->transaction->status;
            $res['amount']=$result->transaction->amount;
            $res['merchant_account_id']=$result->transaction->merchantAccountId;
            $res['service_fee_amount']=$result->transaction->serviceFeeAmount;
            $res['escrow_status']=$result->transaction->escrowStatus;
        }else{
            $res['success']=FALSE;
            foreach($result->errors->deepAll() as $error){
                $res['error']=$error->message;
            }
        }
        return $res;
    }

    /*
     * This function accepts transaction id and release the funds from escrow
     */
    public function releaseFromEscrow($transaction_id){
        $res=[];
        try{
            $result = \Braintree_Transaction::releaseFromEscrow($transaction_id);
            if($result->success){
                $res['success']=TRUE;
                //$res['escrowStatus']=$result->escrowStatus;
            }else{
                $res['success']=FALSE;
                foreach($result->errors->deepAll() as $error){
                    $res['error']=$error->message;
                }
                return $res;
            }

        }  catch (\Braintree\Exception\NotFound $e){
            $res['success']=FALSE;
            $res['error']="Invalid Transaction Id";
        }
        return $res;
    }

    //function to void an unsattled transaction
    public function transactionVoid($transaction_id){
        $res=[];
        try{
            $result = \Braintree_Transaction::void($transaction_id);
            if($result->success){
                $res['success']=TRUE;
            }else{
                $res['success']=FALSE;
                foreach($result->errors->deepAll() as $error){
                    $res['error']=$error->message;
                }
                return $res;
            }
        }  catch (\Braintree\Exception\NotFound $e){
            $res['success']=FALSE;
            $res['error']="Invalid Transaction Id";
        }
        return $res;
    }

    //function to refund a sattled transaction
    public function transactionRefund($transaction_id){
        $res=[];
        try{
            $result = \Braintree_Transaction::refund($transaction_id);
            if($result->success){
                $res['success']=TRUE;
            }else{
                $res['success']=FALSE;
                foreach($result->errors->deepAll() as $error){
                    $res['error']=$error->message;
                }
                return $res;
            }
        }  catch (\Braintree\Exception\NotFound $e){
            $res['success']=FALSE;
            $res['error']="Invalid Transaction Id";
        }
        return $res;
    }

    /*
     * Find a transaction status by Id
     */
    public function findEscrowStatus($transaction_id){
        $res=[];
        try{
            $result = \Braintree_Transaction::find($transaction_id);
            pr($result);
            $res['success']=TRUE;
            $res['status']=$result->status;
            $res['escrowStatus']=$result->escrowStatus;
        }  catch (\Braintree\Exception\NotFound $e){
            //$result=$e->getMessage();
            $res['success']=FALSE;
            $res['error']=$e->getMessage();
        }
        return $res;
    }

    public function findMerchant($merchantAccountId){
        try{
            $result = \Braintree_MerchantAccount::find($merchantAccountId);
            //pr($result);
            $res['data']['status']=$result->status;
            $res['data']['individual']=$result->individual;
            $res['data']['business']=$result->business;
            $res['data']['funding']=$result->funding;
            $res['success']=TRUE;

        }  catch (\Braintree\Exception\NotFound $e){
            $res['success']=FALSE;
            $res['error']=$e->getMessage();
        }
        return $res;
    }


    /*
     * This function accepts the valid Merchant Account Data and return $response['success'] true if successful
     * else return $response['success'] false
     */
    public function onboard($merchantAccountParams) {
        $result = \Braintree_MerchantAccount::create($merchantAccountParams);
        $response = [];
        if ($result->success) {
            $response['success'] = TRUE;
            $response['status'] = $result->merchantAccount->status;
            $response['merchantAccountId'] = $result->merchantAccount->id;
            $response['masterMerchantAccountId'] = $result->merchantAccount->masterMerchantAccount->id;
        } else {
            $response['success'] = FALSE;
            foreach($result->errors->deepAll() as $error){
                $response['error']=$error->message;
            }
            //$response['error'] = $result->errors->deepAll();
        }
        return $response;
    }

    /*Customer Related Functions */

    public function createCustomer($data){
        $response = [];
        $result = \Braintree_Customer::create($data);
        if($result->success){
            $response['success'] = TRUE;
            $response['customer_id'] = $result->customer->id;
        }else{
            foreach($result->errors->deepAll() AS $error) {
                $response['success'] = FALSE;
                $response['error'] = $error->message;
            }
        }
        return $response;
    }

    public function findCustomer($customer_id){
        $res=[];
        try{
            $customer = \Braintree_Customer::find($customer_id);
            //pr($customer);
            $res['success']=TRUE;
            $res['customerData']['firstName']=$customer->firstName;
            $res['customerData']['lastName']=$customer->lastName;
            $res['customerData']['company']=$customer->company;
            $res['customerData']['email']=$customer->email;
            $res['paymentMethods']=[];
            if(!empty($customer->paymentMethods)){
                foreach($customer->paymentMethods as $pm){
                    //pr($pm);
                    $pm_data=[
                        'bin'=>!empty($pm->bin)?$pm->bin:'',
                        'last4'=>!empty($pm->last4)?$pm->last4:'',
                        'cardType'=>!empty($pm->cardType)?$pm->cardType:'',
                        'imageUrl'=>!empty($pm->imageUrl)?$pm->imageUrl:'',
                        'maskedNumber'=>!empty($pm->maskedNumber)?$pm->maskedNumber:'',
                        'email'=>!empty($pm->email)?$pm->email:'',
                        'token'=>!empty($pm->token)?$pm->token:''
                    ];
                    $res['paymentMethods'][]=$pm_data;
                }
            }

        }catch(\Braintree\Exception\NotFound $e){
            $res['success']=FALSE;
            $res['error']=$e->getMessage();
        }
        return $res;
    }

    public function addPaymentMethod($customer_id,$nonce){
        $response=[];
        $result = \Braintree_PaymentMethod::create([
            'customerId' => $customer_id,
            'paymentMethodNonce' => $nonce
        ]);
        if($result->success){
            $response['success'] = TRUE;
        }else{
            foreach($result->errors->deepAll() AS $error) {
                $response['success'] = FALSE;
                $response['error'] = $error->message;
            }
        }
        //pr($result);exit();
        return $response;
    }

    public function deletePaymentMethod($token){
        $res=[];
        try{
            $result = \Braintree_PaymentMethod::delete($token);
            if($result->success){
                $res['success']=TRUE;
                $res['error']='PAYMENT_METHOD_DELETED';
            }else{
                $res['success']=FALSE;
                $res['error']='PAYMENT_METHOD_DELETE_FAILED';
            }

        }catch(\Braintree\Exception\NotFound $e){
            $res['success']=FALSE;
            $res['error']=$e->getMessage();
        }
        return $res;
    }

}
