<?php
	namespace Vivalytics;

	class UserEndpointRequest{
		private $pdo;
		private $storeOldPostCounts;
		private $isFirstMediaPage;
		private $onlyFindPosts;

		function __construct(\PDO $pdo, $options = array()){
			$this->pdo = $pdo;
			if (isset($options['isFirstMediaPage']) && $options['isFirstMediaPage']){
				$this->isFirstMediaPage = true;
			} else{
				$this->isFirstMediaPage = false;
			}
		}

		public function storeProfile($response){
			if ($response['meta']['code'] == INSTAGRAM_API_SUCCESS_CODE){
				$statement = $this->pdo->prepare("UPDATE instagram_accounts SET username = :username, full_name = :fullName, bio = :bio, website = :website, profile_picture = :profilePicture, media_count = :mediaCount, followed_by = :followedBy, follows = :follows WHERE id = :id");
				$statement->execute(array(":username" => $response['data']['username'], ":fullName" => $response['data']['full_name'], ":bio" => $response['data']['bio'], ":website" => $response['data']['website'], ":profilePicture" => $response['data']['profile_picture'], ":mediaCount" => $response['data']['counts']['media'], ":followedBy" => $response['data']['counts']['followed_by'], ":follows" => $response['data']['counts']['follows'], ":id" => $response['data']['id']));

				$statement = $this->pdo->prepare("INSERT INTO instagram_historical_user_counts (id, media_count, followed_by, follows, time) VALUES (:id, :mediaCount, :followedBy, :follows, :time)");
				$statement->execute(array(":id" => $response['data']['id'], ":mediaCount" => $response['data']['counts']['media'], ":followedBy" => $response['data']['counts']['followed_by'], ":follows" => $response['data']['counts']['follows'], ":time" => time()));
			} else{
				return false;
			}
		}

		public function storeMediaAll($response, $callback){
			if ($response['meta']['code'] == INSTAGRAM_API_SUCCESS_CODE){
				foreach ($response['data'] as $i => $post){
					$this->storePost($post);
					if (!$this->isFirstMediaPage || ($i > SETTING_RECENT_POSTS_CUTOFF_INDEX - 1)){
						$this->storePostCounts($post);
					}
				}
				if (isset($response['pagination']['next_url'])){
					$callback($response['pagination']['next_url'], array(new UserEndpointRequest($this->pdo), "storeMediaAll"));
				}		
			}
		}

		public function storeMediaRecent($response){
			if ($response['meta']['code'] == INSTAGRAM_API_SUCCESS_CODE){
				foreach ($response['data'] as $post){
					$this->storePost($post);
					$this->storePostCounts($post);
				}
			}
		}

		private function storePost($post){
			$statement = $this->pdo->prepare("INSERT INTO instagram_posts (user_id, post_id, time_created, likes, comments, image_thumbnail, image_low_resolution, image_standard_resolution, filter, link) VALUES (:userId, :postId, :timeCreated, :likes, :comments, :imageThumbnail, :imageLowResolution, :imageStandardResolution, :filter, :link) ON DUPLICATE KEY UPDATE likes = VALUES(likes), comments = VALUES(comments)");
			$statement->execute(array(":userId" => $post['user']['id'], ":postId" => $post['id'], ":timeCreated" => $post['created_time'], ":likes" => $post['likes']['count'], ":comments" => $post['comments']['count'], ":imageThumbnail" => $post['images']['thumbnail']['url'], ":imageLowResolution" => $post['images']['low_resolution']['url'], ":imageStandardResolution" => $post['images']['standard_resolution']['url'], ":filter" => $post['filter'], ":link" => $post['link']));
		}

		private function storePostCounts($post){
			$statement = $this->pdo->prepare("INSERT INTO instagram_historical_post_counts (user_id, post_id, likes, comments, time) VALUES (:userId, :postId, :likes, :comments, :time) ON DUPLICATE KEY UPDATE likes = likes");
			$statement->execute(array(":userId" => $post['user']['id'], ":postId" => $post['id'], ":likes" => $post['likes']['count'], ":comments" => $post['comments']['count'], ":time" => time()));
		}
	}