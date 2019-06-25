<?php
// Config
$dir = sys_get_temp_dir();
$cmd_line = '';

// Include your task file here
require_once 'sse.task.php';

// Processing command line
if (isset($cmd_line) && !empty($cmd_line)) {
	/* echo 'Will run: ' . $cmd_line . '<br>' . PHP_EOL;
	echo 'Escaped as: ' . escapeshellcmd($cmd_line) . '<br>' . PHP_EOL; */

	// Write command line to temp files
	file_put_contents($dir . '/run_cmd', $cmd_line);
	if (isset($_POST['db_pass']) && !empty($_POST['db_pass'])) {
		file_put_contents($dir . '/run_var', json_encode(['PGPASSWORD' => $_POST['db_pass']]));
	}
	else {
		file_put_contents($dir . '/run_var', json_encode([]));
	}
}