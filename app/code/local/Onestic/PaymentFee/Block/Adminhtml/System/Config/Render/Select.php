<?php
/**
 * Criado por Onestic
 * Baseado no módulo "Magentix" (https://github.com/magentix/Fee)
 *
 * @category   Onestic
 * @package    Onestic_PaymentFee
 * @author     Felipe Macedo (f.macedo@onestic.com)
 * @license    Módulo gratuito, pode ser redistribuido e/ou modificado
 */
/**
 * Class Onestic_PaymentFee_Block_Adminhtml_System_Config_Render_Select
 */
class Onestic_PaymentFee_Block_Adminhtml_System_Config_Render_Select extends Mage_Core_Block_Html_Select {
    public function _toHtml() {
        return trim(preg_replace('/\s+/', ' ', parent::_toHtml()));
    }
}
