<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class PhonesTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('phones');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
    }

    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('phone', 'create')
            ->notEmpty('phone');
        $validator
            ->requirePresence('user_id', 'create')
            ->notEmpty('user_id');

        return $validator;
    }

    public function isMobileAvilable($phone)
    {
        $phones = $this->find('all',[
                      'contain' => ['Users']
                  ])
                  ->where(['Phones.phone'=>$phone])
                  ->orwhere(['Users.phone'=>$phone])
                  ->count();
        return $phones;
    }

    public function addPhone($phone)
    {
        $phones = $this->find('all',[
                      'contain' => ['Users']
                  ])
                  ->where(['Phones.phone'=>$phone])
                  ->orwhere(['Users.phone'=>$phone])
                  ->count();
        return $phones;
    }

    public function getPhoneDetails($id)
    {
        $phones = $this->find('all')
                  ->where(['id'=>$id])
                  ->first();
        return $phones;
    }

    public function checkVerificationCode($data){
        $phones = $this->find('all')
                  ->where(['id'=>$data['id'],'phone_verification_code'=>$data['phone_verification_code']])
                  ->first();
        if($phones){
            $query = $this->query();
            $query->update()
              ->set(['is_phone_verified' => 1,'phone_verification_code' => ''])
              ->where(['id' => $data['id']])
              ->execute();
            return true;
        }else{
            return false;
        }
    }


}
