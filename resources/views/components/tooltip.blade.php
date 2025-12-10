@props([
    'text',            // Tooltip content
    'placement' => 'top', // top | bottom | left | right
    'underline' => true // true | false
])

@php
$positionClasses = match($placement) {
    'bottom' => 'top-full left-1/2 -translate-x-1/2 mt-2',
    'left'   => 'top-1/2 right-full -translate-y-1/2 mr-2',
    'right'  => 'top-1/2 left-full -translate-y-1/2 ml-2',
    default  => 'bottom-full left-1/2 -translate-x-1/2 mb-0', // top
};
$underlineClass = $underline ? 'border-b border-dashed border-gray-400' : '';
@endphp

<span class="relative group cursor-help {{ $underlineClass }}" {{ $attributes }}>
    {{ $slot }}
    @if($text)
        <span class="absolute {{ $positionClasses }}
                    bg-gray-800 text-white text-xs rounded px-2 py-1
                    opacity-0 group-hover:opacity-100 transition-opacity
                    pointer-events-none z-50 whitespace-nowrap">
            {{ $text }}
        </span>
    @endif
</span>
