@props(['class' => 'h-4 w-4'])

<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" {{ $attributes->merge(['class' => $class.' animate-spin']) }} viewBox="0 0 24 24" fill="none">
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
    <path class="opacity-90" fill="currentColor" d="M12 2a10 10 0 0 1 10 10h-3a7 7 0 0 0-7-7V2Z"></path>
</svg>
