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
class FeeStructuresController extends AppController
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
			$required['student_id'] = isset($data['student_id'])?$data['student_id']:'';
			$required['fee'] = isset($data['fee'])?$data['fee']:'';
			$required['class_id'] = isset($data['class_id'])?$data['class_id']:'';
			$required['section_id'] = isset($data['section_id'])?$data['section_id']:'';
			$blank_field = $this->__require_fields($required);
            if (count($blank_field)>0){
                $error_code = 'ERROR';
                $error_message = 'Please enter required field.';
            }else{
				$feeStructure = $this->FeeStructures->newEntity();
				$feeStructure->school_id = $this->Auth->user('id');
				//$feeStructure->student_id = $data['student_id'];
				$feeStructure->fee = $data['fee'];
				$feeStructure->class_id = $data['class_id'];
				$feeStructure->section_id = $data['section_id'];
				$feeStructure->created = Time::now();
				$feeStructure->created_by = $this->Auth->user('id');
				if($this->FeeStructures->save($feeStructure)){
					$error_code = 'SUCCESS';
	                $error_message = 'Fee structure added successfully.';
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
			$blank_field = $this->__require_fields($required);
            if (count($blank_field)>0){
                $error_code = 'ERROR';
                $error_message = 'Please enter required field.';
            }else{
				$feeStructure = $this->FeeStructures->get($data['id']);
				if($feeStructure){
					$feeStructure->fee = $data['fee'];
					$feeStructure->class_id = $data['class_id'];
					$feeStructure->section_id = $data['section_id'];
					if($this->FeeStructures->save($feeStructure)){
						$error_code = 'SUCCESS';
		                $error_message = 'Fee structure update successfully.';
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
				$feeStructure = $this->FeeStructures->get($data['id']);
				if($feeStructure){
					if($this->FeeStructures->delete($feeStructure)){
						$error_code = 'SUCCESS';
		                $error_message = 'Fee structure delete successfully.';
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

}
