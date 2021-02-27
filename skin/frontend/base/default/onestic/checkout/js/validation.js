if (!window.Onestic)
    var Onestic = new Object();

function mascaraDocumento(obj) {
	if(tipo_documento == "CPF") {
		mascaraCPF(obj);
	} else {
		if(tipo_documento == "CNPJ") {
			mascaraCNPJ(obj);
		} else {
			var valor = obj.value;
			valor = valor.replace(/[^a-zA-Z0-9]/gi,"");
			obj.value = valor;
		}
	}
}

function mascaraCPF(campo) {
	var valor = campo.value;
	valor = valor.replace(/\D/g,"");
	valor = valor.replace(/(\d{3})(\d)/,"$1.$2");
	valor = valor.replace(/(\d{3})\.(\d{3})(\d)/,"$1.$2.$3");
	valor = valor.replace(/(\d{3})\.(\d{3})\.(\d{3})(\d{1,2})/,"$1.$2.$3-$4");
	
	campo.value = valor;
}

function mascaraCNPJ(campo) {
	var valor = campo.value;
	valor = valor.replace(/\D/g,"");
	valor = valor.replace(/(\d{2})(\d)/,"$1.$2");
	valor = valor.replace(/(\d{2})\.(\d{3})(\d)/,"$1.$2.$3");
	valor = valor.replace(/(\d{2})\.(\d{3})\.(\d{3})(\d)/,"$1.$2.$3/$4");
	valor = valor.replace(/(\d{2})\.(\d{3})\.(\d{3})\/(\d{4})(\d)/,"$1.$2.$3/$4-$5");
	
	campo.value = valor;
}

function formata_telefone(obj){
	val = obj.value;
	val = val.replace(/\D/g,"");                 
	val = val.replace(/^(\d\d)(\d)/g,"($1) $2");
	regexCel = /^\((10)|([1-9]{2})\)\s[9][0-9\-]*/;
	if (regexCel.test(val)) {
		obj.setAttribute('maxlength',"15");
		val = val.replace(/(\d{5})(\d)/,"$1-$2");
	} else {
		obj.setAttribute('maxlength',"14");
		val = val.replace(/(\d{4})(\d)/,"$1-$2");
	}
	obj.value = val;
}
function formata_cep(obj){
	val = obj.value;
	val = val.replace(/\D/g,"");
	val = val.replace(/(\d{5})(\d)/,"$1-$2");
	obj.value = val;
}
jQuery(function(){
    jQuery('#postcode, .validate-zip-br, #street_2').bind('input', function(){
        jQuery(this).val(function(_, v){
            return v.replace(/\D/g, '');
        });
    });
});


function checkMail(mail) {
	var er = new RegExp(/^[A-Za-z0-9_\-\.]+@[A-Za-z0-9_\-\.]{2,}\.[A-Za-z0-9]{2,}(\.[A-Za-z0-9])?/);
	if (typeof (mail) == "string") {
	    if (er.test(mail)) {
	   		return true;
	    }
	} else if (typeof (mail) == "object") {
	    if (er.test(mail.value)) {
	  		return true;
	    }
	} 
	return false;
}

function checkMailDocument(value) {
	document = value.replace(/\D/g,"");
	if (document) { // é CPF/CNPJ
	 	return true;
	} else { //é e-mail
		if(checkMail(value)) {
			return true;
		}
	}
	
	return false;
}

