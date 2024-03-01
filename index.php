<?php
// error_reporting(E_ALL);
// ini_set('display_errors', '1');
require 'res/Util.php';
require 'res/Command.php';
require 'res/Response.php';
require 'res/Config.php';
require 'res/Card.php';
require 'res/Database.php';
require 'res/Strelka.php';
require 'res/DialogProcessor.php';

$data = json_decode(file_get_contents('php://input'));
$proc = new DialogProcessor($data);
echo $proc->process()->toJson();
?>