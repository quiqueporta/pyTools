<?php

namespace Etc\Hosts;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Process\Process;

class DeleteCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('etc:hosts:del')
            ->setDescription('elimina una entrada en el archivo hosts')
            ->addArgument('hostname', null, InputArgument::REQUIRED, 'pruebas.local')
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

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        do
        {
            $hostname = $this->getDialog()->ask($output, sprintf('<question>Hostname</question> (defecto: %s): ', $input->getArgument('hostname')), $input->getArgument('hostname'));
        }
        while (!$hostname);

        $input->setArgument('hostname', $hostname);

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (null === $input->getArgument('hostname'))
        {
            throw new LogicException('Debe especificar un nombre para el hostname.');
        }

        $process = new Process(sprintf('sudo sed "/%s/d" %s', $input->getArgument('hostname'), $input->getOption('hosts-file')));
        $process->run(function($type, $buffer) use($output) {$output->writeln($buffer);});
    }

    protected function getDialog()
    {
        return $this->getHelperSet()->get('dialog');
    }

}