function toggle_taxvat(value) {
	switch (value) {
		case 'Física':
			if ($('taxvat')) {
				$('taxvat').maxLength = 14;
				$$('label[for="taxvat"]').first().update('CPF');
				//$$('label[for="rgie"]').first().update('RG');
				$$('label[for="firstname"]').first().update('Nome');
				$$('label[for="lastname"]').first().update('Sobrenome');
		        $$('input[name="taxvat"]').first().writeAttribute('title','CPF');
		        //$$('input[name="rgie"]').first().writeAttribute('title','RG');
		        $$('input[name="firstname"]').first().writeAttribute('title','Nome');
		        $$('input[name="lastname"]').first().writeAttribute('title','Sobrenome');
				tipo_documento = "CPF";
			}
			break;
		case 'Jurídica':
			if ($('taxvat')) {
				$('taxvat').maxLength = 18;
				$$('label[for="taxvat"]').first().update('CNPJ');
				//$$('label[for="rgie"]').first().update('IE (Inscrição Estadual)');				
				$$('label[for="firstname"]').first().update('Razão Social');
				$$('label[for="lastname"]').first().update('N. Fantasia');
				$$('input[name="taxvat"]').first().writeAttribute('title','CNPJ');
				//$$('input[name="rgie"]').first().writeAttribute('title','IE (Inscrição Estadual)');
				$$('input[name="firstname"]').first().writeAttribute('title','Razão social');
				$$('input[name="lastname"]').first().writeAttribute('title','N. Fantasia');
				tipo_documento = "CNPJ";
			}
			break;
		case 'Estrangeiro':
			if ($('taxvat')) {
				$('taxvat').maxLength = 30;
				$$('label[for="taxvat"]').first().update('Documento');
				//$$('label[for="rgie"]').first().update('Documento 2');
				$$('label[for="firstname"]').first().update('Nome');
				$$('label[for="lastname"]').first().update('Sobrenome');
		        $$('input[name="taxvat"]').first().writeAttribute('title','Documento');
		        $$('input[name="rgie"]').first().writeAttribute('title','Documento 2');
		        $$('input[name="firstname"]').first().writeAttribute('title','Nome');
		        $$('input[name="lastname"]').first().writeAttribute('title','Sobrenome');
				tipo_documento = "Documento";
			}
			break;
		
	}
}

function validaDocumento(theCPF){ 
	if (theCPF.value == ""){ 
		return true;
	}
	
	if (tipo_documento == 'Documento') {
		return true;
	}

	var documento = theCPF.value;
	documento = documento.replace(/\D/g,"");
	
	if (!((documento.length == 11) || (documento.length == 14))) {
		alert(tipo_documento + " inválido.");
		theCPF.value = "";
		theCPF.focus();
		return (false);
	}
  
	if (((documento.length == 11) && (documento == 11111111111) || (documento == 22222222222) || (documento == 33333333333) || (documento == 44444444444) || (documento == 55555555555) || (documento == 66666666666) || (documento == 77777777777) || (documento == 88888888888) || (documento == 99999999999) || (documento == 00000000000))) { 
		alert(tipo_documento + " inválido.");
		theCPF.value = "";
		theCPF.focus(); 
		return (false); 
	} 

	var checkOK = "0123456789";
	var checkStr = documento; 
	var allValid = true; 
	var allNum = ""; 
	for (i = 0;  i < checkStr.length;  i++) { 
		ch = checkStr.charAt(i); 
		for (j = 0;  j < checkOK.length;  j++) 
			if (ch == checkOK.charAt(j)) 
				break; 
		if (j == checkOK.length) { 
			allValid = false; 
			break; 
		} 
		allNum += ch; 
	} 
	if (!allValid) { 
		alert("Favor preencher somente com numeros o campo " + tipo_documento);
		documento = "";
		theCPF.focus(); 
		return (false); 
	} 

	var chkVal = allNum; 
	var prsVal = parseFloat(allNum); 
	if (chkVal != "" && !(prsVal > "0")) { 
		alert("CPF zerado !"); 
		theCPF.value = "";
		theCPF.focus(); 
		return (false); 
	} 

	if (documento.length == 11) { 
		var tot = 0; 

		for (i = 2;  i <= 10;  i++) 
			tot += i * parseInt(checkStr.charAt(10 - i)); 

		if ((tot * 10 % 11 % 10) != parseInt(checkStr.charAt(9))) { 
			alert(tipo_documento + " inválido.");
			theCPF.value = ""; 
			theCPF.focus(); 
			return (false); 
		} 
  
		tot = 0; 
  
		for (i = 2;  i <= 11;  i++) 
			tot += i * parseInt(checkStr.charAt(11 - i)); 

		if ((tot * 10 % 11 % 10) != parseInt(checkStr.charAt(10))) { 
			alert(tipo_documento + " inválido.");
			theCPF.value = "";
			theCPF.focus(); 
			return (false); 
		} 
	} else { 
		var tot  = 0; 
		var peso = 2; 
  
		for (i = 0;  i <= 11;  i++) { 
			tot += peso * parseInt(checkStr.charAt(11 - i)); 
			peso++; 
			if (peso == 10) 
			{ 
				peso = 2; 
			} 
		} 

		if ((tot * 10 % 11 % 10) != parseInt(checkStr.charAt(12))) { 
			alert(tipo_documento + " inválido.");
			theCPF.value = "";
			theCPF.focus(); 
			return (false); 
		} 
  
		tot  = 0; 
		peso = 2; 
  
		for (i = 0;  i <= 12;  i++) { 
			tot += peso * parseInt(checkStr.charAt(12 - i)); 
			peso++; 
			if (peso == 10) 
			{ 
				peso = 2; 
			} 
		} 

		if ((tot * 10 % 11 % 10) != parseInt(checkStr.charAt(13))) { 
			alert(tipo_documento + " inválido.");
			theCPF.value = "";
			theCPF.focus(); 
			return (false); 
		} 
	} 

	return(true); 
}

