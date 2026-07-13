document.addEventListener('DOMContentLoaded', function () {
    var messages = [];

    function agmelhorenvioAjaxUrl() {
        if (typeof agmelhorenvio_ajax_url !== 'undefined' && agmelhorenvio_ajax_url) {
            return agmelhorenvio_ajax_url;
        }

        var el = document.querySelector('.agmelhorenvio-generate-label[data-ajax-url]');
        if (el) {
            return el.getAttribute('data-ajax-url');
        }

        console.error('agmelhorenvio: agmelhorenvio_ajax_url is not defined');
        return null;
    }

    function getOrderIdFromPage() {
        var match = location.href.match(/id_order=([0-9]+)/);
        if (match) {
            return match[1];
        }

        match = location.href.match(/sell\/orders\/(\d+)\/view/);
        if (match) {
            return match[1];
        }

        if (typeof id_order !== 'undefined' && id_order) {
            return String(id_order);
        }

        return null;
    }

    function getMessageAnchor() {
        return document.querySelector('.kpi-container')
            || document.querySelector('#order-view-page')
            || document.querySelector('.content-div')
            || document.querySelector('#main-div')
            || document.body;
    }

    function addMessage(message, type) {
        var alert = document.createElement('div');
        alert.className = 'alert alert-' + type;
        alert.textContent = message;
        getMessageAnchor().prepend(alert);
        messages.push(alert);
    }

    function removeMessages() {
        while (messages.length) {
            var node = messages.pop();
            if (node && node.parentNode) {
                node.parentNode.removeChild(node);
            }
        }
    }

    function buildQuery(params) {
        return Object.keys(params).map(function (key) {
            return encodeURIComponent(key) + '=' + encodeURIComponent(params[key]);
        }).join('&');
    }

    function ajaxRequest(url, params, onSuccess, onError) {
        if (!url) {
            if (typeof onError === 'function') {
                onError();
            }
            return;
        }

        var xhr = new XMLHttpRequest();
        xhr.open('GET', url + (url.indexOf('?') >= 0 ? '&' : '?') + buildQuery(params), true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onreadystatechange = function () {
            if (xhr.readyState !== 4) {
                return;
            }

            if (xhr.status >= 200 && xhr.status < 300) {
                var data = {};
                try {
                    data = JSON.parse(xhr.responseText);
                } catch (e) {
                    data = {};
                }
                if (typeof onSuccess === 'function') {
                    onSuccess(data);
                }
                return;
            }

            if (typeof onError === 'function') {
                onError();
            }
        };
        xhr.send();
    }

    function btnGenerateLabelClicked(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        var button = e && e.currentTarget ? e.currentTarget : this;
        var idOrder = button.getAttribute('data-id-order') || getOrderIdFromPage();
        var ajaxUrl = agmelhorenvioAjaxUrl();
        var serviceSelect = document.getElementById('agmelhorenvio_service_id');
        var serviceId = serviceSelect ? serviceSelect.value : '';

        removeMessages();
        addMessage('As etiquetas solicitadas estão sendo geradas.', 'info');
        button.setAttribute('disabled', 'disabled');

        var params = {
            ajax: 1,
            controller: 'AdminAgMelhorEnvioLabels',
            action: 'CreateLabelForOrder',
            token: typeof agmelhorenvio_token !== 'undefined' ? agmelhorenvio_token : '',
            id_order: idOrder,
        };
        if (serviceId) {
            params.id_agmelhorenvio_service = serviceId;
        }

        ajaxRequest(
            ajaxUrl,
            params,
            function (data) {
                removeMessages();
                button.removeAttribute('disabled');

                if (typeof data.success !== 'undefined' && data.success) {
                    addMessage('Etiquetas geradas com sucesso!', 'success');
                    return;
                }

                var error = 'Ocorreu um erro ao gerar as etiquetas';
                if (typeof data.error !== 'undefined') {
                    error = data.error;
                }
                addMessage(error, 'danger');
            },
            function () {
                removeMessages();
                button.removeAttribute('disabled');
                addMessage('Ocorreu um erro ao gerar as etiquetas.', 'danger');
            }
        );

        return false;
    }

    function bindGenerateLabelButtons() {
        document.querySelectorAll('.agmelhorenvio-generate-label').forEach(function (button) {
            if (button.getAttribute('data-agme-bound') === '1') {
                return;
            }
            button.setAttribute('data-agme-bound', '1');
            button.addEventListener('click', btnGenerateLabelClicked);
        });

        var legacyButton = document.getElementById('page-header-desc-order-melhorenvio_label');
        if (legacyButton && legacyButton.getAttribute('data-agme-bound') !== '1') {
            legacyButton.setAttribute('data-agme-bound', '1');
            legacyButton.addEventListener('click', btnGenerateLabelClicked);
        }
    }

    bindGenerateLabelButtons();

    if (typeof MutationObserver !== 'undefined') {
        var observer = new MutationObserver(bindGenerateLabelButtons);
        observer.observe(document.body, { childList: true, subtree: true });
    }
});
