<?php
class App {

	static function carregaClasse() {
		spl_autoload_register(function($classname){
			if (strpos($classname,'_') === false && $classname != 'app') {
				$classname = 'Core_' . $classname;
			}
			$classname = str_replace('_','/',$classname);
			$path = BASEPATH . '/app/' . $classname . '.php';
			if(file_exists($path))
				include_once($path);
		});
	}

	static function run() {
		self::carregaClasse();
		Session::start();
		$router = new Router();
		$router->response();
	}

}