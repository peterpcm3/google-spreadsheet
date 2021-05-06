<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use \SimpleXMLElement;

use App\Service\XmlParser;
use App\Service\GoogleSpreadSheet;

/**
 * Class XmlDataImporter
 * Import a xml data to the google spread sheet
 *
 * @package App\Service
 */
class XmlDataImporter
{
    /**
     * @var XmlParser $xmlParser
     */
    private $xmlParser;

    /**
     * @var GoogleSpreadSheet $googleSpreadSheet
     */
    private $googleSpreadSheet;

    /**
     * @var XmlDataValidator $xmlDataValidator
     */
    private $xmlDataValidator;

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * XmlDataImporter constructor.
     *
     * @param \App\Service\XmlParser $xmlParser
     * @param \App\Service\GoogleSpreadSheet $googleSpreadSheet
     * @param \App\Service\XmlDataValidator $xmlDataValidator
     * @param \App\Service\LoggerInterface $logger
     */
    public function __construct(
        XmlParser $xmlParser,
        GoogleSpreadSheet $googleSpreadSheet,
        XmlDataValidator $xmlDataValidator,
        LoggerInterface $logger
    ) {
        $this->xmlParser = $xmlParser;
        $this->googleSpreadSheet = $googleSpreadSheet;
        $this->xmlDataValidator = $xmlDataValidator;
        $this->logger = $logger;
    }

    /**
     * Import data from xml file to google spreadsheet
     *
     * @param $filename
     *
     * @return int
     */
    public function importData($filename)
    {
       $catalog = $this->xmlParser->parseFile($filename);

       $itemNumber = 0;
       $imported = 0;
       foreach ($catalog as $item) {
           $itemNumber ++;

           if (!$this->xmlDataValidator->validate($item)) {
               $this->logger->error(sprintf('Error on validation of node %s', $itemNumber));
               continue;
           }

           $itemArray = $this->convertNodeToArray($item);
           $itemArray = $this->convertNodeArrayToSimpleTypes($itemArray);

           $this->googleSpreadSheet->writeData($itemArray);
           $imported ++;
       }

       return $imported;
    }

    /**
     * Convert node data to array
     *
     * @param
     *
     * @return array
     */
    private function convertNodeToArray(SimpleXMLElement $node)
    {
        $xml = simplexml_load_string($node->asXML(), 'SimpleXMLElement', LIBXML_NOCDATA);

        return json_decode(json_encode((array)$xml), true);
    }

    /**
     * Convert array types to simple data type
     *
     * @param array $data
     *
     * @return array
     */
    private function convertNodeArrayToSimpleTypes(array $data)
    {
        foreach ($data as $key => $el)
        {
            if(is_array($el)) {
                $data[$key] = implode(',', $el);
            }
        }

        return $data;
    }
}
