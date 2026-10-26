<?php
/**
 * Plugin Name:       Admin Cleanup
 * Description:       Declutter the WordPress admin for clients and editors: hide the WP logo, comments menu, welcome panel and noisy dashboard widgets, set a custom footer credit, and optionally hide the admin bar for low-privilege roles.
 * Version:           1.0.0
 * Author:            Azan Umer
 * License:           MIT
 * Text Domain:       wac
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WAC_OPTION', 'wac_settings' );

/** Default settings. Filterable via `wac_default_settings`. */
function wac_defaults() {
    return apply_filters( 'wac_default_settings', array(
        'hide_wp_logo'      => 1,
        'hide_comments'     => 0, // Only hides the menu; comments still work.
        'hide_welcome'      => 1,
        'hide_quick_draft'  => 1,
        'hide_activity'     => 0,
        'hide_admin_bar'    => 0, // Hide the front-end admin bar for non-admins.
        'footer_text'       => '',
    ) );
}

function wac_settings() {
    return wp_parse_args( get_option( WAC_OPTION, array() ), wac_defaults() );
}

/** Remove selected items from the admin bar (WP logo, etc.). */
function wac_clean_admin_bar( $wp_admin_bar ) {
    $s = wac_settings();
    if ( ! empty( $s['hide_wp_logo'] ) ) {
        $wp_admin_bar->remove_node( 'wp-logo' );
    }
}
add_action( 'admin_bar_menu', 'wac_clean_admin_bar', 999 );

/** Remove the Comments menu item (does not disable commenting). */
function wac_clean_admin_menu() {
    $s = wac_settings();
    if ( ! empty( $s['hide_comments'] ) ) {
        remove_menu_page( 'edit-comments.php' );
    }
}
add_action( 'admin_menu', 'wac_clean_admin_menu', 999 );

/** Remove noisy dashboard widgets. Runs late so other plugins' widgets can still be removed. */
function wac_clean_dashboard() {
    $s = wac_settings();
    if ( ! empty( $s['hide_welcome'] ) ) {
        remove_action( 'welcome_panel', 'wp_welcome_panel' );
    }
    if ( ! empty( $s['hide_quick_draft'] ) ) {
        remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
    }
    if ( ! empty( $s['hide_activity'] ) ) {
        remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
    }
}
add_action( 'wp_dashboard_setup', 'wac_clean_dashboard', 999 );

/** Custom footer credit, shown only when set (empty = WordPress default). */
function wac_footer_text( $text ) {
    $s = wac_settings();
    if ( '' !== trim( $s['footer_text'] ) ) {
        return esc_html( $s['footer_text'] );
    }
    return $text;
}
add_filter( 'admin_footer_text', 'wac_footer_text' );

/** Hide the front-end admin bar for users who cannot manage options. */
function wac_maybe_hide_admin_bar() {
    $s = wac_settings();
    if ( ! empty( $s['hide_admin_bar'] ) && ! current_user_can( 'manage_options' ) ) {
        show_admin_bar( false );
    }
}
add_action( 'after_setup_theme', 'wac_maybe_hide_admin_bar' );

/** Settings page under Settings -> Admin Cleanup. */
function wac_register_settings() {
    register_setting( 'wac_settings_group', WAC_OPTION, array(
        'sanitize_callback' => function ( $input ) {
            $checkboxes = array( 'hide_wp_logo', 'hide_comments', 'hide_welcome', 'hide_quick_draft', 'hide_activity', 'hide_admin_bar' );
            $out = array();
            foreach ( $checkboxes as $key ) {
                $out[ $key ] = ! empty( $input[ $key ] ) ? 1 : 0;
            }
            $out['footer_text'] = isset( $input['footer_text'] ) ? sanitize_text_field( wp_unslash( $input['footer_text'] ) ) : '';
            return $out;
        },
    ) );
    add_options_page( __( 'Admin Cleanup', 'wac' ), __( 'Admin Cleanup', 'wac' ), 'manage_options', 'wac-settings', 'wac_settings_page' );
}
add_action( 'admin_menu', 'wac_register_settings' );

function wac_settings_page() {
    $s = wac_settings();
    $fields = array(
        'hide_wp_logo'     => __( 'Hide the WordPress logo from the admin bar', 'wac' ),
        'hide_comments'    => __( 'Hide the Comments menu (commenting still works)', 'wac' ),
        'hide_welcome'     => __( 'Hide the dashboard welcome panel', 'wac' ),
        'hide_quick_draft' => __( 'Hide the Quick Draft dashboard widget', 'wac' ),
        'hide_activity'    => __( 'Hide the Activity dashboard widget', 'wac' ),
        'hide_admin_bar'   => __( 'Hide the front-end admin bar for non-admins', 'wac' ),
    );
    ?>
    <div class="wrap"><h1><?php esc_html_e( 'Admin Cleanup', 'wac' ); ?></h1>
    <form method="post" action="options.php">
        <?php settings_fields( 'wac_settings_group' ); ?>
        <table class="form-table">
            <?php foreach ( $fields as $key => $label ) : ?>
            <tr><th scope="row"><?php echo esc_html( $label ); ?></th>
                <td><input type="checkbox" name="<?php echo esc_attr( WAC_OPTION ); ?>[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( $s[ $key ] ); ?> /></td></tr>
            <?php endforeach; ?>
            <tr><th scope="row"><?php esc_html_e( 'Custom admin footer text', 'wac' ); ?></th>
                <td><input type="text" class="regular-text" name="<?php echo esc_attr( WAC_OPTION ); ?>[footer_text]" value="<?php echo esc_attr( $s['footer_text'] ); ?>" placeholder="<?php esc_attr_e( 'Leave empty for the WordPress default', 'wac' ); ?>" /></td></tr>
        </table>
        <?php submit_button(); ?>
    </form></div>
    <?php
}
