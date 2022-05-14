<?php

namespace serdiukov\tunduk\xml;


class Element
{
    public ?string $namespace;
    public string $fieldName;
    public $fieldValue;
    public array $attr = [];

    public function __construct(string $fieldName, $fieldValue = null, ?string $namespace = null, array $attr = [])
    {
        $this->fieldName = $fieldName;
        $this->namespace = $namespace;
        $this->attr = $attr;

        if (is_array($fieldValue)) {
            foreach ($fieldValue as $value) {
                if ($value instanceof  Element) {
                    $this->fieldValue[] = $value;
                }
            }

        } else {
            $this->fieldValue = $fieldValue;
        }
    }

    public function hasChild() : bool
    {
        return is_array($this->fieldValue);
    }
}
