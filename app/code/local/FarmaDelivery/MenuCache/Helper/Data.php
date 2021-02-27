<?php
class FarmaDelivery_MenuCache_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getCacheMenu($env='mobile') {
        $menuCacheFilename = Mage::getBaseDir() . '/var/cache/menu_' . $env . Mage::app()->getStore()->getId() . '.cache';

        if (!file_exists($menuCacheFilename)) {
            return false;
        }

        $_menu = file_get_contents($menuCacheFilename);
        return $_menu;
    }

    public function saveDesktopMenu($data)
    {
        $menuCacheFilename = Mage::getBaseDir() . '/var/cache/menu_desktop' . Mage::app()->getStore()->getId() . '.cache';
        $cacheFile = fopen($menuCacheFilename, 'w+');
        fwrite($cacheFile, $data);
        fclose($cacheFile);

        return $data;
    }

    public function saveMobileMenu($data)
    {
        $menuCacheFilename = Mage::getBaseDir() . '/var/cache/menu_mobile' . Mage::app()->getStore()->getId() . '.cache';
        $cacheFile = fopen($menuCacheFilename, 'w+');
        fwrite($cacheFile, $data);
        fclose($cacheFile);

        return $data;
    }
}
