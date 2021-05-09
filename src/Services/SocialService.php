<?php
namespace Publik\Services;

use Publik\Services\FacebookService;
use Publik\Services\TwitterService;

class SocialService
{
    public function __construct( array $socials )
    {
        $this->env = $this->getEnvData();

        $this->socials = $this->startSocials( $socials );
    }

    private function getEnvData()
    {
        $url = plugin_dir_path( __FILE__ ) . "../../publik.json";
        $json = file_get_contents($url);

        return json_decode($json, true);
    }

    private function startSocials( array $available )
    {
        $social = [];

        if ( $available['facebook'] ) {
            $facebookService = new FacebookService( $this->env['facebook'] );
            if ( $facebookService ) {
                $social['facebook'] = $facebookService;
            }
        }

        if ( $available['twitter'] ) {
            $twitterService = new TwitterService( $this->env['twitter'] );
            if ( $twitterService ) {
                $social['twitter']  = $twitterService;
            }            
        }

        return $social;
    }

    public function publish( $args, $now )
    {
        if ( count( $this->socials ) == 0 ) return false;

        foreach( $this->socials as $social ) {
            $social->publish( $args, $now );
        }
    }
}