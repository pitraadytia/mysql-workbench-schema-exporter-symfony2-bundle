<?php

namespace MysqlWorkbenchSchemaExporterBundle\Command;

use MysqlWorkbenchSchemaExporterBundle\Service\Exporter;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Dumps assets to the filesystem.
 *
 * @author Marc Easen <marc@easen.co.uk>
 */
class ExportWithRepositoryCommand extends ContainerAwareCommand
{
    /**
     * Verbose flag.
     *
     * @var bool
     */
    protected $verbose;

    /**
     * Configure method.
     */
    protected function configure()
    {
        $this
            ->setName('mysqlworkbenchschemaexporter:withRepository')
            ->setDescription('Dumps configured workbench files into the correct schemas')
        ;
    }

    /**
     * Initialise.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->verbose = $input->getOption('verbose');
    }

    /**
     * Execute method.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('Dumping all <comment>%s</comment> mysql workbench schemas.', $input->getOption('env')));
        $output->writeln('');

        $exporter = $this->getContainer()->get('mysql_workbench_schema_exporter.exporter');
        $exporter->exportWithRepository($output);
    }
}
