<?php

namespace Naldz\Bundle\FixturamaBundle\Fixturama\Event;

use Symfony\Component\EventDispatcher\Event;

class FixturamaEvent extends Event
{
    const DATA_ROW_LOAD_PRE = 'fixturama.data_row.load.pre';
    const DATA_ROW_LOAD_POST = 'fixturama.data_row.load.post';
}