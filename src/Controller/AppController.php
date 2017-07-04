<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Network\Email\Email;
use Cake\ORM\TableRegistry;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');

		$this->loadComponent('Auth', [
			'authenticate' => [
				'Form' => [
					'userModel' => 'Schools',
					'fields' => ['username' => 'email', 'password' => 'password','is_active' => 1]
				],
				'Basic'=>[
					'userModel' => 'Schools',
					'fields' => ['username' => 'email', 'password' => 'password','is_active' => 1]
				]
			],
			'loginAction' => [
				'controller' => 'Pages',
				'action' => 'index',
				'home'
			],
			//'unauthorizedRedirect' => true,
			'storage' => 'Memory'
		]);
        $this->Auth->Allow(['getSiteUrl','__require_fields','sent_email','sendNotificationSMS']);
        /*
         * Enable the following components for recommended CakePHP security settings.
         * see http://book.cakephp.org/3.0/en/controllers/components/security.html
         */
        //$this->loadComponent('Security');
        //$this->loadComponent('Csrf');
    }

    /**
     * Before render callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     * @return \Cake\Network\Response|null|void
     */
    public function beforeRender(Event $event)
    {
        if (!array_key_exists('_serialize', $this->viewVars) &&
            in_array($this->response->type(), ['application/json', 'application/xml'])
        ) {
            $this->set('_serialize', true);
        }
        // if($this->Auth && $this->Auth->user()){
        //   $this->set('authUser',$this->Auth->user());
        // }else{
        //   $this->set('authUser',NULL);
        // }
    }

    public function sent_email($to=array(),$data=array()){
        //Data array should have following two fields...
        //$data['template_name']
        //$data['to'] = $to;
        //pr($data); exit;
        $sender='ConnectSO';
        $CakeEmail = new Email('default');
        $from = 'test@mobisolz.com';
        $CakeEmail->template($data['template_name'], 'default')
                    ->emailFormat('html')
                    ->to($to)
                    ->from([$from => $sender])
                    ->subject($data['subject'])
                    ->viewVars(compact('data'));
            if (@$CakeEmail->send()) {
                return true;
            }
        return false;
    }

    public function __require_fields($required){
        $empty_fields = [];
        foreach($required as $key=>$val){
            if($val == ''){
                $empty_fields[$key] = $val;
            }
        }
        return $empty_fields;
    }

    public function __require_fields_login($required){
        $empty_fields = [];
        foreach($required as $key=>$val){
            if($val == ''){
                $empty_fields[$key] = $val;
            }
        }
        return $empty_fields;
    }

    public function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function move_file($source,$destination){
        @rename($source, $destination);
    }

    public function __upload_file($pic,$target_path) {
        //if ($pic['type'] == 'image/jpeg' || $pic['type'] == 'image/png') {
            $ext = explode('.', $pic['name']);
            $l_name = uniqid(time()).$this->generateRandomString(). "." . end($ext);
            $path = WWW_ROOT . "img" . DS . $target_path;

            if (move_uploaded_file($pic['tmp_name'], $path . DS . $l_name)) {
                return $l_name;
            }
        //}
        return FALSE;
    }

}
