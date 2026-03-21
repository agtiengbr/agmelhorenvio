document.addEventListener('DOMContentLoaded', function(){
	var buttons = document.getElementsByClassName('agmelhorenvio-generate-label');
	var table = document.querySelector('table.order');

	var messages = [];

	function addMessage(message, type)
	{
		var container = table.closest('.col-lg-12');
		var kpi = container.querySelector('.kpi-container');

		var alert = document.createElement('div');
		alert.classList.add('alert');
		alert.classList.add('alert-' + type);

		alert.innerText = message;

		$(alert).insertBefore(document.getElementById('form-order'));

		messages.push(alert);
	}

	function removeMessages()
	{
		while(messages.length) {
			messages[0].parentNode.removeChild(messages[0]);
			messages.splice(0, 1);
		}
	}

	function btnGenerateLabelClicked(e)
	{
		e.preventDefault();
		e.stopPropagation();

		var button = this;
		var btn_group = this.closest('.btn-group.pull-right');
		var icon_view = btn_group.querySelector('.icon-search-plus');
		var btn_view = icon_view.parentNode;

		var id_order = btn_view.href.match(/id_order=([0-9]*)/)[1];

		removeMessages();
		addMessage('As etiquetas solicitadas estão sendo geradas.', 'info');

		$.ajax({
			url: 'ajax-tab.php',
			dataType: 'json',
			data :{
				ajax: true,
				controller: 'AdminAgMelhorEnvioLabels',
				action: 'CreateLabelForOrder',
				token: agmelhorenvio_token,

				id_order: id_order
			},
			success: function(data) {
				if (typeof data.success !== 'undefined' && data.success) {
					removeMessages();
					addMessage('Etiquetas geradas com sucesso!', 'success');
				} else {
					removeMessages();
					addMessage('Ocorreu um erro ao gerar as etiquetas.', 'danger');
				}
			},
			error: function(){
				removeMessages();
				addMessage('Ocorreu um erro ao gerar as etiquetas.', 'danger');
			}
		})
	}

	for (var i=0; i<buttons.length; i++) {
		var button = buttons[i];

		button.addEventListener('click', btnGenerateLabelClicked);
	}
});