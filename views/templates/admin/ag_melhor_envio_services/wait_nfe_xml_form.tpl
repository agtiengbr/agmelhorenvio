{if $is_azul}
	<div class="alert alert-info">
		Para a Azul Cargo Express o XML da NF-e é obrigatório em envios comerciais via API
		(<code>options.invoice.xml_content</code>). Esta opção permanece ativada.
	</div>
{/if}
<span class="switch prestashop-switch fixed-width-lg">
	<input type="radio" name="wait_nfe_xml" id="wait_nfe_xml_on" value="1"
		{if $wait_nfe_xml}checked="checked"{/if}
		{if $is_azul}disabled="disabled"{/if}>
	<label for="wait_nfe_xml_on">Sim</label>
	<input type="radio" name="wait_nfe_xml" id="wait_nfe_xml_off" value="0"
		{if !$wait_nfe_xml}checked="checked"{/if}
		{if $is_azul}disabled="disabled"{/if}>
	<label for="wait_nfe_xml_off">Não</label>
	<a class="slide-button btn"></a>
</span>
{if $is_azul}
	<input type="hidden" name="wait_nfe_xml" value="1">
{/if}
<p class="help-block">Se ativado, a emissão automática/manual espera o XML anexado no pedido antes de chamar a API.</p>

<script type="text/javascript">
(function () {
	function toggleWaitXml() {
		var selected = document.querySelector('input[name="shipment_type"]:checked');
		var group = document.querySelector('.agmelhorenvio-wait-nfe-xml-group');
		if (!group) {
			return;
		}
		var show = selected && selected.value === 'commercial';
		group.style.display = show ? '' : 'none';
	}
	document.addEventListener('change', function (e) {
		if (e.target && e.target.name === 'shipment_type') {
			toggleWaitXml();
		}
	});
	document.addEventListener('DOMContentLoaded', toggleWaitXml);
	toggleWaitXml();
})();
</script>
