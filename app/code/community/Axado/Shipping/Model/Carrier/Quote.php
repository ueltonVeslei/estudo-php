<?php

class Axado_Shipping_Model_Carrier_Quote
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = 'axado';
    protected $_result = null;
    protected $_method = null;

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $this->_result = Mage::getModel('shipping/rate_result');

        $this->_cep_origem = Mage::getStoreConfig('shipping/origin/postcode', $this->getStore());
        $this->_cep_destino = $request->getDestPostcode();

        // Fix ZIP code
        $this->_cep_origem  = preg_replace('/[^0-9]/', '', $this->_cep_origem);
        $this->_cep_destino = preg_replace('/[^0-9]/', '', $this->_cep_destino);

        $this->_token = $this->getConfigData('token');
        $this->_prazo_adicional = $this->getConfigData('prazo_adicional');
        $this->_preco_adicional = $this->getConfigData('preco_adicional');

        $this->_getQuote($request);

        return $this->_result;
    }

    protected function _getQuote($_request)
    {
        $parameters = array(
            'cep_origem' => $this->_cep_origem,
            'cep_destino' => $this->_cep_destino,
            'prazo_adicional' => $this->_prazo_adicional,
            'preco_adicional' => $this->_preco_adicional,
            'valor_notafiscal' => number_format($_request->getPackageValue(), 2, ',', ''),
            'volumes' => array(),
        );

        foreach($_request->getAllItems() as $item) {
            $_product = $item->getProduct();
            if ($item->getParentItem()) {
                continue;
            } 

            if ($this->getConfigData('formato_peso') == Axado_Shipping_Model_Source_WeightType::WEIGHT_GR) {
                $this->peso = number_format($item->getWeight()/1000, 2, '.', '');
            } else {
                $this->peso = number_format($item->getWeight(), 2, '.', '');
            }

            $volume = array();
            $volume["quantidade"] = $item->getQty();
            $volume["sku"] = $_product->getData('sku');
            $volume["preco"] = number_format($_product->getData('price'), 2, ',', '');
            $volume["peso"] = $this->peso;

            if (!$this->isDimensionSet($_product)) {
                Mage::log('Product ' . $_product->getData('sku') . ' does not have dimensions set', null, 'axado.log');
                
                if ($this->getConfigData('notificar_dimensao')) {
                        $notification = Mage::getSingleton('adminnotification/inbox');
                        $notification->add(
                            Mage_AdminNotification_Model_Inbox::SEVERITY_MINOR,
                            'Product missing dimensions',
                            $_product->getData('sku')
                        );
                }                

            } else {
                $volume["comprimento"] = $_product->getData('volume_comprimento');
                $volume["largura"] = $_product->getData('volume_largura');
                $volume["altura"] = $_product->getData('volume_altura');
            }

            array_push($parameters["volumes"], $volume);
        }

        $client = new Zend_Http_Client("http://api.axado.com.br/v2/consulta/?token=$this->_token");
        $client->setRawData(json_encode($parameters), 'text');
        $httpResponse = $client->request('POST');
        $response = json_decode($httpResponse->getBody(), true);

        if (!isset($response['cotacoes'])) {
            Mage::log('Server did not return quote', null, 'axado.log');
            Mage::log($parameters, null, 'axado.log');
            Mage::log($response, null, 'axado.log');

            return false;
        }

        foreach ($response['cotacoes'] as $quote) {
            $method = Mage::getModel('shipping/rate_result_method');
            $method->setCarrier             ('axado');
            $method->setCarrierTitle        ($this->getConfigData('title'));
            $method->setMethod              ($quote['servico_metaname']);
            $method->setMethodTitle         (sprintf("%s %s", $quote['servico_nome'], $this->formatDeadline($quote['cotacao_prazo'])));
            $method->setPrice               (str_replace(',','.', str_replace('.','',$quote['cotacao_preco'])));
            $method->setCost                (str_replace(',','.', str_replace('.','',$quote['cotacao_custo'])));
            $method->setMethodDescription   ();
            $method->setData                ('token', "$response[consulta_token]-$quote[cotacao_codigo]");

            $this->_result->append($method);
        }
    }

    public function getAllowedMethods()
    {
        return array($this->_code => $this->getConfigData('name'));
    }

    private function isDimensionSet($product)
    {
        $volume_altura = $product->getData('volume_altura');
        $volume_largura = $product->getData('volume_largura');
        $volume_comprimento = $product->getData('volume_comprimento');

        if ($volume_comprimento == '' || (int)$volume_comprimento == 0 || $volume_largura == '' || (int)$volume_largura == 0 || $volume_altura == '' || (int)$volume_altura == 0) {
            return false;
        }
        return true;
    }

    private function formatDeadline($days)
    {
        if ($days == 0)
            return ('(mesmo dia)');

        if ($days == 1)
            return ('(1 dia)');

        if ($days == 101)
            return ('(prazo desconhecido)');

        return sprintf(('(%s dias)'), $days);
    }
}
