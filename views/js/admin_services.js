document.addEventListener('DOMContentLoaded', function(){

	//ps 1.7
	var table = document.getElementById('table-agmelhorenvio_service');

	//ps 1.6
	if (table === null) {
		table = document.querySelector('table.agmelhorenvio_service');
	}

	if (table === null) {
		return;
	}

	var rows = table.querySelectorAll('tbody tr');

	for (var i=0; i<rows.length; i++) {
		var tds = rows[i].getElementsByTagName('td');
		var td_image = tds[6];

		var image = document.createElement('img');
		image.src = td_image.innerText.trim();
		image.width = 100;

		td_image.innerText = '';
		td_image.appendChild(image);
	}
});
