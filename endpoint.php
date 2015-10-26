<?php
	require_once(dirname(__FILE__)."/config/config.php");
	require_once(dirname(__FILE__)."/kint/Kint.class.php");

	if (isset($_GET['code'])){
		$code = $_GET['code'];

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, "https://api.instagram.com/oauth/access_token");
		curl_setopt($ch, CURLOPT_POST, 1);
		curL_setopt($ch, CURLOPT_POSTFIELDS, "client_id=".INSTAGRAM_CLIENT_ID."&client_secret=".INSTAGRAM_CLIENT_SECRET."&grant_type=authorization_code&redirect_uri=http://104.131.134.53/endpoint.php&code=".$code);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$output = curl_exec($ch);
		curl_close($ch);

		$data = json_decode($output, true);

		$pdo = new PDO('mysql:host=localhost;dbname=vivaDev', 'vivaDev', 'u62MjHL3B4n3PBgnJM05', array(PDO::ATTR_EMULATE_PREPARES => false, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));

		$accessToken = $data['access_token'];
		$id = $data['user']['id'];
		$username = $data['user']['username'];
		$fullName = $data['user']['full_name'];
		$bio = $data['user']['bio'];
		$website = $data['user']['website'];
		$profilePicture = $data['user']['profile_picture'];

		$statement = $pdo->prepare("INSERT INTO instagram_accounts (id, username, full_name, bio, website, profile_picture, access_token, update_every) VALUES (:id, :username, :fullName, :bio, :website, :profilePicture, :accessToken, 1)");
		$statement->execute(array(":id" => $id, ":username" => $username, ":fullName" => $fullName, ":bio" => $bio, ":website" => $website, ":profilePicture" => $profilePicture, ":accessToken" => $accessToken));

		+\Kint::dump($data);
		+\Kint::dump($output);
	} else{
		echo "<p>No code provided.</p>";
	}
	
?>
