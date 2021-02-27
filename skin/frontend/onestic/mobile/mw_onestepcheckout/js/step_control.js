jQuery(function(){
	
    var controlStepInterval = null;
	
    /**
	 * verifica o preenchimento no billing e shipping
	 */
    var verifyBilling = function(){
        var func_return = true; 
        //var text = '';
        jQuery('#billing-block').find('.required-entry').each(function(){
			
            if(jQuery('#billing-address-select').val()) return; 
            if(jQuery('#ship_to_same_address_observe').val() == 0 && jQuery('#shipping-address-select').val()) return;
            if(jQuery('#ship_to_same_address_observe').val() == 1 && /^shipping/.test(jQuery(this).attr('name'))) return;
            if(/\[region\]$/.test(jQuery(this).attr('name'))) return; 
			
            if(/^(billing|shipping)/.test(jQuery(this).attr('name'))){
                if(!jQuery(this).val()){
                    func_return = false;
                //text += jQuery(this).attr('name') + ' - ' + jQuery(this).attr('value') + '; ';
                }
            }
        });
        //alert(text);
		
        if(!func_return){
            disabledStep('shipping');
        }else{
            enabledStep('shipping');
        }
		
        return func_return;
    };
	
    /**
	 * verifica se foi seleciona alguma forma de envio
	 */
    var verifyShipping = function(lastState){
        var func_return = false;
		
        jQuery('#shipping-block').find('.radio').each(function(){
            if(jQuery(this).attr('checked')){
                func_return = true;
            }
        });
		
        if(!func_return || !lastState){
            disabledStep('payment');
            func_return = false;
        }else{
            enabledStep('payment');
        }
		
        return func_return;
    };
	
    /**
	 * verifica o preenchimento nas formas de pagamento
	 */
    var verifyPayment = function(lastState){
        var func_return = true; 
		
        func_return = (jQuery('#payment-block .ativo').length)? true: false;
		
        jQuery('#payment-block .ativo').find('.required-entry').each(function(){
            if(!jQuery(this).val()){
                func_return = false;
            }
        });
		
        if(!func_return || !lastState){
            disabledStep('review');
            func_return = false;
        }else{
            enabledStep('review');
        }
		
        return func_return;
    };
	
    disabledStep = function(step){
        switch(step){
            case 'shipping':
                jQuery('#shipping-block').addClass('inative-block');
                jQuery('input[name="shipping_method"]').attr('disabled','disabled');
                break;
            case 'payment':
                jQuery('#payment-block').addClass('inative-block');
                jQuery('#payment-block').find('input,select').attr('disabled','disabled');
                break;
            case 'review':
                jQuery('#review-block').addClass('inative-block');
                jQuery('#review-block').find('input,select').attr('disabled','disabled');
                jQuery('#review-block #btnfinalizar').addClass('inative-final-button').attr('disabled','disabled');
                break;
        }
    };
	
    enabledStep = function(step){
        switch(step){
            case 'shipping':
                jQuery('#shipping-block').removeClass('inative-block');
                jQuery('input[name="shipping_method"]').removeAttr('disabled');
                break;
            case 'payment':
                if(jQuery('#payment-block').hasClass('inative-block')){
                    jQuery('#payment-block').removeClass('inative-block');
                }
                jQuery('#payment-block').find('input,select').removeAttr('disabled');
                break;
            case 'review':
                jQuery('#review-block').removeClass('inative-block');
                jQuery('#review-block').find('input,select').removeAttr('disabled');
                jQuery('#review-block #btnfinalizar').removeClass('inative-final-button').removeAttr('disabled');
                break;
        }
    };
	
    /**
	 * verifica todos os passos, disparando os verifys metodos 
	 */
    checkSteps = function(){
        var func_return = true;
		
        func_return = verifyBilling();
        func_return = verifyShipping(func_return);
        func_return = verifyPayment(func_return);
		
        return func_return;
    };
	
    /**
	 * Inicia as verificações
	 */
    initControlStep = function(){
        controlStepInterval = setInterval(function(){
            checkSteps();
        },100);
    };
	
    /**
	 * Para todas as verificações
	 */
    stopControlStep = function(){
        clearInterval(controlStepInterval);
    };
	
    initControlStep();
});
