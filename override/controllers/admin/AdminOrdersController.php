<?php
class AdminOrdersController extends AdminOrdersControllerCore
{
	public function displayCreateMelhorEnvioLabelLink($token, $id)
	{
		return "<a class='agmelhorenvio-generate-label'><i class='icon-truck'></i> Criar Etiqueta do Melhor Envio</a>";
	}
}
