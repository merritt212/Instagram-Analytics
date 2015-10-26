<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/config/config.php");
	require_oncE($_SERVER['DOCUMENT_ROOT']."/autoload.php");

	$return = array("status" => "failure");

	if (isset($_GET['accountId']) && isset($_GET['page'])){
		$pdo = new \PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_USER_PASSWORD, array(PDO::ATTR_EMULATE_PREPARES => false, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"));

		$return['data'] = array();
		$offset = ($_GET['page'] - 1) * SETTING_POSTS_PER_PAGE;

		$statement = $pdo->prepare("SELECT COUNT(*) as num_posts FROM instagram_posts WHERE user_id = :accountId");
		$statement->execute(array(":accountId" => $_GET['accountId']));
		$result = $statement->fetch(\PDO::FETCH_ASSOC);
		$return['postCount'] = $result['num_posts'];

		if ($offset < $result['num_posts']){
			$statement = $pdo->prepare("SELECT * FROM instagram_posts ORDER BY time_created DESC LIMIT 12 OFFSET :offset");
			$statement->execute(array(":offset" => $offset));
			foreach ($statement as $i => $post){
				array_push($return['data'], array("postId" => $post['post_id'], "link" => $post['link'], "image" => $post['image_low_resolution'], "likes" => $post['likes'], "comments" => $post['comments']));
			}
			$return['status'] = "success";
		}		
	}

	echo json_encode($return);
?>