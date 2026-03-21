<?php

class AgMelhorEnvioRangeCep extends AgObjectModel
{
	public static $definition = [
		'table'     => 'agmelhorenvio_range_cep',
        'primary'   => 'id_agmelhorenvio_range_cep',
        'multilang' => false,
        'fields'    => [
            'id_agmelhorenvio_range_cep'=> ['type' => self::TYPE_INT,    'db_type' => 'int',         'validate' => 'isInt'],
            'id_agmelhorenvio_discount'=> ['type' => self::TYPE_INT,    'db_type' => 'int',         'validate' => 'isInt'],
            'region' =>       ['type' => self::TYPE_STRING,    'db_type' => 'varchar(80)'],
            'state' =>        ['type' => self::TYPE_STRING,    'db_type' => 'varchar(80)'],
            'city' =>         ['type' => self::TYPE_STRING,    'db_type' => 'varchar(80)'],
            'neighborhood' => ['type' => self::TYPE_STRING,    'db_type' => 'varchar(80)'],
            'cep_start' =>    ['type' => self::TYPE_INT,    'db_type' => 'int'],
            'cep_end' =>      ['type' => self::TYPE_INT,    'db_type' => 'int'],
        ]
	];

	public $id_agmelhorenvio_range_cep;
    public $id_agmelhorenvio_discount;
    public $region;
    public $state;
	public $city;
	public $neighborhood;
    public $cep_start;
    public $cep_end;

    public static function deleteByDiscount(AgMelhorEnvioDiscount $discount)
    {
        Db::getInstance()->delete('agmelhorenvio_range_cep', 'id_agmelhorenvio_discount=' . $discount->id);
    }

    public static function getByDiscount(AgMelhorEnvioDiscount $discount)
    {
        $sql = new DbQuery;
        $sql->from('agmelhorenvio_range_cep')
            ->where('id_agmelhorenvio_discount=' . (int)$discount->id);

        $dbData = Db::getInstance()->executeS($sql);

        $ret = [];
        foreach ($dbData as $row) {
            $obj = new AgMelhorEnvioRangeCep;
            $obj->hydrate($row);

            $ret[] = $obj;
        }

        return $ret;
    }


    
    //adiciona a validação para verificar CEPS em mais de um intervalo ou região
    public function validateFields($die = true, $error_return = false)
    {        
        if (!parent::validateFields($die, $error_return)) {
            return false;
        }

        //carrega o desconto referente a essa faixa de ceps
        $discount= new AgMelhorEnvioDiscount($this->id_agmelhorenvio_discount);

        //carrega todos os descontos do mesmo serviço que tenham interseçção com o intervalo de preços do carrinho
        $sql = new DbQuery;
        $sql->from('agmelhorenvio_discount')
            ->where('id_agmelhorenvio_service=' . (int)$discount->id_agmelhorenvio_service)
            ->where('cart_value_end >= ' . (float)$discount->cart_value_begin)
            ->where('cart_value_begin <= '. (float)$discount->cart_value_end);

        $discounts = Db::getInstance()->executeS($sql);

        //separa os ids dos descontos
        $ids = [];
        foreach ($discounts as $discount) {
            $ids[] = (int) $discount['id_agmelhorenvio_discount'];
        }

        //verifica se há alguma faixa de CEP em algum dos descontos acima
        //com intersecção entre as faixas de CEP da regra atual
        //e que não seja a própria regra que está sendo salva (caso de um UPDATE)

        $sql = new DbQuery();
        $sql->from('agmelhorenvio_range_cep')
            ->where("id_agmelhorenvio_discount IN (" . implode(',', $ids) . ')')
            ->where('cep_end >= ' . (int) $this->cep_start)
            ->where('cep_start <= ' . (int)$this->cep_end);

        //se for um update, exclui o ID da faixa atual
        if ($this->id) {
            $sql->where('id_agmelhorenvio_range != ' . (int)$this->id);
        }

        $ranges = Db::getInstance()->getRow($sql);
        if ($ranges) {
            if ($die) {
                $discountConflicted = new AgMelhorEnvioDiscount($ranges['id_agmelhorenvio_service']);
                throw new PrestaShopException("O intervalo de {$this->cep_start} a {$this->cep_end} conflita com uma faixa de CEP do desconto desconto {$discountConflicted->alias}.");
            }

            return false;
        }

        return true;
    }
}
