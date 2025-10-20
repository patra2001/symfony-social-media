<?php

// use App\Kernel;

// require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

// return function (array $context) {
//     return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
// };



use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/vendor/autoload.php';

if (!class_exists(Dotenv::class)) {
    throw new RuntimeException('The "symfony/dotenv" package is not installed. Run "composer require symfony/dotenv".');
}

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

$request = Request::createFromGlobals();
$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool) ($_SERVER['APP_DEBUG'] ?? ('dev' === $_SERVER['APP_ENV'])));
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
