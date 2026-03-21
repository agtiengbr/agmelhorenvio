document.addEventListener('DOMContentLoaded', function(){
	//sem esse delay o select buga por algum motivo...
	$('#tabSender').addClass('active');
	$('#agmelhorenvio_agency_jadlog').chosen();
	$('#agmelhorenvio_agency_latam').chosen();
	$('#agmelhorenvio_agency_total_express').chosen();
	$('#tabSender').removeClass('active');
})