<?php
namespace App\Controller;

use App\Controller\AppController;
use Firebase\JWT\JWT;
use Cake\Utility\Security;
use Cake\Routing\Router;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;


class NotificationsController extends AppController
{
    public function initialize() {
        parent::initialize();
        $this->Auth->allow(['index']);
    }


    public function getNotifications()
    {
        $error_code = "FAIL";
        $error_message = "Fail";
        $result=[];
        if($this->request->is('POST')){
            $data = $this->request->data;
            $data['id'] = $this->Auth->user('id');
            $result = $this->Notifications->getNotifications($data);
            $error_code = "SUCCESS";
            $error_message = "Notification List.";
        }
        $this->set(["error_code"=>$error_code,
                    "error_message"=>$error_message,
                    "result"=>$result,
                    '_serialize' => ['error_code','error_message','result']
                ]);
    }


    public function deleteNotification()
    {
        $error_code = "FAIL";
        $error_message = "Fail";
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $data['id'] = $this->Auth->user('id');
            $result = $this->Notifications->deleteNotification($data);
            if($result){
              $error_code = 'SUCCESS';
              $error_message = 'Notification delete successfully.';
            }else {
              $error_code = 'FAIL';
              $error_message = 'Some Problem.';
            }
        }
        $this->set(["error_code"=>$error_code,
                    "error_message"=>$error_message,
                    '_serialize' => ['error_code','error_message']
                ]);
    }

    public function add()
    {
        $result['error_message']='This is post method';
        $result['error_code']='FAIL';
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $required=[];
            $required['phone']  = isset($data['phone'])?$data['phone']:'';
            $blank_field = $this->__require_fields($required);
            if (count($blank_field)>0){
                $result['error_code'] = 'FAIL';
                $result['error_message'] = 'Please enter your Email ID and Password.';
            }else{
                if($this->Phones->isMobileAvilable($data['phone']) == 0){
                  $data['user_id'] = $this->Auth->user('id');
                  $Phones = $this->Phones->newEntity($data);
                  if($this->Phones->save($Phones)){
                    $result['error_code'] = 'SUCCESS';
                    $result['error_message'] = 'Phone number add successfully.';
                  }
                }else{
                  $result['error_code'] = 'FAIL';
                  $result['error_message'] = 'Phone already exists';
                }
                $this->set('result', $result);
                $this->set('_serialize', ['result']);
            }
        }
    }

    public function sendMotificationSMS(){
        $error_code = "FAIL";
        $error_message = "Fail";
        if ($this->request->is('post')) {
            $data = $this->request->data;
            if(isset($data['phone'])){
                $users = $this->Phones->isMobileAvilable($data['phone'],$data['id']);
                if($users){
                    $conn = ConnectionManager::get('default');
                    $query = "call sp_get_random_number_for_phones(".$data['id'].");";
                    if($conn->execute($query)){
                        $phone = $this->Phones->getPhoneDetails($data['id']);
                        $to =$data['phone'];
                        $message = "Your verification code for ITIS4RENT is: ".$phone['phone_verification_code'];
                        //if($this->sendVerification($to,$message)){
                          $error_code = "SUCCESS";
                          $error_message = "Verification code generated.";
                        //}else{
                        //  $error_code = "FAIL";
                        //  $error_message = "Currently unable to send verification code to your number. Try again later.";
                        //}
                    }
                }else{
                    $error_code = "FAIL";
                    $error_message = "This phone no is assosicated with other user.";
                }
            }else{
                $error_code = "FAIL";
                $error_message = "Not a valid phone no.";
            }
        }
        $this->set(["error_code"=>$error_code,
                    "error_message"=>$error_message,
                    '_serialize' => ['error_code','error_message']
                ]);
    }

    public function notificationReaded()
    {
      $error_code = "FAIL";
      $error_message = "Fail";
      if($this->request->is('POST')){
          $data = $this->request->data;
          $data['id'] = $this->Auth->user('id');
          $result = $this->Notifications->notificationReaded($data);
          $error_code = "SUCCESS";
          $error_message = "Notification List.";
      }
      $this->set(["error_code"=>$error_code,
                  "error_message"=>$error_message,
                  '_serialize' => ['error_code','error_message']
              ]);
    }

    public function sendVerification($to,$message){
        $this->loadComponent('twilio');
        $res=$this->twilio->sendSms($to,$message);
        if($res){
          return TRUE;
        }
        return FALSE;
    }
}
