<?php

// Add the dashboard widget
function role_dash_add_dashboard_widget() {
    wp_add_dashboard_widget('role_dash_dashboard_widget', 'Notices', 'role_dash_display_dashboard_widget');
}
add_action('wp_dashboard_setup', 'role_dash_add_dashboard_widget');

// Display the notices in the dashboard widget
function role_dash_display_dashboard_widget() {
    $current_user = wp_get_current_user();
    $user_roles = $current_user->roles;
    $user_id = $current_user->ID;

    // Get the list of archived notices for the current user
    $archived_notices = get_user_meta($user_id, 'archived_notices', true);
    if (!is_array($archived_notices)) {
        $archived_notices = [];
    }

    // Get the list of deleted notices for the current user
    $deleted_notices = get_user_meta($user_id, 'deleted_notices', true);
    if (!is_array($deleted_notices)) {
        $deleted_notices = [];
    }

    // Get the list of read notices for the current user
    $read_notices = get_user_meta($user_id, 'read_notices', true);
    if (!is_array($read_notices)) {
        $read_notices = [];
    }

    // Exclude both archived and deleted notices
    $args = [
        'post_type' => 'role_dash_notice',
        'post_status' => ['publish'],
        'posts_per_page' => 5,
        'post__not_in' => array_merge(array_keys($archived_notices), $deleted_notices), // Exclude archived and deleted notices
    ];

    $notices = new WP_Query($args);

    echo '<div class="activity-block table-view-list">';
    echo '<ul id="the-notice-list" class="dashboard-notice-list">';

    if ($notices->have_posts()) {
        while ($notices->have_posts()) {
            $notices->the_post();

            // Get the roles this notice is intended for
            $notice_roles = get_post_meta(get_the_ID(), '_role_dash_notice_roles', true);

            // Check if the current user's role matches the intended roles
            if (empty($notice_roles) || array_intersect($user_roles, $notice_roles)) {
                $user_id = get_post_field('post_author', get_the_ID());
                $sender_first_name = get_the_author_meta('first_name', $user_id);
                $sender_username = get_the_author_meta('user_login', $user_id);
                $sender = $sender_first_name ? $sender_first_name : $sender_username;
                $date = get_the_date();
                $time = get_the_time();
                $message = get_the_content();
                $priority = get_post_meta(get_the_ID(), '_role_dash_notice_priority', true);

                // Check if this notice is marked as read for the current user
                $is_read = in_array(get_the_ID(), $read_notices);

                // Determine priority class and read status
                $priority_class = 'priority-' . ($priority ? $priority : 'low');
                $read_class = $is_read ? ' read' : '';

                // Default image URL
                $default_avatar = plugins_url('assets/images/default-avatar.png', __FILE__);

                // User avatar
                $user_avatar = get_avatar_url($user_id, ['size' => 50]);
                if (!$user_avatar) {
                    $user_avatar = $default_avatar;
                }

                echo '<li class="notice-item">';
                echo '<div class="notice-avatar"><img src="' . esc_url($user_avatar) . '" alt="Avatar" width="50" height="50"></div>';
                echo '<div class="notice-wrap has-row-actions has-avatar">';
                echo '<p class="notice-meta">From <strong>' . esc_html($sender) . '</strong> on <span class="notice-date">' . esc_html($date) . ' at ' . esc_html($time) . '</span></p>';
                echo '<blockquote class="notice-message ' . esc_attr($priority_class) . esc_attr($read_class) . '"><p class="notice-message-p">' . esc_html($message) . '</p>';
                echo '<p class="rbn-row-actions">';
                echo '<span class="rbn-mark-as-read"><a href="#" class="rbn-mark-as-read-link" data-post-id="' . esc_attr(get_the_ID()) . '">' . ($is_read ? esc_html('Mark as Unread') : esc_html('Mark as Read')) . '</a></span>';
                echo '<span class="rbn-divider"> | </span>';
                echo '<span class="rbn-archive"><a href="#" class="rbn-archive-link" data-post-id="' . esc_attr(get_the_ID()) . '">Archive</a></span>';
                echo '<span class="rbn-divider"> | </span>';
                echo '<span class="rbn-delete"><a href="#" class="rbn-delete-link" data-post-id="' . esc_attr(get_the_ID()) . '">Delete</a></span>';
                echo '</p>';
                echo '</blockquote>';
                echo '</div>';
                echo '</li>';
            }
        }
    } else {
        echo '<li><p>No notices available.</p></li>';
    }

    echo '</ul>';
    echo '</div>';

    wp_reset_postdata();
}

