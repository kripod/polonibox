var wsuri = 'wss://api.poloniex.com';
var messageTable = document.getElementsByTagName('tbody')[0];
var notificationContainer = document.getElementById('notification-container');
var notificationContainerMessageCount = document.getElementById('notification-container-message-count');
var rowsToDelete = 0;
var specialUsers;

$.get('../data/specialUsers.txt', function(data) {
	specialUsers = data;
}, 'json');

Number.prototype.addLeadingZeroes = function(charCount) {
	charCount = charCount || 2;

	var inputString = this.toString();
	var addition = '';
	for (var i = Math.max(charCount - inputString.length, 0); i > 0; i--) {
		addition += '0';
	}

	return addition + inputString;
};

decodeHtmlEntities = (function() {
	var element = document.createElement('div');

	function decodeHtmlEntitiesInternal(str) {
		if (str && typeof str === 'string') {
			str = str.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '');
			str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');
			element.innerHTML = str;
			str = element.textContent;
			element.textContent = '';
		}

		return str;
	}

	return decodeHtmlEntitiesInternal;
})();

removeUnnecessaryRows = function() {
	notificationContainer.style.visibility = 'hidden';

	for (var i = rowsToDelete; i > 0; i--) {
		messageTable.deleteRow(messageTable.rows.length - 1);
	}

	rowsToDelete = 0;
};

notificationContainer.onclick = function() {
	notificationContainer.style.visibility = 'hidden';

	$('html, body').animate(
		{ scrollTop: 0 },
		700
	);
};

window.onscroll = function() {
	if ($(window).scrollTop() == 0) {
		removeUnnecessaryRows();
	}
};

var connection = new autobahn.Connection({
	url: wsuri,
	realm: 'realm1'
});

connection.onopen = function(session) {
	function trollboxEvent(args, kwargs) {
		var senderName = decodeHtmlEntities(args[2]);
		var senderReputation = args[4];
		var messageNumber = args[1];
		var messageDate = new Date();
		var messageText = linkifyStr(decodeHtmlEntities(args[3]));

		var messageDateLocal = messageDate.getFullYear() + '-' + (messageDate.getMonth() + 1).addLeadingZeroes() + '-' + messageDate.getDate().addLeadingZeroes() + ' ' + messageDate.getHours().addLeadingZeroes() + ':' + messageDate.getMinutes().addLeadingZeroes() + ':' + messageDate.getSeconds().addLeadingZeroes();
		var messageDateUtc = messageDate.getUTCFullYear() + '-' + (messageDate.getUTCMonth() + 1).addLeadingZeroes() + '-' + messageDate.getUTCDate().addLeadingZeroes() + ' ' + messageDate.getUTCHours().addLeadingZeroes() + ':' + messageDate.getUTCMinutes().addLeadingZeroes() + ':' + messageDate.getUTCSeconds().addLeadingZeroes();

		switch (senderName) {
			case 'Banhammer':
				var messageRowClass = 'danger';
				break;
			case 'marking@poloniex':
				var messageRowClass = 'info';
				break;
			default:
				var messageRowClass = '';
		}

		if (specialUsers !== undefined) {
			switch (specialUsers[senderName]) {
				case 1:
					var senderBadgeCode = '<span title="Moderator" class="badge alert-info">M</span> ';
					break;
				case 2:
					var senderBadgeCode = '<span title="Engineer" class="badge alert-warning">E</span> ';
					break;
				case 3:
					var senderBadgeCode = '<span title="Administrator" class="badge alert-danger">A</span> ';
					break;
				default:
					var senderBadgeCode = '';
			}
		} else {
			var senderBadgeCode = '';
		}

		if (senderReputation != null) {
			senderBadgeCode += '<span title="Reputation" class="badge">' + senderReputation + '</span> ';
		}

		if (messageText.substr(0, 3) == '/me') {
			if (messageText.substr(4, 5) == '/tiny') {
				messageText = '<i><small>' + messageText.substr(10) + '</small></i>';
			} else {
				messageText = '<i>' + messageText.substr(4) + '</i>';
			}
		} else if (messageText.substr(0, 5) == '/tiny') {
			messageText = '<small>' + messageText.substr(6) + '</small>';
		}

		var row = messageTable.insertRow(0);
		var cell1 = row.insertCell(0);
		var cell2 = row.insertCell(1);

		row.className = messageRowClass;

		cell1.className = 'text-right text-nowrap';
		cell1.innerHTML = senderBadgeCode + '<a href="?username=' + senderName + '"><b>' + senderName + '</b></a><br>' +
		'<small><a href="?messageId=' + messageNumber + '"><i class="glyphicon glyphicon-link"></i></a> <span title="' + messageDateUtc + ' UTC">' + messageDateLocal + '</span></small>';

		cell2.innerHTML = messageText;

		rowsToDelete += 1;

		if ($(window).scrollTop() != 0) {
			window.scrollBy(0, row.clientHeight);
			notificationContainerMessageCount.innerHTML = rowsToDelete;
			notificationContainer.style.visibility = 'visible';
		} else {
			removeUnnecessaryRows();
		}
	}

	session.subscribe('trollbox', trollboxEvent);
};

connection.open();