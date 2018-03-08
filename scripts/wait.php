<?php

namespace go1\monolith\scripts;

use Doctrine\DBAL\Connection;
use Pimple\Container;
use Silex\Provider\DoctrineServiceProvider;

require_once __DIR__ . '/../php/vendor/go1.autoload.php';
error_reporting(E_ERROR | E_PARSE);

function is_json($string) {
  return !empty($string) && is_string($string) && is_object(json_decode($string)) && json_last_error() == 0;
}

$c = (new Container)->register(new DoctrineServiceProvider, ['dbs.options' => [
    'install' => $base = [
        'host'          => '127.0.0.1',
        'user'          => 'root',
        'password'      => 'root',
        'port'          => '3306',
        'driver'        => 'pdo_mysql',
        'driverOptions' => [1002 => 'SET NAMES utf8'],
    ],
    'core'        => $base + ['dbname' => 'go1_dev'],
    'realtime'    => $base + ['dbname' => 'realtime_dev'],
]]);
/** @var Connection $con */
$con = $c['dbs']['install'];

return function ($withScorm) use ($con) {
    $waitFor = $withScorm ? 'web, mysql and scormengine' : 'web and mysql';
    while (true) {
        try {
            $mysqlAvailable = $con->ping();
        }
        catch (\Exception $exception) {
            $mysqlAvailable = false;
        }
        $webData = file_get_contents('http://localhost/GO1/interactive-li');
        $webAvailable = is_json($webData);
        $scormData = file_get_contents('http://localhost:9999');
        $scormengineAvailable = false !== strpos($scormData, 'Apache Tomcat');
        $allAvailable = $withScorm ? $webAvailable && $scormengineAvailable && $mysqlAvailable : $webAvailable && $mysqlAvailable;
        if ($allAvailable) {
            break;
        }
        echo "Waiting for $waitFor. Sleep in 5 seconds...\n";
        sleep(5);
    }

    return $con;
};
