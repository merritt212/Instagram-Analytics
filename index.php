<?php
	if (isset($_GET['access_code']) && $_GET['access_code'] == "WuEsPnRa0GqfZrenek2I"){
		if (isset($_GET['username'])){
			require_once(dirname(__FILE__)."/config/config.php");
			require_once(dirname(__FILE__)."/kint/Kint.class.php");
			$pdo = new \PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_USER_PASSWORD, array(PDO::ATTR_EMULATE_PREPARES => false, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"));

			$statement = $pdo->prepare("SELECT * FROM instagram_accounts WHERE username = :username");
			$statement->execute(array(":username" => $_GET['username']));

			$user = $statement->fetch(PDO::FETCH_ASSOC);
			$posts = $pdo->query("SELECT * FROM instagram_posts ORDER BY time_created DESC LIMIT 12 OFFSET 0");
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title>Instagram Analytics</title>

		<!-- Bootstrap -->
		<link rel="stylesheet" href="<?php echo SITEURL; ?>public/css/bootstrap.min.css">
		<link rel="stylesheet" href="<?php echo SITEURL; ?>public/css/daterangepicker.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
		<link rel="stylesheet" href="<?php echo SITEURL; ?>public/css/site.css">
	</head>
	<body>
		<div class="container-fluid">
			<!-- Zoom chart showing the overall followers on the instagram account -->
			<div class="row">
				<div class="col-md-7 col">
					<div class="btn-group pull-right" role="group">
						<button type="button" class="btn btn-default" id="overall-followers-daterange"><i class="fa fa-calendar"></i></button>
						<button type="button" class="btn btn-default overall-followers-range" timeinterval-value="0">All</button>
						<button type="button" class="btn btn-default overall-followers-range" timeinterval-value="604800">7 days</button>
						<button type="button" class="btn btn-default overall-followers-range active" timeinterval-value="259200">3 days</button>
					</div>
				</div>
			</div>

			<!-- Live update follower chart -->
			<div class="row">
				<div class="col-md-7">
					<div id="chart-overall-followers"></div>
				</div>
				<div class="col-md-5">
					<div id="live-overall-followers"></div>
				</div>
			</div>

			<!-- Iterate through the next 12 most popular posts -->
			<div id="post-tracking">
				<h1>Test</h1>
			<?php
				foreach ($posts as $i => $post){
					if ($i % 6 == 0){ 
			?>
					<div class="row">
			<?php 
					}
			?>
						<div class="col-md-2">
							<span class="label-white"><i class="fa fa-heart"></i> <?php echo $post['likes']; ?></span>
							<span class="label-white"><i class="fa fa-comments"></i> <?php echo $post['comments']?></span>
							<button type="button" class="btn btn-default btn-xs pull-right show-post-analytics" post-id="<?php echo $post['post_id']; ?>">Analytics</button>
							<a href="<?php echo $post['link']; ?>"><img src="<?php echo $post['image_low_resolution']; ?>" class="img-responsive"></a>
						</div>
			<?php
					if ($i % 6 == 5){
			?>
						</div>
			<?php
					}
				}
			?>
			</div>

			<!-- Modal which holds the post analytics -->
			<div class="modal fade" id="post-analytics-modal" tabindex="-1" role="dialog" aria-labelledby="postAnalyticsModal">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<!--
						<div class="modal-header">
							<div class="btn-group" role="group">
								<button type="button" class="btn btn-default post-analytics-daterange" class="btn btn-default"><i class="fa fa-calendar"></i></button>
								<button type="button" class="btn btn-default post-analytics-range" timeinterval-value="0">All</button>
								<button type="button" class="btn btn-default post-analytics-range" timeinterval-value="604800">7 days</button>
								<button type="button" class="btn btn-default post-analytics-range active" timeinterval-value="259200">3 days</button>
							</div>
						</div>
						-->
						<div class="modal-body">
						</div>
					</div>
				</div>
			</div>
		</div>
		<input type="hidden" id="accountId" value="<?php echo $user['id']; ?>">
		<input type="hidden" id="followerCount" value="<?php echo $user['followed_by']; ?>">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
		<script src="<?php echo SITEURL; ?>public/js/bootstrap.min.js"></script>
		<script src="<?php echo SITEURL; ?>public/js/moment.js"></script>
		<script src="<?php echo SITEURL; ?>public/js/daterangepicker.js"></script>
		<script src="http://code.highcharts.com/highcharts.js"></script>
		<script src="<?php echo SITEURL; ?>public/js/site.js"></script>
	</body>
</html>
<?php
		}
	}
?>