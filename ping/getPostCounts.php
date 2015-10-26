<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/config/config.php");
	require_oncE($_SERVER['DOCUMENT_ROOT']."/autoload.php");

	use Vivalytics\InstagramPost as InstagramPost;

	$return = array("status" => "failure");

	if (isset($_GET['postId'])){
		$pdo = new \PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_USER_PASSWORD, array(PDO::ATTR_EMULATE_PREPARES => false, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"));
		$post = new InstagramPost($pdo, $_GET['postId']);

		if (isset($_GET['start']) && isset($_GET['end'])){
			$start = $_GET['start'];
			$end = $_GET['end'];
		} else{
			$start = false;
			$end = false;
		}

		if ($counts = $post->getPostCounts($start, $end)){
			$return['data'] = array();
			foreach ($counts as $count){
				array_push($return['data'], array($count['time'], $count['likes']));
			}
			$return['status'] = "success";
		}
	}

	echo json_encode($return);
?>