function buscar_end() {
    if (!$('street_1').value.length > 0) $('street_1').value = '';
    if (!$('street_4').value.length > 0) $('street_4').value = '';
    if ($('city')) $('city').value = '';
	if ($('region')) $('region').value = '';
	if ($('region_id')) $('region_id').value = '';

    if($('postcode').value == ''){
        document.getElementById('street_1').readOnly = false;
        document.getElementById('street_4').readOnly = false;
        document.getElementById('city').readOnly = false;
        document.getElementById('region_id').disabled = false;
        document.getElementById('region').readOnly = false;
    }else{
    	// Coloque true nas opções abaixo para desabilitar os campos ao trazer o resultado
        document.getElementById('street_1').readOnly = true;
        document.getElementById('street_4').readOnly = false;
        document.getElementById('city').readOnly = true;
        document.getElementById('region_id').disabled = true;
        document.getElementById('region').readOnly = true;
    }

	var urlAjax = BASE_URL + 'onestic_checkout/customer/address';

	new Ajax.Request( urlAjax, {
		method: 'POST',
		parameters: 'cep='+$('postcode').value,
		evalScripts: true,
		onLoading: function(transport) {
			$('load-end').show();
		},
		onComplete: function(transport) {
			$('load-end').hide();
		},
		onSuccess: function(transport) {
			if (200 == transport.status) {
				try {
					eval(transport.responseText);

					if ( resultadoCEP.resultado > 0 ) {

						$('street_1').value = resultadoCEP.tipo_logradouro + ' ' + resultadoCEP.logradouro;

                        if ($('street_1').value == '' || $('street_1').value == ' ' || $('street_1').value == 'undefined') {
                            document.getElementById('street_1').readOnly = false;
                            $('street_1').value = '';
                        }else{
                            // Coloque true nas opções abaixo para desabilitar o campo rua ao trazer o resultado
                            document.getElementById('street_1').readOnly = true;
                        }

						if ($('street_4')) $('street_4').value = resultadoCEP.bairro;
                        if (!$('street_4').value) {
                            document.getElementById('street_4').readOnly = false;
                        }

						$('city').value = resultadoCEP.cidade;
                        if (!$('city').value) {
                            document.getElementById('city').readOnly = false;
						}

						/*$('region').value = resultadoCEP.uf;
                        if (!$('region').value) {
                            document.getElementById('region').readOnly = false;
						}*/
						
						for (var obj in countryRegions['BR'] ) {
							if ( resultadoCEP.uf == countryRegions['BR'][obj].code ) {
								$('region').value = obj;
								if (!$('region').value) {
									document.getElementById('region').readOnly = false;
								}
							}
						}

						for (var obj in countryRegions['BR'] ) {
							if ( resultadoCEP.uf == countryRegions['BR'][obj].code ) {
                                $('region_id').value = obj;
                                if (!$('region_id').value) {
                                    document.getElementById('region_id').disabled = false;
                                }
							}
						}
						$('street_2').focus();
					}else if(resultadoCEP.hasOwnProperty("cep"))
                    {

                    	$('street_1').value = resultadoCEP.logradouro;

                        if ($('street_1').value == '' || $('street_1').value == ' ' || $('street_1').value == 'undefined') {
                            document.getElementById('street_1').readOnly = false;
                            $('street_1').value = '';
                        }else{
                            // Coloque true nas opções abaixo para desabilitar o campo rua ao trazer o resultado
                            document.getElementById('street_1').readOnly = false;
                        }

                        if ($('street_4')) $('street_4').value = resultadoCEP.bairro;
                        if (!$('street_4').value) {
                            document.getElementById('street_4').readOnly = false;
                        }

                        $('city').value = resultadoCEP.cidade;
                        if (!$('city').value) {
                            document.getElementById('city').readOnly = false;
                        }

                        $('region').value = resultadoCEP.estado;
                        if (!$('region').value) {
                            document.getElementById('region').readOnly = false;
                        }

                        for (var obj in countryRegions['BR'] ) {
                            if ( resultadoCEP.estado == countryRegions['BR'][obj].code ) {
                                $('region_id').value = obj;
                                if (!$('region_id').value) {
                                    document.getElementById('region_id').disabled = false;
                                }
                            }
                        }
                        $('street_2').focus();
                    }else {
                        document.getElementById('street_1').readOnly = false;
                        document.getElementById('street_4').readOnly = false;
                        document.getElementById('city').readOnly = false;
                        document.getElementById('region_id').disabled = false;
                        document.getElementById('region').readOnly = false;
                    }
				} catch(e) {}
			}
		}
	});
}

