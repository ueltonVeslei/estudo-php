<?php
class Onestic_Elastic_ExporterCategories {

    protected $_elasticApi = null;
    protected $_configs = null;

    public function export() {
        $this->_getElasticApi()->setIndexName($this->getNameIndex());

        $categories = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('entity_id')
            ->addIsActiveFilter();
        foreach ($categories as $category){
            try {
                $categoryFull = Mage::getModel('catalog/category')->load($category->getId());
                $this->_getCategoriesTree($categoryFull,1);
            } catch (Exception $e) {
                Mage::log('EXPORT ERROR: ' . $e->getMessage(), null, 'export_elastic_categories.log');
            }
        }

        echo 'CATEGORIAS ENVIADAS COM SUCESSO!' . PHP_EOL;
    }

    protected function _getCategoriesTree($category,$level=1) {
        echo str_repeat('-', $level) . $category->getName() . PHP_EOL;
        $this->_send($category);
        if ($category->getChildren()) {
            $subcategories = explode(',', $category->getChildren());
            foreach ($subcategories as $subcat) {
                $subcategory = Mage::getModel('catalog/category')->load($subcat);
                $this->_getCategoriesTree($subcategory,$level+1);
            }
        }
    }

    protected function getNameIndex(){
        return $this->_getConfig('name'). '-category';
    }

    protected function _send($category) {
        $categoryData = $this->_getCategoryData($category);
        $this->_getElasticApi()->mapperUntouchedField('category', 'url_key');
        $this->_getElasticApi()->sendCategories('category_id', [$categoryData]);
    }

    protected function _getConfig($name) {
        if (!$this->_configs) {
            $this->_configs = json_decode(file_get_contents(ABSOLUTE_PATH . '/config.json'));
        }

        return $this->_configs->{$name};
    }

    protected function _getElasticApi() {
        if (!$this->_elasticApi) {
            $this->_elasticApi = new ElasticIndex($this->_getConfig('host'), $this->_getConfig('user'), $this->_getConfig('pass'));
        }
 	    $this->_elasticApi->setIndexName($this->getNameIndex());

        return $this->_elasticApi;
    }

    protected function _getCategoryData($category) {
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
