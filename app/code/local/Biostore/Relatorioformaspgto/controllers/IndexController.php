<?php

ini_set('memory_limit', -1);

class Biostore_Relatorioformaspgto_IndexController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {

    	// Define o tempo limite para execucao dos script
    	set_time_limit(3600);

		// Obtem os dados do post
		$postData = Mage::app()->getRequest()->getPost();

		// Array que ira conter os resultados
		$resultados = array();

		// Verifica se a requisicao e do tipo post
		if ($this->getRequest()->isPost()){

			// Verifica se o form foi enviado
			if (isset($postData['from']) && isset($postData['to']) && !empty($postData['from']) && !empty($postData['to'])){

				// Calcula as datas de inicio e fim
				$tmp_inicio = explode('/', $postData['from']);
				$tmp_fim = explode('/', $postData['to']);
				$data_inicio = $tmp_inicio[2] . '-' . $tmp_inicio[1] . '-' . $tmp_inicio[0] . ' 00:00:00';
				$data_fim = $tmp_fim[2] . '-' . $tmp_fim[1] . '-' . $tmp_fim[0] . ' 23:59:59';

				// Corrige o fuso horario
				$data_inicio_certa = Mage::getModel('core/date')->gmtDate(null,strtotime($data_inicio));
				$data_fim_certa    = Mage::getModel('core/date')->gmtDate(null,strtotime($data_fim));

				// Gera o arquivo CSV
				$arquivo = $this->_gerarCSV($data_inicio_certa, $data_fim_certa);

				// Envia o arquivo para download
				$this->_enviarDownload($arquivo);


			} else {

				// Exibe a mensagem de erro
				$this->_getSession()->addError('Informe uma data inicial e uma data final.');

			}

		}

		// Obtem a chave do form
		$formKey = Mage::getSingleton('core/session')->getFormKey();

		// HTML do form
		$html = $this->_gerarHtmlForm($formKey);

		// Exibicao do HTML
        $this->loadLayout();
        $block = $this->getLayout()
       				  ->createBlock('core/text', 'form-block')
        			  ->setText($html);

