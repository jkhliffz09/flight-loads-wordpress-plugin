<?php
/**
 * Shortcodes functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class PFL_Shortcodes {
    
    public function __construct() {
        add_shortcode('pfl_application_form', array($this, 'application_form_shortcode'));
        add_shortcode('pfl_user_account', array($this, 'user_account_shortcode'));
        add_shortcode('pfl_flight_requests', array($this, 'flight_requests_shortcode'));
        add_shortcode('pfl_login_form', array($this, 'login_form_shortcode'));
        add_shortcode('pfl_application_success', array($this, 'application_success_shortcode'));
        add_shortcode('pfl_login_form', array($this, 'login_form_shortcode'));
        
    }
    
    public function application_form_shortcode($atts) {
        $atts = shortcode_atts(array(
            'redirect_url' => home_url('/flightloads')
        ), $atts);
        
        ob_start();
        include PFL_PLUGIN_PATH . 'templates/shortcodes/application-form.php';
        return ob_get_clean();
    }

    public function application_success_shortcode(){
        ob_start();
        include PFL_PLUGIN_PATH . 'templates/shortcodes/submitted-success.php';
        return ob_get_clean();
    }
    
    public function user_account_shortcode($atts) {
        if (!is_user_logged_in()) {
            ob_start();
            include PFL_PLUGIN_PATH . 'templates/shortcodes/user-login-prompt.php';
            return ob_get_clean();
        }
        
        ob_start();
        include PFL_PLUGIN_PATH . 'templates/shortcodes/user-account.php';
        return ob_get_clean();
    }
    
    public function flight_requests_shortcode($atts) {
        $atts = shortcode_atts(array(
            'theme' => 'default'
        ), $atts);

        if (!is_user_logged_in()) {
            ob_start();
            include PFL_PLUGIN_PATH . 'templates/shortcodes/user-login-prompt.php';
            return ob_get_clean();
        }

        // Base slug of the current page
        $base_slug = get_post_field('post_name', get_post());

        // Break URL path into segments
        $request_uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $parts = explode('/', $request_uri);

        // Find base slug position in path
        $pos = array_search($base_slug, $parts);

        // Subpage is the next segment after base slug
        $subpage = ($pos !== false && isset($parts[$pos + 1])) ? $parts[$pos + 1] : 'dashboard';

        switch ($subpage) {
            case 'my-requests':
                $template = 'pages/my-requests.php';
                break;
            case 'request':
                $template = 'pages/requests.php';
                break;
            case 'about':
                $template = 'pages/about.php';
                break;
            case 'account':
                $template = 'pages/account.php';
                break;
            default:
                $template = $atts['theme'] === 'default'
                    ? 'shortcodes/flight-requests.php'
                    : 'pages/dashboard.php';
                break;
        }

        ob_start();
        $atts['theme'] === 'default' ? '' : include PFL_PLUGIN_PATH . 'templates/pages/nav.php';
        include PFL_PLUGIN_PATH . 'templates/' . $template;
        return ob_get_clean();
    }

    public function nav_link($url, $label, $ismobile=false) {
        global $wp;
        $current_url = trim( $_SERVER['REQUEST_URI'], '/' );
        $is_active = (site_url($current_url) === $url);

        $classes = 'rounded-md px-3 py-2 text-sm font-medium !no-underline ';
        $classes .= $ismobile ? 'block ' : '';
        $classes .= $is_active 
            ? 'bg-gray-900 text-white' 
            : 'text-gray-300 hover:bg-white/5 hover:text-white';

        echo '<a href="' . esc_url($url) . '" class="' . esc_attr($classes) . '">' . esc_html($label) . '</a>';
    }

    
    public function login_form_shortcode($atts) {
        if (is_user_logged_in()) {
            return '<p>You are already logged in.</p>';
        }
        
        $atts = shortcode_atts(array(
            'redirect_url' => home_url('/flightloads')
        ), $atts);
        
        ob_start();
        include PFL_PLUGIN_PATH . 'templates/shortcodes/login-form.php';
        return ob_get_clean();
    }

    public static function getStatusBadgeClass($status) {
        switch ($status) {
            case "pending":
                return "bg-yellow-100 text-yellow-800";
            case "answered":
                return "bg-green-100 text-green-800";
            case "expired":
                return "bg-red-100 text-red-800";
            default:
                return "bg-gray-100 text-gray-800";
        }
    }


}
