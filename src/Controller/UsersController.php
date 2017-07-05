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
        $this->Auth->allow(['login']);
    }

	public function login(){
        $res['error_message']='This is post method';
        $res['error_code']='1';
        if ($this->request->is('post')) {
            $user = $this->Auth->identify();
            if(!$user){
              $this->set([
                  'error_code'=>"ERROR",
                  'error_message'=>"Invalid username or password",
                  '_serialize'=>['error_code','error_message']
              ]);
            }else{
              //if($user['status'] == 'Active'){
			  if(1){
                  $this->set([
                      'error_code'=>'SUCCESS',
					  'error_message'=>'Login successfuly',
                      'data'=>[
                          'token'=>  JWT::encode([
                              'sub'=>$user['id'],
                              'exp'=>time()+604800
                          ],  Security::salt()),
                          'name'=>$user['name'],
                          'email'=>$user['email']
                      ],
                      '_serialize'=>['error_code','error_message','data']
                  ]);
              }else{
                  $this->set([
                      'error_code'=>'ERROR',
					  'error_message'=>"Invalid username or password",
                      'data'=>[
                          'token'=>'',
                          'email'=>$user['email']
                      ],
                      '_serialize'=>['error_code','error_message','data']
                  ]);
              }

            }
        }
    }

    public function index()
    {
        $auth = $this->Auth->user('id');
        pr($auth);exit();
        $users = $this->paginate($this->Users);
        $this->set(compact('users'));
        $this->set('_serialize', ['users']);
    }

    public function view($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => ['Addresses', 'Carts', 'Orders', 'Packages', 'Products']
        ]);
        $this->set('user', $user);
        $this->set('_serialize', ['user']);
    }


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
}
