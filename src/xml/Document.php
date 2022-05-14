<?php

namespace serdiukov\tunduk\xml;


use DOMDocument;
use DOMNode;

class Document
{
    private DOMDocument $dom;
    private DOMNode $root;
    private array $namespaceURIs = [];

    public function __construct(array $namespaceURIs = [], string $rootName = 'Envelope')
    {
        $this->dom = new DOMDocument('1.0', 'UTF-8');
        $this->dom->preserveWhiteSpace = false;
        $this->dom->formatOutput = true;

        if (!empty($namespaceURIs)) {
            $this->namespaceURIs = $namespaceURIs;
        }

        $this->root = $this->appendChild($this->dom, new Element($rootName, null,'soapenv'));

        foreach ($this->namespaceURIs as $key => $value) {
            if (is_array($value)) {
                $this->root->setAttributeNS(key($value), $key, current($value));
            } else {
                $this->dom->createAttributeNS($value, $key .':attr');
            }
        }
    }

    public function getRoot(): DOMNode
    {
        return $this->root;
    }

    public function appendChild(DOMNode $node, Element $element): DOMNode
    {
        if ($element->hasChild()) {
            $fieldValue = null;
        } else {
            $fieldValue = $element->fieldValue;
        }

        if ($element->namespace && isset($this->namespaceURIs[$element->namespace])) {
            $domElement = $this->dom->createElementNS($this->getNamespaceURI($element->namespace), $element->namespace .':'. $element->fieldName, $fieldValue);
        } else {
            $domElement = $this->dom->createElement($element->fieldName, $fieldValue);
        }

        if (!empty($element->attr)) {
            if (count($element->attr) > 1) {
                foreach ($element->attr as $key => $value) {
                    $domElement->setAttribute($key, $value);
                }
            } else {
                $domElement->setAttribute(key($element->attr), current($element->attr));
            }
        }

        $nodeElement = $node->appendChild($domElement);

        if ($element->hasChild()) {
            foreach ($element->fieldValue as $element) {
                $this->appendChild($nodeElement, $element);
            }
        }

        return $nodeElement;
    }

    public function getNamespaceURI(string $key)
    {
        return $this->namespaceURIs[$key];
    }

    public function asXml()
    {
        return $this->dom->saveXML();
    }
}
