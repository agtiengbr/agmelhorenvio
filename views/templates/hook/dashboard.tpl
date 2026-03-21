<div id="dashmelhorenvio" class="panel widget">
	<header class="panel-heading">
		<i class="icon-truck"></i> Encomendas Recentes
	</header>

	<div class="tab-content panel">
		<div class="tab-pane active">
			<h3>Últimas 10 encomendas</h3>
			<div class="table-responsive">
				<table class="table">
					<thead>
						<tr>
							<th>Transportadora</th>
							<th>Custo</th>
							<th>Pedido</th>
							<th>Data</th>
							<th></th>
						</tr>
						<tbody>
							{foreach from=$shippings item=shipping}
								<tr>
									<td>{$shipping->getService()->getCarrier()->name}</td>
									<td>{displayPrice price=$shipping->shipping_cost}</td>
									<td><a href="{$shipping->getOrderBoLink()}" target="_blank">{$shipping->getOrder()->reference}</a></td>
									<td>{dateFormat date=$shipping->date_add}</td>
									<td class="text-right"><a href="{$shipping->approval_url}" target="_blank" class="btn btn-default"><i class="icon-check"></i>Aprovar</a></td>		
								</tr>
							{/foreach}
						</tbody>
				</table>
			</div>
		</div>
	</div>
</div>