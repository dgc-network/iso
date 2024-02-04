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
        //'show_in_menu'  => false, // Set this to false to hide from the admin menu
    );
    register_post_type( 'document', $args );
}
add_action('init', 'register_document_post_type');

// Register doc category post type
function register_doc_field_post_type() {
    $args = array(
        'public'        => true,
        'rewrite'       => array('slug' => 'doc-fields'),
        'supports'      => array( 'title', 'editor', 'custom-fields' ),
        'has_archive'   => true,
        //'show_in_menu'  => false, // Set this to false to hide from the admin menu
    );
    register_post_type( 'doc-field', $args );
}
add_action('init', 'register_doc_field_post_type');

// Register doc category post type
function register_doc_category_post_type() {
    $args = array(
        'public'        => true,
        'rewrite'       => array('slug' => 'doc-categories'),
        'supports'      => array( 'title', 'editor', 'custom-fields' ),
        'has_archive'   => true,
        //'show_in_menu'  => false, // Set this to false to hide from the admin menu
    );
    register_post_type( 'doc-category', $args );
}
add_action('init', 'register_doc_category_post_type');

// Shortcode to display documents
function display_documents_shortcode() {
    // Migration
    if( isset($_GET['_doc_title_migration']) ) {
        // Retrieve the value
        $args = array(
            'post_type'      => 'document',
            'posts_per_page' => -1,
        );
        $query = new WP_Query($args);
        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post();
                update_post_meta( get_the_ID(), 'doc_title', get_the_title());
            endwhile;
            wp_reset_postdata();
        endif;    
    }
    
    // Check if the user is logged in
    if (is_user_logged_in()) {
        $current_user_id = get_current_user_id();
        $site_id = esc_attr(get_post_meta($current_user_id, 'site_id', true));
        $user_data = get_userdata( $current_user_id );
        ?>
        <div class="ui-widget" id="result-container">
        <h2><?php echo __( 'Documents', 'your-text-domain' );?></h2>
        <fieldset>
            <div id="document-setting-div" style="display:none">
            <fieldset>
                <label for="display-name">Name : </label>
                <input type="text" id="display-name" value="<?php echo $user_data->display_name;?>" class="text ui-widget-content ui-corner-all" disabled />
                <label for="site-title"> Site: </label>
                <input type="text" id="site-title" value="<?php echo get_the_title($site_id);?>" class="text ui-widget-content ui-corner-all" disabled />
                <input type="hidden" id="site-id" value="<?php echo $site_id;?>" />
            </fieldset>
            </div>
        
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                    <select id="select-category"><?php echo select_doc_category_option_data($_GET['_category']);?></select>
                </div>
                <div style="text-align: right">
                    <input type="text" id="search-document" style="display:inline" placeholder="Search..." />
                    <span id="btn-document-setting" style="margin-left:5px;" class="dashicons dashicons-admin-generic"></span>
                </div>
            </div>

            <table class="ui-widget" style="width:100%;">
                <thead>
                    <tr>
                        <th><?php echo __( '文件編號', 'your-text-domain' );?></th>
                        <th><?php echo __( '名稱', 'your-text-domain' );?></th>
                        <th><?php echo __( '版本', 'your-text-domain' );?></th>
                        <th><?php echo __( '狀態', 'your-text-domain' );?></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $query = retrieve_document_list_data($site_id);
                if ($query->have_posts()) :
                    //$x = 0;
                    while ($query->have_posts()) : $query->the_post();
                        $post_id = (int) get_the_ID();
                        $doc_title = esc_html(get_post_meta($post_id, 'doc_title', true));
                        $doc_url = esc_html(get_post_meta($post_id, 'doc_url', true));
                        $doc_date = esc_attr(get_post_meta($post_id, 'doc_date', true));
                        $doc_status = esc_attr(get_post_meta($post_id, 'doc_status', true));
                        $deleting = esc_attr(get_post_meta($post_id, 'deleting', true));
                        ?>
                        <tr class="document-list-<?php echo $x;?>" id="edit-document-<?php the_ID();?>">
                            <td style="text-align:center;"><?php echo esc_html(get_post_meta($post_id, 'doc_number', true));?></td>
                            <?php if ($doc_date){?><td><a href="<?php echo $doc_url;?>"><?php echo $doc_title;?></a></td><?php }?>
                            <?php if (!$doc_date){?><td><?php  echo $doc_title;?></td><?php }?>
                            <td style="text-align:center;"><?php echo esc_html(get_post_meta($post_id, 'doc_revision', true));?></td>
                            <td style="text-align:center;"><?php echo wp_date( get_option('date_format'), $doc_date );?></td>
                        </tr>
                        <?php 
                        //$x += 1;
                    endwhile;
                    wp_reset_postdata();
                    //while ($x<50) {
                    //    echo '<tr class="document-list-'.$x.'" style="display:none;"></tr>';
                    //    $x += 1;
                    //}
                endif;
                ?>
                </tbody>
            </table>
            <input type ="button" id="new-document-button" value="+" style="width:100%; margin:3px; border-radius:5px; font-size:small;" />
        </fieldset>
        </div>
        <?php

    } else {
        user_did_not_login_yet();
    }
}
add_shortcode('display-documents', 'display_documents_shortcode');

