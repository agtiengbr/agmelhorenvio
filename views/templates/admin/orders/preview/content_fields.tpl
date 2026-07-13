<div class="tab-pane d-print-block fade show active" id="melhorEnvioContent" role="tabpanel" aria-labelledby="melhorEnvioTab">
  <div class="card mb-3" id="agmelhorenvio-order-card">
    <h3 class="card-header">
      <i class="material-icons">local_shipping</i>
      Melhor Envio
    </h3>
    <div class="card-body" data-id-order="{$id_order|intval}" id="agmelhorenvio-order-panel"
         data-ajax-url="{$agmelhorenvio_ajax_url|escape:'html':'UTF-8'}">

      <div class="alert alert-success agmelhorenvio-order-success" style="display:none;"></div>
      <div class="alert alert-danger agmelhorenvio-order-error" style="display:none;"></div>

      <h4>Gerar etiqueta</h4>
      {if !$is_melhor_envio_order}
        <div class="alert alert-info">
          Este pedido não usou um frete do Melhor Envio. Escolha o serviço abaixo para gerar a etiqueta mesmo assim.
        </div>
      {/if}
      <div class="form-row align-items-end mb-3">
        <div class="form-group col-md-6 mb-2">
          <label for="agmelhorenvio_service_id" class="control-label">Serviço Melhor Envio</label>
          <select class="form-control" id="agmelhorenvio_service_id" name="id_agmelhorenvio_service">
            <option value="">— Selecione —</option>
            {foreach from=$melhor_envio_services item=svc}
              <option value="{$svc.id|intval}"{if $selected_service_id == $svc.id} selected="selected"{/if}>
                {$svc.name|escape:'html':'UTF-8'}{if !$svc.installed} (não instalado como transportadora){/if}
              </option>
            {/foreach}
          </select>
        </div>
        <div class="form-group col-md-6 mb-2">
          <button type="button" class="btn btn-primary" id="agmelhorenvio-create-label">
            {if $order_labels|@count}Atualizar / regenerar etiqueta{else}Gerar etiqueta{/if}
          </button>
          <p class="help-block mb-0">Depois você compra e imprime na lista de etiquetas do módulo.</p>
        </div>
      </div>

      <hr>

      <h4>Nota fiscal / XML</h4>
      {if $can_edit_invoice_fields}
        <form method="post" class="row" id="agmelhorenvio-invoice-form">
          <div class="form-group col-md-6">
            <label for="agmelhorenvio_invoice_number" class="control-label">Número</label>
            <input class="form-control" name="agmelhorenvio_invoice_number" id="agmelhorenvio_invoice_number" value="{$agmelhorenvio_invoice_number|escape:'html':'UTF-8'}">
          </div>
          <div class="form-group col-md-6">
            <label for="agmelhorenvio_invoice_serie" class="control-label">Chave de Acesso</label>
            <input class="form-control" name="agmelhorenvio_invoice_serie" id="agmelhorenvio_invoice_serie" value="{$agmelhorenvio_invoice_serie|escape:'html':'UTF-8'}">
          </div>
          <div class="col-md-12 text-right mb-3">
            <button type="button" class="btn btn-outline-primary" id="agmelhorenvio-save-invoice">Salvar dados da Nota Fiscal</button>
          </div>
        </form>
      {else}
        <p class="text-muted">
          Número: <strong>{$agmelhorenvio_invoice_number|escape:'html':'UTF-8'|default:'—'}</strong>
          &nbsp;|&nbsp;
          Chave: <strong>{$agmelhorenvio_invoice_serie|escape:'html':'UTF-8'|default:'—'}</strong>
        </p>
      {/if}

      <div class="form-group">
        <label class="control-label">XML da NF-e</label>
        {if $nfe}
          <div class="mb-2">
            <span class="badge badge-success">Anexado</span>
            {$nfe.filename|escape:'html':'UTF-8'}
            {if $nfe.nfe_key} — chave {$nfe.nfe_key|escape:'html':'UTF-8'}{/if}
            <small class="text-muted">({$nfe.date_upd|escape:'html':'UTF-8'})</small>
            <button type="button" class="btn btn-sm btn-outline-danger ml-2" id="agmelhorenvio-remove-xml">Remover</button>
          </div>
        {else}
          <p class="text-muted mb-2">Nenhum XML anexado neste pedido.</p>
        {/if}
        <div class="input-group" style="max-width: 480px;">
          <input type="file" accept=".xml,text/xml,application/xml" class="form-control" id="agmelhorenvio-xml-file">
          <div class="input-group-append">
            <button type="button" class="btn btn-primary" id="agmelhorenvio-upload-xml">Enviar XML</button>
          </div>
        </div>
        <p class="help-block">O arquivo é gravado em disco no módulo (não no banco). Necessário para transportadoras como Azul em envios comerciais.</p>
      </div>

      <hr>

      <h4>Histórico de envio</h4>
      <div id="agmelhorenvio-shipment-log" class="mb-0">
        {if $shipment_logs}
          <div class="table-responsive">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>Hora</th>
                  <th>Evento</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {foreach from=$shipment_logs item=log}
                  <tr class="{if $log.success === '0' || $log.success === 0}table-danger{elseif $log.success === '1' || $log.success === 1}table-success{/if}">
                    <td style="white-space: nowrap;">{$log.date_add|escape:'html':'UTF-8'}</td>
                    <td>{$log.message|escape:'html':'UTF-8'}</td>
                    <td>
                      {if $log.success === '1' || $log.success === 1}
                        <span class="badge badge-success">OK</span>
                      {elseif $log.success === '0' || $log.success === 0}
                        <span class="badge badge-danger">Erro</span>
                      {/if}
                    </td>
                  </tr>
                {/foreach}
              </tbody>
            </table>
          </div>
        {else}
          <p class="text-muted mb-0">Ainda não há eventos registrados para este pedido.</p>
        {/if}
      </div>
    </div>
  </div>
</div>
