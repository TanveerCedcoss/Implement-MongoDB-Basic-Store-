<?php

use Phalcon\Mvc\Controller;

/**
 * ProductController class is responsible for all the operations related to the Products
 * @package Product
 * @author Tanveer <tanveer@cedcoss.com>
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
        if ($success == 1) {
            $message = "Product added successfully";
        }
        $this->view->message = $message;
    }

    /**
     * addAction add new product to database
     * and also assign value to $message accordingly
     * @return void
     */
    public function addAction()
    {
        if ($this->request->ispost()) {
            $values = $this->request->getpost();
        
            $additonalFeildsData = $this->getAdditionalDataAction($values);
            $variantsData = $this->getVariationsDataAction($values);

            $insertValuesDataInFormat = $this->assignValues($values, $additonalFeildsData, $variantsData);

            $response = $this->mongo->products->insertOne($insertValuesDataInFormat);
            if (($response->getInsertedCount()) > 0) {
                $success = 1;
            }
            $this->response->redirect('product/index/' . $success);
        }
    }

     /**
     * showAction get and pass all the products to the View of this class
     *
     * @return void
     */
    public function showAction()
    {
        if (!$this->request->ispost() || $this->request->getpost('param') == '') {
            $result = $this->mongo->products->find();
            $this->view->result = $result;
        } else {
            $val = ['name' => $this->request->getpost('param')];
            $result = $this->mongo->products->find($val);
            $this->view->result = $result;
        }
    }

    /**
     * deleteAction gets the ID of the product as param and then find the product in db based on that
     *  and finally delete it from the the database and redirects to view of this controller
     * @param [num] $id
     * @return void
     */
    public function deleteAction($id)
    {
        $val = ["_id" => new MongoDB\BSON\ObjectId($id)];
        $this->mongo->products->deleteOne($val);
        $this->response->redirect('product/show');
    }

    /**
     * updateAction updates an existing product in the database
     *
     * @param [str] $id
     * @return void
     */
    public function updateAction($id)
    {
        $val = ["_id" => new MongoDB\BSON\ObjectId($id)];
        $result = $this->mongo->products->findOne($val);
        $this->view->result = $result;
        if ($this->request->ispost()) {
            $val = ["_id" => new MongoDB\BSON\ObjectId($id)];

            $values = $this->request->getpost();
          
            $additonalFeildsData = $this->getAdditionalDataAction($values);
            $variantsData = $this->getVariationsDataAction($values);

            $updateValues = $this->assignValues($values, $additonalFeildsData, $variantsData);
            $response = $this->mongo->products->updateOne($val, ['$set' => $updateValues]);
            $this->response->redirect('product/show');
        }
    }

    /**
     * assignValues recieves values and arranges it in such a form that the data
     * is required to be stored in database
     * @param [array] $values
     * @param [array] $additonalFeildsData
     * @param [array] $variantsData
     * @return void
     */
    public function assignValues($values, $additonalFeildsData, $variantsData)
    {
        $data = array(
            "name" => $values['name'],
            "category" => $values['category'],
            "price" => $values['price'],
            "stock" => $values['stock']
        );
        if ($additonalFeildsData) {
            $data["additional_fields"] = $additonalFeildsData;
        }
        if ($variantsData) {
            $data["variations"] = $variantsData;
        }
        return $data;
    }

    /**
     * getAdditionalDataAction gets the values and extracts Additional fields and return the
     * data in the desired format
     * @param [array] $values
     * @return [array] variantsData
     */
    public function getAdditionalDataAction($values)
    {
        $addkeys = $values['additionalKey'];
        $addValues = $values['additionalValue'];
        if (isset($addkeys)) {
            $additonalFeilds = array_combine($addkeys, $addValues);
        }
        return $additonalFeilds;
    }

    /**
     * getVariationsDataAction gets the values and extracts Variations and return the
     * data in the desired format
     * @param [array] $values
     * @return [array] variantsData
     */
    public function getVariationsDataAction($values)
    {
        $variantsRawData = $values['variant'];
        if ($variantsRawData) {
            foreach ($variantsRawData as $k => $v) {
                $variantsData[$k] = array_combine($v['field'], $v['value']);
                $variantsData[$k]['price'] = $v['price'];
            }
        }
        return $variantsData;
    }

    /**
     * getInfo recieves request and returns the data in json format
     *
     * @return [json] result
     */
    public function getInfoAction()
    {
        $id = $this->request->getpost('id');
        $val = ["_id" => new MongoDB\BSON\ObjectId($id)];
        $result = $this->mongo->products->findOne($val);
        return json_encode($result);
    }
}
