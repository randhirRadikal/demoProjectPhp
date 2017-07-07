<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
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
class PartiesTable extends Table
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

        $this->table('parties');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasOne('People', [
            'foreignKey' => 'party_id'
        ]);
        $this->hasMany('PartyContacts', [
            'foreignKey' => 'party_id'
        ]);
        $this->hasMany('PartySecurities', [
            'foreignKey' => 'party_id'
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

    public function addParty($data,$dateTime){
        $passwordSecurityStatus = $this->getAppCodeStatusId('SECURITY_INFO_TYPE','APP_SECURITY','ENC_PWD');
        $EmailSecurityStatus = $this->getAppCodeStatusId('SECURITY_INFO_TYPE','EMAIL_VERIFICATION','EMAIL_VER_TOKEN');
        $partyStatus = $this->getAppCodeStatusId('PARTY_STATUS','STATUS','INCOMPLETE');
        $partyTypeId = $this->getAppCodeStatusId('PARTY_TYPE','INDIVIDUAL','PERSON');
        $contactTypeStatusEmail = $this->getAppCodeStatusId('CONTACT_TYPE','EMAIL','OTHER_EMAIL');
        $contactTypeStatusAddress = $this->getAppCodeStatusId('CONTACT_TYPE','ADDRESS','BILLING_ADDRESS');
        $contactStatus = $this->getAppCodeStatusId('PARTY_STATUS','STATUS','COMPLETE');
        $stateCD = $this->getAppCodeStatusId('PARTY_ADDRESS','STATE_CD','IL');
        $countryCD = $this->getAppCodeStatusId('PARTY_ADDRESS','COUNTRY_CD','USA');
        $passwordEncrypted = (new DefaultPasswordHasher)->hash($data['c_password']);

        $party = [
          'party_status'=>$partyStatus,
          'party_type_id'=>$partyTypeId,
          'party_handle'=>$data['email'],
          'created_by'=>0,
          'created'=>$dateTime,
        ];
        $partiesTable = TableRegistry::get('Parties');
        $entity = $partiesTable->newEntity($party);
        $party = $partiesTable->save($entity);
        $party_id=$party->id;
        if($party){
          $people = [
              'party_id' =>$party_id,
              'first_name'=>$data['first_name'],
              'middle_initial'=>$data['middle_name'],
              'last_name'=>$data['last_name'],
              'created_by'=>$party_id,
              'created'=>$dateTime
          ];
          $peoplesTable = TableRegistry::get('People');
          $entity = $peoplesTable->newEntity($people);
          $peoplesTable->save($entity);

          $partyContacts = [
              'party_id' =>$party_id,
              'contact_type_id'=>7,
              'party_contacts_status'=>57,
              'is_verified'=>'N',
              'is_primary'=>'Y',
              'preferred_flag'=>'Y',
              'preferred_from_time'=>$dateTime,
              'created_by'=>$party_id,
              'created'=>$dateTime
          ];
          $partyContactsTable = TableRegistry::get('PartyContacts');
          $entity = $partyContactsTable->newEntity($partyContacts);
          $PartyContacts = $partyContactsTable->save($entity);
          $party_contact_id = $PartyContacts->id;

          $partyEmail = [
              'party_contact_id' =>$party_contact_id,
              'email_address'=>$data['email'],
              'email_verification_code'=>$data['email_verification_code'],
              'created_by'=>$party_id,
              'created'=>$dateTime
          ];
          $partyEmailsTable = TableRegistry::get('PartyEmails');
          $entity = $partyEmailsTable->newEntity($partyEmail);
          $partyEmailsTable->save($entity);

          $partyContacts = [
              'party_id' =>$party_id,
              'contact_type_id'=>12,
              'party_contacts_status'=>57,
              'is_verified'=>'N',
              'is_primary'=>'Y',
              'preferred_flag'=>'Y',
              'preferred_from_time'=>$dateTime,
              'created_by'=>$party_id,
              'created'=>$dateTime
          ];
          $partyContactsTable = TableRegistry::get('PartyContacts');
          $entity = $partyContactsTable->newEntity($partyContacts);
          $PartyContacts = $partyContactsTable->save($entity);
          $party_contact_id = $PartyContacts->id;

          $partyAddress = [
              'party_contact_id' =>$party_contact_id,
              'address_line_1'=>$data['address_line_1'],
              'address_line_2'=>$data['address_line_2'],
              'city'=>$data['city'],
              'zipcode'=>$data['zipcode'],
              'county_name'=>$data['country'],
              'state_cd'=>22,
              'country_cd'=>23,
              'latitude'=>$data['latLng']['lat'],
              'longitude'=>$data['latLng']['lng'],
              'created_by'=>$party_id,
              'created'=>$dateTime
          ];
          $partyAddressesTable = TableRegistry::get('PartyAddresses');
          $entity = $partyAddressesTable->newEntity($partyAddress);
          $partyAddressesTable->save($entity);

          $partySecurities = [
              'party_id' =>$party_id,
              'party_security_status'=>50,
              'is_verified'=>'Y',
              'created_by'=>$party_id,
              'created'=>$dateTime
          ];
          $partySecuritiesTable = TableRegistry::get('PartySecurities');
          $entity = $partySecuritiesTable->newEntity($partySecurities);
          $PartySecurity = $partySecuritiesTable->save($entity);
          $party_security_id = $PartySecurity->id;

          $partySecurityInfos = [
              'party_security_id' =>$party_security_id,
              'security_info_type_id'=>50,
              'security_info_value'=>$passwordEncrypted,
              'security_info_verified'=>'Y',
              'created_by'=>$party_id,
              'created'=>$dateTime
          ];
          $partySecurityInfosTable = TableRegistry::get('PartySecurityInfos');
          $entity = $partySecurityInfosTable->newEntity($partySecurityInfos);
          if($partySecurityInfosTable->save($entity)){
            return true;
          }
        }
        return false;
    }

    public function emailCheck($email){
      $partyEmailsTable = TableRegistry::get('PartyEmails');
      $party=$partyEmailsTable->Find('all')->where([
        'email_address'=>$email
      ])->count();
      return $party;
    }

    public function verifyPartiesEmail($verificationCode)
    {
      $partyEmailsTable = TableRegistry::get('PartyEmails');
      $party=$partyEmailsTable->Find('all')->where([
        'email_verification_code'=>$verificationCode
      ])->first();

      if($party){
          $partyContactsTable = TableRegistry::get('PartyContacts');
          $query = $partyEmailsTable->query();
          $query->update()
            ->set(['email_verification_code' => ''])
            ->where(['id' => $party->id])
            ->execute();
          $query = $partyContactsTable->query();
          $query->update()
            ->set(['is_verified' => 'Y'])
            ->where(['id' => $party->party_contact_id])
            ->execute();
          return true;
      }else{
        return false;
      }
    }

    public function checkEmailOrPhone($email_phone)
    {
        $partyEmailsTable = TableRegistry::get('PartyContacts');
        $party=$partyEmailsTable->Find('all',['contain'=>'PartyEmails'])->where([
          'PartyEmails.email_address'=>$email_phone
        ])->first();
        if($party){
            $party['checkEmailOrPhone'] = 'email';
            $dateTime = Time::now();
            $passwordVerificationCode = md5($dateTime.'-'.$party->email);
            $security_info_type_id = $this->getAppCodeStatusId('SECURITY_INFO_TYPE','CHANGE_PASSWORD','PWD_VER_TOKEN');
            $partySecurities = [
                'party_id' =>$party->party_id,
                'party_security_status'=>$security_info_type_id,
                'is_verified'=>'Y',
                'created_by'=>$party->party_id,
                'created'=>$dateTime,
                'party_security_info'=>[
                      'security_info_type_id'=>$security_info_type_id,
                      'security_info_value'=>$passwordVerificationCode,
                      'security_info_verified'=>'Y',
                      'created_by'=>$party->party_id,
                      'created'=>$dateTime
                  ]
            ];
            $partySecuritiesTable = TableRegistry::get('PartySecurities');
            $entity = $partySecuritiesTable->newEntity($partySecurities,['associated' => ['PartySecurityInfos']]);
            if($partySecuritiesTable->save($entity)){
              $party->password_verification_code = $passwordVerificationCode;
              return $party;
            }
            return false;
        }else{
          $partyPhonesTable = TableRegistry::get('PartyContacts');
          $party=$partyPhonesTable->Find('all')
          ->where(['PartyPhones.phone_number'=>$email_phone])
          ->contain(['PartyPhones'])
          ->first();
          if($party){
              $party['checkEmailOrPhone'] = 'phone';
              return $party;
          }
        }
        $party['checkEmailOrPhone'] = 'false';
        return $party;
    }

    public function sendMobileVerificationCode($data){
      $party_security_status = $this->getAppCodeStatusId('SECURITY_INFO_TYPE','PHONE_VERIFICATION','PHONE_VER_TOKEN');
      $partySecurities = [
          'party_id' =>$data['party_id'],
          'party_security_status'=>$party_security_status,
          'is_verified'=>'N',
          'created_by'=>$data['party_id'],
          'created'=>$data['dateTime'],
          'party_security_info'=>[
                  'security_info_type_id'=>$party_security_status,
                  'security_info_value'=>$data['phone_verification_code'],
                  'security_info_verified'=>'N',
                  'created_by'=>$data['party_id'],
                  'created'=>$data['dateTime']
              ]
      ];
      $partySecuritiesTable = TableRegistry::get('PartySecurities');
      $entity = $partySecuritiesTable->newEntity($partySecurities,['associated' => ['PartySecurityInfos']]);
      if($partySecuritiesTable->save($entity)){
        return true;
      }
      return false;
    }

    public function checkVerificationCode($verification_code)
    {
      $partySecurityTable = TableRegistry::get('PartySecurities');
      $partySecurity=$partySecurityTable->Find('all')
      ->where(['PartySecurityInfos.security_info_value'=>$verification_code,'PartySecurityInfos.security_info_type_id'=>56,'PartySecurityInfos.security_info_verified'=>'N'])
      ->contain(['PartySecurityInfos'])
      ->first();
      if($partySecurity){
        $party_security_status = $this->getAppCodeStatusId('SECURITY_INFO_TYPE','CHANGE_PASSWORD','PWD_VER_TOKEN');
        $data['dateTime'] = Time::now();
        $changePasswordToken = md5($data['dateTime'].'-'.$partySecurity->party_id.'-'.$party_security_status);
        $partySecurities = [
            'party_id' =>$partySecurity->party_id,
            'party_security_status'=>$party_security_status,
            'is_verified'=>'Y',
            'created_by'=>$partySecurity->party_id,
            'created'=>$data['dateTime'],
            'party_security_info'=>[
                    'security_info_type_id'=>$party_security_status,
                    'security_info_value'=>$changePasswordToken,
                    'security_info_verified'=>'Y',
                    'created_by'=>$partySecurity->party_id,
                    'created'=>$data['dateTime']
                ]
        ];
        $partySecuritiesTable = TableRegistry::get('PartySecurities');
        $entity = $partySecuritiesTable->newEntity($partySecurities,['associated' => ['PartySecurityInfos']]);
        if($partySecuritiesTable->save($entity)){
          $partySecurity->changePasswordToken = $changePasswordToken;
          return $partySecurity;
        }
      }
      return false;
    }

    public function userChangePassword($data)
    {
      $partySecurityTable = TableRegistry::get('PartySecurities');
      $security_info_type_id = $this->getAppCodeStatusId('SECURITY_INFO_TYPE','CHANGE_PASSWORD','PWD_VER_TOKEN');
      $partySecurity=$partySecurityTable->Find('all')
      ->where(['PartySecurityInfos.security_info_value'=>$data['email'],'PartySecurityInfos.security_info_type_id'=>$security_info_type_id,'PartySecurityInfos.security_info_verified'=>'Y'])
      ->contain(['PartySecurityInfos'])
      ->first();
      if($partySecurity){
        $party_security_status = $this->getAppCodeStatusId('SECURITY_INFO_TYPE','APP_SECURITY','ENC_PWD');
        $partySecurity1=$partySecurityTable->Find('all')
        ->where([
          'PartySecurities.party_id'=>$partySecurity->party_id,'PartySecurities.is_verified'=>'Y','PartySecurities.party_security_status'=>$party_security_status
        ])
        ->contain(['PartySecurityInfos'])
        ->first();
        if($partySecurity1){
          $password = (new DefaultPasswordHasher)->hash($data['password']);
          $partyContactsTable = TableRegistry::get('PartySecurityInfos');
          $query = $partyContactsTable->query();
          $query->update()
            ->set(['security_info_value' => $password])
            ->where(['id' => $partySecurity1->party_security_info->id])
            ->execute();
            $partySecurities = [
                'is_verified'=>'N',
                'party_security_info'=>[
                        'security_info_value'=>'',
                        'security_info_verified'=>'N'
                    ]
            ];
          $entity = $partySecurityTable->patchEntity($partySecurity, $partySecurities, ['associated' => ['PartySecurityInfos']]);
          $partySecurityTable->save($entity);
          return true;
        }
      }
      return false;
    }

    public function checkDrivingLicenceAndSocialSecurity($data)
    {
      $party_security_status1 = $this->getAppCodeStatusId('SECURITY_INFO_TYPE','PII','SSN');
      $party_security_status2 = $this->getAppCodeStatusId('SECURITY_INFO_TYPE','GOVT_ID','DLNUM');
      $partySecurityTable = TableRegistry::get('PartySecurities');
      $security_info_type_id = $this->getAppCodeStatusId('SECURITY_INFO_TYPE','CHANGE_PASSWORD','PWD_VER_TOKEN');
      $partySecurity=$partySecurityTable->Find('all')
          ->where(['PartySecurities.party_security_status'=>$party_security_status1])
          ->orwhere(['PartySecurities.party_security_status'=>$party_security_status2])
          ->where(['PartySecurityInfos.security_info_value'=>$data['driving_licence_number']])
          ->orwhere(['PartySecurityInfos.security_info_value'=>$data['social_security_number']])
          ->contain(['PartySecurityInfos'])
          ->toArray();
          if(sizeof($partySecurity) == 2){
            $result = false;
            if($partySecurity[0]['party_security_status'] == $party_security_status1 && $partySecurity[1]['party_security_status'] == $party_security_status2){
              $result = true;
            }else if($partySecurity[0]['party_security_status'] == $party_security_status2 && $partySecurity[1]['party_security_status'] == $party_security_status1){
              $result = true;
            }
            if($result){
              $query = $this->find('all')
                      ->where(['Parties.id' => $partySecurity[0]['party_id']])
                      ->contain(['People', 'PartyContacts'=>['PartyPhones']])
                      ->first();
              $party = $query->toArray();
              pr($party); exit;
            }
            exit;
          }
          return false;
    }





    public function getUserDetailsForUpdate($id)
    {
          $query = $this->find('all')
                  ->where(['Parties.id' => $id])
                  ->contain(['People', 'PartyContacts'=>['PartyEmails','PartyAddresses','PartyPhones'],'PartySecurities'=>['PartySecurityInfos']])
                  ->first();
          $party = $query->toArray();
          if($party){
            $phone=[];
            $address=[];
            $email=[];
            $security=[];
            $party['person']['social_security_number']='';
            $party['person']['driving_licence_number']='';
            foreach($party['party_contacts'] as $key=>$val){
                if(!empty($val['party_phone'])){
                  $val['party_phone']['contact_type_name'] = $this->getAppCodeStatus($val['contact_type_id']);
                  $val['party_phone']['is_verified'] = $val['is_verified'];
                  $val['party_phone']['is_primary'] = $val['is_primary'];
                  $val['party_phone']['contact_type_id'] = $val['contact_type_id'];
                  $val['party_phone']['party_contact_status'] = $val['party_contact_status'];
                  $val['party_phone']['preferred_flag'] = $val['preferred_flag'];
                  $val['party_phone']['preferred_from_time'] = $val['preferred_from_time'];
                  $phone[] = $val['party_phone'];
                }
                if(!empty($val['party_address'])){
                  $val['party_address']['contact_type_name'] = $this->getAppCodeStatus($val['contact_type_id']);
                  $val['party_address']['is_verified'] = $val['is_verified'];
                  $val['party_address']['is_primary'] = $val['is_primary'];
                  $val['party_address']['contact_type_id'] = $val['contact_type_id'];
                  $val['party_address']['party_contact_status'] = $val['party_contact_status'];
                  $val['party_address']['preferred_flag'] = $val['preferred_flag'];
                  $val['party_address']['preferred_from_time'] = $val['preferred_from_time'];
                  $address[] = $val['party_address'];

                }
                if(!empty($val['party_email'])){
                  $val['party_email']['contact_type_name'] = $this->getAppCodeStatus($val['contact_type_id']);
                  $val['party_email']['is_verified'] = $val['is_verified'];
                  $val['party_email']['is_primary'] = $val['is_primary'];
                  $val['party_email']['contact_type_id'] = $val['contact_type_id'];
                  $val['party_email']['party_contact_status'] = $val['party_contact_status'];
                  $val['party_email']['preferred_flag'] = $val['preferred_flag'];
                  $val['party_email']['preferred_from_time'] = $val['preferred_from_time'];
                  $email[] = $val['party_email'];
                }
              }
              foreach($party['party_securities'] as $key=>$val){
                if($val['party_security_status'] == 44){
                    $party['person']['social_security_number'] = $val['party_security_info']['security_info_value'];
                    $val['party_security_info']['party_id'] = $val['party_id'];
                    $val['party_security_info']['is_verified'] = $val['is_verified'];
                    $val['party_security_info']['party_security_status'] = $val['party_security_status'];
                    $val['party_security_info']['created_by'] = $val['created_by'];
                    $security = $val['party_security_info'];
                }else if($val['party_security_status'] == 45){
                    $party['person']['driving_licence_number'] = $val['party_security_info']['security_info_value'];
                    $val['party_security_info']['party_id'] = $val['party_id'];
                    $val['party_security_info']['is_verified'] = $val['is_verified'];
                    $val['party_security_info']['party_security_status'] = $val['party_security_status'];
                    $val['party_security_info']['created_by'] = $val['created_by'];
                    $security = $val['party_security_info'];
                }
              }
              $party['phone'] = $phone;
              $party['address']= $address;
              $party['email']=$email;
              $party['security']=$security;
              return $party;
          }
          return false;
    }

    public function saveProfileImage($party_id,$profile_pic)
    {
        $partyContactsTable = TableRegistry::get('People');
        $query = $partyContactsTable->query();
        $query->update()
          ->set(['profile_pic' => $profile_pic])
          ->where(['party_id' => $party_id])
          ->execute();
        return true;
    }

    public function getOldProfileImageName($party_id)
    {
        $partyPeopleTable = TableRegistry::get('People');
        $party=$partyPeopleTable->Find('all')
                ->where(['id'=>$party_id])
                ->first();
        if(!empty($party->profile_image)){
            return $party->profile_image;
        }else {
            return false;
        }
    }

    public function userUpdateProfileDetails($data)
    {
      $partyContactsTable = TableRegistry::get('People');
      $query = $partyContactsTable->query();
      $query->update()
        ->set(['first_name' => $data['first_name'],'last_name' => $data['last_name'],'middle_initial' => $data['middle_initial']])
        ->where(['party_id' => $data['party_id']])
        ->execute();
      $PartySecuritiesTable = TableRegistry::get('PartySecurities');
      $party=$PartySecuritiesTable->Find('all')
              ->where(['party_id'=>$data['party_id']])
              ->where(['party_security_status'=>44])
              ->orwhere(['party_security_status'=>45])
              ->toArray();
      if($party){
          $PartySecurityInfosTable = TableRegistry::get('PartySecurityInfos');
          foreach($party as $key=>$val){
            if($val['party_security_status']==44){
              $query = $PartySecurityInfosTable->query();
              $query->update()
                ->set(['security_info_value' =>$data['social_security_number']])
                ->where(['party_security_id' => $val['id']])
                ->execute();
            }else if($val['party_security_status']==45){
              $query = $PartySecurityInfosTable->query();
              $query->update()
                ->set(['security_info_value' =>$data['driving_licence_number']])
                ->where(['party_security_id' => $val['id']])
                ->execute();
            }
          }
          return true;
      }else{
          $partySecurities = [
              'party_id' =>$data['party_id'],
              'party_security_status'=>44,
              'is_verified'=>'N',
              'created_by'=>$data['party_id'],
              'created'=>$data['dateTime'],
              'party_security_info'=>[
                      'security_info_type_id'=>44,
                      'security_info_value'=>$data['social_security_number'],
                      'security_info_verified'=>'N',
                      'created_by'=>$data['party_id'],
                      'created'=>$data['dateTime']
                  ]
          ];
          $partySecuritiesTable = TableRegistry::get('PartySecurities');
          $entity = $partySecuritiesTable->newEntity($partySecurities,['associated' => ['PartySecurityInfos']]);
          $partySecuritiesTable->save($entity);

          $partySecurities = [
              'party_id' =>$data['party_id'],
              'party_security_status'=>45,
              'is_verified'=>'N',
              'created_by'=>$data['party_id'],
              'created'=>$data['dateTime'],
              'party_security_info'=>[
                      'security_info_type_id'=>45,
                      'security_info_value'=>$data['driving_licence_number'],
                      'security_info_verified'=>'N',
                      'created_by'=>$data['party_id'],
                      'created'=>$data['dateTime']
                  ]
          ];
          $partySecuritiesTable = TableRegistry::get('PartySecurities');
          $entity = $partySecuritiesTable->newEntity($partySecurities,['associated' => ['PartySecurityInfos']]);
          $partySecuritiesTable->save($entity);
          return true;
      }
    }


    // for get app_code_desc


        public function getAppCodeStatus($id){
            $appCodesTable = TableRegistry::get('AppCodes');
            $appCode=$appCodesTable->Find('all')
            ->where(['id'=>$id])
            ->first();
            if($appCode){
              return $appCode->app_code_desc;
            }
            return "Admin delete the row.";
        }

        function getAppCodeStatusId($app_code_type,$app_code_subtype=0,$app_code_name=0,$app_code_desc=0){
            $appCodesTable = TableRegistry::get('AppCodes');

            if($app_code_type && $app_code_subtype && $app_code_name && $app_code_desc){
                $appCode=$appCodesTable->Find('all')
                ->where(['app_code_type'=>$app_code_type])
                ->where(['app_code_subtype'=>$app_code_subtype])
                ->where(['app_code_name'=>$app_code_name])
                ->where(['app_code_desc'=>$app_code_desc])
                ->toArray();
            }else if($app_code_type && $app_code_subtype && $app_code_name){
                $appCode=$appCodesTable->Find('all')
                ->where(['app_code_type'=>$app_code_type])
                ->where(['app_code_subtype'=>$app_code_subtype])
                ->where(['app_code_name'=>$app_code_name])
                ->toArray();
            }else if($app_code_type && $app_code_subtype){
                $appCode=$appCodesTable->Find('all')
                ->where(['app_code_type'=>$app_code_type])
                ->where(['app_code_subtype'=>$app_code_subtype])
                ->toArray();
            }else if($app_code_type){
                $appCode=$appCodesTable->Find('all')
                ->where(['app_code_type'=>$app_code_type])
                ->toArray();
            }else{
              $appCode=[];
            }

            if(sizeof($appCode) == 1){
              return $appCode[0]->id;
            }
            return $appCode;
        }


}
