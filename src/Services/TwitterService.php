<?php
namespace Publik\Services;

use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterService {

    public function __construct( $settings )
    {
        if ( $this->validateSettings($settings) ) return false;

        $this->oauth_token         = $settings['oauth_token'];
        $this->oauth_token_secret  = $settings['oauth_token_secret'];
        $this->consumer_key        = $settings['consumer_key'];
        $this->consumer_secret     = $settings['consumer_secret'];

        if ( 
            $this->oauth_token          == "" ||
            $this->oauth_token_secret   == "" ||
            $this->consumer_key         == "" ||
            $this->consumer_secret      == ""
        ) {
            return false;
        }

        $this->connection = new TwitterOAuth(
            $this->consumer_key,
            $this->consumer_secret,
            $this->oauth_token,
            $this->oauth_token_secret
        );
    }

    public function validateSettings( $settings ) {

        $required = array( 'oauth_token', 'oauth_token_secret', 'consumer_key', 'consumer_secret' );

        if ( in_array( $required, $settings ) ) {
            return true;
        }

        return false;
    }

    public function publish( $args = array('message' => 'Sample tweet'), $now = true )
    {
        try {

            $args['message'] .= ( isset($args['link']) ) ? ' ' . $args['link'] : '';
            
            $response = $this->connection->post("statuses/update", array(
                'status'         => $args['message']
            ));

        } catch ( Exception $e ) {
            echo $e->getMessage();
            return false;
        }

        return true;
    }

    /**
     * Returns login url
     */
    public static function loginURL( $consumer_key, $consumer_secret )
    {
        $connection = new TwitterOAuth( $consumer_key, $consumer_secret );

        $request_token = $connection->oauth('oauth/request_token', array(
            'oauth_callback' => PUBLIK_URL . 'twitter_callback.php'
        ));
    
        return $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
    }

    /**
     * Return oauth_token and oauth_token_secret for use
     */
    public static function setOAuthToken( $consumer_key, $consumer_secret )
    {
        if ( !isset($_GET['oauth_token']) || !isset($_GET['oauth_verifier']) ) die;

        $oauth_token    = $_GET['oauth_token'];
        $oauth_verifier = $_GET['oauth_verifier'];

        $connection = new TwitterOAuth( $consumer_key, $consumer_secret );

        return $connection->oauth('oauth/access_token', array(
            'oauth_consumer_key' => $consumer_key,
            'oauth_token' => $oauth_token,
            'oauth_verifier' => $oauth_verifier
        ));
    }
}