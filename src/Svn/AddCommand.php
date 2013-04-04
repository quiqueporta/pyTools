<?php

namespace Svn;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class AddCommand extends Command {

    protected function configure() {
        $this
            ->setName('svn:add')
            ->setDescription('Crea un repositorio de SVN en la maquina remota.')
            ->addArgument('ProjectName', InputArgument::REQUIRED, 'ProjectName (ejemplo: mi_proyecto)')
            ->addArgument('DefaultUserAccess', InputArgument::REQUIRED, 'DefaultUserAccess (ejemplo: quique)');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln(sprintf('<info>Creando un repositorio de SVN para el proyecto: %s</info>', $input->getArgument('ProjectName')));

        $ProjectName = $input->getArgument('ProjectName');
        $DefaultUserAccess = $input->getArgument('DefaultUserAccess');

        if ($this->getDialog()->askConfirmation($output, '<question>¿Realmente desea crear el repositorio? (defecto: y)</question> ', true)){

            try {

                $config = Yaml::parse(file_get_contents(dirname(__FILE__).'/../../config/pytools.yml'));

                $process = new Process(sprintf('ssh %s@%s -p %s ./%s %s %s', $config['ssh']['username'], $config['ssh']['servername'], $config['ssh']['port'], $config['ssh']['remote_svn_script'], $ProjectName, $DefaultUserAccess));
                $process->run(function($type, $buffer) use($output) {$output->writeln($buffer);});

                if ($this->getDialog()->askConfirmation($output, '<question>¿Quieres traerte la copia del trunk a la carpeta actual? (defecto: n)</question> ', false)){
                    $process = new Process(sprintf('svn co %s/%s/trunk .', $config['svn']['repository_url'], $ProjectName));
                    $process->run(function($type, $buffer) use($output) {$output->writeln($buffer);});

                }

            } catch (ParseException $e) {
                $output->writeln(sprintf('<error>Imposible parsear el archivo YAML de configuración: %s</error>', $e->getMessage()));
            }

        }


    }

    protected function getDialog() {
        return $this->getHelperSet()->get('dialog');
    }

}


