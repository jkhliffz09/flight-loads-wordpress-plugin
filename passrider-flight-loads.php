<?php
/**
 * Plugin Name: Passrider Flight Loads
 * Plugin URI: https://passrider.com/
 * Description: A comprehensive flight load request system for airline employees and retirees.
 * Version: 1.3.24
 * Author: khliffz
 * Author URI: https://fb.me/khliffz
 * License: GPL v2 or later
 * Text Domain: passrider-flight-loads
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('PFL_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PFL_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('PFL_VERSION', '1.3.24');

require_once PFL_PLUGIN_PATH . 'includes/class-database-utils.php';

if ( ! function_exists('pfl_flightload_form') ) {
    function pfl_flightload_form($request_id) {
        ob_start(); ?>
        
        <!-- Inline Expander Form -->
        <div id="flightload-<?= esc_attr($request_id); ?>" class="give-load-form mt-4 p-4 bg-gray-50 border rounded-lg hidden">
            <form class="pfl-flight-load-request-form">
                <p class="font-semibold mb-3">Provide Flight Load Info</p>

                <!-- Checkboxes for Cabins -->
                <div class="flex flex-col gap-2 mb-4">
                    <?php
                    $cabins = [
                        'first_class' => 'First Class',
                        'business_class' => 'Business Class',
                        'premium_economy_class' => 'Premium Economy Class',
                        'economy_class' => 'Economy Class'
                    ];
                    foreach ($cabins as $key => $label) : ?>
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" 
                                   class="cabin-toggle !h-4 !w-4" 
                                   data-request="<?= esc_attr($request_id); ?>" 
                                   data-cabin="<?= esc_attr($key); ?>" 
                                   name="class_types[]" 
                                   value="<?= esc_attr($key); ?>" />
                            <?= esc_html($label); ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div id="cabin-sections-<?= esc_attr($request_id); ?>" class="space-y-4"></div>
                
                <div>
                    <label for="flight_load_notes_<?= esc_attr($request_id); ?>" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea id="flight_load_notes_<?= esc_attr($request_id); ?>" 
                              name="notes" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" 
                              placeholder="Additional information..."></textarea>
                </div>

                <input type="hidden" name="special" value="special">
                <input type="hidden" name="request_id" value="<?= esc_attr($request_id); ?>">

                <!-- Buttons -->
                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" 
                            class="cancel-btn px-3 py-1 rounded-lg border text-gray-600 hover:bg-gray-100 text-sm" 
                            data-request="<?= esc_attr($request_id); ?>">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-3 py-1 rounded-lg bg-blue-600 text-white hover:bg-blue-700 text-sm">
                        Submit
                    </button>
                </div>
            </form>
        </div>

        <?php
        return ob_get_clean();
    }
}

if ( ! function_exists('pfl_render_request_card') ) {
    function pfl_render_request_card($request, $time_ago, $fill, $isOwner, $loads) {
        ob_start(); 

        ?>
        <div class="flex items-start justify-between mb-3">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-gray-300"></div>
                <div>
                    <p class="font-semibold text-gray-800 m-0 p-0">
                        <?= esc_html($request->requester_name); ?> 
                        <span class="px-2 py-1 text-xs font-medium rounded-full <?= esc_attr($fill); ?>">
                            <?= esc_html($request->status); ?>
                        </span>
                    </p>
                    <!-- Time Ago -->
                    <p class="text-xs text-gray-500 m-0">
                        <span class="whitespace-nowrap"><?= esc_html($time_ago); ?></span>
                    </p>
                </div>
            </div>

            <!-- Right side: Time + Menu -->
            <div class="flex items-center space-x-2">

                <!-- Dropdown -->
                <div class="relative">
                    <button 
                        type="button" 
                        class="p-1 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100 hover:outline-none focus:outline-none"
                        onclick="this.nextElementSibling.classList.toggle('hidden')"
                    >
                        <!-- 3-dot icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </button>

                    <!-- Dropdown menu -->
                    <div class="hidden absolute right-0 mt-2 w-28 bg-white border border-gray-200 rounded-md shadow-lg z-50">
                        <?php if($isOwner): ?>
                        <a href="/flightloads/request?edit_id=<?= $request->id; ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 !no-underline">Edit</a>
                        <a href="javascript:void(0)" 
                           class="delete-request block px-4 py-2 text-sm text-red-600 hover:bg-gray-100 !no-underline" 
                           data-request-id="<?= $request->id; ?>">
                           Delete
                        </a>
                        <?php else: ?>
                        <?php endif; ?> 
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 !no-underline">Share</a> 
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 !no-underline">Report</a> 
                    </div>
                      
                </div>
            </div>
        </div>

        <p class="font-semibold text-gray-800 m-0"><?= esc_html($request->notes); ?></p>
        <p class="text-sm text-gray-500 m-0">
            Requested flight 
            <b><?= esc_html($request->airline_code.$request->flight_number.'/'.$request->aircraft); ?></b> · 
            <b class="text-blue-600"><?= esc_html($request->from_airport_id.' → '.$request->to_airport_id); ?></b> · 
            <?= esc_html(date_format(date_create($request->travel_date), 'F d, Y')); ?>
        </p>
        <?php

        echo pfl_render_flight_load_responses($loads);

        return ob_get_clean();
    }
}

function pfl_render_flight_load_responses($responses) {
    $utc_now = time();

    // Or, as MySQL datetime string in UTC
    $utc_mysql = gmdate('Y-m-d H:i:s');

    ob_start();
    foreach ($responses as $response) {
        $load_data = !empty($response->flight_load_data) 
            ? json_decode($response->flight_load_data, true) 
            : [];

        $time_ago = human_time_diff( strtotime( $response->created_at ), $utc_now ) . ' ago';
        ?>
        
        <div class="p-4 mb-4 bg-white border border-gray-200 rounded-lg shadow-sm mt-2">
            <p class="font-semibold text-gray-800 mb-2 p-2 text-center bg-green-100 text-green-800 w-full">FLIGHT LOADS</p>
            <!-- Header -->
            <div class="flex items-center space-x-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-gray-300"></div>
                <div>
                    <p class="font-semibold text-gray-800 m-0 p-0">
                        <?= esc_html($response->giver_name); ?> 
                    </p>
                    <!-- Time Ago -->
                    <p class="text-xs text-gray-500 m-0">
                        <span class="whitespace-nowrap"><?= esc_html($time_ago); ?></span>
                    </p>
                </div>
            </div>

            <!-- Flight Load Details -->
            <?php if (!empty($load_data)): ?>
                <div class="grid gap-3">
                    <?php foreach ($load_data as $cabin => $details): ?>
                        <div class="p-3 border border-gray-200 rounded-md bg-gray-50">
                            <p class="text-sm font-medium text-gray-800 capitalize mb-1">
                                <?= str_replace('_', ' ', $cabin); ?>
                            </p>
                            <div class="grid grid-cols-2 gap-2 text-xs text-gray-600">
                                <span>Capacity: <b><?= intval($details['cap']); ?></b></span>
                                <span>Held: <b><?= intval($details['held']); ?></b></span>
                                <span>Booked: <b><?= esc_html($details['booked']); ?></b></span>
                                <span>Standbys: <b><?= intval($details['standbys']); ?></b></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php
    }
    return ob_get_clean();
}




// Main plugin class
class PassriderFlightLoads {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        add_action('init', array($this, 'register_rewrites'));
        add_filter('query_vars', array($this, 'register_query_vars'));
        // Hook into REST API init
        add_action('rest_api_init', [$this, 'register_rest_endpoints']);
        // Register cron schedule on init
        add_action('init', array($this, 'register_cron'));
        // Hook the actual update function
        add_action('pfl_expire_flight_requests', array($this, 'update_expired_flights'));
    }
    
    public function init() {
        //flush_rewrite_rules();
        // Load plugin textdomain
        load_plugin_textdomain('passrider-flight-loads', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Initialize components
        $this->load_dependencies();
        $this->init_hooks();

        // Hook login redirect
        add_filter('login_redirect', array($this, 'redirect_flight_loads_user'), 10, 3);
        add_filter('logout_redirect', array($this, 'redirect_after_logout'), 10, 3);
    }

    /**
     * Register cron event if not already scheduled
     */
    public function register_cron() {
        if (!wp_next_scheduled('pfl_expire_flight_requests')) {
            wp_schedule_event(time(), 'daily', 'pfl_expire_flight_requests');
        }
    }

    /**
     * Update expired flight requests
     */
    public function update_expired_flights() {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_flight_requests';

        $wpdb->query("
            UPDATE {$table}
            SET status = 'expired'
            WHERE travel_date < CURDATE()
            AND status != 'expired'
        ");
    }

    /**
     * Register custom REST API routes
     */
    public function register_rest_endpoints() {
        register_rest_route('pfl/v1', '/notifications', [
            'methods'  => 'GET',
            'callback' => [$this, 'get_notifications'],
            'permission_callback' => function () {
                return is_user_logged_in();
            },
        ]);

        // Mark as read
        register_rest_route('pfl/v1', '/notifications/read', [
            'methods'  => 'POST',
            'callback' => [$this, 'rest_mark_read'],
            'permission_callback' => function () {
                return is_user_logged_in();
            },
        ]);

        // Clear all
        register_rest_route('pfl/v1', '/notifications/clear', [
            'methods'  => 'POST',
            'callback' => [$this, 'rest_clear_notifications'],
            'permission_callback' => function () {
                return is_user_logged_in();
            },
        ]);
    }

    public function get_notifications(WP_REST_Request $request) {
        global $wpdb;
        $user_id = get_current_user_id();
        $table   = $wpdb->prefix . 'pfl_notifications';

        $items = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table WHERE user_id=%d ORDER BY created_at DESC LIMIT 5", $user_id)
        );

        return [
            'count' => count($items),
            'items' => array_map(function ($row) {
                return [
                    'message'    => $row->message,
                    'created_at' => $row->created_at,
                ];
            }, $items),
        ];
    }

    public function mark_notification_read($notification_id, $user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_notifications';
        return $wpdb->update(
            $table,
            ['is_read' => 1],
            ['id' => $notification_id, 'user_id' => $user_id],
            ['%d'],
            ['%d', '%d']
        );
    }

    public function clear_notifications($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_notifications';
        return $wpdb->delete(
            $table,
            ['user_id' => $user_id],
            ['%d']
        );
    }

    public function rest_mark_read(WP_REST_Request $request) {
        $id = intval($request->get_param('id'));
        $user_id = get_current_user_id();
        $success = $this->mark_notification_read($id, $user_id);

        return rest_ensure_response(['success' => (bool) $success]);
    }

    public function rest_clear_notifications(WP_REST_Request $request) {
        $user_id = get_current_user_id();
        $success = $this->clear_notifications($user_id);

        return rest_ensure_response(['success' => (bool) $success]);
    }

    public function register_rewrites() {
        // Match /flightloads/something
        add_rewrite_rule(
            '^flightloads/([^/]+)/?',
            'index.php?pagename=flightloads&pfl_subpage=$matches[1]',
            'top'
        );
    }

    public function register_query_vars($vars) {
        $vars[] = 'pfl_subpage';
        return $vars;
    }
    
    private function load_dependencies() {
        require_once PFL_PLUGIN_PATH . 'includes/class-admin.php';
        require_once PFL_PLUGIN_PATH . 'includes/class-frontend.php';
        require_once PFL_PLUGIN_PATH . 'includes/class-ajax.php';
        require_once PFL_PLUGIN_PATH . 'includes/class-shortcodes.php';
    }
    
    private function init_hooks() {
        // Initialize admin
        if (is_admin()) {
            new PFL_Admin();
        }
        
        // Initialize frontend
        new PFL_Frontend();
        new PFL_Ajax();
        new PFL_Shortcodes();
    }
    
    public function activate() {

        require_once PFL_PLUGIN_PATH . 'includes/class-database.php';
        // Create database tables
        PFL_Database::create_tables();
        
        // Set default options
        add_option('pfl_version', PFL_VERSION);
        
    }
    
    public function deactivate() {
        // Clean up if needed
        flush_rewrite_rules();
    }

    public function redirect_flight_loads_user($redirect_to, $requested_redirect_to, $user) {
        
        if (!is_wp_error($user) && $user instanceof WP_User) {
            $user_profile = PFL_Database_Utils::get_user_profile($user->ID);

            if ($user_profile && $user_profile->status) {
                return site_url('/flight-loads-account/', 'https');
            }
        }

        return $redirect_to;
    }

    public function redirect_after_logout($redirect_to, $requested_redirect_to, $user) {
        // For example, redirect all users to home page after logout
        if (!is_wp_error($user) && $user instanceof WP_User) {
            $user_profile = PFL_Database_Utils::get_user_profile($user->ID);

            if ($user_profile && $user_profile->status) {
                return site_url('/flight-loads-login/', 'https');
            }
        }
    }
}

// Initialize the plugin
new PassriderFlightLoads();
