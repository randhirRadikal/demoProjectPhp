<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Auth\DefaultPasswordHasher;
use Cake\I18n\Time;

class FeeStructuresTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('fee_structures');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->requirePresence('school_id', 'create')
            ->notEmpty('school_id');

        $validator
			->requirePresence('class_id', 'create')
			->notEmpty('class_id');
		$validator
			->requirePresence('section_id', 'create')
			->notEmpty('section_id');

        $validator
            ->requirePresence('fee', 'create')
            ->notEmpty('fee');

        return $validator;
    }
}
