<?php
class Controller_Preflight extends Controller {

	public function _options() {
		$this->setResponse('status',Standard::STATUS200);
		$this->setResponse('data',array('message'=>'Preflight OK'));
	}

}

