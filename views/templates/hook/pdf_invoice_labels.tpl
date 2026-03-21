<table width="100%" border="1" cellpadding="4" cellspacing="0">
	<tr>
		<td>
			<strong>Etiquetas (Melhor Envio)</strong>
		</td>
	</tr>

	{foreach from=$agme_invoice_labels item=label name=agme_labels}
	<tr>
		<td>
			{if $label.service_name}
				<strong>{$label.service_name|escape:'html':'UTF-8'}</strong><br>
			{/if}
			{if $label.protocol}
				Protocolo: {$label.protocol|escape:'html':'UTF-8'}<br>
			{/if}
			{if $label.tracking}
				Rastreio: {$label.tracking|escape:'html':'UTF-8'}<br>
			{/if}
			Dimensões: {$label.length|string_format:"%.2f"} x {$label.height|string_format:"%.2f"} x {$label.width|string_format:"%.2f"} cm - {$label.weight|string_format:"%.3f"} kg
		</td>
	</tr>
	{/foreach}
</table>

