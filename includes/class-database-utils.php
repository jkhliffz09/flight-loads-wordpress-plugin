<?php
/**
 * Database utility functions
 */

if (!defined('ABSPATH')) {
    exit;
}

class PFL_Database_Utils {
    
    // Airlines functions
    public static $loads;

    public static function get_airlines($search = '', $limit = 50) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_airlines';

        $limit_sql = $limit > 0 ? $wpdb->prepare("LIMIT %d", $limit) : '';

        if ($search) {
            $search = '%' . $wpdb->esc_like($search) . '%';
            return $wpdb->get_results($wpdb->prepare(
                "SELECT id, name, iata_code, domain 
                 FROM $table 
                 WHERE name LIKE %s OR iata_code LIKE %s OR domain LIKE %s 
                 ORDER BY name $limit_sql",
                $search, $search, $search
            ));
        }

        return $wpdb->get_results(
            "SELECT id, name, iata_code, domain 
             FROM $table 
             ORDER BY name $limit_sql"
        );
    }

    public static function add_notification($user_id, $actor_id, $request_id, $type, $message) {

        require_once PFL_PLUGIN_PATH . 'includes/class-email-handler.php';

        global $wpdb;
        $table = $wpdb->prefix . 'pfl_notifications';

        $wpdb->insert($table, [
            'user_id'    => $user_id,
            'actor_id'   => $actor_id,
            'request_id' => $request_id,
            'type'       => $type,
            'message'    => $message,
            'created_at' => current_time('mysql'),
        ]);

        $result = $wpdb->insert_id;
        $sender= self::get_user_profile($actor_id);
        $recipient = self::get_user_profile($user_id);
        $request = self::get_flight_request_by_id($request_id);
        $info = $request->airline_code.$request->flight_number.' '.$request->from_airport_id.' → '. $request->to_airport_id;

        if($result){
            if($type === 'request'){
                $sent = PFL_Email_Handler::send_related_notification($recipient->user_email, $recipient->display_name, $sender->user_email, $sender->display_name, $info);
            } else if($type === 'flightloads'){
                $sent = PFL_Email_Handler::send_flightload_notification_to_owner($recipient, $sender, $request, self::$loads);
            }
        }
    }

    
    public static function get_airline_by_id($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_airlines';
        
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }
    
    public static function get_airline_by_domain($domain) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_airlines';
        
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE domain = %s", $domain));
    }
    
    public static function create_airline($name, $iata_code, $domain) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_airlines';
        
        $result = $wpdb->insert(
            $table,
            array(
                'name' => sanitize_text_field($name),
                'iata_code' => strtoupper(sanitize_text_field($iata_code)),
                'domain' => sanitize_text_field($domain)
            ),
            array('%s', '%s', '%s')
        );
        
        return $result ? $wpdb->insert_id : false;
    }
    
    public static function update_airline($id, $name, $iata_code, $domain) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_airlines';
        
        return $wpdb->update(
            $table,
            array(
                'name' => sanitize_text_field($name),
                'iata_code' => strtoupper(sanitize_text_field($iata_code)),
                'domain' => sanitize_text_field($domain)
            ),
            array('id' => $id),
            array('%s', '%s', '%s'),
            array('%d')
        );
    }
    
    public static function delete_airline($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_airlines';
        
        return $wpdb->delete($table, array('id' => $id), array('%d'));
    }
    
    // Airports functions
    public static function get_airports($search = '', $limit = 50) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_airports';
        
        if ($search) {
            $search = '%' . $wpdb->esc_like($search) . '%';
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table WHERE name LIKE %s OR iata_code LIKE %s OR city LIKE %s ORDER BY name LIMIT %d",
                $search, $search, $search, $limit
            ));
        }
        
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM $table ORDER BY name LIMIT %d", $limit));
    }
    
    public static function get_airport_by_id($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_airports';
        
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }
    
    // User profiles functions
    public static function get_user_profile($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_user_profiles';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT p.*, u.display_name, u.user_email, u.user_registered, a.name as airline_name, a.iata_code, a.domain 
             FROM $table p 
             JOIN {$wpdb->users} u ON p.user_id = u.ID
             LEFT JOIN {$wpdb->prefix}pfl_airlines a ON p.airline_id = a.id 
             WHERE p.user_id = %d",
            $user_id
        ));
    }
    
    public static function create_user_profile($user_id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_user_profiles';
        
        $profile_data = array(
            'user_id' => $user_id,
            'airline_id' => isset($data['airline_id']) ? intval($data['airline_id']) : null,
            'status' => isset($data['status']) ? sanitize_text_field($data['status']) : 'active',
            'approval_status' => 'pending'
        );
        
        // Add retired user fields if status is retired
        $profile_data['airline_email'] = isset($data['airline_email']) ? sanitize_email($data['airline_email']) : '';
        $profile_data['airline_code'] = isset($data['airline_code']) ? sanitize_text_field($data['airline_code']) : '';
        $profile_data['phone_number'] = isset($data['phone_number']) ? sanitize_text_field($data['phone_number']) : '';
        $profile_data['employment_retirement_date'] = isset($data['employment_retirement_date']) ? sanitize_text_field($data['employment_retirement_date']) : null;
        $profile_data['airline_job'] = isset($data['airline_job']) ? sanitize_text_field($data['airline_job']) : '';
        $profile_data['years_worked'] = isset($data['years_worked']) ? intval($data['years_worked']) : null;
        $profile_data['upload_id_url'] = isset($data['upload_id_url']) ? esc_url_raw($data['upload_id_url']) : '';
        
        
        $result = $wpdb->insert($table, $profile_data);
        return $result ? $wpdb->insert_id : false;
    }
    
    public static function update_user_approval_status($user_id, $status) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_user_profiles';
        
        return $wpdb->update(
            $table,
            array('approval_status' => sanitize_text_field($status)),
            array('user_id' => $user_id),
            array('%s'),
            array('%d')
        );
    }
    
    public static function get_users_by_status($status = 'pending', $limit = 50) {
        global $wpdb;
        $profiles_table = $wpdb->prefix . 'pfl_user_profiles';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT p.*, u.display_name, u.user_email, u.user_registered, a.name as airline_name, a.iata_code
             FROM $profiles_table p
             JOIN {$wpdb->users} u ON p.user_id = u.ID
             LEFT JOIN {$wpdb->prefix}pfl_airlines a ON p.airline_id = a.id
             WHERE p.approval_status = %s
             ORDER BY p.created_at DESC
             LIMIT %d",
            $status, $limit
        ));
    }

    public static function update_flight_request($data, $request_id, $current_user){
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_flight_requests';

        // Security check: make sure user owns the request
        $owner = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM $table WHERE id = %d", $request_id
        ));

        if ($owner != $current_user) {
            wp_send_json_error("You don’t have permission to edit this request.");
        }

        $updated = $wpdb->update(
            $table,
            $data,
            ['id' => $request_id],
            ['%s','%s', '%s', '%s', '%s', '%s'],
            ['%d']
        );

        if ($updated !== false) {
            wp_send_json_success("Request updated successfully!");
        } else {
            wp_send_json_error("Failed to update request.");
        }
    }

    
    // Flight requests functions
    public static function create_flight_request($user_id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_flight_requests';
        $airline_users = $wpdb->prefix . 'pfl_user_profiles';

        $is_return = !empty($data['is_return']) ? 1 : 0;
        
        $request_data = array(
            'user_id' => $user_id,
            'airline_id' => $data['airline_id'],
            'airline_code' => $data['airline_code'],
            'aircraft' => $data['aircraft'],
            'flight_number' => sanitize_text_field($data['flight_number']),
            'from_airport_id' => $data['from_airport_id'],
            'to_airport_id' => $data['to_airport_id'],
            'travel_date' => sanitize_text_field($data['travel_date']),
            'is_return' => $is_return,
            'notes' => !empty($data['notes']) ? sanitize_textarea_field($data['notes']) : ''
        );
        
        // Add return flight data if applicable
        if ($is_return) {
            $request_data['return_airline_code'] = $data['return_airline_code'];
            $request_data['return_flight_number'] = sanitize_text_field($data['return_flight_number']);
            $request_data['return_from_airport_id'] = $data['return_from_airport_id'];
            $request_data['return_to_airport_id'] = $data['return_to_airport_id'];
            $request_data['return_travel_date'] = sanitize_text_field($data['return_travel_date']);
        }
        
        $result = $wpdb->insert($table, $request_data);

        if ($result) {
            $actor_id = $user_id;

            // Get all owners of this airline
            $owners = $wpdb->get_col($wpdb->prepare(
                "SELECT user_id FROM $airline_users WHERE airline_code = %s",
                $data['airline_code']
            ));

            $actor_name = self::get_user_profile($user_id);

            if (!empty($owners)) {
                foreach ($owners as $owner) {
                    if (intval($actor_id) !== $owner_id) {
                        self::add_notification(
                            $owner,
                            $actor_id,
                            $wpdb->insert_id,
                            'request',
                            $actor_name->display_name . ' submitted a new flight request.'
                        );
                    }
                }
            }

            return $wpdb->insert_id;
        }

        return false;
    }
    
    public static function get_flight_requests_for_airline($airline_id, $status='', $limit = 50) {
        global $wpdb;
        $requests_table = $wpdb->prefix . 'pfl_flight_requests';

        $sql = "SELECT r.*, u.display_name as requester_name,
                       r.airline_id, r.airline_code, r.from_airport_id,
                       r.to_airport_id, r.status
                FROM $requests_table r
                JOIN {$wpdb->users} u ON r.user_id = u.ID
                WHERE r.airline_id = %d";

        $params = [ $airline_id ];

        // Only filter by status if a valid enum is passed
        $valid_statuses = [ 'pending', 'answered', 'expired' ];
        if ( in_array($status, $valid_statuses, true) ) {
            $sql .= " AND r.status = %s";
            $params[] = $status;
        }

        $sql .= " ORDER BY r.created_at DESC LIMIT %d";
        $params[] = (int) $limit;

        return $wpdb->get_results( $wpdb->prepare($sql, $params) );
    }

    public static function get_user_flight_requests($user_id, $status = '', $limit = 50) {
        global $wpdb;
        $requests_table = $wpdb->prefix . 'pfl_flight_requests';

        $sql = "SELECT r.*, u.display_name as requester_name,
                       r.airline_id, r.airline_code, r.from_airport_id,
                       r.to_airport_id, r.status
                FROM $requests_table r
                JOIN {$wpdb->users} u ON r.user_id = u.ID
                WHERE r.user_id = %d";

        $params = [ $user_id ];

        // Only filter by status if a valid enum is passed
        $valid_statuses = [ 'pending', 'answered', 'expired' ];
        if ( in_array($status, $valid_statuses, true) ) {
            $sql .= " AND r.status = %s";
            $params[] = $status;
        }

        $sql .= " ORDER BY r.created_at DESC LIMIT %d";
        $params[] = (int) $limit;

        return $wpdb->get_results( $wpdb->prepare($sql, $params) );
    }
    
    public static function check_duplicate_request($user_id, $airline_id, $flight_number, $travel_date) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_flight_requests';
        
        $one_hour_ago = date('Y-m-d H:i:s', strtotime('-1 hour'));
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table 
             WHERE airline_id = %d AND flight_number = %s AND travel_date = %s 
             AND created_at > %s AND status = 'answered'",
            $airline_id, $flight_number, $travel_date, $one_hour_ago
        ));
    }

    public static function get_user_profile_by_flight_request($id){
        global $wpdb;

        $profiles_table = $wpdb->prefix . 'pfl_user_profiles';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT p.*, u.display_name, u.user_email, u.user_registered, a.name as airline_name, a.iata_code
             FROM $profiles_table p
             JOIN {$wpdb->users} u ON p.user_id = u.ID
             LEFT JOIN {$wpdb->prefix}pfl_airlines a ON p.airline_id = a.id
             WHERE p.user_id = %d
             ORDER BY p.created_at DESC", $id
        ));

    }
    
    public static function get_all_flight_requests() {
        global $wpdb;
        $requests_table = $wpdb->prefix . 'pfl_flight_requests';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT r.*, u.display_name as requester_name, u.user_email,
                    airline_id,from_airport_id, to_airport_id
             FROM $requests_table r
             JOIN {$wpdb->users} u ON r.user_id = u.ID
             ORDER BY r.created_at DESC"
        ));
    }

    public static function get_loads($id){
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_flight_responses';

        return $wpdb->get_results($wpdb->prepare(
           "SELECT r.*, u.display_name as giver_name, u.user_email
             FROM $table r
             JOIN {$wpdb->users} u ON r.user_id = u.ID
             WHERE request_id = %d", $id
        ));

    }
    
    public static function delete_flight_request($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_flight_requests';
        
        return $wpdb->delete($table, array('id' => $id), array('%d'));
    }

    public static function get_flight_request_by_id($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_flight_requests';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT *
             FROM $table 
             WHERE id = %d",
            $id
        ));
    }

    
    // Flight responses functions
    public static function add_flight_response($user_id, $request_id, $response_text) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_flight_responses';
        
        $result = $wpdb->insert(
            $table,
            array(
                'user_id' => $user_id,
                'request_id' => $request_id,
                'response_text' => sanitize_textarea_field($response_text)
            ),
            array('%d', '%d', '%s')
        );
        
        return $result ? $wpdb->insert_id : false;
    }

    public static function get_flight_responses($request_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_flight_responses';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT r.*, u.display_name as author_name, u.user_email,
                    p.airline_id, a.name as airline_name, a.iata_code
             FROM $table r
             JOIN {$wpdb->users} u ON r.user_id = u.ID
             LEFT JOIN {$wpdb->prefix}pfl_user_profiles p ON r.user_id = p.user_id
             LEFT JOIN {$wpdb->prefix}pfl_airlines a ON p.airline_id = a.id
             WHERE r.request_id = %d
             ORDER BY r.created_at DESC",
            $request_id
        ));
    }

    public static function get_response_by_id($response_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_flight_responses';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT r.*, u.display_name as author_name
             FROM $table r
             JOIN {$wpdb->users} u ON r.user_id = u.ID
             WHERE r.id = %d",
            $response_id
        ));
    }

    public static function get_request_response_count($request_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_flight_responses';
        
        return intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE request_id = %d",
            $request_id
        )));
    }
    
    // Response likes functions
    public static function toggle_response_like($user_id, $response_id) {
        global $wpdb;
        $likes_table = $wpdb->prefix . 'pfl_response_likes';
        $responses_table = $wpdb->prefix . 'pfl_flight_responses';
        $request_table = $wpdb->prefix . 'pfl_flight_requests';
        
        // Check if already liked
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $likes_table WHERE user_id = %d AND response_id = %d",
            $user_id, $response_id
        ));
        
        if ($existing) {
            // Unlike
            $wpdb->delete($likes_table, array('user_id' => $user_id, 'response_id' => $response_id), array('%d', '%d'));
            // Decrease likes count
            $wpdb->query($wpdb->prepare(
                "UPDATE $responses_table SET likes_count = likes_count - 1 WHERE id = %d",
                $response_id
            ));
            return false;
        } else {
            // Like
            $wpdb->insert($likes_table, array('user_id' => $user_id, 'response_id' => $response_id), array('%d', '%d'));

            // Increase likes count
            $wpdb->query($wpdb->prepare(
                "UPDATE $responses_table SET likes_count = likes_count + 1 WHERE id = %d",
                $response_id
            ));

            // 🔔 Add notification
            $actor_id = $user_id;

            // Get the owner of the response
            $owner_id = $wpdb->get_var($wpdb->prepare(
                "SELECT user_id FROM $responses_table WHERE id = %d",
                $response_id
            ));

            if ($owner_id && $owner_id != $actor_id) {
                self::add_notification(
                    $owner_id,
                    $actor_id,
                    1,
                    'like',
                    'liked your flight response'
                );
            }

            return true;
        }
    }

    public static function is_response_liked($user_id, $response_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_response_likes';
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d AND response_id = %d",
            $user_id, $response_id
        )) > 0;
    }

    public static function get_response_like_count($response_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_flight_responses';
        
        return intval($wpdb->get_var($wpdb->prepare(
            "SELECT likes_count FROM $table WHERE id = %d",
            $response_id
        )));
    }

    // Flight request likes functions (keeping these for request likes)
    public static function toggle_request_like($user_id, $request_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_request_likes';
        $request_table = $wpdb->prefix . 'pfl_flight_requests';

        
        // Check if already liked
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE user_id = %d AND request_id = %d",
            $user_id, $request_id
        ));
        
        if ($existing) {
            // Unlike
            $wpdb->delete($table, array('user_id' => $user_id, 'request_id' => $request_id), array('%d', '%d'));
            return false;
        } else {
            // Like
            $wpdb->insert($table, array('user_id' => $user_id, 'request_id' => $request_id), array('%d', '%d'));

            // 🔔 Add notification
            $actor_id = $user_id;

            $actor_name = self::get_user_profile($user_id);

            // Get the owner of the response
            $owner_id = $wpdb->get_var($wpdb->prepare(
                "SELECT user_id FROM $request_table WHERE id = %d",
                $request_id
            ));

            if ($owner_id) {
                self::add_notification(
                    $owner_id,
                    $actor_id,
                    $request_id,
                    'like',
                    $actor_name->display_name . ' liked your flight request'
                );
            }

            return true;
        }
    }

    public static function is_request_liked($user_id, $request_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_request_likes';
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d AND request_id = %d",
            $user_id, $request_id
        )) > 0;
    }

    public static function get_request_like_count($request_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_request_likes';
        
        return intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE request_id = %d",
            $request_id
        )));
    }
    
    // Flight request comments functions
    public static function add_request_comment($user_id, $request_id, $comment_text) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_request_comments';
        $request_table = $wpdb->prefix . 'pfl_flight_requests';
        
        $result = $wpdb->insert(
            $table,
            array(
                'user_id' => $user_id,
                'request_id' => $request_id,
                'comment' => $comment_text
            ),
            array('%d', '%d', '%s')
        );

        // 🔔 Add notification
        $actor_id = $user_id;

        $actor_name = self::get_user_profile($user_id);

        // Get the owner of the response
        $owner_id = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM $request_table WHERE id = %d",
            $request_id
        ));

        if ($owner_id) {
            self::add_notification(
                $owner_id,
                $actor_id,
                $request_id,
                'comment',
                $actor_name->display_name . ' commented on your flight request'
            );
        }
        
        return $result ? $wpdb->insert_id : false;
    }

    public static function get_request_comments($request_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_request_comments';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT c.*, u.display_name as author_name
             FROM $table c
             JOIN {$wpdb->users} u ON c.user_id = u.ID
             WHERE c.request_id = %d
             ORDER BY c.created_at ASC",
            $request_id
        ));
    }

    public static function get_comment_by_id($comment_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_request_comments';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT c.*, u.display_name as author_name
             FROM $table c
             JOIN {$wpdb->users} u ON c.user_id = u.ID
             WHERE c.id = %d",
            $comment_id
        ));
    }

    public static function get_request_comment_count($request_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_request_comments';
        
        return intval($wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE request_id = %d",
            $request_id
        )));
    }
    
    // Statistics functions
    public static function get_total_users() {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_user_profiles';
        
        return $wpdb->get_var("SELECT COUNT(*) FROM $table");
    }
    
    public static function get_pending_users() {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_user_profiles';
        
        return $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE approval_status = 'pending'");
    }
    
    public static function get_active_requests() {
        global $wpdb;
        $table = $wpdb->prefix . 'pfl_flight_requests';
        
        return $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'pending'");
    }
    
    // Email verification functions
    public static function check_email_exists($email) {
        return email_exists($email) !== false;
    }
    
    public static function validate_airline_email($email, $airline_id) {
        $airline = self::get_airline_by_id($airline_id);
        if (!$airline) {
            return false;
        }
        
        $domain = substr(strrchr($email, "@"), 1);
        return $domain === $airline->domain;
    }

    // Flight load submission function
    public static function submit_flight_load($user_id, $request_id, $flight_load_data, $notes = '') {
        global $wpdb;
        $responses_table = $wpdb->prefix . 'pfl_flight_responses';
        $requests_table = $wpdb->prefix . 'pfl_flight_requests';
        
        // Format flight load data as JSON
        $flight_load_json = json_encode($flight_load_data);

        self::$loads = $flight_load_json;
        
        // Create response text from flight load data
        $response_text = "Flight Load Information:\n\n";
        foreach ($flight_load_data as $class_type => $data) {
            $class_name = str_replace('_', ' ', ucwords($class_type));
            $response_text .= "{$class_name}:\n";
            if (!empty($data['booked'])) {
                $response_text .= "  Booked: {$data['booked']}\n";
            }
            if ($data['cap'] > 0) {
                $response_text .= "  Cap: {$data['cap']}\n";
            }
            if ($data['held'] > 0) {
                $response_text .= "  Held: {$data['held']}\n";
            }
            if ($data['standbys'] > 0) {
                $response_text .= "  Standbys: {$data['standbys']}\n";
            }
            $response_text .= "\n";
        }
        
        if (!empty($notes)) {
            $response_text .= "Notes: {$notes}\n";
        }
        
        // Insert flight response
        $result = $wpdb->insert(
            $responses_table,
            array(
                'user_id' => $user_id,
                'request_id' => $request_id,
                'response_text' => $response_text,
                'flight_load_data' => $flight_load_json
            ),
            array('%d', '%d', '%s', '%s')
        );
        
        if ($result) {
            // Update request status to completed
            $wpdb->update(
                $requests_table,
                array('status' => 'answered'),
                array('id' => $request_id),
                array('%s'),
                array('%d')
            );

            // 🔔 Add notification
            $actor_id = $user_id;

            $actor_name = self::get_user_profile($user_id);

            // Get the owner of the response
            $owner_id = $wpdb->get_var($wpdb->prepare(
                "SELECT user_id FROM $requests_table WHERE id = %d",
                $request_id
            ));

            if ($owner_id) {
                self::add_notification(
                    $owner_id,
                    $actor_id,
                    $request_id,
                    'flightloads',
                    $actor_name->display_name . ' provided a flight load to your flight request'
                );
            }
            
            return $wpdb->insert_id;
        }
        
        return false;
    }
}
