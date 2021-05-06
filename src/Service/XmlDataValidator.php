<?php

namespace App\Service;

use \SimpleXMLElement;

/**
 * Class XmlDataValidator
 * @package App\Service
 */
class XmlDataValidator
{
    /**
     * @var string[] $xmlNodes
     */
    private $xmlNodes = [
        'entity_id', 'CategoryName', 'sku', 'name', 'description', 'shortdesc', 'price', 'link', 'image',
        'Brand', 'Rating', 'CaffeineType', 'Count', 'Flavored', 'Seasonal', 'Instock', 'Facebook', 'IsKCup'
    ];

    /**
     * @var array $activeValidators
     */
    private $activeValidators = [];

    /**
     * XmlDataValidator constructor
     *
     * @param $validators
     */
    public function __construct($validators)
    {
        $this->activeValidators = $validators;
    }

    /**
     * Validate xml data
     *
     * @param SimpleXMLElement $node
     *
     * @return boolean
     */
    public function validate(SimpleXMLElement $node)
    {
        foreach ($this->activeValidators as $validator) {
            if ($validator === 'structure' && !$this->validateStructure($node)) {
                return false;
            }

            if ($validator === 'image' && !$this->validateImage($node->image)) {
                return false;
            }

            if ($validator === 'link' && !$this->validateLink($node->link)) {
                return false;
            }

            if ($validator === 'price' && !$this->validatePrice($node->price)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate the array structure is same as expected
     *
     * @param SimpleXMLElement $node
     *
     * @return boolean
     */
    public function validateStructure(SimpleXMLElement $node)
    {
        foreach ($this->xmlNodes as $element) {
            if(!property_exists($node, $element)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate Image Data
     *
     * @param string $imageUrl
     *
     * @return boolean
     */
    public function validateImage(string $imageUrl)
    {
        $imageSize = @getimagesize($imageUrl);

        return is_array($imageSize);
    }

    /**
     * Validate Link
     *
     * @param string $link
     *
     * @return boolean
     */
    public function validateLink(string $link)
    {
        return curl_init($link) !== false;
    }

    /**
     * Validate if the price is numeric
     *
     * @param string $price
     *
     * @return boolean
     */
    public function validatePrice(string $price)
    {
        return is_numeric($price);
    }
}
