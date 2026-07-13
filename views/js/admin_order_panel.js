document.addEventListener('DOMContentLoaded', function () {
  var panel = document.getElementById('agmelhorenvio-order-panel');
  if (!panel) {
    return;
  }

  var ajaxUrl = panel.getAttribute('data-ajax-url');
  var idOrder = panel.getAttribute('data-id-order');
  var successEl = panel.querySelector('.agmelhorenvio-order-success');
  var errorEl = panel.querySelector('.agmelhorenvio-order-error');

  function showMessage(ok, message) {
    if (successEl) {
      successEl.style.display = ok ? '' : 'none';
      successEl.textContent = ok ? message : '';
    }
    if (errorEl) {
      errorEl.style.display = ok ? 'none' : '';
      errorEl.textContent = ok ? '' : message;
    }
  }

  function postForm(formData, onDone) {
    formData.append('id_order', idOrder);
    fetch(ajaxUrl, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data && data.success) {
          showMessage(true, data.message || 'Operação realizada com sucesso.');
          if (onDone) {
            onDone(data);
          } else if (data.reload !== false) {
            window.location.reload();
          }
        } else {
          showMessage(false, (data && data.error) ? data.error : 'Falha na operação.');
        }
      })
      .catch(function (err) {
        showMessage(false, err.message || 'Erro de comunicação.');
      });
  }

  var uploadBtn = document.getElementById('agmelhorenvio-upload-xml');
  if (uploadBtn) {
    uploadBtn.addEventListener('click', function () {
      var input = document.getElementById('agmelhorenvio-xml-file');
      if (!input || !input.files || !input.files[0]) {
        showMessage(false, 'Selecione um arquivo XML.');
        return;
      }
      var fd = new FormData();
      fd.append('ajax', '1');
      fd.append('action', 'UploadOrderNfeXml');
      fd.append('nfe_xml', input.files[0]);
      postForm(fd);
    });
  }

  var removeBtn = document.getElementById('agmelhorenvio-remove-xml');
  if (removeBtn) {
    removeBtn.addEventListener('click', function () {
      if (!window.confirm('Remover o XML anexado deste pedido?')) {
        return;
      }
      var fd = new FormData();
      fd.append('ajax', '1');
      fd.append('action', 'RemoveOrderNfeXml');
      postForm(fd);
    });
  }

  var saveBtn = document.getElementById('agmelhorenvio-save-invoice');
  if (saveBtn) {
    saveBtn.addEventListener('click', function () {
      var fd = new FormData();
      fd.append('ajax', '1');
      fd.append('action', 'SaveInvoiceData');
      var numberInput = document.getElementById('agmelhorenvio_invoice_number');
      var keyInput = document.getElementById('agmelhorenvio_invoice_serie');
      fd.append('agmelhorenvio_invoice_number', numberInput ? numberInput.value : '');
      fd.append('agmelhorenvio_invoice_serie', keyInput ? keyInput.value : '');
      postForm(fd, function () {
        showMessage(true, 'Dados da nota fiscal salvos.');
      });
    });
  }

  var createLabelBtn = document.getElementById('agmelhorenvio-create-label');
  if (createLabelBtn) {
    createLabelBtn.addEventListener('click', function () {
      var serviceSelect = document.getElementById('agmelhorenvio_service_id');
      var serviceId = serviceSelect ? serviceSelect.value : '';
      if (!serviceId) {
        showMessage(false, 'Selecione um serviço do Melhor Envio.');
        return;
      }
      createLabelBtn.setAttribute('disabled', 'disabled');
      var fd = new FormData();
      fd.append('ajax', '1');
      fd.append('action', 'CreateLabelForOrder');
      fd.append('id_agmelhorenvio_service', serviceId);
      fd.append('id_order', idOrder);
      fetch(ajaxUrl, {
        method: 'POST',
        body: fd,
        credentials: 'same-origin'
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          createLabelBtn.removeAttribute('disabled');
          if (data && data.success) {
            showMessage(true, data.message || 'Etiquetas geradas com sucesso.');
            window.location.reload();
          } else {
            showMessage(false, (data && data.error) ? data.error : 'Falha ao gerar etiqueta.');
          }
        })
        .catch(function (err) {
          createLabelBtn.removeAttribute('disabled');
          showMessage(false, err.message || 'Erro de comunicação.');
        });
    });
  }
});
