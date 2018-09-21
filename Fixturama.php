<?php

namespace Naldz\Bundle\FixturamaBundle\Fixturama;

use Naldz\Bundle\FixturamaBundle\Fixturama\FixtureGenerator;
use Naldz\Bundle\FixturamaBundle\Fixturama\FixtureLoader;

class Fixturama
{
    private $generator = null;
    private $loader = null;

    public function __construct(FixtureGenerator $generator, FixtureLoader $loader)
    {
        $this->generator = $generator;
        $this->loader = $loader;
    }

    public function setUp($dataPreset)
    {
        $dbFixtures = $this->generator->generate($dataPreset);
        $this->loader->load($dbFixtures);
    }
}