/**
 * This file is part of AwesomeCheckout.
 *
 * AwesomeCheckout is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * AwesomeCheckout is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AwesomeCheckout.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Global functions
 */

function ccType(num) {
	num = num.replace(/[^\d]/g, '');
	// only consider the first 6 digits to match
	num = num.slice(0,6);
	if (num.match(/^5[1-5][0-9]{4}$/)) {
		return 'MasterCard';
	} else if (num.match(/^4[0-9]{5}(?:[0-9]{3})?$/)) {
		return 'Visa';
	} else if (num.match(/^3[47][0-9]{4}$/)) {
		return 'AmEx';
	} else if (num.match(/^(606282)|(3841[0-9]{2})/)) {
		return 'Hipercard';
	} else if (num.match(/^6(?:011|5[0-9]{2}|[4][4-9][0-9])[0-9]{2}$/)) {
		return 'Discover';
	} else if (num.length == 6 && parseInt(num) >= 622126 && parseInt(num) <= 622925) {
		return 'Discover';
	}
	return null;
}

function magentoCcType(name,pg) {
	if (pg) {
		if (pg == 'sagepaydirectpro_cc_number') {
			// VISA,MC,DELTA,SWITCH,MAESTRO,AMEX,UKE,DINERS,JCB,LASER
			var mc = {
				Visa: "VISA",
				MasterCard: "MC",
				// SagePay doesn't support Discover but leaving it here won't hurt & will work as is (if it accepts DI) when added in future
				Discover: "DI",
				AmEx: "AMEX"
			};
		}
	} else {
		var mc = {
			Visa: "VI",
			MasterCard: "MC",
			Discover: "DI",
			AmEx: "AE"
		};
	}
	return mc[name];
}

function getErrorMessageLine(id) {
	id = id.replace('shipping:','');
	id = id.replace('billing:','');
	var message;
	switch (id) {
		case 'login-email': message = 'E-mail'; break;
		case 'login-password': message = 'Senha'; break;
		case 'fullname': message = 'Nome Completo'; break;
		case 'street1': message = 'Rua'; break;
		case 'postcode': message = 'CEP'; break;
		case 'city': message = 'Cidade'; break;
		case 'region_id': message = 'Estado'; break;
		case 'country_id': message = 'País'; break;
		case 'telephone': message = 'Telefone'; break;
		case 'ccsave_cc_number':
		case 'authorizenet_cc_number': message = 'Número do Cartão de Crédito'; break;
		case 'mundipagg_creditcard_cc_holder_name_1_1':
		case 'mundipagg_twocreditcards_cc_holder_name_2_1':
		case 'mundipagg_twocreditcards_cc_holder_name_2_2':
		case 'pagseguro_cc_cc_owner': message = 'Nome completo no Cartão de Crédito'; break;
		case 'ccsave_expiration':
		case 'authorizenet_expiration': message = 'Data de expiração do Cartão de Crédito (Mês)'; break;
		case 'ccsave_expiration_yr':
		case 'authorizenet_expiration_yr': message = 'Data de expiração do Cartão de Crédito (Ano)'; break;
		case 'authorizenet_cc_cid':
		case 'ccsave_cc_cid': message = 'Número de Verificação do Cartão'; break;
		default: message = 'Informação incorreta';
	}
	// If default message is being used, try to guess for CC fields, if its some payment extension
	if (message == 'Informação incorreta') {
		if (id.indexOf('cc_owner') != -1)
			message = 'Nome completo no Cartão de Crédito';
		if (id.indexOf('cc_number') != -1)
			message = 'Número do Cartão de Crédito';
		if (id.indexOf('expiration') != -1)
			message = 'Data de expiração do Cartão de Crédito (Mês)';
		if (id.indexOf('expiration_yr') != -1)
			message = 'Data de expiração do Cartão de Crédito (Ano)';
		if (id.indexOf('cc_cid') != -1)
			message = 'Número de Verificação do Cartão';
	}
	return '<li>'+message+'</li>';
}

// jQuery modal function as an alternative to bootstrap's modal
var modalfreeze = false;
jQuery.fn.modal = function (options) {
	// preventing against accidental double click
	if (modalfreeze)
		return;

	if (!options)
		return this.modal(this.is(':visible') ? 'hide' : 'show');

	var el = this;
	var hide = function () {
		el.modal('hide');
		return false;
	}
	var show = function () {
		el.modal('show');
		return false;
	}
	var keypresshandler = function (e) {
		if (e.keyCode === 27)
			hide();
		return false;
	}

	modalfreeze = true;
	if (options == 'show') {
		jQuery('body').append('<div class="modal-backdrop fade in"></div>');
		this.show().animate({top: '50%', opacity: 1}, 500, null, function () {
			jQuery(this).removeClass('hide').addClass('in');
			jQuery(this).one('click', '[data-dismiss="modal"]', hide);
			jQuery('div.modal-backdrop').on('click', hide);
			jQuery(document).on('keyup', keypresshandler);
			modalfreeze = false;
		});
	} else if (options == 'hide') {
		this.off('click', '[data-dismiss="modal"]', hide);
		jQuery('div.modal-backdrop').off('click', hide);
		jQuery(document).off('keyup', keypresshandler);
		this.animate({top: '-25%', opacity: 0}, 500, null, function () {
			jQuery(this).removeClass('in').hide();
			jQuery('div.modal-backdrop').remove();
			modalfreeze = false;
		});
	}

	return this;
}

// Extend Array prototype with custom inArray method
Array.prototype.inArray = function(element) {
	for (var i=0; i < this.length; i++) {
		if (element == this[i])
			return i;
	}
	return false;
};

// Extend Array prototype with custom pushIfNotExist method
Array.prototype.pushIfNotExist = function(element) {
	if ( this.inArray(element) === false ) {
		this.push(element);
	}
};

// Extend Array prototype with custom removeIfExist method
Array.prototype.removeIfExist = function(element) {
	var index = this.inArray(element);
	if ( index !== false ) {
		this.splice(index,1);
	}
}

/**
 * Validator settings
 */
var validatorSettings = {
	debug: false,
	errorPlacement: function(error, element) {
		var id = element.attr('id');

		jQuery(jq(id)).parents('.fields').find('.sidetip p').hide();
		jQuery(jq(id)).parents('.fields').find('.sidetip .bad').show();

		// change IDs for fields where we have single label for 2 inputs
		id = (id == 'shipping:region') ? 'shipping:region_id' : id;
		id = (id == 'shipping:country_label') ? 'shipping:country_id' : id;
		id = (id == 'billing:region') ? 'billing:region_id' : id;
		id = (id == 'billing:country_label') ? 'billing:country_id' : id;
		jQuery('label[for="' + id + '"]').addClass('err');

		if (checkout.collectErrorsFlag)
			checkout.errorsCollection.pushIfNotExist(getErrorMessageLine(id));
	},
	success: function(label) {
		var for_attr = label.attr('for');

		jQuery(jq(for_attr)).parents('.fields').find('.sidetip p').hide();
		// show checkmark only when the field has lost focus
		if (!jQuery(jq(for_attr)).is(':focus'))
			jQuery(jq(for_attr)).parents('.fields').find('.sidetip .good').show().delay(2000).fadeOut();

		// change IDs for fields where we have single label for 2 inputs
		for_attr = (for_attr == 'shipping:country_label') ? 'shipping:country_id' : for_attr;
		for_attr = (for_attr == 'shipping:region') ? 'shipping:region_id' : for_attr;
		for_attr = (for_attr == 'billing:country_label') ? 'billing:country_id' : for_attr;
		for_attr = (for_attr == 'billing:region') ? 'billing:region_id' : for_attr;
		jQuery('label[for="' + for_attr + '"]').removeClass('err');

		if (checkout.collectErrorsFlag)
			checkout.errorsCollection.removeIfExist(getErrorMessageLine(for_attr));
	}
};

// Trigger blur on email field to counter-attach browser's autocomplete feature
window.onload = function() {
	if (jQuery('#login-email') && jQuery('#login-email').val() != '') {
		jQuery('#login-email').trigger('blur');
	};
}

// Make checkout go back when interacting via browser's back
window.onpopstate = function(event) {
	if (event.state != null)
		jQuery('a[href=#'+event.state.step+']').trigger('click');
};

/**
 * Document ready
 */

