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

$config = new Platformsh\ConfigReader\Config();
if ($config->isAvailable()) {
    $solr = $config->relationships['solr'][0];
    $solr_url = "http://{$solr['host']}:{$solr['port']}/{$solr['path']}";
} else {
    $solr_url = 'http://localhost:30000/solr';
}

$app = new Application();

$app->get('/solr/admin/{action}', function(Request $request, $action) use ($solr_url) {
    $client = new GuzzleHttp\Client();
    $response = $client->get("$solr_url/admin/$action?" . $request->getQueryString(), []);
    return $response->getBody();
});

$app->get('/solr/{action}', function(Request $request, $action) use ($solr_url) {
    $client = new GuzzleHttp\Client();
    $response = $client->get("$solr_url/$action?" . $request->getQueryString(), []);
    return $response->getBody();
});

$app->post('/solr/update', function(Request $request) use ($solr_url) {
    $log = new Logger('update');
    $log->pushHandler(new StreamHandler(realpath(__DIR__ . '/../log/update.log'), Logger::INFO));
    $client = new GuzzleHttp\Client();

    try {
        $response = $client->request('POST',
            "$solr_url/update?" . $request->getQueryString(),
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