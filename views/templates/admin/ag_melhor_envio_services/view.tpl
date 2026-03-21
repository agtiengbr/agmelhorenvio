<form name='teste' class="form-horizontal" method="post" action="{$form_action|escape:'htmlall':'utf-8'}">
	<ps-panel icon='icon-cogs' header='Configurações'>
		<div class="alert alert-info">
			Parâmetros conhecidos:
			<ul>
				<li><strong>invoice</strong> - Número da Nota Fiscal</li>
				<li><strong>declared_value</strong> - Valor Declarado</li>
				<li><strong>own_hand</strong> - Mãos Próprias Declarado</li>				
				<li><strong>receipt</strong> - Retirada em Mãos</li>
			</ul>
		</div>

		{if count($object->getOptionals())}
			<h4>Parâmetros opcionais</h4>
			<p>
				Os seguintes parâmetros estão disponíveis para este serviço:
			</p>
			
			<ul>
				{foreach from=$object->getOptionals() item=optional}
					<li>{$optional->getOption()->name}</li>
				{/foreach}
			</ul>
		{/if}

		{if count($object->getRequirements())}
			<h4>Parâmetros obrigatórios</h4>

			<p>
				Os seguintes parâmetros devem ser informados para que este serviço seja utilizado:
			</p>

			<ul>
				{foreach from=$object->getRequirements() item=requirement}
					<li>{$requirement->getOption()->name}</li>
				{/foreach}
			</ul>
		{/if}

        <ps-panel-footer>
            <ps-panel-footer-submit direction="left" title="Cancelar" icon='process-icon-cancel' name="agmelhorenvio_cancel"></ps-panel-footer-submit>
            <ps-panel-footer-submit direction="right" title="Salvar" icon='process-icon-save' name="agmelhorenvio_submit"></ps-panel-footer-submit>
        </ps-panel-footer>
    </ps-panel>

{* 	<div class="col-lg-6">
		<ps-panel icon='icon-cogs' header='Mapeamentos'>
			<ps-select label="Número" name="mapping_number">
				{foreach from=$mappings['address_number']->getColumnsFromTable() item=column}
					<option value='{$column}'>{$column}</option>
				{/foreach}
			</ps-select>

			<ps-select label="Nota Fiscal" name="mapping_invoice">
				{foreach from=$mappings['invoice_number']->getColumnsFromTable() item=column}
					<option value='{$column}'>{$column}</option>
				{/foreach}
			</ps-select>
		</ps-panel>
	</div> *}
</form>

{include file="../ps-tags.tpl"}