<?php
/**
 * Email handling functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class PFL_Email_Handler {
    
    public static function send_application_notification($user_id) {
        $user = get_user_by('ID', $user_id);
        $profile = PFL_Database_Utils::get_user_profile($user_id);
        
        // Send email to admin
        self::send_admin_notification($user, $profile);
        
        // Send confirmation to applicant
        self::send_applicant_confirmation($user, $profile);
    }
    
    private static function send_admin_notification($user, $profile) {
        $admin_email = get_option('admin_email');
        $subject = 'New Passrider Flight Loads Application';
        
        $message = "A new user has applied for Passrider Flight Loads access:\n\n";
        $message .= "Name: {$user->display_name}\n";
        $message .= "Email: {$user->user_email}\n";
        $message .= "Phone: {$user->phone_number}\n";
        $message .= "Airline: {$profile->airline_name} ({$profile->domain})\n";
        $message .= "Airline Job: {$profile->airline_job}\n";
        $message .= "Status: {$profile->status}\n";
        $message .= "Registration Date: {$user->user_registered}\n\n";
        $message .= "Please review and approve/deny this application in the admin dashboard:\n";
        $message .= admin_url('admin.php?page=pfl-users');
        
        wp_mail($admin_email, $subject, $message);
    }

    private static function send_author_notification($owner, $loads, $requests, $giver){
        $url = site_url('/flightloads/', 'https');
        $subject = 'Your Passrider Flight Loads Request Has Been Filled';

        $message = "Dear {$owner->display_name},\n\n";
        $message .= "Your flight load request has been filled with the following information:\n\n";
        $message .= "Flight Request: <strong>{$requests->airline_code}{$requests->flight_number} {$requests->from_airport_id} → {$requests->to_airport_id}</strong>\n";
        $message .= "From: {$giver->display_name}";
        $message .= "Load Details: \n\n";
        $message .= "{$loads->response_text}\n\n";
        $message .= "To check the full details, login to your account.\n";
        $message .= "{$url}\n\n";
        $message .= "Best regards,\nPassrider Flight Loads Team";

        wp_mail($owner->user_email, $subject, $message);
    }

    public static function send_flightload_notification_to_owner($owner, $user, $request, $loads){
        $flight_load_data = json_decode($loads, true);

        // Build HTML table
        $html = '<table cellpadding="8" cellspacing="0" border="1" style="border-collapse: collapse; width: 100%;">';
        $html .= '<tr style="background: #f3f3f3;"><th>Class</th><th>Booked</th><th>Cap</th><th>Held</th><th>Standbys</th></tr>';

        foreach ($flight_load_data as $class => $info) {
            $html .= '<tr>';
            $html .= '<td style="text-transform: capitalize;">' . htmlspecialchars(str_replace('_', ' ', $class)) . '</td>';
            $html .= '<td>' . htmlspecialchars($info['booked'] ?? '-') . '</td>';
            $html .= '<td>' . htmlspecialchars($info['cap'] ?? '-') . '</td>';
            $html .= '<td>' . htmlspecialchars($info['held'] ?? '-') . '</td>';
            $html .= '<td>' . htmlspecialchars($info['standbys'] ?? '-') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';

        $subject = 'Your request has been filled.';

        // Build message in HTML
        $message = "<p>Dear {$owner->display_name},</p>";
        $message .= "<p>One of your requests has been filled. See information below:</p>";
        $message .= "<p><strong>From:</strong> {$user->display_name}<br>";
        $message .= "<strong>Request:</strong> {$request->airline_code}{$request->flight_number}</p>";
        $message .= "<p><strong>Flight Loads:</strong><br>{$html}</p>";
        $message .= "<p>Best regards,<br>Passrider Flight Loads Team</p>";

        // Send as HTML
        add_filter('wp_mail_content_type', function() { return 'text/html'; });
        wp_mail($owner->user_email, $subject, $message);
        remove_filter('wp_mail_content_type', function() { return 'text/html'; });
    }


    public static function send_related_notification($recipient, $recipient_name, $sender, $sender_name, $info) {
        $url = site_url('/flightloads/', 'https');
        $subject = 'A Request has been added to your related airline';

        // Sanitize inputs
        $recipient      = sanitize_email($recipient);
        $recipient_name = sanitize_text_field($recipient_name);
        $sender         = sanitize_email($sender);
        $sender_name    = sanitize_text_field($sender_name);
        $info           = sanitize_text_field($info);

        // Build plain text message
        $message  = "Dear {$recipient_name},\n\n";
        $message .= "A Request has been added to your related airline.\n\n";
        $message .= "Here is the information of the request:\n\n";
        $message .= "Flight Request: <strong>{$info}</strong>\n";
        $message .= "From: {$sender_name} <{$sender}>\n\n";
        $message .= "To check the full details, go to your account:\n{$url}\n\n";
        $message .= "Best regards,\n";
        $message .= "Passrider Flight Loads Team";

        // Send email
        return wp_mail($recipient, $subject, $message);
    }

    
    private static function send_applicant_confirmation($user, $profile) {
        $subject = 'Your Passrider Flight Loads Application Received';
        
        $message = "Dear {$user->display_name},\n\n";
        $message .= "Thank you for applying to Passrider Flight Loads!\n\n";
        $message .= "Your application has been received and is currently under review. ";
        $message .= "You will receive an email notification once your application has been approved.\n\n";
        $message .= "Application Details:\n";
        $message .= "- Airline: {$profile->airline_name}\n";
        $message .= "- Status: {$profile->status}\n";
        $message .= "- Submitted: " . date('F j, Y g:i A') . "\n\n";
        $message .= "If you have any questions, please contact our support team.\n\n";
        $message .= "Best regards,\nPassrider Flight Loads Team";
        
        wp_mail($user->user_email, $subject, $message);
    }
    
    public static function send_approval_email($user_id) {
        $url = site_url('/flight-loads-login/', 'https');
        $user = get_user_by('ID', $user_id);
        $subject = 'Your Passrider Flight Loads Application Has Been Approved!';
        
        $message = "Dear {$user->display_name},\n\n";
        $message .= "Great news! Your application for Passrider Flight Loads has been approved.\n\n";
        $message .= "You can now access your account and start making flight load requests:\n";
        $message .= home_url('/flight-loads/account') . "\n\n";
        $message .= "Login Details:\n";
        $message .= "Username: {$user->user_login}\n";
        $message .= "Password: (Your chosen password)\n\n";
        $message .= "Login URL: {$url}\n\n";
        $message .= "Welcome to Passrider Flight Loads!\n\n";
        $message .= "Best regards,\nPassrider Flight Loads Team";
        
        wp_mail($user->user_email, $subject, $message);
    }
    
    public static function send_verification_code($email, $code, $airline) {
        $text = $airline ? 'airline' : 'flight loads';
        $subject = 'Your Passrider Flight Loads Verification Code';
        
        $message = "Your verification code is: {$code}\n\n";
        $message .= "Please enter this code to verify your {$text} email address.\n\n";
        $message .= "This code will expire in 10 minutes.\n\n";
        $message .= "If you didn't request this code, please ignore this email.";
        
        return wp_mail($email, $subject, $message);
    }
}
