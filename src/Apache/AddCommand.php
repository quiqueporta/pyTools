<?php

namespace Apache;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class AddCommand extends Command {

    protected function configure() {
        $this
            ->setName('apache:vhost:add')
            ->setDescription('Crea un nuevo archivo de VirtualHost')
            ->addArgument('ServerName', InputArgument::OPTIONAL, 'ServerName (ejemplo: ejemplo.local)')
            ->addOption('DocumentRoot', null, InputOption::VALUE_OPTIONAL, 'DocumentRoot (ejemplo: /var/www/mi_web)')
            ->addOption('DirectoryIndex', null, InputOption::VALUE_OPTIONAL, 'DirectoryIndex (ejemplo: index.php)');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        // Se ejecuta hasta que no devuelvan un valor si no lo pasaron como parametro.
        if (!$ServerName = $input->getArgument('ServerName')){
            do {
                $ServerName = $this->getDialog()->ask($output, '<question>ServerName (ejemplo: ejemplo.local)</question> ');
            } while (!$ServerName);
        }

        if (!$DocumentRoot = $input->getOption('DocumentRoot')){
            $DocumentRoot = $this->getDialog()->ask($output, sprintf('<question>DocumentRoot (defecto: %s)</question> ',getcwd()), getcwd());
        }

        if (!$DirectoryIndex = $input->getOption('DirectoryIndex')){
            $DirectoryIndex = $this->getDialog()->ask($output, '<question>DirectoryIndex (defecto: index.php)</question> ', 'index.php');
        }

        $vhost_template = <<<EOF
<VirtualHost *:80>
  ServerName %ServerName%
  DocumentRoot %DocumentRoot%
  DirectoryIndex %DirectoryIndex%

  <Directory %DocumentRoot%>
    AllowOverride All
    Allow from All
  </Directory>

</VirtualHost>
EOF;

        $VhostFile = strtr($vhost_template,array(
            '%ServerName%' => $ServerName,
            '%DocumentRoot%' => $DocumentRoot,
            '%DirectoryIndex%' => $DirectoryIndex,
        ));

        $output->writeln($VhostFile);

        if ($this->getDialog()->askConfirmation($output, '<question>¿Quieres grabar este archivo en sites-aviable? (defecto: y)</question> ', true)){
            $filename = sprintf('/tmp/vhost-%s',time());
            file_put_contents($filename,$VhostFile);
            $process = new Process(sprintf('sudo cp %s /etc/apache2/sites-available/%s', $filename, $ServerName));
            $process->run(function($type, $buffer) use($output) {$output->writeln($buffer);});

            if ($this->getDialog()->askConfirmation($output, '<question>¿Quieres habilitar este vhost? (defecto: y)</question> ', true)){
                $process = new Process(sprintf('sudo a2ensite %s', $ServerName));
                $process->run(function($type, $buffer) use($output) {$output->writeln($buffer);});
            }
        }

        if ($this->getDialog()->askConfirmation($output, '<question>¿Quieres reiniciar el seridor de apache? (defecto: y)</question> ', true)){
            //unset($process);
            $process = new Process('sudo service apache2 restart');
            $process->run(function($type, $buffer) use($output) {$output->writeln($buffer);});

        }
    }

    protected function getDialog() {
        return $this->getHelperSet()->get('dialog');
    }

}

