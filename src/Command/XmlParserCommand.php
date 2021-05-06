<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use App\Service\XmlDataImporter;

class XmlParserCommand extends Command
{
    protected static $defaultName = 'app:parse-xml';
    protected static $defaultDescription = 'Import xml data to google spreadsheet';

    /**
     * @var XmlDataImporter $xmlDataImporter
     */
    private $xmlDataImporter;

    public function __construct(string $name = null, XmlDataImporter $xmlDataImporter)
    {
        parent::__construct($name);

        $this->xmlDataImporter = $xmlDataImporter;
    }

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription);

        $this->addArgument('file_name', InputArgument::REQUIRED, 'Xml file name');
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $filename = $input->getArgument('file_name');

        if(empty($filename)) {
            $io->error('Error: no file passed');

            return 1;
        }

        $importResult = $this->xmlDataImporter->importData($filename);
        if(!$importResult) {
            return 1;
        }

        $io->success('Successful imported xml data to spreadsheet.');

        return 0;
    }
}