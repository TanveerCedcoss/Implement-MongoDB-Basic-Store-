<?php
use Phalcon\Di\FactoryDefault;
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Url;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
use Phalcon\Config;

$config = new Config([]);

// Define some absolute path constants to aid in locating resources
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('BASE_URL', 'http://localhost:8080');

// Register an autoloader
$loader = new Loader();

$loader->registerDirs(
    [
        APP_PATH . "/controllers/",
        APP_PATH . "/models/",
    ]
);
require_once('../app/vendor/autoload.php');

$loader->register();

$container = new FactoryDefault();

$container->set(
    'view',
    function () {
        $view = new View();
        $view->setViewsDir(APP_PATH . '/views/');
        return $view;
    }
);

$container->set(
    'url',
    function () {
        $url = new Url();
        $url->setBaseUri('/');
        return $url;
    }
);

// $container->set(
//     'db',
//     function () {
//         return new DbAdapter(
//             [
//                 'host'     => 'mysql-server',
//                 'username' => 'root',
//                 'password' => 'secret',
//                 'dbname'   => 'users',
//                 ]
//             );
//         }
// ); 

$application = new Application($container);





$container->set(
    'mongo',
    function () {
        $mongo =  new MongoDB\Client('mongodb://mongo', array('username'=>'root',"password"=>'password123'));
        return $mongo->store;
    },
    true
);


try {
    // Handle the request
    $response = $application->handle(
        $_SERVER["REQUEST_URI"]
    );

    $response->send();
} catch (\Exception $e) {
    echo 'Exception: ', $e->getMessage();
}
