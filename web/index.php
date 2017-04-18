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

$app = new Application();

$app->get('/', function(Application $app, Request $request) {
    return 'hello';
});