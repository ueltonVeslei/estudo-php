<?php
/**
 * Onestic - Smart PBMs
 *
 * @title      Magento -> Módulo Smart PBMs
 * @category   Integração
 * @package    Onestic_Smartpbm
 * @author     Onestic
 * @copyright  Copyright (c) 2016 Onestic
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Onestic_Smartpbm_Model_Pbms_Abstract {
	
    protected $_url;
    protected $_environment;
    protected $_name;
    protected $_label;
    protected $_clientType = 'SOAP';
    
    protected $_client = null;
    
    CONST ENV_DEV 	= 'D';
    CONST ENV_PROD 	= 'P';
    
    public function getName() {
        return $this->_label;
    }
    
	protected function getClient() {
	    if (!$this->_client) {
	        $this->_debug("getClient URL: " . $this->_url);
	        if ($this->_clientType == 'SOAP') {
                $this->_client = new SoapClient($this->_url);
            } elseif ($this->_clientType == 'REST') {
	            $this->_client = Mage::getModel('smartpbm/client_rest');
	            $this->_client->init($this->_url);
            }
	    }
	    
	    return $this->_client;
	}
	
	protected function _debug($string) {
	    if ($this->_environment == self::ENV_PROD) {
            Mage::log($string,0,$this->_name . '.log');
        }	    
    }
    
    public function validarCnpj($cnpj) {
        $cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);
        // Valida tamanho
        $invalidos = [
            '00000000000000',
            '11111111111111',
            '22222222222222',
            '33333333333333',
            '44444444444444',
            '55555555555555',
            '66666666666666',
            '77777777777777',
            '88888888888888',
            '99999999999999'
        ];

        // Verifica se o CNPJ está na lista de inválidos
        if (in_array($cnpj, $invalidos)) {
            return false;
        }
        if (strlen($cnpj) != 14)
            return false;
        // Valida primeiro dígito verificador
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj{$i} * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        if ($cnpj{12} != ($resto < 2 ? 0 : 11 - $resto))
            return false;
        // Valida segundo dígito verificador
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj{$i} * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        return $cnpj{13} == ($resto < 2 ? 0 : 11 - $resto);
    }

    public function validaEan($codigo)
    {
        $result = (strlen($codigo) == 13);
        if ($result)
        {
            $checkSum = '131313131313';

            $lenArrayCodigo = strlen($codigo) - 1;
            $digito = $codigo[$lenArrayCodigo];
            $ean = substr($codigo, 0, $lenArrayCodigo);

            $sum = 0;
            for ($i = 0; $i <= strlen($ean) - 1; $i+=1)
            {
                $sum += $ean[$i] * $checkSum[$i];
            }
            $calculo = 10 - ($sum%10);
            $result = ($digito == $calculo);
        }
        return $result;
    }
 
	public function validaCpf($cpf)
    {
        $soma = 0;
        $resto;
        if ($cpf == "00000000000" ||
            $cpf == "11111111111" ||
            $cpf == "22222222222" ||
            $cpf == "33333333333" ||
            $cpf == "44444444444" ||
            $cpf == "55555555555" ||
            $cpf == "66666666666" ||
            $cpf == "77777777777" ||
            $cpf == "88888888888" ||
            $cpf == "99999999999")
            return false;

        for ($i = 1; $i <= 9; $i+=1)
        {
            $resto = substr($cpf, $i - 1, 1);
            $soma += substr($cpf, $i - 1, 1) * (11 - $i);
        }
        $resto = ($soma * 10) % 11;

        if (($resto == 10) || ($resto == 11))
            $resto = 0;
        if ($resto != substr($cpf, 9, 1))
            return false;

        $soma = 0;
        for ($i = 1; $i <= 10; $i++)
            $soma = $soma + substr($cpf, $i - 1, 1) * (12 - i);
        $resto = ($soma * 10) % 11;

        if (($resto == 10) || ($resto == 11))
            $resto = 0;
        if ($resto != substr($cpf, 10, 1))
            return false;
        return true;
    }
}