jQuery(document).ready(function() {

	// clear any hashes
	location.hash = '';

	var handler = function (e) {
		var form_type = jQuery(this).closest('form').attr('id').split('-')[1];
		if ('payment' == form_type)
			form_type = 'billing';
		//checkout.setPhoneMasking(form_type);
		return false;
	}

	jQuery('#checkoutSteps').on('change', jq('shipping:country_id') + ',' + jq('billing:country_id'), handler);

	// trigger the phonemasking handler on page load
	jQuery(jq('shipping:country_id')).trigger('change');

	checkout.prepareAddressForm('shipping');
	jQuery('.validate-cc-number').live('keyup change', function() {
		var type = window.ccType(this.value);
		var cards = jQuery(this).parents('ul:first').find('.cards');
		if (cards.length) {
			jQuery('li', cards).removeClass('on').addClass('off');
			var ccType = jQuery(this).parents('ul:first').find('.cc_type')
			jQuery(ccType).val('');
			if (type) {
				var mType = magentoCcType(type);
				jQuery('li.' + mType, cards).removeClass('off').addClass('on');
				// If this is Sagepay, then save ccType's field value which it can accept
				if (jQuery(this).attr('id') == 'sagepaydirectpro_cc_number') mType = magentoCcType(type,'sagepaydirectpro_cc_number');
				jQuery(ccType).val(mType);
			}
		}
	});

	jQuery('#login-email').blur(function() {
		if (jQuery(this).closest('form').validate().element(this)) {
			checkout.checkEmailExists(this.value);
		};
	});

	// This was added because the original function used .required-entry as the class
	var _orig_init_func = RegionUpdater.prototype.initialize;
	RegionUpdater.prototype.initialize = function(countryEl, regionTextEl, regionSelectEl, regions, disableAction, zipEl) {
		var config = regions.config;
		_orig_init_func.call(this, countryEl, regionTextEl, regionSelectEl, regions, disableAction, zipEl);
		regions.config = config;
	};
	var _orig_func = RegionUpdater.prototype._checkRegionRequired;
	RegionUpdater.prototype._checkRegionRequired = function() {
		_orig_func.call(this);
		var elements = [this.regionTextEl, this.regionSelectEl];
		if(typeof this.config == 'undefined') {
			return;
		}
		var regionRequired = this.config.regions_required.indexOf(this.countryEl.value) >= 0;

		elements.each(function(currentElement) {
			if(!regionRequired) {
				if(currentElement.hasClassName('required')) {
					currentElement.removeClassName('required');
				}
				if('select' == currentElement.tagName.toLowerCase() &&
					currentElement.hasClassName('validate-select')) {
					currentElement.removeClassName('validate-select');
				}
			} else {
				if(!currentElement.hasClassName('required')) {
					currentElement.addClassName('required');
				}
				if('select' == currentElement.tagName.toLowerCase() && !currentElement.hasClassName('validate-select')) {
					currentElement.addClassName('validate-select');
				}
			}
		});
	};

	jQuery('#checkout-shipping-method-load input[type=radio]:checked').click();
	jQuery(document).on('click', '#checkout-shipping-method-load input[type=radio]', function(){
		shipping.selectedShippingMethod = jQuery(this).val();
	});

	if(typeof EbizmartsSagePaySuite !== 'undefined' && typeof EbizmartsSagePaySuite.Checkout !== 'undefined' ){
		EbizmartsSagePaySuite.Checkout.prototype.getPaymentMethod = function(){
			var form = null;

    			//Fix this when using IE patch

    			if($('multishipping-billing-form')){
        			form = $('multishipping-billing-form');
    			}else if(this.getConfig('osc')){
        			form = this.getConfig('oscFrm');
    			}else if((typeof this.getConfig('payment')) != 'undefined'){
        			form = $(this.getConfig('payment').form)[0];
    			}

    			if(form === null){
       	 			return this.code;
    			}

    			var checkedPayment = null

    			form.getInputs('radio', 'payment[method]').each(function(el){
        			if(el.checked){
            			checkedPayment = el.value;
            			throw $break;
        			}
    			});

    			if(checkedPayment != null){
        			return checkedPayment;
    			}

    			return this.code;
		};
	}
});

/**
 * Checkout
 * @param options
 */
