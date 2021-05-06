<?php

namespace App\Service;

use \SimpleXMLElement;
use Symfony\Component\Finder\Finder;

/**
 * Class XmlParser
 * Parse and validate data in xml file
 *
 * @package App\Service
 */
class XmlParser
{
    /**
     * @var string $filePath
     */
    private $filePath;

    /**
     * XmlParser constructor
     *
     * @param string $filePath
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * @param string $filename
     *
     * @return SimpleXMLElement
     */
    public function parseFile($filename)
    {
        $data = $this->getFileData($filename);

        $items = new SimpleXMLElement($data);

        return $items;
    }

    /**
     * Get the raw file data
     *
     * @param $filename
     *
     * @return mixed
     * @throws \Exception
     */
    private function getFileData($filename)
    {
        $finder = new Finder();
        $finder->files()->in($this->filePath)->name($filename);

        if (!$finder->hasResults()) {
            throw new \Exception(sprintf('File %s not found in path %s', $filename, $this->filePath));
        }
        foreach ($finder as $file) {
            return $file->getContents();
        }
        $file = reset($finder);

        return $file->getContents();
    }
}
