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
class CountriesController extends AppController
{
    public function initialize() {
        parent::initialize();
        $this->Auth->allow(['index','add','edit','delete','getCountriesList','getStatesList','getCitiesList']);
    }

     
    public function index()
    {
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

    public function add()
    {
        $user = $this->Users->newEntity();
        $res['error_message']='This is post method';
        $res['error_code']='1';
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
            $user = $this->Users->patchEntity($user, $data);
            //pr($user);exit;
            $mail_content=[];
            if ($this->Users->save($user)) {
                $to = $data['email'];
                $mail_content['template_name'] = 'account_verification_success_email_template';
                $mail_content['subject'] = 'ITIS4RENT - Verified account';
                $mail_content['first_name'] = $data['first_name'];
                $mail_content['account_verification_link'] = Router::url('/', TRUE) . "Users/email_verification/" .$data['email_verification_code'];
                $this->sent_email($to, $mail_content);
                $res['error_message']='The user has been saved.';
                $res['error_code']='0';
            } else {
                $res['error_message']='The user could not be saved, Please, try again.';
                $res['error_code']='1';
            }
        }
        echo json_encode($res);
        exit;
        //$this->set(compact('user'));
        //$this->set('_serialize', ['user']);
    }


    public function edit($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->data);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The user could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('user'));
        $this->set('_serialize', ['user']);
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }




}
