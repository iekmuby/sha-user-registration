var settings = {
		fields: {
			'username': {
				'id': 'regUser',
/*
				'validators': [
					{
						'validator': 'isNotEmpty',
						'error': 'Email incorrect'
					}
				]
*/
			},
			'email': {
				'id': 'regEmail',
/*
				'validators': [
					{
						'validator': 'isEmail',
						'error': 'Email incorrect'
					}
				]
*/
			},
			'password': {
				'id': 'regPass',
/*
				'validators': [
					{
						'validator': 'isStrongPassword',
						'error': 'Password is weak'
					}
				]
*/	
			},
			'regAgreement': {
				'id': 'policy',
				'validators': [
					{
						'validator': 'isChecked',
					}
				]
			}
		}
	},
	validators = {
		'isChecked': function(field) {		
			return (!field.checked) ? false : true;
		}
	}

var ajaxRegisterObj = new ajaxRegister();
//ajaxRegisterObj.addValidators(validators);

ajaxRegisterObj.beforeSend = function() {
	document.getElementById('preloader').style.display = 'block';
}

ajaxRegisterObj.success = function(response) {
	document.getElementById('preloader').style.display = 'none';
	ajaxRegisterObj.emptyForm();
	jQuery(".registration").fadeOut();
};

ajaxRegisterObj.failed = function(response) {
	document.getElementById('preloader').style.display = 'none';
	if (response.hasOwnProperty('errors')) {
		var errors = response.errors;

		if (errors.length > 0) {
			ajaxRegisterObj.showErrors(errors);
		}
	}
	grecaptcha.reset();
};

ajaxRegisterObj.init(settings);
