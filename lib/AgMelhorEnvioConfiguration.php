<?php

class AgMelhorEnvioConfiguration
{
    protected static $email;

    protected static $handling_time;
    protected static $timeout_clear_requests;
    protected static $coupon;
    protected static $enabled_cache;
    protected static $time_expire_cache;

    protected static $shop_name;
    protected static $shop_address;
    protected static $shop_address_number;
    protected static $shop_address_district;
    protected static $shop_address_city;
    protected static $shop_address_state;
    protected static $shop_address_zipcode;
    protected static $shop_address_phone;

    protected static $cnpj;
    protected static $state_register;

    protected static $token;
    protected static $payment_mode;

    protected static $agency_jadlog;
    protected static $agency_latam;
    protected static $agency_total_express;

    protected static $sandbox_enabled;
    protected static $sandbox_email;
    protected static $sandbox_token;
    protected static $sandbox_payment_mode;

    protected static $auto_generate_labels;
    protected static $auto_generate_label_states;
    protected static $status_mapping_enabled;
    protected static $cnae;

    protected static $sendTrackingEmail;


    const PAY_IN_CASH = 0;
    const PAY_WITH_MOIP = 1;
    const PAY_WITH_MERCADOPAGO = 2;

    public static function loadConfigurations()
    {
        $config = Configuration::getMultiple(array(
            'AGMELHORENVIO_SHOP_NAME',

            'AGMELHORENVIO_CONFIGURATION_EMAIL',
            'AGMELHORENVIO_CONFIGURATION_HANDLING_TIME',
            'AGMELHORENVIO_CONFIGURATION_TIMEOUT_CLEAR_REQUESTS',
            'AGMELHORENVIO_CONFIGURATION_ENABLED_CACHE',
            'AGMELHORENVIO_CONFIGURATION_TIME_EXPIRE_CACHE',
            'AGMELHORENVIO_CONFIGURATION_SHOP_ADDRESS',
            'AGMELHORENVIO_CONFIGURATION_SHOP_ADDRESS_NUMBER',
            'AGMELHORENVIO_CONFIGURATION_SHOP_ADDRESS_DISTRICT',
            'AGMELHORENVIO_CONFIGURATION_SHOP_ADDRESS_CITY',
            'AGMELHORENVIO_CONFIGURATION_SHOP_ADDRESS_STATE',
            'AGMELHORENVIO_CONFIGURATION_SHOP_ADDRESS_ZIPCODE',
            'AGMELHORENVIO_CONFIGURATION_SHOP_ADDRESS_PHONE',

            'AGMELHORENVIO_CONFIGURATION_CNPJ',
            'AGMELHORENVIO_CONFIGURATION_CNAE',
            'AGMELHORENVIO_CONFIGURATION_STATE_REGISTER',
            'AGMELHORENVIO_CONFIGURATION_TOKEN',
            'AGMELHORENVIO_PAYMENT_MODE',
            'AGMELHORENVIO_AGENCY_JADLOG',
            'AGMELHORENVIO_AGENCY_LATAM',
            'AGMELHORENVIO_AGENCY_TOTAL_EXPRESS',

            'AGMELHORENVIO_CONFIGURATION_SANDBOX_EMAIL',
            'AGMELHORENVIO_CONFIGURATION_SANDBOX_TOKEN',
            'AGMELHORENVIO_CONFIGURATION_SANDBOX_PAYMENT_MODE',
            'AGMELHORENVIO_CONFIGURATION_SANDBOX_ENABLED',

            'AGMELHORENVIO_AUTO_GENERATE_LABELS',
            'AGMELHORENVIO_AUTO_GENERATE_LABEL_STATES',
            'AGMELHORENVIO_STATUS_MAPPING_ENABLED',
            'AGMELHORENVIO_COUPONS',

            'AGMELHORENVIO_CONFIGURATION_SEND_TRACKING_EMAIL',
        ));

        self::$email = $config['AGMELHORENVIO_CONFIGURATION_EMAIL'];
        self::$handling_time = $config['AGMELHORENVIO_CONFIGURATION_HANDLING_TIME'];
        self::$timeout_clear_requests = $config['AGMELHORENVIO_CONFIGURATION_TIMEOUT_CLEAR_REQUESTS'];
        self::$enabled_cache = $config['AGMELHORENVIO_CONFIGURATION_ENABLED_CACHE'];
        self::$time_expire_cache = $config['AGMELHORENVIO_CONFIGURATION_TIME_EXPIRE_CACHE'];
        self::$shop_name = $config['AGMELHORENVIO_SHOP_NAME'];
        self::$shop_address = $config['AGMELHORENVIO_CONFIGURATION_SHOP_ADDRESS'];
        self::$shop_address_number = $config['AGMELHORENVIO_CONFIGURATION_SHOP_ADDRESS_NUMBER'];
        self::$shop_address_district = $config['AGMELHORENVIO_CONFIGURATION_SHOP_ADDRESS_DISTRICT'];
        self::$shop_address_city = $config['AGMELHORENVIO_CONFIGURATION_SHOP_ADDRESS_CITY'];
        self::$shop_address_state = $config['AGMELHORENVIO_CONFIGURATION_SHOP_ADDRESS_STATE'];
        self::$shop_address_zipcode = $config['AGMELHORENVIO_CONFIGURATION_SHOP_ADDRESS_ZIPCODE'];
        self::$shop_address_phone = $config['AGMELHORENVIO_CONFIGURATION_SHOP_ADDRESS_PHONE'];

        self::$cnpj = $config['AGMELHORENVIO_CONFIGURATION_CNPJ'];
        self::$cnae = $config['AGMELHORENVIO_CONFIGURATION_CNAE'];
        
        self::$state_register = $config['AGMELHORENVIO_CONFIGURATION_STATE_REGISTER'];
        self::$token = $config['AGMELHORENVIO_CONFIGURATION_TOKEN'];
        self::$payment_mode = $config['AGMELHORENVIO_PAYMENT_MODE'];
        self::$agency_jadlog = $config['AGMELHORENVIO_AGENCY_JADLOG'];
        self::$agency_latam = $config['AGMELHORENVIO_AGENCY_LATAM'];
        self::$agency_total_express = $config['AGMELHORENVIO_AGENCY_TOTAL_EXPRESS'];


        self::$sandbox_enabled      = $config['AGMELHORENVIO_CONFIGURATION_SANDBOX_ENABLED'];
        self::$sandbox_email        = $config['AGMELHORENVIO_CONFIGURATION_SANDBOX_EMAIL'];
        self::$sandbox_token        = $config['AGMELHORENVIO_CONFIGURATION_SANDBOX_TOKEN'];
        self::$sandbox_payment_mode = $config['AGMELHORENVIO_CONFIGURATION_SANDBOX_PAYMENT_MODE'];

        self::$auto_generate_labels = $config['AGMELHORENVIO_AUTO_GENERATE_LABELS'];
        self::$auto_generate_label_states = self::normalizeOrderStateIds($config['AGMELHORENVIO_AUTO_GENERATE_LABEL_STATES']);
        self::$status_mapping_enabled = $config['AGMELHORENVIO_STATUS_MAPPING_ENABLED'];
        self::$coupon = $config['AGMELHORENVIO_COUPONS'];
    }

