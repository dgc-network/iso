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
        'show_in_menu'  => false, // Set this to false to hide from the admin menu
    );
    register_post_type( 'document', $args );
}
add_action('init', 'register_document_post_type');

// Register action post type
function register_action_post_type() {
    $args = array(
        'public'        => true,
        'rewrite'       => array('slug' => 'actions'),
        'supports'      => array( 'title', 'editor', 'custom-fields' ),
        'has_archive'   => true,
        'show_in_menu'  => false, // Set this to false to hide from the admin menu
    );
    register_post_type( 'action', $args );
}
add_action('init', 'register_action_post_type');

// Shortcode to display documents
function display_documents_shortcode() {
    // Check if the user is logged in
    if (is_user_logged_in()) {
        $current_user_id = get_current_user_id();
        $site_id = esc_attr(get_post_meta($current_user_id, 'site_id', true));
        $user_data = get_userdata( $current_user_id );
        ?>
        <h2><?php echo __( 'Documents', 'your-text-domain' );?></h2>
        <div class="ui-widget">
        <fieldset>
            <label for="display-name">Name : </label>
            <input type="text" id="display-name" value="<?php echo $user_data->display_name;?>" class="text ui-widget-content ui-corner-all" disabled />
            <label for="site-title"> Site: </label>
            <input type="text" id="site-title" value="<?php echo get_the_title($site_id);?>" class="text ui-widget-content ui-corner-all" disabled />
        
            <table class="ui-widget" style="width:100%;">
                <thead>
                    <tr>
                        <th><?php echo __( '文件編號', 'your-text-domain' );?></th>
                        <th><?php echo __( '名稱', 'your-text-domain' );?></th>
                        <th><?php echo __( '版本', 'your-text-domain' );?></th>
                        <th><?php echo __( '發行日期', 'your-text-domain' );?></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $query = retrieve_document_list_data($site_id);
                if ($query->have_posts()) :
                    $x = 0;
                    while ($query->have_posts()) : $query->the_post();
                        $post_id = (int) get_the_ID();
                        $doc_url = esc_html(get_post_meta($post_id, 'doc_url', true));
                        $doc_date = esc_attr(get_post_meta($post_id, 'doc_date', true));
                        ?>
                        <tr class="document-list-<?php echo $x;?>" id="edit-document-<?php the_ID();?>">
                            <td style="text-align:center;"><?php echo esc_html(get_post_meta($post_id, 'doc_number', true));?></td>
                            <td><a href="<?php echo $doc_url;?>"><?php the_title();?></a></td>
                            <td style="text-align:center;"><?php echo esc_html(get_post_meta($post_id, 'doc_revision', true));?></td>
                            <td style="text-align:center;"><?php echo wp_date( get_option('date_format'), $doc_date );?></td>
                        </tr>
                        <?php 
                        $x += 1;
                    endwhile;
                    wp_reset_postdata();
                    while ($x<50) {
                        echo '<tr class="document-list-'.$x.'" style="display:none;"></tr>';
                        $x += 1;
                    }
                endif;
                ?>
                </tbody>
            </table>
            <div id="btn-new-document" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
            <?php display_document_dialog($site_id);?>
        </fieldset>
        </div>
        <?php

    } else {
        user_did_not_login_yet();
    }
}
add_shortcode('display-documents', 'display_documents_shortcode');

function retrieve_document_list_data($site_id=0) {
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
    $query = new WP_Query($args);
    return $query;
}

function get_document_list_data() {
    // Retrieve the documents data
    $query = retrieve_document_list_data($_POST['_site_id']);
    $_array = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            $post_id = (int) get_the_ID();
            $doc_url = esc_html(get_post_meta($post_id, 'doc_url', true));
            $_list = array();
            $_list["doc_id"] = $post_id;
            $_list["doc_title"] = '<a href="'.$doc_url.'">'.get_the_title().'</a>';
            $_list["doc_number"] = esc_html(get_post_meta($post_id, 'doc_number', true));
            $_list["doc_revision"] = esc_html(get_post_meta($post_id, 'doc_revision', true));
            $doc_date = esc_attr(get_post_meta($post_id, 'doc_date', true));
            $_list["doc_date"] = wp_date( get_option('date_format'), $doc_date );
            array_push($_array, $_list);
        endwhile;
        wp_reset_postdata();
    }
    wp_send_json($_array);
}
add_action( 'wp_ajax_get_document_list_data', 'get_document_list_data' );
add_action( 'wp_ajax_nopriv_get_document_list_data', 'get_document_list_data' );

