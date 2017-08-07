<?php

namespace Archivr\Cli;

class Application extends \Symfony\Component\Console\Application
{
    const LOGO =  <<<TXT
   ___           __   _      ___ 
  / _ | ________/ /  (_)  __/ _ \
 / __ |/ __/ __/ _ \/ / |/ / , _/
/_/ |_/_/  \__/_//_/_/|___/_/|_| 


TXT;

    public function getHelp()
    {
        return static::LOGO . parent::getHelp();
    }
}
