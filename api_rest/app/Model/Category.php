<?php
class Model_Category {

    public function _getCategories($productId){
        $categories = array();
        $productFactory = Mage::getModel('catalog/product');
        $product = $productFactory->load($productId);
        $categoriesId = $product->getCategoryIds();
        foreach($categoriesId as $id){
            $categoryFactory = Mage::getModel('catalog/category');
            $category = $categoryFactory->load($id);
            $categories[] = $this->_getCategoryData($category);
        }
        return $categories;
    }

	public function _getCategoryData($category) {
        $categoryData = array(
            'category_id'           => $category->getId(),
            'parent_id'             => $category->getParentId(),
            'name'                  => $category->getName(),
            'description'           => $category->getDescription(),
            'status'                => $category->getIsActive(),
            'url_key'               => $category->getUrlKey(),
            'level'                 => $category->getLevel(),
            'meta_keywords'         => $category->getMetaKeywords(),
            'meta_description'      => $category->getMetaDescription(),
            'meta_title'            => $category->getMetaTitle(),
        );

        return $categoryData;
    }

}