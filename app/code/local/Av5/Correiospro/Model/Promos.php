<?php
/**
 * AV5 Tecnologia
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Shipping (Frete)
 * @package    Av5_Correiospro
 * @copyright  Copyright (c) 2013 Av5 Tecnologia (http://www.av5.com.br)
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Av5_Correiospro_Model_Promotion
 *
 * @category   Shipping
 * @package    Av5_Correiospro
 * @author     AV5 Tecnologia <anderson@av5.com.br>
 */

class Av5_Correiospro_Model_Promos extends Mage_Rule_Model_Rule {
	
	public function _construct()
	{
		parent::_construct();
		$this->_init('av5_correiospro/promos');
		$this->setIdFieldName('id');
	}
	
	public function getConditionsInstance()	{
		return Mage::getModel('av5_correiospro/rule_condition_combine');
	}
	
	protected function _beforeSave() {
		if (is_array($this->getServico())) {
			$this->setServico(implode(',',$this->getServico()));
		}
		parent::_beforeSave();
		return $this;
	}
	
	public function getValid($service) {
		$quote = Mage::getModel('checkout/session')->getQuote();
		$address = $quote->getShippingAddress();
		$totals = $quote->getTotals(); 
		$rules = Mage::getResourceModel('av5_correiospro/promos_collection')
			->addServiceFilter($service)
			->addStatusFilter()
			->orderPrioridade();
		
		$validRule = array(
			'prazo' 		   => 0,
			'tipo_prazo' 	   => '',
			'gratis'		   => '',
			'tipo_desconto'    => '',
			'valor'			   => '',
			'desativar_servico' => 0,
			'esconde_se'       => ''
		);
		foreach($rules as $rule) {
			if ($rule->validate($address)) {
				$totalOrder = $quote->getData($rule->getTipoPedido());
				if ( (($rule->getPedido() < $totalOrder) || ($rule->getPedido()=='0.00')) &&
                    (($rule->getPedidoMaximo() > $totalOrder) || $rule->getPedidoMaximo() == '0.00') ) {
				    // Verifica se a regra considera o valor do pedido
					$validRule['prazo'] += $rule->getPrazo();
					$validRule['tipo_prazo'] = $rule->getTipoPrazo();
					$validRule['gratis'] = $rule->getGratis();
					$validRule['valor'] = $rule->getValor();
					$validRule['tipo_desconto'] = $rule->getTipoDesconto();
					$validRule['desativar_servico'] = $rule->getDesativarServico();
					$validRule['esconde_se'] = (string)$rule->getEscondeSe();
				}
			}
		}
		return $validRule;
	}
}