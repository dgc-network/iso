<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Register custom post type
function register_document_post_type() {
    $labels = array(
        'name'               => _x( 'Documents', 'post type general name', 'your-text-domain' ),
        'singular_name'      => _x( 'Document', 'post type singular name', 'your-text-domain' ),
        'add_new'            => _x( 'Add New Document', 'book', 'your-text-domain' ),
        'add_new_item'       => __( 'Add New Document', 'your-text-domain' ),
        'edit_item'          => __( 'Edit Document', 'your-text-domain' ),
        'new_item'           => __( 'New Document', 'your-text-domain' ),
        'all_items'          => __( 'All Documents', 'your-text-domain' ),
        'view_item'          => __( 'View Document', 'your-text-domain' ),
        'search_items'       => __( 'Search Documents', 'your-text-domain' ),
        'not_found'          => __( 'No documents found', 'your-text-domain' ),
        'not_found_in_trash' => __( 'No documents found in the Trash', 'your-text-domain' ),
        'parent_item_colon'  => '',
        'menu_name'          => 'Documents'
    );

    $args = array(
        'labels'        => $labels,
        'public'        => true,
        'supports'      => array( 'title', 'custom-fields' ),
        'taxonomies'    => array( 'category', 'post_tag' ),
        'has_archive'   => true,
        'rewrite'       => array('slug' => 'documents'),
        'menu_icon'     => 'dashicons-media-document',
    );
    register_post_type( 'document', $args );
}
add_action('init', 'register_document_post_type');

// Shortcode to display documents
function display_documents_shortcode() {
    //ob_start(); // Start output buffering

    // Check if the user is logged in
    if (is_user_logged_in()) {
        $current_user_id = get_current_user_id();
        $site_id = esc_attr(get_post_meta($current_user_id, 'site_id', true));
        $user_data = get_userdata( $current_user_id );

        ?>
        <div class="ui-widget">
        <h2><?php echo __( 'Documents', 'your-text-domain' );?></h2>
        <label for="display-name">Name : </label>
        <input type="text" id="display-name" value="<?php echo $user_data->display_name;?>" class="text ui-widget-content ui-corner-all" disabled />
        <label for="site-title"> Site: </label>
        <input type="text" id="site-title" value="<?php echo get_the_title($site_id);?>" class="text ui-widget-content ui-corner-all" disabled />
        <table class="ui-widget" style="width:100%;">
            <thead>
                <tr>
                    <th></th>
                    <th><?php echo __( '文件名稱', 'your-text-domain' );?></th>
                    <th><?php echo __( '編號', 'your-text-domain' );?></th>
                    <th><?php echo __( '版本', 'your-text-domain' );?></th>
                    <th><?php echo __( '發行日期', 'your-text-domain' );?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
        <?php

        $query = retrieve_documents_data($site_id);
        if ($query->have_posts()) :
            $x = 0;
            while ($query->have_posts()) : $query->the_post();
                $post_id = (int) get_the_ID();
                $doc_url = esc_html(get_post_meta($post_id, 'doc_url', true));
                ?>
                    <tr id="document-list-<?php echo $x;?>">
                        <td style="text-align:center;"><span id="btn-edit-document-<?php the_ID();?>" class="dashicons dashicons-edit"></span></td>
                        <td><a href="<?php echo $doc_url;?>"><?php the_title();?></a></td>
                        <td style="text-align:center;"><?php echo esc_html(get_post_meta($post_id, 'doc_number', true));?></td>
                        <td style="text-align:center;"><?php echo esc_html(get_post_meta($post_id, 'doc_revision', true));?></td>
                        <td style="text-align:center;"><?php echo esc_html(get_post_meta($post_id, 'doc_date', true));?></td>
                        <td style="text-align:center;"><span id="btn-del-document-<?php the_ID();?>" class="dashicons dashicons-trash"></span></td>
                    </tr>
                <?php 
                $x += 1;
            endwhile;
            while ($x<50) {
                echo '<tr id="document-list-'.$x.'" style="display:none;"></tr>';
                $x += 1;
            }
            wp_reset_postdata();
        endif;
        ?>
            </tbody>
                <tr><td colspan="6"><div id="btn-new-document" style="border:solid; margin:3px; text-align:center; border-radius:5px">+</div></td></tr>
        </table>

        <div id="document-dialog" title="Document dialog" style="display:none;">
        <fieldset>
            <input type="hidden" id="site-id" value="<?php echo $site_id;?>"/>
            <input type="hidden" id="doc-id" />
            <label for="doc-title">Title:</label>
            <input type="text" id="doc-title" />
            <div>
                <div style="display:inline-block;">
                    <label for="doc-number">Doc.#:</label>
                    <input type="text" id="doc-number" />
                </div>
                <div style="display:inline-block;">
                    <label for="doc-revision">Revision:</label>
                    <input type="text" id="doc-revision" style="width:30px;" />
                </div>
                <div style="display:inline-block;">
                    <label for="doc-date">Date:</label>
                    <input type="text" id="doc-date" />
                </div>
            </div>
            <label for="doc-url">URL:</label>
            <textarea id="doc-url" rows="3" style="width:99%;"></textarea>

            <table style="width:100%;">
            <thead>
                <tr>
                    <th></th>
                    <th><?php echo __( 'Action', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Description', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Submit', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Time', 'your-text-domain' );?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
        <?php
        $x = 0;
        while ($x<50) {
            echo '<tr id="doc-action-list-'.$x.'" style="display:none;"></tr>';
            $x += 1;
        }
        ?>
            </tbody>
            <tr><td colspan="6"><div id="btn-new-doc-action" style="border:solid; margin:3px; text-align:center; border-radius:5px">+</div></td></tr>
            </table>
        </fieldset>
        </div>
        </div>

        <?php

    } else {
        user_did_not_login_yet();
    }
    //return ob_get_clean(); // Return the buffered content
    
}
add_shortcode('display-documents', 'display_documents_shortcode');

function site_documents_table($site_id=0) {
}

function retrieve_documents_data($site_id=0) {
    // Retrieve the documents value
    $args = array(
        'post_type'      => 'document', // Change to your custom post type if needed
        'posts_per_page' => -1, // Retrieve all posts
        'meta_query'     => array(
            'relation' => 'AND', // Combine conditions with AND
            array(
                'key'     => 'course_id', // Replace with your first meta key
                'value'   => $_id, // Replace with the desired value
                'compare' => '=', // Change to your desired comparison (e.g., '=', '>', '<', etc.)
            ),
            array(
                'key'     => 'sorting_in_course', // Replace with your second meta key
                'compare' => 'EXISTS', // Check if the second meta key exists
            ),
        ),
        'orderby'        => 'meta_value', // Order by the second meta key value
        'meta_key'       => 'sorting_in_course', // Specify the second meta key for ordering
        'order'          => 'ASC', // Change to 'DESC' for descending order
    );
    
    $args = array(
        'post_type'      => 'document',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'   => 'site_id',
                'value' => $site_id,
            ),
        ),
    );
