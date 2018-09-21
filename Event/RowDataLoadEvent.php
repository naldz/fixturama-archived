<?php

namespace Naldz\Bundle\FixturamaBundle\Fixturama\Event;

use Symfony\Component\EventDispatcher\Event;

class RowDataLoadEvent extends Event
{
    private $modelName;
    private $data;

    public function __construct($modelName, $data)
    {
        $this->modelName = $modelName;
        $this->data = $data;
    }

    public function getModelName()
    {
        return $this->modelName;
    }

    public function getData()
    {
        return $this->data;
    }
}