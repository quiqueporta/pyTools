<?php

namespace Apache;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class DeleteCommand extends Command {

    protected function configure() {
        $this
            ->setName('apache:vhost:del')
            ->setDescription('Elimina un VirtualHost')
            ->addArgument('ServerName', InputArgument::REQUIRED, 'ServerName (ejemplo: ejemplo.local)');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        
        $ServerName = $input->getArgument('ServerName');

        if ($this->getDialog()->askConfirmation($output, sprintf('<question>¿Seguro que quieres eliminar el vhost de %s? (defecto: y)</question> ', $ServerName), true)){
            $output->writeln('<info>Deshabilitando el sitio</info>');
            $process = new Process(sprintf('sudo a2dissite %s', $ServerName));
            $process->run(function($type, $buffer) use($output) {$output->writeln($buffer);});
            
            $output->writeln('<info>Eliminando el vhost</info>');
            $process = new Process(sprintf('sudo rm /etc/apache2/sites-available/%s', $ServerName));
            $process->run(function($type, $buffer) use($output) {$output->writeln($buffer);});

            if ($this->getDialog()->askConfirmation($output, '<question>¿Quieres eliminar la entrada en el archivo hosts? (defecto: y)</question> ', true)){
                $command = $this->getApplication()->find('etc:hosts:del');
                $arguments = array(
                    'command' => 'etc:hosts:del',
                    'hostname' => $ServerName,
                );
                $input_hosts = new ArrayInput($arguments);
                $command->run($input_hosts, $output);
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


