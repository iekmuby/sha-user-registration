var ajaxRegister = function() {
	_self = this;

	//Deafult settings
	_self.formId = 'register-form';
	_self.ajaxUrl = '/wp-admin/admin-ajax.php';
	_self.ajaxMethod = 'post';
	_self.fields = {
		'login': {
			'id': 'regUser',
			'validators': [
				{
					'validator': 'isNotEmpty',
					'error': 'User name is empty'
				}
			],
		},
		'password': {
			'id': 'regPass',
			'validators': [
				{
					'validator': 'isNotEmpty',
					'error': 'User name is empty'
				},
				{
					'validator': 'isStrongPassword',
					'error': 'Password is weak'
				}
			]
		},
		'email': {
			'id': 'regEmail',
			'validators': [
				{
					'validator': 'isNotEmpty',
					'error': 'User name is empty'
				},
				{
					'validator': 'isEmail',
					'error': 'Email incorrect'
				}
			]
		},
		'regAgreement': {
			'id': 'regAgreement',
			'validators': [
				{
					'validator': 'isChecked',
				}
			]
		}
	}

	//Default validators
	this.validators = {
		'isEmail': function(field) {
			var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
			return re.test(String(field.value).toLowerCase());
		},
		'isNotEmpty': function(field) {
			return (field.value === "") ? false : true;
		},
		'isStrongPassword': function(field) {
			var score = 0,
				password = field.value;

			if (password.length > 8) {
				score += 20;
			} else {
				score -= 50;
			}

			if ((password.match(/[a-z]/))) {
				score += 10;
			}

			if ((password.match(/[A-Z]/))) {
				score += 20;
			}
			
			if (password.match(/.[,!,@,#,$,%,^,&,*,?,_,~,-,(,)]/)) {
				score += 40;
			}
			
			if (password.match(/[0-9]/)) {
				score += 10;
			}

			return (score < 50) ? false : true;
		},
		'isChecked': function(field) {		
			return (!field.checked) ? false : true;
		}

	}
	
	//Add custom validators
	this.addValidators = function(validators) {
		if (typeof validators !== 'undefined' ) {
			for (var validator in validators) {
				_self.validators[validator] = validators[validator];
			}
		}
	}

	//Validate fields by validators
	this.validateForm = function() {
		_self.clearForm();

		var isValid = true,
			errors = [];
		for (var field in _self.fields) {
			var fieldObject = document.getElementById(_self.fields[field].id);
			for (var validator in _self.fields[field].validators) {
				var validatorName = _self.fields[field].validators[validator].validator;

				//If validator exists
				if (_self.validators.hasOwnProperty(validatorName)) {
					//If field not valid
					if (!_self.validators[validatorName](fieldObject)) {
						isValid = false;
						var inputDiv = document.getElementById(_self.fields[field].id).parentNode,
							inputDivClass = inputDiv.className;

						if (_self.fields[field].validators[validator].hasOwnProperty('error')) {
							errors.push({
								'id':	 _self.fields[field].id,
								'error': _self.fields[field].validators[validator].error
							});						} else {
							errors.push({
								'id':	 _self.fields[field].id,
							});
						}
					}
				}
			}
		}
		
		//Show errors
		if (errors.length > 0) {
			this.showErrors(errors);
		}
		
		return isValid;
	}

	//Clear errors before validation
	this.clearForm = function() {
		for (field in _self.fields) {
			var inputDiv = document.getElementById(_self.fields[field].id).parentNode,
				inputDivClass = inputDiv.className;

			if (inputDivClass.indexOf('has-error')  != -1) {
				inputDiv.className = inputDivClass.replace(/\s+has-error/ig,'');
			}
			
			if (inputDiv.getElementsByClassName('help-block').length > 0) {
				inputDiv.getElementsByClassName('help-block')[0].innerHTML = '';
			}
			
			document.getElementById('globalErrors').getElementsByClassName('help-block')[0].innerHTML = '';
		}
	}
	
	//Empty form fields validation
	this.emptyForm = function() {		
		for (field in _self.fields) {			
			var inputField = document.getElementById(_self.fields[field].id);
			
			if (inputField.type == 'checkbox') {
				inputField.checked = false;
			}

			if ((inputField.type == 'text') || (inputField.type == 'email') || (inputField.type == 'password')) {
				inputField.value = '';
			}
		}
	}
	
	//Show errors
	this.showErrors = function(errors) {
		if (typeof errors !== 'undefined' ) {
			for (error in errors) {
				var errorItem = errors[error],
					inputDiv = document.getElementById(errorItem.id).parentNode,
					inputDivClass = inputDiv.className;

				if (inputDivClass.indexOf('has-error')  == -1) {
					inputDiv.className = inputDivClass + ' has-error';
				}

				if (errorItem.hasOwnProperty('error')) {
					inputDiv.getElementsByClassName('help-block')[0].innerHTML = errorItem.error;
				}
			}
		}
	}

	//Init variables and bind actions
	this.init = function(settings) {
		document.addEventListener('DOMContentLoaded', function() {
			if (typeof settings !== 'undefined' ) {
				if (settings.hasOwnProperty('formId')) {
					_self.formId = settings.formId;
				}
				
				if (settings.hasOwnProperty('ajaxUrl')) {
					_self.ajaxUrl = settings.ajaxUrl;
				}

				if (settings.hasOwnProperty('ajaxMethod')) {
					_self.ajaxMethod = settings.ajaxMethod;
				}

				if (settings.hasOwnProperty('fields')) {
					_self.fields = settings.fields;
				}
			}			

			var form = document.getElementById(_self.formId);
			
			form.addEventListener('submit', function(e) {
				e.preventDefault();
				
				if (_self.validateForm()) {
					
					_self.beforeSend();
								
					var inputElements = document.getElementById(_self.formId).getElementsByTagName("input"),
						formData = [];
						
					for (var i = 0; i < inputElements.length; i++) {
						var inputElement = inputElements[i];
						formData.push(inputElement.name + '=' + inputElement.value);
					}
					
					if (document.getElementById('g-recaptcha-response')) {
						formData.push('g-recaptcha-response=' + document.getElementById('g-recaptcha-response').value);
					}

					_self.ajaxRequest(_self.ajaxUrl, _self.ajaxMethod, formData.join('&'));
				}
			});
		});
	}

	this.ajaxRequest = function(url, method, data) {
		var httpRequest;
		if (window.XMLHttpRequest) { // Mozilla, Safari, ...
			httpRequest = new XMLHttpRequest();
		} else if (window.ActiveXObject) { // IE
			try {
				httpRequest = new ActiveXObject("Msxml2.XMLHTTP");
			}
			catch (e) {
				try {
					httpRequest = new ActiveXObject("Microsoft.XMLHTTP");
				}
				catch (e) {
				}
			}
		}

		if (!httpRequest) {
			console.log('Giving up :( Cannot create an XMLHTTP instance');
			return false;
		}
		
		httpRequest.onreadystatechange = function() {

			if (httpRequest.readyState == 4) {
				if (httpRequest.status == 200) {
					var response = JSON.parse(httpRequest.responseText);
					if (response.status == 'success') {
						_self.success(response);
					} 
					
					if (response.status == 'failed') {
						_self.failed(response);
					}
				} else {
					var response = {
						'status':	'serverror',
						'errors':	[
							{
								'id':		'globalErrors',
								'error':	'Server error, try again'
							}
						]
					}
					_self.failed(response);
				}
			}
		}

		if (method && method.toUpperCase() == 'POST') {
			httpRequest.open(method, url, true);
			httpRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			httpRequest.setRequestHeader("X-Requested-With", "XMLHttpRequest");
			httpRequest.send(data);
		} else {
			httpRequest.open(method, url);
			httpRequest.send();
		}
	}

	this.beforeSend = function() { }
	
	this.failed = function(response) { }
	
	this.success = function(response) { }
}
