@props(['items' => []])

@if(!empty($items))
<div class="details-list">
    @foreach($items as $label => $value)
        <div class="detail-item">
            <span class="detail-label">{{ $label }}:</span>
            <span class="detail-value">{{ $value }}</span>
        </div>
    @endforeach
</div>
@endif
