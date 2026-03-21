document.addEventListener('DOMContentLoaded', function(){
    $('#module_agmelhorenvio .btn-primary').click(function(e){
        e.preventDefault();
        e.stopPropagation();

        $.ajax({
            data: {
                action: 'agmelhorenvio_save_hook_extra_product',
                zipcode: $('[name=agmelhorenvio_zipcode_origin]').val(),
                id_product: $('.form-items').attr('data-id-product'),
                shop_name: $('[name=agmelhorenvio_shop_name').val(),
                address: $('[name=agmelhorenvio_address').val(),
                number: $('[name=agmelhorenvio_number').val(),
                district: $('[name=agmelhorenvio_district').val(),
                city: $('[name=agmelhorenvio_city').val(),
                uf: $('[name=agmelhorenvio_uf').val(),
                phone: $('[name=agmelhorenvio_phone').val(),
                cnpj: $('[name=agmelhorenvio_cnpj').val(),
                state_register: $('[name=agmelhorenvio_state_register').val(),
                agency_jadlog: $('[name=agmelhorenvio_agency_jadlog').val(),
                agency_latam: $('[name=agmelhorenvio_agency_latam').val()
            },
            dataType: 'json',
            success: function(data) {
                if (data.success == 1) {
                    $.growl.notice({title: '', message: 'Salvo com sucesso.'});
                } else {
                    $.growl.error({title: '', message: data.error});
                }
            },
            error: function(){
                $.growl.error({title: '', message: 'Ocorreu um erro inesperado.'});
            }
        });

        return false;
    });

    $('[name=agmelhorenvio_zipcode_origin]').change(function(){
        $.ajax({
            data: {
                action: 'agmelhorenvio_search_address',
                zipcode: $(this).val()
            },
            dataType: 'json',
            success: function(data){
                if (data.success == 1) {
                    if (data.type == 'address') {
                        $('[name=agmelhorenvio_address]').val(data.data.street);
                        $('[name=agmelhorenvio_number]').val('');
                        $('[name=agmelhorenvio_district]').val(data.data.district);
                        $('[name=agmelhorenvio_city]').val(data.data.city);
                        $('[name=agmelhorenvio_uf]').val(data.data.state);
                    } else {                        
                        $('[name=agmelhorenvio_shop_name]').val(data.data.shop_name);
                        $('[name=agmelhorenvio_address]').val(data.data.address);
                        $('[name=agmelhorenvio_number]').val(data.data.number);
                        $('[name=agmelhorenvio_district]').val(data.data.district);
                        $('[name=agmelhorenvio_city]').val(data.data.city);
                        $('[name=agmelhorenvio_uf]').val(data.data.uf);
                        $('[name=agmelhorenvio_phone]').val(data.data.phone);
                        $('[name=agmelhorenvio_cnpj]').val(data.data.cnpj);
                        $('[name=agmelhorenvio_state_register]').val(data.data.state_register);
                        $('[name=agmelhorenvio_agency_jadlog]').val(data.data.agency_jadlog);
                        $('[name=agmelhorenvio_agency_latam]').val(data.data.agency_latam);
                    }
                }
            },
            error: function(){
                $.growl.error({title: '', message: 'Ocorreu um erro inesperado.'});
            }
        })
    })
});