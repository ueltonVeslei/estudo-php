<?
function getRegionId($uf) {
	switch ($uf) {
		case 'AC': { return 485; break;}
		case 'AL': { return 486; break;}
		case 'AP': { return 487; break;}
		case 'AM': { return 488; break;}
		case 'BA': { return 489; break;}
		case 'CE': { return 490; break;}
		case 'DF': { return 511; break;}
		case 'ES': { return 491; break;}
		case 'GO': { return 492; break;}
		case 'MA': { return 493; break;}
		case 'MT': { return 494; break;}
		case 'MS': { return 495; break;}
		case 'MG': { return 496; break;}
		case 'PA': { return 497; break;}
		case 'PB': { return 498; break;}
		case 'PR': { return 499; break;}
		case 'PE': { return 500; break;}
		case 'PI': { return 501; break;}
		case 'RJ': { return 502; break;}
		case 'RN': { return 503; break;}
		case 'RS': { return 504; break;}
		case 'RO': { return 505; break;}
		case 'RR': { return 506; break;}
		case 'SC': { return 507; break;}
		case 'SP': { return 508; break;}
		case 'SE': { return 509; break;}
		case 'TO': { return 510; break;}

	}
}
$cep = $_GET['cep'];
if(!is_numeric ($cep)){
    echo '0';
  }
else{
	$client = new SoapClient("http://wscorreios.biostore.com.br/ws/service.php?class=Endereco&wsdl");
	$result = $client->getEnderecoByCep($cep);
	if ($result != null) {
		echo $result->Logradouro."|".$result->Bairro."|".$result->Cidade."|".getRegionId($result->Estado)."|".$result->CEP;
	}
	else {
	    echo "0";	
	}
}
?>
