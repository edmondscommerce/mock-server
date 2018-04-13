<?php declare(strict_types=1);
$files = [
    __DIR__.'/../../../../autoload.php',
    __DIR__.'/../../vendor/autoload.php',
];

$autoloadFileFound = false;
foreach ($files as $file) {
    if (file_exists($file)) {
        require $file;
        $autoloadFileFound = true;
        break;
    }
}
if (!$autoloadFileFound) {
    echo 'You need to set up the project dependencies using the following commands:'.PHP_EOL.
         'curl -s http://getcomposer.org/installer | php'.PHP_EOL.
         'php composer.phar install'.PHP_EOL;
    die(1);
}

/**
 * Ensure we are displaying all errors - these will show up in the logs
 */
ini_set('display_errors', '1');
error_reporting(E_ALL);


