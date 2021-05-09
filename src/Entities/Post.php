<?php
namespace Publik\Entities;

class Post
{
    public function save( $args )
    {
        $post_date = str_replace( 'T', ' ', $args['schedule'] ) . ':00';

        $tags = [];
        if ( isset( $args['tags'] ) ) {
            $tags = array_map(function( $tag ){
                return (int)$tag;
            }, $args['tags']);
        }

        $post = array(
            'post_title'    => $args['title'],
            'post_content'  => $args['content'],
            'post_status'   => ( $args['now'] === 'true' ) ? 'publish' : 'future',
            'post_date'     => ( $args['now'] === 'true' ) ? '' : $post_date,
            'post_author'   => $args['author'],
            'post_category' => isset( $args['categories'] ) ? $args['categories'] : [],
            'tags_input'    => $tags
        );

        $post_id = wp_insert_post( $post );

        if ( isset( $args['featured'] ) && $args['featured'] !== '' ) {
            set_post_thumbnail( $post_id, $args['featured'] );
        }

        return $post_id;        
    }   

    public function socialSchedule( $post_id, $schedule )
    {
        $schedule = str_replace( 'T', ' ', $schedule ) . ':00';

        update_post_meta( $post_id, 'publik_schedule_social_date', $schedule );
        update_post_meta( $post_id, 'publik_schedule_social_status', 'false' );
    }
}