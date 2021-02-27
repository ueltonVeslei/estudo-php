<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Advancedsearch
 * @version    1.2.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */

class AW_Advancedsearch_Model_Engine_Sphinx
{
    const SEARCHD_CALL = 'searchd';
    const INDEXER_CALL = 'indexer';

    protected $_instance = null;

    public function getInstance($indexer = null)
    {
        if ($indexer) {
            switch (get_class($indexer)) {
                case 'AW_Advancedsearch_Model_Indexer_Catalog':
                    $instance = Mage::getModel('awadvancedsearch/engine_sphinx_catalog');
                    break;
                case 'AW_Advancedsearch_Model_Indexer_Cms_Pages':
                    $instance = Mage::getModel('awadvancedsearch/engine_sphinx_cms_pages');
                    break;
                case 'AW_Advancedsearch_Model_Indexer_Awblog':
                    $instance = Mage::getModel('awadvancedsearch/engine_sphinx_awblog');
                    break;
                case 'AW_Advancedsearch_Model_Indexer_Awkbase':
                    $instance = Mage::getModel('awadvancedsearch/engine_sphinx_awkbase');
                    break;
            }
            if (isset($instance) && is_object($instance)) {
                $instance->setIndexer($indexer);
                return $instance;
            }
        }
    }

    public function getSphinxInstance($indexer = null)
    {
        if ($this->_instance === null && $indexer) {
            $this->_instance = $this->getInstance($indexer);
        }
        return $this->_instance;
    }

    public function reindex($indexer)
    {
        if ($this->getSphinxInstance($indexer)) {
            return $this->getSphinxInstance()->reindex();
        }
        return false;
    }

    public function reindexDelta($indexer)
    {
        if ($this->getSphinxInstance($indexer)) {
            return $this->getSphinxInstance()->reindexDelta();
        }
        return false;
    }

    public function mergeDeltaWithMain($indexer)
    {
        if ($this->getSphinxInstance($indexer)) {
            return $this->getSphinxInstance()->mergeDeltaWithMain();
        }
        return false;
    }

    public function connect()
    {
        try {
            include_once BP . DS . 'lib' . DS . 'Sphinx' . DS . 'sphinxapi.php';
            $sphinx = new SphinxClient();
            $config = Mage::helper('awadvancedsearch/config')->getSphinxConfig();
            $sphinx->setServer($config['addr'], (int)$config['port']);
            return $sphinx;
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
        return false;
    }

    public function getConfigFileName()
    {
        return $this->_getHelper()->getVarDir() . DS . 'searchd.conf';
    }

    protected function _getHelper($name = null)
    {
        return Mage::helper('awadvancedsearch' . ($name ? '/' . $name : ''));
    }

    protected function _getLogHelper()
    {
        return $this->_getHelper('log');
    }

    protected function _getActiveIndexes()
    {
        $indexes = Mage::getModel('awadvancedsearch/catalogindexes')->getCollection();
        $indexes->addStatusFilter();
        return $indexes;
    }

    public function createConfigFile()
    {
        $configFileName = $this->getConfigFileName();
        @unlink($configFileName);
        $indexes = $this->_getActiveIndexes();
        $connectionConfig = Mage::getSingleton('core/resource')->getConnection('core_write')->getConfig();
        $sphinxConfig = Mage::getStoreConfig('awadvancedsearch/sphinx');
        $_files = array(
            'pid_file' => $this->_getHelper()->getVarDir() . DS . 'searchd.pid',
            'log' => $this->_getHelper()->getVarDir() . DS . 'searchd.log'
        );
        $fcontent = <<<FILE
source dbconnect
{
    type = mysql
    sql_host = {$connectionConfig['host']}
    sql_user = {$connectionConfig['username']}
    sql_pass = {$connectionConfig['password']}
    sql_db = {$connectionConfig['dbname']}
}

FILE;
        foreach ($indexes as $index) {
            $sphinxIndexer = $this->getInstance($index->getIndexer());
            $fcontent .= $sphinxIndexer->getConfigFileContent();
        }
        $fcontent .= <<<FILE
indexer
{
    mem_limit = 32M
}
searchd
{
    address = {$sphinxConfig['server_addr']}
    port = {$sphinxConfig['server_port']}
    read_timeout = 5
    max_children = 30
    pid_file = {$_files['pid_file']}
    log = {$_files['log']}
    max_matches = 1000
}
FILE;
        if (@file_put_contents($configFileName, $fcontent)) {
            $this->_getLogHelper()->log($this, 'Sphinx: Common config file created');
            return true;
        }
        $this->_getLogHelper()->log($this, 'Sphinx: Error creating common config file');
        return false;
    }

    public function checkSearchdState()
    {
        $config = Mage::helper('awadvancedsearch/config')->getSphinxConfig();
        if (isset($config['addr']) && $config['addr'] && isset($config['port']) && $config['addr']) {
            try {
                $fp = @fsockopen($config['addr'], $config['port'], $errno, $errstr, 5);
                if ($fp) {
                    fclose($fp);
                    return true;
                }
            } catch (Exception $ex) {
            }
            return false;
        }
        return null;
    }

    public function startSearchd()
    {
        if ($this->checkSearchdState() === false) {
            $path = Mage::helper('awadvancedsearch/config')->getSphinxServerPath();
            ob_start();
            passthru($path . self::SEARCHD_CALL . ' -c ' . $this->getConfigFileName(), $_ret);
            $_out = ob_get_contents();
            ob_end_clean();
            if ($_ret === 0) {
                $this->_getLogHelper()->log($this, 'Sphinx', null, 'Daemon for ' . $this->getConfigFileName() . ' has been started');
            } else {
                $this->_getLogHelper()->log($this, 'Sphinx error', null, $_out);
            }
            return $_ret === 0;
        }
        return null;
    }

    public function stopSearchd()
    {
        if ($this->checkSearchdState() === true) {
            $path = Mage::helper('awadvancedsearch/config')->getSphinxServerPath();
            ob_start();
            passthru($path . self::SEARCHD_CALL . ' -c ' . $this->getConfigFileName() . ' --stop', $_ret);
            $_out = ob_get_contents();
            ob_end_clean();
            if ($_ret === 0) {
                $this->_getLogHelper()->log($this, 'Sphinx', null, 'Daemon for ' . $this->getConfigFileName() . ' has been stopped');
            } else {
                $this->_getLogHelper()->log($this, 'Sphinx error', null, $_out);
            }
            return $_ret === 0;
        }
        return null;
    }

    public function restartSearchd()
    {
        if ($this->checkSearchdState() === true) {
            $this->stopSearchd();
            $this->startSearchd();
        }
    }
}
