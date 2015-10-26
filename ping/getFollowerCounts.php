<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/config/config.php");
	require_oncE($_SERVER['DOCUMENT_ROOT']."/autoload.php");

	use Vivalytics\InstagramAccount as InstagramAccount;

	$return = array("status" => "failure");

	if (isset($_GET['accountId'])){
		$pdo = new \PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_USER_PASSWORD, array(PDO::ATTR_EMULATE_PREPARES => false, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"));
		$account = new InstagramAccount($pdo, $_GET['accountId']);

		if (isset($_GET['start']) && isset($_GET['end'])){
			$start = $_GET['start'];
			$end = $_GET['end'];
		} else{
			$start = false;
			$end = false;
		}

		if ($counts = $account->getUserCounts($start, $end)){
			$return['data'] = array();
			foreach ($counts as $count){
				array_push($return['data'], array($count['time'], $count['followed_by']));
			}
			$return['status'] = "success";
		}
	}

	echo json_encode($return);
?>