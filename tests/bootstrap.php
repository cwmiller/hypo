<?php
$autoloader = require(__DIR__.'/../vendor/autoload.php');

if (!$autoloader) {
	die('You must set up the project dependencies, run the following commands:
	        wget http://getcomposer.org/composer.phar
	        php composer.phar install');
}

$autoloader->add('CWM\Hypo\Tests', __DIR__);