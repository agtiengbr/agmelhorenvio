document.addEventListener('DOMContentLoaded', function(){
	var buttons = document.getElementsByClassName('agmelhorenvio-generate-label');
	var table = document.querySelector('table.order');

	var messages = [];

	function confirmMessage() {
        if (confirm('Isso irá cancelar a etiqueta atual do pedido e gerar uma nova etiqueta. Tem certeza?')) {
            return true;
        } else {
			return false;
		}
    }

	function btnGenerateLabelClicked(e)
	{
		var that = this;

		e.preventDefault();
		e.stopPropagation();

        if(!confirmMessage()) {
			return;
		}

		var id_order = $(this).attr('data-id-order');

		$(this).prop('disabled', true).addClass('disabled');

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
					$.growl.notice({'title': '', 'message': 'Etiqueta gerada com sucesso.'});
				} else if (typeof data.error !== 'undefined') {
					$.growl.error({'title': '', 'message': data.error});
				} else {
					$.growl.error({'title': '', 'message': 'Ocorreu um erro ao gerar as etiquetas.'});
				}

				$(that).prop('disabled', false).removeClass('disabled');

			},
			error: function(){
				$.growl.error({'title': '', 'message': 'Ocorreu um erro ao gerar as etiquetas.'});
				$(that).prop('disabled', false).removeClass('disabled');
			}
		});
	}

	$(document).on('click', '.agmelhorenvio-generate-label', btnGenerateLabelClicked);
});