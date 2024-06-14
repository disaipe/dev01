<?php

namespace App\Core\Reference;

use App\Models\CustomReference;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class ReferenceManager
{
    /** @var ReferenceEntry[] */
    private array $references = [];

    public function __construct()
    {
        $this->registerCustomReferences();
    }

    public function addReference(ReferenceEntry $reference): void
    {
        $this->references[$reference->getName()] = $reference;
    }

    public function getReferences(): array
    {
        return array_values($this->references);
    }

    public function getByName(string $name): ?ReferenceEntry
    {
        return Arr::get($this->references, $name);
    }

    public function getByTableName(string $table): ?ReferenceEntry
    {
        return Arr::first(
            $this->references,
            fn (ReferenceEntry $entry) => $entry->getModelInstance()->getTable() === $table
        );
    }

    public function register(string|ReferenceEntry $reference): void
    {
        if (is_string($reference)) {
            $this->addReference(app()->make($reference));
        } else {
            $this->addReference($reference);
        }
    }

    public function registerCustomReferences(): void
    {
        if (! Schema::hasTable('custom_references')) {
            return;
        }

        $customReferences = CustomReference::query()
            ->enabled()
            ->get();

        foreach ($customReferences as $customReference) {
            $entry = ReferenceEntry::fromCustomReference($customReference);
            $this->register($entry);
        }
    }
}
