<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class AppCodesTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('app_codes');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');
        $this->hasMany('AppCodes', [
            'foreignKey' => 'party_contact_status'
        ]);
    }


}
