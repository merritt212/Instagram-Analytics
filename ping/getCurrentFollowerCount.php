<?php
	require_once($_SERVER['DOCUMENT_ROOT']."/config/config.php");
	require_oncE($_SERVER['DOCUMENT_ROOT']."/autoload.php");

	use Vivalytics\InstagramApiHandler as InstagramApiHandler;
	use Vivalytics\UserEndpointRequest as UserEndpointRequest;

	$return = array("status" => "failure");

	if (isset($_GET['accountId'])){
		$mem = new Memcached();
		$mem->addServer("localhost", 11211);

		if (!($followerCount = $mem->get($_GET['accountId'].time())) || !is_int($followerCount)){
			$apiHandler = new InstagramApiHandler();
			$apiHandler->initRequests();
			$route = $apiHandler->getUserProfileEndpoint(array("userId" => $_GET['accountId'], "accessToken" => INSTAGRAM_ACCESS_TOKEN));
			$response = $apiHandler->executeCustomRequest($route);

			if ($response && $response['meta']['code'] == INSTAGRAM_API_SUCCESS_CODE){
				$followerCount = $response['data']['counts']['followed_by'];
				$mem->set($_GET['accountId'].time(), $followerCount, 60);
				$return['status'] = "success";
				$return['method'] = "live";
				$return['followerCount'] = array(time(), $followerCount);
			}
		} else{
			$return['status'] = "success";
			$return['method'] = "cached";
			$return['followerCount'] = array(time(), $followerCount);
		}
		
	}

	echo json_encode($return);
?>