Checkout = function(accordion, options) {
	this.init(accordion, options);
}
jQuery.extend(Checkout.prototype, {

	options: null,
	accordion: null,
	activeSection: 'shipping',
	loadWaiting: false,
	placeholderSupport: 'placeholder' in document.createElement('input'),
	currentShippingMethodIndex: null,
	highlightShippingCost: false, // flag to indicate whether we need to highlight shipping cost in progress box
	collectErrorsFlag: 0, // flag to indicate whether to collect validation errors to show in a modal box
	errorsCollection: [], // placeholder for collecting validation error messages
	GASteps: [{"payment":0}, {"review":0}], // google analytics steps

	init: function(accordion, options) {
		this.accordion = accordion;
		this.options = options;
		if(this.options.separate_shipping_method_step){
			this.GASteps= [{"shipping_method":0}, {"payment":0}, {"review":0}];
		}
	},

	reloadProgressBlock: function(section) {
		jQuery('#checkout-progress-wrapper').load(this.options.progressUrl + "?section=" + section,function(){
			jQuery('table.p-final tr.shine').delay(2000).queue(function(next){
				jQuery(this).removeClass('shine');
				next();
			});
			if (checkout.highlightShippingCost) {
				jQuery('.shipping-mthd-total').addClass('price-highlight').delay(800).queue(function(next){
					jQuery('.shipping-mthd-total').removeClass('price-highlight');
					next();
				});
				checkout.highlightShippingCost = false;
			}
			if('shipping_method' === section){
				jQuery('#checkout-shipping-method-load input[value='+shipping.selectedShippingMethod+']').click();
			}
		});
		if('shipping_method' === section){
			jQuery('#checkout-shipping-method-load input[value='+shipping.selectedShippingMethod+']').click();
		}
	},

	reloadReviewBlock: function() {
		jQuery('#checkout-review-load').load(this.options.reviewUrl);
	},

	_disableEnableAll: function(element, isDisabled) {
		var descendants = element.descendants();
		for (var k in descendants) {
			descendants[k].disabled = isDisabled;
		}
		element.disabled = isDisabled;
	},

	setLoadWaiting: function(step, keepDisabled) {
		if (step) {
			if (this.loadWaiting) {
				this.setLoadWaiting(false);
			}
			var container = $(step + '-buttons-container');
			container.addClassName('disabled');
			container.setStyle({
				opacity: .5
			});
			this._disableEnableAll(container, true);
			Element.show(step + '-please-wait');
		} else {
			if (this.loadWaiting) {
				var container = $(this.loadWaiting + '-buttons-container');
				var isDisabled = (keepDisabled ? true : false);
				if (!isDisabled) {
					container.removeClassName('disabled');
					container.setStyle({
						opacity: 1
					});
				}
				this._disableEnableAll(container, isDisabled);
				Element.hide(this.loadWaiting + '-please-wait');
			}
		}
		this.loadWaiting = step;
	},

	gotoSection: function(section) {
		// @TODO: add GA tracking via hooks
		if ((typeof _gaq !== "undefined" && _gaq !== null) || typeof ga !== 'undefined') {
			var i = 0;
			if (section === 'review' && this.options.separate_shipping_method_step) {
				i = 2;
			} else if (section === 'review') {
				i = 1;
			}
			if (section === 'payment' && this.options.separate_shipping_method_step) {
				i = 1;
			}
			if (typeof checkout.GASteps[i][section] !== 'undefined' && checkout.GASteps[i][section] === 0)
			{
				if(typeof _gaq !== "undefined" && _gaq !== null){
					_gaq.push(['_trackPageview', '/checkout/onepage/' + section + '/']);
				}
				if(typeof ga !== 'undefined'){
					ga('send', 'pageview', '/checkout/onepage/' + section + '/');
				}
				checkout.GASteps[i][section] = 1;
			}
		}
		this.activeSection = section;
		block = jQuery('#opc-' + section);
		block.addClass('allow');
		this.accordion.openSection('opc-' + section);
	},

	openSection: function(section) {
		this.activeSection = section;
		this.accordion.openSection('opc-' + section);
		this.markActiveSection();
                if(!checkout.options.disable_postcode_autocomplete){
			jQuery(jq('shipping-postcode-please-wait')).hide();
			jQuery(jq('billing-postcode-please-wait')).hide();
		}
	},

	markActiveSection: function() {
		var section = this.activeSection == 'shipping_method' ? 'shipping' : this.activeSection; // shipping and shipping_method share progress-block
		if (section != 'review') jQuery('.progress-block.review .buttons-set').remove(); // remove submit button
		this.reloadProgressBlock(section);
		jQuery('.progress-block').removeClass('active');
		jQuery('.progress-block.' + section).addClass('active');
		jQuery('.mark-arrow').appendTo(jQuery('.progress-block.' + section));
	},

	back: function() {
		if (this.loadWaiting) return;
		this.accordion.openPrevSection(true);
	},

	saveShippingMethodIndex: function(){
		this.currentShippingMethodIndex = jQuery('#checkout-shipping-method-load input[type=radio]').index(jQuery('#checkout-shipping-method-load input[type=radio]:checked'));
	},

	getSavedShippingMethodIndex: function(){
		return this.currentShippingMethodIndex;
	},

	getCurrentShippingMethodIndex: function() {
		return jQuery('#checkout-shipping-method-load input[type=radio]').index(jQuery('#checkout-shipping-method-load input[type=radio]:checked'));
	},

	setStepResponse: function(response) {
		if (response.update_section) {
			// Sometimes 'payment-method' is used instead of 'payment'
			sectionName = ("payment-method" === response.update_section.name) ? "payment" : response.update_section.name;

			jQuery('#checkout-' + sectionName + '-load').html(response.update_section.html);
		}
		if (response.allow_sections) {
			response.allow_sections.each(function(e) {
				$('opc-' + e).addClass('allow');
			});
		}

		if (response.goto_section) {
			// Useful for customers on phones, otherwise they might start a step half way down the screen.
			if('shipping_method' !== response.goto_section) {
				// If we are on the shipping method step we don't need to set the scroll position as shipping_method step is a part of shipping step
				jQuery('html, body').scrollTop(0);
			}

			this.reloadProgressBlock(response.goto_section);
			this.gotoSection(response.goto_section);
			if(billing.sameAsBillingChecked === 0){
				checkout.prepareAddressForm('billing');
				jQuery('.dflt-adrs-labl input[type=checkbox][name="billing[same_as_shipping]"]').removeAttr('checked');
				jQuery('#billing-address-previously-saved').hide(400);
				jQuery('#billing-new-address-form').show(400);
				jQuery('#billing-address-dropdown').show(400);
			}
			return true;
		}
		if (response.redirect) {
			location.href = response.redirect;
			return true;
		}
		return false;
	},

	handleError: function(msg,title) {
		if (typeof(msg) == 'object')
			msg = msg.join("\n<br />");

		// set title
		if (title)
			jQuery('#error-message .modal-ac-header h3').text(title);
		else
			jQuery('#error-message .modal-ac-header h3').text('Oops...');

		if (msg) {
			jQuery('#error-message .modal-ac-body').html(msg);
			jQuery('#error-message').modal('show');
		}
		return false;
	},

	checkEmailExists: function(email) {
		// checking if there has actually been a change since the last time this function was called, this is because of bug in IE that it calls change event even when value hasn't been modified
		if(email === this._previouscheckingemail)
			return;
		this._previouscheckingemail = email;

		jQuery('#email-please-wait').fadeIn();
		var that = this;
		jQuery.post(this.options.emailExistsUrl, {
			email: email
		}, function(data) {
			jQuery('#email-please-wait').fadeOut();
			jQuery('#login-password-container').show();
			if (data.exists) {
				jQuery('.has-account').show();
				jQuery('.already_customer').hide();
				jQuery('.create_account').hide();
				jQuery('#login-submit').show();
				jQuery('#send-new-password').show();
				jQuery('#login-email').parents('.fields').find('.sidetip .good').show().delay(2000).fadeOut();
				jQuery('.enter_password label').text( Translator.translate( 'Password' ) );
				if(jQuery('.create_account input').prop('checked')) {
					jQuery('.enter_password').show();
					jQuery('.enter_password input').focus();
					jQuery('#shipping-new-address-form').find('label').removeClass('err');
					jQuery('#shipping-new-address-form').find('.sidetip').children().hide();
					jQuery('.has-account').hide();
					that.loginFadeIn();
					jQuery('#continue_as_guest').hide();
				} else {
					jQuery('#continue_as_guest').show();
					//jQuery('.enter_password').hide();
				}
			} else {
				jQuery('.has-account').hide();
				jQuery('.already_customer').hide();
				jQuery('.create_account').show();
				jQuery('#continue_as_guest').hide();
				jQuery('#login-submit').hide();
				jQuery('#send-new-password').hide();
				jQuery('.enter_password label').text( Translator.translate( 'Create a password' ) );
				that.loginFadeOut();
				if(jQuery('.create_account input').prop('checked')) {
					jQuery('.enter_password').show();
					//jQuery('.enter_password input').focus();
					jQuery('#shipping-new-address-form').find('label').removeClass('err');
					jQuery('#shipping-new-address-form').find('.sidetip').children().hide();
				} else {
					jQuery('.enter_password').hide();
				}
			}
		}, 'json');
	},

	loginFadeIn: function() {
		// fade out & disable fields
		var form = jQuery('#shipping-new-address-form');
		var shippingMethodForm = jQuery('#co-shipping-method-form');
		form.addClass('faded');
		shippingMethodForm.addClass('faded');
		form.find('input,select').removeClass('error').prop('disabled',true);
		shippingMethodForm.find('input,select').removeClass('error').prop('disabled',true);
		jQuery('#shipping-new-address-form label').removeClass('err');
		jQuery('#co-shipping-method-form label').removeClass('err');
		form.find('.sidetip p').hide();
		shippingMethodForm.find('.sidetip p').hide();
		jQuery('#shipping-method-buttons-container input.button').addClass('inactive').attr('disabled', 'disabled');
	},

	loginFadeOut: function() {
		// fade in & enable fields
		var form = jQuery('#shipping-new-address-form');
		var shippingMethodForm = jQuery('#co-shipping-method-form');
		form.removeClass('faded');
		shippingMethodForm.removeClass('faded');
		form.find('input,select').prop('disabled',false);
		shippingMethodForm.find('input,select').prop('disabled',false);
		jQuery('#shipping-method-buttons-container input.button').removeClass('inactive').removeAttr('disabled');
	},

	sendNewPassword: function(email) {
		jQuery.post(this.options.sendNewPasswordUrl, {
			email: email
		}, function(data) {
			checkout.handleError(data.message, data.title);
		}, 'json');
	},

	login: function() {
		if(this.options.isVirtual)
			var validator = billing.getValidator();
		else
			var validator = shipping.getValidator();
		if (validator.element('#login-password') && validator.element('#login-email')) {
			jQuery.post(this.options.loginUrl, {
				username: jQuery('#login-email').val(),
				password: jQuery('#login-password').val()
			}, function(data) {
				if (!data.error) {
					window.location.reload();
				} else {
					checkout.handleError(data.message, data.title);
				}
			}, 'json');
		}
	},

	getCountryLabel: function(id) {
		for (var i in countryOptions) {
			if (countryOptions[i].value == id) {
				return countryOptions[i].label;
			}
		}
		throw "There is no country by id: " + id;
	},

	isCountryAllowed: function(label) {
		for (var i in countryOptions) {
			if (countryOptions[i].label == label) {
				return true;
			}
		}
		return false;
	},

	postcodeAddress: function(el) {
		var id = el.id;
		var type = id.substr(0, id.indexOf(':'));
		var country = jQuery(jq(type + ':country_label')).val(); // send the country so that we return pincode details specific to the country passed, if multiples are available
		if(!checkout.options.disable_postcode_autocomplete){
			jQuery('#' + type + '-postcode-please-wait').fadeIn();
		}
		if(this.postcodeAddressXHR && 4 !== this.postcodeAddressXHR.readystate) {
			this.postcodeAddressXHR.abort();
		}
		this.postcodeAddressXHR = jQuery.get(this.options.postcodeAddressUrl, {
			postcode: el.value,
			country: country
		}, function(resp) {
			// Hide ajax loader
			if(!checkout.options.disable_postcode_autocomplete){
				jQuery('#' + type + '-postcode-please-wait').fadeOut();
			}

			if (resp.error || typeof resp.data === 'undefined') {
				return;
			}

			var data = resp.data;
			if ( !data ) {
				// there wasn't any error but we still have no data returned, let's clear the fields to force user input
				jQuery(jq(type + ':city')).val('');
				jQuery(jq(type + ':region')).val('');
				jQuery(jq(type + ':region_id')).val('');
				jQuery(jq(type + ':country_label')).val('');
				jQuery(jq(type + ':country_id')).val('');
				window[type + "RegionUpdater"]["update"]();
				//checkout.setPhoneMasking(type);
			}

			if (data.city) {
				// Hide IE placeholder for city
				if (checkout.placeholderSupport === false) jQuery(jq(type + ':city')).parent().find('.ieplaceholder').hide();

				jQuery(jq(type + ':city')).val(data.city).addClass('shine').delay(1000).queue(function(next){
					jQuery(jq(type + ':city')).removeClass('shine');
					jQuery(jQuery(jq(type + ':city'))).closest('form').validate(validatorSettings).element(jQuery(jq(type + ':city')));
					next();
				}).change();
			}
			if (data.state) {
				jQuery(jq(type + ':region')).val(data.state).addClass('shine').delay(1000).queue(function(next){
					jQuery(jq(type + ':region')).removeClass('shine');
					jQuery(jQuery(jq(type + ':region'))).closest('form').validate(validatorSettings).element(jQuery(jq(type + ':region')));
					next();
				});
				jQuery(jq(type + ':region_id')).addClass('shine').delay(1000).queue(function(next){
					jQuery(jq(type + ':region_id')).removeClass('shine');
					jQuery(jQuery(jq(type + ':region_id'))).closest('form').validate(validatorSettings).element(jQuery(jq(type + ':region_id')));
					next();
				}).change();
			}
			if (data.country) {
				jQuery(jq(type + ':country_label')).val(data.country).addClass('shine').delay(1000).queue(function(next){
					jQuery(jq(type + ':country_label')).removeClass('shine');
					jQuery(jQuery(jq(type + ':country_label'))).closest('form').validate(validatorSettings).element(jQuery(jq(type + ':country_label')));
					next();
				}).change();
				jQuery(jq(type + ':country_id')).val(data.country).addClass('shine').delay(1000).queue(function(next){
					jQuery(jq(type + ':country_id')).removeClass('shine');
					next();
				});
			}
			if (data.country_id) jQuery(jq(type + ':country_id')).val(data.country_id).change();
			jQuery(jQuery(jq(type + ':country_id'))).closest('form').validate(validatorSettings).element(jQuery(jq(type + ':country_id')));
			window[type + "RegionUpdater"]["update"]();
			// TODO: validate city, state, country

			// do it once we have new city/state/country fields filled
			//checkout.setPhoneMasking(type);
			if(!checkout.options.separate_shipping_method_step)
			{	// save shipping info when postcode ajax fills location
				if (type == 'shipping')
					shipping.save(0);
			}
		}, 'json');
	},

	cancelCoupon: function() {
		jQuery('#remove-coupone').val(1);
		jQuery('#coupon-cancel-link').hide();
		jQuery('#coupon-cancel-please-wait').show(400);
		jQuery.get(this.options.couponPostUrl, jQuery('#discount-coupon-form').serialize(), function(response) {
			if (response.error) {
				jQuery('#coupon-cancel-please-wait').hide();
				jQuery('#coupon-cancel-link').show(400);
				return checkout.handleError(response.message);
			} else {
				jQuery('#coupon-cancel-please-wait').hide();
				jQuery('#coupon-cancel-link').hide(400);
				jQuery('#coupon-apply-link').show(400);
			}

			checkout.reloadProgressBlock(response.update_section.name);
			if (response.update_section.html) {
				checkout.setStepResponse(response);
			}
			jQuery('#checkout-review-table > tfoot > tr:last').delay(500).queue(function(next){
				jQuery(this).addClass('shine');
				next();
			}).delay(1000).queue(function(next){
				jQuery(this).removeClass('shine');
				next();
			});
		}, 'json');
	},

	applyCoupon: function() {
		jQuery('#remove-coupone').val(0);
		if (!jQuery.trim(jQuery('#coupon_code').val()))
			return checkout.handleError("Please enter a valid promo code");
		jQuery('#coupon_apply_button').hide();
		jQuery('#coupon-apply-please-wait').show(400);

		jQuery.get(this.options.couponPostUrl, jQuery('#discount-coupon-form').serialize(), function(response) {
			if (response.error) {
				jQuery('#coupon-apply-please-wait').hide();
				jQuery('#coupon_apply_button').show(400);
				return checkout.handleError(response.error);
			} else {
				jQuery('#coupon-apply-please-wait').hide();
				jQuery('#coupon_apply_button').show(400);
				jQuery('#coupon-apply-link').hide();
				jQuery('#discount-coupon-form-wrapper').hide();
				jQuery('#coupon-cancel-link').show(400);
			}

			checkout.reloadProgressBlock(response.update_section.name);
			if (response.update_section.html) {
				checkout.setStepResponse(response);
			}
			jQuery('#checkout-review-table > tfoot > tr:last, #checkout-review-table > tfoot > tr:eq(1)').delay(200).queue(function(next){
				jQuery(this).addClass('shine');
				next();
			}).delay(1000).queue(function(next){
				jQuery(this).removeClass('shine');
				next();
			});
		}, 'json');
	},

	setPhoneMasking: function(form_type) {
		var ph = jQuery(jq(form_type+':telephone')).val(),
			new_ph = '';
			country = jQuery(jq(form_type+':country_id')).val(),
			city = '',
			state = '',
			mask = '';

		ph = ph.replace(/\D/g,''); // reject everything other than numbers

		switch(country) {
			case 'US':
			case 'CA':
				mask = "(999) 999-9999";
				break;
			case 'ES':
				mask = "(999) 999 99 99";
				break;
			case 'BR':
				mask = "(99) 9999-9999?9"; // (xx) xxxx-xxxx OR (11) 9xxxx-xxxx
				break;
			case 'DE':
				mask = "(9999) 999999";
				break;
			case 'FR':
				mask = "99 99 99 99 99";
				break;
			case 'AU':
				mask = "99 9999 9999";
				break;
			case 'RU':
				mask = "(9 9999) 99-99-99";
				break;
			case 'GB':
			case 'IT':
			default:
				jQuery(jq(form_type+':telephone')).unmask();
				jQuery(jq(form_type+':telephone')).val(ph);
		}
		if (mask != '') {
			jQuery(jq(form_type+':telephone')).mask(mask);
			// now replace 9 in mask with the actual numbers & others with underscore
			for (var digit=0; digit<mask.length; digit++) {
				if (mask[digit] == '9') {
					if ( ph.length ) {
						new_ph += ph.charAt(0);
						ph = ph.slice(1); // remove the first digit too
					} else {
						new_ph += '_';
					}
				} else {
					new_ph += mask[digit];
				}
			}
			jQuery(jq(form_type+':telephone')).val(new_ph);
		}
	},

	ajaxFailure: function() {}, // Braintree's payment extension support

	prepareAddressForm: function(addressType) {
		// placeholder attribute support for IE
		if ( checkout.placeholderSupport === false ) {

			jQuery('.input-text').each(function() {
				var field = jQuery(this);
				// Show placeholders initially on page load if the field is empty
				// Counter-attack timing issue
				setTimeout(function(){
					if (jQuery.trim(field.val()) == '' && field.attr('id') != 'shipping:region' && field.attr('id') != 'billing:region')
						field.parent().find('.ieplaceholder').show();
				},100);

				// Make sure placeholder is hidden when field is in focus
				field.live('focus', function() {
					field.parent().find('.ieplaceholder').hide();
				});

				// Upon blur, show placeholder only when the field is empty
				field.live('blur', function() {
					// Counter-attack timing issue
					setTimeout( function(){
						if ( jQuery.trim( field.val() ) == '' )
							field.parent().find('.ieplaceholder').show();
					},100);
				});
			});

			jQuery('.ieplaceholder').click(function(){
				jQuery(this).hide();
				jQuery(this).parent().find('input').focus();
			});
		}

		jQuery('.input-text, .validate-select').each(function() {
			jQuery(this).live('focus', function() {
				//show tip if we are already not on success for that field
				if (!jQuery(this).parents('.fields').find('.sidetip .good').is(':visible')) {
					jQuery(this).parents('.fields').find('.sidetip p').hide();
					jQuery(this).parents('.fields').find('.sidetip .tip').show();
				}
			});
			jQuery(this).live('blur', function() {
				jQuery(this).closest('form').validate(validatorSettings).element(this);
			});
		});

		jQuery(jq('shipping:country_label') + ',' + jq('billing:country_label')).autocomplete({
		source: function(request, response) {
			var matches = jQuery.map(countryOptions, function(tag) {
				if (tag.label.toUpperCase().indexOf(request.term.toUpperCase()) === 0) {
					return tag;
				}
			});
			response(matches);
		},
		select: function(event, ui) {
			event.target.value = ui.item.label;
			jQuery(event.target).next().val(ui.item.value);

			if(this.id === 'shipping:country_label'){
				shippingRegionUpdater.update();
				// region updater changes the state field amongst selectbox & text-input, also if the state field is required or not depends on the selected country
				// so let us remove the error class if it is present, if there is an error we will still trigger it later
				jQuery(jq('shipping:region_id')).closest('div.state').find('label').removeClass('err');
				jQuery(jq('shipping:region_id')).removeClass('error');
				jQuery(jq('shipping:region')).removeClass('error');
			}else if(this.id === 'billing:country_label'){
				billingRegionUpdater.update();
				// region updater changes the state field amongst selectbox & text-input also if the state field is required or not depends on the selected country
				// so let us remove the error class if it is present, if there is an error we will still trigger it later
				jQuery(jq('billing:region_id')).closest('div.state').find('label').removeClass('err');
				jQuery(jq('billing:region_id')).removeClass('error');
				jQuery(jq('billing:region')).removeClass('error');
			}
			// trigger change event manually since input[type=hidden] doesn't fire change event when its value changes
			jQuery(jq(event.target.id.replace('label','id'))).trigger('change');

			return false;
		},
		change: function(event, ui) {
			if (ui.item == null) {
				jQuery(event.target).next().val('');
				if (event.target.value != '' && checkout.isCountryAllowed(event.target.value) == false) {
					checkout.handleError(Translator.translate('Country is not allowed. Please select one from the list.'));
					event.target.value = '';
				}
			}
		},
		autoFocus: true,
		delay: 0
	});

	if(addressType === 'shipping'){
		// save shipping info on load, since we might have an address loaded already
		if(!checkout.options.isVirtual) {
			shipping.supressErrorsModal = 1;
			shipping.save(0);
		}

		// trigger the saving of shipping step when we have the change event on shipping form, which will be bubbled up by the corresponding input fields & select boxes
		jQuery('#co-shipping-form').change(function(event){
                        shipping.supressErrorsModal = 1;
			if(jQuery(event.target).is(jQuery(jq('shipping:telephone'))) && !(this.xhr && this.xhr.readystate != 4) && !jQuery('#shipping-address-select').val())
				shipping.reloadShippingMethods = false;
			else
				shipping.reloadShippingMethods = true;
			shipping.save(0);
		});
	}

	if(!checkout.options.disable_postcode_autocomplete)
	{
		(function() {
			var timeoutID;
			var prevValue;
			jQuery(jq('shipping:postcode') + ',' + jq('billing:postcode')).bind('keyup',function(e) {
				var value = String(jQuery(this).val());

				var that = this;

				// if we already have the keyup event queued, remove it's callback
				if(timeoutID) {
					clearTimeout(timeoutID);
				}

				if(prevValue === value) {
					// if the previously loaded value is same as the current value, then return. Useful for cases where event is triggered by keys such as Ctrl, Shift, Tab etc.
					return;
				}

				if (value.length >= 4) {
					// add the postcode lookup to the queue
					timeoutID = setTimeout(function() {

						if ( e.originalEvent === undefined ) {
							// triggered programmatically
						} else {
							// clicked by the user
						}

						checkout.postcodeAddress(that);

						prevValue = value;
					}, 500);
				}
			});
		})();
		// reload address from postcode on pageload
		jQuery(jq(addressType+':postcode')).trigger('keyup');
	}

	jQuery(jq('shipping:postcode') + ',' + jq('billing:postcode')).focus(function(){
		jQuery(this).attr('pattern', '\\d*');
		jQuery(this).attr('novalidate','novalidate');
		var v = jQuery(this.id.substr(0,this.id.indexOf(':')) + ':country_id').val();
		if ('AR' == v || 'CA' == v || 'NL' == v || 'UK' == v){
			jQuery(this).removeAttr('pattern');
			jQuery(this).removeAttr('novalidate');
		}
	});

	// submission of forms of a particular step using enter/return key
	jQuery(document).keypress(function(event) {
		if (event.which == 13) {
			// just hide modal box if it's modal box is active
			if ( jQuery('#error-message').is(':visible') ) {
				jQuery('#error-message').modal('hide');
			} else if (jQuery('input').not('#login-password').is(':focus')) { // any input element except the password field
				jQuery('#opc-' + checkout.activeSection).find('footer button').trigger('click');
			}
		}
	});

	jQuery(document).ajaxError(function(event, response) {
		if (response.readyState === 0 && response.status === 0) {
			// do nothing, this happens in cases like when we quickly reload, and browser cancels the ajax request
		} else if (response.readyState === 4 && response.status === 403) {
			window.location = checkout.options.failureUrl;
		} else if (response.status === 500) {
			checkout.handleError('Server 500 error!');
		} else if (response.status !== 200) {
			checkout.handleError('Server ' + response.status + ' error! (Ready State: ' + response.readyState + ')');
		}
	});
	}
});

