<?php
/**
 * Database management class
 */

if (!defined('ABSPATH')) {
    exit;
}

class PFL_Database {
    
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Airlines table
        $airlines_table = $wpdb->prefix . 'pfl_airlines';
        $sql_airlines = "CREATE TABLE $airlines_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            iata_code varchar(3) NOT NULL,
            domain varchar(255) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_iata (iata_code),
            UNIQUE KEY unique_domain (domain)
        ) $charset_collate;";
        
        // Airports table
        $airports_table = $wpdb->prefix . 'pfl_airports';
        $sql_airports = "CREATE TABLE $airports_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            iata_code varchar(3) NOT NULL,
            city varchar(255) NOT NULL,
            country varchar(255) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_iata (iata_code),
            KEY idx_name (name),
            KEY idx_city (city)
        ) $charset_collate;";
        
        // User profiles table (extends WordPress users)
        $user_profiles_table = $wpdb->prefix . 'pfl_user_profiles';
        $sql_user_profiles = "CREATE TABLE $user_profiles_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            airline_id int(11),
            airline_code char(3),
            airline_email varchar(50),
            status enum('active','retired') NOT NULL DEFAULT 'active',
            phone_number varchar(20),
            employment_retirement_date date,
            airline_job varchar(255),
            years_worked int(11),
            upload_id_url varchar(500),
            approval_status enum('pending','approved','denied') NOT NULL DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_user (user_id),
            KEY idx_airline (airline_id),
            KEY idx_status (approval_status),
            FOREIGN KEY (user_id) REFERENCES {$wpdb->users}(ID) ON DELETE CASCADE,
            FOREIGN KEY (airline_id) REFERENCES $airlines_table(id) ON DELETE SET NULL
        ) $charset_collate;";
        
        // Flight requests table
        $flight_requests_table = $wpdb->prefix . 'pfl_flight_requests';
        $sql_flight_requests = "CREATE TABLE $flight_requests_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            airline_id int(11) NOT NULL,
            airline_code chat(3) NOT NULL,
            flight_number varchar(10) NOT NULL,
            aircraft varchar(4) NOT NULL,
            from_airport_id char(3) NOT NULL,
            to_airport_id char(3) NOT NULL,
            travel_date date NOT NULL,
            is_return tinyint(1) DEFAULT 0,
            return_airline_id char(3),
            return_flight_number varchar(10),
            return_from_airport_id char(3),
            return_to_airport_id char(3),
            return_travel_date date,
            traveler_airline_id int(11) NOT NULL,
            notes text,
            status enum('pending','answered','expired') NOT NULL DEFAULT 'pending',
            created_at timestamp DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_user (user_id),
            KEY idx_airline (airline_id),
            KEY idx_travel_date (travel_date),
            KEY idx_status (status),
            FOREIGN KEY (user_id) REFERENCES {$wpdb->users}(ID) ON DELETE CASCADE,
            FOREIGN KEY (airline_id) REFERENCES $airlines_table(id) ON DELETE CASCADE,  
        ) $charset_collate;";
        
        // Flight responses table
        $flight_responses_table = $wpdb->prefix . 'pfl_flight_responses';
        $sql_flight_responses = "CREATE TABLE $flight_responses_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            request_id int(11) NOT NULL,
            user_id bigint(20) unsigned NOT NULL,
            response_text text NOT NULL,
            likes_count int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_request (request_id),
            KEY idx_user (user_id),
            FOREIGN KEY (request_id) REFERENCES $flight_requests_table(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES {$wpdb->users}(ID) ON DELETE CASCADE
        ) $charset_collate;";
        
        // Response likes table
        $response_likes_table = $wpdb->prefix . 'pfl_response_likes';
        $sql_response_likes = "CREATE TABLE $response_likes_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            response_id int(11) NOT NULL,
            user_id bigint(20) unsigned NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_like (response_id, user_id),
            FOREIGN KEY (response_id) REFERENCES $flight_responses_table(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES {$wpdb->users}(ID) ON DELETE CASCADE
        ) $charset_collate;";
        
        // Integrations table
        $integrations_table = $wpdb->prefix . 'pfl_integrations';
        $sql_integrations = "CREATE TABLE $integrations_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            api_url varchar(500),
            api_key varchar(255),
            status enum('connected','disconnected') NOT NULL DEFAULT 'disconnected',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_name (name)
        ) $charset_collate;";

        $request_likes_table = $wpdb->prefix . 'pfl_request_likes';
        $sql_request_likes = "CREATE TABLE $request_likes_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            request_id int(11) NOT NULL,
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_user_request_like (user_id, request_id),
            KEY idx_request_likes_request_id (request_id),
            KEY idx_request_likes_user_id (user_id),
            CONSTRAINT fk_request_likes_user FOREIGN KEY (user_id) REFERENCES {$wpdb->users}(ID) ON DELETE CASCADE,
            CONSTRAINT fk_request_likes_request FOREIGN KEY (request_id) REFERENCES $flight_requests_table(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $request_comments_table = $wpdb->prefix . 'pfl_request_comments';
        $sql_request_comments = "CREATE TABLE $request_comments_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            request_id int(11) NOT NULL,
            comment text NOT NULL,
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_request_comments_request_id (request_id),
            KEY idx_request_comments_user_id (user_id),
            KEY idx_request_comments_created_at (created_at),
            CONSTRAINT fk_request_comments_user FOREIGN KEY (user_id) REFERENCES {$wpdb->users}(ID) ON DELETE CASCADE,
            CONSTRAINT fk_request_comments_request FOREIGN KEY (request_id) REFERENCES $flight_requests_table(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";


        $notification_table = $wpdb->prefix . 'pfl_notifications';
        $sql_notification = "CREATE TABLE $notification_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            actor_id BIGINT(20) UNSIGNED NOT NULL,
            request_id INT(11) NOT NULL,
            type VARCHAR(50) NOT NULL,
            message TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            KEY user_id (user_id)
            CONSTRAINT fk_request_notification_user FOREIGN KEY (actor_id) REFERENCES {$wpdb->users}(ID) ON DELETE CASCADE,
            CONSTRAINT fk_request_notification_request FOREIGN KEY (request_id) REFERENCES $flight_requests_table(id) ON DELETE CASCADE
        ) $charset_collate;";

        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sql_airlines);
        dbDelta($sql_airports);
        dbDelta($sql_user_profiles);
        dbDelta($sql_flight_requests);
        dbDelta($sql_flight_responses);
        dbDelta($sql_response_likes);
        dbDelta($sql_integrations);
        dbDelta($sql_request_likes);
        dbDelta($sql_request_comments);
        dbDelta($sql_notification);
        
        // Insert default data
        self::insert_default_data();
    }
    
    private static function insert_default_data() {
        global $wpdb;
        
        // Insert some default airlines
        $airlines_table = $wpdb->prefix . 'pfl_airlines';
        $default_airlines = array(
            array('American Airlines', 'AA', 'aa.com'),
            array('Delta Air Lines', 'DL', 'delta.com'),
            array('United Airlines', 'UA', 'united.com'),
            array('Southwest Airlines', 'WN', 'southwest.com'),
            array('JetBlue Airways', 'B6', 'jetblue.com')
        );
        
        foreach ($default_airlines as $airline) {
            $wpdb->insert(
                $airlines_table,
                array(
                    'name' => $airline[0],
                    'iata_code' => $airline[1],
                    'domain' => $airline[2]
                )
            );
        }
        
        // Insert some default airports
        $airports_table = $wpdb->prefix . 'pfl_airports';
        $default_airports = array(
            array('John F. Kennedy International Airport', 'JFK', 'New York', 'United States'),
            array('Los Angeles International Airport', 'LAX', 'Los Angeles', 'United States'),
            array('Chicago O\'Hare International Airport', 'ORD', 'Chicago', 'United States'),
            array('Hartsfield-Jackson Atlanta International Airport', 'ATL', 'Atlanta', 'United States'),
            array('Dallas/Fort Worth International Airport', 'DFW', 'Dallas', 'United States')
        );
        
        foreach ($default_airports as $airport) {
            $wpdb->insert(
                $airports_table,
                array(
                    'name' => $airport[0],
                    'iata_code' => $airport[1],
                    'city' => $airport[2],
                    'country' => $airport[3]
                )
            );
        }
    }
}
