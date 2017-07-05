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
class CommonsController extends AppController
{
    public function initialize() {
        parent::initialize();
        $this->Auth->allow(['index1']);
    }

	public function index(){

	}

	  public function getClassList()
    {
      $school_id=$this->Auth->user('id');
      $classess = TableRegistry::get('classes')->where(['school_id'=>$school_id]);
      $class = $classess->find('all');
      $this->set(compact('class'));
      $this->set('_serialize', ['class']);
    }

    public function getSectionList($class_id)
    {
      $school_id=$this->Auth->user('id');
      $sections = TableRegistry::get('sections');
      $section = $sections->find('all')->where(['class_id'=>$class_id,'school_id'=>$school_id]);
      $this->set(compact('section'));
      $this->set('_serialize', ['section']);
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
