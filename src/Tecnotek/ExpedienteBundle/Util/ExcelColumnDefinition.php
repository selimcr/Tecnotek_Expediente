<?php
namespace Tecnotek\ExpedienteBundle\Util;

class ExcelColumnDefinition {

    const TYPE_SINGLE_ENTRY = 1;
    const TYPE_PERCENTAGE = 2;
    const TYPE_AVERAGE = 3;

    private $column;
    private $type;
    private $config;
    private $entryId;

    public function __construct($entryId, $column, $type, $config) {
        $this->column = $column;
        $this->entryId = $entryId;
        $this->type = $type;
        $this->config = $config;
    }

    /**
     * @return mixed
     */
    public function getEntryId()
    {
        return $this->entryId;
    }

    /**
     * @param mixed $entryId
     */
    public function setEntryId($entryId)
    {
        $this->entryId = $entryId;
    }

    /**
     * @return mixed
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * @param mixed $column
     */
    public function setColumn($column)
    {
        $this->column = $column;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param mixed $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }
}