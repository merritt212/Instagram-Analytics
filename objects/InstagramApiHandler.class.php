<?php
	namespace Vivalytics;

	class InstagramApiHandler{
		private $multiHandle;
		private $requestArray;
		private $callbackArray;

		function __construct(){
		}

		public function getUserProfileEndpoint($metadata){
			if (isset($metadata['userId']) && isset($metadata['accessToken'])){
				return "https://api.instagram.com/v1/users/".$metadata['userId']."/?access_token=".$metadata['accessToken'];
			} else{
				return false;
			}
		}

		public function getUserMediaRecentEndpoint($metadata){
			if (isset($metadata['userId']) && isset($metadata['accessToken'])){
				$parameters = "access_token=".$metadata['accessToken'];
				if (isset($metadata['maxTimestamp'])){
					$minTimestamp = 0;
					$parameters .= "&min_timestamp=".$minTimestamp."&max_timestamp=".$metadata['maxTimestamp'];
				}
				if (isset($metadata['count'])){
					$parameters .= "&count=".$metadata['count'];
				}
				return "https://api.instagram.com/v1/users/".$metadata['userId']."/media/recent/?".$parameters;
			} else{
				return false;
			}
		}

		public function getMediaSearchEndpoint($metadata){
			if (isset($metadata['latitude']) && isset($metadata['longitude']) && isset($metadata['accessToken'])){
				$parameters= "lat=".$metadata['latitude']."&lng=".$metadata['longitude']."&access_token=".$metadata['accessToken'];
				if (isset($metadata['maxTimestamp'])){
					$minTimestamp = $metadata['maxTimestamp'] - 7*24*60*60;
					$parameters .= "&max_timestamp=".$metadata['maxTimestamp']."&min_timestamp=".$minTimestamp;
				}
				return "https://api.instagram.com/v1/media/search?".$parameters;
			} else{
				return false;
			}
		}

		public function initRequests(){
			$this->multiHandle = curl_multi_init();
			$this->requestArray = array();
		}

		public function loadRequest($requestType, $metadata, $callback){
			switch ($requestType){
				case "user_profile":
					$url = $this->getUserProfileEndpoint($metadata);
					$return = $this->curlHelper($url, $requestType, $callback);
					break;
				case "user_media_all":
				case "user_media_recent":
					$url = $this->getUserMediaRecentEndpoint($metadata);
					$return = $this->curlHelper($url, $requestType, $callback);
					break;
				case "media_search":
					$url = $this->getMediaSearchEndpoint($metadata);
					$return = $this->curlHelper($url, $requestType, $callback);
				default:
					$return = false;
					break;
			}
			return $return;
		}

		public function executeRequests(){
			foreach ($this->requestArray as $i => $request){
				curl_multi_add_handle($this->multiHandle, $request["handle"]);
			}

			do {
				$status = curl_multi_exec($this->multiHandle, $active);
				$info = curl_multi_info_read($this->multiHandle);
			} while ($status === CURLM_CALL_MULTI_PERFORM || $active);

			foreach ($this->requestArray as $i => $request){
				$result = curl_multi_getcontent($request["handle"]);
				$response = json_decode($result, true);
				//+\Kint::dump($response);
				if ($request['endpoint'] == "user_media_all"){
					$request["callback"]($response, array($this, "executeCustomRequest"));
				} else{
					$request["callback"]($response);
				}
				curl_close($request["handle"]);
			}
		}

		public function executeCustomRequest($url, $callback = false){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			$response = json_decode($result, true);
			if ($callback){
				$callback($response, array($this, "executeCustomRequest"));
			} else{
				return $response;
			}		
		}

		private function curlHelper($url, $endpoint, $callback){
			if ($url){
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				array_push($this->requestArray, array("handle" => $ch, "endpoint" => $endpoint, "callback" => $callback));
			} else{
				return false;
			}
		}
	}