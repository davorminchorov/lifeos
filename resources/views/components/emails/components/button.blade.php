@props(['url', 'type' => 'primary'])

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="margin: 28px 0;">
    <tr>
        <td style="text-align: center;">
            <!--[if mso]>
            <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $url }}" style="height:48px;v-text-anchor:middle;width:220px;" arcsize="17%" @if($type === 'primary') fillcolor="#F53003" strokecolor="#F53003" @else fillcolor="#FDFDFC" strokecolor="#E3E3E0" @endif strokeweight="1px">
            <w:anchorlock/>
            <center style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; font-weight: 600; @if($type === 'primary') color: #ffffff; @else color: #1B1B18; @endif">{{ $slot }}</center>
            </v:roundrect>
            <![endif]-->
            <!--[if !mso]><!-->
            @if($type === 'primary')
                <a href="{{ $url }}" style="display: inline-block; background-color: #F53003; color: #ffffff; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; font-weight: 600; text-decoration: none; padding: 13px 28px; border-radius: 8px; line-height: 1; mso-hide: all;">{{ $slot }}</a>
            @else
                <a href="{{ $url }}" style="display: inline-block; background-color: #FDFDFC; color: #1B1B18; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; font-weight: 600; text-decoration: none; padding: 12px 28px; border-radius: 8px; border: 1px solid #E3E3E0; line-height: 1; mso-hide: all;">{{ $slot }}</a>
            @endif
            <!--<![endif]-->
        </td>
    </tr>
</table>
