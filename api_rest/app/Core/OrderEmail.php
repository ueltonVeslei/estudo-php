<?php
abstract class OrderEmail {

  const XML_PATH_EMAIL_COPY_TO                = 'sales_email/order/copy_to';
  const XML_PATH_EMAIL_COPY_METHOD            = 'sales_email/order/copy_method';
  const XML_PATH_EMAIL_IDENTITY               = 'sales_email/order/identity';
  const ENTITY                                = 'order';
  const EMAIL_EVENT_NAME_NEW_ORDER    = 'new_order';
  const XML_PATH_EMAIL_TEMPLATE               = 'sales_email/order/template';

  protected function _getEmails($order, $configPath)
  {
      $data = Mage::getStoreConfig($configPath, $order->getStoreId());
      if (!empty($data)) {
          return explode(',', $data);
      }
      return false;
  }

	public static function send ($order) {
    $storeId = $order->getStore()->getId();

    if (!Mage::helper('sales')->canSendNewOrderEmail($storeId)) {
        return $order;
    }

    // Get the destination email addresses to send copies to
    $copyTo = self::_getEmails($order, self::XML_PATH_EMAIL_COPY_TO);
    $copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $storeId);

    // Start store emulation process
    /** @var $appEmulation Mage_Core_Model_App_Emulation */
    $appEmulation = Mage::getSingleton('core/app_emulation');
    $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

    try {
        // Retrieve specified view block from appropriate design package (depends on emulated store)
        $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
            ->setIsSecureMode(true);
        $paymentBlock->getMethod()->setStore($storeId);
        $paymentBlockHtml = $paymentBlock->toHtml();
    } catch (Exception $exception) {
        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        throw $exception;
    }

    // Stop store emulation process
    $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

    // Retrieve corresponding email template id and customer name
    $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE, $storeId);
    $customerName = $order->getCustomerName();

    /** @var $mailer Mage_Core_Model_Email_Template_Mailer */
    $mailer = Mage::getModel('core/email_template_mailer');
    /** @var $emailInfo Mage_Core_Model_Email_Info */
    $emailInfo = Mage::getModel('core/email_info');
    $emailInfo->addTo($order->getCustomerEmail(), $customerName);

    if ($copyTo && $copyMethod == 'bcc') {
        // Add bcc to customer email
        foreach ($copyTo as $email) {
            $emailInfo->addBcc($email);
        }
    }
    $mailer->addEmailInfo($emailInfo);

    // Email copies are sent as separated emails if their copy method is 'copy'
    if ($copyTo && $copyMethod == 'copy') {
        foreach ($copyTo as $email) {
            $emailInfo = Mage::getModel('core/email_info');
            $emailInfo->addTo($email);
            $mailer->addEmailInfo($emailInfo);
        }
    }

    // Set all required params and send emails
    $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId));
    $mailer->setStoreId($storeId);
    $mailer->setTemplateId($templateId);
    $mailer->setTemplateParams(array(
        'order'        => $order,
        'billing'      => $order->getBillingAddress(),
        'payment_html' => $paymentBlockHtml
    ));

    /** @var $emailQueue Mage_Core_Model_Email_Queue */
    $emailQueue = Mage::getModel('core/email_queue');
    $emailQueue->setEntityId($order->getId())
        ->setEntityType(self::ENTITY)
        ->setEventType(self::EMAIL_EVENT_NAME_NEW_ORDER)
        ->setIsForceCheck(!$forceMode);

    $mailer->setQueue($emailQueue)->send();

    $order->setEmailSent(true);
    $order->getResource()->saveAttribute($order, 'email_sent');

    return $order;
  }
}

