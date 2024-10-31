<?php

// Save the priority and read status meta data
function role_dash_save_notice_meta($post_id) {
    // Verify nonce
    if ( ! isset( $_POST['role_dash_notice_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['role_dash_notice_nonce'] ) ), 'role_dash_save_notice' ) ) {
        return;
    }       

    // Check if the user has the permission to edit the post
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (array_key_exists('role_dash_notice_priority', $_POST)) {
        update_post_meta($post_id, '_role_dash_notice_priority', sanitize_text_field( wp_unslash( $_POST['role_dash_notice_priority'] ) ) );
    }
}
add_action('save_post', 'role_dash_save_notice_meta');

// Add meta box for notice priority
function role_dash_notice_priority_box_html($post) {
    $priority = get_post_meta($post->ID, '_role_dash_notice_priority', true);
    // Add nonce field for priority meta box
    wp_nonce_field('role_dash_save_notice', 'role_dash_notice_nonce');
    ?>
    <label for="role_dash_notice_priority">Priority:</label>
    <select name="role_dash_notice_priority" id="role_dash_notice_priority">
        <option value="low" <?php selected($priority, 'low'); ?>>Low</option>
        <option value="medium" <?php selected($priority, 'medium'); ?>>Medium</option>
        <option value="high" <?php selected($priority, 'high'); ?>>High</option>
    </select>
    <?php
}

// Register meta box for notice priority
function role_dash_add_meta_boxes() {
    add_meta_box(
        'role_dash_notice_priority_box',
        'Notice Priority',
        'role_dash_notice_priority_box_html',
        'role_dash_notice',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'role_dash_add_meta_boxes');

// Save post meta for user roles
function role_dash_save_post_meta($post_id) {
    // Early return for autosave or lacking permissions
    if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || !current_user_can('edit_post', $post_id)) {
        return;
    }

    // Verify nonce for user roles
    if ( ! isset( $_POST['role_dash_roles_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['role_dash_roles_nonce'] ) ), 'role_dash_save_roles' ) ) {
        return;
    }    

    // Prevent infinite loop by removing action temporarily
    remove_action('save_post', 'role_dash_save_post_meta', 10);

    // Validate role selection and save if valid
    if (isset($_POST['role_dash_notice_roles']) && !empty($_POST['role_dash_notice_roles'])) {
        $roles = array_map('sanitize_text_field', wp_unslash( $_POST['role_dash_notice_roles'] ) );
        update_post_meta($post_id, '_role_dash_notice_roles', $roles);
    } else {
        set_transient('role_dash_roles_error', true, 45);

        add_filter('redirect_post_location', function($location) {
            return remove_query_arg('message', $location);
        }, 99);

        wp_update_post(array(
            'ID'          => $post_id,
            'post_status' => 'draft',
        ));

        return;
    }

    // Re-add the save_post action
    add_action('save_post', 'role_dash_save_post_meta', 10);
}
add_action('save_post', 'role_dash_save_post_meta');

// Meta box callback for selecting user roles
function role_dash_notice_roles_meta_box_callback($post) {
    $selected_roles = get_post_meta($post->ID, '_role_dash_notice_roles', true);
    if (!is_array($selected_roles)) {
        $selected_roles = [];
    }

    $editable_roles = get_editable_roles();

    echo '<label>Select User Roles:</label><br>';
    
    // Add nonce field for roles meta box
    wp_nonce_field('role_dash_save_roles', 'role_dash_roles_nonce');

    foreach ($editable_roles as $role => $details) {
        $checked = in_array($role, $selected_roles) ? 'checked="checked"' : '';
        echo '<input type="checkbox" name="role_dash_notice_roles[]" value="' . esc_attr($role) . '" ' . (in_array($role, $selected_roles) ? 'checked' : '') . '> ' . esc_html($details['name']) . '<br>';
    }
}

// Register meta box for user roles
function role_dash_register_meta_boxes() {
    add_meta_box(
        'role_dash_notice_roles',
        'Send Notice to',
        'role_dash_notice_roles_meta_box_callback',
        'role_dash_notice',
        'side'
    );
}
add_action('add_meta_boxes', 'role_dash_register_meta_boxes');

?>
