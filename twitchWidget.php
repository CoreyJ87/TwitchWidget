<?php
/*
Plugin Name: Twitch Widget
Plugin URI: http://testytraitors.com/plugins
Description: Creates a widget with a list of CoC Streams
Version: 0.1
Author: Corey Jones (SyNiK4L)
Author URI: http://testytraitors.com
License: A "Slug" license name e.g. GPL2
*/
class twitch_widget extends WP_Widget
{
    function twitch_widget()
    {
        $widget_ops = array('classname' => 'twitch_widget', 'description' => 'Displays a widget with Clash of Clans Channels in it. ' );
        $this->WP_Widget('twitch_widget', 'Twitch Widget', $widget_ops);
    }

    function form($instance)
    {
        $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'content' => '' ) );

        $title = $instance['title'];
        $game = $instance['game'];
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
        <p><label for="<?php echo $this->get_field_id('game'); ?>">Game: <input class="widefat" id="<?php echo $this->get_field_id('game'); ?>" name="<?php echo $this->get_field_name('game'); ?>" type="text" value="<?php echo attribute_escape($game); ?>" /></label></p>

    <?php
    }

    function update($new_instance, $old_instance)
    {
        $instance = $old_instance;

        // Retrieve Fields
        $instance['title']   = strip_tags($new_instance['title']);
        $instance['game']   = strip_tags($new_instance['game']);

        return $instance;
    }

    function widget($args, $instance)
    {

        extract($args, EXTR_SKIP);
        echo $before_widget;

        $title   = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
        $game = $instance['game'];

        if(empty($title)){
            echo '<div>Missing widget Title</div>';
        }
        else {
            echo $before_title . $title . $after_title;
        }

        if(empty($game)) {
            echo "<div>Missing Game Widget Setting</div>";
        }
        else {
            $fields = array("query" => $game, "offset" => 0, "limit" => 5);
            $list = curlItGet('https://api.twitch.tv/kraken/search/streams?', $fields);
        }
        if(!empty($title) && $list != false){
            echo '<div class="twitchData">';
            foreach ($list->streams as $single_stream) {
                echo '<div class="twitch_streamer" id="' . $single_stream->channel->display_name . '">'
                    . '<div class="user_logo"><a href="' . $single_stream->channel->url . '" target="_blank">'
                    . '<img src="' . $single_stream->channel->logo . '" style="width: 40px;height:40px;"></a></div>'
                    . '<div class="twitch_username">'
                    . '<a href="' . $single_stream->channel->url . '" target="_blank">' . $single_stream->channel->display_name
                    . '</a></div>'
                    . '<div class="twitch_status">' . $single_stream->channel->status . '</div>'
                    . '<div class="twitch_viewers">Viewers:' . $single_stream->viewers . '</div></div>';
            }
            echo '</div>';

        }
        else{
            echo '<div>Twitch failed to respond. Please refresh to try again</div>';
        }
        echo $after_widget;
    }

}

add_action( 'widgets_init', create_function('', 'return register_widget("twitch_widget");') );


function rbw_scripts() {
    wp_enqueue_style( "rbw_css", path_join(WP_PLUGIN_URL, basename( dirname( __FILE__ ) )."/styles.css"));
}

add_action('wp_enqueue_scripts', 'rbw_scripts');

function curlItGet($url, $fields) {
    $fields_string="";
    foreach ($fields as $key => $value) {
        $the_value=urlencode($value);
        $fields_string .= $key . '=' . $the_value . '&';
    }
    $trimmed_string = substr($fields_string, 0, -1);
    $ch = curl_init();
    $options = array(CURLOPT_URL => $url.$trimmed_string,CURLOPT_RETURNTRANSFER => 1,CURLOPT_SSL_VERIFYPEER=> 1);
    curl_setopt_array($ch, $options);
    $result = curl_exec($ch);
    $decoded = json_decode($result);
    $info = curl_getinfo($ch,CURLINFO_HTTP_CODE);

    curl_close($ch);
    if($info == 200)
    return $decoded;
    else
        return false;
}

?>