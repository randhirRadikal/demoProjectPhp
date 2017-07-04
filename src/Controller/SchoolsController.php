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
class SchoolsController extends AppController
{
    public function initialize() {
        parent::initialize();
        $this->Auth->allow(['login','emailVerification','getSchoolDetails','add','forgotPassword','restPassword','resendVerificationLink']);
    }



    public function getSchoolDetails(){
        echo $id = $this->Auth->user('id');
        $data['message'] = 'Some problem in app. please reload the page.';
        $data['error_code'] = '1';
        $user = $this->Schools->get($id);
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
            $user = $this->Auth->identify();
            if(!$user){
              $this->set([
                  'error_code'=>'FAIL',
				  'error_message'=>'Invalid username or password',
                  '_serialize'=>['error_code','error_message']
              ]);
            }else{
				$this->set([
  				  'error_code'=>'SUCCESS',
				  'error_message'=>'School login successfully.',
  				  'data'=>[
  					  'token'=>  JWT::encode([
  						  'sub'=>$user['id'],
  						  'exp'=>time()+604800
  					  ],  Security::salt()),
  					  'name'=>$user['name'],
					  'id'=>$user['id']
  				  ],
  				  '_serialize'=>['error_code','error_message','data']
  			  ]);

            }
        }
    }

	public function add(){
        $user = $this->Schools->newEntity();
        $error_code = "FAIL";
        $error_message = "Fail";
        if ($this->request->is('post')) {
            $data = $this->request->data;
            if(!empty($data['email'])){
              $data['email_verification_code'] = md5($data['email']);
            }else{
              $data['email_verification_code']='';
            }
            //pr($data); exit;
            if($this->Schools->emailCheck($data['email']) == 0){
              $user = $this->Schools->patchEntity($user, $data);
              $mail_content=[];
              if ($this->Schools->save($user)) {
                  $to = $data['email'];
                  $mail_content['template_name'] = 'account_verification_success_email_template';
                  $mail_content['subject'] = 'School System - Verified account';
                  $mail_content['first_name'] = $data['name'];
                  $mail_content['account_verification_link'] = Router::url('/', TRUE) . "Schools/email_verification/" .$data['email_verification_code'];
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
