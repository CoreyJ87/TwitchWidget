jQuery(document).ready(function(){
    jQuery('.video_container').click(function(event){
        if(event.target.text!='Open video in new window') {
            jQuery("#player_container").html("<object bgcolor='#000000' data='http://www.twitch.tv/widgets/archive_embed_player.swf' height='378' id='clip_embed_player_flash' type='application/x-shockwave-flash' width='620'><param name='movie' value='http://www.twitch.tv/widgets/archive_embed_player.swf' /><param name='allowScriptAccess' value='always' /><param name='allowNetworking' value='all' /><param name='allowFullScreen' value='true' />" +
            "<param name='flashvars' value='channel=" + jQuery(this).attr('channel') + "&start_volume=25&auto_play=false&chapter_id=" + jQuery(this).attr('id') + "'/></object>");
            jQuery("#player_container").attr('title', jQuery(this).attr('channel'));
            jQuery('#player_container').dialog({
                show: {
                    effect: "blind",
                    duration: 1000
                },
                height: "auto",
                width: "auto",
                modal: true
            });
        }
    });
});