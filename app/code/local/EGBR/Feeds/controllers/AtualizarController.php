<?php
/**
 * @desc Interface de acesso externo para gerar os feeds XML - STeV
 * 
 * @author Estevam Neves
 *
 */
class EGBR_Feeds_AtualizarController extends Mage_Core_Controller_Front_Action{
    public function todosAction(){
    	Mage::getModel('feeds/cliquefarma')->toCliqueFarma();
    	Mage::getModel('feeds/multifarmas')->toMultiFarmas();
    	Mage::getModel('feeds/Zoomfeed')->toZoom();
    }
    
    public function cliquefarmaAction(){
    	Mage::getModel('feeds/cliquefarma')->toCliqueFarma();
    }
    
    public function multifarmasAction(){
    	Mage::getModel('feeds/multifarmas')->toMultiFarmas();
    }
    
    public function zoomAction(){
    	Mage::getModel('feeds/zoomfeed')->toZoom();
    }
    
	public function zoomtesteAction(){
    	Mage::getModel('feeds/zoomfeed')->gerarZoomXml();
    }
    
    public function bysqlAction(){
        Mage::getModel('feeds/multifarmas')->bySql();
    }
}