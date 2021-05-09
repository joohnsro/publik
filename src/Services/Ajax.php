<?php
namespace Publik\Services;

class Ajax
{
    public static function set( $script_id, $custom = false )
    {
        $ajax_data = array(
            'nonce' => wp_create_nonce('ajax_data_nonce'),
            'url'   => admin_url('admin-ajax.php')
        );

        if ( $custom ) {
            $ajax_data['custom'] = $custom;
        }

        wp_localize_script( $script_id, 'ajax_data', $ajax_data );
    }

    public static function load( $action, $callback )
    {
        add_action( 'wp_ajax_' . $action, $callback );
    }
    
}