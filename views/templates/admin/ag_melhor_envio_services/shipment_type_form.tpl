{if !$allows_non_commercial}
	<div class="alert alert-info">
		Este serviço exige NF-e na API do Melhor Envio (<code>requirements: invoice</code>).
		Por isso as opções <strong>Não comercial</strong> e <strong>Híbrido</strong> ficam indisponíveis.
	</div>
{/if}

<div class="radio">
	<label>
		<input type="radio" name="shipment_type" value="commercial" {if $shipment_type == 'commercial'}checked="checked"{/if}>
		Comercial (com NF-e)
	</label>
</div>
<div class="radio">
	<label {if !$allows_non_commercial}class="text-muted"{/if}>
		<input type="radio" name="shipment_type" value="non_commercial"
			{if $shipment_type == 'non_commercial'}checked="checked"{/if}
			{if !$allows_non_commercial}disabled="disabled"{/if}>
		Não comercial (sem NF-e)
	</label>
</div>
<div class="radio">
	<label {if !$allows_non_commercial}class="text-muted"{/if}>
		<input type="radio" name="shipment_type" value="hybrid"
			{if $shipment_type == 'hybrid'}checked="checked"{/if}
			{if !$allows_non_commercial}disabled="disabled"{/if}>
		Híbrido (usar NF-e se existirem os dados)
	</label>
</div>
