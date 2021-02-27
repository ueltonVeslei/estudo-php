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

class AW_Advancedsearch_Block_Result_Awkbase extends AW_Advancedsearch_Block_Result_Abstract
{
    const PAGER_ID = 'kbase_posts_pager';

    public function getArticleContent($article)
    {
        $text = $this->getLayout()->createBlock('kbase/list_item')->getProccessedText($article->getShortDescription());
        return $text;
    }

    public function getArticleUrl($article)
    {
        $url = AW_Kbase_Helper_Url::getUrl(AW_Kbase_Helper_Url::URL_TYPE_ARTICLE, $article);
        return $url;
    }

    public function getPager()
    {
        $pager = $this->getChild(self::PAGER_ID);
        if (!$pager->getCollection()) {
            $pager->setCollection($this->getResults());
        }
        return $pager->toHtml();
    }
}
