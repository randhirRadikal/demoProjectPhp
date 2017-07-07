<?php
namespace App\Controller;

use App\Controller\AppController;
use Firebase\JWT\JWT;
use Cake\Utility\Security;
use Cake\Routing\Router;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\I18n\Time;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class PartiesController extends AppController
{
    public function initialize() {
        parent::initialize();
        $this->Auth->allow(['adminLogin','login','test','userLogin','add','checkEmail','emailVerification','forgotPassword','restPassword','resendVerificationLink','secondFormSubmit','sendVerification','userChangePassword','userForgotPasswordPhoneVerification','userForgotPasswordDrivingLicenceAndSocialSecurity','forgotPasswordSendVerificationLinkOrCode']);
    }

    public function test(){
        $this->loadComponent('S3');
        $this->S3->test();
        exit();
    }

    public function add()
    {
        //ok
        $error_code = "FAIL";
        $error_message = "Fail";
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $required['email'] = $data['email'];
            $required['c_password'] = $data['c_password'];
            $required['first_name'] = $data['first_name'];
            $required['last_name'] = $data['last_name'];
            $required['address_line_1'] = $data['address_line_1'];
            $required['city'] = $data['city'];
            $required['zipcode'] = $data['zipcode'];
            $blank_field = $this->__require_fields($required);
            if (count($blank_field)>0){
                $res['error_code'] = 'FAIL';
                $res['error_message'] = 'Please enter your required fields.';
            }else{
				        $dateTime = Time::now();
                $data['email_verification_code'] = md5($data['email'].'-'.$dateTime);
                $data['address_line_2'] = isset($data['address_line_2'])?$data['address_line_2']:'';
                $data['middle_name'] = isset($data['middle_name'])?$data['middle_name']:'';
                $data['county'] = isset($data['county'])?$data['county']:'';

                if($this->Parties->emailCheck($data['email']) ==0){
                  if($this->Parties->addParty($data,$dateTime)){
                    $mail_content=[];
                    $to = $data['email'];
                    $mail_content['template_name'] = 'account_verification_success_email_template';
                    $mail_content['subject'] = 'ITIS4RENT - Verified account';
                    $mail_content['first_name'] = $data['first_name'];
                    $mail_content['account_verification_link'] = Router::url('/', TRUE) . "Parties/email_verification/" .$data['email_verification_code'];
                    $this->sent_email($to, $mail_content);
                    $error_code = "SUCCESS";
                    $error_message = "Sign up successfuly. Please check your mail to verifiy.";
                  }else{
                    $error_code = "FAIL";
                    $error_message = "The user could not be saved, Please, try again.";
                  }
                }else{
                    $error_code = "FAIL";
                    $error_message = "Email already exists.";
                }
            }
        }
        $this->set(["error_code"=>$error_code,
                    "error_message"=>$error_message,
                    '_serialize' => ['error_code','error_message']
                ]);
    }

    public function emailVerification($verificationCode)
    {
        if($this->Parties->verifyPartiesEmail($verificationCode)){
          $data =  "Email is verified, please login.";
        }else{
          $data = "Email already verified.";
        }
        $message = base64_encode($data);
        return $this->redirect($this->getSiteUrl()."#/login/".$message);
    }

    public function checkEmail()
    {
      //ok
        $res = false;
        if ($this->request->is('post')) {
            $data = $this->request->data;
              if($this->Parties->emailCheck($data['value']) ==0){
                $res = array('isValid' => 'bool');
              }
        }
        echo json_encode($res);
        exit;
    }

    public function login(){
      //ok
        $res['error_message']='This is post method';
        $res['error_code']='1';
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $user = $this->Auth->identify();
            if(!$user){
              $this->set([
                  'success'=>FALSE,
                  'err'=>"Invalid username or password",
                  '_serialize'=>['success','err']
              ]);
            }else{
              $this->set([
                  'success'=>'login',
                  'data'=>[
                      'token'=>  JWT::encode([
                          'sub'=>$user['party_id'],
                          'exp'=>time()+604800
                      ],  Security::salt())
                  ],
                  '_serialize'=>['success','data']
              ]);
            }
        }
    }

    public function forgotPassword(){
      //ok
      $error_code = "FAIL";
      $error_message = "Fail";
      $result = ['type'=>''];
      if ($this->request->is('post')) {
            $data = $this->request->data;
            $user = $this->Parties->checkEmailOrPhone($data['email']);
            if($user['checkEmailOrPhone'] == 'email'){
                  $mail_content=[];
                  $to = $user->party_email->email_address;
                  $mail_content['template_name'] = 'account_reset_password_email_template';
                  $mail_content['subject'] = 'ITIS4RENT - Reset password';
                  $mail_content['first_name'] = '';
                  $mail_content['reset_password_link'] = $this->getSiteUrl() ."#/change_password/".$user->password_verification_code;
                  $this->sent_email($to, $mail_content);
                  $error_code = "SUCCESS";
                  $error_message = "verification link send to your email id, Please check your mail";
                  $result = ['type'=>'email'];
            }elseif($user['checkEmailOrPhone'] == 'phone'){
                $data['dateTime'] = Time::now();
                $data['phone_verification_code'] = $this->generateRandomStringPhone(6);
                $data['party_id']= $user->party_id;
                $to =$user->party_phone->phone_number;
                $message = "Your verification code for ITIS4RENT is: ".$data['phone_verification_code'];
                if($this->sendVerification($to,$message)){
                  $users = $this->Parties->sendMobileVerificationCode($data);
                  $error_code = "SUCCESS";
                  $error_message = "Verification code generated.";
                  $result = ['type'=>'phone'];
                }else{
                  $error_code = "FAIL";
                  $error_message = "Currently unable to send verification code to your number. Try again later.";
                }
            }else{
              $error_code = "FAIL";
              $error_message = "Please enter a valid email address or phone";
            }

      }
      $this->set(["error_code"=>$error_code,
                  "error_message"=>$error_message,
                  "result"=>$result,
                  '_serialize' => ['error_code','error_message','result']
              ]);
    }

    public function userForgotPasswordPhoneVerification()
    {
      //ok
      $error_code = "FAIL";
      $error_message = "Fail";
      $result = ['type'=>''];
      if ($this->request->is('post')) {
        $data = $this->request->data;
        $user = $this->Parties->checkVerificationCode($data['verification_code']);
        if($user){
          if($user->changePasswordToken){
            $error_code = "SUCCESS";
            $error_message = "successfully";
            $result = ['type'=>$user->changePasswordToken];
          }else{
            $error_code = "FAIL";
            $error_message = "Some problem.";
          }
        }else{
          $error_code = "FAIL";
          $error_message = "Please enter valied verification code.";
        }
      }
      $this->set(["error_code"=>$error_code,
                  "error_message"=>$error_message,
                  "result"=>$result,
                  '_serialize' => ['error_code','error_message','result']
              ]);
    }


    public function userForgotPasswordDrivingLicenceAndSocialSecurity()
    {
      //ok - not done
        $error_code = "FAIL";
        $error_message = "Fail";
        $result=[];
        if ($this->request->is('post')) {
          $data = $this->request->data;
          $user = $this->Parties->checkDrivingLicenceAndSocialSecurity($data);
          if($user){
            if(!empty($user->email)){
                $em   = explode("@",$user->email);
                $newstring[0] = substr($em[0], 0, 1);
                $newstring[1] = substr($em[0], -1);
                $em   = explode(".",$em[1]);
                $newstring[2] = substr($em[0], 0, 2);
                $newstring[3] = $em[1];
                $user->email = $newstring[0].'*****'.$newstring[1].'@'.$newstring[2].'**.'.$newstring[3];
            }
            if(!empty($user->phone)){

                $newstring[0] = substr($user->phone, 0, 3);
                $newstring[1] = substr($user->phone, -4);
                $user->phone = $newstring[0].'*****'.$newstring[1];
            }
            $error_code = "SUCCESS";
            $error_message = "To reset your password choose one of below action.";
            $result = $user;
          }else{
            $error_code = "FAIL";
            $error_message = "Sorry! we couldn't figure out your account. Please contact admin.";
          }
        }
        $this->set(["error_code"=>$error_code,
                    "error_message"=>$error_message,
                    "result"=>$result,
                    '_serialize' => ['error_code','error_message','result']
                ]);
    }



    public function userChangePassword()
    {
        //ok
        $error_code = "FAIL";
        $error_message = "Fail";
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $users = $this->Parties->userChangePassword($data);
            if($users){
                $error_code = "SUCCESS";
                $error_message = "Password changed successfully";
            }else{
                $error_code = "FAIL";
                $error_message = "Some problem in app. please reload the page";
            }
        }
        $this->set(["error_code"=>$error_code,
                    "error_message"=>$error_message,
                    '_serialize' => ['error_code','error_message']
                ]);
    }

    public function resendVerificationLink(){
      if ($this->request->is('post')) {
            $userData = $this->request->data;
            $user = $this->Users->resendVerificationLink($userData['email']);
            if(empty($user->email_verification_code)){
              $user->email_verification_code= md5($user->email);
              $this->Users->save($user);
            }
            if($user){
                $to = $user->email;
                $mail_content['template_name'] = 'account_verification_success_email_template';
                $mail_content['subject'] = 'ITIS4RENT - Verified account';
                $mail_content['first_name'] = $user->first_name;

                $mail_content['account_verification_link'] = Router::url('/', TRUE) . "Users/email_verification/" .$user->email_verification_code;
                $this->sent_email($to, $mail_content);
                $data['message'] = 'verification link send to your email, Please check your mail';
                $data['error_code'] = '0';
            }
      }
      $this->set(compact('data'));
      $this->set('_serialize', ['data']);
    }


    public function verifyPhoneVerificationCode(){
        $error_code = "FAIL";
        $error_message = "Fail";
        if ($this->request->is('post')) {
            $data = $this->request->data;
            if(isset($data['verification_code'])){
                $users = $this->Users->isMobileVerificationCodeAvilable($data['phone'],$this->Auth->user('id'),$data['verification_code']);
                if($users){
                    $error_code = "SUCCESS";
                    $error_message = "Phone verified successfully.";
                }else{
                    $error_code = "FAIL";
                    $error_message = "Invalid sms verification code";
                }
            }else{
                $error_code = "FAIL";
                $error_message = "Please enter verification code sent to your phone.";
            }
        }
        $this->set(["error_code"=>$error_code,
                    "error_message"=>$error_message,
                    '_serialize' => ['error_code','error_message']
                ]);
    }

    public function logout()
    {
        //ok
        if($this->Auth->logout()){
            $this->set(["error_code"=>'SUCCESS',
                    "error_message"=>'Logout Successfully.',
                    '_serialize' => ['error_code','error_message']
                ]);
        }else{
             $this->set(["error_code"=>'FAIL',
                    "error_message"=>'Could not Logout.',
                    '_serialize' => ['error_code','error_message']
                ]);
        }
    }

    public function sendVerification($to,$message){
        $this->loadComponent('twilio');
        $res=$this->twilio->sendSms($to,$message);
        if($res){
          return TRUE;
        }
        return FALSE;
    }

    public function getUserDetailsForUpdate(){
      //ok
        $id = $this->Auth->user('id');
        $error_code = "FAIL";
        $error_message = "Fail";
        $party = $this->Parties->getUserDetailsForUpdate($id);
        if($party){
          $error_code = "SUCCESS";
          $error_message = "successfully.";
        }
        $this->set(["error_code"=>$error_code,
                    "error_message"=>$error_message,
                    "result"=>!empty($party)?$party:array(),
                    '_serialize' => ['error_code','error_message','result']
                ]);
    }


    public function saveProfileImage()
    {
        //ok
        $error_code = "FAIL";
        $error_message = "Fail";
        if($this->request->is('POST')){
          $file = $this->request->data['file'];
          if($file){
            $this->loadComponent('S3');
            $savedData=($this->S3->saveProfileImage($file));
            if($savedData){
              $party_id = $this->Auth->user('id');
              $profileImageName = $this->Parties->getOldProfileImageName($party_id);
              if(!empty($profileImageName)){
                   $this->S3->deleteProfileImage($profileImageName);
              }
              if($this->Parties->saveProfileImage($party_id,$savedData)){
                  $error_code = "SUCCESS";
                  $error_message = "Photo upload successfully.";
                  $data = array('profile_name'=>$savedData);
              }
            }
          }
        }
        $this->set(["error_code"=>$error_code,
                    "error_message"=>$error_message,
                    "result"=>!empty($data)?$data:array(),
                    '_serialize' => ['error_code','error_message','result']
                ]);
    }

    public function userChangeProfilePassword()
    {
        $error_code = "FAIL";
        $error_message = "Fail";
        $user =$this->Users->get($this->Auth->user('id'));
        if ($this->request->is('POST')) {
          $data = $this->request->data;
          $required['old_password'] = isset($data['old_password'])?$data['old_password']:'';
          $required['password1'] = isset($data['password1'])?$data['password1']:'';
          $required['password2'] = isset($data['password2'])?$data['password2']:'';
          $blank_field = $this->__require_fields($required);
          if (count($blank_field)>0){
            $error_code = "FAIL";
            $error_message = "Please enter required fields.";
          }else{
            $user = $this->Users->patchEntity($user, [
                   'old_password' => $this->request->data['old_password'],
                   'password' => $this->request->data['password1'],
                   'password1' => $this->request->data['password1'],
                   'password2' => $this->request->data['password2']
                ],
                ['validate' => 'password']
            );
            if($this->Users->save($user)){
              $error_code = "SUCCESS";
              $error_message = "Password change successfully.";
            }else{
              $error_code = "FAIL";
              $error_message = "The old password does not match the current password.";
            }
          }
        }
        $this->set(["error_code"=>$error_code,
                    "error_message"=>$error_message,
                    '_serialize' => ['error_code','error_message']
                ]);
    }

    public function userUpdateProfileDetails()
    {
      //ok
      $error_code = "FAIL";
      $error_message = "Fail";
      if($this->request->is('POST')){
          $data = $this->request->data;
          $required['first_name'] = isset($data['first_name'])?$data['first_name']:'';
          $required['last_name'] = isset($data['last_name'])?$data['last_name']:'';
          $required['driving_licence_number'] = isset($data['driving_licence_number'])?$data['driving_licence_number']:'';
          $required['social_security_number'] = isset($data['social_security_number'])?$data['social_security_number']:'';
          $blank_field = $this->__require_fields($required);
          if (count($blank_field)>0){
            $error_code = "FAIL";
            $error_message = "Please enter required fields.";
          }else{
            $data['party_id'] = $this->Auth->user('id');
            $data['dateTime'] = Time::now();
            if($this->Parties->userUpdateProfileDetails($data)){
                $error_code = "SUCCESS";
                $error_message = "Profile update successfully.";
            }else{
                $error_code = "FAIL";
                $error_message = "Some problem please reload the page.";
            }
          }
      }
      $this->set(["error_code"=>$error_code,
                  "error_message"=>$error_message,
                  '_serialize' => ['error_code','error_message']
              ]);
    }
}