        $this->_addContent($block);
        $this->renderLayout();

    }


    /**
     *
     * Metodo responsavel por enviar um arquivo para o cliente realizar o download
     * @param string $caminho
     * @return void
     *
     */
    private function _enviarDownload($caminho){
    	
    	if (is_file($caminho)){

    		// Obtem o nome do arquivo
    		$arquivo = basename($caminho);

    		// Headers
			header('Content-Description: File Transfer');
			header('Content-Disposition: attachment; filename="' . $arquivo . '"');
			header('Content-Type: application/octet-stream');
			header('Content-Transfer-Encoding: binary');
			header('Content-Length: ' . filesize($caminho));
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Expires: 0');
			
			// Envia o arquivo para download
			readfile($caminho);
    	}

    }


    /**
     *
     * Metodo responsavel por gerar o CSV com os dados corretos
     * @param string $data_inicio
     * @param string $data_fim
     * @return string
     *
     */
    private function _gerarCSV($data_inicio, $data_fim){

    		// Nome do arquivo
    		$caminho = '';

    		$total_faturado = 0.00;
    		$total_quantidade = 0;

    		if (!empty($data_inicio) && !empty($data_fim)){

	    		// Realiza a consulta
				$collection = Mage::getModel('sales/order')->getCollection()
	        	->addAttributeToFilter('created_at', array('from' => "{$data_inicio}"))
	        	->addAttributeToFilter('created_at', array('to' => "{$data_fim}"))
	        	->addAttributeToFilter('state', 'complete');

	        	// Array com dados finais
	        	$dados = array('Paypal' 		  => array('quantidade' => 0, 'total' => 0.00),
	        				   'Boleto' 		  => array('quantidade' => 0, 'total' => 0.00),
	        				   'Visa'			  => array('quantidade' => 0, 'total' => 0.00),
	        				   'Mastercard' 	  => array('quantidade' => 0, 'total' => 0.00),
	        				   'Hipercard' 		  => array('quantidade' => 0, 'total' => 0.00),
	        				   'Dinners' 		  => array('quantidade' => 0, 'total' => 0.00),
	        				   'Aura' 	  		  => array('quantidade' => 0, 'total' => 0.00),
	        				   'Elo' 	  		  => array('quantidade' => 0, 'total' => 0.00),
	        				   'American Express' => array('quantidade' => 0, 'total' => 0.00),
	        				   'Oi Paggo' 	  	  => array('quantidade' => 0, 'total' => 0.00));


	        	// Percorre os dados encontrados
	            foreach ($collection as $c){

	            	$p = $c->getPayment();
	            	$total = $c->getData('base_total_invoiced');
	            	$metodo = $p->getData('method');
	            	$tipo = $p->getData('cc_type');

	            	$chave = '';

	            	// Verifica o tipo de pagamento
	            	switch($metodo){
	            		
	            		case 'braspagcc':{

	            			$chave = '';

	            			// Se for cartao de credito, verifica o tipo do cartao
	            			switch ($tipo){
	            				case 'VI': { $chave = 'Visa'; 		 	   break; }
	            				case 'MC': { $chave = 'Mastercard'; 	   break; }
	            				case 'HI': { $chave = 'Hipercard';  	   break; }
	            				case 'DI': { $chave = 'Dinners'; 	 	   break; }
	            				case 'AU': { $chave = 'Aura'; 		 	   break; }
	            				case 'EL': { $chave = 'Elo'; 			   break; }
	            				case 'AE': { $chave = 'American Express';  break; }
	            				default: {   $chave = 'Oi Paggo';		   break; }
	            			}

	            			break;

	            		}
	            		
	            		case 'braspagboleto': { $chave = 'Boleto'; break; }
	      
	            		case 'paypal_standard': { $chave = 'Paypal'; break; }
	            		

	            	}

	            	// Adiciona os dados ao array de dados finais
	            	if (isset($dados[$chave])){
	            		$total_faturado += $total;
	            		$total_quantidade++;
	            		$dados[$chave]['quantidade']++;
	            		$dados[$chave]['total'] += $total;
	            	}

	        }

            // String que sera enviada para o arquivo CSV
            $csv = "forma_pagamento;quantidade;total_faturado\r\n";

            // Cria a string com os dados finais
            foreach ($dados as $tipo_pgto => $dados_pgto){
            	$csv .= "{$tipo_pgto};{$dados_pgto['quantidade']};{$dados_pgto['total']}\r\n";
            }

			// Envia os dados para o arquivo
            $arquivo = 'relatorio-formas-pgto-' . date('d-m-Y-H-i-s') . '.csv';
			$caminho = Mage::getBaseDir('tmp') . '/' . $arquivo;
			file_put_contents($caminho, $csv);

        }

        return $caminho;

    }


    /**
     *
     * Metodo responsavel por gerar o HTML do formulario para geracao do relatorio
     * @param string $formKey
     * @return string
     *
     */
    private function _gerarHtmlForm($formKey = ''){
    	return '
			<form name="report" action="" method="post">
			<input name="form_key" type="hidden" value="' . $formKey . '">
			<table cellspacing="0" class="form-list">
            <tbody>
                <tr>
        			<td colspan="2" class="hidden"><input id="sales_report_store_ids" name="store_ids" value="" type="hidden"/></td>
    			</tr>

				<tr>
        			<td class="label"><label for="sales_report_from">De <span class="required">*</span></label></td>
    				<td class="value">
        				<input name="from" id="sales_report_from" value="" title="De" type="text" class=" required-entry input-text" style="width:110px !important;" /> <img src="' . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . 'adminhtml/default/default/images/grid-cal.gif" alt="" class="v-middle" id="sales_report_from_trig" title="Select Date" style="" />
				            <script type="text/javascript">
				            //<![CDATA[
				                Calendar.setup({
				                    inputField: "sales_report_from",
				                    ifFormat: "%d/%m/%Y",
				                    showsTime: false,
				                    button: "sales_report_from_trig",
				                    align: "Bl",
				                    singleClick : true
				                });
				            //]]>
				            </script>            
				    </td>
    			</tr>

				<tr>
        			<td class="label"><label for="sales_report_to">Para <span class="required">*</span></label></td>
    				<td class="value">
        				<input name="to" id="sales_report_to" value="" title="Para" type="text" class=" required-entry input-text" style="width:110px !important;" /> <img src="' . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . 'adminhtml/default/default/images/grid-cal.gif" alt="" class="v-middle" id="sales_report_to_trig" title="Select Date" style="" />
				            <script type="text/javascript">
				            //<![CDATA[
				                Calendar.setup({
				                    inputField: "sales_report_to",
				                    ifFormat: "%d/%m/%Y",
				                    showsTime: false,
				                    button: "sales_report_to_trig",
				                    align: "Bl",
				                    singleClick : true
				                });
				            //]]>
				            </script>            
				    </td>
    			</tr>
            </tbody>
       		</table>
       		<input type="submit" value="Gerar CSV" />
       		</form>
		';
    }


}
