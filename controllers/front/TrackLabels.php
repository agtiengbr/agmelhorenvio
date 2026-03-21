<?php

class AgMelhorEnvioTrackLabelsModuleFrontController extends ModuleFrontController
{
	public function initContent()
	{
		parent::initContent();

		/** @var AgClienteWorker */
        global $agti_worker;
        $agti_worker = new AgClienteWorker(Tools::getValue('id_agworker'));
		$agti_worker->save();

		$sql = new DbQuery;
		$sql->from('agmelhorenvio_label', 'a')
			->where('status IN ("to_be_shipped", "released", "posted", "paid", "received", "printed")')
			->innerJoin('orders', 'o', 'o.id_order=a.id_order')
			->where('id_order_remote != "" and id_order_remote IS NOT NULL')
			->orderBy('a.date_upd ASC')
			->limit(150);

		$labels = Db::getInstance()->executeS($sql);

		$orders = [];

		foreach ($labels as $label) {
			$orders[] = $label['id_order_remote'];
		}

		$agti_worker->save();
		if (!count($orders)) {
			exit();
		}
		
		$response = AgMelhorEnvioGateway::trackLabels($orders);
		$agti_worker->save();

		$labels_tracked = [];
		foreach ($response as $label) {
			$labels_tracked[] = $label->getId();

			$agti_worker->save();
			$agme_label = AgMelhorEnvioLabel::getByIdOrderRemote($label->getId());

			if (Validate::isLoadedObject($agme_label)) {
				$agme_label->status = $label->getStatus();

				$agme_label->paid_at        = $label->getPaidAt();
				$agme_label->generated_at   = $label->getGeneratedAt();
				$agme_label->posted_at      = $label->getPostedAt();
				$agme_label->delivered_at   = $label->getDeliveredAt();
				$agme_label->canceled_at    = $label->getCanceledAt();
				$agme_label->expired_at     = $label->getExpiredAt();
				$agme_label->created_at     = $label->getCreatedAt();
				$agme_label->tracking       = $label->getTracking();
				$agme_label->self_tracking  = $label->getSelfTracking();

				$agme_label->update();
			}
		}


		//as etiquetas que não foram rastreadas são marcadas em um estado de "erro" para que não voltem a ser consultadas
		foreach ($orders as $order) {
			if (!in_array($order, $labels_tracked)) {
				$agme_label = AgMelhorEnvioLabel::getByIdOrderRemote($order);
				if (Validate::isLoadedObject($agme_label)) {
					$agme_label->status = 'error';
					$agme_label->update();
				}
			}
		}
		
		exit();
	}
}
