<?php
/**
 * AJAX functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class PFL_Ajax {
    
    public function __construct() {
        // Admin AJAX actions
        add_action('wp_ajax_pfl_approve_user', array($this, 'approve_user'));
        add_action('wp_ajax_pfl_deny_user', array($this, 'deny_user'));
        add_action('wp_ajax_pfl_delete_request', array($this, 'delete_request'));
        add_action('wp_ajax_pfl_save_airline', array($this, 'save_airline'));
        add_action('wp_ajax_pfl_delete_airline', array($this, 'delete_airline'));
        add_action('wp_ajax_pfl_get_user_profile', array($this, 'pfl_get_user_profile'));
        
        // Frontend AJAX actions
        add_action('wp_ajax_pfl_search_airlines', array($this, 'search_airlines'));
        add_action('wp_ajax_nopriv_pfl_search_airlines', array($this, 'search_airlines'));
        add_action('wp_ajax_pfl_get_all_airlines', array($this, 'get_all_airlines'));
        add_action('wp_ajax_nopriv_pfl_get_all_airlines', array($this, 'get_all_airlines'));
        add_action('wp_ajax_pfl_get_all_airports', array($this, 'pfl_get_all_airports'));
        add_action('wp_ajax_nopriv_pfl_get_all_airports', array($this, 'pfl_get_all_airports'));
        add_action('wp_ajax_pfl_get_all_airlines_xml', array($this, 'pfl_get_all_airlines'));
        add_action('wp_ajax_nopriv_pfl_get_all_airlines_xml', array($this, 'pfl_get_all_airlines'));
        add_action('wp_ajax_pfl_search_airports', array($this, 'search_airports'));
        add_action('wp_ajax_nopriv_pfl_search_airports', array($this, 'search_airports'));
        add_action('wp_ajax_pfl_submit_application', array($this, 'submit_application'));
        add_action('wp_ajax_nopriv_pfl_submit_application', array($this, 'submit_application'));
        add_action('wp_ajax_pfl_submit_flight_request', array($this, 'submit_flight_request'));
        add_action('wp_ajax_pfl_update_flight_request', array($this, 'update_flight_request'));
        add_action('wp_ajax_pfl_like_response', array($this, 'like_response'));
        add_action('wp_ajax_pfl_update_password', array($this, 'update_password'));
        add_action('wp_ajax_pfl_send_verification', array($this, 'send_verification'));
        add_action('wp_ajax_nopriv_pfl_send_verification', array($this, 'send_verification'));
        add_action('wp_ajax_pfl_verify_code', array($this, 'verify_code'));
        add_action('wp_ajax_nopriv_pfl_verify_code', array($this, 'verify_code'));

        add_action('wp_ajax_pfl_toggle_like', array($this, 'toggle_like'));
        add_action('wp_ajax_pfl_add_comment', array($this, 'add_comment'));
        add_action('wp_ajax_pfl_get_comments', array($this, 'get_comments'));
        add_action('wp_ajax_pfl_get_flight_requests', array($this, 'get_flight_requests'));
        add_action('wp_ajax_pfl_get_flight_requests_by_id', array($this, 'pfl_get_flight_requests_by_id'));
        add_action('wp_ajax_pfl_get_respond_requests', array($this, 'get_respond_requests'));
        add_action('wp_ajax_pfl_submit_flight_load', array($this, 'submit_flight_load'));
        add_action('wp_ajax_pfl_bulk_user_action', array($this, 'pfl_bulk_user_action'));

        add_action('wp_ajax_pfl_search_flights', array($this, 'pfl_search_flights'));
        add_action('wp_ajax_pfl_delete_flight_request', array($this, 'delete_flight_request'));
        
        require_once PFL_PLUGIN_PATH . 'includes/class-database-utils.php';
        require_once PFL_PLUGIN_PATH . 'includes/class-email-handler.php';
    }
    
    public function approve_user() {
        check_ajax_referer('pfl_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $user_id = intval($_POST['user_id']);
        
        $result = PFL_Database_Utils::update_user_approval_status($user_id, 'approved');
        
        if ($result !== false) {
            // Send approval email
            PFL_Email_Handler::send_approval_email($user_id);
            
            wp_send_json_success('User approved successfully');
        } else {
            wp_send_json_error('Failed to approve user');
        }
    }

    public function pfl_search_flights() {
        check_ajax_referer('pfl_frontend_nonce', 'nonce');

        $from = sanitize_text_field($_POST['from'] ?? '');
        $to   = sanitize_text_field($_POST['to'] ?? '');
        $date = sanitize_text_field($_POST['date'] ?? '');

        if (!$from || !$to || !$date) {
            wp_send_json_error('Missing required fields');
        }

        $url = "http://services.flightlookup.com/v1/xml/TimeTable/{$from}/{$to}/{$date}/?Airline=---&Results=10&Language=en&Interline=Y&CodeShare=N&Nofilter=Y&Compression=MOST&subscription-key=ee573326b2c34c619eadfff56300ba16";

        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            wp_send_json_error('API request failed');
        }

        $body = wp_remote_retrieve_body($response);
        if (!$body) {
            wp_send_json_error('No data from API');
        }

        // Parse XML     
        $xml = simplexml_load_string($body);
        $ns = $xml->getNamespaces(true);

        error_log(json_encode($xml));

        if (!$xml) {
            wp_send_json_error('Invalid API response');
        }

        $flights = [];

        foreach ($xml->FlightDetails as $flight) {
            $leg = $flight->FlightLegDetails;

            $flights[] = [
                'airline'        => (string)$leg->MarketingAirline['CompanyShortName'],
                'aircraft'       => (string)$leg->Equipment['AirEquipType'],
                'number'         => (string)$leg['FlightNumber'],
                'departure'      => (string)$leg->DepartureAirport['LocationCode'],
                'arrival'        => (string)$leg->ArrivalAirport['LocationCode'],
                'departure_time' => (string)$leg['DepartureDateTime'],
                'arrival_time'   => (string)$leg['ArrivalDateTime'],
                'duration'       => (string)$leg['JourneyDuration'],
                'airline_code'   => (string)$leg->MarketingAirline['Code'],
            ];
        }


        wp_send_json_success($flights);
    }

    public function pfl_bulk_user_action() {
        check_ajax_referer('pfl_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $action = sanitize_text_field($_POST['bulk_action']);
        $user_ids = array_map('intval', $_POST['user_ids']);

        $updated = [];

        foreach ($user_ids as $user_id) {
            if ($action === 'approve') {
                $result = PFL_Database_Utils::update_user_approval_status($user_id, 'approved');
                if ($result !== false) {
                    PFL_Email_Handler::send_approval_email($user_id);
                    $updated[] = $user_id;
                }
            } elseif ($action === 'deny') {
                $result = PFL_Database_Utils::update_user_approval_status($user_id, 'denied');
                if ($result !== false) {
                    // Optional: send deny email
                    $updated[] = $user_id;
                }
            }
        }

        if (!empty($updated)) {
            wp_send_json_success(['updated' => $updated]);
        } else {
            wp_send_json_error('No users updated.');
        }
    }

    
    public function deny_user() {
        check_ajax_referer('pfl_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $user_id = intval($_POST['user_id']);
        
        $result = PFL_Database_Utils::update_user_approval_status($user_id, 'denied');
        
        if ($result !== false) {
            wp_send_json_success('User denied successfully');
        } else {
            wp_send_json_error('Failed to deny user');
        }
    }

    public function pfl_get_user_profile() {
        global $wpdb;

        if ( !current_user_can('manage_options')){
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $user_id = intval($_GET['user_id']);

        if (!$user_id) {
            wp_send_json_error(['message' => 'Invalid user ID']);
        }

        $table = $wpdb->prefix . 'pfl_user_profiles';
        $profile = PFL_Database_Utils::get_user_profile($user_id);

        if ($profile) {
            wp_send_json_success($profile);
        } else {
            wp_send_json_error(['message' => 'Profile not found']);
        }
    }
    
    public function delete_request() {
        check_ajax_referer('pfl_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $request_id = intval($_POST['request_id']);
        
        $result = PFL_Database_Utils::delete_flight_request($request_id);
        
        if ($result !== false) {
            wp_send_json_success('Request deleted successfully');
        } else {
            wp_send_json_error('Failed to delete request');
        }
    }

    public function get_all_airlines() {
        // No search term — just preload all airlines
        $results = PFL_Database_Utils::get_airlines('', 0); // 0 means no limit
        wp_send_json_success($results);
    }

    public function pfl_get_all_airports(){
        $response = wp_remote_get('https://services.flightlookup.com/v1/xml/airports/');
        $airports = [];

        if (!is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            $xml = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);

            if ($xml) {
                foreach ($xml->Airport as $airport) {
                    $airports[] = [                        
                        'name'      => (string) $airport['Name'],
                        'code'      => (string) $airport['IATACode'],  
                        'type'      => 'airport',                      
                    ];
                }
            }
        }

        wp_send_json_success($airports);
    }

    public function pfl_get_all_airlines(){
        $response = wp_remote_get('https://services.flightlookup.com/v1/xml/airlines');
        $airlines = [];

        if(!is_wp_error($response)){
            $body = wp_remote_retrieve_body($response);
            $xml = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);

            if($xml){
                foreach ($xml->Airline as $airline){
                    $airlines[] = [
                        'name' => (string) $airline['Name'],
                        'code' => (string) $airline['IATACode'],
                        'type' => 'airline',
                    ];
                }
            }
        }

        $airlines = [
            [
                'name' => 'Passrider',
                'code' => 'PZ',
                'type' => 'airline'
            ],
            [
                'name' => 'Ford Test',
                'code' => 'FT',
                'type' => 'airline'
            ],
            [
                'name' => 'Gmail',
                'code' => 'GM',
                'type' => 'airline'
            ]
        ];

        wp_send_json_success($airlines);
    }
        
    public function search_airlines() {
        $search = sanitize_text_field($_GET['search']);
        $results = PFL_Database_Utils::get_airlines($search, 10);
        wp_send_json_success($results);
    }
    
    public function search_airports() {
        $search = sanitize_text_field($_GET['search']);
        $results = PFL_Database_Utils::get_airports($search, 10);
        wp_send_json_success($results);
    }
    
    public function submit_application() {
        check_ajax_referer('pfl_application_nonce', 'pfl_nonce');
        
        // Validate required fields
        $required_fields = array('full_name', 'airline_id', 'status', 'username', 'email', 'password');
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error("Field $field is required");
            }
        }
        
        // Check if username or email already exists
        if (username_exists($_POST['username'])) {
            wp_send_json_error('Username already exists');
        }
        
        if (email_exists($_POST['email'])) {
            wp_send_json_error('Email already exists');
        }
        
        // Create WordPress user
        $user_data = array(
            'user_login' => sanitize_user($_POST['username']),
            'user_email' => sanitize_email($_POST['email']),
            'user_pass' => $_POST['password'],
            'display_name' => sanitize_text_field($_POST['full_name']),
            'first_name' => sanitize_text_field($_POST['full_name']),
            'role' => 'subscriber'
        );
        
        $user_id = wp_insert_user($user_data);
        
        if (is_wp_error($user_id)) {
            wp_send_json_error($user_id->get_error_message());
        }
        
        // Create user profile
        $profile_data = array(
            'airline_id' => intval($_POST['airline_id']),
            'status' => sanitize_text_field($_POST['status'])
        );
        
        // Add retired user data if applicable
        $profile_data['airline_email'] = sanitize_email($_POST['airline_email']);
        $profile_data['airline_code'] = sanitize_text_field($_POST['airline_code']);
        $profile_data['phone_number'] = sanitize_text_field($_POST['phone_number']);
        $profile_data['employment_retirement_date'] = sanitize_text_field($_POST['employment_retirement_date']);
        $profile_data['airline_job'] = sanitize_text_field($_POST['airline_job']);
        $profile_data['years_worked'] = intval($_POST['years_worked']);
        // Handle file upload for retired ID
        if (!empty($_FILES['upload_id']) && $_FILES['upload_id']['size'] > 0) {
            $max_size = 5 * 1024 * 1024; // 5 MB
            
            if ($_FILES['upload_id']['size'] > $max_size) {
                wp_send_json_error('Max file size is 5MB');
            }

            $upload = wp_handle_upload($_FILES['upload_id'], array('test_form' => false));
            if (!isset($upload['error'])) {
                $profile_data['upload_id_url'] = $upload['url'];
            } else {
                wp_send_json_error($upload['error']);
            }
        }

        
        
        $profile_id = PFL_Database_Utils::create_user_profile($user_id, $profile_data);
        
        if ($profile_id) {
            // Send notification emails
            PFL_Email_Handler::send_application_notification($user_id);
            
            wp_send_json_success(array(
                'message' => 'Application submitted successfully',
                'redirect_url' => home_url('/application-submitted/')
            ));
        } else {
            // Clean up user if profile creation failed
            wp_delete_user($user_id);
            wp_send_json_error('Failed to create user profile');
        }
    }

    public function delete_flight_request(){
        if( !isset($_POST['request_id']) ){
            wp_send_json_error('Missing request ID.');
        }

        global $wpdb;
        $request_id = intval($_POST['request_id']);

        try {
            $result = PFL_Database_Utils::delete_flight_request($request_id);

            if($result !== false){
                wp_send_json_success('Flight request deleted successfully.');
            } else {
                wp_send_json_error('Could not delete. It may have related records (foreign key).');
            }
        } catch (Exception $e) {
            wp_send_json_error('Database error: '.$e->getMessage());
        }
    }

    function update_flight_request() {
        check_ajax_referer('pfl_flight_request_nonce', 'pfl_nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to submit requests');
        }

        $request_id = intval($_POST['request_id']);
        $current_user = get_current_user_id();
        $user_profile = PFL_Database_Utils::get_user_profile($current_user);
        $airline_id = $user_profile->airline_id;

        if (!$user_profile || $user_profile->approval_status !== 'approved') {
            wp_send_json_error('Your account must be approved to submit requests');
        }

        // Validate required fields
        $required_fields = array('request_airline_code', 'flight_number', 'from_airport', 'to_airport', 'travel_date');
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error("Field $field is required");
            }
        }

        $data = [
            'airline_code'  => sanitize_text_field($_POST['request_airline_code']),
            'flight_number' => sanitize_text_field($_POST['flight_number']),
            'from_airport_id'  => sanitize_text_field($_POST['from_airport']),
            'to_airport_id'    => sanitize_text_field($_POST['to_airport']),
            'travel_date'   => sanitize_text_field($_POST['travel_date']),
            'notes'         => sanitize_textarea_field($_POST['notes']),
        ];

        $request_id = PFL_Database_Utils::update_flight_request($data, $request_id, $current_user);

        if ($request_id) {
            wp_send_json_success(array(
                'message' => 'Flight request updated successfully',
                'request_id' => $request_id
            ));
        } else {
            wp_send_json_error(['error' => 'Failed to update flight request', 'data'=> $request_id]);
        }
    }

    
    public function submit_flight_request() {
        check_ajax_referer('pfl_flight_request_nonce', 'pfl_nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to submit requests');
        }
        
        $user_id = get_current_user_id();
        $user_profile = PFL_Database_Utils::get_user_profile($user_id);
        $airline_id = $user_profile->airline_id;
        
        if (!$user_profile || $user_profile->approval_status !== 'approved') {
            wp_send_json_error('Your account must be approved to submit requests');
        }
        
        // Validate required fields
        $required_fields = array('request_airline_code', 'flight_number', 'from_airport', 'to_airport', 'travel_date');
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error("Field $field is required");
            }
        }
        
        // Check for duplicate requests
        $duplicate_count = PFL_Database_Utils::check_duplicate_request(
            $user_id,
            intval($_POST['request_airline_code']),
            sanitize_text_field($_POST['flight_number']),
            sanitize_text_field($_POST['travel_date'])
        );
        
        if ($duplicate_count > 0) {
            wp_send_json_error('A similar request has been answered within the last hour');
        }
        
        // Format flight number (remove leading zeros)
        $flight_number = ltrim(sanitize_text_field($_POST['flight_number']), '0');
        if (empty($flight_number)) {
            $flight_number = '0';
        }
        
        $request_data = array(
            'airline_id' => $airline_id,
            'airline_code' => $_POST['request_airline_code'],
            'flight_number' => $flight_number,
            'aircraft'     => $_POST['aircraft'],
            'from_airport_id' => $_POST['from_airport'],
            'to_airport_id' => $_POST['to_airport'],
            'travel_date' => sanitize_text_field($_POST['travel_date']),
            'is_return' => isset($_POST['is_return']) ? 1 : 0,
            'notes' => sanitize_textarea_field($_POST['notes'])
        );
        
        // Add return flight data if applicable
        if (isset($_POST['is_return']) && $_POST['is_return']) {
            $request_data['return_airline_code'] = $_POST['return_airline_code'];
            $request_data['return_flight_number'] = ltrim(sanitize_text_field($_POST['return_flight_number']), '0');
            $request_data['return_from_airport_id'] = $_POST['to_airport']; // Switched
            $request_data['return_to_airport_id'] = $_POST['from_airport']; // Switched
            $request_data['return_travel_date'] = sanitize_text_field($_POST['return_travel_date']);
        }
        
        $request_id = PFL_Database_Utils::create_flight_request($user_id, $request_data);
        
        if ($request_id) {
            wp_send_json_success(array(
                'message' => 'Flight request submitted successfully',
                'request_id' => $request_id
            ));
        } else {
            wp_send_json_error(['error' => 'Failed to submit flight request', 'data'=> $request_id]);
        }
    }
    
    public function update_password() {
        check_ajax_referer('pfl_password_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in');
        }
        
        $user_id = get_current_user_id();
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_new_password'];
        
        // Verify current password
        $user = get_user_by('ID', $user_id);
        if (!wp_check_password($current_password, $user->user_pass, $user_id)) {
            wp_send_json_error('Current password is incorrect');
        }
        
        // Check if new passwords match
        if ($new_password !== $confirm_password) {
            wp_send_json_error('New passwords do not match');
        }
        
        // Update password
        wp_set_password($new_password, $user_id);
        
        wp_send_json_success('Password updated successfully');
    }
    
    public function send_verification() {
        $email = sanitize_email($_POST['email']);
        $airline_id = intval($_POST['airline_id']);
        $airline = false;

        if($airline_id !== 999999){        
            // Validate airline email
            $airline = true;
            if (!PFL_Database_Utils::validate_airline_email($email, $airline_id)) {
                wp_send_json_error('Email domain does not match selected airline'.$airline_id);
            }
        }
        
        // Check if email already exists
        if (PFL_Database_Utils::check_email_exists($email)) {
            wp_send_json_error('Email already exists in database');
        }
        
        // Generate verification code
        $code = sprintf('%06d', mt_rand(100000, 999999));
        
        // Store code in transient (expires in 10 minutes)
        set_transient('pfl_verification_' . md5($email), $code, 600);
        
        // Send verification email
        $sent = PFL_Email_Handler::send_verification_code($email, $code, $airline);
        
        if ($sent) {
            wp_send_json_success('Verification code sent');
        } else {
            wp_send_json_error('Failed to send verification code');
        }
    }
    
    public function verify_code() {
        $email = sanitize_email($_POST['email']);
        $code = sanitize_text_field($_POST['code']);
        
        // Get stored code
        $stored_code = get_transient('pfl_verification_' . md5($email));
        
        if (!$stored_code) {
            wp_send_json_error('Verification code expired or not found');
        }
        
        if ($code !== $stored_code) {
            wp_send_json_error('Invalid verification code');
        }
        
        // Delete the transient
        delete_transient('pfl_verification_' . md5($email));
        
        wp_send_json_success('Email verified successfully');
    }

    public function toggle_like() {
        check_ajax_referer('pfl_frontend_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to like requests');
        }
        
        $user_id = get_current_user_id();
        $request_id = intval($_POST['request_id']);
        
        // Check if user has access to this request (same airline domain)
        $user_profile = PFL_Database_Utils::get_user_profile($user_id);
        $request = PFL_Database_Utils::get_flight_request_by_id($request_id);
        
        if (!$user_profile || !$request ) {
            wp_send_json_error(['message' => 'Access denied', 'user'=> $user_profile, 'request'=>$request]);
        }
        
        $is_liked = PFL_Database_Utils::toggle_request_like($user_id, $request_id);
        $like_count = PFL_Database_Utils::get_request_like_count($request_id);
        
        wp_send_json_success(array(
            'is_liked' => $is_liked,
            'like_count' => $like_count
        ));
    }
    
    public function add_comment() {
        check_ajax_referer('pfl_frontend_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to comment');
        }
        
        $user_id = get_current_user_id();
        $request_id = intval($_POST['request_id']);
        $comment_text = sanitize_textarea_field($_POST['comment']);
        
        if (empty($comment_text)) {
            wp_send_json_error('Comment cannot be empty');
        }
        
        // Check if user has access to this request (same airline domain)
        $user_profile = PFL_Database_Utils::get_user_profile($user_id);
        $request = PFL_Database_Utils::get_flight_request_by_id($request_id);
        
        /*if (!$user_profile || !$request || $user_profile->airline_id !== $request->airline_id) {
            wp_send_json_error('Access denied');
        }*/

        if (!$user_profile || !$request) {
            wp_send_json_error('Access denied');
        }
        
        $comment_id = PFL_Database_Utils::add_request_comment($user_id, $request_id, $comment_text);
        
        if ($comment_id) {
            $comment = PFL_Database_Utils::get_comment_by_id($comment_id);
            wp_send_json_success(array(
                'comment' => $comment,
                'message' => 'Comment added successfully'
            ));
        } else {
            wp_send_json_error('Failed to add comment');
        }
    }
    
    public function get_comments() {
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in');
        }
        
        $user_id = get_current_user_id();
        $request_id = intval($_GET['request_id']);
        
        // Check if user has access to this request (same airline domain)
        $user_profile = PFL_Database_Utils::get_user_profile($user_id);
        $request = PFL_Database_Utils::get_flight_request_by_id($request_id);
        
        if (!$user_profile) {
            wp_send_json_error('Access denied');
        }
        
        $comments = PFL_Database_Utils::get_request_comments($request_id);
        wp_send_json_success($comments);
    }

    public function pfl_get_flight_requests_by_id(){
        if(!current_user_can('manage_options')){
            wp_send_json_error('You must be an admin.');
        }

        $request_id = $_GET['request_id'];

        $requests = PFL_Database_Utils::get_flight_request_by_id($request_id);

        wp_send_json_success($requests);

    }
    
    public function get_flight_requests() {
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in');
        }
        
        $user_id = get_current_user_id();
        $status = $_GET['filter_status'];
        $user_profile = PFL_Database_Utils::get_user_profile($user_id);
        
        if (!$user_profile || $user_profile->approval_status !== 'approved') {
            wp_send_json_error('Your account must be approved');
        }
        
        //$tab = sanitize_text_field($_GET['tab']);
        $requests = array();

        $requests = PFL_Database_Utils::get_user_flight_requests($user_id, $status);
        
        /*switch ($tab) {
            case 'my-requests':
                $requests = PFL_Database_Utils::get_user_flight_requests($user_id);
                break;
            case 'browse-requests':
                $requests = PFL_Database_Utils::get_flight_requests_for_airline($user_profile->airline_id);
                break;
            case 'give-loads':
                $requests = PFL_Database_Utils::get_flight_requests_for_airline($user_profile->airline_id, 'pending');
                break;
            default:
                $requests = PFL_Database_Utils::get_user_flight_requests($user_id);
                break;
        }*/
        
        // Add like status and counts for each request
        foreach ($requests as &$request) {
            $request->is_liked = PFL_Database_Utils::is_request_liked($user_id, $request->id);
            $request->like_count = PFL_Database_Utils::get_request_like_count($request->id);
            $request->comment_count = PFL_Database_Utils::get_request_comment_count($request->id);
        }

        
        wp_send_json_success($requests);
    }

    public function get_respond_requests() {
        check_ajax_referer('pfl_frontend_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in');
        }
        
        $user_id = get_current_user_id();
        $status = $_GET['filter_status'];
        $user_profile = PFL_Database_Utils::get_user_profile($user_id);
        $airline_id = intval($user_profile->airline_id);
        
        if (!$user_profile || $user_profile->approval_status !== 'approved') {
            wp_send_json_error('Your account must be approved');
        }
        
        // Get flight requests for the user's airline that they can respond to
        $requests = PFL_Database_Utils::get_flight_requests_for_airline($airline_id, $status);

        foreach ($requests as &$request) {
            $request->currentUser = (string)$user_id;
        }
        
        wp_send_json_success($requests);
    }

    public function submit_flight_load() {
        check_ajax_referer('pfl_frontend_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to submit flight loads');
        }
        
        $user_id = get_current_user_id();
        $request_id = intval($_POST['request_id']);
        $user_profile = PFL_Database_Utils::get_user_profile($user_id);
        
        if (!$user_profile || $user_profile->approval_status !== 'approved') {
            wp_send_json_error('Your account must be approved to submit flight loads');
        }
        
        // Check if user has access to this request (same airline)
        $request = PFL_Database_Utils::get_flight_request_by_id($request_id);
        if (!$request) {
            wp_send_json_error(['message' => 'Access denied', 'data' => $request, 'id'=>$request_id]);
        }
        
        // Validate that at least one class type is selected
        if (empty($_POST['class_types'])) {
            wp_send_json_error('Please select at least one class type');
        }
        
        // Prepare flight load data
        $flight_load_data = array();
        $class_types = $_POST['class_types'];
        
        foreach ($class_types as $class_type) {
            $booked = sanitize_text_field($_POST[$class_type . '_booked']);
            $cap = intval($_POST[$class_type . '_cap']);
            $held = intval($_POST[$class_type . '_held']);
            $standbys = intval($_POST[$class_type . '_standby']);
            
            $flight_load_data[$class_type] = array(
                'booked' => $booked,
                'cap' => $cap,
                'held' => $held,
                'standbys' => $standbys
            );
        }
        
        $notes = sanitize_textarea_field($_POST['notes']);
        
        // Submit flight load
        $result = PFL_Database_Utils::submit_flight_load($user_id, $request_id, $flight_load_data, $notes);
        
        if ($result) {
            wp_send_json_success('Flight load submitted successfully');
        } else {
            wp_send_json_error('Failed to submit flight load');
        }
    }
}
