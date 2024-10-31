<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function role_dash_add_settings_page() {
    add_options_page(
        'Notice Settings',
        'Notice Settings',
        'manage_options',
        'role_dash_notice_settings',
        'role_dash_render_settings_page'
    );
}
add_action('admin_menu', 'role_dash_add_settings_page');

function role_dash_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Notice Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('role_dash_notice_settings_group');
            do_settings_sections('role_dash_notice_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function role_dash_register_settings() {
    register_setting(
        'role_dash_notice_settings_group', 
        'role_dash_notice_access_roles', 
        'role_dash_save_notice_access_roles'
    );
    
    add_settings_section(
        'role_dash_notice_settings_section',
        'Access Control',
        null,
        'role_dash_notice_settings'
    );

    add_settings_field(
        'role_dash_notice_access_roles_field',
        'Who Can Send Notices',
        'role_dash_notice_access_roles_field_callback',
        'role_dash_notice_settings',
        'role_dash_notice_settings_section'
    );
}
add_action('admin_init', 'role_dash_register_settings');

function role_dash_notice_access_roles_field_callback() {
    $roles = wp_roles()->roles;
    $selected_roles = get_option('role_dash_notice_access_roles', []);

    // Remove the Administrator role from the roles array for the settings page
    if (isset($roles['administrator'])) {
        unset($roles['administrator']);
    }

    foreach ($roles as $role_slug => $role_details) {
        $checked = in_array($role_slug, $selected_roles) ? 'checked' : '';
        echo '<label><input type="checkbox" name="role_dash_notice_access_roles[]" value="' . esc_attr($role_slug) . '" ' . checked(in_array($role_slug, $selected_roles), true, false) . '> ' . esc_html($role_details['name']) . '</label><br>';
    }
}

// Ensure the Administrator role is always included when saving
function role_dash_save_notice_access_roles($input) {
    // Unslash the input first
    $input = wp_unslash($input);

    // Ensure $input is an array
    $input = is_array($input) ? $input : [];

    // Sanitize each role in the input array
    $input = array_map('sanitize_text_field', $input);

    // Always include Administrator in the saved roles
    if (!in_array('administrator', $input)) {
        $input[] = 'administrator';
    }

    return $input;
}

// Add this filter to ensure the Administrator role is always included
add_filter('pre_update_option_role_dash_notice_access_roles', 'role_dash_save_notice_access_roles');

function role_dash_user_has_notice_access() {
    $selected_roles = get_option('role_dash_notice_access_roles', []);

    // Always allow administrators
    if (current_user_can('administrator')) {
        return true;
    }

    // Check if the current user's role is in the selected roles
    $user = wp_get_current_user();
    foreach ($user->roles as $role) {
        if (in_array($role, $selected_roles)) {
            return true;
        }
    }

    return false;
}

function role_dash_update_capabilities_on_save($old_value, $value, $option) {
    if ($option === 'role_dash_notice_access_roles') {
        role_dash_register_post_type(); // Re-register the post type with the updated roles
    }
}
add_action('update_option_role_dash_notice_access_roles', 'role_dash_update_capabilities_on_save', 10, 3);