/**
 * Shipping
 * @param options
 */
Shipping = function(options) {
	this.init(options);
}
jQuery.extend(Shipping.prototype, {

	options: null,
	selectedShippingMethod: null,

	init: function(options) {
		this.options = options;

		// change url hash, doesn't really need to be inside init, but semantically its correct to have it here
		if (window.history && 'replaceState' in window.history)
			window.history.replaceState({ step: 'shipping'},'','#shipping');
	},

	newAddress: function(isNew) {
		jQuery('.box-address').removeClass('active');
		if (isNew) {
			jQuery(this.options.form).find('input[type=text], textarea').val('');
			jQuery(jq('shipping:region_id')).val('').hide();
			jQuery(jq('shipping:region')).show();
			if (checkout.placeholderSupport === false)
				jQuery('#co-shipping-form .ieplaceholder').show();
			this.resetSelectedAddress();
			jQuery('#shipping-new-address-form').show(400);
			if(!checkout.options.disable_postcode_autocomplete){
				jQuery(jq('shipping:postcode')).trigger('keyup');
				jQuery(jq('shipping-postcode-please-wait')).hide();
			}
			jQuery(jq('shipping:telephone')).closest('li').find('.sidetip').children().hide();
			jQuery('.box-address-new').addClass('active');
		} else {
			currentAddress = jQuery('input[name="shipping_address_id"]:checked').val();
			jQuery('#address-please-wait').fadeIn();
			jQuery.get(this.options.getAddressUrl, {
				address: currentAddress
			}, function(data) {
				jQuery('#address-please-wait').fadeOut();
				shipping.fillForm(data);
				billing.fillForm(data);
			}, 'json');
			jQuery('#shipping-new-address-form').hide(400);
			jQuery('#shipping_address_label_' + currentAddress).addClass('active');
		}
	},

	fillForm: function(elementValues) {
		// hide all placeholders
		if (checkout.placeholderSupport === false) jQuery('.ieplaceholder').hide();

		arrElements = jQuery(':input', this.options.form).not('input[name="shipping_address_id"]').not('input[name="shipping[address_id]"]').not('input[name="shipping[save_in_address_book]"]');

		for (var elemIndex in arrElements) {
			if (arrElements[elemIndex].id) {
				var fieldName = arrElements[elemIndex].id.replace(/^shipping:/, '');
				arrElements[elemIndex].value = elementValues[fieldName] ? elementValues[fieldName] : '';

				if (fieldName.indexOf('country') == -1 && fieldName.indexOf('region') == -1 && arrElements[elemIndex].value == '' && checkout.placeholderSupport === false) {
					jQuery(arrElements[elemIndex]).parent().find('.ieplaceholder').show();
				}
				if (fieldName == 'country_label') {
					arrElements[elemIndex].value = checkout.getCountryLabel(elementValues['country_id']);
				}
			}
		}

		// trigger blur event to format input as per masking
		jQuery(jq('shipping:telephone')).trigger('blur');

		shippingRegionUpdater.update();

		jQuery(this.options.form).valid();
		if(!checkout.options.separate_shipping_method_step)
		{
			// save shipping info after populating form
			this.save();
		}
	},

	_beforeSave: function() {
		// This can be overriden
	},

	save: function(validateFrom) {
		validateFrom = (typeof validateFrom === "undefined") ? 1 : validateFrom;
		if (checkout.loadWaiting != false && this.xhr && this.xhr.readystate != 4) {
			this.xhr.abort();
		}
		checkout.collectErrorsFlag = 1;
		var validator = jQuery(this.options.form).validate(validatorSettings);
		this._beforeSave();
		var isEmailFieldEmpty = typeof jQuery('#login-email').val() !== 'undefined'? jQuery('#login-email').val() : true;
		if(validateFrom === 1){
			if (validator.form()) {
				this.sendAjaxRequest();
			} else {
				var errors = checkout.errorsCollection.join('') ? checkout.errorsCollection.join('') : '<li>Incorrect Information</li>';
				checkout.handleError('<ul>'+errors+'</ul>','Ops! Ocorreu um erro...');
				checkout.errorsCollection = []; // empty it
				shipping.triggerShippingMethodSave = false;
				shipping.triggerShippingSave = 0;
			}
		}else if((isEmailFieldEmpty
			&& jQuery(jq('shipping:fullname')).val()
			&& jQuery(jq('shipping:street1')).val()
			&& jQuery(jq('shipping:postcode')).val()
			&& jQuery(jq('shipping:city')).val()
			&& jQuery(jq('shipping:country_id')).val()) || jQuery('#shipping-address-select').val()){
				this.sendAjaxRequest();
		}
		checkout.collectErrorsFlag = 0;
	},

	sendAjaxRequest: function() {
		if(checkout.options.separate_shipping_method_step){
			jQuery('#shipping-buttons-container input.button').addClass('inactive').attr('disabled', 'disabled').val(shipping.options.savingMessage);
		}
		if (shipping.reloadShippingMethods) {
			if(!checkout.options.separate_shipping_method_step || shipping.triggerShippingSave){
				checkout.setLoadWaiting(shipping.triggerShippingMethodSave ? 'shipping-method' : 'shipping');
			}
			jQuery('#shipping-method-buttons-container input.button').addClass('inactive').attr('disabled', 'disabled').val(shipping.options.loadingMessage);
		}
		var data = jQuery(this.options.form).serialize();
		if(!jQuery(jq('shipping:street1')).val() || !jQuery(jq('shipping:fullname')).val() || !jQuery(jq('shipping:telephone')).val().replace(/\D/g,'') || !jQuery(jq('shipping:telephone')).closest('form').validate(validatorSettings).element(jQuery(jq('shipping:telephone'))))
			data = data.replace(/&?shipping%5Btelephone%5D=([^&]$|[^&]*)/i, "");
		this.xhr = jQuery.post(jQuery(this.options.form).attr('action'), data, this.nextStep, 'json');
	},

	nextStep: function(response) {
		checkout.setLoadWaiting(false);
		jQuery('#shipping-method-buttons-container input.button').removeClass('inactive').removeAttr('disabled').val(shipping.options.oldLoadingMessage.substring(0,19)+'→');
		if(checkout.options.separate_shipping_method_step){
			jQuery('#shipping-buttons-container input.button').removeClass('inactive').removeAttr('disabled').val(shipping.options.oldSavingMessage.substring(0,28)+'→');
		}
		if (shipping.supressErrorsModal) {
			shipping.collectedErrors = response.message;
		} else {
			if (response.error) {
				if (window.shippingRegionUpdater) {
					shippingRegionUpdater.regionTextEl.value = shippingRegionUpdater.regionSelectEl.value;
					shippingRegionUpdater.update();
				}
				shipping.triggerShippingMethodSave = false;
				shipping.triggerShippingSave = 0;
				return checkout.handleError(response.message);
			}
			shipping.collectedErrors = false;
		}
		if (checkout.options.separate_shipping_method_step) {
			if (shipping.triggerShippingSave) {
				shipping.reloadShippingMethods = true;
			} else {
				shipping.reloadShippingMethods = false;
			}
			shipping.triggerShippingSave = 0;
		}
		if (shipping.reloadShippingMethods)
			checkout.setStepResponse(response);
		shipping.reloadShippingMethods = true;

		// set the first shipping method as default if none is selected
		if (jQuery('#checkout-shipping-method-load input[type=radio]:checked').length == 0) {
			jQuery('#checkout-shipping-method-load input[type=radio]').eq(0).attr('checked','checked');
			jQuery('#checkout-shipping-method-load input[type=radio]:checked').click();
		}

		checkout.saveShippingMethodIndex();

		// change url hash
		if (window.history && 'replaceState' in window.history)
			window.history.pushState({ step: 'shipping_method'},'','#shipping_method');

		// if the shipping method was triggered previously, let's trigger it again now, this will show users error messages in case the shipping method was invalid
		if(shipping.triggerShippingMethodSave) {
			shippingMethod.save();
		}
		shipping.triggerShippingMethodSave = false;
	},

	getValidator: function() {
		return jQuery(this.options.form).validate(validatorSettings);
	},

	resetSelectedAddress: function() {
		var selectElement = $('shipping-address-select')
		if (selectElement) {
			selectElement.value = '';
		}
	},
});

