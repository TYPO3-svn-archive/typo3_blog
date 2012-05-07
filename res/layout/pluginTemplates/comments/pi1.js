function tx_comments_pi1_readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for (var i = 0; i < ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1, c.length);
		}
		if (c.indexOf(nameEQ) == 0) {
			return unescape(c.substring(nameEQ.length,c.length)).replace(/\+/, ' ');
		}
	}
	return false;
}

function tx_comments_pi1_setUserDataField(name) {
	var	field = document.getElementById('tx_comments_pi1_' + name);
	try {
		if (field && field.value == '') {
			var	value = tx_comments_pi1_readCookie('tx_comments_pi1_' + name);
			if (typeof value == 'string') {
				field.value = value;
			}
		}
	}
	catch (e) {
	}
}

function tx_comments_pi1_setUserData() {
	tx_comments_pi1_setUserDataField('firstname');
	tx_comments_pi1_setUserDataField('lastname');
	tx_comments_pi1_setUserDataField('location');
	tx_comments_pi1_setUserDataField('email');
	tx_comments_pi1_setUserDataField('homepage');
}