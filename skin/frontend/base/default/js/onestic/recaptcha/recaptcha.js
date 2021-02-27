var Onestic = Onestic || {};

jQuery(function() {

    Onestic.reCAPTCHA = function() {

        var _captcha = null;
        var _referenceWhere = null;
        var _referenceWhereType = null;
        var _form = null;

        var _errorMessageID = "recaptcha-validation-" + Math.random().toString(36).substring(5);
        var _errorMessageStyle = null;
        var _errorMessageText = null;

        var _setInstances = function() {

            if (errorValidationText)
                _errorMessageText = errorValidationText;

            if (errorValidationStyle)
                _errorMessageStyle = errorValidationStyle;

            _captcha = jQuery('div.g-recaptcha');
			
            if (elWhere) {
                _referenceWhere = jQuery(elWhere);
                if (elWhereType)
                    _referenceWhereType = elWhereType;
                _form = _referenceWhere.closest("form");
            }

        };

        var _bindEvents = function() {

            onesticRecaptchaCallback = function(response) {

                if (response.length !== 0) {
                    jQuery("#" + _errorMessageID).fadeOut();
                }
                
				if (_form && _form.length > 0) {
		        	_form.each(function(index) {
		                _form[index].on('submit', function () {
		
		                    var validationMessage = jQuery("#" + _errorMessageID);
		                    
		                    if (response.length === 0) {
		
		                        validationMessage.remove();
		                        var msg_error =
		                            '<div class="validation-advice" id="' +
		                                _errorMessageID + '" style="' +
		                                (_errorMessageStyle ? _errorMessageStyle : '') +
		                            '">' +
		                            _errorMessageText +
		                            '</div>';
		                        jQuery('div.g-recaptcha',jQuery(this)).append(msg_error);
		                        event.preventDefault();
		                        return false;
		
		                    } else {
		
		                        jQuery('div.g-recaptcha',jQuery(this)).append('<input type="hidden" name="onestic_recaptcha" value="1">');
		                        validationMessage.fadeOut();
		
		                    }
		
		                });
		            });
		        }//end if form.lenght
            };

        };

        var _moveCaptcha = function() {
			
            if (_captcha && _captcha.length > 0 &&
                _referenceWhere && _referenceWhere.length > 0) {
				
				_referenceWhere.each(function(index) {
				
					jQuery(_captcha[index]).css({
												"display":"",
												"position":"relative"
												});
					
	                if (_referenceWhereType === "after") {

	                    jQuery(this).after(_captcha[index]);
	
	                } else if (_referenceWhereType === "prepend") {
	
	                    jQuery(this).prepend(_captcha[index]);
	
	                } else if (_referenceWhereType === "append") {
	
	                    jQuery(this).append(_captcha[index]);
	
	                } else {
	                
	                    jQuery(this).before(_captcha[index]);
	
	                }
				});

            }

        };

        var _init = function() {
        
            if (typeof errorValidationText === 'undefined' || typeof errorValidationStyle === 'undefined' || typeof elWhere === 'undefined' || typeof elWhereType === 'undefined') {
            	return false;
            }
            
        	_setInstances();
        	_bindEvents();
        	_moveCaptcha();
            
        };

        return {
            init: _init
        };

    };

    new Onestic.reCAPTCHA().init();

});