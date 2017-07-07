<?php

namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Network\Email\Email;
use Cake\Routing\Router;
use Cake\ORM\TableRegistry;


class AppController extends Controller
{
    public function initialize()
    {

        parent::initialize();
        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
        $this->loadComponent('Auth', [
            'storage' => 'Memory',
            'authenticate' => [
                'Form' => [
                    'fields' => ['username' => 'email', 'password' => 'password']
                ],
                'ADmad/JwtAuth.Jwt' => [
                    'parameter' => 'token',
                    'userModel' => 'Parties',
                    'fields' => [
                        'username' => 'id'
                    ],
                    'queryDatasource' => true
                ]
            ],
            'unauthorizedRedirect' => false,
            'checkAuthIn' => 'Controller.initialize'
        ]);

        $this->Auth->Allow(['getSiteUrl','__require_fields','addNotification','sendNotificationSMS']);
    }


    public function beforeRender(Event $event)
    {
        if (!array_key_exists('_serialize', $this->viewVars) &&
            in_array($this->response->type(), ['application/json', 'application/xml'])
        ) {
            $this->set('_serialize', true);
        }
    }

    public function getSiteUrl(){
      $url = Router::url('/', TRUE);
      $url = substr($url,0,-4)."site/";
      return $url;
    }

    function generateRandomStringPhone($length = 6) {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function getAppCodeStatusId($app_code_type,$app_code_subtype=0,$app_code_name=0,$app_code_desc=0){
        $appCodesTable = TableRegistry::get('AppCodes');

        if($app_code_type && $app_code_subtype && $app_code_name && $app_code_desc){
            $appCode=$appCodesTable->Find('all')
            ->where(['app_code_type'=>$app_code_type])
            ->where(['app_code_subtype'=>$app_code_subtype])
            ->where(['app_code_name'=>$app_code_name])
            ->where(['app_code_desc'=>$app_code_desc])
            ->toArray();
        }else if($app_code_type && $app_code_subtype && $app_code_name){
            $appCode=$appCodesTable->Find('all')
            ->where(['app_code_type'=>$app_code_type])
            ->where(['app_code_subtype'=>$app_code_subtype])
            ->where(['app_code_name'=>$app_code_name])
            ->toArray();
        }else if($app_code_type && $app_code_subtype){
            $appCode=$appCodesTable->Find('all')
            ->where(['app_code_type'=>$app_code_type])
            ->where(['app_code_subtype'=>$app_code_subtype])
            ->toArray();
        }else if($app_code_type){
            $appCode=$appCodesTable->Find('all')
            ->where(['app_code_type'=>$app_code_type])
            ->toArray();
        }else{
          $appCode=[];
        }
        return $appCode;
    }

    function getAppCodeStatus($id){
        $appCodesTable = TableRegistry::get('AppCodes');
        $appCode=$appCodesTable->Find('all')
        ->where(['id'=>$id])
        ->first();
        if($appcode){
          return $appcode->app_code_desc;
        }
        return "Admin delete the row.";
    }

    function sent_email($to=array(),$data=array()){
        //Data array should have following two fields...
        //$data['template_name']
        //$data['subject']
        //pr($data); exit;
        $sender='ITIS4RENT';
        $CakeEmail = new Email('default');
        $from = 'test@mobisolz.com';
        $CakeEmail->template($data['template_name'], 'default')
                    ->emailFormat('html')
                    ->to($to)
                    ->from([$from => $sender])
                    ->subject($data['subject'])
                    ->viewVars(compact('data'));
            if (@$CakeEmail->send()) {
                return true;
            }
        return false;
    }
    public function __require_fields($required){
        $empty_fields = [];
        foreach($required as $key=>$val){
            if($val == ''){
                $empty_fields[$key] = $val;
            }
        }
        return $empty_fields;
    }

    public function addNotification($data)
    {
      if(!empty($data)){
          $notificationTable = TableRegistry::get('Notifications');
          $notification = $notificationTable->newEntity();
          if(!empty($data['email'])){
            $notification->email = $data['email'];
            $notification->email_message = $data['email_message'];
            $to = $data['email'];
            $mail_content['template_name'] = 'send_notification_email_template';
            $mail_content['subject'] = 'ITIS4RENT - Notification';
            $mail_content['first_name'] = $data['first_name'];
            $mail_content['notification_message'] = Router::url('/', TRUE) . "Users/email_verification/" .$data['email_verification_code'];
            if($this->sent_email($to, $mail_content)){
              $notification->is_email_sent = 1;
            }
          }
          if(!empty($data['phone'])){
            $notification->phone = $data['phone'];
            $notification->sms_message = $data['sms_message'];
            $to =$data['phone'];
            $message = "Your verification code for ITIS4RENT is: ".$phone['phone_verification_code'];
            if($this->sendVerification($to,$message)){
              $notification->is_sms_sent = 1;
            }
          }
          $notification->user_id = $data['user_id'];
          $notification->redirect_link = isset($data['redirect_link'])?$data['redirect_link']:'';
          $notification->subject = $data['subject'];
          $notification->type = $data['type'];
          $notification->status = 'pending';
          $notification->created_by_user_id = $data['created_by_user_id'];
          if($notificationTable->save($notification)){
              return  'SUCCESS';
          }else{
              return  'FAIL';
          }
      }
      return 'FAIL';
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
