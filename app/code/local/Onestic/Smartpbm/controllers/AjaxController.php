<?php
class Onestic_Smartpbm_AjaxController extends Mage_Core_Controller_Front_Action
{
	public function activateAction() {
	    echo $this->checkBenefit();
    }
	
	public function registerAction(){
		//Obtem os parâmetros
		$data = $this->getRequest()->getParams();

		//Obtem a instância da model
		$model = Mage::getModel('smartpbm/pbms_funcionalCadastro');

		//Chama a função de cadastro
		$response = $model->registro($data);
		if (is_object($response)) {
		    if ($response->IsValid) {
                $response = $this->checkBenefit();
            }
        }

		echo $response;
	}

	protected function checkBenefit() {
        $data = $this->getRequest()->getParams();

        if (isset($data['produto'])) {
            $data['beneficio'] = array_merge($data['beneficio'], $data['produto']);
        }

        $pbm = $this->getRequest()->getParam('pbm');
        $model = Mage::getModel('smartpbm/pbms_' . $pbm);
        $discount = $model->validaBeneficio($data);

        $response = 'Problemas na validação do benefício. Por favor contate o seu programa.';

        if ($discount !== false) {
            $formattedPrice = Mage::helper('core')->currency($discount, true, false);
            $response = 'Você economizará <b>' . $formattedPrice . '</b> ao adicionar esse produto ao carrinho!';
        }

        return $response;
    }

    public function eligibilityAction(){
        //Obtem o objeto request
        $request = $this->getRequest();

        //Obtem os parâmetros
        $data['cpf'] = $request->getParam('document');
        $data['ean'] = $request->getParam('ean');

        //Obtem a instância da model
        $model = Mage::getModel('smartpbm/pbms_funcionalCadastro');

        //Chama a função de cadastro
        $response = $model->verificaElegibilidade($data);

        if (is_object($response)) {
            $result = '';

            if ($response->CamposBeneficiario) {
                $result .= $this->getLayout()->createBlock('smartpbm/register')->setFields($response->CamposBeneficiario)->toHtml();
            }

            if ($response->CamposPaciente) {
                $result .= $this->getLayout()->createBlock('smartpbm/register')->setFields($response->CamposPaciente)->toHtml();
            }

            $camposBeneficio = [
                (object)[
                    "Campo"         => "receita",
                    "NomeExibicao"  => "Data da Receita",
                    "Entidade"      => 5,
                    "Tipo"          => 5,
                    "Exibicao"      => 3
                ],
                (object)[
                    "Campo"         => "profissional",
                    "NomeExibicao"  => "Nome do Médico",
                    "Entidade"      => 5,
                    "Tipo"          => 1,
                    "Exibicao"      => 3
                ],
            ];
            if (!$response->CamposProduto) {
                $camposBeneficio[] = (object)[
                    "Campo"         => "NumeroRegistroConselho",
                    "NomeExibicao"  => "Número do CRM",
                    "Entidade"      => 5,
                    "Tipo"          => 1,
                    "Exibicao"      => 3
                ];
                $camposBeneficio[] = (object)[
                    "Campo"         => "UF",
                    "NomeExibicao"  => "Estado",
                    "Entidade"      => 5,
                    "Tipo"          => 6,
                    "Exibicao"      => 3,
                    "Opcoes"        => json_decode('[{"Texto":"Acre","Valor":"AC"},{"Texto":"Alagoas","Valor":"AL"},{"Texto":"Amazonas","Valor":"AM"},{"Texto":"Amap\u00e1","Valor":"AP"},{"Texto":"Bahia","Valor":"BA"},{"Texto":"Cear\u00e1","Valor":"CE"},{"Texto":"Distrito Federal","Valor":"DF"},{"Texto":"Espirito Santo","Valor":"ES"},{"Texto":"Goi\u00e1s","Valor":"GO"},{"Texto":"Maranh\u00e3o","Valor":"MA"},{"Texto":"Minas Gerais","Valor":"MG"},{"Texto":"Mato Grosso do Sul","Valor":"MS"},{"Texto":"Mato Grosso","Valor":"MT"},{"Texto":"Par\u00e1","Valor":"PA"},{"Texto":"Para\u00edba","Valor":"PB"},{"Texto":"Pernambuco","Valor":"PE"},{"Texto":"Piau\u00ed","Valor":"PI"},{"Texto":"Paran\u00e1","Valor":"PR"},{"Texto":"Rio de Janeiro","Valor":"RJ"},{"Texto":"Rio Grande do Norte","Valor":"RN"},{"Texto":"Rond\u00f4nia","Valor":"RO"},{"Texto":"Roraima","Valor":"RR"},{"Texto":"Rio Grande do Sul","Valor":"RS"},{"Texto":"Santa Catarina","Valor":"SC"},{"Texto":"Sergipe","Valor":"SE"},{"Texto":"S\u00e3o Paulo","Valor":"SP"},{"Texto":"Tocantins","Valor":"TO"}]'),
                ];
                $camposBeneficio[] = (object)[
                    "Campo"         => "is_activate",
                    "NomeExibicao"  => "Pode validar",
                    "Entidade"      => 5,
                    "Tipo"          => 1,
                    "Exibicao"      => 1,
                    "ValorInicial"  => '1'
                ];
            }
            $result .= $this->getLayout()->createBlock('smartpbm/register')->setFields($camposBeneficio)->toHtml();

            if ($response->CamposProduto) {
                $result .= $this->getLayout()->createBlock('smartpbm/register')->setFields($response->CamposProduto)->toHtml();
            }
        } else {
            $result = $response['message'];
        }

        //$this->getResponse()->setHeader('content-type', 'application/json', true);
        //$this->getResponse()->setBody(json_encode($));
        echo $result;
    }
}