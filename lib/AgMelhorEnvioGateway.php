<?php
class AgMelhorEnvioGateway
{
    protected static $token;
    protected static $is_sandbox;
    protected static $cache_config;
    protected static $shipping_cost_cache = array();

    public static function setToken($token)
    {
        self::$token = $token;
    }

    public static function setSandbox($is_sandbox)
    {
        self::$is_sandbox = $is_sandbox;
    }

    public static function setCacheConfig($enabled_cache, $time_clean)
    {
        self::$cache_config['enabled_cache'] = $enabled_cache;
        self::$cache_config['time_expire'] = $time_clean;
    }

    /**
     *  @throws AgMelhorEnvioCommunicatorResponseCodeException Código de retorno é maior ou igual a 400
     *  @throws AgMelhorEnvioMissingArgumentsException parâmetros de autenticação não foram informados
     */
    public static function getCarriers()
    {
        $cache_file = _PS_MODULE_DIR_ . 'agmelhorenvio/cache/getCarriers';
        //cache de uma semana..
        if (file_exists($cache_file) && filemtime($cache_file) > time() - 7 * 24 * 60 * 60) {
            return unserialize(file_get_contents($cache_file));
        }

        $getter = new AgMelhorEnvioServicesGetter(self::$is_sandbox);
        $getter->setToken(self::$token);
        $r = $getter->getServices();

        file_put_contents($cache_file, serialize($r));
        return $r;
    }