function retrieve_document_list_data($site_id = 0) {
    $site_filter = array(
        'key'     => 'site_id',
        'value'   => $site_id,
        'compare' => '=',
    );

    $select_category = sanitize_text_field($_GET['_category']);
    $category_filter = array(
        'key'     => 'doc_category',
        'value'   => $select_category,
        'compare' => '=',
    );

    $search_query = sanitize_text_field($_GET['_search']);
    $number_filter = array(
        'key'     => 'doc_number',
        'value'   => $search_query,
        'compare' => 'LIKE',
    );
    $title_filter = array(
        'key'     => 'doc_title',
        'value'   => $search_query,
        'compare' => 'LIKE',
    );

    $args = array(
        'post_type'      => 'document',
        'posts_per_page' => 30,
        'paged'          => (get_query_var('paged')) ? get_query_var('paged') : 1,
        'meta_query'     => array(
            'relation' => 'OR',
            array(
                'relation' => 'AND',
                ($site_id) ? $site_filter : '',
                ($select_category) ? $category_filter : '',
                ($search_query) ? $number_filter : '',
            ),
            array(
                'relation' => 'AND',
                ($site_id) ? $site_filter : '',
                ($select_category) ? $category_filter : '',
                ($search_query) ? $title_filter : '',
            )
        ),
        //'s'              => $search_query,
        'orderby'        => 'meta_value',
        'meta_key'       => 'doc_number',
        'order'          => 'ASC',
    );

    $query = new WP_Query($args);
    return $query;
}

function translate_custom_strings($original_string) {
    // Define translations for specific strings
    $translations = array(
        'doc_title' => __( '文件名稱', 'your-text-domain' ),
        'doc_number' => __( '文件編號', 'your-text-domain' ),
        'doc_revision' => __( '文件版本', 'your-text-domain' ),
        'doc_url' => __( '文件地址', 'your-text-domain' ),
        'start_job' => __( '起始職務', 'your-text-domain' ),
        'start_leadtime' => __( '前置時間', 'your-text-domain' ),
        'doc_category' => __( '文件類別', 'your-text-domain' ),
        'site_id' => __( '單位', 'your-text-domain' ),
        'todo_status' => __( '文件狀態', 'your-text-domain' ),
        // Add more translations as needed
    );
    // Check if there's a translation for the given string
    if (isset($translations[$original_string])) {
        return $translations[$original_string];
    }
    // If no translation is found, return the original string
    return $original_string;
}

