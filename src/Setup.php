<?php
namespace Publik;

use Publik\Pages\Index as PageIndex;
use Publik\Pages\Settings as PageSettings;

class Setup {

    public $availablePages = array();

    public static function init()
    {
        $self = new self();

        $self->availablePages = array(
            'publik'            => new PageIndex(),
            'publik_settings'   => new PageSettings()
        );    

        add_action( 'admin_menu', array( $self, 'menu' ) );
        
        $self->setDependencies();

        // Registra ajax das páginas
        $self->availablePages['publik']->ajax();

    }

    /**
     * Adiciona os menus
     */
    public function menu()
    {
        add_menu_page( 
            'Publik', 
            'Publik', 
            'manage_options', 
            'publik', 
            array( $this, 'handlePages' ),
            'dashicons-editor-ul',
            4
        );

        add_submenu_page( 
            'publik',
            'Configurações', 
            'Configurações', 
            'manage_options', 
            'publik_settings', 
            array( $this, 'handlePages' ),
            null
        );
    }

    /**
     * Se tiver o atributo page ele o retorna,
     * caso contrário ele retorna falso
     */
    public function getCurrentPage()
    {
        if ( ! isset( $_GET['page'] ) ) { return false; }

        $target_page = $_GET['page'];
     
        if ( ! array_key_exists( $target_page, $this->availablePages ) ) { 
            return false; 
        }

        return $this->availablePages[$target_page];
    }

    /**
     * Pega a página atual e inicializa as dependências stylesheet e script
     */
    public function setDependencies()
    {
        $page = $this->getCurrentPage();

        if ( ! $page ) return false;

        $page->init();
    }

    /**
     * Pega a página atual e exibe o conteúdo html
     */
    public function handlePages()
    {
        $page = $this->getCurrentPage();

        if ( ! $page ) return false;
        
        $page->html();
    }

}