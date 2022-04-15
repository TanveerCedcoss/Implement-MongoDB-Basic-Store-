<?php

use Phalcon\Mvc\Controller;

/**
 * TestController consists of basic CRUD operations done to learn MongoDB
 */
class TestController extends Controller
{
    /**
     * indexAction adds new user to db
     *
     * @return void
     */
    public function indexAction()
    {
        $values = $this->request->getpost();
        $insertValues = array(
            "name" => $values['name'],
            "email" => $values['email'],
            "profession" => $values['profession']
        );
        $response = $this->mongo->details->insertOne($insertValues);
        $this->response->redirect('test/show');
    }

    public function addAction()
    {

    }

    public function showAction()
    {
        $result = $this->mongo->details->find();
        $this->view->result = $result;
    }

    public function deleteAction($id)
    {
        $val = ["_id" => new MongoDB\BSON\ObjectId ($id)];
        $this->mongo->details->deleteOne($val);
        $this->response->redirect('test/show');
    }

    public function updateAction($id)
    {
        $val = ["_id" => new MongoDB\BSON\ObjectId ($id)];
        $result = $this->mongo->details->findOne($val);
        $this->view->result = $result;
        if ($this->request->ispost() ) {
            $val = ["_id" => new MongoDB\BSON\ObjectId ($id)];
            $up = ['name' => $this->request->getpost('name'),
            'email' => $this->request->getpost('email'),
            'profession' => $this->request->getpost('profession')];
            $this->mongo->details->updateOne($val, ['$set' => $up]);
            $this->response->redirect('test/show');
        }
    }
}
