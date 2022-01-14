<?php
declare(strict_types=1);

//error_reporting(E_ALL);
//if($_ENV['env'] === 'prod') {
    //error_reporting(0);
//}



// Timezone
date_default_timezone_set('Europe/Helsinki');

// Path settings
$settings['env'] = $_ENV['env'];
$settings['root'] = dirname(__DIR__);
$settings['temp'] = $settings['root'] . '/tmp';
$settings['public'] = $settings['root'] . '/public';
$settings['youtube_api_key'] = 'AIzaSyApdfZ1id5T6enPW6rGN7aU8AM4d-o4h3I';

// Error Handling Middleware settings
//$settings['error'] = [
//    // Should be set to false in production
//    'display_error_details' => false,
//    // Parameter is passed to the default ErrorHandler
//    // View in rendered output by enabling the "displayErrorDetails" setting.
//    // For the console and unit tests we also disable it
//    'log_errors' => true,
//    // Display error details in error log
//    'log_error_details' => true,
//];

$settings['logger'] = [
    'name' => 'app',
    'path' => __DIR__ . '/../logs',
    'filename' => 'app.log',
    'level' => \Monolog\Logger::DEBUG,
    'file_permission' => 0775,
];

$settings['jwt'] = [
    'secret' => $_ENV['secret'],
    'lifetime' => 2592000
];

return $settings;
