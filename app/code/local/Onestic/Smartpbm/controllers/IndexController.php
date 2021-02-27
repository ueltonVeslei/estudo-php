<?php
class Onestic_Smartpbm_IndexController extends Mage_Core_Controller_Front_Action
{
	public function funcionalAction() {
	    $card = '60100022201100005';
	    $ean = '7896422516068';
	    echo "<h1>====== FUNCIONAL COMUNICACAO =======</h1>";
		$model = Mage::getModel('smartpbm/pbms_funcional');
		echo "<br /><h2>*** CONSULTA CARTAO ***</h2>";
		$cartao = $model->consultaCartao($card);
		echo var_export($cartao,true);
		echo "<br /><h2>*** CONSULTA PRODUTO ***</h2>";
		$produto = $model->consultaProduto($ean);
		echo var_export($produto,true);
		echo "<br /><h2>*** CONSULTA PRECO PRODUTO ***</h2>";
		$preco = $model->consultaPreco($card,$ean);
		echo var_export($preco,true);
		echo "<br /><h2>*** FIM DAS CONSULTAS ***</h2>";
    }
    
    public function vidalinkAction() {
        $card = 'FPGCONV';
        $conv = 'CT999997';
        $ean = '7894916145145';
        echo "<h1>====== VIDALINK COMUNICACAO =======</h1>";
        $model = Mage::getModel('smartpbm/pbms_vidalink');
        echo "<br /><h2>*** VALIDA CARTAO ***</h2>";
        $cartao = $model->validaCartao($card, $conv);
        echo var_export($cartao,true);
        echo "<br /><h2>*** CONSULTA PRODUTO ***</h2>";
        $produto = $model->enviaProduto($ean, $card, $conv);
        echo var_export($produto,true);
        echo "<br /><h2>*** FIM DAS CONSULTAS ***</h2>";
    }
    
    public function sevenpdvAction() {
        $card = '7770000100049';
        Mage::getSingleton('checkout/session')->unsSevenpdvTransaction();
        Mage::getSingleton('checkout/session')->unsSevenpdvChave();
        Mage::getSingleton('checkout/session')->unsSevenpdvNrCentral();
        Mage::getSingleton('checkout/session')->unsSevenpdvHoraCentral();
        Mage::getSingleton('checkout/session')->unsSevenpdvCtlAP();
        Mage::getSingleton('checkout/session')->unsSevenpdvAdministradora();
        echo "<h1>====== SEVENPDV COMUNICACAO =======</h1>";
        $model = Mage::getModel('smartpbm/pbms_sevenpdv');
        echo "<br /><h2>*** LOGON ***</h2>";
        $retorno = $model->logon();
        echo "<br /><h2>*** CONSULTA PRODUTO ***</h2>";
        $produto = $model->consultaProduto($card, 3, 1);
        echo "<br /><h2>*** FIM DAS CONSULTAS ***</h2>";
    }
}