/*
    $args = array(
        'post_type'      => 'document',
        'posts_per_page' => -1,
    );
*/
    $query = new WP_Query($args);
    return $query;
}

function get_document_list_data() {
    // Retrieve the documents data
    $query = retrieve_documents_data($_POST['_site_id']);

    $_array = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            $post_id = (int) get_the_ID();
            $doc_url = esc_html(get_post_meta($post_id, 'doc_url', true));
            $_list = array();
            $_list["doc_id"] = get_the_ID();
            $_list["doc_title"] = '<a href="'.$doc_url.'">'.get_the_title().'</a>';
            $_list["doc_number"] = esc_html(get_post_meta($post_id, 'doc_number', true));
            $_list["doc_revision"] = esc_html(get_post_meta($post_id, 'doc_revision', true));
            $_list["doc_date"] = esc_html(get_post_meta($post_id, 'doc_date', true));
            $_list["doc_url"] = esc_html(get_post_meta($post_id, 'doc_url', true));
            array_push($_array, $_list);
        endwhile;
        wp_reset_postdata(); // Reset post data to the main loop
    }
    wp_send_json($_array);
}
add_action( 'wp_ajax_get_document_list_data', 'get_document_list_data' );
add_action( 'wp_ajax_nopriv_get_document_list_data', 'get_document_list_data' );

