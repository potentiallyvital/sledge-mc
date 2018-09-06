<?php

if (empty($_SESSION)) {
        session_start();
}

require 'config.php';

$controller = new BaseController();
$controller->initialize();
