<?php
	namespace Vivalytics;

	class InstagramPost{
		private $pdo;
		private $postId;

		function __construct(\PDO $pdo, $postId){
			$this->pdo = $pdo;
			$this->postId = $postId;
		}

		function getPost(){
			$statement = $this->pdo->prepare("SELECT * FROM instagram_posts WHERE post_id = :postId");
			$statement->execute(array(":postId" => $this->postId));

			if ($statement){
				return $statement->fetch(\PDO::FETCH_ASSOC);
			} else{
				return false;
			}
		}

		function getPostCounts($start = false, $end = false){
			if ($start && $end){
				$statement = $this->pdo->prepare("SELECT * FROM instagram_historical_post_counts WHERE post_id = :postId AND time >= :start AND time <= :end");
				$statement->execute(array(":postId" => $this->postId, ":start" => $start, ":end" => $end));
			} else{
				$statement = $this->pdo->prepare("SELECT * FROM instagram_historical_post_counts WHERE post_id = :postId");
				$statement->execute(array(":postId" => $this->postId));
			}

			return $statement;
		}
	}