function get_document_dialog_data() {
    $response = array();
    if( isset($_POST['_doc_id']) ) {
        $response["doc_title"] = get_the_title($_POST['_doc_id']);
        $response["doc_number"] = esc_html(get_post_meta($_POST['_doc_id'], 'doc_number', true));
        $response["doc_revision"] = esc_html(get_post_meta($_POST['_doc_id'], 'doc_revision', true));
        $response["doc_date"] = esc_html(get_post_meta($_POST['_doc_id'], 'doc_date', true));
        $response["doc_url"] = esc_html(get_post_meta($_POST['_doc_id'], 'doc_url', true));

        $args = array(
            'post_type'      => 'job',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'   => 'doc_id',
                    'value' => $_POST['_doc_id'],
                ),
            ),
        );
        $query = new WP_Query($args);
        $_array = array();
        if ($query->have_posts()) {
            while ($query->have_posts()) : $query->the_post();
                $post_id = (int) get_the_ID();
                $doc_action_id = esc_html(get_post_meta($post_id, 'doc_action_id', true)); //get doc_action_id from site_action_id
                //$doc_action_title = esc_html(get_the_title($doc_action_id)); //get site_action_title
                //$doc_action_content = esc_html(get_the_content($doc_action_id)); //get site_action_content from site_actions
                $doc_next_action_id = esc_html(get_post_meta($post_id, 'doc_next_action_id', true)); //get doc_next_action_id from site_next_action_id
                $doc_next_action_title = esc_html(get_the_title($doc_next_action_id)); //get site_next_action_title 
                $doc_next_action_leadtime = esc_html(get_post_meta($post_id, 'doc_next_action_leadtime', true)); //get site_action_leadtime from site_next_action_leadtime
                $doc_submit_user_id = esc_html(get_post_meta($post_id, 'doc_action_submit_user', true));
                $user = get_userdata($doc_submit_user_id);
                //$doc_action_submit_user = esc_html($user->display_name);
                //$doc_action_submit_time = esc_html(get_post_meta($post_id, 'doc_action_submit_time', true)); //get doc_next_action_id from site_next_action_id

                $_list = array();
                $_list["action_id"] = get_the_ID();
                $_list["action_title"] = esc_html(get_the_title($doc_action_id));
                $_list["action_content"] = esc_html(get_the_content($doc_action_id));
                $_list["action_submit_user"] = esc_html($user->display_name);
                $_list["action_submit_time"] = esc_html(get_post_meta($post_id, 'doc_action_submit_time', true));
                array_push($_array, $_list);
            endwhile;
            wp_reset_postdata(); // Reset post data to the main loop
        }
        $response["action_array"] = $_array;

    }
    wp_send_json($response);
}
add_action( 'wp_ajax_get_document_dialog_data', 'get_document_dialog_data' );
add_action( 'wp_ajax_nopriv_get_document_dialog_data', 'get_document_dialog_data' );

function set_document_dialog_data() {
    if( isset($_POST['_doc_id']) ) {
        $data = array(
            'ID'         => $_POST['_doc_id'],
            'post_title' => $_POST['_doc_title'],
            'meta_input' => array(
                'doc_number'   => $_POST['_doc_number'],
                'doc_revision' => $_POST['_doc_revision'],
                'doc_date'     => $_POST['_doc_date'],
                'doc_url'     => $_POST['_doc_url'],
                'site_id'     => $_POST['_site_id'],
            )
        );
        wp_update_post( $data );
    } else {
        $current_user = wp_get_current_user();
        // Set up the post data
        $new_post = array(
            'post_title'    => 'New document',
            'post_content'  => 'Your post content goes here.',
            'post_status'   => 'publish', // Publish the post immediately
            'post_author'   => $current_user->ID, // Use the user ID of the author
            'post_type'     => 'document', // Change to your custom post type if needed
        );    
        // Insert the post into the database
        $post_id = wp_insert_post($new_post);
        update_post_meta( $post_id, 'site_id', sanitize_text_field($_POST['_site_id']));
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_set_document_dialog_data', 'set_document_dialog_data' );
add_action( 'wp_ajax_nopriv_set_document_dialog_data', 'set_document_dialog_data' );

function del_document_dialog_data() {
    // Delete the post
    $result = wp_delete_post($_POST['_doc_id'], true); // Set the second parameter to true to force delete    
    wp_send_json($result);
}
add_action( 'wp_ajax_del_document_dialog_data', 'del_document_dialog_data' );
add_action( 'wp_ajax_nopriv_del_document_dialog_data', 'del_document_dialog_data' );