/**
 * ShippingMethod
 * @param options
 */
ShippingMethod = function(options) {
	this.init(options);
}
jQuery.extend(ShippingMethod.prototype, {

	options: null,

	init: function(options) {
		this.options = options;
	},

	_beforeSave: function() {
		// This can be overriden
	},

	save: function() {
		shipping.supressErrorsModal = 0;
		shipping.triggerShippingMethodSave = true;
		if (checkout.loadWaiting != false) return;
		checkout.setLoadWaiting('shipping-method');
		if (!checkout.options.separate_shipping_method_step) {
			checkout.collectErrorsFlag = 1;
			var validator = jQuery(shipping.options.form).validate(validatorSettings);
			if (!validator.form()) {
				var errors = checkout.errorsCollection.join('') ? checkout.errorsCollection.join('') : '<li>Informa&ccedil;&otilde;es Incorretas</li>';
				checkout.handleError('<ul>'+errors+'</ul>','Ops! Ocorreu um problema com:');
				checkout.errorsCollection = []; // empty it
				checkout.setLoadWaiting(false);
				shipping.triggerShippingMethodSave = false;
				return false;
			}
			checkout.collectErrorsFlag = 0;
		}
		if (shipping.collectedErrors){
			return shipping.save();
		}
		this._beforeSave();
		shipping.triggerShippingMethodSave = false;
		jQuery.post(jQuery(this.options.form).attr('action'), jQuery(this.options.form).serialize()+ "&"+jQuery('#co-shipping-form').serialize(), this.nextStep, 'json');

		// Check if we need to highlight shipping cost
		checkout.highlightShippingCost = ( checkout.getSavedShippingMethodIndex() != checkout.getCurrentShippingMethodIndex() ) ? true : false;
		checkout.saveShippingMethodIndex();
	},

	nextStep: function(response) {
		checkout.setLoadWaiting(false);
		if (shipping.collectedErrors){
			return checkout.handleError(shipping.collectedErrors);
		}
		if (response.error) {
			return checkout.handleError(response.message);
		}
		checkout.setStepResponse(response);

		// hide payment label if only one payment method is enabled
		if (jQuery('#checkout-payment-method-load dt').length == 1) {
			jQuery('#checkout-payment-method-load dt').hide();
			jQuery('#checkout-payment-method-load dt.free').show();
		}

		// change url hash
		if (window.history && 'replaceState' in window.history)
			window.history.pushState({ step: 'payment'},'','#payment');
	}
});

