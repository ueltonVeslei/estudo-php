<?php


/**
 * Sales Order Invoice Pdf grouped items renderer
 *
 * @category   Nastnet
 * @package    Nastnet_OrderPrint
 */
class Nastnet_OrderPrint_Model_Order_Pdf_Items_Invoice_Grouped extends Nastnet_OrderPrint_Model_Order_Pdf_Items_Invoice_Default
{
    public function draw()
    {
        $type = $this->getItem()->getOrderItem()->getRealProductType();
        $renderer = $this->getRenderedModel()->getRenderer($type);
        $renderer->setOrder($this->getOrder());
        $renderer->setItem($this->getItem());
        $renderer->setPdf($this->getPdf());
        $renderer->setPage($this->getPage());

        $renderer->draw();
    }
}