function open_doc_dialog_and_buttons() {
    // Check if the action has been set
    if (isset($_POST['action']) && $_POST['action'] === 'open_doc_dialog_and_buttons') {
        $doc_id = (int)sanitize_text_field($_POST['_doc_id']);
        $todo_id = esc_attr(get_post_meta($doc_id, 'todo_status', true));
        $doc_url = esc_attr(get_post_meta($doc_id, 'doc_url', true));
        $params = array();

        echo '<h2>Document</h2>';
        display_doc_field_list();    
        echo '<fieldset>';
        echo '<div style="text-align: right" class="button">';
        echo '<span id="doc-field-setting" style="margin-left:5px;" class="dashicons dashicons-admin-generic"></span>';
        //echo '<input type="hidden" id="doc-id" value="'.$doc_id.'" />';
        echo '</div>';

        if (function_exists($doc_url) && is_callable($doc_url)) {
            $param_count = count($params);
            $expected_param_count = (new ReflectionFunction($doc_url))->getNumberOfParameters();
        
            if ($param_count === $expected_param_count) {
                // The function is valid, and the parameter count matches
                call_user_func_array($doc_url, $params);
            } else {
                // Invalid parameter count
                echo "Invalid parameter count for $doc_url";
            }
        } else {
            array_push($params, $doc_id);
            call_user_func_array('display_document_dialog', $params);
        }

        echo '<hr>';
        echo '<input type="button" id="set-document-button" value="'.__( 'Save', 'your-text-domain' ).'" style="margin:3px;" />';
        echo '<input type="button" id="del-document-button" value="'.__( 'Delete', 'your-text-domain' ).'" style="margin:3px;" />';
        echo '</fieldset>';
        
        wp_die();
    } else {
        // Handle invalid AJAX request
        echo 'Invalid AJAX request!';
        wp_die();
    }
}
add_action('wp_ajax_open_doc_dialog_and_buttons', 'open_doc_dialog_and_buttons');
add_action('wp_ajax_nopriv_open_doc_dialog_and_buttons', 'open_doc_dialog_and_buttons');

function display_document_dialog($post_id) {
    $site_id = esc_attr(get_post_meta($post_id, 'site_id', true));
    // Get all existing meta data for the specified post ID
    $all_meta = get_post_meta($post_id);
    // Output or manipulate the meta data as needed
    foreach ($all_meta as $key => $values) {
        if ($key!='site_id') 
        //if ($key!='todo_status') 
        foreach ($values as $value) {
            echo '<label for="'.$key.'">'.translate_custom_strings($key).'</label>';
            switch (true) {
                case strpos($key, 'url'):
                    echo '<textarea id="' . $key . '" rows="3" style="width:100%;">' . $value . '</textarea>';
                    break;
        
                    case strpos($key, '_job'):
                        echo '<select id="' . $key . '" class="text ui-widget-content ui-corner-all">' . select_start_job_option_data($value, $site_id) . '</select>';
                        break;
            
                    case strpos($key, '_category'):
                    echo '<select id="' . $key . '" class="text ui-widget-content ui-corner-all">' . select_doc_category_option_data($value) . '</select>';
                    break;
        
                default:
                    echo '<input type="text" id="' . $key . '" value="' . $value . '" class="text ui-widget-content ui-corner-all" />';
                    break;
            }
        }
    }
}

function select_start_job_option_data($selected_job=0, $site_id=0) {
    // Retrieve the value
    $args = array(
        'post_type'      => 'job',
        'posts_per_page' => -1,
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'   => 'site_id',
                'value' => $site_id,
            ),
            array(
                'key'   => 'is_start_job',
                'value' => 1,
            ),
        ),
    );
    $query = new WP_Query($args);
    $options = '<option value="">Select job</option>';
    while ($query->have_posts()) : $query->the_post();
        $selected = ($selected_job == get_the_ID()) ? 'selected' : '';
        $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html(get_the_title()) . '</option>';
    endwhile;
    wp_reset_postdata();
    return $options;
}

