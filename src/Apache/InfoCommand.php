<?php

namespace Apache;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class InfoCommand extends Command {

    protected function configure() {
        $this
            ->setName('apache:vhost:info')
            ->setDescription('Muestra la información del host indicado.')
            ->addArgument('ServerName', InputArgument::REQUIRED, 'ServerName (ejemplo: ejemplo.local)');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $ServerName = $input->getArgument('ServerName');
        
        $output->writeln(sprintf("<info>%s</info>", $ServerName));
        $output->writeln(sprintf("<info>---------------------------</info>\n", $ServerName));

        $output->writeln(sprintf("<comment>Archivo vhost de %s</comment>\n", $ServerName));
        if (file_exists(sprintf('/etc/apache2/sites-available/%s', $ServerName))) {
            $process = new Process(sprintf('cat /etc/apache2/sites-available/%s', $ServerName));
            $process->run(function ($type, $buffer) use ($output) {
                $output->writeln($buffer);
            });
        } else {
            $output->writeln(sprintf("<error>El vhost %s no existe.</error>", $ServerName));
        }

        $output->writeln(sprintf("\n<comment>Archivo vhost de %s</comment>\n", $ServerName));
        $process = new Process(sprintf('grep %s /etc/hosts', $ServerName));
        $process->run(function ($type, $buffer) use ($output) {
            $output->writeln($buffer);
        });
        $output->writeln(sprintf("\n<info>---------------------------</info>\n", $ServerName));

    }

    protected function getDialog() {
        return $this->getHelperSet()->get('dialog');
    }

}

