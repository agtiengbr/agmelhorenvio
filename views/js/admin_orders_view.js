$(function(){
    var messages = [];

    function agmelhorenvioAjaxUrl() {
        return (typeof agmelhorenvio_ajax_url !== 'undefined') ? agmelhorenvio_ajax_url : 'ajax-tab.php';
    }

    function getOrderIdFromPage()
    {
        var match = location.href.match(/id_order=([0-9]*)/);
        if (match) {
            return match[1];
        }

        match = location.href.match(/sell\/orders\/(\d+)\/view/);
        if (match) {
            return match[1];
        }

        return null;
    }

    function addMessage(message, type)
    {
        var alert = document.createElement('div');
        alert.classList.add('alert');
        alert.classList.add('alert-' + type);

        alert.innerText = message;

        $(alert).insertBefore(document.querySelector('.kpi-container'));

        messages.push(alert);
    }

    function removeMessages()
    {
        while(messages.length) {
            messages[0].parentNode.removeChild(messages[0]);
            messages.splice(0, 1);
        }
    }

    function renderInvoiceInputs()
    {
        var panel = $('<div/>', {
            class : 'panel'
        });

        var invoice_number_input = $('<input/>', {
            name: 'agmelhorenvio_invoice_number',
            id: 'agmelhorenvio_invoice_number',
            value: agmelhorenvio_invoice_numbe
        });

        var invoice_number_label = $('<label/>',{
            for: 'agmelhorenvio_invoice_number',
            text: 'Número da Nota Fiscal',
            class: 'control-label col-lg-3'
        });

        var invoice_number_group = $('<div/>', {
            class: 'form-group'
        });

        invoice_number_group
            .append(invoice_number_label)
            .append(invoice_number_input)
            .appendTo(panel);


        var invoice_serie_input = $('<input/>', {
            name: 'agmelhorenvio_invoice_serie',
            id: 'agmelhorenvio_invoice_serie',
            value: agmelhorenvio_invoice_serie
        });

        var invoice_serie_label = $('<label/>',{
            for: 'agmelhorenvio_invoice_serie',
            text: 'Chave de Acesso da Nota Fiscal',
            class: 'control-label col-lg-3'
        });

        var invoice_serie_group = $('<div/>', {
            class: 'form-group'
        });

        invoice_serie_group
            .append(invoice_serie_label)
            .append(invoice_serie_input)
            .appendTo(panel);

        
        var btn = $('<button/>', {
            class: 'btn btn-primary',
            text: 'Salvar dados da Nota Fiscal'
        }).appendTo(panel);


        panel.insertAfter($('.row > .col-lg-7 > .panel > .hidden-print'));

        
        btn.click(submitInvoiceData);
    }

    function submitInvoiceData()
    {
        var invoice_number = $('#agmelhorenvio_invoice_number').val();
        var invoice_serie = $('#agmelhorenvio_invoice_serie').val();

        $.ajax({
            url: agmelhorenvioAjaxUrl(),
            data: {
                controller: 'AdminAgMelhorEnvioLabels',
                ajax: true,
                action: 'SaveInvoiceData',
                token: agmelhorenvio_token,

                invoice_number: invoice_number,
                invoice_serie: invoice_serie,
                id_order: id_orde
            },
            complete: function(){
                location.reload();
            }
        });

        $(this).attr('disabled', 'disabled');
        return false;
    }


    function btnGenerateLabelClicked(e)
    {
        e.preventDefault();
        e.stopPropagation();

        var id_order = $(this).attr('data-id-order') || getOrderIdFromPage();

        removeMessages();
        addMessage('As etiquetas solicitadas estão sendo geradas.', 'info');

        $.ajax({
            url: agmelhorenvioAjaxUrl(),
            dataType: 'json',
            data :{
                ajax: true,
                controller: 'AdminAgMelhorEnvioLabels',
                action: 'CreateLabelForOrder',
                token: agmelhorenvio_token,

                id_order: id_orde
            },
            success: function(data) {
                removeMessages();
                if (typeof data.success !== 'undefined' && data.success) {
                    addMessage('Etiquetas geradas com sucesso!', 'success');
                } else {
                    var error = 'Ocorreu um erro ao gerar as etiquetas';
                    if (typeof data.error !== 'undefined') {
                        error = data.error;
                    }

                    addMessage(error, 'danger');
                }
            },
            error: function(){
                removeMessages();
                addMessage('Ocorreu um erro ao gerar as etiquetas.', 'danger');
            }
        });

        return false;
    }

    $('#page-header-desc-order-melhorenvio_label').click(btnGenerateLabelClicked);
    $(document).on('click', '.agmelhorenvio-generate-label', btnGenerateLabelClicked);

    renderInvoiceInputs();
});