function select_doc_category_option_data($selected_category=0) {
    // Retrieve the value
    $args = array(
        'post_type'      => 'doc-category',
        'posts_per_page' => -1,
    );
    $query = new WP_Query($args);

    $options = '<option value="">Select category</option>';
    while ($query->have_posts()) : $query->the_post();
        $selected = ($selected_category == get_the_ID()) ? 'selected' : '';
        $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html(get_the_title()) . '</option>';
    endwhile;
    wp_reset_postdata();
    return $options;
}

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
            //'post_title' => $_POST['_doc_title'],
            'meta_input' => array(
                'doc_title'   => $_POST['_doc_title'],
                'doc_number'   => $_POST['_doc_number'],
                'doc_revision' => $_POST['_doc_revision'],
                'doc_url'      => $_POST['_doc_url'],
                'start_job'    => $start_job,
                'start_leadtime' => $start_leadtime,
                'doc_category'  => $_POST['_doc_category'],
            )
        );
        wp_update_post( $data );
    } else {
        // Insert the post into the database
        $new_post = array(
            'post_title'    => 'No title',
            'post_content'  => 'Your post content goes here.',
            'post_status'   => 'publish',
            'post_author'   => $current_user_id,
            'post_type'     => 'document',
        );    
        $post_id = wp_insert_post($new_post);
        update_post_meta( $post_id, 'site_id', sanitize_text_field($_POST['_site_id']));
        update_post_meta( $post_id, 'doc_title', 'New document');
        update_post_meta( $post_id, 'doc_number', '-');
        update_post_meta( $post_id, 'doc_revision', '');
        update_post_meta( $post_id, 'doc_url', '');
        update_post_meta( $post_id, 'start_job', 0);
        update_post_meta( $post_id, 'start_leadtime', 86400);
        update_post_meta( $post_id, 'doc_category', 0);
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

function display_doc_field_list() {
    ?>
    <div id="doc-field-list" title="Field list" style="display:none;">
    <fieldset>
        <table style="width:100%;">
            <thead>
                <tr>
                    <th><?php echo __( 'Field', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Description', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Listing', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Editing', 'your-text-domain' );?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $x = 0;
                while ($x<50) {
                    echo '<tr class="doc-field-list-'.$x.'" style="display:none;"></tr>';
                    $x += 1;
                }
                ?>
            </tbody>
        </table>
        <input type ="button" id="new-doc-field" value="+" style="width:100%; margin:3px; border-radius:5px; font-size:small;" />
    </fieldset>
    </div>
    <?php display_doc_field_dialog();?>
    <?php
}

function retrieve_doc_field_list_data($doc_id=0) {
    $args = array(
        'post_type'      => 'doc-field',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'   => 'doc_id',
                'value' => $doc_id,
            ),
        ),
    );
    $query = new WP_Query($args);
    return $query;
}

function get_doc_field_list_data() {
    // Retrieve the documents data
    $query = retrieve_doc_field_list_data($_POST['_doc_id']);
    $_array = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            $next_job = esc_attr(get_post_meta(get_the_ID(), 'next_job', true));
            $_list = array();
            $_list["field_id"] = get_the_ID();
            $_list["field_title"] = get_the_title();
            $_list["field_content"] = get_post_field('post_content', get_the_ID());
            //$_list["doc_id"] = esc_attr(get_post_meta(get_the_ID(), 'doc_id', true));
            $_list["is_listing"] = esc_attr(get_post_meta(get_the_ID(), 'is_listing', true));
            $_list["is_editing"] = esc_attr(get_post_meta(get_the_ID(), 'is_editing', true));
            array_push($_array, $_list);
        endwhile;
        wp_reset_postdata();
    }
    wp_send_json($_array);
}
add_action( 'wp_ajax_get_doc_field_list_data', 'get_doc_field_list_data' );
add_action( 'wp_ajax_nopriv_get_doc_field_list_data', 'get_doc_field_list_data' );

