<?php
/**
 * Plugin Name: Test Resources Plugin
 * Description: Registers a resource custom post type and exposes /wp-json/test/v1/resources REST endpoint.
 * Version: 1.0
 * Author: Alistair Alva
 * License: GPL2
 */

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

class Test_Resources_Plugin {
    const AUTH_TOKEN = 'dev-secret-token-REDDING-TEST-ALISTAIR-ALVA'; // simulation token for authenticated requests
    const META_KEY_LEVEL = 'trp_level'; // enum: beginner, intermediate, advanced
    const META_KEY_SUMMARY = 'trp_summary'; // text
    private $level_order = array('beginner' => 1, 'intermediate' => 2, 'advanced' => 3); // for filtering

    public function __construct() {
        register_activation_hook(__FILE__, array($this, 'on_activate'));
        add_action('init', array($this, 'register_post_type'));
        add_action('rest_api_init', array($this, 'register_routes'));
        add_action('acf/init', array($this, 'register_acf_fields')); // use ACF API
        // add CORS headers for testing from local frontend
        add_filter('rest_pre_serve_request', array($this, 'rest_add_cors_headers'), 10, 4);
    }

    public function on_activate() {

        $this->register_post_type();
        // Create a sample resource post if it doesn't exist
        if (get_page_by_title('Sample Resource', OBJECT, 'resource')) {
            return;
        }

        $post_id = wp_insert_post(array(
            'post_title'   => 'Sample Resource',
            'post_status'  => 'publish',
            'post_type'    => 'resource',
            'post_content' => '',
        ));

        if (! is_wp_error($post_id)) {
            // use ACF API to set fields
            update_field('summary', 'This is a sample summary stored in ACF. It will be hidden for unauthenticated users.', $post_id);
            update_field('level', 'beginner', $post_id);

        }
    }

    public function register_post_type() {
        // Register CPT
        register_post_type('resource', array(
            'labels' => array(
                'name' => 'Resources',
                'singular_name' => 'Resource',
            ),
            'public' => true,
            'show_in_rest' => true,
            'has_archive' => false,
            'supports' => array('title'),
            'capability_type' => 'post',
        ));
    }

    // Register ACF fields programmatically
    public function register_acf_fields() {
        if (! function_exists('acf_add_local_field_group')) {
            return;
        }

        acf_add_local_field_group(array(
            'key' => 'group_resource_fields',
            'title' => 'Resource Fields',
            'fields' => array(
                array(
                    'key' => 'field_summary',
                    'label' => 'Summary',
                    'name' => 'summary',
                    'type' => 'textarea',
                    'required' => 1,
                ),
                array(
                    'key' => 'field_level',
                    'label' => 'Level',
                    'name' => 'level',
                    'type' => 'select',
                    'choices' => array(
                        'beginner' => 'Beginner',
                        'intermediate' => 'Intermediate',
                        'advanced' => 'Advanced',
                    ),
                    'default_value' => 'beginner',
                    'ui' => 1,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'resource',
                    ),
                ),
            ),
        ));
    }
    public function register_routes() {
        register_rest_route('test/v1', '/resources', array(
            'methods' => \WP_REST_Server::READABLE, // GET
            'callback' => array($this, 'handle_get_resources'),
            'permission_callback' => '__return_true',
        ));
    }

    public function rest_add_cors_headers($served, $result, $request, $server) {
        // Allow local dev origins
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS'); //just in case
        header('Access-Control-Allow-Headers: Authorization, Content-Type');
        return $served;
    }
    private function is_request_authenticated(\WP_REST_Request $request) : bool {
        // Simulation: Accept Authorization header: "Bearer <token>"
        $auth = $request->get_header('authorization') ?: $request->get_header('Authorization') ?: $request->get_header('x-auth-token');
        
        // Fallbacks: PHP may expose it via $_SERVER or apache_request_headers
        if (empty($auth)) {
            if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
                $auth = $_SERVER['HTTP_AUTHORIZATION'];
            } elseif (function_exists('apache_request_headers')) {
                $headers = apache_request_headers();
                foreach ($headers as $k => $v) {
                    if (strtolower($k) === 'authorization') {
                        $auth = $v;
                        break;
                    }
                }
            }
        }

        if (empty($auth)) {
            return false;
        }

        $auth = trim($auth);
        if (stripos($auth, 'bearer ') === 0) {
            $token = substr($auth, 7);
        } else {
            // No Bearer prefix
            $token = $auth;
        }

        // Compare to the constant token above
        return is_string($token) && hash_equals(self::AUTH_TOKEN, $token);
    }

    public function handle_get_resources(\WP_REST_Request $request) {
        $min_level = $request->get_param('min_level');
        if (!in_array($min_level, array('beginner','intermediate','advanced'))) {
            $min_level = 'beginner';
        }
        $min_value = $this->level_order[$min_level];

        // Basic query for resource posts (small dataset assumed). We'll filter by meta in PHP for clarity.
        $query = new WP_Query(array(
            'post_type' => 'resource',
            'post_status' => 'publish',
            'posts_per_page' => 100,
        ));

        $items = array();
        $authenticated = $this->is_request_authenticated($request);

        while ($query->have_posts()) {
            $query->the_post();
            $pid = get_the_ID();
            $title = get_the_title($pid);
            //use ACF instead of meta
            $summary = get_field('summary', $pid);
            $level = get_field('level', $pid) ?: 'beginner';

            if (!$level) $level = 'beginner';
            $level_value = isset($this->level_order[$level]) ? $this->level_order[$level] : 1;

            if ($level_value < $min_value) {
                continue; // skip lower-level posts
            }

            // Compute reading_estimate: formula: minutes = ceil(word_count / 200)
            $word_count = str_word_count(trim($summary));
            $reading_estimate = (int) ceil($word_count / 200.0);

            $items[] = array(
                'id' => (int)$pid,
                'title' => $title,
                // summary is null for unauthenticated requests
                'summary' => $authenticated ? $summary : null,
                'level' => $level,
                'reading_estimate' => $reading_estimate,
            );
        }
        wp_reset_postdata();

        return rest_ensure_response(array(
            'success' => true,
            'min_level' => $min_level,
            'authenticated' => $authenticated,
            'items' => $items,
        ));
    }

}

new Test_Resources_Plugin();
