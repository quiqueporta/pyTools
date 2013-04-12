<?php

namespace Symfony\v1;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;


class CreateProjectCommand extends Command {

    protected function configure() {
        $this
            ->setName('symfony1:project:create')
            ->setDescription('Crea un nuevo projecto de Symfony 1.x')
            ->addArgument('ProjectName', InputArgument::REQUIRED, 'ProjectName (ejemplo: MiProyecto)')
            ->addOption('base-project-tag', null, InputOption::VALUE_OPTIONAL, 'Indica la versión del royecto base que se quiere obtener (ejemplo: --base-project-tag=2_1)');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $ProjectName = $input->getArgument('ProjectName');
        $output->writeln(sprintf('<info>Creando el proyecto "%s" en Symfony 1.x</info>', $ProjectName ));

        $existe_el_directorio = false;

        do {
            $ProjectDirectory = $this->getDialog()->ask($output, sprintf('<question>Directorio donde deseas crear el proyecto (%s):</question> ',getcwd()), getcwd());

            $existe_el_directorio = file_exists($ProjectDirectory);

            if (!$existe_el_directorio) {
                $output->writeln(sprintf('<error>No existe el directorio %s</error>', $ProjectDirectory ));
            }

        } 
        while (!$existe_el_directorio);

        $tag = "aaa";

        if (!file_exists(dirname(__FILE__).'/../../../config/pytools.yml'))
        {
            $output->writeln(sprintf('<error>No se encontró el archivo %s</error>', dirname(__FILE__).'/../../config/pytools.yml'));
            exit();
        }

        try {

                $config = Yaml::parse(file_get_contents(dirname(__FILE__).'/../../../config/pytools.yml'));
                
                $process = new Process(sprintf('svn ls %s/%s/tags | sort -r | head -1', $config['svn']['repository_url'], $config['symfony_1']['base_project']));
                $process->run($tag = function($type, $buffer) use (&$tag) {
                    $tag = trim($buffer);
                });

        } catch (ParseException $e) {
            $output->writeln(sprintf('<error>Imposible parsear el archivo YAML de configuración: %s</error>', $e->getMessage()));
            exit();
        }

        if (!$BaseProjectTag = $input->getOption('base-project-tag')){
            $BaseProjectTag = $this->getDialog()->ask($output, sprintf('<question>Tag del Proyecto Base (ultimo tag: %s)</question> ',$tag), $tag);
        }

        $output->writeln('<comment>* Importando el proyecto desde svn ...</comment>');
        $process = new Process(sprintf('svn export %s/%s/tags/%s . --force',$config['svn']['repository_url'], $config['symfony_1']['base_project'], $BaseProjectTag));
        $process->run();

        $output->writeln('<comment>* Configurando el proyecto ...</comment>');
        $process = new Process(sprintf('find . -type f -exec sed -i "s/mi_proyecto/%s/g" {} \;', $ProjectName));
        $process->run();

        $output->writeln('<comment>* Generando los assets del proyecto ...</comment>');
        $process = new Process(sprintf('./symfony plugin:publish-assets', $ProjectName));
        $process->run();

        $output->writeln('<comment>* Limpiando la cache ...</comment>');
        $process = new Process(sprintf('./symfony cc', $ProjectName));
        $process->run();

        $output->writeln('<comment>* Estableciendo permisos ...</comment>');
        $process = new Process(sprintf('./symfony project:permissions', $ProjectName));
        $process->run();
    }

    protected function getDialog() {
        return $this->getHelperSet()->get('dialog');
    }
}