    public static function setEmail($email)
    {
        Configuration::updateValue('AGMELHORENVIO_CONFIGURATION_EMAIL', $email);
        self::$email = $email;
    }

    public static function getEmail()
    {
        return self::$email;
    }

    public static function setShopAddressNumber($number)
    {
        Configuration::updateValue('AGMELHORENVIO_CONFIGURATION_SHOP_ADDRESS_NUMBER', $number);
        self::$shop_address_number = $number;
    }

    public static function getShopAddressNumber()
    {
        return self::$shop_address_number;
    }

    public static function setShopAddressDistrict($district)
    {
        Configuration::updateValue('AGMELHORENVIO_CONFIGURATION_SHOP_ADDRESS_DISTRICT', $district);
        self::$shop_address_district = $district;
    }

    public static function getShopAddressDistrict()
    {
        return self::$shop_address_district;
    }

    public static function setShopAddress($address)
    {
        Configuration::updateValue('AGMELHORENVIO_CONFIGURATION_SHOP_ADDRESS', $address);
        self::$shop_address = $address;
    }

    public static function getShopAddress()
    {
        return self::$shop_address;
    }

    public static function setShopAddressCity($city)
    {
        Configuration::updateValue('AGMELHORENVIO_CONFIGURATION_SHOP_ADDRESS_CITY', $city);
        self::$shop_address_city = $city;
    }

    public static function getShopAddresscity()
    {
        return self::$shop_address_city;
    }

    public static function setShopAddressState($state)
    {
        Configuration::updateValue('AGMELHORENVIO_CONFIGURATION_SHOP_ADDRESS_STATE', $state);
        self::$shop_address_state = $state;
    }

    public static function getShopAddressState()
    {
        return self::$shop_address_state;
    }

