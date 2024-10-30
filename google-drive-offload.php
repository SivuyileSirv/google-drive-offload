<?php
/**
 * Plugin Name: Google Drive Offload
 * Description: Offload WordPress media files to Google Drive.
 * Version: 1.0
 * Author: Sivuyile Parkies
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Include Google API Client Library
require_once __DIR__ . '/vendor/autoload.php';

class GoogleDriveOffload {
    private $client;
    
    public function __construct() {
        $this->client = new Google_Client();
        $this->client->setClientId('YOUR_CLIENT_ID');
        $this->client->setClientSecret('YOUR_CLIENT_SECRET');
        $this->client->setRedirectUri(admin_url('admin.php?page=google-drive-offload'));
        $this->client->addScope(Google_Service_Drive::DRIVE);
        $this->client->setAccessType('offline');
        $this->client->setApprovalPrompt('force');

        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'handle_admin_actions']);
        add_action('google_drive_offload_cron', [$this, 'gd_offload_new_media_files']);

        // Register cron job activation and deactivation
        register_activation_hook(__FILE__, [$this, 'gd_cron_activation']);
        register_deactivation_hook(__FILE__, [$this, 'gd_cron_deactivation']);
    }

    // Add settings page to WordPress admin
    public function add_settings_page() {
        add_menu_page(
            'Google Drive Offload',
            'Google Drive Offload',
            'manage_options',
            'google-drive-offload',
            [$this, 'settings_page'],
            'dashicons-cloud',
            100
        );
    }

    // Settings page display
    public function settings_page() {
        $accessToken = get_option('google_drive_access_token');

        echo '<h1>Google Drive Offload Settings</h1>';
        
        if (!$accessToken) {
            echo '<p>You need to authenticate with Google Drive to offload media files.</p>';
            echo '<a href="' . esc_url(admin_url('admin.php?page=google-drive-offload&action=authenticate')) . '" class="button button-primary">Authenticate with Google</a>';
        } else {
            echo '<p>Google Drive is authenticated.</p>';
            echo '<a href="' . esc_url(admin_url('admin.php?page=google-drive-offload&action=reset_token')) . '" class="button button-secondary">Reset Google Authentication</a>';
            echo '<a href="' . esc_url(admin_url('admin.php?page=google-drive-offload&action=offload_existing')) . '" class="button button-primary">Offload Existing Media</a>';
        }
    }

    // Handle admin actions (Authenticate, Reset Token, Offload Existing)
    public function handle_admin_actions() {
        if (isset($_GET['page']) && $_GET['page'] == 'google-drive-offload') {
            if (isset($_GET['action'])) {
                if ($_GET['action'] == 'authenticate') {
                    $this->authenticate();
                } elseif ($_GET['action'] == 'reset_token') {
                    delete_option('google_drive_access_token');
                    wp_redirect(admin_url('admin.php?page=google-drive-offload'));
                    exit;
                } elseif ($_GET['action'] == 'offload_existing') {
                    $this->offload_existing_media();
                    wp_redirect(admin_url('admin.php?page=google-drive-offload&offload_complete=true'));
                    exit;
                }
            }
        }
    }

    // Authenticate with Google Drive
    public function authenticate() {
        if (isset($_GET['code'])) {
            $token = $this->client->fetchAccessTokenWithAuthCode($_GET['code']);
            update_option('google_drive_access_token', $token);
            wp_redirect(admin_url('admin.php?page=google-drive-offload'));
            exit;
        } else {
            $authUrl = $this->client->createAuthUrl();
            wp_redirect($authUrl);
            exit;
        }
    }

    // Offload all existing media files to Google Drive
    public function offload_existing_media() {
        $media_query = new WP_Query([
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'post_status' => 'inherit',
        ]);

        if ($media_query->have_posts()) {
            while ($media_query->have_posts()) {
                $media_query->the_post();
                $this->upload_to_google_drive(get_the_ID());
            }
            wp_reset_postdata();
            echo 'All media files have been offloaded to Google Drive.';
        }
    }

    // Upload media to Google Drive
    public function upload_to_google_drive($attachment_id) {
        $file_path = get_attached_file($attachment_id);
        $file_name = basename($file_path);

        // Get Google Drive service
        $accessToken = get_option('google_drive_access_token');
        $this->client->setAccessToken($accessToken);
        $drive_service = new Google_Service_Drive($this->client);

        $file_metadata = new Google_Service_Drive_DriveFile([
            'name' => $file_name
        ]);

        $content = file_get_contents($file_path);
        $drive_service->files->create($file_metadata, [
            'data' => $content,
            'mimeType' => mime_content_type($file_path),
            'uploadType' => 'multipart',
        ]);
    }

    // Cron Job for offloading new media files
    public function gd_offload_new_media_files() {
        global $wpdb;
        $last_offload_time = get_option('gd_last_offload_time', 0);

        // Get new media files uploaded since the last offload
        $query = "SELECT ID FROM $wpdb->posts WHERE post_type='attachment' AND post_date > %s";
        $results = $wpdb->get_results($wpdb->prepare($query, date('Y-m-d H:i:s', $last_offload_time)));

        if ($results) {
            foreach ($results as $attachment) {
                $this->upload_to_google_drive($attachment->ID);
            }
        }

        // Update the last offload time
        update_option('gd_last_offload_time', time());
    }

    // Activate cron job
    public function gd_cron_activation() {
        if (!wp_next_scheduled('google_drive_offload_cron')) {
            wp_schedule_event(time(), 'hourly', 'google_drive_offload_cron');
        }
    }

    // Deactivate cron job
    public function gd_cron_deactivation() {
        wp_clear_scheduled_hook('google_drive_offload_cron');
    }
}

// Initialize the plugin
new GoogleDriveOffload();
