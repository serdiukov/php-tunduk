<?php

namespace serdiukov\tunduk;


use serdiukov\tunduk\xml\Document;
use serdiukov\tunduk\xml\Element;

class TundukRequest
{
    private Document $dom;
    /**
     * @var string 'GOV'
     */
    private string $memberClass;
    /**
     * @var string '70000001'
     */
    private string $memberCode;
    /**
     * @var string 'name-service'
     */
    private string $subsystemCode;
    /**
     * @var array
     */
    private array $namespaceURIs = [
        'soapenv' => 'http://schemas.xmlsoap.org/soap/envelope/',
        'xro'  => 'http://x-road.eu/xsd/xroad.xsd',
        'iden' => 'http://x-road.eu/xsd/identifiers',
        'prod' => 'http://tunduk-seccurity-infocom.x-road.fi/producer'
    ];

    public function __construct(string $memberClass, string $memberCode, string $subsystemCode)
    {
        // set params
        $this->memberClass = $memberClass;
        $this->memberCode = $memberCode;
        $this->subsystemCode = $subsystemCode;
    }

    public function createDom(): TundukRequest
    {
        // create dom
        $this->dom = new Document($this->namespaceURIs);

        return $this;
    }

    public function setHeader(string $userId, string $requestId, string $subsystemCode, string $serviceCode): TundukRequest
    {
        $header = $this->dom->appendChild($this->dom->getRoot(), new Element('Header', null, 'soapenv'));
        // set userId
        $this->dom->appendChild($header, new Element('userId', $userId, 'xro'));

        // service
        $service = $this->dom->appendChild($header, new Element('service', null, 'xro', [
            'iden:objectType' => 'SERVICE'
        ]));

        $this->dom->appendChild($service, new Element('xRoadInstance', 'central-server', 'iden'));
        $this->dom->appendChild($service, new Element('memberClass', 'GOV', 'iden'));
        $this->dom->appendChild($service, new Element('memberCode', '70000005', 'iden'));
        $this->dom->appendChild($service, new Element('subsystemCode', $subsystemCode, 'iden'));
        $this->dom->appendChild($service, new Element('serviceCode', $serviceCode, 'iden'));
        $this->dom->appendChild($service, new Element('serviceVersion', 'v1', 'iden'));

        // set protocolVersion
        $this->dom->appendChild($header, new Element('protocolVersion', '4.0', 'xro'));
        // set issue
        $this->dom->appendChild($header, new Element('issue', 'request', 'xro'));
        $this->dom->appendChild($header, new Element('id', $requestId, 'xro'));

        // set client
        $client = $this->dom->appendChild($header, new Element('client', null, 'xro', [
            'iden:objectType' => 'SUBSYSTEM'
        ]));

        $this->dom->appendChild($client, new Element('xRoadInstance', 'central-server', 'iden'));
        $this->dom->appendChild($client, new Element('memberClass', $this->memberClass, 'iden'));
        $this->dom->appendChild($client, new Element('memberCode', $this->memberCode, 'iden'));
        $this->dom->appendChild($client, new Element('subsystemCode', $this->subsystemCode, 'iden'));

        return $this;
    }

    public function setBody(array $nodes, string $attr = 'prod'): TundukRequest
    {
        $body = $this->dom->appendChild($this->dom->getRoot(), new Element('Body', null, 'soapenv'));
        $this->setBodyNodes($body, $nodes, $attr);

        return $this;
    }

    private function setBodyNodes(\DOMNode $node, array $nodes, string $attr)
    {
        foreach ($nodes as $index => $item) {
            if (is_array($item)) {
                $node = $this->dom->appendChild($node, new Element($index, null, $attr));
                $this->setBodyNodes($node, $item, $attr);
            } else {
                $this->dom->appendChild($node, new Element($index, $item, $attr));
            }
        }
    }

    public function asXml()
    {
        return $this->dom->asXml();
    }
}
