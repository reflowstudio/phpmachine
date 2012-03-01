<?php

// Define path to application directory
defined('RESOURCE_PATH')
        || define('RESOURCE_PATH', realpath(dirname(__FILE__) . '/../resources'));

// Define application environment
defined('APPLICATION_ENV')
        || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
            realpath(realpath(dirname(__FILE__) . '/../../')),
            get_include_path(),
        )));

require '../../../PHPMachine.php';

\PHPMachine\Loader::autoload('MyExample', RESOURCE_PATH, false);

\PHPMachine\http_request(dirname(__FILE__). '/../config/dispatch.conf.php');