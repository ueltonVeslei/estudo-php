<?php

class Intelipost_Basic_Block_About
extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{

protected $_name = 'Basic';
protected $_handle = 'Intelipost_Basic';

public function render (Varien_Data_Form_Element_Abstract $element)
{

$logo = $this->getSkinUrl ('images/intelipost/basic/logo.png');
$version = Mage::getConfig ()->getModuleConfig ($this->_handle)->version;

$htmlBlock = <<< HTMLBLOCK
<div style="padding:20px; padding-left:0;">
<img src="{$logo}" alt="" style="display:block" />
<span style="font-size:25px; font-weight:bold;">{$this->__('%s module - version %s', $this->_name, $version)}</span>
</div>
HTMLBLOCK;

if ($this->_name == 'Basic') 
{
$htmlBlock .= <<< HTMLBLOCK
<div style="margin-top: 15px">
<p>O módulo "Basic" funciona como base para todos os outros módulos da Intelipost que providenciam as seguintes funcionalidades da plataforma Intelipost:</p>
<ul style="list-style-type:disc; list-style-position: inside">
<li>Cotação/Leilão/Calculo de frete</li>
<li>Rastreamento de cargas</li>
<li>Autocomplete</li>
<li>Etiquetas/PLP/Romaneio/Manifesto</li>
</ul><br />
<p>Para pedir os outros módulos fale conosco pelo tel: (11) 4872-8009 ou info@intelipost.com.br!</p>
</div>
HTMLBLOCK;
}

if ($this->_name == 'Tracking')
{
$protocol = strtoupper(stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https' : 'http');
$hostname = $_SERVER['HTTP_HOST'];
$htmlBlock .= <<< HTMLBLOCK
<div style="margin-top: 15px">
<p>Webhook Configuração</p>
<p><a href="https://secure.intelipost.com.br/tracking-config/webhook" target="_blank">https://secure.intelipost.com.br/tracking-config/webhook</a></p>
<ul style="list-style-type:disc; list-style-position: inside">
<li><b>Protocolo</b>: $protocol</li>
<li><b>Host</b>: $hostname</li>
<li><b>Path</b>: /tracking/webhook</li>
<li><b>Porta</b>: 80</li>
</ul><br />
</div>
HTMLBLOCK;
}
return $htmlBlock;

}

}

