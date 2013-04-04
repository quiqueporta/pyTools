<?php

namespace Tools;

use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    public function __construct()
    {
        parent::__construct('PyndooTools', '0.2');
        $this->add(new \Apache\AddCommand());
        $this->add(new \Apache\DeleteCommand());
        $this->add(new \Etc\Hosts\AddCommand());
        $this->add(new \Etc\Hosts\DeleteCommand());
        $this->add(new \Symfony\v1\CreateProjectCommand());
    }

}