    public static function setShopAddressZipcode($zipcode)
    {
        Configuration::updateValue('AGMELHORENVIO_CONFIGURATION_SHOP_ADDRESS_ZIPCODE', $zipcode);
        self::$shop_address_zipcode = $zipcode;
    }

    public static function getShopAddressZipcode()
    {
        return self::$shop_address_zipcode;
    }

    public static function setShopAddressPhone($phone)
    {
        Configuration::updateValue('AGMELHORENVIO_CONFIGURATION_SHOP_ADDRESS_PHONE', $phone);
        self::$shop_address_phone = $phone;
    }

    public static function getShopAddressPhone()
    {
        return self::$shop_address_phone;
    }

    public static function setCnpj($cnpj)
    {
        Configuration::updateValue('AGMELHORENVIO_CONFIGURATION_CNPJ', $cnpj);
        self::$cnpj = $cnpj;
    }

    public static function getCnpj()
    {
        return self::$cnpj;
    }

    public static function setStateRegister($state_register)
    {
        configuration::updateValue('AGMELHORENVIO_CONFIGURATION_STATE_REGISTER', $state_register);
        self::$state_register = $state_register;
    }

    public static function getStateRegister()
    {
        return self::$state_register;
    }


    public static function setToken($token)
    {
        $token = preg_replace('/\s+/', '', $token);

        Configuration::updateValue('AGMELHORENVIO_CONFIGURATION_TOKEN', $token);
        self::$token = $token;
    }

    public static function getToken()
    {
        return self::$token;
    }

    public static function setPaymentMode($payment_mode)
    {
        Configuration::updateValue('AGMELHORENVIO_PAYMENT_MODE', $payment_mode);
        self::$payment_mode = $payment_mode;
    }

    public static function getPaymentMode()
    {
        return self::$payment_mode;
    }

    public static function setAgencyJadlog($agency_jadlog)
    {
        Configuration::updateValue('AGMELHORENVIO_AGENCY_JADLOG', $agency_jadlog);
        self::$agency_jadlog = $agency_jadlog;
    }

    public static function getAgencyJadlog()
    {
        return self::$agency_jadlog;
    }

    public static function setHandlingTime($handling_time)
    {
        Configuration::updateValue('AGMELHORENVIO_CONFIGURATION_HANDLING_TIME', $handling_time);
        self::$handling_time = $handling_time;
    }

    public static function getHandlingTime()
    {
        return self::$handling_time;
    }

    public static function setTimeoutClearRequests($timeout_clear_requests)
    {
        Configuration::updateValue('AGMELHORENVIO_CONFIGURATION_TIMEOUT_CLEAR_REQUESTS', $timeout_clear_requests);
        self::$timeout_clear_requests = $timeout_clear_requests;
    }

    public static function getTimeoutClearRequests()
    {
        return self::$timeout_clear_requests;
    }

    public static function setEnabledCache($enabled_cache)
    {
        Configuration::updateValue('AGMELHORENVIO_CONFIGURATION_ENABLED_CACHE', $enabled_cache);
        self::$enabled_cache = $enabled_cache;
    }

    public static function getEnabledCache()
    {
        return self::$enabled_cache;
    }

    public static function setTimeExpireCache($time_expire_cache)
    {
        Configuration::updateValue('AGMELHORENVIO_CONFIGURATION_TIME_EXPIRE_CACHE', $time_expire_cache);
        self::$time_expire_cache = $time_expire_cache;
    }

    public static function getTimeExpireCache()
    {
        return self::$time_expire_cache;
    }

    public static function setSandboxEnabled($sandbox_enabled)
    {
        self::$sandbox_enabled = $sandbox_enabled;
        Configuration::updateValue('AGMELHORENVIO_CONFIGURATION_SANDBOX_ENABLED', $sandbox_enabled);
    }

    public static function getSandboxEnabled()
    {
        return self::$sandbox_enabled;
    }

    public static function setSandboxEmail($sandbox_email)
    {
        self::$sandbox_email = $sandbox_email;
        Configuration::updateValue('AGMELHORENVIO_CONFIGURATION_SANDBOX_EMAIL', $sandbox_email);
    }

    public static function getSandboxEmail()
    {
        return self::$sandbox_email;
    }

    public static function setSandboxToken($sandbox_token)
    {
        self::$sandbox_token = $sandbox_token;
        Configuration::updateValue('AGMELHORENVIO_CONFIGURATION_SANDBOX_TOKEN', $sandbox_token);
    }

    public static function getSandboxToken()
    {
        return self::$sandbox_token;
    }