    public static function simulateShipping(
        AgMelhorEnvioRemoteAddress $from,
        AgMelhorEnvioRemoteAddress $to,
        AgMelhorEnvioRemoteOptions $options,
        array $products,
        array $package,
        array $services = [],
        $cache = true
    ) {
        $cache_keys = [];
        $uncached_data = [];


        if ($cache && self::$cache_config['enabled_cache']) {
            // concatenada todas as informações para obter as chaves
            $cache_keys = AgMelhorEnvioCache::GetCacheKeys($from, $to, $services, $options, $products);

            //IDS temporarios
            $original_ids = [];
            // cria os IDs temporarios para quando houver produtos onde a cache_key seja igual, com isso será apenas necessário trocar os IDs temporarios pelos originais antes de retorna os dados armazenados no banco
            foreach ($products as $i => $product) {
                $original_ids[$i + 1] = $product['id'];
                $products[$i]['id'] = $i + 1;
            }

            // concatenada todas as informações para obter as chaves
            $cached_data = [];
            /** 
             * verifica na variavel do cache se há dados com a chave consultada
             *  caso exista, salva na variavel cached_data
             *  se não existir salva o id do serviço na variavel uncached_data para ser consultado depois
             */
            $current_time = strtotime(date('Y-m-d H:i:s'));
            foreach ($cache_keys as $index => $key) {
                if (isset(self::$shipping_cost_cache[$key])) {
                    if ((self::$cache_config['time_expire'] > 0 || self::$shipping_cost_cache[$key]['time_expire'] > 0) && $current_time >= self::$shipping_cost_cache[$key]['time_expire']) {
                        unset(self::$shipping_cost_cache[$key]['data']);
                        $uncached_data[] = (string) $index;
                    } else {
                        if (
                            method_exists(self::$shipping_cost_cache[$key]['data'], 'getIdService') &&
                            self::$shipping_cost_cache[$key]['data']->getIdService() > 0
                        ) {
                            $cached_data[] = self::ReturnProductIDsPackages(self::$shipping_cost_cache[$key]['data'], $original_ids);
                        }
                    }
                } else {
                    $uncached_data[] = (string) $index;
                }
            }

            // verifica se não há dados sem estarem na variavel do cache
            if (count($cached_data) > 0 && count($uncached_data) === 0) {
                return $cached_data;
            } else {
                /** 
                 * caso exista serviço que foram inseridos na variavel uncached_data, eles serão buscados no banco
                 */
                $services = $uncached_data;

                $uncached_data = [];
                $arr_caching_cost = [];
                $all_data = AgMelhorEnvioCache::get($cache_keys, $services);

                // percorre todos os dados obtidos no banco
                foreach ($all_data as $dbData) {
                    if (\Validate::isLoadedObject($dbData)) {
                        if (self::$cache_config['time_expire'] > 0 && $current_time >= (strtotime($dbData->date_add) + self::$cache_config['time_expire'])) {
                            $dbData->delete();
                        } else {
                            $service = AgMelhorEnvioService::getByIdRemote($dbData->id_remote);
                            $shipping_costs = json_decode($dbData->shipping_cost_data, true);
                            $shipping_price = $shipping_costs['price'];
                            $shipping_discount = $shipping_costs['discount'];

                            if ($shipping_price > 0) {
                                // id_service(remote), price, discount, delivery time
                                $row = new AgMelhorEnvioRemoteShippingResponse;
                                $row->setIdService($dbData->id_remote)
                                    ->setName($service->service_name)
                                    ->setPrice($shipping_price)
                                    ->setDiscount($shipping_discount);

                                if (isset($dbData->delivery_time)) {
                                    $row->setDeliveryTime($dbData->delivery_time);
                                }
                                //pacotes
                                foreach ($shipping_costs['packages'] as $package) {
                                    $package_obj = new AgMelhorEnvioRemotePackage;

                                    $package_obj->setInsuranceValue($package['insurance_value'])
                                        ->setWeight($package['weight'])
                                        ->setWidth(isset($package['dimensions']->width) ? $package['dimensions']->width : $package['width'])
                                        ->setHeight(isset($package['dimensions']->height) ? $package['dimensions']->height : $package['height'])
                                        ->setLength(isset($package['dimensions']->length) ? $package['dimensions']->length : $package['length'])
                                        ->setFormat($package['format'])
                                        ->setPrice(@$package['price'])
                                        ->setDiscount(@$package['discount'])
                                        ->setDeliveryTime(@$package['delivery_time']);

                                    if (isset($package['products'])) {
                                        foreach ($package['products'] as $product) {
                                            $package_obj->addProduct((object) $product);
                                        }
                                    }

                                    $row->addPackage($package_obj);
                                }

                                //opções adicionais (AR, VD, MP, CL)
                                foreach ($shipping_costs['additional_services'] as $name => $value) {
                                    $opt = new AgMelhorEnvioRemoteOption;
                                    $opt->setName($name)
                                        ->setValue($value);

                                    $row->addAdditionalService($opt);
                                }

                                self::$shipping_cost_cache[$cache_keys[$dbData->id_remote]]['data'] = $row;
                                self::$shipping_cost_cache[$cache_keys[$dbData->id_remote]]['time_expire'] = self::$cache_config['time_expire'] > 0 ? strtotime($dbData->date_add) + self::$cache_config['time_expire'] : 0;

                                $row = self::ReturnProductIDsPackages($row, $original_ids);
                                $arr_caching_cost[] = $row;
                            }

                            if (($key = array_search($dbData->id_remote, $services)) !== false) {
                                unset($services[$key]);
                            }
                        }
                    }
                }
            }

            $cached_data = array_merge($cached_data, $arr_caching_cost);
            if (count($services) === 0) {
                return $cached_data;
            }
        }

        /**
         * caso ainda assim ainda existam serviços na variavel services os mesmo serão consultados no MelhorEnvio
         */
        $simulator = new AgMelhorEnviosShippingSimulator(self::$is_sandbox);
        $simulator->setToken(self::$token);
        $services = array_values($services);

        $simulate_shipping = $simulator->simulateShipping($from, $to, $options, $products, $package, $services);

        if ($cache && self::$cache_config['enabled_cache']) {
            foreach ($simulate_shipping as $melhorenvio_data) {
                /** 
                 * @var AgMelhorEnvioRemoteShippingResponse
                 * 
                 */

                $save_db = AgMelhorEnvioCache::saveCache($cache_keys, $melhorenvio_data);
                if (\Validate::isLoadedObject($save_db)) {
                    self::$shipping_cost_cache[$cache_keys[$melhorenvio_data->getIdService()]]['data'] = $melhorenvio_data;
                    self::$shipping_cost_cache[$cache_keys[$melhorenvio_data->getIdService()]]['time_expire'] = self::$cache_config['time_expire'] > 0 ? strtotime(date('Y-m-d H:i:s')) + self::$cache_config['time_expire'] : 0;

                    $melhorenvio_data = self::ReturnProductIDsPackages($melhorenvio_data, $original_ids);

                    $cached_data[] = $melhorenvio_data;
                    if (($key = array_search($melhorenvio_data->getIdService(), $services)) !== false) {
                        unset($services[$key]);
                    }
                }
            }

            if (count($services) > 0) {
                foreach ($services as $service) {
                    $services_without_response = new AgMelhorEnvioRemoteShippingResponse;
                    $services_without_response->setIdService($service)
                        ->setPrice(-1)
                        ->setDiscount(-1)
                        ->setDeliveryTime(-1);

                    $save_db = AgMelhorEnvioCache::saveCache($cache_keys, $services_without_response);
                }
            }

            $simulate_shipping = $cached_data;
        }

        return $simulate_shipping;
    }

