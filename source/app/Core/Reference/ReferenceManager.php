<?php

namespace App\Core\Reference;

class ReferenceManager
{
    /** @var ReferenceEntry[] */
    private array $references = [];

    public function addReference(ReferenceEntry $reference): void
    {
        $this->references[] = $reference;
    }

    public function getReferences(): array
    {
        return $this->references;
    }

    public function register(string|ReferenceEntry $reference): void
    {
        if (is_string($reference)) {
            $this->addReference(app()->make($reference));
        } else {
            $this->addReference($reference);
        }
    }
}
