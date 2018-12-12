<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . "/vendor/autoload.php";

$t = new LanguageDetection\Trainer();
 
$t->setMaxNgrams(9000);
 
$t->learn();