/**
 * Payment
 * @param options
 */
Payment = function(options) {
	this.init(options);
}
jQuery.extend(Payment.prototype, {

	options: null,

	init: function(options) {
		this.options = options;
		// Braintree's payment extension support
		var p = this;
		setTimeout(function() {
			p.form = jQuery(p.options.form);
		}, 1000);
	},

	_beforeSave: function() {
		// This can be overriden
	},

	save: function() {
		checkout.collectErrorsFlag = 1;
		var validator = jQuery(this.options.form).validate(validatorSettings);
		if (checkout.loadWaiting != false)
			return;
		this._beforeSave();
		if (validator.form()) {
			checkout.setLoadWaiting('payment');
			jQuery(this.options.form).append('<div id="temporary_password_div" style="display:none"><input type="hidden" name="password" value="'+jQuery('#login-password').val()+'"</div>');
			jQuery.post(jQuery(this.options.form).attr('action'), jQuery(this.options.form).serialize(), this.nextStep, 'json');
			jQuery('#temporary_password_div').remove();
		} else {
			var errors = checkout.errorsCollection.join('') ? checkout.errorsCollection.join('') : '<li>Informa&ccedil;&otilde;es Incorretas</li>';
			checkout.handleError('<ul>'+errors+'</ul>','Ops! Ocorreu um problema com:');
			checkout.errorsCollection = []; // empty it
		}
		checkout.collectErrorsFlag = 0;
	},

	nextStep: function(response) {
		checkout.setLoadWaiting(false);
		if (response.error) {
			if (response.update_section !== undefined) {
				checkout.handleError(response.error);
			} else {
				return checkout.handleError(response.error);
			}
		}
		checkout.setStepResponse(response);

		// change url hash
		if (window.history && 'replaceState' in window.history)
			window.history.pushState({ step: 'review'},'','#review');

		// set focus to "Place order" button so that enter / return key can place orders too
		jQuery('#review-buttons-container button').focus();
	},

	switchMethod: function(method) {
		if (this.currentMethod && $('payment_form_' + this.currentMethod)) {
			this.changeVisible(this.currentMethod, true);
			$('payment_form_' + this.currentMethod).fire('payment-method:switched-off', {
				method_code: this.currentMethod
			});
		}
		if ($('payment_form_' + method)) {
			this.changeVisible(method, false);
			$('payment_form_' + method).fire('payment-method:switched', {
				method_code: method
			});
		} else {
			//Event fix for payment methods without form like "Check / Money order"
			document.body.fire('payment-method:switched', {
				method_code: method
			});
		}
		this.currentMethod = method;

		// add active class to active payment method
		jQuery('#checkout-payment-method-load .sp-methods dt').removeClass('active');
		jQuery('#checkout-payment-method-load .sp-methods dd').removeClass('active');
		jQuery('#checkout-payment-method-load .sp-methods dt.'+method).addClass('active');
		jQuery('#checkout-payment-method-load .sp-methods dd.'+method).addClass('active');
	},

	changeVisible: function(method, mode) {
		var block = '#payment_form_' + method;
		if (mode) {
			jQuery([block, block + '_before', block + '_after'].join(',')).hide().find(':input').attr('disabled', 'disabled');
		} else {
			jQuery([block, block + '_before', block + '_after'].join(',')).show().find(':input').removeAttr('disabled');
		}
	}
});

/**
 * Billing
 * @param options
 */
Billing = function(options) {
	this.init(options);
}
jQuery.extend(Billing.prototype, {

	options : null,
	sameAsBillingChecked: 1,

	init : function(options) {
		this.options = options;
	},

	newAddress: function(isNew) {
		if (isNew) {
			jQuery(this.options.form).find("input[type=text]:not('ul.payment-form *'), textarea:not('ul.payment-form *')").val('');
			jQuery(jq('billing:region_id')).val('').hide();
			jQuery(jq('billing:region')).show();
			if (checkout.placeholderSupport === false)
				jQuery('#co-payment-form .ieplaceholder').show();
			jQuery('#billing-new-address-form').show(400);
			if(!checkout.options.disable_postcode_autocomplete){
				jQuery(jq('billing:postcode')).trigger('keyup');
				jQuery(jq('billing-postcode-please-wait')).hide();
			}
			checkout.prepareAddressForm('billing');
		} else {
			jQuery('#billing-address-please-wait').fadeIn();
			jQuery.get(this.options.getAddressUrl, {
				address: jQuery('select[name=billing_address_id]').val()
			}, function(data) {
				jQuery('#billing-address-please-wait').fadeOut();
				billing.fillForm(data);
			}, 'json');
			jQuery('#billing-new-address-form').hide(400);
		}
	},

	fillForm: function(elementValues) {
		// hide all placeholders
		if (checkout.placeholderSupport === false) jQuery('.ieplaceholder').hide();

		arrElements = jQuery(':input', this.options.form)
		.not('select[name="billing_address_id"]')
		.not('input[name="billing[address_id]"]');

		for (var elemIndex in arrElements) {
			if (arrElements[elemIndex].id) {
				var fieldName = arrElements[elemIndex].id.replace(/^billing:/, '');
				arrElements[elemIndex].value = elementValues[fieldName] ? elementValues[fieldName] : '';
				if (arrElements[elemIndex].value == '' && checkout.placeholderSupport === false) {
					jQuery(arrElements[elemIndex]).parent().find('.ieplaceholder').show();
				}

				if (fieldName.indexOf('country') == -1 && fieldName.indexOf('region') == -1 && arrElements[elemIndex].value == '' && checkout.placeholderSupport === false) {
					jQuery(arrElements[elemIndex]).parent().find('.ieplaceholder').show();
				}
				if (fieldName == 'country_label') {
					arrElements[elemIndex].value = checkout.getCountryLabel(elementValues['country_id']);
				}
			}
		}

		// trigger blur event to format input as per masking
		jQuery(jq('billing:telephone')).trigger('blur');

		billingRegionUpdater.update();

		jQuery(this.options.form).valid();
	},

	_beforeSave: function() {
		// This can be overriden
	},

	save : function() {
		var validator = this.getValidator();
		if (checkout.loadWaiting!=false) return;
		this._beforeSave();
		if(validator.form()) {
			checkout.setLoadWaiting('billing');
			jQuery.post(jQuery(this.options.form).attr('action'), jQuery(this.options.form).serialize(), this.nextStep, 'json');
		}
	},

	nextStep: function(response) {
		checkout.setLoadWaiting(false);

		if (window.billingRegionUpdater) {
			billingRegionUpdater.update();
		}

		if (response.error) {
			return checkout.handleError(response.message);
		}

		checkout.setStepResponse(response);

		// hide payment label if only one payment method is enabled
		if (jQuery('#checkout-payment-method-load dt').length == 1)
			jQuery('#checkout-payment-method-load dt').hide();

		// change url hash
		if (window.history && 'replaceState' in window.history)
			window.history.pushState({ step: 'payment'},'','#payment');
	},

	getValidator: function() {
		return jQuery(this.options.form).validate(validatorSettings);
	},

	addressSameAsBilling: function() {
		jQuery('#billing-address-previously-saved').toggle(400);
		if (jQuery('#billing-address-dropdown').length && jQuery('#billing-address-dropdown').is(":hidden")) {
			jQuery('#billing-address-dropdown').toggle(400);
			if (jQuery('#billing-address-select').val() === '') {
				jQuery('#billing-new-address-form').show(400);
			}
		} else if (jQuery('#billing-address-dropdown').length) {
			jQuery('#billing-address-dropdown').toggle(400);
			jQuery('#billing-new-address-form').hide(400);
		} else {
			jQuery('#billing-new-address-form').toggle(400);
			if(!checkout.options.disable_postcode_autocomplete){
				jQuery('#billing-postcode-please-wait').hide();
			}
		}
		checkout.prepareAddressForm('billing');
		if(!jQuery('.dflt-adrs-labl input[type=checkbox][name="billing[same_as_shipping]"]:checked').length){
			billing.sameAsBillingChecked = 0;
		}else{
			billing.sameAsBillingChecked = 1;
		}
	},
});

