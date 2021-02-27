<?php
/**
 * path: app/code/local/Onestic/Autoversion/Core/Model/Design
 * File: Package.php
 * Date: 2018/07/12
 * Time: 17:16
 * Author: Felipe Benincasa (f.macedo@onestic.com)
 */

class Onestic_Autoversion_Core_Model_Design_Package extends Mage_Core_Model_Design_Package
{
    /**
     * Obter o timestamp do arquivo mais recente
     *
     * @param array $files
     * @return int $timeStamp
     */
    protected function getNewestFileTimestamp($srcFiles) {
        $timeStamp = null;
        foreach ($srcFiles as $file) {
            if(is_null($timeStamp)) {
                $timeStamp = filemtime($file);
            } else {
                $timeStamp = max($timeStamp, filemtime($file));
            }
        }
        return $timeStamp;
    }


    /**
     * Faz o merge do arquivo CSS e retorna a URL do mesmo
     *
     * @param $files
     * @return string
     */
    public function getMergedCssUrl($files)
    {
        $isSecure = Mage::app()->getRequest()->isSecure();
        $mergerDir = $isSecure ? 'css_secure' : 'css';
        $targetDir = $this->_initMergerDir($mergerDir);
        if (!$targetDir) {
            return '';
        }

        $baseMediaUrl = Mage::getBaseUrl('media', $isSecure);
        $hostname = parse_url($baseMediaUrl, PHP_URL_HOST);
        $port = parse_url($baseMediaUrl, PHP_URL_PORT);
        if (false === $port) {
            $port = $isSecure ? 443 : 80;
        }
        $filesTimeStamp = $this->getNewestFileTimestamp($files);

        $targetFilename = md5(implode(',', $files) . "|{$hostname}|{$port}") . "." . date("YmdHi",$filesTimeStamp) . '.css';

        $mergeFilesResult = $this->_mergeFiles(
            $files, $targetDir . DS . $targetFilename,
            false,
            array($this, 'beforeMergeCss'),
            'css'
        );
        if ($mergeFilesResult) {
            return $baseMediaUrl . $mergerDir . '/' . $targetFilename;
        }
        return '';


    }

    public function getMergedJsUrl($files)
    {

        $filesTimeStamp = $this->getNewestFileTimestamp($files);

        $targetFilename = md5(implode(',', $files)) .'.'.date("YmdHi",$filesTimeStamp). '.js';

        $targetDir = $this->_initMergerDir('js');
        if (!$targetDir) {
            return '';
        }
        if ($this->_mergeFiles($files, $targetDir . DS . $targetFilename, false, null, 'js')) {
            return Mage::getBaseUrl('media', Mage::app()->getRequest()->isSecure()) . 'js/' . $targetFilename;
        }
        return '';
    }
}