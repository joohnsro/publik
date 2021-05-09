<?php
namespace Publik\Pages;

error_reporting(E_ALL);
ini_set("display_errors","On");

use Publik\Pages\PagesInterface;
use Publik\Services\Ajax;
use Publik\Services\FacebookService;
use Publik\Services\TwitterService;
use Publik\Entities\Post;

use Publik\Services\SocialService;



class Index implements PagesInterface
{
    public function init()
    {
        $self = new self();
        add_action( 'admin_enqueue_scripts', array( $self, 'stylesheet' ) );
        add_action( 'admin_enqueue_scripts', array( $self, 'script' ) );
    }

    public function stylesheet()
    {
        wp_enqueue_style( 'publik-admin-style', PUBLIK_URL . 'assets/css/styles.css', array(), '1.0.0', 'all' );
    }

    public function script(){
        wp_enqueue_script( 'publik-admin-script', PUBLIK_URL . 'assets/js/scripts.js', array('jquery'), '1.0.0', true );

        $custom = $this->groupDataToAjax();

        Ajax::set( 'publik-admin-script', $custom );

        wp_enqueue_media();
    }

    public function html()
    {   
?>
        <div id="publik-plugin">
            <div class="header">
                <h3>
                    Publik
                    <a class="btn-send" id="publik-open-lightbox">+</a>
                </h3>
                <p>
                    Publique e agende suas próximas novidades.
                </p>
            </div>

            <ul id="post-list"></ul>
            
            <div class="action">
                <button href="#" id="publik-save-button" class="btn-send">Salvar</button>
                <div id="publik-save-message"></div>
            </div>

            <div id="publik-lightbox">
                <div class="publish-content">
                    <div class="header">
                        <div class="title">
                            Agendamento de publicação
                        </div>
                        <div class="close">
                            <button id="publik-close-lightbox">✖</button>
                        </div>
                    </div>
                    <div class="content">

                        <div class="column">
                            <div class="input-field">
                                <label for="publish-title">Título</label>
                                <input type="text" id="publish-title" name="publish-title">
                            </div>

                            <div class="input-field">
                                <label for="publish-content">Conteúdo</label>
                                <?php wp_editor( '', "publish-content", ['textarea_rows' => 5] ); ?>
                            </div>

                            <div class="input-field">
                                <label for="publish-author">Autor</label>
                                <select id="publish-author" name="publish-author">
                                    <option value="" disabled></option>
                                </select>
                            </div>
                        </div>

                        <div class="column">
                            <div class="input-field checkbox">
                                <label for="publish-now">
                                    <input type="checkbox" id="publish-now" name="publish-now" checked>
                                    Publicar automaticamente
                                </label>
                            </div>                        

                            <div id="no-automatic" class="input-field schedule">
                                <label for="publish-schedule">Agendamento</label>
                                <input type="datetime-local" id="publish-schedule" name="publish-schedule">
                            </div>

                            <div class="input-field checkbox social">
                                <label class>Compartilhar nas redes</label>
                                <label for="publish-facebook">
                                    <input type="checkbox" id="publish-facebook" name="publish-facebook" checked>
                                    Facebook
                                </label>
                                <label for="publish-twitter">
                                    <input type="checkbox" id="publish-twitter" name="publish-twitter" checked>
                                    Twitter
                                </label>
                            </div> 

                            <div class="input-field inline-button">
                                <label for="publish-featured">Imagem principal</label>
                                <button id="publish-lightbox-featured" class="btn">Selecionar imagem</button>
                                <input type="hidden" id="publish-featured" name="publish-featured">
                            </div>

                            <div class="input-field">
                                <label for="publish-categories">Categorias</label>
                                <select id="publish-categories" name="publish-categories" multiple>
                                    <option value="" disabled></option>
                                </select>
                            </div>

                            <div class="input-field">
                                <label for="publish-tags">Tags</label>
                                <select id="publish-tags" name="publish-tags" multiple>
                                    <option value="" disabled></option>
                                </select>
                            </div>
                            
                        </div>

                    </div>
                    <div class="footer">
                        <a href="#" id="publik-add-post" class="btn-send">Adicionar</a>
                    </div>
                </div>
            </div>

        </div>
<?php
    }    

    public function ajax()
    {        
        $self = new self();
        Ajax::load( 'publik_publish_response', array( $self, 'publik_publish_response' ) );
    }

    public function publik_publish_response()
    {
        $posts = $_POST['data'];
        $posts_ids = [];
        
        foreach( $posts as $post ) { 

            $entity = new Post();
            $post_id = $entity->save($post);

            $args = [
                'schedule'  => get_gmt_from_date($post['schedule']),
                'message'   => $post['title'],
                'link'      => 'https://imprensaabc.com.br/2021/04/29/teatro-municipal-de-santo-andre-celebra-50-anos-com-serie-de-depoimentos/'
                //'link'      => get_the_permalink($post_id)
            ];

            $social = new SocialService([
                'facebook' => $post['facebook'] === 'true' ? true : false,
                'twitter'  => $post['twitter'] === 'true' ? true : false
            ]);

            $publishNow = $post['now'] === 'true' ? true : false;
            $social->publish( $args, $publishNow );
                
            if ( $post_id ) {
                array_push( $posts_ids, $post_id );
            }           
           
        }        

        echo \json_encode($posts_ids);
        exit;
    }

    public function groupDataToAjax()
    {
        /**
         * Get all authors
         */
        $authors_data = get_users( ['role' => 'Author'] );
        $authors = array_map(function( $author ){
            return [
                'id'            => $author->ID,
                'display_name'  => $author->display_name
            ];
        }, $authors_data);

        /**
         * Get all categories
         */
        $categories_data = get_terms( 'category', array(
            'hide_empty' => false,
        ) );
        $categories = array_map(function( $category ){
            return [
                'id'    => $category->term_id,
                'name'  => $category->name
            ];
        }, $categories_data);

        /**
         * Get all tags
         */
        $tags_data = get_terms( 'post_tag', array(
            'hide_empty' => false,
        ) );
        $tags = array_map(function( $tag ){
            return [
                'id'    => $tag->term_id,
                'name'  => $tag->name
            ];
        }, $tags_data);

        return [
            'authors'    => $authors,
            'categories' => $categories,
            'tags'       => $tags
        ];
    }
}