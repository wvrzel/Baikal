<?php

namespace BaikalAdmin\Core;

class Auth {
	static function assertEnabled() {
		if(!defined("BAIKAL_ADMIN_ENABLED") || BAIKAL_ADMIN_ENABLED !== TRUE) {
			die("<h1>Ba&iuml;kal Admin is disabled.</h1>To enable it, set BAIKAL_ADMIN_ENABLED to TRUE in <b>Specific/config.php</b>");
		}

		$bLocked = TRUE;
		$sEnableFile = BAIKAL_PATH_SPECIFIC . "ENABLE_ADMIN";
		if(file_exists($sEnableFile)) {

			clearstatcache();
			$iTime = intval(filemtime($sEnableFile));
			if((time() - $iTime) < 3600) {
				// file has been created more than an hour ago
				// delete and declare locked

				$bLocked = FALSE;
			} else {
				if(!@unlink($sEnableFile)) {
					die("<h1>Ba&iuml;kal Admin is locked.</h1>To unlock it, delete and re-create an empty file named ENABLE_ADMIN in <b>Specific/config.php</b>");
				}
			}
		}

		if($bLocked) {
			die("<h1>Ba&iuml;kal Admin is locked.</h1>To unlock it, create an empty file named ENABLE_ADMIN in <b>Specific/</b>");
		} else {
			// update filemtime
			@touch($sEnableFile);
		}
	}

	static function assertAuthentified() {
		if(!self::isAuthentified()) {
			header(utf8_decode('WWW-Authenticate: Basic realm="Baïkal admin"'));
			header('HTTP/1.0 401 Unauthorized'); 
			die("Please authenticate.");
		}

		return TRUE;
	}

	static function isAuthentified() {

		if(array_key_exists("PHP_AUTH_USER", $_SERVER)) {
			$sUser = $_SERVER["PHP_AUTH_USER"];
		} else {
			$sUser = FALSE;
		}

		if(array_key_exists("PHP_AUTH_PW", $_SERVER)) {
			$sPass = $_SERVER["PHP_AUTH_PW"];
		} else {
			$sPass = FALSE;
		}

		$sPassHash = self::hashAdminPassword($sPass);

		if($sUser === "admin" && $sPassHash === BAIKAL_ADMIN_PASSWORDHASH) {
			return TRUE;
		}

		return FALSE;
	}

	static function hashAdminPassword($sPassword) {
		return md5('admin:' . BAIKAL_AUTH_REALM . ':' . $sPassword);
	}
}