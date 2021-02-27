<?php
class Standard {
	CONST STATUS200			= 200;
	CONST STATUS400			= 400;
	CONST STATUS403			= 403;
	CONST STATUS404			= 404;
	CONST STATUS405			= 405;
	CONST STATUS500			= 500;

	public static function getStatus($code) {
		$status = array(
            200 => 'OK',
            400 => 'Error In Application',
            403 => 'Invalid Credentials',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        );
        return $code . ' ' . (($status[$code]) ? $status[$code] : $status[500]);
	}

	public static function isAllowed($method) {
		$allowedMethods = array('GET','POST','PUT','DELETE','OPTIONS');
		if (in_array($method,$allowedMethods)) {
			return true;
		}

		return false;
	}
}