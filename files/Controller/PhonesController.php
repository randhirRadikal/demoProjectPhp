<?php
namespace App\Controller;

use App\Controller\AppController;
use Firebase\JWT\JWT;
use Cake\Utility\Security;
use Cake\Routing\Router;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;


class PhonesController extends AppController
{
    public function initialize() {
        parent::initialize();
        $this->Auth->allow(['index','lookup']);
    }

    public function lookup($number){
        $this->loadComponent('twilio');
        $number = $this->twilio->lookUp($number);
        $res=[];
        if($number){
            $res['error_code'] = 'SUCCESS';
            $res['data']=$number;
        }else{
            $res['error_code']='FAIL';
            $res['error_message']="Invalid phone";
        }
        $this->set('res', $res);
        $this->set('_serialize', ['res']);
    }

    public function index()
    {
        $users = $this->paginate($this->Users);
        $this->set(compact('users'));
        $this->set('_serialize', ['users']);
    }


    public function deletePhone()
    {
        $res['error_message']='This is post method';
        $res['error_code']='FAIL';
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $entity = $this->Phones->get($data['phone_id']);
            if($this->Phones->delete($entity)){
              $res['error_code'] = 'SUCCESS';
              $res['error_message'] = 'Phones delete successfully.';
            }else {
              $res['error_code'] = 'FAIL';
              $res['error_message'] = 'Some Problem.';
            }
        }
        //$this->set('res', $res);
        //$this->set('_serialize', ['res']);
        $this->set(["error_code"=>$res['error_code'],
                    "error_message"=>$res['error_message'],
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
                  $result['error_message'] = 'This phone no is assosicated with other user.';
                }
                $this->set(["error_code"=>$result['error_code'],
                            "error_message"=>$result['error_message'],
                            '_serialize' => ['error_code','error_message']
                        ]);
            }
        }
    }

    public function sendMobileVerificationCode(){
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
                        if($this->sendVerification($to,$message)){
                          $error_code = "SUCCESS";
                          $error_message = "Verification code generated.";
                        }else{
                          $error_code = "FAIL";
                          $error_message = "Currently unable to send verification code to your number. Try again later.";
                        }
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

    public function verifyPhoneVerification($value='')
    {
        $error_code = "FAIL";
        $error_message = "Fail";
        if($this->request->is('POST')){
            $data = $this->request->data;
            $phone = $this->Phones->checkVerificationCode($data);
            if($phone){
                $error_code = "SUCCESS";
                $error_message = "Phone verified successfully.";
            }else{
                $error_code = "FAIL";
                $error_message = "Invalid sms verification code";
            }
        }
        $this->set(["error_code"=>$error_code,
                    "error_message"=>$error_message,
                    '_serialize' => ['error_code','error_message']
                ]);
    }

    public function changePhoneNumberToPrimary()
    {
        $error_code = "FAIL";
        $error_message = "Fail";
        if($this->request->is('POST')){
            $data = $this->request->data;
            $data['user_id'] = $this->Auth->user('id');
            $conn = ConnectionManager::get('default');
            $query = "call sp_move_phone_number(".$data['user_id'].",".$data['phone_id'].");";
            $result =  $conn->execute($query)->fetchAll('assoc');
            //pr($result); exit;
            if($result[0]['error_code'] == 'SUCCESS'){
                $error_code = "SUCCESS";
                $error_message = $result[0]['error_message'];
            }else{
                $error_code = "FAIL";
                $error_message = "Some problem";
            }
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
