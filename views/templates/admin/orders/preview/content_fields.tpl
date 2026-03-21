<div class="tab-pane d-print-block active fade show" id="notaFiscalContent" role="tabpanel" aria-labelledby="notaFiscalContent">
  <form method="post" enctype="multipart/form-data">
    <div id="extra" class="card-body row" data-id-order="{$id_order}">
      <div class="form-group col-md-6">
        <label for="agmelhorenvio_invoice_number" class="control-label">Número</label>
        <input class="form-control" name="agmelhorenvio_invoice_number" id="agmelhorenvio_invoice_number" value="{$agmelhorenvio_invoice_number}">
      </div>
      <div class="form-group col-md-6">
        <label for="agmelhorenvio_invoice_serie" class="control-label">Chave de Acesso</label>
        <input class="form-control" name="agmelhorenvio_invoice_serie" id="agmelhorenvio_invoice_serie" value="{$agmelhorenvio_invoice_serie}">
      </div>
    </div>
    <div class="card-footer text-right">
      <button type="submit" name="agmelhorenvio-invoices" class="btn ml-3 btn-outline-primary">Salvar dados da Nota Fiscal</button>
    </div>
  </form>
</div>
