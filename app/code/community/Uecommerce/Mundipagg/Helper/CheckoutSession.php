<?php

/**
 * Helper methods to checkout session
 */
class Uecommerce_Mundipagg_Helper_CheckoutSession extends Mage_Core_Helper_Abstract
{
    static protected $log;

    public function getInstance()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get approval_request_success flag from checkout session
     * @return null|string
     */
    public function getApprovalRequest()
    {
        return $this->getInstance()->getApprovalRequestSuccess();
    }

    /**
     * Set approval_request_success flag into checkout session
     * @param string $flag
     */
    public function setApprovalRequest($flag, $order = null)
    {
        $this->logChange($flag, $order);
        $this->getInstance()->setApprovalRequestSuccess($flag);
    }

    public function setApprovalRequestSuccess($flag, $order = null)
    {
        $this->setApprovalRequest($flag, $order);
    }

    protected function getLogger()
    {
        if (self::$log === null) {
            self::$log = new Uecommerce_Mundipagg_Helper_Log(__METHOD__);
        }

        return self::$log;
    }

    protected function logChange($newFlag, $order = null)
    {
        $log = $this->getLogger();
        $currentFlag = $this->getInstance()->getApprovalRequestSuccess();

        $log->setLogLabel('');
        if ($order !== null) {
            $log->setLogLabel('|' . $order->getIncrementId() . '|');
        }

        $stackTrace = debug_backtrace();

        $level = 0;
        while ($stackTrace[$level]['file'] === __FILE__ ) {
            $level++;
        }
        $file = $stackTrace[$level]['file'];
        $line = $stackTrace[$level]['line'];

        $msg = "Changing approvalRequestSuccess from '$currentFlag' to '$newFlag'. From $file:$line";
        $log->info($msg);
    }
}
