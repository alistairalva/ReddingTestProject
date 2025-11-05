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
        add_action('init', array($this, 'register_post_type_and_meta'));
        add_action('rest_api_init', array($this, 'register_routes'));
        // add CORS headers for testing from local frontend
        add_filter('rest_pre_serve_request', array($this, 'rest_add_cors_headers'), 10, 4);
    }

    public function on_activate() {
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

        if (! is_wp_error($post_id) && $post_id) {
            update_post_meta($post_id, self::META_KEY_SUMMARY, 'This is a sample summary for the resource. It demonstrates how summaries are stored and used to compute reading estimates.');
            update_post_meta($post_id, self::META_KEY_LEVEL, 'beginner');
        }
    }

    public function register_post_type_and_meta() {
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

        // Meta: summary (text) and level (enum)
        register_post_meta('resource', self::META_KEY_SUMMARY, array(
            'single' => true,
            'type' => 'string',
            'show_in_rest' => false, // we'll control returned summary manually in the REST route
            'auth_callback' => function() { return current_user_can('edit_posts'); },
        ));

        register_post_meta('resource', self::META_KEY_LEVEL, array(
            'single' => true,
            'type' => 'string',
            'show_in_rest' => true,
            'sanitize_callback' => function($value) {
                $v = strtolower(trim((string)$value));
                if (! in_array($v, array('beginner','intermediate','advanced'))) {
                    return 'beginner';
                }
                return $v;
            },
            'auth_callback' => function() { return current_user_can('edit_posts'); },
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
        $auth = $request->get_header('authorization');
        
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
            $summary = get_post_meta($pid, self::META_KEY_SUMMARY, true);
            $level = get_post_meta($pid, self::META_KEY_LEVEL, true);
            if (! $level) $level = 'beginner';
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
