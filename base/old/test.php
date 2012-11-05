<?php

include('Codebase.class.php');

$username = 'barrycarlyon/barrycarlyon';
$apikey = '8014ecd1e25fe68d6ecb71196e202cb533caebd6';

$cb = new Codebase($username, $apikey, 'barrycarlyon', 's');

//$r = $cb->project('test-2');
$r = $cb->deleteProject('test-2');
print_r($r);
