<?php
/**
 * Copyright (c) 1998-2014 Browser Capabilities Project
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the LICENSE file distributed with this package.
 *
 * @category   Browscap
 * @copyright  1998-2014 Browser Capabilities Project
 * @license    MIT
 */

namespace Browscap\Formatter;

use Browscap\Data\PropertyHolder;
use Browscap\Filter\FilterInterface;

/**
 * Class JsonFormatter
 *
 * @category   Browscap
 * @author     Thomas Müller <t_mueller_stolzenhain@yahoo.de>
 */
class JsonFormatter implements FormatterInterface
{
    /**
     * @var \Browscap\Filter\FilterInterface
     */
    private $filter = null;

    /**
     * returns the Type of the formatter
     *
     * @return string
     */
    public function getType()
    {
        return 'json';
    }

    /**
     * formats the name of a property
     *
     * @param string $name
     *
     * @return string
     */
    public function formatPropertyName($name)
    {
        return $this->jsonEncode($name);
    }

    /**
     * formats the name of a property
     *
     * @param string $value
     * @param string $property
     *
     * @return string
     */
    public function formatPropertyValue($value, $property)
    {
        $propertyHolder = new PropertyHolder();

        switch ($propertyHolder->getPropertyType($property)) {
            case PropertyHolder::TYPE_STRING:
                $valueOutput = $this->jsonEncode(trim($value));
                break;
            case PropertyHolder::TYPE_BOOLEAN:
                if (true === $value || $value === 'true') {
                    $valueOutput = '"true"';
                } elseif (false === $value || $value === 'false') {
                    $valueOutput = '"false"';
                } else {
                    $valueOutput = '""';
                }
                break;
            case PropertyHolder::TYPE_IN_ARRAY:
                try {
                    $valueOutput = $this->jsonEncode($propertyHolder->checkValueInArray($property, $value));
                } catch (\InvalidArgumentException $ex) {
                    $valueOutput = '""';
                }
                break;
            default:
                $valueOutput = $this->jsonEncode($value);
                break;
        }

        if ('unknown' === $valueOutput || '"unknown"' === $valueOutput) {
            $valueOutput = '""';
        }

        return $valueOutput;
    }

    /**
     * @param \Browscap\Filter\FilterInterface $filter
     *
     * @return \Browscap\Formatter\FormatterInterface
     */
    public function setFilter(FilterInterface $filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @return \Browscap\Filter\FilterInterface
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param string $val
     *
     * @return string
     */
    private function jsonEncode($val)
    {
        if ($val === null) {
            return '"null"';
        }

        if ($val === true) {
            return '"true"';
        }

        if ($val === false) {
            return '"false"';
        }

        if (is_string($val)) {
            return json_encode($val);
        }

        if (is_numeric($val)) {
            return '"' . json_encode($val) . '"';
        }

        $assoc = false;
        $i     = 0;
        foreach ($val as $k => $v) {
            if ($k !== $i++) {
                $assoc = true;
                break;
            }
        }
        $res = [];
        foreach ($val as $k => $v) {
            $v = $this->jsonEncode($v);
            if ($assoc) {
                $k = json_encode($k);
                $v = $k . ':' . $v;
            }
            $res[] = $v;
        }
        $res = implode(',', $res);

        return ($assoc) ? '{' . $res . '}' : '[' . $res . ']';
    }
}
