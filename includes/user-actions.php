<?php

// Handle AJAX request to mark notice as read
function role_dash_mark_as_read() {
    check_ajax_referer('role_dash_nonce', '_ajax_nonce');

    if (isset($_POST['post_id'])) {
        $post_id = intval(wp_unslash($_POST['post_id'])); // Unslash and sanitize
        $user_id = get_current_user_id(); // Get current user ID

        if (get_post_type($post_id) === 'role_dash_notice') {
            // Store the read status for this specific user
            $read_notices = get_user_meta($user_id, 'read_notices', true);

            if (!is_array($read_notices)) {
                $read_notices = [];
            }

            // Mark the notice as read for this user
            if (!in_array($post_id, $read_notices)) {
                $read_notices[] = $post_id;
                update_user_meta($user_id, 'read_notices', $read_notices);
            }

            wp_send_json_success();
        }
    }

    wp_send_json_error();
}
add_action('wp_ajax_role_dash_mark_as_read', 'role_dash_mark_as_read');


// Handle AJAX request to mark notice as unread
function role_dash_mark_as_unread() {
    check_ajax_referer('role_dash_nonce', '_ajax_nonce');

    if (isset($_POST['post_id'])) {
        $post_id = intval(wp_unslash($_POST['post_id'])); // Unslash and sanitize
        $user_id = get_current_user_id(); // Get current user ID

        if (get_post_type($post_id) === 'role_dash_notice') {
            // Get read notices for this user
            $read_notices = get_user_meta($user_id, 'read_notices', true);

            if (is_array($read_notices) && in_array($post_id, $read_notices)) {
                // Remove the notice from the read list
                $read_notices = array_diff($read_notices, [$post_id]);
                update_user_meta($user_id, 'read_notices', $read_notices);
            }

            wp_send_json_success();
        }
    }

    wp_send_json_error();
}
add_action('wp_ajax_role_dash_mark_as_unread', 'role_dash_mark_as_unread');



// Handle AJAX request to archive notice
function role_dash_archive_notice() {
    check_ajax_referer('role_dash_nonce', '_ajax_nonce');

    if (isset($_POST['post_id'])) {
        $post_id = intval(wp_unslash($_POST['post_id'])); // Unslash and sanitize
        $user_id = get_current_user_id();
        $archived_notices = get_user_meta($user_id, 'archived_notices', true);

        if (!is_array($archived_notices)) {
            $archived_notices = [];
        }

        // Use the post_id as the key
        if (!isset($archived_notices[$post_id])) {
            $archived_notices[$post_id] = true;  // Mark as archived
            update_user_meta($user_id, 'archived_notices', $archived_notices);
        }

        wp_send_json_success();
    }

    wp_send_json_error();
}
add_action('wp_ajax_role_dash_archive_notice', 'role_dash_archive_notice');


// Handle AJAX request to unarchive notice
function role_dash_unarchive_notice() {
    check_ajax_referer('role_dash_nonce', '_ajax_nonce');

    if (isset($_POST['post_id'])) {
        $post_id = intval(wp_unslash($_POST['post_id'])); // Unslash and sanitize
        $user_id = get_current_user_id();
        $archived_notices = get_user_meta($user_id, 'archived_notices', true);

        if (!is_array($archived_notices)) {
            $archived_notices = [];
        }

        // Remove from the array
        if (isset($archived_notices[$post_id])) {
            unset($archived_notices[$post_id]);
            update_user_meta($user_id, 'archived_notices', $archived_notices);
        }

        wp_send_json_success();
    }

    wp_send_json_error();
}
add_action('wp_ajax_role_dash_unarchive_notice', 'role_dash_unarchive_notice');



// Handle AJAX request to delete notice for the current user
function role_dash_delete_notice() {
    check_ajax_referer('role_dash_nonce', '_ajax_nonce');

    if (isset($_POST['post_id'])) {
        $post_id = intval(wp_unslash($_POST['post_id'])); // Unslash and sanitize
        $user_id = get_current_user_id();
        
        // Get the deleted and archived notices
        $deleted_notices = get_user_meta($user_id, 'deleted_notices', true);
        $archived_notices = get_user_meta($user_id, 'archived_notices', true);

        if (!is_array($deleted_notices)) {
            $deleted_notices = [];
        }

        if (!is_array($archived_notices)) {
            $archived_notices = [];
        }

        // Add the notice to the deleted list for this user
        if (!in_array($post_id, $deleted_notices)) {
            $deleted_notices[] = $post_id;
        }

        // Remove the notice from the archived list if it exists
        if (isset($archived_notices[$post_id])) {
            unset($archived_notices[$post_id]);
        }

        // Update the user meta with the new lists
        update_user_meta($user_id, 'deleted_notices', $deleted_notices);
        update_user_meta($user_id, 'archived_notices', $archived_notices);

        wp_send_json_success();
    }

    wp_send_json_error();
}
add_action('wp_ajax_role_dash_delete_notice', 'role_dash_delete_notice');

