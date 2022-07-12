<?php

namespace App\Services;

use App\Entity\Offer;
use App\Entity\Section;
use DOMAttr;
use DOMDocument;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class XmlGenerator
{
    static public function loadToXML(ManagerRegistry $doctrine) : void
    {
        $dom = new DOMDocument();

        $dom->xmlVersion = '1.0';
        $dom->formatOutput = true;
        $xml_file_name = 'CRM.xml';

        $ymlCatalog = $dom->createElement('yml_catalog');
        $dateAttr = new DOMAttr('date', date("now"));
        $ymlCatalog->setAttributeNode($dateAttr);

        $dom->appendChild($ymlCatalog);

        $shop = $dom->createElement('shop');

        $shopName = $dom->createElement('name', "Khalif");
        $shop->appendChild($shopName);

        $shopCompany = $dom->createElement('company', 'antihype LSTU');
        $shop->appendChild($shopCompany);

        $shopCategories = $dom->createElement('categories');
        $categories = $doctrine->getRepository(Section::class)->findAll();
        foreach ($categories as $category) {
            $shopCategory = $dom->createElement('category', $category->getName());
            $idAttr = new DOMAttr('id', $category->getXmlId());
            if ($category->getParent() != null) {
                $parentIdAttr = new DOMAttr('parentId', $category->getParent()->getId());
                $shopCategory->setAttributeNode($parentIdAttr);
            }
            $shopCategory->setAttributeNode($idAttr);
            $shopCategories->appendChild($shopCategory);
            $ymlCatalog->setAttributeNode($dateAttr);
        }
        $shop->appendChild($shopCategories);

        $shopOffers = $dom->createElement('offers');
        $offers = $doctrine->getRepository(Offer::class)->findAll();
        foreach ($offers as $offer) {
            $shopOffer = $dom->createElement('offer');
            $idAttr = new DOMAttr("id", $offer->getId());
            $productIdAttr = new DOMAttr("productId", $offer->getProduct()->getId());
            $quantityAttr = new DOMAttr("quantity", $offer->getQuantity());
            $shopOffer->setAttributeNode($idAttr);
            $shopOffer->setAttributeNode($productIdAttr);
            $shopOffer->setAttributeNode($quantityAttr);

            $url = $dom->createElement('url', 'http://antihype-lstu.com/product/'.$offer->getProduct()->getId());
            $shopOffer->appendChild($url);

            $price = $dom->createElement('price', $offer->getPrice());
            $shopOffer->appendChild($price);

            $categoryId = $dom->createElement('categoryId', $offer->getProduct()->getSections()[0]->getId());
            $shopOffer->appendChild($categoryId);

            $picture = $dom->createElement('picture', $offer->getPicture());
            $shopOffer->appendChild($picture);

            $name = $dom->createElement('name', $offer->getName());
            $shopOffer->appendChild($name);

            $xmlId = $dom->createElement('xmlId', $offer->getXmlId());
            $shopOffer->appendChild($xmlId);

            $productName = $dom->createElement('productName', $offer->getProduct()->getName());
            $shopOffer->appendChild($productName);

            foreach ($offer->getPropertyValues() as $propertyValue) {
                $param = $dom->createElement('param', $propertyValue->getValue());
                $paramName = new DOMAttr('name', $propertyValue->getProperty()->getName());
                $paramCode = new DOMAttr('code', $propertyValue->getProperty()->getCode());
                $param->setAttributeNode($paramName);
                $param->setAttributeNode($paramCode);
                $shopOffer->appendChild($param);
            }

            $vendor = $dom->createElement('productName', $offer->getProduct()->getVendor());
            $shopOffer->appendChild($vendor);

            $unit = $dom->createElement('unit');
            $unitName = new DOMAttr('Name', $offer->getUnit());
            $unit->setAttributeNode($unitName);
            $shopOffer->appendChild($unit);

            $vatRate = $dom->createElement('vatRate', $offer->getProduct()->getVatRate() ?? "none");
            $shopOffer->appendChild($vatRate);

            if ($offer->getProduct()->getActive()) {
                $productActivity = $dom->createElement('productActivity', "Y");
            }
            else {
                $productActivity = $dom->createElement('productActivity', "N");
            }
            $shopOffer->appendChild($productActivity);

            $shopOffers->appendChild($shopOffer);
        }
        $shop->appendChild($shopOffers);

        $dom->appendChild($shop);
        $dom->save($xml_file_name);
    }
}