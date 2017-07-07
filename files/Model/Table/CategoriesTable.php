<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class CategoriesTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('categories');
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
            ->requirePresence('parent_id', 'create')
            ->integer('parent_id')
            ->notEmpty('parent_id');

        $validator
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        return $validator;
    }

    public function getProductCategoryList($data)
    {
      $query=$this->Find('all')
              ->toArray();
      return $query;
    }

    public function getCategoryListForDropdown($data)
    {
      $query=$this->Find('all')
              ->where(['parent_id !='=>10])
              ->toArray();
      return $query;
    }

    public function getCategoryDetails($data)
    {
      $query=$this->Find('all')
              ->where(['id'=>$data['category_id']])
              ->first();
      return $query;
    }

    public function updateCategory($data)
    {
      $query = $this->query();
      $query->update()
        ->set(['parent_id' => $data['parent_id'],'name' => $data['name'],'status'=>$data['status']])
        ->where(['id' => $data['id']])
        ->execute();
      return true;
    }

    public function checkItIsParent($id){
      $query=$this->Find('all')
              ->where(['parent_id'=>$id])
              ->count();
      return $query;
    }
}
