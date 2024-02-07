<?php


class SystemUtils {
	// Returns a file size limit in bytes based on the PHP upload_max_filesize
	// and post_max_size
	static function file_upload_max_size() {
		static $max_size = -1;

		if ($max_size < 0) {
			// Start with post_max_size.
			$post_max_size = SystemUtils::parse_size(ini_get('post_max_size'));
			if ($post_max_size > 0) {
				$max_size = $post_max_size;
			}

			// If upload_max_size is less, then reduce. Except if upload_max_size is
			// zero, which indicates no limit.
			$upload_max = SystemUtils::parse_size(ini_get('upload_max_filesize'));
			if ($upload_max > 0 && $upload_max < $max_size) {
				$max_size = $upload_max;
			}
		}
		return $max_size;
	}

	static function parse_size($size) {
		$unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
		/** @noinspection RegExpRedundantEscape */
		$size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
		if ($unit) {
			// Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
			return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
		} else {
			return round($size);
		}
	}

	static function recursive_rmdir($dir): bool {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (is_dir($dir . "/" . $object) && !is_link($dir . "/" . $object)) {
						SystemUtils::recursive_rmdir($dir . "/" . $object);
					} else {
						unlink($dir . "/" . $object);
					}
				}
			}
			rmdir($dir);
			return true;
		} else {
			return false;
		}
	}

	static function validateAge($minimumAge, $dob): bool {
		$today = date("d-m-Y");//today's date

		$dob = new DateTime($dob);
		$today = new DateTime($today);

		$interval = $dob->diff($today);

		$age = $interval->y;

		if ($age >= $minimumAge) {
			return true;
		}
		return false;
	}

	static function validateAddress($streetAddress, $city, $state, $zip): bool {
		$baseUrl = 'https://api.usps.com';
		require_once ROOT_DIR . '/sys/CurlWrapper.php';

		//GET OAUTH TOKEN
		$getOauthToken = new CurlWrapper();
		$getOauthToken->addCustomHeaders([
			'Content-Type: application/x-www-form-urlencoded',
			'Accept: application/json',
		], false);

		require_once ROOT_DIR . '/sys/Administration/USPS.php';
		$uspsInfo = USPS::getUSPSInfo();
		$postParams = [
			'grant_type'=>'client_credentials',
			'client_id'=>$uspsInfo->clientId,
			'client_secret'=>$uspsInfo->clientSecret,
		];

		$url = $baseUrl . '/oauth2/v3/token';
		$accessTokenResults = $getOauthToken->curlPostPage($url, $postParams);
		$accessToken = "";
		if ($accessTokenResults) {
			$jsonResponse = json_decode($accessTokenResults);
			if (isset($jsonResponse->access_token)) {
				$accessToken = $jsonResponse->access_token;
			}
		}

		//ADDRESS VALIDATION
		$validateAddress = new CurlWrapper();
		$validateAddress->addCustomHeaders([
			'Authorization: Bearer ' . $accessToken,
			'Content-Type: application/x-www-form-urlencoded',
			'Accept: application/json',
		], true);

		$url = $baseUrl . '/addresses/v3/address?streetAddress=' . urlencode($streetAddress) . '&city=' . $city . '&state=' . $state . '&ZIPCode=' . $zip;
		$validateAddressResults = $validateAddress->curlGetPage($url);
		ExternalRequestLogEntry::logRequest('usps.validateAddress', 'GET', $url, $validateAddress->getHeaders(), '', $validateAddress->getResponseCode(), $validateAddressResults, []);

		if ($validateAddress->getResponseCode() == 200){
			return true;
		}

		return false;
	}
}