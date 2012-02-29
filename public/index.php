<?php

// Define path to application directory
defined('APPLICATION_PATH')
        || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
        || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
            realpath(realpath(dirname(__FILE__) . '/../../')),
            get_include_path(),
        )));

require '../PHPMachine.php';
require '../Tests/PHPMachine/TestResource.php';

header('Content-Type: text/plain');

$response = new \PHPMachine\Http\Response();

\PHPMachine\DecisionCore::handleRequest('\\Tests\\PHPMachine\\TestResource', new \PHPMachine\Http\Request(), $response);

$timeDif = $response->getMetadataItem('end-time')-$response->getMetadataItem('start-time');

echo "\n\n Total Time: ".$timeDif." seconds\n\n Response:\n\n";
echo $response;