    static function ReturnProductIDsPackages($data, $original_ids)
    {
        $products = [];
        // volta os IDs originais dos produtos
        if (method_exists($data, 'getPackages')) {
            foreach ($data->getPackages() as $revert_products_id) {
                $products = $revert_products_id->getProducts();
                foreach ($products as $product) {
                    if (isset($original_ids[$product->id])) {
                        $product->id = $original_ids[$product->id];
                    }
                }
            }
        }

        return $data;
    }

    public static function addShippingToCart(AgMelhorEnvioRemoteCartItem $item)
    {
        $service = new AgMelhorEnvioShippingAddToCart(self::$is_sandbox);;
        $service->setToken(self::$token);
        return $service->addToCart($item);
    }

    public static function buyLabels(
        $orders = array(),
        $payment_mode = 0,
        $redirect_url = "",
        $wallet = 0
    ) {
        $service = new AgMelhorEnvioShippingBuyer(self::$is_sandbox);;
        $service->setToken(self::$token);

        return $service->buy($orders, $payment_mode, $redirect_url, $wallet);
    }

    public static function previewLabels($orders = array())
    {
        $service = new AgMelhorEnvioShippingPreviewer(self::$is_sandbox);;
        $service->setToken(self::$token);

        return $service->preview($orders);
    }

    public static function generateLabels($orders = array())
    {
        $service = new AgMelhorEnvioLabelsGenerator(self::$is_sandbox);;
        $service->setToken(self::$token);

        return $service->generate($orders);
    }

    public static function printLabels($orders = array())
    {
        $service = new AgMelhorEnvioShippingPrinter(self::$is_sandbox);;
        $service->setToken(self::$token);

        return $service->preview($orders);
    }

    public static function trackLabels($orders = array())
    {
        $service = new AgMelhorEnvioShippingTracker(self::$is_sandbox);;
        $service->setToken(self::$token);

        return $service->track($orders);
    }

    public static function cancelLabels($orders = array())
    {
        $service = new AgMelhorEnvioShippingCancellation(self::$is_sandbox);;
        $service->setToken(self::$token);
        return $service->cancel($orders);
    }

    public static function getAgencies()
    {
        $cache_file = _PS_MODULE_DIR_ . 'agmelhorenvio/cache/getAgencies';
        if (file_exists($cache_file) && filemtime($cache_file) > time() - 24 * 60 * 60) {
            return unserialize(file_get_contents($cache_file));
        }

        $service = new AgMelhorEnvioAgenciesGetter(self::$is_sandbox);
        $r = $service->get();


        file_put_contents($cache_file, serialize($r));
        return $r;
    }
}