function display_doc_field_dialog(){
    ?>
    <div id="doc-field-dialog" title="Field dialog" style="display:none;">
    <fieldset>
        <input type="hidden" id="field-id" />
        <label for="field-title">Title:</label>
        <input type="text" id="field-title" class="text ui-widget-content ui-corner-all" />
        <label for="field-content">Content:</label>
        <input type="text" id="field-content" class="text ui-widget-content ui-corner-all" />
        <div>
            <div style="display:inline-block; width:50%;">
                <label for="is-listing">Is listing:</label>
                <input type="checkbox" id="is-listing" />
            </div>
            <div style="display:inline-block;">
                <label for="is-editing">Is editing:</label>
                <input type="checkbox" id="is-editing" />
            </div>
        </div>
    </fieldset>
    </div>
    <?php    
}

function get_doc_field_dialog_data() {
    $response = array();
    if( isset($_POST['_field_id']) ) {
        $field_id = (int)sanitize_text_field($_POST['_field_id']);
        //$doc_id = esc_attr(get_post_meta($field_id, 'doc_id', true));
        $response["field_title"] = get_the_title($action_id);
        $response["field_content"] = get_post_field('post_content', $action_id);
        $response["is_listing"] = esc_html(get_post_meta($field_id, 'is_listing', true));
        $response["is_editing"] = esc_html(get_post_meta($field_id, 'is_editing', true));
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_get_doc_field_dialog_data', 'get_doc_field_dialog_data' );
add_action( 'wp_ajax_nopriv_get_doc_field_dialog_data', 'get_doc_field_dialog_data' );

function set_doc_field_dialog_data() {
    $current_user_id = get_current_user_id();
    if( isset($_POST['_field_id']) ) {
        // Update the post into the database
        $data = array(
            'ID'         => $_POST['_field_id'],
            'post_title' => $_POST['_field_title'],
            'post_content' => $_POST['_field_content'],
            'meta_input' => array(
                'is_listing'  => $_POST['_is_listing'],
                'is_editing'  => $_POST['_is_editing'],
            )
        );
        wp_update_post( $data );
    } else {
        // Insert the post into the database
        $new_post = array(
            'post_title'    => 'New field',
            'post_content'  => 'Your post content goes here.',
            'post_status'   => 'publish',
            'post_author'   => $current_user_id,
            'post_type'     => 'field',
        );    
        $post_id = wp_insert_post($new_post);
        update_post_meta( $post_id, 'doc_id', sanitize_text_field($_POST['_doc_id']));
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_set_doc_field_dialog_data', 'set_doc_field_dialog_data' );
add_action( 'wp_ajax_nopriv_set_doc_field_dialog_data', 'set_doc_field_dialog_data' );

function del_doc_field_dialog_data() {
    // Delete the post
    $result = wp_delete_post($_POST['_field_id'], true);
    wp_send_json($result);
}
add_action( 'wp_ajax_del_doc_field_dialog_data', 'del_doc_field_dialog_data' );
add_action( 'wp_ajax_nopriv_del_doc_field_dialog_data', 'del_doc_field_dialog_data' );
/*
function get_document_list_data() {
    // Retrieve the documents data
    $query = retrieve_document_list_data($_POST['_site_id']);
    $_array = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            $post_id = (int) get_the_ID();
            $doc_url = esc_html(get_post_meta($post_id, 'doc_url', true));
            $doc_date = esc_attr(get_post_meta($post_id, 'doc_date', true));
            $_list = array();
            $_list["doc_id"] = $post_id;
            $_list["doc_title"] = (($doc_date) ? '<a href="'.$doc_url.'">'.get_the_title().'</a>' : get_the_title());
            $_list["doc_number"] = esc_html(get_post_meta($post_id, 'doc_number', true));
            $_list["doc_revision"] = esc_html(get_post_meta($post_id, 'doc_revision', true));
            $_list["doc_date"] = esc_html(wp_date( get_option('date_format'), $doc_date ));
            array_push($_array, $_list);
        endwhile;
        wp_reset_postdata();
    }
    wp_send_json($_array);
}
add_action( 'wp_ajax_get_document_list_data', 'get_document_list_data' );
add_action( 'wp_ajax_nopriv_get_document_list_data', 'get_document_list_data' );

function get_document_dialog_data() {
    $response = array();
    if( isset($_POST['_doc_id']) ) {
        $doc_id = (int)sanitize_text_field($_POST['_doc_id']);
        $site_id = esc_attr(get_post_meta($doc_id, 'site_id', true));
        $start_job = esc_attr(get_post_meta($doc_id, 'start_job', true));
        $doc_date = esc_attr(get_post_meta($doc_id, 'doc_date', true));
        $doc_category = esc_attr(get_post_meta($doc_id, 'doc_category', true));
        $doc_status = esc_attr(get_post_meta($doc_id, 'doc_status', true));
        $deleting = esc_attr(get_post_meta($doc_id, 'deleting', true));
        $response["doc_title"] = get_the_title($doc_id);
        $response["doc_number"] = esc_html(get_post_meta($doc_id, 'doc_number', true));
        $response["doc_revision"] = esc_html(get_post_meta($doc_id, 'doc_revision', true));
        $response["doc_url"] = esc_html(get_post_meta($doc_id, 'doc_url', true));
        $response["start_job"] = select_site_job_option_data($start_job, $site_id);
        $response["start_leadtime"] = esc_attr(get_post_meta($doc_id, 'start_leadtime', true));
        $response["doc_date"] = wp_date( get_option('date_format'), $doc_date );
        $response["doc_category"] = select_doc_category_option_data($doc_category);
        //$response["doc_status"] = get_post_field('post_content', $doc_status).($deleting)?'Deleting':'';
        $response["doc_status"] = get_post_field('post_content', $start_job).(($deleting>0)?'<span style="color:red;">Deleting</span>':'');
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_get_document_dialog_data', 'get_document_dialog_data' );
add_action( 'wp_ajax_nopriv_get_document_dialog_data', 'get_document_dialog_data' );

function display_doc_workflow_list() {
    ?>
    <div id="doc-workflow-list-dialog" title="Workflow list" style="display:none;">
        <table style="width:100%;">
            <thead>
                <tr>
                    <th><?php echo __( 'Todo', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Description', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Submit', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Action', 'your-text-domain' );?></th>
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
            'relation' => 'AND', // Use 'AND' for an AND relationship between conditions
            array(
                'key'     => 'submit_user',
                'compare' => 'EXISTS',
            ),
            array(
                'key'     => 'doc_id',
                'value'   => $doc_id,
                'compare' => '=',
            ),
        ),
        'orderby'        => 'meta_value',
        'meta_key'       => 'submit_time',
        'order'          => 'ASC',
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
            $submit_user = esc_attr(get_post_meta(get_the_ID(), 'submit_user', true));
            $user_data = get_userdata( $submit_user );
            $_list["submit_user"] = $user_data->display_name;
            $submit_action = esc_attr(get_post_meta(get_the_ID(), 'submit_action', true));            
            $_list["submit_action"] = get_the_title($submit_action);
            $submit_time = esc_attr(get_post_meta(get_the_ID(), 'submit_time', true));            
            $_list["submit_time"] = wp_date( get_option('date_format'), $submit_time );
            array_push($_array, $_list);
        endwhile;
        wp_reset_postdata(); // Reset post data to the main loop
    }
    wp_send_json($_array);
}
add_action( 'wp_ajax_get_doc_workflow_list_data', 'get_doc_workflow_list_data' );
add_action( 'wp_ajax_nopriv_get_doc_workflow_list_data', 'get_doc_workflow_list_data' );
*/
