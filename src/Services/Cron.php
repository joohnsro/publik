<?php
namespace Publik\Services;

use Publik\Services\FacebookService;
use Publik\Services\TwitterService;

class Cron
{
    public static function init()
    {
        $self = new self();

        add_filter( 'cron_schedules', array( $self, 'addInterval' ) );
        add_action( 'publik_cron_hook', array( $self, 'execJob' ) );

        if ( ! wp_next_scheduled( 'publik_cron_hook' ) ) {
            wp_schedule_event( time(), 'publik_five_seconds', 'publik_cron_hook' );
        }
    }

    public function addInterval()
    {
        $schedules['publik_five_seconds'] = array(
            'interval' => 60,
            'display'  => esc_html__( 'Every Minute' ), );
        return $schedules;
    }

    public function execJob()
    {
        //Pega todos post que não foram lançados até essa hora
        $date = date( 'Y-m-d H:i:s' );

        $posts = $tihs->getSchedulePostsBefore( '2021-04-30 18:00:00' );

        if ( !$posts ) return;

        $url = plugin_dir_path( __FILE__ ) . "../../publik.json";

        $publikSettingsJSON = file_get_contents($url);
        $publikSettings = json_decode($publikSettingsJSON, true);

        $facebook = new FacebookService( $publikSettings['facebook'] );
        $twitter  = new TwitterService( $publikSettings['twitter'] );

        foreach( $posts as $post ) {
            $response['facebook'] = $facebook->publish([
                'message'   => $post->post_title,
                //'message'   => 'teste',
                'link'      => 'https://imprensaabc.com.br/2021/04/29/teatro-municipal-de-santo-andre-celebra-50-anos-com-serie-de-depoimentos/'
                //'link'      => get_the_permalink($post->ID)
            ]);

            $response['twitter'] = $twitter->publish([
                'status' => $post->post_title,
                //'status' => 'teste',
                'link' => 'https://imprensaabc.com.br/2021/04/29/teatro-municipal-de-santo-andre-celebra-50-anos-com-serie-de-depoimentos/'
            ]);

            $this->socialPublished($post->ID);
        }        
    }

    public function printCronJobs()
    {
        echo '<pre>'; print_r( _get_cron_array() ); echo '</pre>';
    }

    public function jobDeactivation()
    {
        register_deactivation_hook( __FILE__, 'publik_cron_hook' ); 
 
        $timestamp = wp_next_scheduled( 'publik_cron_hook' );
        wp_unschedule_event( $timestamp, 'publik_cron_hook' );
    }

    public static function getSchedulePostsBefore( $schedule )
    {
        $args = array(
            'post_type' => 'post',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => 'publik_schedule_social_date',
                    'value' => $schedule,
                    'compare' => '<=',
                ),
                array(
                    'key' => 'publik_schedule_social_status',
                    'value' => 'false',
                    'compare' => '=',
                ),
            )
        );

        $query = query_posts($args);

        if ( $query->have_posts() ) {
            return $query->posts;
        }

        return false;
    }

    public function socialPublished( $post_id )
    {
        \update_post_meta( $post_id, 'publik_schedule_social_status', 'true' );
    }
}