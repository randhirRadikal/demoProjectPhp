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
class AddressesController extends AppController
{
    public function initialize() {
        parent::initialize();
        $this->Auth->allow(['index','getCountriesList','getStatesList','getCitiesList']);
    }

    public function getCountriesList()
    {
      $countries = TableRegistry::get('countries');
      $countries = $countries->find('all')->where(['status'=>'Active']);
      $this->set(compact('countries'));
      $this->set('_serialize', ['countries']);
    }
    public function getStatesList($country_id)
    {
      $states = TableRegistry::get('states');
      $states = $states->find('all')->where(['country_id'=>$country_id]);
      $this->set(compact('states'));
      $this->set('_serialize', ['states']);
    }
    public function getCitiesList($state_id)
    {
      $cities = TableRegistry::get('cities');
      $cities = $cities->find('all')->where(['state_id'=>$state_id]);
      $this->set(compact('cities'));
      $this->set('_serialize', ['cities']);
    }
    public function index()
    {
        $users = $this->paginate($this->Users);

        $this->set(compact('users'));
        $this->set('_serialize', ['users']);
    }

    public function getAddressDetails()
    {
        $error_message='This is post method';
        $error_code='FAIL';
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $result = $this->Addresses->get($data['address_id']);
            if($result){
              $error_code = 'SUCCESS';
              $error_message = 'Address detials successfully.';
              $data =$result;
            }else {
              $error_code = 'FAIL';
              $error_message = 'Some Problem.';
            }
        }
        $this->set(["error_code"=>$error_code,
                    "error_message"=>$error_message,
                    "data"=>!empty($data)?$data:array(),
                    '_serialize' => ['error_code','error_message','data']
                ]);
    }


    public function deleteAddress()
    {
        $res['error_message']='This is post method';
        $res['error_code']='FAIL';
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $entity = $this->Addresses->get($data['address_id']);
            if($this->Addresses->delete($entity)){
              $res['error_code'] = 'SUCCESS';
              $res['error_message'] = 'Address delete successfully.';
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
        $error_message='This is post method';
        $error_code='FAIL';
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $required=[];
            $required['address']  = isset($data['address'])?$data['address']:'';
            $required['country']  = isset($data['country'])?$data['country']:'';
            $required['state']    = isset($data['state'])?$data['state']:'';
            $required['city']     = isset($data['city'])?$data['city']:'';
            $required['pincode']  = isset($data['pincode'])?$data['pincode']:'';
            $blank_field = $this->__require_fields($required);
            if (count($blank_field)>0){
                $error_code = 'FAIL';
                $error_message = 'Please enter required field.';
            }else{
                if(!empty($data['latLng'])){
                  $data['lat'] = $data['latLng']['lat'];
                  $data['lng'] = $data['latLng']['lng'];
                  unset($data['latLng']);
                }else{
                  $data['lat'] = '0.000000';
                  $data['lng'] = '0.000000';
                  unset($data['latLng']);
                }
                $data['address1'] = isset($data['address1'])?$data['address1']:'';
                $data['address2'] = isset($data['address2'])?$data['address2']:'';
                $data['landmark'] = isset($data['landmark'])?$data['landmark']:'';
                $data['user_id'] = $this->Auth->user('id');
                if($data['type'] == 'Other'){
                  $data['type'] = $data['type1'];
                }
                //pr($data);exit;
                $address = $this->Addresses->newEntity($data);
                if($this->Addresses->save($address)){
                  $error_code = 'SUCCESS';
                  $error_message = 'Address add successfully.';
                }
            }
            $this->set(["error_code"=>$error_code,
                        "error_message"=>$error_message,
                        '_serialize' => ['error_code','error_message']
                    ]);

        }
    }



}
