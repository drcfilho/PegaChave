<?php
$_SERVER['REQUEST_URI'] = '/api/processar_scan';
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['qr_code'] = '123';
require 'index.php';
