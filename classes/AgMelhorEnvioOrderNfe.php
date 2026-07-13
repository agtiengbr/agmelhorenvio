<?php

class AgMelhorEnvioOrderNfe extends AgObjectModel
{
    public static $definition = [
        'table' => 'agmelhorenvio_order_nfe',
        'primary' => 'id_agmelhorenvio_order_nfe',
        'multilang' => false,
        'fields' => [
            'id_agmelhorenvio_order_nfe' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'id_order' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'db_type' => 'int unsigned',
                'required' => true,
            ],
            'file_path' => [
                'type' => self::TYPE_STRING,
                'db_type' => 'varchar(512)',
                'required' => true,
            ],
            'filename' => [
                'type' => self::TYPE_STRING,
                'db_type' => 'varchar(255)',
                'required' => true,
            ],
            'nfe_key' => [
                'type' => self::TYPE_STRING,
                'db_type' => 'varchar(44)',
                'allow_null' => true,
            ],
            'filesize' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'db_type' => 'int unsigned',
                'default' => 0,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'db_type' => 'datetime',
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'db_type' => 'datetime',
            ],
        ],
        'indexes' => [
            [
                'fields' => ['id_order'],
                'prefix' => 'unique',
                'name' => 'unique_id_order',
            ],
        ],
    ];

    public $id_agmelhorenvio_order_nfe;
    public $id_order;
    public $file_path;
    public $filename;
    public $nfe_key;
    public $filesize;
    public $date_add;
    public $date_upd;

    public static function getBaseDir()
    {
        return _PS_MODULE_DIR_ . 'agmelhorenvio/var/nfe';
    }

    public static function getOrderDir($id_order)
    {
        return self::getBaseDir() . '/' . (int) $id_order;
    }

    public static function ensureStorageProtection()
    {
        $base = self::getBaseDir();
        if (!is_dir($base)) {
            @mkdir($base, 0755, true);
        }

        $htaccess = $base . '/.htaccess';
        if (!file_exists($htaccess)) {
            @file_put_contents($htaccess, "Deny from all\n");
        }

        $index = $base . '/index.php';
        if (!file_exists($index)) {
            @file_put_contents($index, "<?php\nheader('HTTP/1.0 403 Forbidden');\n");
        }
    }

    /**
     * @param int $id_order
     * @return AgMelhorEnvioOrderNfe
     */
    public static function getByIdOrder($id_order)
    {
        $sql = new DbQuery();
        $sql->from('agmelhorenvio_order_nfe')
            ->where('id_order = ' . (int) $id_order);

        $row = Db::getInstance()->getRow($sql);
        $obj = new self();
        if (is_array($row) && $row) {
            $obj->hydrate($row);
        }

        return $obj;
    }

    public function getAbsolutePath()
    {
        if (!$this->file_path) {
            return '';
        }

        if (strpos($this->file_path, '/') === 0 || preg_match('#^[A-Za-z]:\\\\#', $this->file_path)) {
            return $this->file_path;
        }

        return _PS_MODULE_DIR_ . 'agmelhorenvio/' . ltrim($this->file_path, '/');
    }

    public function fileExists()
    {
        $path = $this->getAbsolutePath();
        return $path && is_file($path) && is_readable($path);
    }

    /**
     * @return string|null
     */
    public function getXmlContent()
    {
        if (!$this->fileExists()) {
            return null;
        }

        $content = file_get_contents($this->getAbsolutePath());
        return $content === false ? null : $content;
    }

    public static function extractNfeKey($xml)
    {
        if (!is_string($xml) || $xml === '') {
            return null;
        }

        if (preg_match('/Id=\"NFe(\d{44})\"/', $xml, $m)) {
            return $m[1];
        }

        if (preg_match('/<chNFe>(\d{44})<\/chNFe>/', $xml, $m)) {
            return $m[1];
        }

        if (preg_match('/\b(\d{44})\b/', $xml, $m)) {
            return $m[1];
        }

        return null;
    }

    public static function extractNfeNumber($xml)
    {
        if (!is_string($xml) || $xml === '') {
            return null;
        }

        if (preg_match('/<nNF>(\d+)<\/nNF>/', $xml, $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * @param int $id_order
     * @param string $tmpPath
     * @param string $originalName
     * @return AgMelhorEnvioOrderNfe
     * @throws Exception
     */
    public static function saveUpload($id_order, $tmpPath, $originalName)
    {
        $id_order = (int) $id_order;
        if ($id_order <= 0) {
            throw new Exception('Pedido inválido para anexar o XML da NF-e.');
        }

        if (!$tmpPath || !is_uploaded_file($tmpPath)) {
            throw new Exception('Arquivo XML inválido ou não enviado.');
        }

        $xml = file_get_contents($tmpPath);
        if ($xml === false || trim($xml) === '') {
            throw new Exception('Não foi possível ler o conteúdo do XML.');
        }

        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($xml);
        if ($doc === false) {
            throw new Exception('O arquivo enviado não é um XML válido.');
        }

        $nfe_key = self::extractNfeKey($xml);
        self::ensureStorageProtection();

        $orderDir = self::getOrderDir($id_order);
        if (!is_dir($orderDir) && !@mkdir($orderDir, 0755, true)) {
            throw new Exception('Não foi possível criar o diretório de armazenamento do XML.');
        }

        $safeName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', (string) $originalName);
        if (!$safeName || !preg_match('/\.xml$/i', $safeName)) {
            $safeName = ($nfe_key ?: ('nfe_' . time())) . '.xml';
        }

        $relativePath = 'var/nfe/' . $id_order . '/' . $safeName;
        $absolutePath = _PS_MODULE_DIR_ . 'agmelhorenvio/' . $relativePath;

        $existing = self::getByIdOrder($id_order);
        if (Validate::isLoadedObject($existing)) {
            $oldPath = $existing->getAbsolutePath();
            if ($oldPath && is_file($oldPath) && $oldPath !== $absolutePath) {
                @unlink($oldPath);
            }
        } else {
            $existing = new self();
            $existing->id_order = $id_order;
        }

        if (!@move_uploaded_file($tmpPath, $absolutePath) && !@rename($tmpPath, $absolutePath)) {
            if (@file_put_contents($absolutePath, $xml) === false) {
                throw new Exception('Erro ao gravar o XML em disco.');
            }
        }

        $existing->file_path = $relativePath;
        $existing->filename = $safeName;
        $existing->nfe_key = $nfe_key;
        $existing->filesize = (int) @filesize($absolutePath);
        $existing->date_upd = date('Y-m-d H:i:s');
        if (!$existing->date_add) {
            $existing->date_add = $existing->date_upd;
        }

        if (!$existing->save()) {
            throw new Exception('Erro salvando metadados do XML da NF-e: ' . Db::getInstance()->getMsgError());
        }

        return $existing;
    }

    public function deleteFileAndRecord()
    {
        $path = $this->getAbsolutePath();
        if ($path && is_file($path)) {
            @unlink($path);
        }

        $dir = dirname($path);
        if ($dir && is_dir($dir)) {
            @rmdir($dir);
        }

        return $this->delete();
    }
}