/**
 * Review
 * @param options
 */
Review = function(options) {
	this.init(options);
}
jQuery.extend(Review.prototype, {

	options: null,

	init: function(options) {
		this.options = options;
		// Braintree's payment extension support
		if('sagepaypaypal' === payment.currentMethod) {
			var p = this;
			setTimeout(function() {
				p.options.saveUrl = p.saveUrl;
			}, 1000);
		} else {
			var p = this;
			setTimeout(function() {
				p.saveUrl = p.options.saveUrl;
			}, 1000);
		}
	},

	_beforeSave: function() {
		// This can be overriden
	},

	save: function() {
		if (checkout.loadWaiting != false) return;
		this._beforeSave();
		checkout.setLoadWaiting('review');

		//see if the sagepay payment method has been selected & call their function if it is, it also does the ajax request & call nextStep
		if('sagepaydirectpro' == payment.currentMethod || 'sagepayform' == payment.currentMethod) {
			return new Ajax.Request(SuiteConfig.getConfig('global', 'sgps_saveorder_url'),{
				method:"post",
				parameters: Form.serialize($$(payment.options.form)[0]),
				onSuccess:function(f){
					SageServer.reviewSave(f);
				}.bind(SageServer)
			});
		}

		if (payment.currentMethod + 'Save' in this) {
			var params = this[payment.currentMethod + 'Save']();
		}
		else
		{
			var params = jQuery(payment.options.form).serialize();
		}

		var disabled = jQuery(this.options.agreementsForm).find(':input:disabled').removeAttr('disabled');
		params += '&' + jQuery(this.options.agreementsForm).serialize();
		params += '&' + jQuery(this.options.giftMessageForm).serialize();
		if (jQuery(this.options.newsletterForm).length)
			params += '&' + jQuery(this.options.newsletterForm).serialize();
		disabled.attr('disabled', 'disabled');

		params.save = true;
		jQuery.post(this.options.saveUrl, params, this.nextStep, 'json');
	},

	nextStep: function(response) {
		//sagepay sends in the raw xhr object over to this function but we send in a json object, here we check for raw xhr object & try to convert it to json
		try {
			if(4 == response.readyState && 200 == response.status)
				response = jQuery.parseJSON(response.responseText);
		} catch(e) {
			//suppressed the error for all the cases where it's not sagepay
		}

		if (response.redirect) {
			location.href = response.redirect;
			return;
		}
		if (response.success) {
			window.location = review.options.successUrl;
		} else {
			if(response.error_messages){
				checkout.handleError(response.error_messages);
				checkout.setLoadWaiting(false);
			} else {
				alert('Ocorreu algum problema ao finalizar a compra. Por favor, entre em contato e informe sua tentativa.');
			}
		}

		/*if (response.update_section) {
			jQuery('#checkout-' + response.update_section.name + '-load').html(response.update_section.html);
		}*/

		if (response.goto_section) {
			checkout.gotoSection(response.goto_section);
			checkout.reloadProgressBlock();
			checkout.setLoadWaiting(false);
		}
	}
});

/* http://docs.jquery.com/Frequently_Asked_Questions#How_do_I_select_an_element_by_an_ID_that_has_characters_used_in_CSS_notation.3F */

function jq(id) {
	return '#' + id.replace(/(:|\.)/g, '\\$1');
}

function numberOfDigitsPerCountry(country_id) {
	switch(country_id) {
		case 'US':
		case 'CA':
			return 10;//"(999) 999-9999";
		case 'ES':
			return 10;//"(999) 999 99 99";
		case 'BR':
			return [10, 11];//"(99) 9999-9999?9"; // (xx) xxxx-xxxx OR (11) 9xxxx-xxxx
		case 'DE':
			return 10;//"(9999) 999999";
		case 'FR':
			return 10;//"99 99 99 99 99";
		case 'AU':
			return 10;//"99 9999 9999";
		case 'RU':
			return 11;//"(9 9999) 99-99-99";
		case 'GB':
		case 'IT':
		default:
			return 0;
	}
}

/* http://docs.jquery.com/Plugins/Validation */
jQuery.validator.addMethod("santander-only", function(value, element) {
	if (/^(401638|407302|407303|407392|407434|407435|407700|409308|409309|410861|410863|410864|410865|411050|411085|419137|419138|419139|419140|419141|419142|419143|419144|419145|419146|419148|419190|419618|419619|419620|419622|419623|419624|419625|419627|419628|419630|419631|422047|422048|422061|423808|423809|424305|425850|430963|439252|439253|439254|441061|441062|441064|441065|441066|441067|441120|441122|441524|441536|446193|448799|451296|451736|455153|455164|455165|455166|455168|455169|459091|459092|459093|459094|459945|459946|463312|463313|463314|463315|467112|467113|467114|469856|469857|474507|474511|474512|485619|485657|491314|491315|491513|491514|491623|491674|491675|491676|491696|491944|499918|499919|499984|502120|502121|515590|515591|517756|518148|518294|518295|518296|518311|518312|518313|518768|519339|520132|520184|520185|521179|521180|522840|526895|528052|530080|530089|530330|530332|531454|531455|531654|531699|531714|531715|531727|532750|533539|540105|540106|541100|542292|542353|542688|542820|543658|543660|544165|544729|544730|544731|545500|548648|548649|552144|552692|552693|552943|553457|553458|554389|554612|556035)/.test(value)){
        return true;
    } else {
        return false;
    }
}, "Esta loja só aceita cartões Santander");

jQuery.validator.addMethod("portoseguro-only", function(value, element) {
	if (/^(536380|536537|532930|553659|484635|412177|415274|415275|446690|512452|519873|462068)/.test(value)){
        return true;
    } else {
        return false;
    }
}, "Esta loja só aceita cartões Porto Seguro");