function display_document_dialog($site_id=0){
    ?>
    <div id="document-dialog" title="Document dialog" style="display:none;">
        <fieldset>
            <input type="hidden" id="site-id" value="<?php echo $site_id;?>"/>
            <input type="hidden" id="doc-id" />
            <label for="doc-title">Title:</label>
            <input type="text" id="doc-title" class="text ui-widget-content ui-corner-all" />
            <div>
                <div style="display:inline-block;">
                    <label for="doc-number">Doc.#:</label>
                    <input type="text" id="doc-number" class="text ui-widget-content ui-corner-all" />
                </div>
                <div style="display:inline-block; width:25%;">
                    <label for="doc-revision">Revision:</label>
                    <input type="text" id="doc-revision" class="text ui-widget-content ui-corner-all" />
                </div>
            </div>
            <label for="doc-url">URL:</label>
            <textarea id="doc-url" rows="3" class="text ui-widget-content ui-corner-all" ></textarea>
    
            <div>
                <div style="display:inline-block;">
                    <label for="start-job">Start:</label>
                    <select id="start-job" class="text ui-widget-content ui-corner-all" ></select>
                </div>
                <div style="display:inline-block; width:25%;">
                    <label for="start-leadtime">Leadtime:</label>
                    <input type="text" id="start-leadtime" class="text ui-widget-content ui-corner-all" />
                </div>
            </div>
            <div>
                <div style="display:inline-block; width:35%;">
                    <label for="doc-date">Published Date:</label>
                    <input type="text" id="doc-date" class="text ui-widget-content ui-corner-all" disabled />
                </div>
            </div>
            <label for="btn-doc-workflow">Workflow:</label>
            <div id="btn-doc-workflow" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;"><span class="dashicons dashicons-networking"></span> Workflow list</div>
            <?php display_doc_workflow_list();?>

        </fieldset>
    </div>
    <?php
}
    
function get_document_dialog_data() {
    $response = array();
    if( isset($_POST['_doc_id']) ) {
        $doc_id = (int)sanitize_text_field($_POST['_doc_id']);
        $response["doc_title"] = get_the_title($doc_id);
        $response["doc_number"] = esc_html(get_post_meta($doc_id, 'doc_number', true));
        $response["doc_revision"] = esc_html(get_post_meta($doc_id, 'doc_revision', true));
        $response["doc_url"] = esc_html(get_post_meta($doc_id, 'doc_url', true));
        $start_job = esc_attr(get_post_meta($doc_id, 'start_job', true));
        $response["start_job"] = select_site_job_option_data($start_job, $_POST['_site_id']);
        $response["start_leadtime"] = esc_html(get_post_meta($doc_id, 'start_leadtime', true));
        $doc_date = esc_attr(get_post_meta($doc_id, 'doc_date', true));
        $response["doc_date"] = wp_date( get_option('date_format'), $doc_date );
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_get_document_dialog_data', 'get_document_dialog_data' );
add_action( 'wp_ajax_nopriv_get_document_dialog_data', 'get_document_dialog_data' );

function set_document_dialog_data() {
    $current_user_id = get_current_user_id();
    if( isset($_POST['_doc_id']) ) {
        $doc_id = sanitize_text_field($_POST['_doc_id']);
        $start_job = sanitize_text_field($_POST['_start_job']);
        $start_leadtime = sanitize_text_field($_POST['_start_leadtime']);
        set_next_job_and_actions($start_job, 0, $doc_id, $start_leadtime);
        // Update the Document data
        $data = array(
            'ID'         => $_POST['_doc_id'],
            'post_title' => $_POST['_doc_title'],
            'meta_input' => array(
                'doc_number'   => $_POST['_doc_number'],
                'doc_revision' => $_POST['_doc_revision'],
                'doc_url'      => $_POST['_doc_url'],
                'start_job'    => $start_job,
                'start_leadtime' => $start_leadtime,
            )
        );
        wp_update_post( $data );
    } else {
        // Insert the post into the database
        $new_post = array(
            'post_title'    => 'New document',
            'post_content'  => 'Your post content goes here.',
            'post_status'   => 'publish',
            'post_author'   => $current_user_id,
            'post_type'     => 'document',
        );    
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

function display_doc_workflow_list() {
    ?>
    <div id="doc-workflow-list-dialog" title="Workflow list" style="display:none;">
        <table style="width:100%;">
            <thead>
                <tr>
                    <th><?php echo __( 'Todo', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Description', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Submit', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Time', 'your-text-domain' );?></th>
                </tr>
            </thead>
            <tbody>
            <?php
                $x = 0;
                while ($x<50) {
                    echo '<tr class="doc-workflow-list-'.$x.'" style="display:none;"></tr>';
                    $x += 1;
                }
            ?>
            </tbody>
        </table>
    </div>
    <?php
}

function retrieve_doc_workflow_list_data($doc_id=0){
    $args = array(
        'post_type'      => 'todo',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => 'doc_id',
                'value'   => $doc_id,
                'compare' => '=',
            ),
        ),
    );
    $query = new WP_Query($args);
    return $query;
}

function get_doc_workflow_list_data() {
    // Retrieve the document workflows data
    $query = retrieve_doc_workflow_list_data($_POST['_doc_id']);
    $_array = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            $_list = array();
            $_list["todo_id"] = get_the_ID();
            $_list["todo_title"] = get_the_title();
            $_list["todo_content"] = get_post_field('post_content', get_the_ID());
            $_list["submit_user"] = esc_html(get_post_meta(get_the_ID(), 'submit_user', true));
            $submit_time = esc_html(get_post_meta(get_the_ID(), 'submit_time', true));            
            $_list["submit_time"] = wp_date( get_option('date_format'), $submit_time );
            array_push($_array, $_list);
        endwhile;
        wp_reset_postdata(); // Reset post data to the main loop
    }
    wp_send_json($_array);
}
add_action( 'wp_ajax_get_doc_workflow_list_data', 'get_doc_workflow_list_data' );
add_action( 'wp_ajax_nopriv_get_doc_workflow_list_data', 'get_doc_workflow_list_data' );

