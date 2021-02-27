<?php
class App {

	static function autoload() {
		spl_autoload_register(function($classname){
			if (strpos($classname,'_') === false && $classname != 'app') {
				$classname = 'Core_' . $classname;
			}
			$classname = str_replace('_','/',$classname);
			$path = BASEPATH . DS . 'app' . DS . $classname . '.php';
			include_once($path);
		});
	}

	static function run() {
		self::autoload();
		Session::start();
		$router = new Router();
		$router->response();
	}

}