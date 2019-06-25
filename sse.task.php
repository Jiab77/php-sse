<?php
// Compose output file name
$db_file  = $dir;
$db_file .= '/' . $_POST['db_name'] . '_dumped';
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

// Compose command line to execute
$cmd_exec = '';
switch ($_POST['db_engine']) {
	case 'postgresql':
		$cmd_exec  = 'pg_dump';
		$cmd_var   = 'PGPASSWORD="' . $_POST['db_pass'] . '"';
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
		$cmd_line .= ' --file=' . $db_file;
		break;
	case 'mysql':
	default:
		$cmd_line  = 'Engine [' . $_POST['db_engine'] . '] not supported yet.';
		break;
}