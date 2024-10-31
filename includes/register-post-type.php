<?php

// Register the custom post type for Notices
function role_dash_register_post_type() {
    $roles = wp_roles()->roles;
    $selected_roles = get_option('role_dash_notice_access_roles', ['administrator']); // Default to admin if not set

    $capabilities = [
        'edit_post' => 'edit_role_dash_notice',
        'read_post' => 'read_role_dash_notice',
        'delete_post' => 'delete_role_dash_notice',
        'edit_posts' => 'edit_role_dash_notices',
        'edit_others_posts' => 'edit_others_role_dash_notices',
        'publish_posts' => 'publish_role_dash_notices',
        'read_private_posts' => 'read_private_role_dash_notices',
    ];

    // Remove capabilities from all roles first
    foreach ($roles as $role_slug => $role_details) {
        $role = get_role($role_slug);
        foreach ($capabilities as $capability) {
            $role->remove_cap($capability);
        }
    }

    // Grant capabilities only to selected roles
    foreach ($selected_roles as $role_slug) {
        $role = get_role($role_slug);
        foreach ($capabilities as $capability) {
            $role->add_cap($capability);
        }
    }

    register_post_type('role_dash_notice', [
        'public' => true,
        'show_ui' => true,
        'supports' => ['title', 'editor'],
        'capability_type' => 'post',
        'capabilities' => $capabilities,
        'menu_icon' => 'dashicons-megaphone',
        'labels' => [
            'name' => 'Notices',
            'singular_name' => 'Notice',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Notice',
            'edit_item' => 'Edit Notice',
            'new_item' => 'New Notice',
            'view_item' => 'View Notice',
            'search_items' => 'Search Notices',
            'not_found' => 'No notices found',
            'not_found_in_trash' => 'No notices found in Trash',
            'all_items' => 'All Notices',
            'archives' => 'Notice Archives',
        ],
    ]);
}
add_action('init', 'role_dash_register_post_type');

// Shows the posts in the notice based on user
function role_dash_restrict_notice_access($query) {
    if (is_admin() && $query->is_main_query() && $query->get('post_type') === 'role_dash_notice') {
        $query->set('author', get_current_user_id());
    }
}
add_action('pre_get_posts', 'role_dash_restrict_notice_access');

// Use WP_Query and caching for user-based post count
function role_dash_adjust_notice_post_counts($views) {
    $post_type = 'role_dash_notice';
    $user_id = get_current_user_id();
    $cache_key = 'role_dash_notice_post_counts_' . $user_id;

    // Try to get cached counts
    $cached_counts = wp_cache_get($cache_key, 'role_dash_notices');
    if ($cached_counts === false) {
        // If cache does not exist, perform WP_Query to get counts
        $args = [
            'post_type' => $post_type,
            'author' => $user_id,
            'post_status' => 'publish',
            'fields' => 'ids',
            'posts_per_page' => -1
        ];
        $published_count = count(get_posts($args));
        $args['post_status'] = 'trash';
        $trash_count = count(get_posts($args));

        // Calculate total count excluding 'trash' and 'auto-draft' statuses
        $args['post_status'] = ['publish', 'future', 'draft', 'pending', 'private'];
        $total_count = count(get_posts($args));

        // Store the counts in cache for 10 minutes
        $cached_counts = [
            'total' => $total_count,
            'published' => $published_count,
            'trash' => $trash_count
        ];
        wp_cache_set($cache_key, $cached_counts, 'role_dash_notices', 600); // 600 seconds = 10 minutes
    }

    // Update the view links with the correct counts
    $views['all'] = preg_replace('/\(.+\)/', "({$cached_counts['total']})", $views['all']);
    if (isset($views['publish'])) {
        $views['publish'] = preg_replace('/\(.+\)/', "({$cached_counts['published']})", $views['publish']);
    }
    if (isset($views['trash'])) {
        $views['trash'] = preg_replace('/\(.+\)/', "({$cached_counts['trash']})", $views['trash']);
    }

    return $views;
}
add_filter('views_edit-role_dash_notice', 'role_dash_adjust_notice_post_counts');

// Function to display the admin notice
function role_dash_display_role_error_notice() {
    // Check if the transient is set
    if (get_transient('role_dash_roles_error')) {
        // Display the admin notice
        echo '<div class="notice notice-error is-dismissible"><p><strong>Error:</strong> Please select at least one role to send the notice to.</p></div>';
        
        // Delete the transient to prevent the notice from showing again
        delete_transient('role_dash_roles_error');
    }
}
add_action('admin_notices', 'role_dash_display_role_error_notice');
