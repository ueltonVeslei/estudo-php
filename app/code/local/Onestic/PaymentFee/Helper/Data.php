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
 * Class Onestic_PaymentFee_Helper_Data
 */
class Onestic_PaymentFee_Helper_Data extends Mage_Core_Helper_Abstract {
    /**
     *  Caminho para a configuração da loja
     */
    const XML_PATH_SYSTEM_CONFIG = "payment_fee/payment_fee/";
    /**
     * @var array
     */
    public $fee = NULL;

    /**
     * Checar se o módulo está ativo
     * @return bool
     */
    public function isEnabled() {
        return $this->getConfig('enabled');
    }

    /**
     * Recuperar configurações da loja
     * @param string $field
     * @return mixed|null
     */
    public function getConfig($field = '') {
        if ($field) {
            return Mage::getStoreConfig(self::XML_PATH_SYSTEM_CONFIG . $field, $this->getCurrentStoreId());
        }

        return NULL;
    }

    /**
     * Recuperar tipo de calculo da taxa (percentual ou fixa)
     * @return string
     */
    public function getFeeType() {
        return $this->getConfig('fee_type');
    }

    /**
     * Recuperar de forma não serealizada os métodos de pagamento e suas taxas a partir da configuração da loja
     * @return array
     */
    public function getFee() {
        if (is_null($this->fee)) {
            $fees = (array)unserialize($this->getConfig('fee'));
            foreach ($fees as $fee) {
                $this->fee[$fee['payment_method']] = array(
                    'fee'         => $fee['fee'],
                    'description' => $fee['description']
                );
            }
        }

        return $this->fee;
    }

    /**
     * Obter o ID da loja atual
     * @return int
     */
    public function getCurrentStoreId()
    {
        $storeId = Mage::getSingleton('adminhtml/session_quote')->getStoreId();
        if ($storeId){ // Se o pedido foi criado a partir do admin da loja
            return $storeId;
        }else{ // se o pedido foi criado a partir do frontend da loja
            return Mage::app()->getStore();
        }
    }
}
