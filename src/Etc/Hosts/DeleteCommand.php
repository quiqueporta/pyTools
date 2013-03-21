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
            ->addArgument('hostname', InputArgument::REQUIRED, 'Nombre del hostname que se desea eliminar')
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

        if ($this->getDialog()->askConfirmation($output, sprintf('<question>¿Deseas realmente eliminar la entrada de hosts (%s)?. (defecto: y)</question> ', $input->getArgument('hostname')), true)){
            $process = new Process(sprintf('sudo sed --in-place "/%s/d" %s', $input->getArgument('hostname'), $input->getOption('hosts-file')));
            $process->run(function($type, $buffer) use($output) {$output->writeln($buffer);});
            $output->writeln(sprintf('<info>Eliminada la entrada %s</info>', $input->getArgument('hostname')));
        }
    }

    protected function getDialog()
    {
        return $this->getHelperSet()->get('dialog');
    }

}
