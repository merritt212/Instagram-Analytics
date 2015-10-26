<?php
	require_once(dirname(dirname(__FILE__))."/autoload.php");
	use Vivalytics\InstagramApiHandler as InstagramApiHandler;
	use Vivalytics\UserEndpointRequest as UserEndpointRequest;

	$pdo = new \PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_USER_PASSWORD, array(PDO::ATTR_EMULATE_PREPARES => false, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"));

	$statement = $pdo->query("SELECT * FROM instagram_accounts");
	$apiHandler = new InstagramApiHandler($pdo);
	$apiHandler->initRequests();

	foreach ($statement as $user){
		// Check to see if the user has an access token assigned, because if they do we can use the self instead of the generic user endpoint (better for rate limits).
		if (strlen($user['access_token']) >= 20){
			$apiHandler->loadRequest("user_media_all", array("userId" => "self", "accessToken" => INSTAGRAM_ACCESS_TOKEN, "maxTimestamp" => time()), array(new UserEndpointRequest($pdo, array("isFirstMediaPage" => true)), "storeMediaAll"));
		} else{
			$apiHandler->loadRequest("user_media_all", array("userId" => $user['id'], "accessToken" => INSTAGRAM_ACCESS_TOKEN, "maxTimestamp" => time()), array(new UserEndpointRequest($pdo, array("isFirstMediaPage" => true)), "storeMediaAll"));
		}
	}

	$apiHandler->executeRequests();