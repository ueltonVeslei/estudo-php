<?php

class Intelipost_Basic_Model_Class_Intelipost_Write_Config
// extends Varien_Object
{

    protected $section;
    protected $group;
    protected $xmlConfig;
    protected $pathConfig;

    public function isIntelipostConfig($_section, $_group)
    {
        $this->section = $_section;
        $this->group = $_group;

        if ($this->section == 'carriers' && $this->group != 'intelipost') {
            return false;
        }

        if (!$this->xmlConfig) {
            $this->loadConfig();

        }

        return true;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function loadConfig()
    {
        $this->pathConfig = Mage::getBaseDir('code') . $this->getModulePath();
        $this->xmlConfig = simplexml_load_file($this->pathConfig);

        return $this;
    }

    public function saveXml()
    {
        $this->xmlConfig->asXML($this->pathConfig);
    }
    public function getModulePath()
    {
        $path = '/community/Intelipost/' . $this->getModuleName() . '/etc/config.xml';
        
        return $path;
    }

    public function getModuleName()
    {
        foreach ($this->sectionToModuleName() as $moduleName) 
        {
            if ($this->section == $moduleName['label']) {
                return $moduleName['value'];
            }
        }

        return $this;
    }

    public function sectionToModuleName()
    {
        return array(
                        array(  'label' => 'intelipost_basic', 'value' => 'Basic' ),
                        array(  'label' => 'carriers', 'value' => 'Quote'),
                        array(  'label' => 'intelipost_export', 'value' => 'Export'),
                        array(  'label' => 'intelipost_autocomplete', 'value' => 'Autocomplete'),
                        array(  'label' => 'intelipost_labels', 'value' => 'Labels'),
                        array(  'label' => 'intelipost_tracking', 'value' => 'Tracking')
                    );
    }

    public function getXmlConfig()
    {
        return $this->xmlConfig;
    }
}

