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

class CheckExternalsCommand extends Command {

    private $exclude_dirs;
    private $file;

    protected function configure() {
        $this
            ->setName('svn:check-externals')
            ->setDescription('Comprueba todos los externals de un repositorio.')
            ->addArgument('Filename', InputArgument::REQUIRED, 'El nombre del archivo donde se exportarán los resultados.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

            try {

                $this->filename = fopen($input->getArgument('Filename'), 'w');

                $config = Yaml::parse(file_get_contents(dirname(__FILE__).'/../../config/pytools.yml'));

                $this->exclude_dirs = $config['svn']['externals-ignore'];

                $directorios = "";

                $process = new Process(sprintf("ssh %s@%s -p %s ls -l %s | egrep '^d' | awk '{ print $8 }'", $config['ssh']['username'], $config['ssh']['servername'], $config['ssh']['port'], $config['svn']['repository_dir']));
                $process->run(function($type, $buffer) use(&$directorios) {$directorios = $buffer;});

                foreach(preg_split("/((\r?\n)|(\r\n?))/", $directorios) as $directorio) {
                    $this->checkExternals($config['svn']['repository_url'], $directorio, $output);
                }

                fclose($this->filename);

            } catch (ParseException $e) {
                $output->writeln(sprintf('<error>Imposible parsear el archivo YAML de configuración: %s</error>', $e->getMessage()));
            }

    }

    private function checkExternals($path, $directorio_padre, OutputInterface $output) {

        $output->writeln(sprintf("<comment>%s/%s</comment>", $path, $directorio_padre));

        $directorios = "";
        $process = new Process(sprintf("svn ls %s/%s | grep '/$'", $path, $directorio_padre));
        $process->run(function($type, $buffer) use(&$directorios) {$directorios = $buffer;});

        if (substr($directorios, 0, 4) != "svn:") {
            foreach(preg_split("/((\r?\n)|(\r\n?))/", $directorios) as $directorio) {
                $directorio = str_replace("/","",$directorio);
                if (!in_array($directorio, $this->exclude_dirs)) {
                    $output->writeln(sprintf("<info>%s</info>", $directorio));
                    if (trim($directorio)!="") {
                        if ($directorio == "plugins") {
                            $externals = "";
                            $process = new Process(sprintf("svn pget svn:externals %s/%s/%s", $path, $directorio_padre, $directorio));
                            $process->run(function($type, $buffer) use(&$externals, $output) {
                                $output->writeln(sprintf("<info>%s</info>", $buffer));
                                $externals = $buffer;
                            });

                            foreach(preg_split("/((\r?\n)|(\r\n?))/", $externals) as $external) {
                                $external = trim($external);
                                if ($external!="") {
                                    fwrite($this->filename, sprintf("%s/%s/%s;%s\n", $path, $directorio_padre, $directorio, $external));
                                }
                            }

                        } else {
                            $this->checkExternals(sprintf("%s/%s", $path, $directorio_padre), $directorio, $output); 
                        }
                    }
                }
            }
        }
    }

    protected function getDialog() {
        return $this->getHelperSet()->get('dialog');
    }

}


