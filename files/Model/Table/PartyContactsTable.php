<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Auth\DefaultPasswordHasher;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
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
class PartyContactsTable extends Table
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

        $this->table('party_contacts');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasOne('PartyEmails', [
            'foreignKey' => 'party_contact_id'
        ]);

        $this->hasOne('PartyAddresses', [
            'foreignKey' => 'party_contact_id'
        ]);

        $this->hasOne('PartyPhones', [
            'foreignKey' => 'party_contact_id'
        ]);

        $this->belongsTo('Parties', [
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

    public function edit_address($data){
        $PartyContactsTable = TableRegistry::get('PartyContacts');
        $query = $PartyContactsTable->query();
        $query->update()
          ->set(['contact_type_id'=>$data['contact_type_id']])
          ->where(['id' => $data['party_contact_id']])
          ->execute();
        $partyAddressesTable = TableRegistry::get('PartyAddresses');
        $query = $partyAddressesTable->query();
        if(isset($data['latLng'])){
            $query->update()
              ->set(['address_line_1' => $data['address_line_1'],'address_line_2' => $data['address_line_2'],'county_name' => $data['county_name'],'city'=>$data['city'],'zip_code'=>$data['zip_code'],'latitude'=>$data['latLng']['lat'],'longitude'=>$data['latLng']['lng']])
              ->where(['id' => $data['id']])
              ->execute();
              return true;
        }else{
            $query->update()
              ->set(['address_line_1' => $data['address_line_1'],'address_line_2' => $data['address_line_2'],'city'=>$data['city'],'zip_code'=>$data['zip_code']])
              ->where(['id' => $data['id']])
              ->execute();
              return true;
        }
        return false;
    }

    public function addPhone($data)
    {
      $partyPhoneTable = TableRegistry::get('PartyPhones');
      $partyPhone=$partyPhoneTable->Find('all')->where([
        'phone_number'=>$data['phone_number']
      ])->first();
      if(!$partyPhone){
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
            'party_phone'=>[
                'area_code'=>91,
                'phone_number'=>$data['phone_number'],
                'created_by'=>$data['party_id'],
                'created'=>$data['dateTime']
            ]
        ];
        $entity = $this->newEntity($partyContacts,['associated' => ['PartyPhones']]);
        if($this->save($entity)){
          return true;
        }
      }
      return false;
    }

    public function sendMobileVerificationCode($data)
    {
        $partySecurities = [
            'party_id' =>$data['party_id'],
            'party_security_status'=>56,
            'is_verified'=>'Y',
            'created_by'=>$data['party_id'],
            'created'=>$data['dateTime']
        ];
        $partySecuritiesTable = TableRegistry::get('PartySecurities');
        $entity = $partySecuritiesTable->newEntity($partySecurities);
        $PartySecurity = $partySecuritiesTable->save($entity);
        $party_security_id = $PartySecurity->id;

        $partySecurityInfos = [
            'party_security_id' =>$party_security_id,
            'security_info_type_id'=>56,
            'security_info_value'=>$data['phone_verification_code'],
            'security_info_verified'=>'N',
            'created_by'=>$data['party_id'],
            'created'=>$data['dateTime']
        ];
        $partySecurityInfosTable = TableRegistry::get('PartySecurityInfos');
        $entity = $partySecurityInfosTable->newEntity($partySecurityInfos);
        if($partySecurityInfosTable->save($entity)){
          return true;
        }
        return false;
    }

    public function verifyPhoneVerification($data)
    {
      $partySecurityInfosTable = TableRegistry::get('PartySecurityInfos');
      $party=$partySecurityInfosTable->Find('all')->where([
        'security_info_value'=>$data['phone_verification_code'],'security_info_verified'=>'N','security_info_type_id'=>56
      ])->first();
      if($party){
        $partyContactsTable = TableRegistry::get('PartyContacts');
        $query = $partyContactsTable->query();
        $query->update()
          ->set(['is_verified' => 'Y'])
          ->where(['id' => $data['party_contact_id']])
          ->execute();
        $query = $partySecurityInfosTable->query();
        $query->update()
          ->set(['security_info_verified' => 'Y','security_info_value'=>''])
          ->where(['id' => $party->id])
          ->execute();
          return true;
      }
      return false;
    }

    public function changePhoneNumberToPrimary($data)
    {
      $query = $this->query();
      $query->update()
        ->set(['is_primary' => 'Y'])
        ->where(['id' => $data['party_contact_id']])
        ->execute();
        return true;
    }
}
