document.addEventListener('DOMContentLoaded', function(){

	$("#agmelhorenvio_shop_address_zipcode").change(function() {
		let cep = $("#agmelhorenvio_shop_address_zipcode").val();
        
		if(cep.length == 9){
			let url = "https://viacep.com.br/ws/"+cep.replace(/\D/g, '')+"/json/";
			$.get(url, function(data, status){
				if(status == 'success'){
					$("#agmelhorenvio_shop_address_district").val(data.bairro);
					$("#agmelhorenvio_shop_address").val(data.logradouro);
					$("#agmelhorenvio_shop_address_city").val(data.localidade);
					$("#agmelhorenvio_shop_address_state").val(data.uf);
				}
			});
		}
  });
});

