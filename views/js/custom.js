$(document).on('click', `#${btn_clear_cache}`, function() {
    $.ajax({
        url: 'index.php',
        type: 'POST',
        dataType: 'json',
        cache: false,
        data: {
            'ajax': true,
            'controller': 'AdminAgMelhorEnvioCache',
            'clearCache': 1,
            'configure': 'agmelhorenvio',
            'action': 'ClearShippingCache',
            'token' : token,
        },
    })
    .then(function(data){
        if(data.result == true) {
            message = '<div class="alert alert-success" role="alert">Limpeza concluida</div>';
            window.location.reload();
        } else {
            message = '<div class="alert alert-danger" role="alert">Ocorreu ao limpar a tabela, confira os registros da loja.</div>';
        }
        
        $(".bootstrap.with-tabs > .row").first().before(message);
    })
    .fail(function(data){
        message = `<div class="alert alert-danger" role="alert">
            Ocorreu ao limpar a tabela, confira os registros da loja.
        </div>`;

        $(".bootstrap.with-tabs > .row").first().before(message);
    });
});