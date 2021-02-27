<?php
class Av5_Correiospro_Block_Adminhtml_Promotion_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$model = Mage::registry('correiospromo_data');

		$form = new Varien_Data_Form(array(
				'id' => 'edit_form',
				'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
				'method' => 'post',
				'enctype' => 'multipart/form-data',
		));

		$fieldset = $form->addFieldset('correiospromo_form', array(
				'legend' =>Mage::helper('av5_correiospro')->__('Dados da Regra')
		));
		
		$fieldset->addField('nome', 'text', array(
				'label'     => Mage::helper('av5_correiospro')->__('Nome da Regra'),
				'class'     => 'required-entry',
				'required'  => true,
				'name'      => 'nome',
				'note'     	=> Mage::helper('av5_correiospro')->__('Informe o nome de sua regra.'),
		));
		
		$fieldset->addField('status', 'select', array(
				'label'     => Mage::helper('av5_correiospro')->__('Ativa?'),
				'class'     => 'required-entry',
				'required'  => true,
				'name'      => 'status',
				'note'     	=> Mage::helper('av5_correiospro')->__('Habilite ou desabilite a sua regra.'),
				'values' 	=> Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
		));
		
		$fieldset->addField('prioridade', 'text', array(
				'label'     => Mage::helper('av5_correiospro')->__('Prioridade'),
				'required'  => false,
				'name'      => 'prioridade',
				'note'     	=> Mage::helper('av5_correiospro')->__('Informe a ordem de prioridade dessa regra.'),
		));
		
		$fieldset->addField('servico', 'multiselect', array(
				'label'     => Mage::helper('av5_correiospro')->__('Serviços'),
				'class'     => 'required-entry',
				'required'  => true,
				'name'      => 'servico',
				'note'     	=> Mage::helper('av5_correiospro')->__('Selecione os serviços desejados.'),
				'values' 	=> Mage::getModel('Av5_Correiospro_Model_Source_Availables')->toOptionArray()
		));

		$fieldset->addField('prazo', 'text', array(
				'label'     => Mage::helper('av5_correiospro')->__('Prazo de entrega'),
				'required'  => false,
				'name'      => 'prazo',
				'note'     	=> Mage::helper('av5_correiospro')->__('Em quantidade de dias.'),
		));
		
		$fieldset->addField('tipo_prazo', 'select', array(
				'label'     => Mage::helper('av5_correiospro')->__('Tipo de Prazo'),
				'class'     => 'required-entry',
				'required'  => true,
				'name'      => 'tipo_prazo',
				'note'     	=> Mage::helper('av5_correiospro')->__('Informe como deve ser mostrada essa alteração de prazo.'),
				'values' 	=> Mage::getModel('Av5_Correiospro_Model_Source_DeliveryType')->toOptionArray()
		));
		
		$fieldset->addField('pedido', 'text', array(
				'label'     => Mage::helper('av5_correiospro')->__('Valor mínimo de pedido'),
				'required'  => false,
				'name'      => 'pedido',
				'note'     	=> Mage::helper('av5_correiospro')->__('use "." como separador decimal.'),
		));
		
		$fieldset->addField('pedido_maximo', 'text', array(
		    'label'     => Mage::helper('av5_correiospro')->__('Valor máximo de pedido'),
		    'required'  => false,
		    'name'      => 'pedido_maximo',
		    'note'     	=> Mage::helper('av5_correiospro')->__('use "." como separador decimal.'),
		));
		
		$fieldset->addField('tipo_pedido', 'select', array(
				'label'     => Mage::helper('av5_correiospro')->__('Total considerado'),
				'class'     => 'required-entry',
				'required'  => true,
				'name'      => 'tipo_pedido',
				'note'     	=> Mage::helper('av5_correiospro')->__('Selecione o total a ser considerado pela regra.'),
				'values' 	=> Mage::getModel('Av5_Correiospro_Model_Source_TotalType')->toOptionArray()
		));
		
		$fieldset->addField('gratis', 'select', array(
				'label'     => Mage::helper('av5_correiospro')->__('Frete Grátis?'),
				'class'     => 'required-entry',
				'required'  => true,
				'name'      => 'gratis',
				'note'     	=> Mage::helper('av5_correiospro')->__('Ative ou desative o frete grátis para essa regra.'),
				'values' 	=> Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
		));
		
		$fieldset->addField('desativar_servico', 'select', array(
		    'label'     => Mage::helper('av5_correiospro')->__('Desativar Serviço?'),
		    'class'     => 'required-entry',
		    'required'  => true,
		    'name'      => 'desativar_servico',
		    'note'     	=> Mage::helper('av5_correiospro')->__('Ative ou desative o serviço para essa regra.'),
		    'values' 	=> Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray()
		));
		
		$fieldset->addField('esconde_se', 'select', array(
		    'label'     => Mage::helper('av5_correiospro')->__('Esconder'),
		    'required'  => false,
		    'name'      => 'esconde_se',
		    'note'     	=> Mage::helper('av5_correiospro')->__('Esconder se o serviço selecionado estiver disponível.'),
		    'values' 	=> Mage::getModel('Av5_Correiospro_Model_Source_PostingMethods')->toOptionArray2()
		));
		
		$fieldset->addField('tipo_desconto', 'select', array(
				'label'     => Mage::helper('av5_correiospro')->__('Mudança no valor?'),
				'required'  => false,
				'name'      => 'tipo_desconto',
				'note'     	=> Mage::helper('av5_correiospro')->__('Selecione o tipo de mudança de valor que será aplicada.'),
				'values' 	=> Mage::getModel('Av5_Correiospro_Model_Source_ValueChange')->toOptionArray()
		));
		
		$fieldset->addField('valor', 'text', array(
				'label'     => Mage::helper('av5_correiospro')->__('Valor da mudança'),
				'required'  => false,
				'name'      => 'valor',
		));
		
		// Product Filters
		$filters = $form->addFieldset('rule_conditions_fieldset', array(
				'legend' => Mage::helper('av5_correiospro')->__('Filtros da regra (deixe em branco para todas as localidades e produtos)'),
				'class'  => 'fieldset-wide'
		));
		
		$filters->addField('conditions', 'text', array(
				'name' => 'conditions',
				'label' => Mage::helper('av5_correiospro')->__('Filtros'),
				'title' => Mage::helper('av5_correiospro')->__('Filtros'),
				'required' => false,
		))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/conditions'));
		
		$renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
			->setTemplate('av5_correiospro/fieldset.phtml')
			->setNewChildUrl($this->getUrl('adminhtml/promo_catalog/newConditionHtml/form/rule_conditions_fieldset'));
		$filters->setRenderer($renderer);

		$form->setValues($model->getData());
		$form->setUseContainer(true);
		$this->setForm($form);
		

		return parent::_prepareForm();
	}
}