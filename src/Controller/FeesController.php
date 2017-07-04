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
class FeesController extends AppController
{
    public function initialize() {
        parent::initialize();
        $this->Auth->allow(['index']);
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
			$required['year'] = isset($data['year'])?$data['year']:'';
			$required['month'] = isset($data['month'])?$data['month']:'';
			$blank_field = $this->__require_fields($required);
            if (count($blank_field)>0){
                $error_code = 'ERROR';
                $error_message = 'Please enter required field.';
            }else{
				$fees = $this->Fees->newEntity();
				$fees->school_id = $this->Auth->user('id');
				$fees->student_id = $data['student_id'];
				$fees->fee = $data['fee'];
				$fees->class_id = $data['class_id'];
				$fees->section_id = $data['section_id'];
				$fees->year = $data['year'];
				$fees->month = $data['month'];
				$fees->created = Time::now();
				$fees->created_by = $this->Auth->user('id');
				$fees->modefied = Time::now();
				if($this->Fees->save($fees)){
					$error_code = 'SUCCESS';
	                $error_message = 'Fee added successfully.';
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
