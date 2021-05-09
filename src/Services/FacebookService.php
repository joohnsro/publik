<?php
namespace Publik\Services;

class FacebookService
{
    public function __construct( $settings )
    {
        if ( $this->validateSettings($settings) ) return false;

        $this->app_id                = "{$settings['app_id']}";
        $this->app_secret            = "{$settings['app_secret']}";
        $this->page_id               = "{$settings['page_id']}";
        $this->page_access_token     = "{$settings['page_access_token']}";

        if ( 
            $this->app_id            == "" ||
            $this->app_secret        == "" ||
            $this->page_id           == "" ||
            $this->page_access_token == ""
        ) {
            return false;
        }

        $this->default_graph_version = 
            isset( $settings['default_graph_version'] ) 
            ? $settings['default_graph_version'] 
            : 'v2.10'
        ;

        $this->connection = new \Facebook\Facebook([
            'app_id'                => $this->app_id,
            'app_secret'            => $this->app_secret,
            'default_graph_version' => $this->default_graph_version,
        ]);
    }
    
    public function validateSettings( $settings ) {

        $required = array( 'app_id', 'app_secret', 'page_id', 'page_access_token' );

        if ( in_array( $required, $settings ) ) {
            return true;
        }

        return false;
    }

    public function publish( $args = array('message' => 'Sample message'), $now = true )
    {      
        $schedule = ( $now ) 
            ? ''
            : $this->getValidDate( $args['schedule'] ) 
        ;

        $data = [
            'link' => ( isset($args['link']) ) ? $args['link'] : '',
            'scheduled_publish_time' => $schedule,
            'message' => $args['message'],
            'published' => $now,
            'access_token' => $this->page_access_token
        ];
        
        try {
            
            $response = $this->connection->post("/{$this->page_id}/feed", $data);

        } catch(\Facebook\Exception\ResponseException $e) {

            echo 'Graph returned an error: ' . $e->getMessage();
            return false;

        } catch(\Facebook\Exception\SDKException $e) {
            
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            return false;
        }
        
        // Returns the post data if you want to display
        $graphNode = $response->getGraphNode();
        
        return true;
    }

    private function getValidDate( $schedule )
    {
        return strtotime( $schedule );
    }
}