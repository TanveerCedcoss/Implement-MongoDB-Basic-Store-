<?php

use Phalcon\Mvc\Controller;

/**
 * OrderController class is responsible for all the operations related to the orders
 * @package Product
 * @author Tanveer <tanveer@cedcoss.com>
 */
class OrderController extends Controller
{
    public function indexAction($success = '')
    {
        if ($success == 1) {
            $message = "Order placed successfully";
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
        $result = $this->mongo->products->find();
        $this->view->result = $result;
        if ($this->request->ispost()) {
            $value = $this->request->getpost();

            $now = date('Y-m-d');
            $value['date'] = $now;
            $value['status'] = 'Paid';
            $response = $this->mongo->orders->insertOne($value);
            if (($response->getInsertedCount()) > 0) {
                $success = 1;
            }
            $this->response->redirect('order/index/' . $success);
        }
    }

    /**
     * showAction get and pass all the products to the View of this class
     *
     * @return void
     */
    public function showAction()
    {
        $startDate = $this->request->getPost("startDate");
        $endDate = $this->request->getPost("endDate");

        if (!$this->request->ispost() || $this->request->getpost('param') == '') {
            $result = $this->mongo->orders->find();
            $this->view->result = $result;
        } else {
            $val = ['customer_name' => $this->request->getpost('param')];
            $result = $this->mongo->orders->find($val);
            $this->view->result = $result;
        }

        if ($this->request->getpost('date')) {
            $date = $this->request->getpost('date');
            $status = $this->request->getpost('status');


            $pipeline = array();

            if ($status == 'All' && $date == 'All') {
                $result = $this->mongo->orders->find();
                $this->view->result = $result;
            }
            if ($status != 'All') {
                $status =  array(
                    '$match' => array('status' => $status)
                );
                array_push($pipeline, $status);
            }
            if ($date != 'All') {
                $date = $this->getDateAction($date);
                $date =  array(
                    '$match' => array('date' => array('$gte' => $date))
                );
                array_push($pipeline, $date);
            }
            if ($status != 'All' || $date != 'All') {
                $result = $this->mongo->orders->aggregate($pipeline)->toArray();
            }

            if (isset($startDate) && isset($endDate)) {
                $result = $this->customDateAction($startDate, $endDate);
            }

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
        $this->mongo->orders->deleteOne($val);
        $this->response->redirect('order/show');
    }

    /**
     * updateStaus function updates the status of the an order in db
     * the new status is provided in a post request
     * @return void
     */
    public function updateStatusAction()
    {
        $values = $this->request->getpost('newStatus');
        $values = explode(',', $values);

        $id = $values[1];
        $val = ["_id" => new MongoDB\BSON\ObjectId($id)];
        $newStatus = $values[0];
        $updateStatus = array("status" => $newStatus);
        $response = $this->mongo->orders->updateOne($val, ['$set' => $updateStatus]);
        die;
    }

    /**
     * getDateAction recieves the string of a date filter and find a date according to that
     *
     * @param [str] $date
     * @return [date] date
     */
    public function getDateAction($date)
    {
        switch ($date) {
            case 'Today':
                $date = date('Y-m-d');
                return $date;
                break;
            case 'This week':
                $date = date('Y-m-d', strtotime('This Week'));
                return $date;
                break;
            case 'This month':
                $date = date('Y-m-d', strtotime('first day of this month'));
                return $date;
                break;
            case 'All':
                $date = date('Y-m-d');
                return $date;
                break;
        }
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
        $result = $this->mongo->orders->findOne($val);
        return json_encode($result);
    }

    /**
     * customDateAction finds the data between a custom range given by
     * the user
     * @param [date] $startDate
     * @param [date] $endDate
     * @return result
     */
    public function customDateAction($startDate, $endDate)
    {
        $document = [
            "date" => ['$gte' => $startDate, '$lte' => $endDate]
        ];
        $searchResponse = $this->mongo->orders->find($document)->toArray();
        return $searchResponse;
    }
}
