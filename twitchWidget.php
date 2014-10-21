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
        $widget_ops = array('classname' => 'twitch_widget', 'description' => 'Displays a widget with a list of streamers. Streaming a certain game. ');
        $this->WP_Widget('twitch_widget', 'Twitch Widget', $widget_ops);
    }

    function form($instance)
    {
        $instance = wp_parse_args((array)$instance, array('title' => '', 'content' => ''));

        $title = $instance['title'];
        $game = $instance['game'];
        $limit = $instance['limit'];
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat"
                                                                                  id="<?php echo $this->get_field_id('title'); ?>"
                                                                                  name="<?php echo $this->get_field_name('title'); ?>"
                                                                                  type="text"
                                                                                  value="<?php echo attribute_escape($title); ?>"/></label>
        </p>
        <p><label for="<?php echo $this->get_field_id('game'); ?>">Game: <input class="widefat"
                                                                                id="<?php echo $this->get_field_id('game'); ?>"
                                                                                name="<?php echo $this->get_field_name('game'); ?>"
                                                                                type="text"
                                                                                value="<?php echo attribute_escape($game); ?>"/></label>
        </p>
        <p><label for="<?php echo $this->get_field_id('limit'); ?>">Limit: <input class="widefat"
                                                                                  id="<?php echo $this->get_field_id('limit'); ?>"
                                                                                  name="<?php echo $this->get_field_name('limit'); ?>"
                                                                                  type="text"
                                                                                  value="<?php echo attribute_escape($limit); ?>"/></label>
        </p>
    <?php
    }

    function update($new_instance, $old_instance)
    {
        $instance = $old_instance;

        // Retrieve Fields
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['game'] = strip_tags($new_instance['game']);
        $instance['limit'] = strip_tags($new_instance['limit']);

        return $instance;
    }

    function widget($args, $instance)
    {

        extract($args, EXTR_SKIP);
        echo $before_widget;

        $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
        $game = $instance['game'];
        $limit = empty($instance['limit']) ? 5 : $instance['limit'];

        if (empty($title))
            echo '<div>Missing widget title</div>';
        else
            echo $before_title . $title . $after_title;

        if (empty($game))
            echo "<div>Please select a game to search for</div>";
        else {
            $debug = false;
            $fields = array("query" => $game, "limit" => $limit);
            if ($debug) {
                echo '<script type="text/javascript">console.log(' . $limit . ')</script>';
            }
            $list = curlItGet('https://api.twitch.tv/kraken/search/streams?', $fields,$debug);
        }
        if (!empty($title) && $list != "fail" && count($list->streams) > 0) {
            echo '<div class="twitchData">';
            foreach ($list->streams as $single_stream) {
                echo '<div class="user_content streamer" id="' . $single_stream->channel->display_name . '">'
                    . '<div class="user_content user_preview"><a href="' . $single_stream->channel->url . '" target="_blank">'
                    . '<img src="' . $single_stream->preview->medium . '" id="user_preview_image"></a></div>'
                    . '<div class="user_content user_name">'
                    . '<a href="' . $single_stream->channel->url . '" target="_blank">' . $single_stream->channel->display_name
                    . '</a></div>'
                    . '<div class="user_content user_status">' . $single_stream->channel->status . '</div>'
                    . '<div class="user_content user_viewers"><b>Viewers:</b>' . $single_stream->viewers . '</div></div>';
            }
            echo '</div>';
        } else if (!empty($title) && $list != "fail" && count($list->streams == 0)) {
            echo '<div>Twitch did not return any results for selected game</div>';
        } else {
            echo '<div>Twitch failed to respond. Please refresh to try again</div>';
        }
        echo $after_widget;
    }

}

function rbw_scripts()
{
    wp_enqueue_style("rbw_css", path_join(WP_PLUGIN_URL, basename(dirname(__FILE__)) . "/styles.css"));
    wp_enqueue_script( 'main', path_join(WP_PLUGIN_URL, basename(dirname(__FILE__)) . '/main.js', array(), '1.0.0', true ));
}


function curlItGet($url, $fields,$debug)
{
    $fields_string = "";
    foreach ($fields as $key => $value) {
        $the_value = urlencode($value);
        $fields_string .= $key . '=' . $the_value . '&';
    }
    $trimmed_string = substr($fields_string, 0, -1);
    $ch = curl_init();
    $options = array(CURLOPT_URL => $url . $trimmed_string, CURLOPT_RETURNTRANSFER => 1, CURLOPT_SSL_VERIFYPEER => 1,CURLOPT_HTTPHEADER=>array('Accept: application/vnd.twitchtv.v3+json'));
    curl_setopt_array($ch, $options);
    $result = curl_exec($ch);
    if ($debug) {
        echo '<script type="text/javascript">console.log(' . $result . ')</script>';
    }
    $info = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($info == 200) {
        $decoded = json_decode($result);
        curl_close($ch);
        return $decoded;
    } else {
        curl_close($ch);
        return "fail";
    }
}
function twitchVids( $atts ) {
    $attrs = shortcode_atts( array(
        'game' => 'default',
        'limit' => '5',
        'period' => 'month'
    ), $atts );
    $debug = true;
    $fields = array("game" => $attrs['game'], "limit" => $attrs['limit'],'period'=> $attrs['period'] );
    if ($debug) {
        echo '<script type="text/javascript">console.log(' . $attrs["limit"] . ')</script>';
    }
    $list = curlItGet('https://api.twitch.tv/kraken/videos/top?', $fields,$debug);
    if ($list != "fail" && count($list->videos) > 0) {
        echo '<div class="twitchData">';
        foreach ($list->videos as $single_video) {
            echo '<div class="video_container" channel="' . $single_video->channel->name . '" id="' . str_replace('c', '', $single_video->_id) . '">'
                . '<div class="user_content video_title">' . $single_video->title . '</div>'
                . '<div class="user_content video_preview"><img src="' . $single_video->preview . '" class="preview_image"></div>'
                . '<div class="user_content video_description">' . $single_video->description . '</div>'
                . '<div class="user_content video_popout"><a href="'.$single_video->url.'" target="_blank">Open video in new window</a></div></div>';
        }
        echo '</div>';
    }
}
add_shortcode( 'twitchVideos', 'twitchVids' );
add_action('widgets_init', create_function('', 'return register_widget("twitch_widget");'));
add_action('wp_enqueue_scripts', 'rbw_scripts');
?>