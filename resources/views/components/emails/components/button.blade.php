@props(['url', 'type' => 'primary'])

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 24px 0;">
    <tr>
        <td>
            <!--[if mso]>
            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $url }}" style="height:44px;v-text-anchor:middle;width:200px;" arcsize="18%" @if($type === 'urgent') fillcolor="#F53003" strokecolor="#F53003" @elseif($type === 'primary') fillcolor="#1B1B18" strokecolor="#1B1B18" @else fillcolor="#FDFDFC" strokecolor="#E3E3E0" @endif strokeweight="1px">
            <w:anchorlock/>
            <center style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 600; @if($type === 'secondary') color: #1B1B18; @else color: #ffffff; @endif">{{ $slot }}</center>
            </v:roundrect>
            <![endif]-->
            <!--[if !mso]><!-->
            @if($type === 'urgent')
                <a href="{{ $url }}" style="display: inline-block; background-color: #F53003; color: #ffffff; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 600; text-decoration: none; padding: 11px 24px; border-radius: 8px; line-height: 1; mso-hide: all;">{{ $slot }}</a>
            @elseif($type === 'primary')
                <a href="{{ $url }}" style="display: inline-block; background-color: #1B1B18; color: #ffffff; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 600; text-decoration: none; padding: 11px 24px; border-radius: 8px; line-height: 1; mso-hide: all;">{{ $slot }}</a>
            @else
                <a href="{{ $url }}" style="display: inline-block; background-color: #FDFDFC; color: #1B1B18; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 600; text-decoration: none; padding: 10px 24px; border-radius: 8px; border: 1px solid #E3E3E0; line-height: 1; mso-hide: all;">{{ $slot }}</a>
            @endif
            <!--<![endif]-->
        </td>
    </tr>
</table>
