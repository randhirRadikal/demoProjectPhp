<?php
namespace App\Controller;

use App\Controller\AppController;
use Firebase\JWT\JWT;
use Cake\Utility\Security;
use Cake\Routing\Router;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    public function initialize() {
        parent::initialize();
        $this->Auth->allow(['adminLogin','login','test','userLogin','add','checkEmail','emailVerification','forgotPassword','restPassword','resendVerificationLink','secondFormSubmit','sendVerification','userChangePassword','userForgotPasswordPhoneVerification','userForgotPasswordDrivingLicenceAndSocialSecurity','forgotPasswordSendVerificationLinkOrCode']);
    }

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    
    public function index()
    {
        $auth = $this->Auth->user('id');
        pr($auth);exit();
        $users = $this->paginate($this->Users);
        $this->set(compact('users'));
        $this->set('_serialize', ['users']);
    }

    public function test(){
        $this->loadComponent('S3');
        $this->S3->test();
        exit();
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => ['Addresses', 'Carts', 'Orders', 'Packages', 'Products']
        ]);
        $this->set('user', $user);
        $this->set('_serialize', ['user']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */


    public function add(){
        $user = $this->Users->newEntity();
        $error_code = "FAIL";
        $error_message = "Fail";
        if ($this->request->is('post')) {
            $data = $this->request->data;
            if($data['device_type'] == 'web'){
                $data['user_ip_address'] = $_SERVER['REMOTE_ADDR'];
                $data['device_token'] = md5($data['user_ip_address']);
            }else{
                $data['user_ip_address'] = '';
            }
            if(!empty($data['email'])){
              $data['email_verification_code'] = md5($data['email']);
            }else{
              $data['email_verification_code']='';
            }
            if(!empty($data['address'])){
              $data['lat'] = $data['address']['geometry']['location']['lat'];
              $data['lng'] = $data['address']['geometry']['location']['lng'];
              $data['address'] = $data['address']['formatted_address'];
            }
            //pr($data); exit;


            if($this->Users->emailCheck($data['email']) == 0){
              $user = $this->Users->patchEntity($user, $data);
              $mail_content=[];
              if ($this->Users->save($user)) {
                  $to = $data['email'];
                  $mail_content['template_name'] = 'account_verification_success_email_template';
                  $mail_content['subject'] = 'ITIS4RENT - Verified account';
                  $mail_content['first_name'] = $data['first_name'];
                  $mail_content['account_verification_link'] = Router::url('/', TRUE) . "Users/email_verification/" .$data['email_verification_code'];
                  $this->sent_email($to, $mail_content);
                  $error_code = "SUCCESS";
                  $error_message = "Sign up successfuly. Please check your mail to verifiy.";
              } else {
                  $error_code = "FAIL";
                  $error_message = "The user could not be saved, Please, try again.";
              }
            }else{
              $error_code = "FAIL";
              $error_message = "Email already exists";
            }
        }
        $this->set(["error_code"=>$error_code,
                    "error_message"=>$error_message,
                    '_serialize' => ['error_code','error_message']
                ]);
    }

    public function emailVerification($verificationCode)
    {
        if($this->Users->verifyUserEmail($verificationCode)){
            $data =  "Email is verified, please login.";
        }else{
          $data = "Email already verified.";
        }
        $message = base64_encode($data);
        return $this->redirect($this->getSiteUrl()."#/login/".$message);
    }

    public function checkEmail()
    {
        $res = false;
        if ($this->request->is('post')) {
            $data = $this->request->data;
              if($this->Users->emailCheck($data['value']) ==0){
                $res = array('isValid' => 'bool');
              }
        }
        echo json_encode($res);
        exit;
    }

    /*public function forgotPassword(){
      $error_code = "FAIL";
      $error_message = "Fail";
      if ($this->request->is('post')) {
            $data = $this->request->data;
            $user = $this->Users->forgotPassword($data['email']);
            if($user){
              $to = $user->email;
              $mail_content['template_name'] = 'account_reset_password_email_template';
              $mail_content['subject'] = 'ITIS4RENT - Reset password';
              $mail_content['first_name'] = $user->first_name;
              $mail_content['reset_password_link'] = $this->getSiteUrl() . "#/user/changePassword/" .$user->password_verification_code;
              $this->sent_email($to, $mail_content);
              $error_code = "SUCCESS";
              $error_message = "verification link send to your email id, Please check your mail";
            }else{
              $error_code = "FAIL";
              $error_message = "Please enter a valid email address";
            }
      }
      $this->set(["error_code"=>$error_code,
                  "error_message"=>$error_message,
                  '_serialize' => ['error_code','error_message']
              ]);
    }*/



    public function forgotPassword(){
      $error_code = "FAIL";
      $error_message = "Fail";
      $result = ['type'=>''];
      if ($this->request->is('post')) {
            $data = $this->request->data;
            $user = $this->Users->checkEmailOrPhone($data['email']);
            if($user['checkEmailOrPhone'] == 'email'){
                  $to = $user->email;
                  $mail_content['template_name'] = 'account_reset_password_email_template';
                  $mail_content['subject'] = 'ITIS4RENT - Reset password';
                  $mail_content['first_name'] = $user->first_name;
                  $mail_content['reset_password_link'] = $this->getSiteUrl() ."#/change_password/".$user->password_verification_code;
                  $this->sent_email($to, $mail_content);
                  $error_code = "SUCCESS";
                  $error_message = "verification link send to your email id, Please check your mail";
                  $result = ['type'=>'email'];
            }elseif($user['checkEmailOrPhone'] == 'phone'){
                $conn = ConnectionManager::get('default');
                $query = "call sp_get_random_number(".$user->id.");";
                if($conn->execute($query)){
                    $user = $this->Users->getUserDetails($user->id);
                    $to =$data['email'];
                    $message = "Your verification code for ITIS4RENT is: ".$user['phone_verification_code'];
                    if($this->sendVerification($to,$message)){
                      $error_code = "SUCCESS";
                      $error_message = "Verification code generated.";
                      $result = ['type'=>'phone'];
                    }else{
                      $error_code = "FAIL";
                      $error_message = "Currently unable to send verification code to your number. Try again later.";
                      $result = ['type'=>'phone'];
                    }
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
      $error_code = "FAIL";
      $error_message = "Fail";
      $result = ['type'=>''];
      if ($this->request->is('post')) {
        $data = $this->request->data;
        $user = $this->Users->checkVerificationCode($data['verification_code']);
        if($user){
          if($user->password_verification_code){
            $error_code = "SUCCESS";
            $error_message = "We have send on sms verification code to your phone.";
            $result = ['type'=>$user->password_verification_code];
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
        $error_code = "FAIL";
        $error_message = "Fail";
        $result=[];
        if ($this->request->is('post')) {
          $data = $this->request->data;
          $user = $this->Users->checkDrivingLicenceAndSocialSecurity($data);
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

    public function forgotPasswordSendVerificationLinkOrCode()
    {
      $error_code = "FAIL";
      $error_message = "Fail";
      $result=['type' =>''];
      if ($this->request->is('post')) {
        $data = $this->request->data;
        if($data['type'] == 'email'){
            $user = $this->Users->forgotPasswordSendVerificationLink($data['user_id']);
            //pr($user); exit;
            if($user){
              $to = $user->email;
              $mail_content['template_name'] = 'account_reset_password_email_template';
              $mail_content['subject'] = 'ITIS4RENT - Reset password';
              $mail_content['first_name'] = $user->first_name;
              $mail_content['reset_password_link'] = $this->getSiteUrl() ."#/change_password/".$user->password_verification_code;
              $this->sent_email($to, $mail_content);
              $error_code = "SUCCESS";
              $error_message = "verification link send to your email id, Please check your mail";
              $result = ['type'=>'email'];
            }else{
              $error_code = "FAIL";
              $error_message = "Some problem in site, please reload the page";
            }
        }elseif($data['type'] == 'phone'){
          $user = $this->Users->forgotPasswordSendVerificationCode($data['user_id']);
          //pr($user); exit;
          if($user){
              $conn = ConnectionManager::get('default');
              $query = "call sp_get_random_number(".$user->id.")";
              if($conn->execute($query)){
                  $user = $this->Users->getUserDetails($user->id);
                  $to =$user['phone'];
                  $message = "Your verification code for ITIS4RENT is: ".$user['phone_verification_code'];
                  if($this->sendVerification($to,$message)){
                    $error_code = "SUCCESS";
                    $error_message = "We have send on sms verification code to your phone.";
                    $result = ['type'=>'phone'];
                  }else{
                    $error_code = "FAIL";
                    $error_message = "Currently unable to send verification code to your number. Try again later.";
                  }
              }
          }else{
            $error_code = "FAIL";
            $error_message = "Some problem in site, please reload the page";
          }
        }else{
          $error_code = "FAIL";
          $error_message = "Some problem in site, please reload the page";
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
        $error_code = "FAIL";
        $error_message = "Fail";
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $users = $this->Users->userChangePassword($data);
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
    public function restPassword($deviceToken)
    {

        if($this->Users->resetPassword($deviceToken)){
            $data = $deviceToken;
        }else{
            $data = "";
            exit;
        }
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data =$this->request->data;
            if ($this->Users->changePassword($data)) {

            } else {
                $this->Flash->error(__(''));
            }
        }
        $this->set(compact('data'));
        $this->set('_serialize', ['data']);
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

    public function getUserDetails(){
        $id = $this->Auth->user('id');
        $data['message'] = 'Some problem in app. please reload the page.';
        $data['error_code'] = '1';
        $user = $this->Users->getUserDetails($id);
        if(!empty($user)){
            $data['message'] = '';
            $data['error_code'] = '0';
            $data['data'] = $user;
        }
        $this->set(compact('data'));
        $this->set('_serialize', ['data']);
    }

    public function userIsApproved(){
        $id = $this->Auth->user('id');
        $data['message'] = 'Some problem in site. please reload the page';
        $data['error_code'] = '1';
        $user = $this->Users->getUserDetails($id);
        if(!empty($user)){
            $data['message'] = '';
            $data['error_code'] = '0';
            $data['data'] = $user;
        }
        $this->set(compact('data'));
        $this->set('_serialize', ['data']);
    }

    public function login(){
        $res['error_message']='This is post method';
        $res['error_code']='1';
        if ($this->request->is('post')) {
            //$data = $this->request->data;
            $user = $this->Auth->identify();
            //pr($user); exit;
            if(!$user){
              $this->set([
                  'success'=>FALSE,
                  'err'=>"Invalid username or password",
                  '_serialize'=>['success','err']
              ]);
            }else{
              if($user['status'] == 'Active'){
                  $this->set([
                      'success'=>'login',
                      'data'=>[
                          'token'=>  JWT::encode([
                              'sub'=>$user['id'],
                              'exp'=>time()+604800
                          ],  Security::salt()),
                          'name'=>$user['first_name'].' '.$user['last_name'],
                          'isApproved'=>$user['is_approved'],
                          'profilePic'=>$user['profile_image'],
                          'isEmailVerified'=>$user['is_email_verified'],
                          'isPhoneVerified'=>$user['is_phone_verified'],
                          'isDlVerified'=>$user['is_dl_verified'],
                          'isSsVerified'=>$user['is_ss_verified']
                      ],
                      '_serialize'=>['success','data']
                  ]);
              }else{
                  $this->set([
                      'success'=>'not_verified',
                      'data'=>[
                          'token'=>'',
                          'email'=>$user['email']
                      ],
                      '_serialize'=>['success','data']
                  ]);
              }

            }
        }
    }
    public function sendMobileVerificationCode(){
        $error_code = "FAIL";
        $error_message = "Fail";
        if ($this->request->is('post')) {
            $data = $this->request->data;
            if(isset($data['phone'])){
                $users = $this->Users->isMobileAvilable($data['phone'],$this->Auth->user('id'));
                if($users){
                    $conn = ConnectionManager::get('default');
                    $query = "call sp_get_random_number(".$this->Auth->user('id').");";
                    if($conn->execute($query)){
                        $user = $this->Users->getUserDetails($this->Auth->user('id'));
                        $to =$data['phone'];
                        $message = "Your verification code for ITIS4RENT is: ".$user['phone_verification_code'];
                        if($this->sendVerification($to,$message)){
                          $error_code = "SUCCESS";
                          $error_message = "We have send on sms verification code to your phone.";
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


    public function resendMobileVerificationCode(){
          $error_code = "FAIL";
          $error_message = "Fail";
          if ($this->request->is('post')) {
              $data = $this->request->data;
              if(isset($data['phone'])){
                  $users = $this->Users->isMobileAvilable($data['phone'],$this->Auth->user('id'));
                  if($users){
                      $conn = ConnectionManager::get('default');
                      $query = "call sp_get_random_number(".$this->Auth->user('id').");";
                      if($conn->execute($query)){
                          $user = $this->Users->getUserDetails($this->Auth->user('id'));
                          $to =$data['phone'];
                          $message = "Your verification code for ITIS4RENT is: ".$user['phone_verification_code'];
                          if($this->sendVerification($to,$message)){
                            $error_code = "SUCCESS";
                            $error_message = "We have send on sms verification code to your phone.";
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

    public function userLogin(){
        $res['error_code']='FAIL';
        $res['error_message']='Invalid Request';
        if ($this->request->is('post')) {
            $data = $this->request->data;
            //pr($data); exit;
            $required['email'] = $data['email'];
            $required['password'] = $data['password'];
            $blank_field = $this->__require_fields($required);

            if (count($blank_field)>0){
                $res['error_code'] = 'FAIL';
                $res['error_message'] = 'Please enter your Email ID and Password.';
            }else{
                $user = $this->Auth->identify();
                //pr($user); exit;
                if(empty($user)){
                    $res['error_code'] = 'FAIL';
                    $res['error_message'] = 'Invalid Details.';
                }else{
                  if($user){
                      $res['error_code']='SUCCESS';
                      $res['error_message']='Logged in successfully.';
                      $res['result']=[
                          'token'=>  JWT::encode([
                              'sub'=>$user['id'],
                              'exp'=>time()+604800
                          ],  Security::salt()),
                          'name'=>$user['first_name'].' '.$user['last_name'],
                          'email'=>$user['email'],
                          'is_email_verified'=>$user['is_email_verified'],
                          'is_phone_verified'=>$user['is_phone_verified'],
                          'is_dl_verified'=>$user['is_dl_verified'],
                          'is_ss_verified'=>$user['is_ss_verified'],
                          'is_approved'=>$user['is_approved'],
                          'profile_image'=>$user['profile_image'],
                          'user_role'=>$user['role']
                      ];
                  }
                }

            }
        }
        $this->set(["error_code"=>$res['error_code'],
                    "error_message"=>$res['error_message'],
                    "result"=>!empty($res['result'])?$res['result']:array(),
                    '_serialize' => ['error_code','error_message','result']
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

    public function generatePhoneVerificationCode($userId=null){
        if(!$userId){
            $userId = $this->Auth->user('id');
        }
        $user=$this->Users->find('all')->where([
            'id'=>$userId,
            'is_phone_verified'=>0
        ])->first();
    }

    public function drivingLicenceVerification(){
        $error_code = "FAIL";
        $error_message = "Fail";
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $required['social_security_number'] = $data['driving_licence_number'];
            $required['social_security_number'] = $data['social_security_number'];
            $blank_field = $this->__require_fields($required);
            if (count($blank_field)>0){
                $error_code = "FAIL";
                $error_message = "Please enter your Driving Licence and Social Security Number.";
            }else{
                $id = $this->Auth->user('id');
                $users = $this->Users->drivingLicenceVerification($id,$data);
                if($users){
                    $error_code = "SUCCESS";
                    $error_message = "Su";
                }else{
                    $error_code = "FAIL";
                    $error_message = "Driving Licence and Social Security Number is already exists";
                }
            }
        }
        $this->set(["error_code"=>$error_code,
                    "error_message"=>$error_message,
                    '_serialize' => ['error_code','error_message']
                ]);
    }

    public function getUserDetailsForUpdate(){
        $id = $this->Auth->user('id');
        $error_code = "FAIL";
        $error_message = "Fail";
        $user = $this->Users->getUserDetailsForUpdate($id);
        if(!empty($user)){
          $profileStrength = 0;
          if($user->is_phone_verified){
            $profileStrength = $profileStrength+1;
          }
          if($user->is_dl_verified){
            $profileStrength = $profileStrength+1;
          }
          if($user->is_ss_verified){
            $profileStrength = $profileStrength+1;
          }
          if($user->profile_image){
            $profileStrength = $profileStrength+1;
            $user->profile_pics = $user->profile_image;
          }
          if($user->is_approved){
            $profileStrength = $profileStrength+1;
          }
          $user->profileStrength = intval($profileStrength * 100 /5);
          $error_code = "SUCCESS";
          $error_message = "successfully.";
        }
        $this->set(["error_code"=>$error_code,
                    "error_message"=>$error_message,
                    "result"=>!empty($user)?$user:array(),
                    '_serialize' => ['error_code','error_message','result']
                ]);
    }

    public function userUpdateProfile()
    {
        $error_code = "FAIL";
        $error_message = "Fail";
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $required['first_name'] = $data['first_name'];
            $required['address'] = $data['address'];
            $blank_field = $this->__require_fields($required);
            if (count($blank_field)>0){
                $error_code = "FAIL";
                $error_message = "Please enter required field";
            }else{
                $id = $this->Auth->user('id');
                if($this->Users->userUpdateProfile($id,$data)){
                    $error_code = "SUCCESS";
                    $error_message = "Profile update successfully.";
                }else{
                    $error_code = "FAIL";
                    $error_message = "";
                }
            }
        }
        $this->set(["error_code"=>$error_code,
                  "error_message"=>$error_message,
                  '_serialize' => ['error_code','error_message']
              ]);
    }


    public function userUpdateProfileWithPhone(){
        $error_code = "FAIL";
        $error_message = "Fail";
        if ($this->request->is('post')) {
            $data = $this->request->data;
            if(isset($data['verification_code'])){
                $users = $this->Users->userUpdateProfileWithPhone($phone,$this->Auth->user('id'),$data['verification_code']);
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

    public function saveProfileImage()
    {
        $error_code = "FAIL";
        $error_message = "Fail";
        if($this->request->is('POST')){
          $file = $this->request->data['file'];
          if($file){
            $this->loadComponent('S3');
            $savedData=($this->S3->saveProfileImage($file));
            if($savedData){
              $id = $this->Auth->user('id');
               $profileImageName = $this->Users->getOldProfileImageName($id);
              if(!empty($profileImageName)){
                   $this->S3->deleteProfileImage($profileImageName);
              }
              if($this->Users->saveProfileImage($id,$savedData)){
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
            $data['user_id'] = $this->Auth->user('id');
            if($this->Users->userUpdateProfileDetails($data)){
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

    // For admin

    public function adminLogin(){
        $res['error_code']='FAIL';
        $res['error_message']='Invalid Access';
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $required['email'] = $data['email'];
            $required['password'] = $data['password'];
            $blank_field = $this->__require_fields($required);

            if (count($blank_field)>0){
                $res['error_code'] = 'FAIL';
                $res['error_message'] = 'Please enter your Email ID and Password.';
            }else{
                    $admin = [];
                    $admin = $this->Auth->identify();
                    //$admin = $this->Users->getAdminLogin($data);
                    //pr($admin['email']);exit;
                if(empty($admin)){
                        $res['error_code'] = 'FAIL';
                        $res['error_message'] = 'Invalid Details.';

                }else{
                        if($admin['status'] == 'Active' && $admin['role'] === 'admin'){

                                $res['error_code']='SUCCESS';
                                $res['error_message']='Logged in successfully.';
                                $res['result']=[
                                    'token'=>  JWT::encode([
                                        'sub'=>$admin['id'],
                                        'exp'=>time()+604800
                                    ],  Security::salt()),
                                    'name'=>$admin['first_name'].' '.$admin['last_name'],
                                    'isApproved'=>$admin['is_approved'],
                                    'profilePic'=>$admin['profile_image'],
                                    'user_role'=>$admin['role']
                                ];
                        }
                }
            }
        }
        $this->set(["error_code"=>$res['error_code'],
                    "error_message"=>$res['error_message'],
                    "result"=>!empty($res['result'])?$res['result']:array(),
                    '_serialize' => ['error_code','error_message','result']
                ]);
    }


    public function getUsersList(){
          $error_code = "FAIL";
          $error_message = "Fail";
          if($this->request->is('POST')){
              $id = $this->Auth->user('id');
              $data = $this->request->data;
              $user = $this->Users->getUsersList($data);
              $error_code = "SUCCESS";
              $error_message = "User list.";
          }
          $this->set(["error_code"=>$error_code,
                      "error_message"=>$error_message,
                      "result"=>!empty($user)?$user:array(),
                      '_serialize' => ['error_code','error_message','result']
                  ]);
    }

    public function userApprovedByAdmin($value='')
    {
        $error_code = "FAIL";
        $error_message = "Fail";
        if($this->request->is('POST')){
            $data = $this->request->data;
            if($this->Users->userApprovedByAdmin($data['user_id'])){
                $error_code = "SUCCESS";
                $error_message = "User account approved.";
            }else{
                $error_code = "FAIL";
                $error_message = "Some problem please reload the page.";
            }
        }
        $this->set(["error_code"=>$error_code,
                    "error_message"=>$error_message,
                    '_serialize' => ['error_code','error_message']
                ]);
    }

}