jQuery.validator.addMethod("credicard-only", function(value, element) {
	if (/^(403249|400237|403245|400245|400242|403242|400249|400242|400239|403243|400245|400237|403242|400245|400249|403249|535081|520401|535097|539029|544829|535858|535822|549329|549319|535858|539073|535863|539068|549368|520400|534513|534503|510414|535085|539073|536143|539081|549344|539029|530038|544858|535091|539011|539073|539073|549365|535085|541886|544863|534562|400689|535085|464299|400217|464296|544870|539063|544830|539058|535088|544829|403217|534516|539041|464294|439389|549370|544810|549331|549312|554917|549381|403254|549330|544829|549360|549358|544864|549320|539050|549341|439388|400248|417872|534503|549374|544820|549364|539063|549340|549361|539040|530038|539029|400248|539065|549379|400637|539020|549382|544881|535850|549310|535081|539010|549350|549358|464295|549384|539019|549351|400248|539070|417873|544850|544811|544812|539074|539020|539021|544839|539055|539023|464295|535085|549371|539068|456137|539071|549355|544880|544855|539061|539040|400217|549363|544866|544819|403217|544840|535091|549333|467793|520401|400254|539033|549311|549380|539064|549377|539060|539082|544865|439390|544823|544863|544871|544878|539012|539030|544821|534503|539084|539080|549321|539066|544820|549323|544871|544822|539022|539030|464295|400645|539022|539070|539060|544831|549366|400642|544860|439388|539031|544882|439388|539077|544879|544844|400652|544833|539071|544882|403254|544851|544841|400649|539073|539079|539051|539031|539041|539079|549322|549392|539044|400252|530038|539073|544877|403237|539044|544861|534513|535081|544874|400248|539051|417874|439389|539050|464294|400689|464294|544840|539011|534503|539055|539055|539074|544884|544874|400689|539033|464294|536143|464295|400217|439389|535085|464296|539010|549368|403217|400248|464299|535085|400217|400254|549319|535858|539033|464294|535081|539021|539029|403217|400689|549319|464299|539011|544877|539029|544831|403237|544829|539066|534513|544884|544812|544879|403245|535081|549363|400217|539051|544881|539064|539071|535081|539063|549363|535858|400248|535081|539061|536143|544880|417872|544855|535858|549329|439388|539021|539023|539010|400217|539044|549368|534513|549319|544829|544810|400689|534503|403217|530038|544877|539044|549319|552070|539077|544844|535085|544858|544861|544830|520401|539022|554917|539078|417873|400252|539039|549319|544829|539033|539080|549368|549329|520401|535097|544870|544844|464294|544811|520400|539029|539084|539021|544863|536143|439388|456137|464295|417872|417873|554917|544820|400652|539060|464299|549329|539065|539077|403254|535081|539029|539020|539082|535091|464296|400254|544865|439389|539070|530038|539050|464294|439389|544891|464299|535858|439389|544850|544850|539040|544880|464294|400652|534513|544833|541886|544851|400252|464294|539058|535858|439388|539030|539041|539082|544863|400217|423074|423074|524314|524314|439390|439390|534516|534516|423074|524314|435086|435002|511258|423074|524314|439390|534516|423074|524314|435086|435002|511258|534562|541886|530038|535850|539066|539058|534516|439390|464296|535091|544860|539050|539084|524314|423074|535088|535863|539068|549368|535822|539073|534513|439389|554917|456137|536143|435086|435002|456137|435086|534503|439388|539063|549363|464294|535081|527467|539062|539085|544862|520400|520401|539022|539041|539077|544810|544829|544831|544833|544841|544863|544864|544874|544881|539011|539012|539021|539031|539040|539051|539060|539064|539070|539074|549319|549329|549341|549358|552070|535858|464299|539029|535085|403217|403254|400217|400248|400689|467793|464295|535097|534447|544828|400225|403225|552128|527496|452407|521043|548984|539028|539039|527533|539083|423153)/.test(value)){
        return true;
    } else {
        return false;
    }
}, "Esta loja só aceita cartões Credicard");

jQuery.validator.addMethod("itaucard-only", function(value, element) {
	if (/^(543960|459455|544507|459457|544169|459458|544507|459457|527616|459454|553665|482447|490172|490172|549167|490172|549167|549167|490172|403247|544859|490172|403247|514868|490172|514868|514868|553636|477176|552072|515640|523432|414505|519699|414504|523431|414506|411049|518613|515765|516070|525661|526769|526788|530073|530148|545462|222661|222662|510089|512215|512363|512374|512461|512658|512673|513728|513731|514090|514568|514868|514898|514945|515640|515743|515836|516164|516275|516283|516291|516306|517858|517967|518020|518054|518138|518218|518306|518307|518328|518491|518950|518984|518995|520196|520199|520404|520977|521039|521042|521043|521397|522027|522446|522949|522979|523284|523431|523432|524003|524314|524474|524703|524820|525496|525662|525663|525664|525695|525718|526863|526892|527036|527468|527495|527496|527497|527532|527533|527538|527539|527543|527544|527616|527887|527889|528941|529285|529323|530049|530452|530599|530996|531681|531705|533914|534249|534447|534448|536804|536956|539039|539059|539083|540508|540631|540755|540760|541187|541555|541759|542510|542622|542711|542974|542976|542982|543051|543095|543391|543559|543960|544169|544199|544300|544507|544540|544547|544554|544560|544570|544599|544654|544665|544839|544859|544883|545670|545719|545768|545780|545810|545823|545832|545850|545967|546056|546460|546461|546559|546744|546754|547468|548295|548296|548337|548474|548723|548724|548984|548985|549167|549339|549359|549383|549585|552072|552236|552317|552640|552914|553624|553636|553665|553666|554282|554309|554480|554613|554722|554774|554775|555047|556615|556616|556673|558303|559089|548639|508116|514201|519810|539028|544828|549328|552128|552297|510426|222663|515740|515741|515742|515802|520402|520403|520405|520621|525320|526762|527405|527407|527425|527430|531249|539090|539091|544890|544891|549390|549391|552697|517914|518967|530780|530994|222664|528392|528860|523791|527010|527018|554563|554587|531449|606282|637095|520400|520401|527467|530038|534503|534513|534516|534562|534571|535081|535085|535088|535091|535094|535097|535106|535822|535841|535850|535858|535863|535867|535871|536143|539011|539012|539021|539022|539029|539031|539040|539041|539050|539051|539058|539060|539062|539063|539064|539066|539068|539070|539073|539074|539077|539084|539085|541886|544810|544829|544831|544833|544841|544860|544862|544863|544864|544874|544881|549319|549329|549341|549358|549363|549368|552070|554917|230086|637568|230097|637599|637612|230090|637609|513629|513630|513631|533655|533706|542193|543744|543927|543971|544265|544276|544293|544296|544328|544386|544442|544600|544672|544673|545073|545287|545453|545606|545707|545752|545824|545957|545959|546003|546337|548305|548722|548802|548839|230085|637600|545011|548625|548708|548721|543869|544753|545349|545394|548514|549202|549221|545368|546338|544522|546841|230132|230169|230226|230305|230319|230330|511258|519166|519662|230378|230610|230667|679018|402434|402773|402774|405773|405774|405775|405776|405777|405778|405779|416914|457548|482481|482482|400217|400225|400235|400238|400247|400248|400252|400253|400254|400268|400439|400635|400637|400638|400642|400643|400645|400646|400647|400649|400652|400653|400654|400689|400694|401130|401131|401132|401144|401145|401146|401165|401167|401168|401652|401653|402433|402439|403217|403225|403237|403246|403247|403254|404948|405967|405980|406211|406871|407409|407414|407505|407600|407657|409923|410001|410002|410003|410004|411049|411860|411861|412723|413409|414505|414506|415221|416069|416094|417872|417873|417874|417957|417987|417988|418668|420180|421309|421507|421843|421844|421845|421847|421848|422004|422005|422007|422100|422102|422103|422200|422203|422527|423074|423075|424430|431929|432942|432943|432944|434101|434945|438672|438903|439354|439388|439389|439390|439480|439481|440132|441030|444054|446305|449771|450321|451301|452407|456137|456340|456348|456401|457943|459020|459023|459077|459078|459080|459313|459314|459315|459316|459450|459451|459454|459455|459457|459458|460034|460035|460038|463292|463293|463294|463295|463296|463297|463298|463299|464294|464295|464296|464297|464298|464299|464300|464301|465769|467100|467793|469865|469866|469867|469868|470598|472663|472664|472665|472666|472667|472668|472669|473660|473661|473662|474538|477128|477129|477176|478307|478308|478309|478310|482447|482448|482476|482477|482478|482479|483085|483096|483097|483098|483150|483151|485103|485104|485486|486654|489391|489399|489400|489423|489430|489451|435002|435033|435035|489639|490144|490172|491446|491447|491448|493490|498530|498553|498554|498555|498556|400234|400634|403230|421470|421471|422006|422101|422201|477175|418043|418044|421508|421644|421862|421864|422185)/.test(value)){
        return true;
    } else {
        return false;
    }
}, "Esta loja só aceita cartões Itaucard");

jQuery.validator.addMethod("hipercard-only", function(value, element) {
	if (/^(606282|528392|528860)/.test(value)){
        return true;
    } else {
        return false;
    }
}, "Esta loja só aceita cartões Hipercard");


jQuery.validator.addMethod("fullname", function(value, element) {
	return (/^[^\d|\s]+\s[^\d]+$/.test(value));
}, "* Fullname should consist of at least first and last names.");

jQuery.validator.addMethod("maskedPostcodeAtleast4Digits", function(value, element) {
	value = value.replace(/_/g,'').replace(/-/g,''); // remove masking junk (dash & underscores)
	return value.length >= 4;
}, "* Postcode needs to be of atleast 4 digits.");

jQuery.validator.addMethod("maskedTelephoneAllDigits", function(value, element) {
	value = value.replace(/\D/g,''); // reject everything other than numbers
	if ( !value && !jQuery(element).hasClass('required') ) {
		return true;
	}
	if(value != parseInt(value, 10))
		return false;
	var form_type = jQuery('#checkoutSteps > li.active form').attr('id').split('-')[1];
	if ('payment' == form_type)
		form_type = 'billing';
	country_id = jQuery(jq(form_type+':country_id')).val();
	var numberOfDigits = numberOfDigitsPerCountry(country_id);
	if (jQuery.isArray(numberOfDigits)) { // if number of possible lengths are multiple
		var flag = false;
		numberOfDigits.forEach(function(element) {
			if(element == value.length)
				flag = true;
		});
		return flag;
	} else if ((parseInt(numberOfDigits, 10)) != 0) { // if number of possible length is a single number
		return (parseInt(numberOfDigits, 10)) == value.length;
	} else { // default case when any length number is acceptable since its known for that country
		return true;
	}
}, "* Phone needs to be of atleast 5 digits & should be valid for your selected country.");

jQuery.validator.addClassRules({
	'required-entry': {
		required: true
	},
	'postcode': {
		maskedPostcodeAtleast4Digits: ''
	},
	'telephone': {
		maskedTelephoneAllDigits: ''
	},
	'validate-cc-number': {
		creditcard2: ''
	}
});
