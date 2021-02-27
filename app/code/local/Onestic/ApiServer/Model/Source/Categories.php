<?php
class Onestic_ApiServer_Model_Source_Categories {
    
    protected $_tree;
    
    public function toOptionArray()
    {
        $this->_tree = array();
        $categories = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addIsActiveFilter();
        foreach ($categories as $category){
            if (!in_array($category->getId(),$this->_tree)) {
                $options[] = array(
                   'value' => $category->getId(),
                   'label' => $category->getName()
                );
                $this->_tree[] = $category->getId();
                $tree = $this->_getCategoriesTree($category,1);
                $options = array_merge($options, $tree);
            }
        }
        return $options;
    }
    
    protected function _getCategoriesTree($category,$level=1) {
        //$category = Mage::getModel('catalog/category')->load($parent->getId());
        $options = array();
        if ($category->getChildren()) {
            $subcategories = explode(',', $category->getChildren());
            foreach ($subcategories as $subcat) {
                if (!in_array($subcat,$this->_tree)) {
                    $subcategory = Mage::getModel('catalog/category')->load($subcat);
                    $options[] = array(
                        'value' => $subcategory->getId(),
                        'label' => str_repeat('-', $level) . $subcategory->getName()
                    );
                    $this->_tree[] = $subcategory->getId();
                    $tree = $this->_getCategoriesTree($subcategory,$level+1);
                    $options = array_merge($options, $tree);
                }
            }
        }
        
        return $options;
    }
    
}

