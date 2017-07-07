<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class NotificationsTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('notifications');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'created_by_user_id',
            'joinType' => 'INNER'
        ]);
    }

    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('user_id', 'create')
            ->notEmpty('user_id');

        return $validator;
    }

    public function getNotifications($data)
    {
        $notifications = $this->find('all',['contain' => [
                          'Users'=>['fields'=>['email','first_name','last_name','profile_image']]
                        ],
                        'order' => ['Notifications.created' => 'DESC']])
                //->where(['user_id'=>$data['id'],'is_deleted'=>'0'])
                //->where(['id >='=>$data['pageToken']])
                //->where(['subject LIKE'=>"%".$data['search_text']."%"])
                //->orwhere(['message LIKE'=>"%".$data['search_text']."%"])
                ->where([
                            'Notifications.user_id' => $data['id'],
                            'Notifications.is_deleted' => 0,
                            'OR' => [['Notifications.subject LIKE' => "%".$data['search_text']."%"], ['Notifications.email_message LIKE' => "%".$data['search_text']."%"],['Notifications.sms_message LIKE' => "%".$data['search_text']."%"]],
                        ])
                //->limit(1)
                ->toArray();
        return $notifications;
    }

    public function deleteNotification($data)
    {
        $query = $this->query();
        $query->update()
          ->set(['is_deleted' => 1])
          ->where(['id' => $data['notification_id']])
          ->execute();
        return true;
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

    public function notificationReaded($data)
    {
      $query = $this->query();
      $query->update()
        ->set(['is_read' => 1])
        ->where(['id' => $data['notification_id']])
        ->execute();
        return true;
    }


}
