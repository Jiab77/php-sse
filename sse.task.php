<?php
// Compose command line to execute
$cmd_exec = '';
switch ($_POST['db_mode']) {
	case 'import':
		// Get input file name
		$db_file = '';
		if (isset($_POST['db_file']) && !empty($_POST['db_file'])) {
			$db_file = realpath('../' . $_POST['db_file']);
		}	
		switch ($_POST['db_engine']) {
			case 'postgresql':
				$cmd_exec = 'psql';
				if (isset($_POST['db_pass']) && !empty($_POST['db_pass'])) {
					$cmd_var = 'PGPASSWORD="' . $_POST['db_pass'] . '"';
				}
				$cmd_line  = $cmd_exec . ' -w';
				$cmd_line .= ' --username=' . $_POST['db_user'];
				$cmd_line .= ' --host=' . $_POST['db_host'];
				$cmd_line .= ' --dbname=' . $_POST['db_name'];
				$cmd_line .= ' --file=' . $db_file;
				// $cmd_line .= ' --echo-all';
				$cmd_line .= ' --echo-queries';
				$cmd_line .= ' --echo-errors';
				break;
			case 'mysql':
				$cmd_exec  = 'mysql';
				$cmd_var   = '';
				$cmd_line  = $cmd_exec . ' --verbose';
				$cmd_line .= ' --host=' . $_POST['db_host'];
				$cmd_line .= ' --user=' . $_POST['db_user'];
				if (isset($_POST['db_pass']) && !empty($_POST['db_pass'])) {
					$cmd_line .= ' --password=' . $_POST['db_pass'];
				}
				$cmd_line .= ' ' . $_POST['db_name'] . ' < ' . $db_file;
				break;
			default:
				$cmd_line  = '';
				break;
		}
		break;
	case 'export':
		// Compose output file name
		$db_file = $_POST['db_name'] . '_dumped';
		switch ($_POST['db_content']) {
			case 'all':
				$db_file .= '_db';
				break;
			case 'schema':
				$db_file .= '_schema';
				break;
			case 'data':
				$db_file .= '_data';
				break;
		}
		if (isset($_POST['db_option_create']) && filter_var($_POST['db_option_create'], FILTER_VALIDATE_BOOLEAN) === true) {
			$db_file .= '_with_create';
			if ($_POST['db_content'] !== 'data' && isset($_POST['db_option_drop']) && filter_var($_POST['db_option_drop'], FILTER_VALIDATE_BOOLEAN) === true) {
				$db_file .= '_and_drop';
			}
		}
		else {
			if ($_POST['db_content'] !== 'data' && isset($_POST['db_option_drop']) && filter_var($_POST['db_option_drop'], FILTER_VALIDATE_BOOLEAN) === true) {
				$db_file .= '_with_drop';
			}
		}
		$db_file .= '.sql';

		// Proceed by engines
		switch ($_POST['db_engine']) {
			case 'postgresql':
				$cmd_exec  = 'pg_dump';
				if (isset($_POST['db_pass']) && !empty($_POST['db_pass'])) {
					$cmd_var   = 'PGPASSWORD="' . $_POST['db_pass'] . '"';
				}
				$cmd_line  = $cmd_exec . ' -w --verbose';
				$cmd_line .= ' --username=' . $_POST['db_user'];
				$cmd_line .= ' --host=' . $_POST['db_host'];
				$cmd_line .= ' --dbname=' . $_POST['db_name'];
				if ($_POST['db_content'] === 'data') {
					$cmd_line .= ' --data-only';
				}
				if ($_POST['db_content'] === 'schema') {
					$cmd_line .= ' --schema-only';
				}
				if (isset($_POST['db_option_create']) && filter_var($_POST['db_option_create'], FILTER_VALIDATE_BOOLEAN) === true) {
					$cmd_line .= ' --create';
				}
				if ($_POST['db_content'] !== 'data' && isset($_POST['db_option_drop']) && filter_var($_POST['db_option_drop'], FILTER_VALIDATE_BOOLEAN) === true) {
					$cmd_line .= ' --clean';
				}
				$cmd_line .= ' --column-inserts --attribute-inserts --inserts';
				$cmd_line .= ' --file=' . $dir . '/' . $db_file;
				break;
			case 'mysql':
				$cmd_exec  = 'mysqldump';
				$cmd_var   = '';
				$cmd_line  = $cmd_exec . ' --verbose';
				$cmd_line .= ' --host=' . $_POST['db_host'];
				$cmd_line .= ' --user=' . $_POST['db_user'];
				if (isset($_POST['db_pass']) && !empty($_POST['db_pass'])) {
					$cmd_line .= ' --password=' . $_POST['db_pass'];
				}
				if ($_POST['db_content'] === 'data') {
					$cmd_line .= ' --no-create-info';
				}
				if ($_POST['db_content'] === 'schema') {
					$cmd_line .= ' --no-data';
				}
				if (!isset($_POST['db_option_create']) || filter_var($_POST['db_option_create'], FILTER_VALIDATE_BOOLEAN) !== true) {
					$cmd_line .= ' --no-create-db';
				}
				if ($_POST['db_content'] !== 'data' && isset($_POST['db_option_drop']) && filter_var($_POST['db_option_drop'], FILTER_VALIDATE_BOOLEAN) === true) {
					$cmd_line .= ' --add-drop-database --add-drop-table';
				}
				$cmd_line .= ' --flush-privileges --dump-date --tz-utc';
				$cmd_line .= ' ' . $_POST['db_name'] . ' > ' . $dir . '/' . $db_file;
				break;
			default:
				$cmd_line  = '';
				break;
		}
		break;
	default:
		$cmd_line = '';
		break;
}