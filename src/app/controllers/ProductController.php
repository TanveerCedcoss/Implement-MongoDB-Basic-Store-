<?php

use Phalcon\Mvc\Controller;

/**
 * ProductController consists of basic CRUD operations related to the products
 */
class ProductController extends Controller
{
    /**
     * indexAction adds new user to db
     *
     * @return void
     */
    public function indexAction($success = '')
    {
        if ($success==1) {
            $message = "Product added successfully";
        }
        $this->view->message = $message;
    }

    public function addAction()
    {
        if ($this->request->ispost()) {
            
            $values = $this->request->getpost();
            echo '<pre>';
            print_r($values);
            die;
            $insertValues = $this->assignValues($values);
            $response = $this->mongo->products->insertOne($insertValues);
            if (($response->getInsertedCount())>0) {
                $success = 1;
            }
            $this->response->redirect('product/index/'.$success);
        }
       
    }

    public function showAction()
    {
        $result = $this->mongo->products->find();
        $this->view->result = $result;
    }

    public function deleteAction($id)
    {
        $val = ["_id" => new MongoDB\BSON\ObjectId ($id)];
        $this->mongo->products->deleteOne($val);
        $this->response->redirect('product/show');
    }

    public function updateAction($id)
    {
        $val = ["_id" => new MongoDB\BSON\ObjectId ($id)];
        $result = $this->mongo->products->findOne($val);
        $this->view->result = $result;
        if ($this->request->ispost() ) {
            $val = ["_id" => new MongoDB\BSON\ObjectId ($id)];
            $values = $this->request->getpost();
            $updateValues = $this->assignValues($values);
            $response = $this->mongo->products->updateOne($val, ['$set' => $updateValues]);
            $this->response->redirect('product/show');
        }
    }

    public function assignValues($values)
    {
        $addkeys = $values['additionalKey'];
        $addValues = $values['additionalValue'];
        $varKeys = $values['variationKey'];
        $varValues = $values['variationValue'];
        if (isset($addkeys)) {
            $additonalFeilds = array_combine($addkeys, $addValues);
        }

        if (isset($varKeys)) {
            $variation = array_combine($varKeys, $varValues);
        }

        $data = array(
            "name" => $values['name'],
            "category" => $values['category'],
            "price" => $values['price'],
            "stock" => $values['stock'],
            "additional_fields" => $additonalFeilds,
            "variations" => $variation
        );
        return $data;
    }

    public function getInfoAction()
    {
        $id = $this->request->getpost('id');
        $val = ["_id" => new MongoDB\BSON\ObjectId ($id)];
        $result = $this->mongo->products->findOne($val);
        return json_encode($result);
    }
}
