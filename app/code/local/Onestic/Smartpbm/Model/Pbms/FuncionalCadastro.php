<?php
class Onestic_Smartpbm_Model_Pbms_FuncionalCadastro extends Onestic_Smartpbm_Model_Pbms_Abstract {
	/**
	 * Atributos da API
	 */
    protected $_name = 'funcional';
    protected $_label = 'Funcional';
    protected $_login;
    protected $_password;
    protected $_cnpj;
    protected $_crf;
    protected $_ufcrf;
    protected $_tipo;
    protected $_simulacao;
    protected $_apiKey;
    protected $_clientType = 'REST';
	
    protected $_urlP = 'https://acessounico.funcionalmais.com/rest';
    protected $_urlD = 'https://acessohml.funcionalmais.com/AcessoUnicoExterno/REST';
    
	/**
	 * Atributos do cadastro
	 */
	protected $_cpf;
	protected $_ean;
	protected $_camposBeneficiario;
	protected $_errors;
    
	public function __construct() {
		$this->_environment = Mage::helper('smartpbm')->getConfigData('funcional/environment');
		$this->_url = $this->{'_url' . $this->_environment};
		$this->_login = Mage::helper('smartpbm')->getConfigData('funcional/login');
		$this->_password = Mage::helper('smartpbm')->getConfigData('funcional/password');
		$this->_cnpj = Mage::helper('smartpbm')->getConfigData('funcional/cnpj');
		$this->_crf = Mage::helper('smartpbm')->getConfigData('funcional/crf');
		$this->_ufcrf = Mage::helper('smartpbm')->getConfigData('funcional/ufcrf');
		$this->_tipo = Mage::helper('smartpbm')->getConfigData('funcional/tipo');
		$this->_simulacao = Mage::helper('smartpbm')->getConfigData('funcional/simulacao');
		$this->_errors = array();
	}
	
	protected function authentication() {
	    if (!$this->_apiKey) {
            $body = [
                'Login'         => $this->_login,
                'Password'      => $this->_password
            ];
            $result = $this->getClient()->post('api/Authentication',$body);
            if ($result['httpCode'] == 200) {
                $this->_apiKey = $result['body']->ApiKey->Key;
                $this->getClient()->init($this->_url, $this->_apiKey);
            }
        }
    }

    public function registro($data) {
        if (isset($data['beneficiario'])) {
            return $this->cadastrarBeneficiario($data);
        } else {
            return $this->cadastrarProduto($data);
        }
    }

    public function cadastrarBeneficiario($data) {
        Mage::log('METHOD: cadastrarBeneficiario', null, 'funcional.log');
		//$this->valida($data);
		if(count($this->_errors) == 0){
			$this->authentication();
			$body = [
				'CNPJ'		          => $this->_cnpj,
				'CPF'  				  => $data['document'],
				'EAN'         		  => $data['ean'],
                'CamposBeneficiario'  => $this->parseFields('beneficiario', $data),
                'CamposPaciente'      => $this->parseFields('paciente', $data),
                'CamposProduto'       => $this->parseFields('produto', $data),
			];

			$result = $this->getClient()->post('api/CadastroUnico/Beneficiario', $body);
			$message = '';

			if ($result['httpCode'] == 200) {
				$resultBody = $result['body'];
				if (isset($resultBody->Errors)) {
					foreach($resultBody->Errors as $error) {
						$message .= $error->Message . PHP_EOL;
					}
				}
				if ($resultBody->IsValid) {
					return $resultBody;
				}
			}

			return $message;
		}
        return $this->_errors;
    }

    public function cadastrarProduto($data) {
        Mage::log('METHOD: cadastrarProduto', null, 'funcional.log');

        if(count($this->_errors) == 0){
            $this->authentication();
            $body = [
                'CNPJ'		          => $this->_cnpj,
                'CPF'  				  => $data['document'],
                'EAN'         		  => $data['ean'],
                'CamposProduto'       => $this->parseFields('produto', $data),
            ];

            $result = $this->getClient()->post('api/CadastroUnico/Produto', $body);
            $message = '';

            if ($result['httpCode'] == 200) {
                $resultBody = $result['body'];
                if (isset($resultBody->Errors)) {
                    foreach($resultBody->Errors as $error) {
                        $message .= $error->Message . PHP_EOL;
                    }
                }
                if ($resultBody->IsValid) {
                    return $resultBody;
                }
            }

            return $message;
        }
        return $this->_errors;
    }

	public function valida($data) {
		if(!$this->validaEan($data['ean']))
			$this->_errors[] = 'Código EAN13 inválido';

		if(!$this->validaCpf($data['document']))
			$this->_errors[] =  'Código cpf inválido';

		$estruturaBeneficiarioErrors = $this->validaEstruturaBeneficiario($data['estruturaBeneficiario']);
		if(count($estruturaBeneficiarioErrors) > 0)
			$this->_errors[] = $estruturaBeneficiarioErrors;
	}

	protected function validaEstruturaBeneficiario($estruturaBeneficiario){
		if($estruturaBeneficiario['nome'] == '')
			$this->_errors[] = 'Informe o seu primeiro nome!';
		if($estruturaBeneficiario['sobrenome'] == '')
			$this->_errors[] = 'Informe o seu sobrenome!';
		if($estruturaBeneficiario['email'] == '')
			$this->_errors[] = 'Informe o seu email!';
		if($estruturaBeneficiario['telefone'] == '')
			$this->_errors[] = 'Informe o seu telefone!';
	}

	public function verificaElegibilidade($data) {
        Mage::log('METHOD: verificaElegibilidade', null, 'funcional.log');
        $this->authentication();
        $result = $this->getClient()->get('api/CadastroUnico/AvaliarElegibilidade?CPF=' . $data['cpf'] . '&EAN=' . $data['ean']);
        $message = '';

        if ($result['httpCode'] == 200) {
            $resultBody = $result['body'];

            if ($resultBody->ResultadoValidacao->Errors) {
                foreach($resultBody->Errors as $error) {
                    $message .= $error->Message . PHP_EOL;
                }

                return ['message' => $message];
            }

            if ($resultBody->ResultadoValidacao->IsValid) {
                if ($resultBody->AvaliacaoElegibilidade->ExisteProdutoPrograma == false) {
                    return ['message' => 'Produto não elegível para desconto!'];
                }

                if ($resultBody->AvaliacaoElegibilidade->RequisitarCadastroPrograma == true) {
                    // Verifica a política cadastral
                    return $resultBody->PoliticaCadastral;
                }

                if ($resultBody->AvaliacaoElegibilidade->RequisitarCadastroProduto == true) {
                    // Verifica a política cadastral
                    return $resultBody->PoliticaCadastral;
                }

                return $resultBody;
            }
        }
        return ['message' => 'Você não está elegível para o programa!'];
    }

    protected function parseFields($location, $data)
    {
        $result = [];

        if (isset($data[$location])) {
            foreach ($data[$location] as $label => $value) {
                $result[] = [
                    'Campo' => $label,
                    'Valor' => $value
                ];
            }
        }

        return $result;
    }
}