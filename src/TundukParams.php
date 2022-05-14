<?php

namespace serdiukov\tunduk;


class TundukParams
{
    private string $subsystemCode;
    private string $serviceCode;
    private array $body = [];

    public function setSubsystemCode(string $subsystemCode): TundukParams
    {
        $this->subsystemCode = $subsystemCode;
        return $this;
    }

    public function setServiceCode(string $serviceCode): TundukParams
    {
        $this->serviceCode = $serviceCode;
        return $this;
    }

    public function setBody(array $body): TundukParams
    {
        $this->body = $body;
        return $this;
    }

    public function getSubsystemCode() : string
    {
        return $this->subsystemCode;
    }

    public function getServiceCode() : string
    {
        return $this->serviceCode;
    }

    public function getBody() : array
    {
        return $this->body;
    }
}
