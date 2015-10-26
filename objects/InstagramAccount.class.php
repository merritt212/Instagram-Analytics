<?php
	namespace Vivalytics;

	class InstagramAccount{
		private $pdo;
		private $accountId;

		function __construct(\PDO $pdo, $accountId){
			$this->pdo = $pdo;
			$this->accountId = $accountId;
		}

		function getAccount(){
			$statement = $this->pdo->prepare("SELECT * FROM instagram_accounts WHERE id = :accountId");
			$statement->execute(array(":accountId" => $this->accountId));

			if ($statement){
				return $statement->fetch(\PDO::FETCH_ASSOC);
			} else{
				return false;
			}
		}

		function getUserCounts($start = false, $end = false){
			if ($start && $end){
				$statement = $this->pdo->prepare("SELECT * FROM instagram_historical_user_counts WHERE id = :accountId AND time >= :start AND time <= :end");
				$statement->execute(array(":accountId" => $this->accountId, ":start" => $start, ":end" => $end));
			} else{
				$statement = $this->pdo->prepare("SELECT * FROM instagram_historical_user_counts WHERE id = :accountId");
				$statement->execute(array(":accountId" => $this->accountId));
			}

			return $statement;
		}
	}
