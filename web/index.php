<?php
/**
 * Created by PhpStorm.
 * User: jgrubb
 * Date: 4/18/17
 * Time: 10:45 AM
 */

require_once realpath(__DIR__ . '/../vendor/autoload.php');

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Silex\Application;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$app = new Application();

$app->get('/solr/admin/{action}', function(Request $request, $action) {
    $client = new GuzzleHttp\Client();
    $response = $client->get("http://localhost:30000/solr/admin/$action?" . $request->getQueryString(), []);
    return $response->getBody();
});

$app->post('/solr/update', function(Request $request) {
    $log = new Logger('update');
    $log->pushHandler(new StreamHandler(realpath(__DIR__ . '/../log/update.log'), Logger::INFO));
    $client = new GuzzleHttp\Client();

    try {
        $response = $client->request('POST',
            "http://localhost:30000/solr/update?wt=json",
            [
                'headers' => $request->headers->all(),
                'body' => $request->getContent()
            ]
        );
        return $response->getBody();
    } catch (Exception $e) {
        $log->info($e->getMessage());
        return 500;
    }
});

$app->run();