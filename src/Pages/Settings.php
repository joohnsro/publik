<?php
namespace Publik\Pages;

use Publik\Pages\PagesInterface;
use Publik\Services\TwitterService;

class Settings implements PagesInterface
{
    public function init()
    {
        $self = new self();
        add_action( 'admin_enqueue_scripts' , array( $self, 'stylesheet' ) );
    }

    public function stylesheet()
    {
        wp_enqueue_style( 'publik-admin-style', PUBLIK_URL . 'assets/css/settings.css', array(), '1.0.0', 'all' );
    }

    public function script(){}

    public function hasUpdate()
    {
        if ( isset($_POST['refresh_data']) ) {

            $url = plugin_dir_path( __FILE__ ) . "../../publik.json";

            $publikSettingsJSON = file_get_contents($url);
            $publikSettings = json_decode($publikSettingsJSON, true);

            $publikSettings["facebook"]['app_id'] = $_POST['facebook_app_id'];
            $publikSettings["facebook"]['app_secret'] = $_POST['facebook_app_secret'];
            $publikSettings["facebook"]['page_id'] = $_POST['facebook_page_id'];
            $publikSettings["facebook"]['page_access_token'] = $_POST['facebook_page_access_token'];

            $publikSettings["twitter"]['consumer_key'] = $_POST['twitter_consumer_key'];
            $publikSettings["twitter"]['consumer_secret'] = $_POST['twitter_consumer_secret'];

            file_put_contents( $url, json_encode($publikSettings) );
        }
    } 

    public function html()
    {
        $this->hasUpdate();

        $url = plugin_dir_path( __FILE__ ) . "../../publik.json";

        $publikSettingsJSON = file_get_contents($url);
        $publikSettings = json_decode($publikSettingsJSON, true);

        $twitterLoginButton = '';

        if ( 
            $publikSettings['twitter']['oauth_token'] == '' || 
            $publikSettings['twitter']['oauth_token_secret'] == ''
        ) {
            $url = TwitterService::loginURL( $publikSettings['twitter']['consumer_key'], $publikSettings['twitter']['consumer_secret'] );
            $twitterLoginButton = "<div class='input-field'><a href='{$url}' class='social-login'>Entre com sua conta do Twitter</a></div>";
        }
?>
        <div id="publik-plugin">
            <div class="header">
                <h3>
                    Configurações
                </h3>
                <p>
                    Altere aqui as configurações para o compartilhamento de publicações.
                </p>
            </div>

            <form class="content" method="post">

                <h4>Facebook</h4>

                <div class="input-field">
                    <label for="facebook_app_id">Api Key</label>
                    <input type="text" id="facebook_app_id" name="facebook_app_id" value="<?php echo $publikSettings['facebook']['app_id']; ?>">
                </div>
                <div class="input-field">
                    <label for="facebook_app_secret">Api Key Secret</label>
                    <input type="text" id="facebook_app_secret" name="facebook_app_secret" value="<?php echo $publikSettings['facebook']['app_secret']; ?>">
                </div>
                <div class="input-field">
                    <label for="facebook_page_id">Page ID</label>
                    <input type="text" id="facebook_page_id" name="facebook_page_id" value="<?php echo $publikSettings['facebook']['page_id']; ?>">
                </div>
                <div class="input-field">
                    <label for="facebook_page_access_token">Page Access Token</label>
                    <input type="text" id="facebook_page_access_token" name="facebook_page_access_token" value="<?php echo $publikSettings['facebook']['page_access_token']; ?>">
                </div>

                <h4>Twitter</h4>

                <div class="input-field">
                    <label for="twitter_consumer_key">Consumer Key</label>
                    <input type="text" id="twitter_consumer_key" name="twitter_consumer_key" value="<?php echo $publikSettings['twitter']['consumer_key']; ?>">
                </div>
                <div class="input-field">
                    <label for="twitter_consumer_secret">Consumer Secret</label>
                    <input type="text" id="twitter_consumer_secret" name="twitter_consumer_secret" value="<?php echo $publikSettings['twitter']['consumer_secret']; ?>">
                </div>

                <?php echo $twitterLoginButton; ?>

                <div class="input-field">
                    <input type="hidden" name="refresh_data" value="1">
                    <button class="btn-send">Salvar</button>
                </div>
            </form>

        </div>

<?php
    }
}