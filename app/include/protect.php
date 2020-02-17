<?php
require_once 'common.php';

$username = '';
if  (!isset($_SESSION['username'])) {
	$_SESSION['errors'] = 'Please log in';
	header("Location: ../login.php");
	exit;
}

?>