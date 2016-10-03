<?php
/**
 * Plugin Name: J-P
 * Description: A combinatorial optimization program to assign conference participants to workshops according to their preferences
 * Version: 1.0
 * Author: James Nolan
 * Author URI: https://www.otherwise.ch
 */
 

// Requires
require_once( 'functions.php' );

// Admin page
add_action('admin_menu', 'register_jp_custom_menu_page');
function register_jp_custom_menu_page () {
	add_menu_page( 'J. pédagogique', 'J. pédagogique', 'manage_options', 'journee-pedagogique', 'display_jp_accueil');
    add_submenu_page( 'journee-pedagogique', 'Accueil', 'Accueil', 'manage_options', 'journee-pedagogique');
    add_submenu_page( 'journee-pedagogique', 'Gestion des journées pédagogiques', 'Gestions de journées pédagogiques', 'manage_options', 'journee-pedagogique-create', 'create_new_jp');
    add_submenu_page( 'journee-pedagogique', 'Participants', 'Participants', 'manage_options', 'journee-pedagogique-participants', 'display_jp_participants');
    //add_submenu_page( 'journee-pedagogique', 'Participants (brute)', 'Participants (brute)', 'manage_options', 'journee-pedagogique-participants-brute', 'download_jp_participants');
    add_submenu_page( 'journee-pedagogique', 'Statistiques', 'Statistiques', 'manage_options', 'journee-pedagogique-statistiques', 'display_jp_statistiques');
    add_submenu_page( 'journee-pedagogique', 'Répartition automatique', 'Répartition automatique', 'manage_options', 'journee-pedagogique-repartition', 'display_jp_repartition');
}

// CSS
function jp_admin_theme_style() {
    wp_enqueue_style('journee-pedagogique-style', plugins_url('wp-admin.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'jp_admin_theme_style');
add_action('login_enqueue_scripts', 'jp_admin_theme_style');

?>
