<?php

namespace Laraditz\Bayar\Data;

abstract class AbstractData
{
    public function toArray(): array
    {
        $body = [];
        $class = new \ReflectionClass(static::class);

        $constructor = $class->getConstructor();

        foreach ($constructor->getParameters() as $property) {

            if ($property->allowsNull() === false || $this->{$property->name}) {

                $body += [$property->name => $this->{$property->name}];
            }
        }

        return $body;
    }
}
