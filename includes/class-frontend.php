<?php
/**
 * Frontend functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class PFL_Frontend {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('init', array($this, 'add_rewrite_rules'));
    }
    
    public function enqueue_frontend_scripts() {
        wp_enqueue_script('pfl-frontend-js', PFL_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), PFL_VERSION, true);
        wp_enqueue_style('pfl-frontend-css', PFL_PLUGIN_URL . 'assets/css/dist/style.css', array(), PFL_VERSION);
        wp_enqueue_style('pfl-custom-css', PFL_PLUGIN_URL . 'assets/css/custom.css', array(), PFL_VERSION);
        
        // Localize script for AJAX
        wp_localize_script('pfl-frontend-js', 'pfl_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pfl_frontend_nonce'),
            'rest_url'  => esc_url_raw( rest_url('pfl/v1/notifications') ),
            'rest_nonce'=> wp_create_nonce('wp_rest'),
        ));
    }
    
    public function add_rewrite_rules() {
        add_rewrite_rule('^flight-loads/apply/?$', 'index.php?pfl_page=apply', 'top');
        add_rewrite_rule('^flight-loads/account/?$', 'index.php?pfl_page=account', 'top');
        add_rewrite_rule('^flight-loads/requests/?$', 'index.php?pfl_page=requests', 'top');
        
        add_rewrite_tag('%pfl_page%', '([^&]+)');
        
        add_action('template_redirect', array($this, 'handle_custom_pages'));
    }
    
    public function handle_custom_pages() {
        $pfl_page = get_query_var('pfl_page');
        
        if ($pfl_page) {
            switch ($pfl_page) {
                case 'apply':
                    include PFL_PLUGIN_PATH . 'templates/frontend/apply.php';
                    exit;
                case 'account':
                    include PFL_PLUGIN_PATH . 'templates/frontend/account.php';
                    exit;
                case 'requests':
                    include PFL_PLUGIN_PATH . 'templates/frontend/requests.php';
                    exit;
            }
        }
    }
}
