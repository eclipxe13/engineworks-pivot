<?php
// report all errors
error_reporting(-1);

setlocale(LC_ALL, 'en_US');
date_default_timezone_set('UTC');

// composer
require_once __DIR__ . '/../vendor/autoload.php';

// environment
call_user_func(function () {
    $dotenv = new \Dotenv\Dotenv(__DIR__);
    $dotenv->load();
});
