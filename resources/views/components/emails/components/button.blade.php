@props(['url', 'type' => 'primary'])

<div class="button-container">
    <a href="{{ $url }}" class="button @if($type === 'secondary') button-secondary @endif">
        {{ $slot }}
    </a>
</div>
