<?php
/**
 * Entrega via EntregaExpressa
 *
 * @category   EntregaExpressa
 * @package    EntregaExpressa_Shipping
 * @author     Igor Pfeilsticker <igorsop@gmail.com>
 */

class EntregaExpressa_Shipping_Model_FreeMethods
{

    public function toOptionArray()
    {
        return array(
           array('value'=>"Entrega Propria", 'label'=>"Motoboy - 1 a 2 dias úteis"),
        );
    }

}
