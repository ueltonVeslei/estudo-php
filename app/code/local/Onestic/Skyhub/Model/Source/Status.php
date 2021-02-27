<?php
class Onestic_Skyhub_Model_Source_Status {
    
    public function toOptionArray()
    {
        return array(
            array('value'=> 'Pagamento Pendente', 'label'=>'Pagamento Pendente'),
        	array('value'=> 'aprovado', 'label'=>'Aprovado'),
        	array('value'=> 'pedido enviado', 'label'=>'Pedido Enviado'),
        	array('value'=> 'completo (entregue)', 'label'=>'Completo (Entregue)'),
            array('value'=> 'Cancelado', 'label'=>'Cancelado'),
        );
    }
    
    public function toColumnOptionArray()
    {
    	return array(
    	    'Pagamento Pendente' => 'Pagamento Pendente',
        	'aprovado' => 'Aprovado',
    		'pedido enviado' => 'Pedido Enviado',
        	'completo (entregue)' => 'Completo (Entregue)',
            'Cancelado' => 'Cancelado',
    	);
    } 
    
}

