<?php

namespace MysqlWorkbenchSchemaExporterBundle\Core;

use \Symfony\Component\DependencyInjection\ContainerAware;
use \MwbExporter\Bootstrap;
use \Symfony\Component\Console\Output\OutputInterface;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Input\ArrayInput;
use \Symfony\Component\Console\Application;

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

        w($this);

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
    protected function getOutputRootDir()
    {
        return $this->getBundle()->getPath() . DIRECTORY_SEPARATOR;


    }

    /**
     * Get the entity output directory
     *
     * @return string
     */
    protected function getOutpuEntitytDir()
    {
        return $this->getOutputRootDir() .
               $this->getOption('output', 'Entity/');

    }

    /**
     * Get the config output directory
     *
     * @return string
     */
    protected function getOutpuConfigDir()
    {
        return $this->getOutputRootDir() .
        $this->getOption('config', 'Config/');

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

    public function initTool()
    {
        $kernel = $this->container->get('kernel');
        $this->console = new Application($kernel);
        $this->console->setAutoExit(false);

        $this->console->register('rm')
            ->setDefinition([new InputArgument('path', InputArgument::REQUIRED)])
            ->setCode(function (InputInterface $input, OutputInterface $output) {

                    $path = $input->getArgument('path');
                    $cmd = 'rm -rf '.$path. ' 2>&1';

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

w(
    $this->getOutputRootDir(),
    $this->getOutpuEntitytDir(),
    $this->getOutpuConfigDir()
);
        $configDoctrineDir = $this->getOutpuConfigDir().'doctrine/';
        $this->console->run(new ArrayInput(['command' => 'rm', 'path'=>$configDoctrineDir]));

        $outputDir = $this->getOutpuEntitytDir();
        $this->console->run(new ArrayInput(['command' => 'rm', 'path'=>$outputDir]));




        //rm -rf ${DIR}/src/Sandbox/GeneratedBundle/Resources/config/doctrine
        //rm -rf ${DIR}/src/Sandbox/GeneratedBundle/Entity

        exit;
        $bootstrap = new Bootstrap();

        // define a formatter and do configuration
        $formatter = $bootstrap->getFormatter($this->getOption('formatter'));
        $formatter->setup($this->getFormatterParams());

        // load document and export
        $document = $bootstrap->export(
            $formatter,
            $this->getMwbFile(),
            $this->getOutputDir()
        );
        // show the output
        return $document->getWriter()->getStorage()->getResult();
    }
}
