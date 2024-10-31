<?php
// Ensure WordPress environment is loaded
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Register the Archive page in the admin menu
function role_dash_register_archive_menu_page() {
    add_menu_page(
        'Archived Notices',     // Page title
        'Archive',              // Menu title
        'read',                 // Capability
        'rbn-archive',          // Menu slug
        'role_dash_display_archive_page', // Callback function to display content
        'dashicons-archive',    // Icon
        25                      // Position
    );
}
add_action('admin_menu', 'role_dash_register_archive_menu_page');

// Display the content of the Archive page
function role_dash_display_archive_page() {
    $user_id = get_current_user_id();
    $archived_notices = get_user_meta($user_id, 'archived_notices', true);

    // Ensure $archived_notices is an array
    if (!is_array($archived_notices)) {
        $archived_notices = [];
    }

    // Fetch only notices that have been archived by the current user
    $args = [
        'post_type' => 'role_dash_notice',
        'post__in'  => array_keys($archived_notices), // Fetch posts using the keys (post IDs) in the archived notices array
        'posts_per_page' => -1,
    ];
    
    $notices = new WP_Query($args);

    echo '<div class="wrap"><h1>' . esc_html__('Archived Notices', 'text-domain') . '</h1>';
    echo '<div class="activity-block table-view-list">';
    echo '<ul id="the-notice-list" class="dashboard-notice-list">';

    if ($notices->have_posts()) {
        while ($notices->have_posts()) {
            $notices->the_post();
            $post_id = get_the_ID();

            // Ensure this post ID is actually archived by the current user
            if (isset($archived_notices[$post_id])) {
                $user_id = get_post_field('post_author', $post_id);
                $sender_first_name = get_the_author_meta('first_name', $user_id);
                $sender_username = get_the_author_meta('user_login', $user_id);
                $sender = $sender_first_name ? $sender_first_name : $sender_username;
                $date = get_the_date();
                $time = get_the_time();
                $message = get_the_content();
                $priority = get_post_meta($post_id, '_role_dash_notice_priority', true);
                $priority_class = 'priority-' . ($priority ? $priority : 'low');

                // User avatar
                $user_avatar = get_avatar_url($user_id, ['size' => 50]);
                $default_avatar = plugins_url('assets/images/default-avatar.png', __FILE__);
                if (!$user_avatar) {
                    $user_avatar = $default_avatar;
                }

                echo '<li class="notice-item notice-archive">';
                echo '<div class="notice-avatar"><img src="' . esc_url($user_avatar) . '" alt="' . esc_attr__('Avatar', 'text-domain') . '" width="50" height="50"></div>';
                echo '<div class="notice-wrap has-row-actions has-avatar">';
                echo '<p class="notice-meta">' . esc_html__('From', 'text-domain') . ' <strong>' . esc_html($sender) . '</strong> ' . esc_html__('on', 'text-domain') . ' <span class="notice-date">' . esc_html($date) . ' ' . esc_html__('at', 'text-domain') . ' ' . esc_html($time) . '</span></p>';
                echo '<blockquote class="notice-message ' . esc_attr($priority_class) . '"><p class="notice-message-p">' . esc_html($message) . '</p>';
                echo '<p class="rbn-row-actions">';
                echo '<span class="rbn-archive"><a href="#" class="rbn-unarchive-link" data-post-id="' . esc_attr($post_id) . '">' . esc_html__('Unarchive', 'text-domain') . '</a></span>';
                echo '<span class="rbn-divider"> | </span>';
                echo '<span class="rbn-delete"><a href="#" class="rbn-delete-link" data-post-id="' . esc_attr($post_id) . '">' . esc_html__('Delete', 'text-domain') . '</a></span>';
                echo '</p>';
                echo '</blockquote>';
                echo '</div>';
                echo '</li>';
            }
        }
    } else {
        echo '<li><p>' . esc_html__('No archived notices available.', 'text-domain') . '</p></li>';
    }

    echo '</ul>';
    echo '</div>';
    echo '</div>';

    wp_reset_postdata();
}
