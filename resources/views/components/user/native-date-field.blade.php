@props([
    'id',
    'label',
    'model',
    'error' => null,
    'type' => 'date',
    'icon' => null,
    'modifier' => 'live',
    'min' => null,
    'max' => null,
])

@php
    $fieldIcon = $icon ?? match ($type) {
        'time' => 'heroicon-o-clock',
        default => 'heroicon-o-calendar-days',
    };
@endphp

<div class="user-native-field">
    <x-forms.label :for="$id" :value="$label" class="user-native-field__label" />

    <div class="user-native-field__control">
        <x-dynamic-component :component="$fieldIcon" class="user-native-field__icon" />
        <input
            id="{{ $id }}"
            type="{{ $type }}"
            aria-label="{{ $label }}"
            @if ($min) min="{{ $min }}" @endif
            @if ($max) max="{{ $max }}" @endif
            @if ($modifier === 'defer')
                wire:model.defer="{{ $model }}"
            @elseif ($modifier === 'live')
                wire:model.live="{{ $model }}"
            @else
                wire:model="{{ $model }}"
            @endif
            {{ $attributes->merge(['class' => 'user-native-field__input']) }}
        >
    </div>

    @if ($error)
        <x-forms.input-error :for="$error" class="mt-2" />
    @endif
</div>
