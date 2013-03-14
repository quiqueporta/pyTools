<?php

namespace Tools;

use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    public function __construct()
    {
        parent::__construct('PyndooTools', '0.1');
        $this->add(new \Apache\AddCommand());
    }

}
