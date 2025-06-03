<?php

require 'vendor/autoload.php';

Dotenv\Dotenv::createUnsafeImmutable(__DIR__)->load();

$api_key = getenv('API_KEY');
