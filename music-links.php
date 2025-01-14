<?php
/**
 * Plugin Name: Music Store Links
 * Description: Easily add Online Music Store Links into your WordPress posts, pages, and custom post types
 * Version: 1.0.1
 */

/**
 * Do not load this file directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
    die();
}

function custom_register_song_post_type() {
    register_post_type('album', [
        'labels' => [
            'name' => __('Albums', 'themify'),
            'singular_name' => __('Album', 'themify'),
        ],
        'supports' => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'revisions', 'page-attributes'],
        'has_archive' => true,
        'hierarchical' => false,
        'public' => true,
        'exclude_from_search' => false,
        'rewrite' => ['slug' => 'songs', 'with_front' => false],
        'query_var' => true,
        'can_export' => true,
        'capability_type' => 'post',
        'menu_icon' => 'dashicons-images-alt2',
    ]);
}
add_action('init', 'custom_register_song_post_type', 20);

function gp_add_meta_boxes() {
    add_meta_box('song_link', 'Song Links', 'song_link_callback', 'album', 'advanced', 'high');
    add_meta_box('song_link', 'Song Links', 'song_link_callback', 'projects', 'advanced', 'high');
}
add_action('add_meta_boxes', 'gp_add_meta_boxes');

function song_link_callback($post) {
    // Display a note for the shortcode
    echo "<p><strong>Note:</strong> Use the shortcode <code>[song_links]</code> in your post to display the links added here.</p>";

    $links = [
        'itunes' => 'iTunes Link',
        'spotify' => 'Spotify Link',
        'amazon' => 'Amazon Link',
        'gplay' => 'Google Play Link',
        'cdbaby' => 'CD Baby Link',
        'sndcld' => 'SoundCloud Link',
        'bandcamp' => 'BandCamp Link',
        'pandora' => 'Pandora Link',
    ];

    // Add icons to links
    foreach ($links as $key => $label) {
        $value = get_post_meta($post->ID, $key, true);
        $icon_url = get_post_meta($post->ID, $key . '_icon', true);  // Get icon URL from meta
        echo "<p>{$label}: <input type='text' name='{$key}' value='{$value}' style='width:100%;' /></p>";

        // Add input for the icon URL
        echo "<p>{$label} Icon URL: <input type='text' name='{$key}_icon' value='{$icon_url}' style='width:100%;' placeholder='Icon URL' /></p>";

        // Display the image preview if an icon URL is set
        if($icon_url){
            echo "<div id='upload_logo_preview_{$key}' style='background-image:url($icon_url); height:50px; width:50px; margin: 10px 0;'></div>";  
        }             

        echo "<button type='button' class='button select-img' data-target='{$key}_icon'>Select Image</button>";
    }
}



function wpdocs_save_meta_box($post_id, $post, $update) {
    // Ensure the post type is either 'album' or 'projects'
    if (get_post_type($post_id) !== 'album' && get_post_type($post_id) !== 'projects') {
        return;
    }

    $fields = ['itunes', 'spotify', 'amazon', 'gplay', 'cdbaby', 'sndcld', 'bandcamp', 'pandora'];
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, $field, esc_attr($_POST[$field]));
        }
        if (isset($_POST[$field . '_icon'])) {
            update_post_meta($post_id, $field . '_icon', esc_url_raw($_POST[$field . '_icon']));
        }
    }
}
add_action('save_post', 'wpdocs_save_meta_box', 10, 3);


/** ---------------------------------------------  
 * Adds a custom menu page to enter main music store links.
 * -------------------------------------------- */
function msl_add_menu_page() {
    add_menu_page(
        __('Music Store Links', 'music-store-links'),
        __('Music Store Links', 'music-store-links'),
        'manage_options',
        'music-store-links',
        'msl_menu_page_content',
        'dashicons-admin-links',
        6
    );
}
add_action('admin_menu', 'msl_add_menu_page');

/**
 * Display content for the custom menu page.
 */
