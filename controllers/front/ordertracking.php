<?php
class AgMelhorEnvioOrderTrackingModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $orderId = (int) Tools::getValue('id_order');
        $order = new Order($orderId);
        if (!Validate::isLoadedObject($order)) {
            // opcional: 404 ou apenas sair silenciosamente
            Tools::redirect('index.php');
        }

        // Buscar dados de rastreio das etiquetas do pedido
        $sql = new DbQuery();
        $sql->select('tracking, self_tracking, date_upd')
            ->from('agmelhorenvio_label')
            ->where('id_order = ' . (int) $orderId)
            ->orderBy('date_upd DESC');
        $rows = Db::getInstance()->executeS($sql);

        // Adapta para o template: um tracking principal e "eventos" se houver
        $trackingInfo = null;
        $trackingEvents = [];
        if (is_array($rows) && count($rows)) {
            foreach ($rows as $r) {
                $code = !empty($r['tracking']) ? $r['tracking'] : $r['self_tracking'];
                if ($code) {
                    if ($trackingInfo === null) {
                        $trackingInfo = $code;
                    }
                    $trackingEvents[] = [
                        'tracking_code' => $code,
                        'date_add' => $r['date_upd'],
                    ];
                }
            }
        }

        $this->context->smarty->assign([
            'order' => $order,
            'trackingInfo' => $trackingInfo,
            'trackingEvents' => $trackingEvents,
        ]);

        // Reutiliza o mesmo padrão do agcorreios: um tpl de listagem de rastreio
        // Se desejar, podemos criar um tpl próprio; por ora, deixamos um básico
        $this->setTemplate('module:agmelhorenvio/views/templates/front/rastreio.tpl');
    }
}
