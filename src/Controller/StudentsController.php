<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Utility\Security;
use Cake\ORM\TableRegistry;
use Cake\I18n\Time;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class StudentsController extends AppController
{
    public function initialize() {
        parent::initialize();
        $this->Auth->allow(['index1']);
    }

	public function index(){

	}

	public function add(){
		$error_code = 'ERROR';
		$error_message = "This is a post method.";
		if($this->request->is('post')){
			$data = $this->request->data;
			$required['first_name'] = isset($data['first_name'])?$data['first_name']:'';
			$required['last_name'] = isset($data['last_name'])?$data['last_name']:'';
			$required['father_name'] = isset($data['father_name'])?$data['father_name']:'';
			$required['mother_name'] = isset($data['mother_name'])?$data['mother_name']:'';
			$required['email'] = isset($data['email'])?$data['email']:'';
			$required['phone'] = isset($data['phone'])?$data['phone']:'';
			$required['class_id'] = isset($data['class_id'])?$data['class_id']:'';
			$required['gender'] = isset($data['gender'])?$data['gender']:'';
			$required['password'] = isset($data['password'])?$data['password']:'';
			$required['section_id'] = isset($data['section_id'])?$data['section_id']:'';
			$required['address1'] = isset($data['address1'])?$data['address1']:'';
			$required['near_by'] = isset($data['near_by'])?$data['near_by']:'';
			$required['county_id'] = isset($data['county_id'])?$data['county_id']:'';
			$required['state_id'] = isset($data['state_id'])?$data['state_id']:'';
			$required['city_id'] = isset($data['city_id'])?$data['city_id']:'';
			$required['pincode'] = isset($data['pincode'])?$data['pincode']:'';
			$blank_field = $this->__require_fields($required);
            if (count($blank_field)>0){
                $error_code = 'ERROR';
                $error_message = 'Please enter required field.';
            }else{
				$address['address1'] = $data['address1'];
				$address['address2'] = isset($data['address2'])?$data['address2']:'';
				$address['near_by'] = $data['near_by'];
				$address['county_id'] = $data['county_id'];
				$address['state_id'] = $data['state_id'];
				$address['city_id'] = $data['city_id'];
				$address['pincode'] = $data['pincode'];
				$addresses = TableRegistry::get('addresses');
				$addressData = $addresses->newEntity($address);
				$result = $addresses->save($addressData);
				if($result){
					$addressId = $result->id;
					$data['middel_name'] = isset($data['middel_name'])?$data['middel_name']:'';
					$data['p_address_id'] = $addressId;
					$data['c_address_id'] = $addressId;
					$data['current_class_id'] = $data['class_id'];
					$data['admission_class_id'] = $data['class_id'];
					$data['current_section_id'] = $data['section_id'];
					$data['admission_section_id'] = $data['section_id'];
					$data['email_verification_code'] = md5($data['email'].'-'.$data['password'].'-'.Time::now());
					$data ['school_id'] = $this->Auth->user('id');
					$student = $this->Students->newEntity($data);
					$result = $this->Students->save($student);
					if($result){
						$error_code = 'SUCCESS';
		                $error_message = 'Fee added successfully.';
					}else{
						$addressData = $addresses->get($addressId);
						if($addresses->delete($addressData)){
							$error_code = 'ERROR';
			                $error_message = 'Some thing is worng, please try again later.';
						}else{
							$error_code = 'ERROR';
			                $error_message = 'Some thing is worng, please try again later.';
						}
					}
				}else{
					$error_code = 'ERROR';
					$error_message = 'Some thing is worng, please try again later.';
				}

			}
		}
		$this->set(["error_code"=>$error_code,
                    "error_message"=>$error_message,
                    '_serialize' => ['error_code','error_message']
                ]);
	}

	public function edit(){
		$error_code = 'ERROR';
		$error_message = "This is a post method.";
		if($this->request->is('post')){
			$data = $this->request->data;
			$required['id'] = isset($data['id'])?$data['id']:'';
			$required['student_id'] = isset($data['student_id'])?$data['student_id']:'';
			$required['fee'] = isset($data['fee'])?$data['fee']:'';
			$required['class_id'] = isset($data['class_id'])?$data['class_id']:'';
			$required['section_id'] = isset($data['section_id'])?$data['section_id']:'';
			$required['year'] = isset($data['year'])?$data['year']:'';
			$required['month'] = isset($data['month'])?$data['month']:'';
			$blank_field = $this->__require_fields($required);
            if (count($blank_field)>0){
                $error_code = 'ERROR';
                $error_message = 'Please enter required field.';
            }else{
				$fees = $this->Fees->get($data['id']);
				if($fees){
					$fees->student_id = $data['student_id'];
					$fees->fee = $data['fee'];
					$fees->class_id = $data['class_id'];
					$fees->section_id = $data['section_id'];
					$fees->year = $data['year'];
					$fees->month = $data['month'];
					$fees->modefied_by = $this->Auth->user('id');
					$fees->modefied = Time::now();
					if($this->Fees->save($fees)){
						$error_code = 'SUCCESS';
		                $error_message = 'Fee update successfully.';
					}else{
						$error_code = 'ERROR';
		                $error_message = 'Some thing is worng, please try again later.';
					}
				}else{
					$error_code = 'ERROR';
					$error_message = 'Some thing is worng, please try again later.';
				}
			}
		}
		$this->set(["error_code"=>$error_code,
                    "error_message"=>$error_message,
                    '_serialize' => ['error_code','error_message']
                ]);
	}

	public function delete(){
		$error_code = 'ERROR';
		$error_message = "This is a post method.";
		if($this->request->is('post')){
			$data = $this->request->data;
			$required['id'] = isset($data['id'])?$data['id']:'';
			$blank_field = $this->__require_fields($required);
            if (count($blank_field)>0){
                $error_code = 'ERROR';
                $error_message = 'Please enter required field.';
            }else{
				$fees = $this->Fees->get($data['id']);
				if($fees){
					if($this->Fees->delete($fees)){
						$error_code = 'SUCCESS';
		                $error_message = 'Fee delete successfully.';
					}else{
						$error_code = 'ERROR';
		                $error_message = 'Some thing is worng, please try again later.';
					}
				}else{
					$error_code = 'ERROR';
					$error_message = 'Some thing is worng, please try again later.';
				}
			}
		}
		$this->set(["error_code"=>$error_code,
                    "error_message"=>$error_message,
                    '_serialize' => ['error_code','error_message']
                ]);
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

}
