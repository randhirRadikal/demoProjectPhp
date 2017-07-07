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

use Cake\Core\Configure;
use Cake\Network\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;
use Cake\ORM\TableRegistry;

/**
 * Static content controller
 *
 * This controller will render views from Template/Pages/
 *
 * @link http://book.cakephp.org/3.0/en/controllers/pages-controller.html
 */
class CategoriesController extends AppController
{

    public function initialize() {
        parent::initialize();
        $this->Auth->allow(['index','getProductCategoryList']);
    }

    public function buildTree($elements, $parentId = 0) {
        $branch = array();
        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = $this->buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[] = $element;
                unset($elements[$element['id']]);
            }
        }
        return $branch;
    }

    public function getProductCategoryList()
    {
      $error_code = "FAIL";
      $error_message = "Fail";
      $result = [];
        $data['id'] = $this->Auth->user('id');
        $Catogory = $this->Categories->getProductCategoryList($data);
        $result = $this->buildTree($Catogory, 0);
        $error_code = "SUCCESS";
        $error_message = "categories list.";
      $this->set(["error_code"=>$error_code,
                  "error_message"=>$error_message,
                  "result"=>$result,
                  '_serialize' => ['error_code','error_message','result']
              ]);
    }

    public function getCategoryListForDropdown()
    {
      $error_code = "FAIL";
      $error_message = "Fail";
      $result = [];
      $data['id'] = $this->Auth->user('id');
      $result = $this->Categories->getCategoryListForDropdown($data);
      $error_code = "SUCCESS";
      $error_message = "categories list.";
      $this->set(["error_code"=>$error_code,
                  "error_message"=>$error_message,
                  "result"=>$result,
                  '_serialize' => ['error_code','error_message','result']
              ]);
    }

    public function getCategoryDetails()
    {
      $error_code = "FAIL";
      $error_message = "Fail";
      $result = [];
      if($this->request->is('POST')){
          $data = $this->request->data;
          $data['id'] = $this->Auth->user('id');
          $result = $this->Categories->getCategoryDetails($data);
          $error_code = "SUCCESS";
          $error_message = "categories list.";
      }
      $this->set(["error_code"=>$error_code,
                  "error_message"=>$error_message,
                  "result"=>$result,
                  '_serialize' => ['error_code','error_message','result']
              ]);
    }


    public function saveCategory()
    {
      $error_code = "FAIL";
      $error_message = "Fail";
      if($this->request->is('POST')){
          $data = $this->request->data;
          if(!empty($data['id'])){
            $category = $this->Categories->updateCategory($data);
            if ($category) {
              $error_code = "SUCCESS";
              $error_message = "Category updated successfully.";
            }
          }else{
            $category = $this->Categories->newEntity();
            $category = $this->Categories->patchEntity($category,$data);
            if ($this->Categories->save($category)) {
              $error_code = "SUCCESS";
              $error_message = "Category added successfully.";
            }
          }
      }
      $this->set(["error_code"=>$error_code,
                  "error_message"=>$error_message,
                  '_serialize' => ['error_code','error_message']
              ]);
    }

    public function deleteCatogory()
    {
        $error_code = "FAIL";
        $error_message = "Fail";
        if($this->request->is('POST')){
            $data = $this->request->data;
            $category = $this->Categories->checkItIsParent($data['id']);
            if($category == 0){
              $entity = $this->Categories->get($data['id']);
              if($this->Categories->delete($entity)){
                $error_code = 'SUCCESS';
                $error_message = 'Category delete successfully.';
              }else {
                $error_code = 'FAIL';
                $error_message = 'Some Problem.';
              }
            }else{
              $error_code = 'FAIL';
              $error_message = 'Parent.';
            }
        }
        $this->set(["error_code"=>$error_code,
                    "error_message"=>$error_message,
                    '_serialize' => ['error_code','error_message']
                ]);
    }
}