function getAddress(local) {
    if (!$(local+':street1').value.length > 0) $(local+':street1').value = '';
    if ($(local+':street4')) $(local+':street4').value = '';
    if ($(local+':city')) $(local+':city').value = '';
    if ($(local+':region')) $(local+':region').value = '';
    if ($(local+':region_id')) $(local+':region_id').value = '';

    if($(local+':postcode').value == ''){
        document.getElementById(local+':street1').readOnly = false;
        document.getElementById(local+':street4').readOnly = false;
        document.getElementById(local+':city').readOnly = false;
        document.getElementById(local+':region').readOnly = false;
        document.getElementById(local+':region_id').disabled = false;
    }else{
        // Coloque true nas opções abaixo para desabilitar os campos ao trazer o resultado
        document.getElementById(local+':street1').readOnly = true;
        document.getElementById(local+':street4').readOnly = false;
        document.getElementById(local+':city').readOnly = true;
        document.getElementById(local+':region').readOnly = true;
        document.getElementById(local+':region_id').disabled = true;
    }

    var urlAjax = BASE_URL + 'onestic_checkout/customer/address';

    new Ajax.Request( urlAjax, {
        method: 'POST',
        parameters: 'cep='+$(local+':postcode').value,
        evalScripts: true,
        onLoading: function(transport) {
            $(local+':load-end').show();
        },
        onComplete: function(transport) {
            $(local+':load-end').hide();
        },
        onSuccess: function(transport) {
            if (200 == transport.status) {
                try {
                    eval(transport.responseText);

                    if ( resultadoCEP.resultado > 0 ) {

                    	$(local+':street1').value = resultadoCEP.tipo_logradouro + ' ' + resultadoCEP.logradouro;


                        if ($(local+':street1').value == '' || $(local+':street1').value == ' ' || $(local+':street1').value == 'undefined') {
                            document.getElementById(local+':street1').readOnly = false;
                            $(local+':street1').value = '';
                        }else{
                            // Coloque true nas opções abaixo para desabilitar o campo rua ao trazer o resultado
                            document.getElementById(local+':street1').readOnly = true;
                        }

                        if ($(local+':street4')) $(local+':street4').value = resultadoCEP.bairro;
                        if (!$(local+':street4').value) {
                            document.getElementById(local+':street4').readOnly = false;
                        }

                        $(local+':city').value = resultadoCEP.cidade;
                        if (!$(local+':city').value) {
                            document.getElementById(local+':city').readOnly = false;
                        }

                        $(local+':region').value = resultadoCEP.uf;
                        if (!$(local+':region').value) {
                            document.getElementById(local+':region').readOnly = false;
                        }

                        for (var obj in countryRegions['BR'] ) {
                            if ( resultadoCEP.uf == countryRegions['BR'][obj].code ) {
                                $(local+':region_id').value = obj;
                                if (!$(local+':region_id').value) {
                                    document.getElementById(local+':region_id').disabled = false;
                                }
                            }
                        }
                        $('street_2').focus();
                    }else if(resultadoCEP.hasOwnProperty("cep")) {

                    	$(local+':street1').value = resultadoCEP.logradouro;


                        if ($(local+':street1').value == '' || $(local+':street1').value == ' ' || $(local+':street1').value == 'undefined') {
                            document.getElementById(local+':street1').readOnly = false;
                            $(local+':street1').value = '';
                        }else{
                            // Coloque true nas opções abaixo para desabilitar o campo rua ao trazer o resultado
                            document.getElementById(local+':street1').readOnly = false;
                        }

                        if ($(local+':street4')) $(local+':street4').value = resultadoCEP.bairro;
                        if (!$(local+':street4').value) {
                            document.getElementById(local+':street4').readOnly = false;
                        }

                        $(local+':city').value = resultadoCEP.cidade;
                        if (!$(local+':city').value) {
                            document.getElementById(local+':city').readOnly = false;
                        }

                        $(local+':region').value = resultadoCEP.estado;
                        if (!$(local+':region').value) {
                            document.getElementById(local+':region').readOnly = false;
                        }

                        for (var obj in countryRegions['BR'] ) {
                            if ( resultadoCEP.estado == countryRegions['BR'][obj].code ) {
                                $(local+':region_id').value = obj;
                                if (!$(local+':region_id').value) {
                                    document.getElementById(local+':region_id').disabled = false;
                                }
                            }
                        }
                        $('street_2').focus();
                    }else {
                        document.getElementById(local+':street1').readOnly = false;
                        document.getElementById(local+':street4').readOnly = false;
                        document.getElementById(local+':city').readOnly = false;
                        document.getElementById(local+':region_id').disabled = false;
                        document.getElementById(local+':region').readOnly = false;
                    }
                } catch(e) {}
            }
        }
    });
}

