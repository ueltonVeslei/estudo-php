<?php
class Session {

	public static function start() {
		session_start();
	}

	public static function isLoggedIn() {
		if (isset($_SESSION['logged_in'])) {
			if ($_SESSION['logged_in']) {
				return true;
			}
		}

		return false;
	}

	public static function getLoggedInUser() {
		if (self::isLoggedIn()) {
			return $_SESSION['logged_in'];
		}

		return false;
	}

	public static function getLoggedInUserId() {
		if (self::isLoggedIn()) {
			return self::getLoggedInUser()->ID;
		}

		return false;
	}

	public static function setLoggedIn($user) {
		$_SESSION['logged_in'] = $user;
	}

	public static function setData($field, $value) {
		$_SESSION[$field] = $value;
	}

	public static function logout(){
		session_destroy();
	}

	public static function getData($field) {
		if (isset($_SESSION[$field])) {
			return $_SESSION[$field];
		}

		return false;
	}

	public static function unsData($field) {
		unset($_SESSION[$field]);
	}


}