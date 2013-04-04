<?php

namespace Symfony\v1;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;


class CreateProjectCommand extends Command {

    protected function configure() {
        $this
            ->setName('symfony1:project:create')
            ->setDescription('Crea un nuevo projecto de Symfony 1.x')
            ->addArgument('ProjectName', InputArgument::REQUIRED, 'ProjectName (ejemplo: MiProyecto)')
            ->addOption('with-backend', null, InputOption::VALUE_NONE, 'Si se establece, se creará la aplicación backend.')
            ->addOption('symfony-lib-dir', null, InputOption::VALUE_OPTIONAL, 'Indica donde se encuentra la libreria de Symfony 1.x.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        # Comprobamos que la libreria se encuentra en la ubicación indicada por el usuario o por defecto.

        if (!$SymfonyLibDir = $input->getOption('symfony-lib-dir')){
            $SymfonyLibDir = $this->getDialog()->ask($output, sprintf('<question>¿Donde está ubicada la libreria de Symfony 1.x? (defecto: %s)</question> ','/usr/lib/symfony/1.4.20/'), '/usr/lib/symfony/1.4.20/');
        }

        
        if (!is_file($SymfonyLibDir.'data/bin/symfony'))
        {
            throw new LogicException(sprintf('No se puede encontrar el archivo "%s"', $input->getOption('hosts-file')));
        }
        

        $output->writeln(sprintf('<info>Creando el proyecto %s en Symfony 1.x</info>', $input->getArgument('ServerName')));
    }

    protected function getDialog() {
        return $this->getHelperSet()->get('dialog');
    }
}
