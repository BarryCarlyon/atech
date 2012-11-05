<?php

include('codebase.php');

$cb = new Codebase('barrycarlyon/barrycarlyon', '8014ecd1e25fe68d6ecb71196e202cb533caebd6');
//$cb = new Codebase('newmedias/bcarlyon', 'ultf29lmtchf2p5eg67dzhwhhdbfx6leleiio4rc');

try {
	$func = $argv[1];
	$result = $cb->$func($argv[2], $argv[3], $argv[4]);
//	$result = $cb->projectRepositories($argv[1]);
//	$result = $cb->createRepository($argv[1], $argv[2]);
//	$result = $cb->createProject($argv[1]);
//	print_r($result);
//	$result = $cb->deleteProject($argv[1]);
//	$result = $cb->deleteRepository($argv[1], $argv[2]);
	print_r($result);
} catch (Exception $e) {
	echo 'Err: ' . $e->getMessage();
}
echo "\n\n";
