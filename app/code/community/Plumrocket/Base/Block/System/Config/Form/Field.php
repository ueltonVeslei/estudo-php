<?php

/*

Plumrocket Inc.

NOTICE OF LICENSE

This source file is subject to the End-user License Agreement
that is available through the world-wide-web at this URL:
http://wiki.plumrocket.net/wiki/EULA
If you are unable to obtain it through the world-wide-web, please
send an email to support@plumrocket.com so we can send you a copy immediately.

@package    Plumrocket_Base-v1.x.x
@copyright  Copyright (c) 2018 Plumrocket Inc. (http://www.plumrocket.com)
@license    http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement

*/


class Plumrocket_Base_Block_System_Config_Form_Field extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $product = Mage::getModel('plumbase/product')->loadByPref(str_replace('_general_serial', '',  $element->getHtmlId()));
        if ($product->isInStock()) {
            $ise = Mage::getConfig()->getModuleConfig('Ent'.'er'.'prise_Checkout') && Mage::getConfig()->getModuleConfig('Ent'.'er'.'prise_Checkout');
            $oldDesign = (version_compare('1.7.0', Mage::getVersion()) >= 0 && !$ise) || (version_compare('1.12.2', Mage::getVersion()) >= 0 && $ise);

            $src = 'images/success_msg_icon.gif';
            $title = implode('', array_map('ch'.'r', explode('.','84.104.97.110.107.32.121.111.117.33.32.89.111.117.114.32.115.101.114.105.97.108.32.107.101.121.32.105.115.32.97.99.99.101.112.116.101.100.46.32.89.111.117.32.99.97.110.32.115.116.97.114.116.32.117.115.105.110.103.32.101.120.116.101.110.115.105.111.110.46')));
            $html = '<div class="field-tooltip" style="background: url('.$this->getSkinUrl($src).') no-repeat 0 0; display: inline-block;width: 15px;height: 15px;position: relative;z-index: 1;vertical-align: middle;"><div '.( $oldDesign ? 'style="display:none;"' : '' ).'>'.$title.'</div></div>';
        } else {
            $html = '<img src="'.$this->getSkinUrl('images/error_msg_icon.gif').'" style="margin-top: 2px;float: right;" />';
        }

        return '<div style="width:300px">'.$element->getElementHtml() . $html . '</div>';
    }

    /**
     * Enter description here...
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $fields = array(
            'plumbase_order_id' => Mage::helper('plumbase')->__('Marketplace Order ID'),
            'plumbase_account_email' => Mage::helper('plumbase')->__('Marketplace Account Email'),
        );

        $moduleHandle = str_replace('_general_serial', '',  $element->getHtmlId());
        $hideMarketplaceFields = !$this->isMarketplace($moduleHandle) ? 'style="display:none;"' : '';
        $marketplaceFields = '';
        $style = '';
        $js = '';

        if ($this->isMarketplace($moduleHandle)) {
          $commentText = Mage::helper('plumbase')->__('You can find Marketplace Order ID and Email in your Magento Marketplace Account. If you have any questions, please contact us at <a href="mailto:support@plumrocket.com">support@plumrocket.com</a>');

          $element->setComment('You can find Serial Key in your Plumrocket Store account. If you have any questions, please contact us at <a href="mailto:support@plumrocket.com">support@plumrocket.com</a>.');

          $versionMessage = ' was developed by Plumrocket Inc. If you have any questions, please contact us at <a href=\"mailto:support@plumrocket.com\">support@plumrocket.com</a>.';
          $sectionHtmlId = str_replace('_serial', '', $element->getHtmlId());
          $style = '<style type="text/css">#' . $sectionHtmlId . ' > legend + div { display: none; }</style>';
          $js = '<script type="text/javascript">document.observe("dom:loaded", function() { var $e = $("' . $sectionHtmlId . '").select(" > legend + div").first(); $e.update($e.innerText.split(" was developed by Plumrocket Inc")[0] + "' . $versionMessage . '").setStyle({"display": "block"}); });</script>';
        } else {
          $commentText = Mage::helper('plumbase')->__('You can find Marketplace Order ID and Email in your Magento Marketplace Account. For manual <a target="_blank" href="%s">click here</a>.', 'http://wiki.plumrocket.com/License_Installation_For_Magento_1_Marketplace_Customers');
        }

        foreach ($fields as $key => $value) {
            $comment = ($key == 'plumbase_account_email') ? '<p class="note">
                <span>' . $commentText . '</span></p>' : '';

            $marketplaceFields .= '
                <tr ' . $hideMarketplaceFields . ' id="row_'. $key . '">
                    <td class="label">
                        <label for="' . $key . '">' . $value . '</label>
                    </td>
                    <td class="value">
                         <input id="' . $key . '" class="input-text" type="text"/>
                         ' . $comment . '
                    </td>
                </tr>
            ';
        }

        $marketplaceFields .= '
            <tr ' . $hideMarketplaceFields . '>
                 <td class="label"></td>
                 <td class="value">
                      <button id="plumbase_activate_extension" title="' . Mage::helper('plumbase')->__('Activate Extension') . '" type="button" class="scalable" onclick="false;" style="">
                           <span>
                               <span>
                                    <span>' . Mage::helper('plumbase')->__('Activate Extension') . '</span>
                               </span>
                           </span>
                      </button>
                 </td>
            </tr>
        ';

        $serialKeyHtml = parent::render($element);
        $value = (string)$element->getValue();

        if ($this->isMarketplace($moduleHandle) && empty($value)) {
            $class = get_class($this->moduleData($moduleHandle));
            $modName = str_replace('Plumrocket_', '', substr($class, 0, strpos($class, '_Helper')));
            $serialKeyHtml = str_replace("<tr", "<tr style='display:none;'", $serialKeyHtml)
                . $marketplaceFields . $this->_js($element->getHtmlId(), $modName);
        }

        return $serialKeyHtml . $style . $js;
    }

    public function isMarketplace($handle)
    {
        $modHelper = $this->moduleData($handle);
        $dataOriginMethod = $this->dataOrigin();
        $cKey = $modHelper->{$dataOriginMethod}();

        if (method_exists($modHelper, 'isMarketplace')) {
            return $modHelper->isMarketplace($cKey);
        }

        return false;
    }

    private function dataOrigin()
    {
        return strrev('yeK'.'remo'.'tsuC'.'teg');
    }

    private function _js($serialKeyId, $modName)
    {
        return '
            <script>
                var orderId = $("plumbase_order_id"),
                accountEmail = $("plumbase_account_email"),
                url = "' . Mage::helper("adminhtml")->getUrl(strrev('xed'.'ni/es'.'abmul'.'p/lmt'.'hni'.'mda')) . '",
                button = $("plumbase_activate_extension"),
                serialKey = $("' . $serialKeyId . '"),
                messageBlock = $("messages"),
                plumbaseMessageBlockEl;

                button.addEventListener("click", function() {
                    new Ajax.Request(url, {
                        method: "post",
                        parameters: {"order_id":orderId.getValue(),"account_email":accountEmail.getValue(),"module":"' . $modName . '"},
                        onCreate: function() {
                            button.addClassName("loading");
                            Ajax.Responders.register(varienLoaderHandler.handler);
                        },
                        onComplete: function() {
                            button.removeClassName("loading");
                        },
                        onSuccess: function(response){
                            var json = response.responseText.evalJSON();
                            if (typeof json.data != "undefined") {
                                serialKey.setValue(json.data);
                            }
                            if (typeof json.error != "undefined") {
                                var plbMessage = "<ul id=\'plumbaseMessageBlock\' class=\'messages\'><li class=\'error-msg\'><ul><li>"
                                    + json.error
                                    + "</li></ul></li></ul>";

                                plumbaseMessageBlockEl = $("plumbaseMessageBlock");
                                if (plumbaseMessageBlockEl) {
                                    plumbaseMessageBlockEl.innerHtml(plbMessage);
                                } else if (messageBlock) {
                                    messageBlock.insert(plbMessage);
                                }
                            } else {
                                plumbaseMessageBlockEl = $("plumbaseMessageBlock");
                                if (plumbaseMessageBlockEl) {
                                    plumbaseMessageBlockEl.hide();
                                }
                            }
                            if (json.hash) {
                                serialKey.up("tr").show();
                                button.up("tr").hide();
                                orderId.up("tr").hide();
                                accountEmail.up("tr").hide();
                            }
                        },
                        onFailure:  function () {
                            alert(Translator.translate("Something went wrong."));
                        }
                    });
                });
            </script>
        ';
    }

    private function moduleData($helper)
    {
        return Mage::helper($helper . '/main');
    }
}