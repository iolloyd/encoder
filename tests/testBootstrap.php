<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

set_include_path(dirname(__FILE__) . '/library:'. get_include_path());
set_include_path(dirname(__FILE__) . '/tests:'. get_include_path());

$zend = dirname(__FILE__)."/Zend";
$tests = dirname(__FILE__);
$path = get_include_path();
set_include_path(implode(':', array($zend, $tests, $path)));
require_once dirname(__FILE__) . "/testBootstrap.php";
require_once "Zend/Test/PHPUnit/ControllerTestCase.php"; 
require_once dirname(dirname(__FILE__))."/library/Configuration.php";
