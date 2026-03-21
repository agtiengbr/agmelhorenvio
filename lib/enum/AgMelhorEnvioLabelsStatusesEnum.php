<?php
class AgMelhorEnvioLabelsStatusesEnum
{
	const PENDING = 'pending';
	const RELEASED = 'released';
	const TO_BE_GENERATED = 'to_be_generated';
	const CANCELED = 'canceled';
	const DELIVERED = 'delivered';
	const PAID = 'paid';
	const RECEIVED = 'received';
	const TO_BE_SHIPPED = 'to_be_shipped';
	CONST SHIPPED = 'posted';
	const PRINTED = 'printed';

	const status_names = [
		self::PENDING => 'Aguardando Pagamento',
		self::RELEASED => 'Pronta para Impressão',
		self::TO_BE_GENERATED => 'Aguardando a Geração da Etiqueta',
		self::CANCELED => 'Cancelada',
		self::DELIVERED => 'Entregue',
		self::PAID => 'Pagamento Aprovado',
		self::RECEIVED => 'Recebida na transportadora',
		self::TO_BE_SHIPPED => 'Aguardando Postagem',
		self::SHIPPED => 'Aguardando Postagem',
		self::PRINTED => 'Etiqueta Impressa'
	];

	public static function getAll()
	{
		return [
			'pending',
			'released',
			'to_be_generated',
			'canceled',
			'delivered',
			'paid',
			'received',
			'to_be_shipped',
			'posted',
			'printed'
		];
	}
}