function existEmail(value) {
    if (checkMail(value)) {
   		var ok = false;
    	var url = BASE_URL + 'onestic_checkout/customer/emailExists/';
   		new Ajax.Request(url, {
	      	method: 'post',
	      	asynchronous: false,
	     	parameters: 'email=' + encodeURIComponent(value),
	       	onSuccess: function(transport) {
				var obj = response = eval('(' + transport.responseText + ')');
				validateTrueEmailMsg = obj.status_desc;
				if (obj.result !== 'clean') {
					Validation.get('validate-email-exist').error = 'Email já cadastrado';
					ok = false;
				} else {
	            	ok = true;
	            }
	        },
	        onComplete: function() {
	          	if ($('advice-validate-email-exist-email')) {
	          		$('advice-validate-email-exist-email').remove();
	            }
	        }
       	});
        return ok;
    } else {
  		Validation.get('validate-email').error = 'Por favor informe um endereço de email válido, exemplo: meu@email.com';
    }
}

function existDocument(elm) {
	if (validaDocumento(elm)) {
		var ok = false;
		var url = BASE_URL + 'onestic_checkout/customer/taxvatExists/';
		new Ajax.Request(url, {
			method: 'post',
			asynchronous: false,
			parameters: 'taxvat=' + encodeURIComponent(elm.value),
			onSuccess: function(transport) {
				var obj = response = eval('(' + transport.responseText + ')');
				validateTrueEmailMsg = obj.status_desc;
                if (obj.result !== 'clean') {
                    jQuery('#cpf-existe').html("CPF já cadastrado! <a href='/customer/account/login/'>Clique aqui</a> para acessar sua conta");
                    jQuery('#mensagem-erro').html("CPF já cadastrado! <a href='/customer/account/login/'>Clique aqui</a> para acessar sua conta");
                    jQuery('.btn-primary').hide();
                    ok = false;
                } else {
                    ok = true;
                    jQuery('#cpf-existe').html("");
                    jQuery('#mensagem-erro').html("");
                    jQuery('.btn-primary').show();
                }
			},
			onComplete: function() {
				if ($('advice-validate-taxvat-taxvat')) {
					$('advice-validate-taxvat-taxvat').remove();
				}
			}
		});
		return ok;
	}else{
		Validation.get('validate-taxvat').error = 'O CPF/CNPJ informado \xE9 inválido';
	}
}

Onestic.DOB = Class.create();
Onestic.DOB.prototype = {
    initialize: function(selector, required, format) {
        var el = $$(selector)[0];
        var container       = {};
        container.day       = Element.select(el, '.dob-day select')[0];
        container.month     = Element.select(el, '.dob-month select')[0];
        container.year      = Element.select(el, '.dob-year select')[0];
        container.full      = Element.select(el, '.dob-full input')[0];
        container.advice    = Element.select(el, '.validation-advice')[0];

        new Varien.DateElement('container', container, required, format);
    }
};