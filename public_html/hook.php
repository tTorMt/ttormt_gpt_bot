<?php

declare (strict_types=1);
/*
/* Webhook file
*/
use TtormtGptBot\App;

require __DIR__.'/../vendor/autoload.php';

try {

    $app = new App();
    $app->run();

} catch (Exception $exception) {
    // TO DO error logging
    file_put_contents(__DIR__ . '/../errors', date('Y-m-d_H:i').' '.$exception->getMessage().PHP_EOL, FILE_APPEND);
}
