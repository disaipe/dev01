@php
    $type = $getType();
    $typeClasses = match ($type) {
        'warn' => 'bg-yellow-100 text-yellow-600',
        default => 'bg-gray-100 text-gray-600',
    }
@endphp

<div class="my-2 px-4 py-2 rounded  {{ $typeClasses }}" {{ $attributes }}>
    {!! $getContent() !!}
</div>
