<?php

class Intelipost_Push_Helper_Data
extends Mage_Core_Helper_Abstract
{

	const moduleName = 'PUSH';
	const LOG_FILENAME = 'intelipost_push.log';

	public function getModuleName()
	{
		return self::moduleName;
	}
		
	public function getModuleHandle()
	{
		return 'Intelipost_Push';
	}

	public function createRomaneioPath($filename)
	{
		$retorno = '';

		$root_dir_path = Mage::getBaseDir();
		$media_dir_path = $root_dir_path.DIRECTORY_SEPARATOR.'media';

		$intelipost_dir_path = $media_dir_path.DIRECTORY_SEPARATOR.'intelipost'.DIRECTORY_SEPARATOR.'push'.DIRECTORY_SEPARATOR.'romaneios';

		try
		{
			if (!is_dir($intelipost_dir_path)) {
				mkdir ($intelipost_dir_path, 0777, true);					
			}

			$retorno = $intelipost_dir_path.DIRECTORY_SEPARATOR.$filename;
		}
		catch (Exception $e)
		{
			Mage::log($e->getMessage());
		}
				
		return $retorno;
	}

	public function log($message, $code = null, $level = null)
	{           
	    
	    if(!is_null($code) && !empty($code))
	    {
	        $message = sprintf('%s: %s', $code, $message);
	    }

	    if (is_array($message))
	    {
	        $message = print_r($message, true);
	    }

	    Mage::log($message, $level, self::LOG_FILENAME);

	    return true;
	}
	
}

