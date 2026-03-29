@props(['items' => []])

@if(!empty($items))
<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 24px 0; border-radius: 8px; border: 1px solid #E3E3E0;" class="card-bg border-color">
    @foreach($items as $label => $value)
        <tr>
            <td style="padding: 10px 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; color: #706F6C; font-weight: 500; width: 40%; vertical-align: top;@if(!$loop->last) border-bottom: 1px solid #E3E3E0; @endif" class="detail-label-dark @if(!$loop->last) border-color @endif">{{ $label }}</td>
            <td style="padding: 10px 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 13px; color: #1B1B18; font-weight: 600; text-align: right; vertical-align: top;@if(!$loop->last) border-bottom: 1px solid #E3E3E0; @endif" class="detail-value-dark @if(!$loop->last) border-color @endif">{{ $value }}</td>
        </tr>
    @endforeach
</table>
@endif
