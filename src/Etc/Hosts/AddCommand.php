<?php

namespace Etc\Hosts;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Process\Process;

class AddCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('etc:hosts:add')
            ->setDescription('Añade una entrada en el archivos hosts')
            ->addArgument('hostname', InputArgument::REQUIRED, 'El hostname que se quiere agregar, ejemplo pruebas.local')
            ->addOption('ip', null, InputOption::VALUE_REQUIRED, "Direccion IP, ejempplo 127.0.0.1", '127.0.0.1')
            ->addOption('hosts-file', null, InputOption::VALUE_REQUIRED, 'Ubicacion del archivo hosts', '/etc/hosts')
            ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        if (!is_file($input->getOption('hosts-file')))
        {
            throw new LogicException(sprintf('No se puede encontrar el archivo "%s"', $input->getOption('hosts-file')));
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        do
            {
                $ip = $this->getDialog()->ask($output, sprintf('<question>¿Cual es la dirección IP?</question> (defecto: %s): ', $input->getOption('ip')), $input->getOption('ip'));
            }
        while (!$ip);

        $input->setOption('ip', $ip);

        $process = new Process(sprintf('echo "%s %s" | sudo tee --append %s', $input->getOption('ip'), $input->getArgument('hostname'), $input->getOption('hosts-file')));

        $process->run(function($type, $buffer) use($output) {$output->writeln($buffer);});
        $output->writeln(sprintf('<info>Añadida la entrada %s %s al archivo %s</info>', $input->getOption('ip'), $input->getArgument('hostname'), $input->getOption('hosts-file')));


    }

    protected function getDialog()
    {
        return $this->getHelperSet()->get('dialog');
    }

}
