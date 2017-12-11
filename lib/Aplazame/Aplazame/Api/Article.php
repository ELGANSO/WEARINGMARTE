<?php

final class Aplazame_Aplazame_Api_Article
{
    /** @var Mage_Catalog_Model_Product */
    private $productModel;

    public function __construct(Mage_Catalog_Model_Product $productModel)
    {
        $this->productModel = $productModel;
    }

    public function articles(array $queryArguments)
    {
        $page = (isset($queryArguments['page'])) ? $queryArguments['page'] : 1;
        $page_size = (isset($queryArguments['page_size'])) ? $queryArguments['page_size'] : 10;

        /** @var Mage_Catalog_Model_Resource_Product_Collection|Mage_Catalog_Model_Product[] $products */
        $products = $this->productModel
            ->getCollection()
            ->addAttributeToSelect(array(
                'name',
                'description'
            ))
            ->addAttributeToFilter('visibility', array('in' => Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds()))
            ->setPage($page, $page_size)
        ;

        $articles = array_map(array('Aplazame_Aplazame_Api_BusinessModel_Article', 'createFromProduct'), $products);

        return Aplazame_Aplazame_ApiController::collection($page, $page_size, $articles);
    }
}
