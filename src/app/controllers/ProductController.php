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
            // echo '<pre>';
            // print_r($values);
            // die;
            $additonalFeildsData = $this->getAdditionalDataAction($values);
            $variantsData = $this->getVariationsDataAction($values);

            $insertValuesDataInFormat = $this->assignValues($values, $additonalFeildsData, $variantsData);
            
            $response = $this->mongo->products->insertOne($insertValuesDataInFormat);
            if (($response->getInsertedCount())>0) {
                $success = 1;
            }
            $this->response->redirect('product/index/'.$success);
        }
       
    }

    public function showAction()
    {
        if (!$this->request->ispost() || $this->request->getpost('param')=='') {
            $result = $this->mongo->products->find();
            $this->view->result = $result;

        } else {
            $val = ['name' => $this->request->getpost('param')];
            $result = $this->mongo->products->find($val);
            $this->view->result = $result;
        }
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
            // echo '<pre>';
            // print_r($values);
            // die;
            $additonalFeildsData = $this->getAdditionalDataAction($values);
            $variantsData = $this->getVariationsDataAction($values);
           
            $updateValues = $this->assignValues($values, $additonalFeildsData, $variantsData);
            $response = $this->mongo->products->updateOne($val, ['$set' => $updateValues]);
            $this->response->redirect('product/show');
        }
    }

    public function assignValues($values, $additonalFeildsData, $variantsData)
    {
        $data = array(
            "name" => $values['name'],
            "category" => $values['category'],
            "price" => $values['price'],
            "stock" => $values['stock']);
            if ($additonalFeildsData) {
                $data["additional_fields"] = $additonalFeildsData;
            }
            if ($variantsData) {
                $data["variations"] = $variantsData;
            }
        return $data;
    }

    public function getAdditionalDataAction($values)
    {
        $addkeys = $values['additionalKey'];
        $addValues = $values['additionalValue'];
        if (isset($addkeys)) {
            $additonalFeilds = array_combine($addkeys, $addValues);
        }
        return $additonalFeilds;
    }

    public function getVariationsDataAction($values)
    {
        $variantsRawData = $values['variant'];
        if ($variantsRawData) {
            foreach ($variantsRawData as $k => $v){
                $variantsData[$k] = array_combine($v['field'], $v['value']);
                $variantsData[$k]['price'] = $v['price'];
            }
        }
        return $variantsData;
    }

    public function getInfoAction()
    {
        $id = $this->request->getpost('id');
        $val = ["_id" => new MongoDB\BSON\ObjectId ($id)];
        $result = $this->mongo->products->findOne($val);
        return json_encode($result);
    }
}