    public static function setSandboxPaymentMode($payment_mode)
    {
        self::$sandbox_payment_mode = $payment_mode;
        Configuration::updateValue('AGMELHORENVIO_CONFIGURATION_SANDBOX_PAYMENT_MODE', $payment_mode);
    }

    public static function getSandboxPaymentMode()
    {
        return self::$sandbox_payment_mode;
    }

    public static function setAutoGenerateLabels($auto_generate_labels)
    {
        self::$auto_generate_labels = $auto_generate_labels;
        Configuration::updateValue('AGMELHORENVIO_AUTO_GENERATE_LABELS', $auto_generate_labels);
    }

    public static function getAutoGenerateLabels()
    {
        return self::$auto_generate_labels;
    }

    public static function setAutoGenerateLabelStates($state_ids)
    {
        $state_ids = self::normalizeOrderStateIds($state_ids);
        self::$auto_generate_label_states = $state_ids;

        Configuration::updateValue('AGMELHORENVIO_AUTO_GENERATE_LABEL_STATES', implode(',', $state_ids));
    }

    public static function getAutoGenerateLabelStates()
    {
        return self::normalizeOrderStateIds(self::$auto_generate_label_states);
    }

    /**
     * Get the value of shop_name
     */
    public static function getShopName()
    {
        return self::$shop_name;
    }

    /**
     * Set the value of shop_name
     *
     * @return  self
     */
    public static function setShopName($shop_name)
    {
        self::$shop_name = $shop_name;
        Configuration::updateValue('AGMELHORENVIO_SHOP_NAME', $shop_name);
    }

    /**
     * Get the value of agency_latam
     */
    public static function getAgencyLatam()
    {
        return self::$agency_latam;
    }

    /**
     * Set the value of agency_latam
     *
     * @return  self
     */
    public static function setAgencyLatam($agency_latam)
    {
        self::$agency_latam = $agency_latam;
        Configuration::updateValue('AGMELHORENVIO_AGENCY_LATAM', $agency_latam);
    }

    /**
     * Get the value of agency_total_express
     */
    public static function getAgencyTotalExpress()
    {
        return self::$agency_total_express;
    }

    /**
     * Set the value of agency_total_express
     *
     * @return  self
     */
    public static function setAgencyTotalExpress($agency_total_express)
    {
        self::$agency_total_express = $agency_total_express;
        Configuration::updateValue('AGMELHORENVIO_AGENCY_TOTAL_EXPRESS', $agency_total_express);
    }

    public static function setAgmelhorenvioStatusMappingEnabled($status_mapping_enabled)
    {
        self::$status_mapping_enabled = $status_mapping_enabled;
        Configuration::updateValue('AGMELHORENVIO_STATUS_MAPPING_ENABLED', $status_mapping_enabled);
    }

    public static function getAgmelhorenvioStatusMappingEnabled()
    {
        return self::$status_mapping_enabled;
    }

    public static function getCoupon()
    {
        return self::$coupon;
    }

    public static function setCoupon($coupon)
    {
        Configuration::updateValue('AGMELHORENVIO_COUPONS', $coupon);
        self::$coupon = $coupon;
    }


    /**
     * Get the value of cnae
     */ 
    public static function getCnae()
    {
        return self::$cnae;

    }

    /**
     * Set the value of cnae
     *
     * @return  self
     */ 
    public static function setCnae($cnae)
    {
        Configuration::updateValue('AGMELHORENVIO_CONFIGURATION_CNAE', $cnae);
        self::$cnae = $cnae;
    }

    /**
     * Get the value of sendTrackingEmail
     */ 
    public static function getSendTrackingEmail()
    {
        return self::$sendTrackingEmail;
    }

    /**
     * Set the value of sendTrackingEmail
     *
     * @return  self
     */ 
    public static function setSendTrackingEmail($sendTrackingEmail)
    {
        Configuration::updateValue('AGMELHORENVIO_CONFIGURATION_SEND_TRACKING_EMAIL', $sendTrackingEmail);
        self::$sendTrackingEmail = $sendTrackingEmail;
    }

    protected static function normalizeOrderStateIds($state_ids)
    {
        if (is_string($state_ids)) {
            $state_ids = array_filter(explode(',', $state_ids));
        }

        if (!is_array($state_ids)) {
            return [];
        }

        $normalized = [];
        foreach ($state_ids as $state_id) {
            $state_id = (int) $state_id;
            if ($state_id > 0) {
                $normalized[] = $state_id;
            }
        }

        return array_values(array_unique($normalized));
    }
}
