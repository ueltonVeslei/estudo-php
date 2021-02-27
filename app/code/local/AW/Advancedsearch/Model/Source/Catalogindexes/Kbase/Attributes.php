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

class AW_Advancedsearch_Model_Source_Catalogindexes_Kbase_Attributes extends AW_Advancedsearch_Model_Source_Abstract
{
    const TITLE = 'article_title';
    const CONTENT = 'article_text';
    const TAGS = 'article_tags';
    const CATEGORY = 'category_names';

    const TITLE_LABEL = 'Title';
    const CONTENT_LABEL = 'Content';
    const TAGS_LABEL = 'Tag';
    const CATEGORY_LABEL = 'Category Name';

    protected function _toOptionArray()
    {
        $helper = $this->_getHelper();
        return array(
                     array('value' => self::TITLE,    'label' => $helper->__(self::TITLE_LABEL)),
                     array('value' => self::CONTENT,  'label' => $helper->__(self::CONTENT_LABEL)),
                     array('value' => self::TAGS,      'label' => $helper->__(self::TAGS_LABEL)),
                     array('value' => self::CATEGORY, 'label' => $helper->__(self::CATEGORY_LABEL))
        );
    }
}
