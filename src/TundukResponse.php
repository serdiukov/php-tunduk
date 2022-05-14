<?php

namespace serdiukov\tunduk;


use DOMDocument;
use DOMXPath;

class TundukResponse
{
    protected DOMDocument $dom;
    protected DOMXPath $xpath;

    public function __construct()
    {
        $this->dom = new DOMDocument;
    }

    public function loadXml(string $xmlContent): TundukResponse
    {
        $this->dom->loadXML($xmlContent);
        $this->xpath = new DOMXPath($this->dom);
        $rootNamespace = $this->dom->lookupNamespaceUri($this->dom->namespaceURI);
        $this->xpath->registerNamespace('SOAP-ENV', $rootNamespace);
        $this->xpath->registerNamespace('ts1', 'http://tunduk-seccurity-infocom.x-road.fi/producer');

        return $this;
    }

    public function getElementData(string $xpath): ?string
    {
        $element = $this->xpath->query($xpath);

        if ($element->length) {
            return trim($element->item(0)->nodeValue);
        }

        return null;
    }

    public function getStatus() : ?int
    {
        if (!$status = $this->getElementData("//SOAP-ENV:Body/*/ts1:response/ts1:status")) {
            $status = $this->getElementData("//SOAP-ENV:Body/*/ts1:response/ts1:code");
        }

        if (isset($status)) {
            return $status;
        }

        return null;
    }

    public function getFault(): ?array
    {
        $elements = $this->xpath->query("//SOAP-ENV:Body/SOAP-ENV:Fault/*");

        if ($elements->length) {
            $error = [];
            foreach ($elements as $node) {
                $error[$node->nodeName] = $node->nodeValue;
            }

            return $error;
        }

        return null;
    }

    public function getResponse(string $key) : array
    {
        $elements = $this->xpath->query("//SOAP-ENV:Body/*/ts1:response/*");
        $array = [];

        if ($elements->length) {
            /** @var \DOMElement $elements */
            foreach ($elements as $i => $node) {
                if (!is_null($node)) {
                    $nodeKey = str_replace($node->prefix .':', '', $node->nodeName);

                    if ($node->childNodes->length) {
                        /** @var \DOMElement $child */
                        foreach ($node->childNodes as $childI => $child) {
                            if ($child->nodeName == '#text') {
                                $array[$nodeKey] = $child->nodeValue;
                            } else {
                                $childKey = str_replace($child->prefix .':', '', $child->nodeName);

                                switch ($nodeKey) {
                                    case 'ownersPeriod':
                                    case 'protocols':
                                        $array[$nodeKey][$i][$childKey] = $child->nodeValue;
                                        break;
                                    default:
                                        $array[$nodeKey][$childKey] = $child->nodeValue;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $array;
    }
}
