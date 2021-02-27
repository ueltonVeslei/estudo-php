<?php
/*
 * Koin Module - payment method module for Magento, integrating
 * the billing forms with Koin Web Service.
 * Copyright (C) 2013  Koin
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
class Saffira_Koin_Block_Verify extends Mage_Checkout_Block_Onepage_Success {
	/**
	 * Define mensagem mostrada ao fim da compra
	 * @return string
	 */
    public function getKoinDataHtml(){
        $html = '<div class="page-title">';
        $html.= '<h1>'.$this->__('Seu pedido foi realizado com sucesso!').'</h1>';
        $html.= '</div>';
        $html.= $this->getMessagesBlock()->getGroupedHtml();
        $html.= '<h2 class="sub-title">'.$this->__('Obrigado por comprar com a Koin Pós-Pago!').'</h2><br>';
        if ($this->getOrderId()):
            if ($this->getCanViewOrder()):
                $html.= '<p>'.$this->__('O número do seu pedido é: %s.', sprintf('<a href="%s" target="_blank">%s</a>', $this->escapeHtml($this->getViewOrderUrl()), $this->escapeHtml($this->getOrderId()))).'</p>';
            else :
                $html.= '<p>'.$this->__('O número do seu pedido é: %s.', $this->escapeHtml($this->getOrderId())).'</p>';
            endif;
            $html.= '<p>'.$this->__('Em breve você receberá um e-mail com os detalhes do pedido e um link para acompanhamento.').'</p>';
            //if ($this->getCanViewOrder() && $this->getCanPrintOrder()) :
            //    $html.= '<p>';
            //    $html.= $this->__('<a href="%s" onclick="this.target=\'_blank\'">Clique aqui</a> caso queira imprimir uma cópia de seu pedido.', $this->getPrintUrl());
            //    $html.= $this->getChildHtml();
            //    $html.= '</p>';
            //endif;
        endif;

        if ($this->getAgreementRefId()):
            $html.= '<p>'.$this->__('Your billing agreement # is: %s.', sprintf('<a href="%s">%s</a>', $this->escapeHtml($this->getAgreementUrl()), $this->escapeHtml($this->getAgreementRefId()))).'</p>';
        endif;

        //$html.= '<br />';
       
        return $html;
    }
}