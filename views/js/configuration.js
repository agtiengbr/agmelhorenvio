document.addEventListener('DOMContentLoaded', function(){
	//sem esse delay o select buga por algum motivo...
	$('#tabSender').addClass('active');
	$('#agmelhorenvio_agency_jadlog').chosen();
	$('#agmelhorenvio_agency_latam').chosen();
	$('#agmelhorenvio_agency_total_express').chosen();

	function toggleAutoGenerateStatesField() {
		var enabled = $('#agmelhorenvio_auto_generate_labels_on').is(':checked');
		$('#agmelhorenvio_auto_generate_label_states').closest('.form-group').toggle(enabled);
	}

	toggleAutoGenerateStatesField();
	$('#agmelhorenvio_auto_generate_labels_on, #agmelhorenvio_auto_generate_labels_off').on('change', toggleAutoGenerateStatesField);
	$('#tabSender').removeClass('active');
})