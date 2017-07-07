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
class PartyContactsController extends AppController
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


    public function deleteAddress(){
        $res['error_message']='This is post method';
        $res['error_code']='FAIL';
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $partyAddress = TableRegistry::get('PartyAddresses');
            $entity = $partyAddress->get($data['party_address_id']);
            if($partyAddress->delete($entity)){
              $entity = $this->PartyContacts->get($data['party_contact_id']);
              if($this->PartyContacts->delete($entity)){
                $res['error_code'] = 'SUCCESS';
                $res['error_message'] = 'Address delete successfully.';
              }else {
                $res['error_code'] = 'FAIL';
                $res['error_message'] = 'Some Problem.';
              }
            }else{
              $res['error_code'] = 'FAIL';
              $res['error_message'] = 'Some Problem.';
            }
        }
        $this->set(["error_code"=>$res['error_code'],
                    "error_message"=>$res['error_message'],
                    '_serialize' => ['error_code','error_message']
                ]);
    }

    public function getTypeOptionsAddress()
    {
      $result=$this->getAppCodeStatusId('CONTACT_TYPE','ADDRESS');
      if($result){
        $error_message='Successfully.';
        $error_code='SUCCESS';
      }else{
        $error_message='Some Error';
        $error_code='FAIL';
      }
      $this->set(["error_code"=>$error_code,
                  "error_message"=>$error_message,
                  'result'=>$result,
                  '_serialize' => ['error_code','error_message','result']
              ]);
    }

    public function getTypeOptionsPhone()
    {
      $result=$this->getAppCodeStatusId('CONTACT_TYPE','PHONE');
      if($result){
        $error_message='Successfully.';
        $error_code='SUCCESS';
      }else{
        $error_message='Some Error';
        $error_code='FAIL';
      }
      $this->set(["error_code"=>$error_code,
                  "error_message"=>$error_message,
                  'result'=>$result,
                  '_serialize' => ['error_code','error_message','result']
              ]);
    }

    public function addAddress()
    {
        $error_message='This is post method';
        $error_code='FAIL';
        if ($this->request->is('post')) {
            $data = $this->request->data;
            $required=[];
            $required['address_line_1']  = isset($data['address_line_1'])?$data['address_line_1']:'';
            $required['county_name']  = isset($data['county_name'])?$data['county_name']:'';
            $required['city']     = isset($data['city'])?$data['city']:'';
            $required['zip_code']  = isset($data['zip_code'])?$data['zip_code']:'';
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
                $data['party_id'] = $this->Auth->user('id');
                $data['address_line_2'] = isset($data['address_line_2'])?$data['address_line_2']:'';
                $data['dateTime'] = Time::now();
                $partyContacts = [
                    'party_id'=>$data['party_id'],
                    'contact_type_id'=>$data['contact_type_id'],
                    'party_contact_status'=>57,
                    'is_verified'=>'N',
                    'is_primary'=>'N',
                    'preferred_flag'=>'N',
                    'preferred_from_time'=>$data['dateTime'],
                    'created_by'=>$data['party_id'],
                    'created'=>$data['dateTime'],
                    'party_address'=>[
                        'address_line_1'=>$data['address_line_1'],
                        'address_line_2'=>$data['address_line_2'],
                        'city'=>$data['city'],
                        'zip_code'=>$data['zip_code'],
                        'county_name'=>$data['county_name'],
                        'state_cd'=>22,
                        'state_cd'=>23,
                        'latitude'=>$data['lat'],
                        'longitude'=>$data['lng'],
                        'created_by'=>$data['party_id'],
                        'created'=>$data['dateTime']
                    ]
                ];
                $entity = $this->PartyContacts->newEntity($partyContacts,['associated' => ['PartyAddresses']]);
                if($this->PartyContacts->save($entity)){
                  $error_code = 'SUCCESS';
                  $error_message = 'Address add successfully.';
                }
            }
          }
            $this->set(["error_code"=>$error_code,
                        "error_message"=>$error_message,
                        '_serialize' => ['error_code','error_message']
                    ]);

        }

        public function editAddress()
        {
            $error_message='This is post method';
            $error_code='FAIL';
            if ($this->request->is('post')) {
                $data = $this->request->data;
                $partyContacts = $this->PartyContacts->edit_address($data);
                if($partyContacts){
                  $error_message='Address update successfully.';
                  $error_code='SUCCESS';
                }else{
                  $error_message='Some';
                  $error_code='FAIL';
                }

            }
            $this->set(["error_code"=>$error_code,
                        "error_message"=>$error_message,
                        '_serialize' => ['error_code','error_message']
                    ]);
        }

        public function addPhone()
        {
            $error_message='This is post method';
            $error_code='FAIL';
            if ($this->request->is('post')) {
                $data = $this->request->data;
                $required['phone_number']  = isset($data['phone_number'])?$data['phone_number']:'';
                $required['contact_type_id']  = isset($data['contact_type_id'])?$data['contact_type_id']:'';
                $blank_field = $this->__require_fields($required);
                if (count($blank_field)>0){
                    $error_code = 'FAIL';
                    $error_message = 'Please enter required field.';
                }else{
                    $data['party_id'] = $this->Auth->user('id');
                    $data['dateTime'] = Time::now();
                    if($this->PartyContacts->addPhone($data)){
                      $error_code = 'SUCCESS';
                      $error_message = 'Phone add successfully.';
                    }else{
                      $error_code = 'FAIL';
                      $error_message = 'This phone no is assosicated with other user.';
                    }
                }
              }
                $this->set(["error_code"=>$error_code,
                            "error_message"=>$error_message,
                            '_serialize' => ['error_code','error_message']
                        ]);

            }

            public function sendMobileVerificationCode(){
                $error_code = "FAIL";
                $error_message = "Fail";
                if ($this->request->is('post')) {
                    $data = $this->request->data;
                    if(isset($data['phone_number'])){
                        $data['party_id'] = $this->Auth->user('id');
                        $data['dateTime'] = Time::now();
                        $data['phone_verification_code'] = $this->generateRandomStringPhone(6);
                        $to =$data['phone_number'];
                        $message = "Your verification code for ITIS4RENT is: ".$data['phone_verification_code'];
                        if($this->sendVerification($to,$message)){
                          $users = $this->PartyContacts->sendMobileVerificationCode($data);
                          $error_code = "SUCCESS";
                          $error_message = "Verification code generated.";
                        }else{
                          $error_code = "FAIL";
                          $error_message = "Currently unable to send verification code to your number. Try again later.";
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

            public function verifyPhoneVerification()
            {
                $error_code = "FAIL";
                $error_message = "Fail";
                if ($this->request->is('post')) {
                    $data = $this->request->data;
                    $required['phone_verification_code']  = isset($data['phone_verification_code'])?$data['phone_verification_code']:'';
                    $blank_field = $this->__require_fields($required);
                    if (count($blank_field)>0){
                        $error_code = 'FAIL';
                        $error_message = 'Please enter required field.';
                    }else{
                      if($this->PartyContacts->verifyPhoneVerification($data)){
                        $error_code = "SUCCESS";
                        $error_message = "Phone verified successfully.";
                      }else{
                        $error_code = "FAIL";
                        $error_message = "Invalid sms verification code";
                      }
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
                    $data['party_id'] = $this->Auth->user('id');
                    if($this->PartyContacts->changePhoneNumberToPrimary($data)){
                        $error_code = "SUCCESS";
                        $error_message = "Add phone number to primary number";
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


}
