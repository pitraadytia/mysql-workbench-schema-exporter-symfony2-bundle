<?php

namespace MysqlWorkbenchSchemaExporterBundle\Core;

use Doctrine\Bundle\DoctrineBundle\Command\GenerateEntitiesDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\ConvertMappingDoctrineCommand;
use \Symfony\Component\DependencyInjection\ContainerAware;
use \MwbExporter\Bootstrap;

use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Input\ArrayInput;
use \Symfony\Bundle\FrameworkBundle\Console\Application;

/**
 * Description of Schema
 *
 * @author Marc Easen <marc@easen.co.uk>
 */
class Schema extends ContainerAware
{
    /**
     * Bundle name
     *
     * @var string
     */
    protected $name = null;

    /**
     * Options
     *
     * @var string[][]
     */
    protected $options = array();

    /**
     * Bundle reflection class
     *
     * @var \Symfony\Component\HttpKernel\Bundle\Bundle
     */
    protected $bundle = null;

    /**
     * @var \Symfony\Component\Console\Application
     */
    protected $console;

    /**
     * Default formater params
     *
     * @var string[][]
     */
    protected $defaultFormatterParams = array(
        'indentation' => 4,
        'backupExistingFile' => false
    );

    /**
     * Constructor
     *
     * @param string $name
     * @param array $options
     */
    public function __construct($name, array $options)
    {
            $this->setName($name);
        $this->setOptions($options);
    }

    /**
     * Set options
     *
     * @param array $options
     * @return \MysqlWorkbenchSchemaExporterBundle\Core\Schema
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Get an option
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getOption($key, $default = null)
    {
        return array_key_exists($key, $this->options) ? $this->options[$key] : $default;
    }

    /**
     * Get the bunde name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the bundle name
     *
     * @param string $name
     * @return \MysqlWorkbenchSchemaExporterBundle\Core\Schema
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the MySQL Workbench file
     *
     * @return string
     * @throws \RuntimeException
     */
    protected function getMwbFile()
    {
        $file = $this->getBundle()->getPath() . DIRECTORY_SEPARATOR . sprintf($this->getOption('file'), $this->getName());

        if (!file_exists($file)) {
            throw new \RuntimeException(sprintf('Unable to locate the MySQL Workbench File %s', $file));
        }

        return $file;
    }

    /**
     * Get the bundle reflection class
     *
     * @return \Symfony\Component\HttpKernel\Bundle\Bundle
     */
    public function getBundle()
    {
        if (null === $this->bundle) {
            $bundle = $this->getOption('bundle');
            $kernel = $this->container->get('kernel');
            $this->bundle = $kernel->getBundle($bundle);
        }
        return $this->bundle;
    }

    /**
     * Get the root output directory
     *
     * @return string
     */
    protected function getBundleRootDir()
    {
        return $this->getBundle()->getPath() . DIRECTORY_SEPARATOR;
    }

    /**
     * Get the model output directory
     *
     * @return string
     */
    protected function getOutpuModeltDir()
    {
        return $this->getBundleRootDir() .
               $this->getOption('output-dir', 'Entity/Model');

    }

    /**
     * Get the entity output directory
     *
     * @return string
     */
    protected function getOutpuEntitytDir()
    {
        return dirname($this->getOutpuModeltDir());
    }

    /**
     * Get the config output directory
     *
     * @return string
     */
    protected function getOutpuConfigDir()
    {
        return $this->getBundleRootDir() .
        $this->getOption('config-dir', 'Resources/config');

    }

    /**
     * Get the formatter params
     *
     * @return string[][]
     */
    protected function getFormatterParams()
    {
        $params = array_merge(
            $this->defaultFormatterParams,
            array(
                'bundleNamespace' => $this->getBundle()->getNamespace()
            ),
            $this->getOption('params', array())
        );
        return $params;
    }


    private function runCmd($cmd, OutputInterface $output)
    {
        @exec($cmd, $res, $returnVar);

        if(!$returnVar) {
            foreach($res as $r) {
                $output->writeln(sprintf('<info>%s</info>', $r));
            }
        } else {
            foreach($res as $r) {
                $output->writeln(sprintf('<comment>%s</comment>', $r));
            }
        }
    }

    public function initTool()
    {
        $kernel = $this->container->get('kernel');
        $this->console = new Application($kernel);

        $this->console->setAutoExit(false);
        $this->console->setCatchExceptions(false);

        $this->console->add(new GenerateEntitiesDoctrineCommand);
        $this->console->add(new ConvertMappingDoctrineCommand);

        $self = $this;

        $this->console->register('rm')
            ->setDefinition([new InputArgument('path', InputArgument::REQUIRED)])
            ->setCode(function (InputInterface $input, OutputInterface $output) use($self) {

                    $path = $input->getArgument('path');
                    $cmd = 'rm -rf '.$path. ' 2>&1';

                    $output->writeln(sprintf('<comment>%s</comment>', $cmd));
                    $self->runCmd($cmd, $output);
                })
        ;

        $this->console->register('mkdir')
            ->setDefinition([new InputArgument('path', InputArgument::REQUIRED)])
            ->setCode(function (InputInterface $input, OutputInterface $output) use($self) {

                    $path = $input->getArgument('path');
                    $cmd = 'mkdir '.$path. ' 2>&1';
                    $output->writeln(sprintf('<comment>%s</comment>', $cmd));
                    $self->runCmd($cmd, $output);
                })
        ;

    }

    /**
     * Export
     *
     * @return string
     */
    public function export(OutputInterface $output)
    {
        $output->writeln(sprintf('Exporting "<info>%s</info>" schema', $this->getName()));

        $outputEntityDir = $this->getOutpuEntitytDir();
        $this->console->run(new ArrayInput(['command' => 'mkdir', 'path'=>$outputEntityDir]));

        $outputModelDir = $this->getOutpuModeltDir();
        $this->console->run(new ArrayInput(['command' => 'rm', 'path'=>$outputModelDir]));

        $configDoctrineDir = $this->getOutpuConfigDir().'/doctrine';

        $configDoctrineXmlDir = $configDoctrineDir.'-xml';
        $this->console->run(new ArrayInput(['command' => 'rm', 'path'=>$configDoctrineXmlDir]));

        $bootstrap = new Bootstrap();

        // define a formatter and do configuration
        $formatter = $bootstrap->getFormatter($this->getOption('formatter'));
        $formatter->setup($this->getFormatterParams());


        // load document and export
        $output->writeln(sprintf('Create Entities'));
        $document = $bootstrap->export(
            $formatter,
            $this->getMwbFile(),
            $this->getOutpuModeltDir()
        );


        $bootstrap->preCompileModels($formatter, $document);

        $options = [
            'command'     => 'doctrine:mapping:convert',
            'to-type'        => 'xml',
            'dest-path'      => $configDoctrineXmlDir,
        ];
        $this->console->run(new ArrayInput($options), $output);
        $output->writeln(sprintf('export model meta to <info>%s</info>', $configDoctrineXmlDir));

        $bootstrap->postCompileModels($formatter, $document);

    }
}
