<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Auth\DefaultPasswordHasher;
use Cake\I18n\Time;
/**
 * Users Model
 *
 * @property \Cake\ORM\Association\HasMany $Addresses
 * @property \Cake\ORM\Association\HasMany $Carts
 * @property \Cake\ORM\Association\HasMany $Orders
 * @property \Cake\ORM\Association\HasMany $Packages
 * @property \Cake\ORM\Association\HasMany $Products
 *
 * @method \App\Model\Entity\User get($primaryKey, $options = [])
 * @method \App\Model\Entity\User newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\User[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\User findOrCreate($search, callable $callback = null)
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UsersTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('users');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Addresses', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Phones', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Notifications', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Carts', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Orders', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Packages', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Products', [
            'foreignKey' => 'user_id'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->allowEmpty('access_token');

        $validator
            ->requirePresence('first_name', 'create')
            ->notEmpty('first_name');

        $validator
            ->allowEmpty('last_name');

        $validator
            ->integer('phone_number')
            ->allowEmpty('phone_number');

        $validator
            ->allowEmpty('profile_image');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmpty('email');


        $validator
            ->requirePresence('password', 'create')
            ->notEmpty('password');

        $validator
            ->allowEmpty('device_type');

        $validator
            ->allowEmpty('device_token');

        $validator
            ->allowEmpty('user_ip_address');


        return $validator;
    }
    public function validationPassword(Validator $validator ) {
      $validator
          ->add('old_password','custom',[
              'rule'=> function($value, $context){
                  $user = $this->get($context['data']['id']);
                  if ($user) {
                      if ((new DefaultPasswordHasher)->check($value, $user->password))
                      {
                        return true;
                      }
                  }
                  return false;
              },
              'message'=>'The old password does not match the current password!',
          ])
          ->notEmpty('old_password');
      $validator
          ->add('password1', [
            'length' => [
              'rule' => ['minLength', 6],
              'message' => 'The password have to be at least 6 characters!',
            ]
          ])
          ->add('password1',[
            'match'=>[
              'rule'=> ['compareWith','password2'],
              'message'=>'The passwords does not match!',
            ]
          ])
          ->notEmpty('password1');
      $validator
          ->add('password2',[
            'length' => [
              'rule' => ['minLength', 6],
              'message' => 'The password have to be at least 6 characters!',
            ]
          ])
          ->add('password2',[
            'match'=>[
              'rule'=> ['compareWith','password1'],
              'message'=>'The passwords does not match!',
            ]
          ])
          ->notEmpty('password2');
        return $validator;
      }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->isUnique(['email']));
        $rules->add($rules->isUnique(['username']));

        return $rules;
    }

    public function emailCheck($email){
      $query = $this->find('all',[
          'conditions' => ['Users.email =' => $email]
      ]);
      return $query->count();
    }

    public function verifyUserEmail($verificationCode){
        $user=$this->Find('all')->where([
          'email_verification_code'=>$verificationCode
        ])->first();

        if($user){
            $query = $this->query();
            $query->update()
              ->set(['status' => 'Active','email_verification_code' => '','is_email_verified' => 1])
              ->where(['id' => $user->id])
              ->execute();
            return true;
        }else{
          return false;
        }
    }

    public function forgotPassword($email){
        $user=$this->Find('all')->where([
          'email'=>$email
        ])->first();
        if($user){
            $passwordVerificationCode = md5(Time::now().'-'.$user->email);
            $query = $this->query();
            $query->update()
              ->set(['password_verification_code' => $passwordVerificationCode])
              ->where(['id' => $user->id])
              ->execute();
            $user->password_verification_code = $passwordVerificationCode;
        }
        return $user;
    }

    public function checkEmailOrPhone($email_phone)
    {
        $user=$this->Find('all')->where([
          'email'=>$email_phone
        ])->first();
        if($user){
          $user['checkEmailOrPhone'] = 'email';
          $passwordVerificationCode = md5(Time::now().'-'.$user->email);
          $query = $this->query();
          $query->update()
            ->set(['password_verification_code' => $passwordVerificationCode])
            ->where(['id' => $user->id])
            ->execute();
          $user->password_verification_code = $passwordVerificationCode;
          return $user;
        }else{
          $user=$this->Find('all')->where([
            'phone'=>$email_phone
          ])->first();
          if($user){
              $user['checkEmailOrPhone'] = 'phone';
              return $user;
          }
        }
        $user['checkEmailOrPhone'] = 'false';
        return $user;
    }

    public function checkVerificationCode($verificationCode)
    {
      $user=$this->Find('all')->where([
        'phone_verification_code'=>$verificationCode
      ])->first();
      if($user){
          $passwordVerificationCode = md5(Time::now().'-'.$user->email);
          $query = $this->query();
          $query->update()
            ->set(['password_verification_code' => $passwordVerificationCode,'phone_verification_code' =>''])
            ->where(['id' => $user->id])
            ->execute();
          $user->password_verification_code = $passwordVerificationCode;
          return $user;
      }else{
          return false;
      }
    }

    public function checkDrivingLicenceAndSocialSecurity($data)
    {
        $user=$this->Find('all',['fields'=>['email','phone','id','driving_licence_number','social_security_number']])->where([
          'driving_licence_number'=>$data['driving_licence_number'],
          'social_security_number'=>$data['social_security_number']
        ])->first();
        return $user;
    }

    public function forgotPasswordSendVerificationLink($user_id){
      $user=$this->Find('all')->where([
        'id'=>$user_id
      ])->first();
      if($user){
        $passwordVerificationCode = md5(Time::now().'-'.$user->email);
        $query = $this->query();
        $query->update()
          ->set(['password_verification_code' => $passwordVerificationCode])
          ->where(['id' => $user->id])
          ->execute();
        $user->password_verification_code = $passwordVerificationCode;
        return $user;
      }
      return $user;
    }

    public function forgotPasswordSendVerificationCode($user_id){
      $user=$this->Find('all')->where([
        'id'=>$user_id
      ])->first();
      return $user;
    }

    public function userChangePassword($data)
    {
      $user=$this->Find('all')->where([
        'password_verification_code'=>$data['email']
      ])->first();
      if($user){
        $password = (new DefaultPasswordHasher)->hash($data['password']);
        $query = $this->query();
        $query->update()
          ->set(['password' =>$password,'password_verification_code'=>''])
          ->where(['id' => $user->id])
          ->execute();
        return true;
      }
      return false;
    }

    public function resetPassword($deviceToken){
        $user=$this->Find('all')->where([
          'device_token'=>$deviceToken
        ])->first();
        return $user;
    }

    public function resendVerificationLink($email){
        $user=$this->Find('all')->where([
          'email'=>$email
        ])->first();
        return $user;
    }
    public function getAdminLogin($data = array()){
        $admin = $this->find('all')->where([
            'email'=>$data['email'],
            'password'=>$data['password'],
            'users_role'=>'admin'
        ])->first();
        return $admin;
    }

    public function getUserDetails($id){
      $user=$this->Find('all')->where([
        'id'=>$id
      ])->first();
      return $user;
    }

    public function getUserDetailsForUpdate($id){
      $user=$this->Find('all',[
            'contain' => [
              'Addresses',
              'Phones'=>['fields'=>['id','phone','user_id','is_phone_verified']]
            ]
        ])
        ->where(['id'=>$id])
        ->first();
      return $user;
    }

    public function isMobileAvilable($phone,$user_id){
        if($this->find('all')->where(['phone'=>$phone,'id !='=>$user_id])->first()){
            return false;
        }else{
            return true;
        }
    }
    public function isMobileVerificationCodeAvilable($phone,$user_id,$code){
       if($this->find('all')->where(['id'=>$user_id,'phone_verification_code'=>$code])->first()){
            $query = $this->query();
            $query->update()
              ->set(['is_phone_verified' => 1,'phone_verification_code' => '','phone'=>$phone,])
              ->where(['id' => $user_id])
              ->execute();
            return true;
       }else{
           return false;
       }
    }

    public function drivingLicenceVerification($id,$data){
        $user=$this->Find('all')->where([
          'driving_licence_number'=>$data['driving_licence_number'],'social_security_number'=>$data['social_security_number']
        ])->first();
        if($user){
            return false;
        }else{
            $query = $this->query();
            $query->update()
              ->set(['driving_licence_number' =>$data['driving_licence_number'],'social_security_number' => $data['social_security_number'],'is_dl_verified' => 1,'is_ss_verified'=>1])
              ->where(['id' => $id])
              ->execute();
            return true;
        }
    }

    public function userUpdateProfile($id,$data){

        $query = $this->query();
        if(isset($data['address']['geometry'])){
          $lat = $data['address']['geometry']['location']['lat'];
          $lng = $data['address']['geometry']['location']['lng'];
          $address = $data['address']['formatted_address'];
          $query->update()
            ->set(['first_name' =>$data['first_name'],'last_name' => $data['last_name'],'lat'=>$lat,'lng'=>$lng,'address'=>$address])
            ->where(['id' => $id])
            ->execute();
        }else{
          $query->update()
            ->set(['first_name' =>$data['first_name'],'last_name' => $data['last_name']])
            ->where(['id' => $id])
            ->execute();
        }
        return true;
    }

    public function saveProfileImage($id,$profileImageName){
        $query = $this->query();
        $query->update()
          ->set(['profile_image' =>$profileImageName])
          ->where(['id' => $id])
          ->execute();
        return true;
    }

    public function getOldProfileImageName($id)
    {
        $user=$this->Find('all',['fields'=>['id','profile_image']])
                ->where(['id'=>$id])
                ->first();
        if(!empty($user->profile_image)){
            return $user->profile_image;
        }else {
            return false;
        }
    }

    public function userUpdateProfileDetails($data)
    {
      $query = $this->query();
      if(empty($data['address'])){
        $query->update()
          ->set(['first_name' =>$data['first_name'],'last_name' =>$data['last_name'],'social_security_number'=>$data['social_security_number'],'driving_licence_number'=>$data['driving_licence_number']])
          ->where(['id' => $data['user_id']])
          ->execute();
          return true;
      }else{
        $query->update()
          ->set(['first_name' =>$data['first_name'],'last_name' =>$data['last_name'],'social_security_number'=>$data['social_security_number'],'driving_licence_number'=>$data['driving_licence_number'],'address'=>$data['address'],'lat'=>$data['latLng']['lat'],'lng'=>$data['latLng']['lng']])
          ->where(['id' => $data['user_id']])
          ->execute();
          return true;
      }
      return false;
    }

    // For admin

    public function getUsersList($data)
    {
      if($data['type'] == 'pending_for_approval'){
        $user=$this->Find('all')
              ->where([
                      'role'=>'user',
                      'is_email_verified'=>1,
                      'is_approved'=>0,
                      'is_phone_verified'=>1,
                      'is_dl_verified'=>1,
                      'is_ss_verified'=>1,
                      'status' => 'Active',
                      'OR' => [
                                ['first_name LIKE' => "%".$data['search_text']."%"],
                                ['last_name LIKE' => "%".$data['search_text']."%"],
                                ['email LIKE' => "%".$data['search_text']."%"],
                                ['phone LIKE' => "%".$data['search_text']."%"],
                                ['address LIKE' => "%".$data['search_text']."%"],
                                ['social_security_number LIKE' => "%".$data['search_text']."%"],
                                ['driving_licence_number LIKE' => "%".$data['search_text']."%"]
                              ],
              ])
            ->toArray();
      }elseif($data['type'] == 'approved'){
          $user=$this->Find('all')
                ->where([
                        'role'=>'user',
                        'is_email_verified'=>1,
                        'is_approved'=>1,
                        'status' => 'Active',
                        'OR' => [
                                  ['first_name LIKE' => "%".$data['search_text']."%"],
                                  ['last_name LIKE' => "%".$data['search_text']."%"],
                                  ['email LIKE' => "%".$data['search_text']."%"],
                                  ['phone LIKE' => "%".$data['search_text']."%"],
                                  ['address LIKE' => "%".$data['search_text']."%"],
                                  ['social_security_number LIKE' => "%".$data['search_text']."%"],
                                  ['driving_licence_number LIKE' => "%".$data['search_text']."%"]
                                ],
                ])
              ->toArray();
      }else{
        $user=$this->Find('all')
              ->where([
                      'role'=>'user',
                      'is_email_verified'=>1,
                      'is_approved'=>0,
                      'is_dl_verified'=>0,
                      'is_ss_verified'=>0,
                      'status' => 'Active',
                      'OR' => [
                                ['first_name LIKE' => "%".$data['search_text']."%"],
                                ['last_name LIKE' => "%".$data['search_text']."%"],
                                ['email LIKE' => "%".$data['search_text']."%"],
                                ['phone LIKE' => "%".$data['search_text']."%"],
                                ['address LIKE' => "%".$data['search_text']."%"],
                                ['social_security_number LIKE' => "%".$data['search_text']."%"],
                                ['driving_licence_number LIKE' => "%".$data['search_text']."%"]
                              ],
              ])
            ->toArray();
      }
        return $user;
    }

    public function userApprovedByAdmin($id)
    {
        $query = $this->query();
        $query->update()
          ->set(['is_approved' =>1])
          ->where(['id' => $id])
          ->execute();
        return true;
    }
}
