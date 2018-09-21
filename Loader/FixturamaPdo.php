<?php

namespace Naldz\Bundle\FixturamaBundle\Fixturama;

class FixturamaPdo extends \PDO
{
    public function __construct($dsnComponents = array())
    {
        $dsn = sprintf('%s:host=%s', $dsnComponents['type'], $dsnComponents['host']);
        parent::__construct($dsn, $dsnComponents['user'], $dsnComponents['password']);
    }
}