function msl_menu_page_content() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if ( isset($_POST['msl_submit']) ) {
        $links = array(
            'itunes' => sanitize_text_field($_POST['msl_itunes']),
            'spotify' => sanitize_text_field($_POST['msl_spotify']),
            'amazon' => sanitize_text_field($_POST['msl_amazon']),
            'cdbaby' => sanitize_text_field($_POST['msl_cdbaby']),
            'soundcloud' => sanitize_text_field($_POST['msl_soundcloud']),
            'bandcamp' => sanitize_text_field($_POST['msl_bandcamp']),
            'itunes_icon' => esc_url_raw($_POST['msl_itunes_icon']),
            'spotify_icon' => esc_url_raw($_POST['msl_spotify_icon']),
            'amazon_icon' => esc_url_raw($_POST['msl_amazon_icon']),
            'cdbaby_icon' => esc_url_raw($_POST['msl_cdbaby_icon']),
            'soundcloud_icon' => esc_url_raw($_POST['msl_soundcloud_icon']),
            'bandcamp_icon' => esc_url_raw($_POST['msl_bandcamp_icon']),
        );
        update_option('msl_links', $links);
        echo '<div id="message" class="updated notice is-dismissible"><p>' . __('Links updated.', 'music-store-links') . '</p></div>';
    }

    $links = get_option('msl_links', array());
    ?>
    <div class="wrap">
        <h1><?php _e('Main Music Links', 'music-store-links'); ?></h1>

        <form method="post">
            <table class="form-table">
                <?php
                $fields = array(
                    'itunes' => 'iTunes',
                    'spotify' => 'Spotify',
                    'amazon' => 'Amazon',
                    'cdbaby' => 'CD Baby',
                    'soundcloud' => 'SoundCloud',
                    'bandcamp' => 'Bandcamp'
                );
                foreach ($fields as $key => $label) {
                    ?>
                    <tr valign="top">
                        <th scope="row"><?php echo $label; ?></th>
                        <td>
                            <input type="text" name="msl_<?php echo $key; ?>" value="<?php echo esc_attr($links[$key] ?? ''); ?>" class="regular-text" placeholder="<?php echo $label; ?> Link">
                            <input type="text" id="msl_<?php echo $key; ?>_icon" name="msl_<?php echo $key; ?>_icon" value="<?php echo esc_attr($links[$key.'_icon'] ?? ''); ?>" class="regular-text" placeholder="<?php echo $label; ?> Icon URL">
                            <button type="button" class="button select-img" data-target="msl_<?php echo $key; ?>_icon"><?php _e('Select Image', 'music-store-links'); ?></button>
                            <div id="upload_logo_preview_<?php echo $key; ?>" style="background-image:url('<?php echo esc_attr($links[$key.'_icon'] ?? ''); ?>');height: 50px;width: 50px;margin: 1rem 0;"></div>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
            <p class="submit">
                <input type="submit" name="msl_submit" class="button-primary" value="<?php _e('Save Changes', 'music-store-links'); ?>">
            </p>
        </form>
    </div>
    <?php
}

function display_song_links_shortcode($atts) {
    global $post;

    if (!in_array(get_post_type($post->ID), ['album', 'projects'])) {
        return '';
    }

    $links = [
        'itunes' => 'iTunes',
        'spotify' => 'Spotify',
        'amazon' => 'Amazon',
        'gplay' => 'Google Play',
        'cdbaby' => 'CD Baby',
        'sndcld' => 'SoundCloud',
        'bandcamp' => 'BandCamp',
        'pandora' => 'Pandora',
    ];

    $output = '<ul class="song-links">';
    foreach ($links as $key => $label) {
        $value = get_post_meta($post->ID, $key, true);
        $icon = get_post_meta($post->ID, $key . '_icon', true); // Get the icon URL

        if ($value) {
            $output .= "<li><a href='" . esc_url($value) . "' target='_blank'>";
            if ($icon) {
                $output .= "<img src='" . esc_url($icon) . "' alt='" . esc_attr($label) . "' class='song-link-icon'> "; 
            } else {
                $output .= $label; 
            }
            $output .= "</a></li>";
        }
    }
    $output .= '</ul>';

    return $output;
}
add_shortcode('song_links', 'display_song_links_shortcode');

/**
 * Create a shortcode to display the themes music store links.
 */
function msl_display_links_shortcode() {
    $links = get_option('msl_links', array());
    ob_start();
    echo '<ul id="music-stores">';
    foreach ($links as $key => $url) {
        if ( strpos($key, '_icon') === false && !empty($url) ) {
            $icon = !empty($links[$key.'_icon']) ? '<img src="'.esc_url($links[$key.'_icon']).'" alt="'.esc_attr(ucfirst($key)).' Icon" style="max-width:50px;">' : ucfirst($key);
            echo '<li><a class="music-store-link" target="_blank" href="' . esc_url($url) . '">' . $icon . '</a></li>';
        }
    }
    echo '</ul>';
    return ob_get_clean();
}
add_shortcode('music_store_links', 'msl_display_links_shortcode');


function msl_shortcode_info() {
    $screen = get_current_screen();
    $current_page = isset($_GET['page']) ? $_GET['page'] : '';
    
    if ($screen && $screen->id === 'toplevel_page_music-store-links' && $current_page === 'music-store-links') {
        echo '<p>' . __('To display your main music store links, use the shortcode [music_store_links], to display the song music links for the current song, use the shortcode [song_link].', 'music-store-links') . '</p>';
    }
}

add_action('admin_notices', 'msl_shortcode_info');

function msl_enqueue_admin_scripts($hook) { 
    wp_enqueue_media();
    wp_enqueue_script('msl-media-upload', plugin_dir_url(__FILE__) . 'js/msl-media-upload.js', array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'msl_enqueue_admin_scripts');


// Enqueue stylesheet on the front end
function msl_enqueue_frontend_styles() {    
        wp_enqueue_style('msl-media-css', plugin_dir_url(__FILE__) . 'css/music-links.css', array(), null);    
}
add_action('wp_enqueue_scripts', 'msl_enqueue_frontend_styles');