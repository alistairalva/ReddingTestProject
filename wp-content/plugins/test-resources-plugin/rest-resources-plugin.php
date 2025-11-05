<?php
/**
 * Plugin Name: Test Resources Plugin
 * Description: Registers a resource custom post type and exposes /wp-json/test/v1/resources REST endpoint.
 * Version: 1.0
 * Author: Alistair Alvar
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
        register_activation_hook(__FILE__, array($this, 'on_activate')); // seed data on activation
        add_action('init', array($this, 'register_post_type_and_meta'));
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function on_activate() {
        // Create a sample resource post (seed)
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

}

new Test_Resources_Plugin();
