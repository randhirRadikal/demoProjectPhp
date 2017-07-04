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
class SchoolsTable extends Table
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

        $this->table('schools');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');
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
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        $validator
			->requirePresence('password', 'create')
			->notEmpty('password');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmpty('email');

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
        return $rules;
    }

    public function emailCheck($email){
      $query = $this->find('all',[
          'conditions' => ['Schools.email =' => $email]
      ]);
      return $query->count();
    }


}
