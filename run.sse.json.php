<?php
header('Content-Type: text/event-stream'); // Indicates that server is aware of server sent events
header('Cache-Control: no-cache'); // Disable caching of response

// Detect if PHP is running from CLI
function is_running_from_cli() {
	return (php_sapi_name() === 'cli');
}

// Create line in SSE format
function create_sse_line($string) {
	echo 'data: ' . $string . "\n\n";
}

// Internals
$stdout_redirect = ' 2>&1';
$to_background = ' &';
$cmd = ''; $var = '';
$dir = sys_get_temp_dir();
$cmd_file = $dir . '/run_cmd';
$var_file = $dir . '/run_var';

// Got instructions to execute?
if (is_readable($cmd_file) && is_readable($var_file)) {
	// Read instructions
	$cmd = file_get_contents($cmd_file);
	$var = json_decode(file_get_contents($var_file), true);
	
	// Run command line
	if (!empty($cmd)) {
		// Standard descriptors
		$descriptorspec = array(
			0 => array("pipe", "r"), // stdin is a pipe that the child will read from
			1 => array("pipe", "w"), // stdout is a pipe that the child will write to
			2 => array("pipe", "w")  // stderr is a pipe that the child will write to
		);

		// Create new process
		$process = proc_open(escapeshellcmd($cmd) . $stdout_redirect . $to_background, $descriptorspec, $pipes, realpath('./'), $var);

		// Check if process is running
		if (is_resource($process)) {
			// Build JSON string
			$obj = new stdClass;
			$obj->command = $cmd;
			$obj->cli = is_running_from_cli();
			$obj->output = '';

			// Processing output
			while ($lines = fgets($pipes[1])) {
				// Status
				$status = proc_get_status($process);

				// Display lines
				$obj->output = $lines;
				$obj->status = $status;
				create_sse_line(json_encode($obj));
				@ob_get_flush(); // I know... using '@' is dirty...
				@flush(); // I know... using '@' is dirty...

				// Check process state
				// if ($status['running'] !== true) {
				if ($status['stopped'] !== false) {
					proc_close($process);
				}
			}

			// Last message
			$obj->output = 'Done.';
			$obj->status = proc_get_status($process);
			create_sse_line(json_encode($obj));

			// Clean everything...
			$cmd = ''; $var = '';
			file_put_contents($cmd_file, $cmd);
			file_put_contents($var_file, $var);
			unlink($cmd_file);
			unlink($var_file);
		}
		else {
			// Error message
			$obj->output = 'Error while creating process.';
			$obj->status = proc_get_status($process);
			create_sse_line(json_encode($obj));
		}
	}
}
?>