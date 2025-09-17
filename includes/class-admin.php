<?php
/**
 * Admin functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class PFL_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        require_once PFL_PLUGIN_PATH . 'includes/class-database-utils.php';
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Passrider Flight Loads',
            'Flight Loads',
            'manage_options',
            'passrider-flight-loads',
            array($this, 'admin_dashboard'),
            'dashicons-airplane',
            30
        );
        
        add_submenu_page(
            'passrider-flight-loads',
            'Users',
            'Users',
            'manage_options',
            'pfl-users',
            array($this, 'admin_users')
        );
        
        add_submenu_page(
            'passrider-flight-loads',
            'Flight Requests',
            'Requests',
            'manage_options',
            'pfl-requests',
            array($this, 'admin_requests')
        );
        
        add_submenu_page(
            'passrider-flight-loads',
            'Settings',
            'Settings',
            'manage_options',
            'pfl-settings',
            array($this, 'admin_settings')
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'passrider-flight-loads') !== false || strpos($hook, 'pfl-') !== false) {
            // Flowbite JS
            wp_enqueue_script('flowbite', 'https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js', array('jquery'), '3.1.2', true);
            wp_enqueue_script('pfl-admin-js', PFL_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), PFL_VERSION, true);
            wp_enqueue_style('pfl-admin-css', PFL_PLUGIN_URL . 'assets/css/dist/style.css', array(), PFL_VERSION);
            
            // Localize script for AJAX
            wp_localize_script('pfl-admin-js', 'pfl_admin_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pfl_admin_nonce')
            ));
        }
    }
    
    public function admin_dashboard() {
        $stats = array(
            'total_users' => PFL_Database_Utils::get_total_users(),
            'pending_users' => PFL_Database_Utils::get_pending_users(),
            'active_requests' => PFL_Database_Utils::get_active_requests()
        );
        include PFL_PLUGIN_PATH . 'templates/admin/dashboard.php';
    }
    
    public function admin_users() {
        $pending_users = PFL_Database_Utils::get_users_by_status('pending');
        $approved_users = PFL_Database_Utils::get_users_by_status('approved', 100);
        $denied_users = PFL_Database_Utils::get_users_by_status('denied');
        include PFL_PLUGIN_PATH . 'templates/admin/users.php';
    }
    
    public function admin_requests() {
        $flight_requests = PFL_Database_Utils::get_all_flight_requests();
        include PFL_PLUGIN_PATH . 'templates/admin/requests.php';
    }
    
    public function admin_settings() {
        if (isset($_POST['submit_airline'])) {
            $this->handle_airline_submission();
        }
        
        $airlines = PFL_Database_Utils::get_airlines();
        include PFL_PLUGIN_PATH . 'templates/admin/settings.php';
    }
    
    private function handle_airline_submission() {
        if (!wp_verify_nonce($_POST['pfl_nonce'], 'pfl_admin_nonce')) {
            wp_die('Security check failed');
        }
        
        $name = sanitize_text_field($_POST['airline_name']);
        $iata_code = sanitize_text_field($_POST['iata_code']);
        $domain = sanitize_text_field($_POST['domain']);
        
        if (isset($_POST['airline_id']) && $_POST['airline_id']) {
            // Update existing airline
            PFL_Database_Utils::update_airline($_POST['airline_id'], $name, $iata_code, $domain);
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success"><p>Airline updated successfully!</p></div>';
            });
        } else {
            // Create new airline
            PFL_Database_Utils::create_airline($name, $iata_code, $domain);
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success"><p>Airline created successfully!</p></div>';
            });
        }
    }
}
