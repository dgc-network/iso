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

// Register doc report post type
function register_doc_report_post_type() {
    $labels = array(
        'menu_name'     => _x('doc-report', 'admin menu', 'textdomain'),
    );
    $args = array(
        'labels'        => $labels,
        'public'        => true,
        'rewrite'       => array('slug' => 'doc-reports'),
        'supports'      => array( 'title', 'editor', 'custom-fields' ),
        'has_archive'   => true,
        //'show_in_menu'  => false, // Set this to false to hide from the admin menu
    );
    register_post_type( 'doc-report', $args );
}
add_action('init', 'register_doc_report_post_type');

// Register doc field post type
function register_doc_field_post_type() {
    $labels = array(
        'menu_name'     => _x('doc-field', 'admin menu', 'textdomain'),
    );
    $args = array(
        'labels'        => $labels,
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
    $labels = array(
        'menu_name'     => _x('doc-category', 'admin menu', 'textdomain'),
    );
    $args = array(
        'labels'        => $labels,
        'public'        => true,
        'rewrite'       => array('slug' => 'doc-categories'),
        'supports'      => array( 'title', 'editor', 'custom-fields' ),
        'has_archive'   => true,
        'show_in_menu'  => false, // Set this to false to hide from the admin menu
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
                <label for="doc-field-setting"> Field setting: </label>
                <?php display_doc_field_list(true);?>                
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

        wp_die();
    } else {
        // Handle invalid AJAX request
        echo 'Invalid AJAX request!';
        wp_die();
    }
}
add_action('wp_ajax_open_doc_dialog_and_buttons', 'open_doc_dialog_and_buttons');
add_action('wp_ajax_nopriv_open_doc_dialog_and_buttons', 'open_doc_dialog_and_buttons');

function retrieve_is_listing_field_list($doc_id) {
    $args = array(
        'post_type'      => 'doc-field',
        'posts_per_page' => -1,
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'   => 'doc_id',
                'value' => $doc_id,
            ),
            array(
                'key'   => 'is_listing',
                'value' => 1,
            ),
        ),
        'meta_key'  => 'sorting_in_doc_field',
        'orderby'   => 'meta_value', // Sort by meta value
        'order'     => 'ASC',
    );
    $query = new WP_Query($args);
    return $query;
}

function retrieve_is_editing_field_list($doc_id) {
    $args = array(
        'post_type'      => 'doc-field',
        'posts_per_page' => -1,
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'   => 'doc_id',
                'value' => $doc_id,
            ),
            array(
                'key'   => 'is_editing',
                'value' => 1,
            ),        
        ),
        'meta_key'  => 'sorting_in_doc_field',
        'orderby'   => 'meta_value', // Sort by meta value
        'order'     => 'ASC',
    );
    $query = new WP_Query($args);
    return $query;
}

function display_report_list($doc_id) {
    ?>
    <fieldset>
        <table style="width:100%;">
            <thead>
                <?php
                $query = retrieve_is_listing_field_list($doc_id);
                if ($query->have_posts()) {
                    echo '<tr>';
                    while ($query->have_posts()) : $query->the_post();
                        echo '<th>';
                        echo esc_html(get_post_field('post_content', get_the_ID()));
                        echo '</th>';
                    endwhile;
                    echo '</tr>';
                    wp_reset_postdata();
                }
                ?>
            </thead>
            <tbody>
                <?php
                $args = array(
                    'post_type'      => 'doc-report',
                    'posts_per_page' => 30,
                    'paged'          => (get_query_var('paged')) ? get_query_var('paged') : 1,
                    'meta_query'     => array(
                        'relation' => 'AND',
                        array(
                            'key'   => 'doc_id',
                            'value' => $doc_id,
                        ),
                    ),
                );
                $query = new WP_Query($args);
                if ($query->have_posts()) {
                    while ($query->have_posts()) : $query->the_post();
                        $report_id = get_the_ID();
                        echo '<tr id="edit-report-'.$report_id.'">';

                        // Reset the inner loop before using it again
                        $inner_query = retrieve_is_listing_field_list($doc_id);
                        if ($inner_query->have_posts()) {
                            while ($inner_query->have_posts()) : $inner_query->the_post();
                                $doc_field = get_the_title();
                                echo '<td>';
                                echo esc_html(get_post_meta($report_id, $doc_field, true));
                                echo '</td>';
                            endwhile;
                            wp_reset_postdata();
                        }

                        echo '</tr>';
                    endwhile;
                    wp_reset_postdata();
                }
                ?>
            </tbody>
        </table>
        <input type="button" id="new-report" value="+" style="width:100%; margin:3px; border-radius:5px; font-size:small;" />
    </fieldset>
    <?php
    echo '<div style="display:none;">';
    display_report_dialog($doc_id);
    echo '</div>';
}

function display_report_dialog($doc_id) {
    $site_id = esc_attr(get_post_meta($doc_id, 'site_id', true));
    echo '<h2>Document</h2>';
    echo '<input type="hidden" id="doc-id" value="'.$doc_id.'" />';
    //display_doc_field_list();
    echo '<fieldset>';
    echo '<div style="text-align: right" class="button">';
    echo '<span id="doc-field-setting" style="margin-left:5px;" class="dashicons dashicons-admin-generic"></span>';
    echo '</div>';
    $query = retrieve_is_editing_field_list($doc_id);
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            $key = esc_attr(get_the_title());
            echo '<label for="'.$key.'">'.esc_html(get_post_field('post_content', get_the_ID())).'</label>';
            switch (true) {
                case strpos($key, 'url'):
                    echo '<textarea id="' . $key . '" rows="3" style="width:100%;">' . $value . '</textarea>';
                    break;
/*        
                    case strpos($key, '_job'):
                        echo '<select id="' . $key . '" class="text ui-widget-content ui-corner-all">' . select_start_job_option_data($value, $site_id) . '</select>';
                        break;
            
                    case strpos($key, '_category'):
                    echo '<select id="' . $key . '" class="text ui-widget-content ui-corner-all">' . select_doc_category_option_data($value) . '</select>';
                    break;
*/        
                default:
                    echo '<input type="text" id="' . $key . '" value="' . $value . '" class="text ui-widget-content ui-corner-all" />';
                    break;
            }

        endwhile;
        wp_reset_postdata();
    }
    echo '<label for="start-job">'.__( '起始職務', 'your-text-domain' ).'</label>';
    echo '<select id="start-job" class="text ui-widget-content ui-corner-all">' . select_start_job_option_data($value, $site_id) . '</select>';
    echo '<label for="start-leadtime">'.__( '前置時間', 'your-text-domain' ).'</label>';
    echo '<input type="text" id="start-leadtime" value="86400" class="text ui-widget-content ui-corner-all" />';
    echo '<label for="doc-category">'.__( '文件類別', 'your-text-domain' ).'</label>';
    echo '<select id="doc-category" class="text ui-widget-content ui-corner-all">' . select_doc_category_option_data($value) . '</select>';
    echo '<hr>';
    echo '<input type="button" id="set-document-button" value="'.__( 'Save', 'your-text-domain' ).'" style="margin:3px;" />';
    echo '<input type="button" id="del-document-button" value="'.__( 'Delete', 'your-text-domain' ).'" style="margin:3px;" />';
    echo '</fieldset>';    
}

function display_document_dialog($doc_id) {
    $site_id = esc_attr(get_post_meta($doc_id, 'site_id', true));
    echo '<h2>Document</h2>';
    echo '<input type="hidden" id="doc-id" value="'.$doc_id.'" />';
    echo '<fieldset>';
    //echo '<div style="text-align: right" class="button">';
    //echo '<span id="doc-field-setting" style="margin-left:5px;" class="dashicons dashicons-admin-generic"></span>';
    //echo '</div>';
    $is_report = 0;
    // Get all existing meta data for the specified post ID
    $all_meta = get_post_meta($doc_id);
    // Output or manipulate the meta data as needed
    foreach ($all_meta as $key => $values) {
        if ($key!='site_id') 
        //if ($key!='todo_status') 
        foreach ($values as $value) {
            if ($key=='is_report') {
                echo '<input type="hidden" id="is_report" value="' . $value . '" />';
                $is_report = $value;
            } else if ($key=='doc_url') {
                if ($is_report==1) {
                    echo '<label id="doc-field-setting" class="button" for="doc_url">'.__( '欄位設定', 'your-text-domain' ).'</label>';
                    echo '<textarea id="doc_url" rows="3" style="width:100%;">' . $value . '</textarea>';
                    display_doc_field_list(true);
                } else {
                    echo '<label id="doc-field-setting" class="button" for="doc_url">'.__( '文件地址', 'your-text-domain' ).'</label>';
                    echo '<textarea id="doc_url" rows="3" style="width:100%;">' . $value . '</textarea>';
                    display_doc_field_list(false);    
                }
            } else {
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
    echo '<hr>';
    echo '<input type="button" id="set-document-button" value="'.__( 'Save', 'your-text-domain' ).'" style="margin:3px;" />';
    echo '<input type="button" id="del-document-button" value="'.__( 'Delete', 'your-text-domain' ).'" style="margin:3px;" />';
    echo '</fieldset>';    
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
    $result = wp_delete_post($_POST['_doc_id'], true);
    wp_send_json($result);
}
add_action( 'wp_ajax_del_document_dialog_data', 'del_document_dialog_data' );
add_action( 'wp_ajax_nopriv_del_document_dialog_data', 'del_document_dialog_data' );

function display_doc_field_list($_is_show=false) {
    if (!$_is_show) $show='display:none;'
    ?>
    <div id="doc-field-list-dialog" title="Field list" style="<?php echo $show;?>">
    <fieldset>
        <table style="width:100%;">
            <thead>
                <tr>
                    <th><?php echo __( 'Field', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Title', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Listing', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Editing', 'your-text-domain' );?></th>
                </tr>
            </thead>
            <tbody id="sortable-doc-field-list">
                <?php
                $x = 0;
                while ($x<50) {
                    echo '<tr class="field-id-array doc-field-list-'.$x.'" style="display:none;"></tr>';
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

function retrieve_doc_field_list_data_in_site($site_id=0) {
    $args = array(
        'post_type'      => 'doc-field',
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

function get_doc_field_list_data() {
    // Retrieve the documents data
    if (isset($_POST['_doc_id'])) $query = retrieve_doc_field_list_data($_POST['_doc_id']);
    if (isset($_POST['_site_id'])) $query = retrieve_doc_field_list_data_in_site($_POST['_site_id']);
    $_array = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            $_list = array();
            $_list["field_id"] = get_the_ID();
            $_list["field_title"] = get_the_title();
            $_list["field_content"] = get_post_field('post_content', get_the_ID());
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
        <label for="field-title">Field:</label>
        <input type="text" id="field-title" class="text ui-widget-content ui-corner-all" />
        <label for="field-content">Title:</label>
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
        $response["field_title"] = get_the_title($field_id);
        $response["field_content"] = get_post_field('post_content', $field_id);
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
            'ID'         => sanitize_text_field($_POST['_field_id']),
            'post_title' => sanitize_text_field($_POST['_field_title']),
            'post_content' => sanitize_text_field($_POST['_field_content']),
        );
        wp_update_post( $data );
        update_post_meta( sanitize_text_field($_POST['_field_id']), 'is_listing', sanitize_text_field($_POST['_is_listing']));
        update_post_meta( sanitize_text_field($_POST['_field_id']), 'is_editing', sanitize_text_field($_POST['_is_editing']));

    } else {
        // Insert the post into the database
        $new_post = array(
            'post_title'    => 'new_field',
            'post_content'  => 'Field Title',
            'post_status'   => 'publish',
            'post_author'   => $current_user_id,
            'post_type'     => 'doc-field',
        );    
        $post_id = wp_insert_post($new_post);
        if (isset($_POST['_site_id'])) update_post_meta( $post_id, 'site_id', sanitize_text_field($_POST['_site_id']));
        if (isset($_POST['_doc_id'])) update_post_meta( $post_id, 'doc_id', sanitize_text_field($_POST['_doc_id']));
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
function set_sorted_field_id_data() {
    if( isset($_POST['_field_id_array']) ) {
        $results = $_POST['_field_id_array'];
        foreach ($results as $index => $result) {
            // Update metadata to the post
            $meta_key   = 'sorting_in_doc_field';
            $meta_value = $index;
            update_post_meta($result, $meta_key, $meta_value);
        }        
    }
    echo json_encode( $response );
    wp_die();
}
add_action( 'wp_ajax_set_sorted_field_id_data', 'set_sorted_field_id_data' );
add_action( 'wp_ajax_nopriv_set_sorted_field_id_data', 'set_sorted_field_id_data' );
*/
/*
function set_sorted_field_id_data() {
    if (isset($_POST['_field_id_array']) && is_array($_POST['_field_id_array'])) {
        $results = $_POST['_field_id_array'];
        foreach ($results as $index => $result) {
            // Update metadata to the post
            $meta_key   = 'sorting_in_doc_field';
            $meta_value = $index;
            update_post_meta($result, $meta_key, $meta_value);
        }
        $response = array('success' => true);
    } else {
        $response = array('success' => false, 'error' => 'Invalid data format');
    }

    echo json_encode($response);
    wp_die();
}
*/
function set_sorted_field_id_data() {
    $response = array('success' => false, 'error' => 'Invalid data format');

    if (isset($_POST['_field_id_array']) && is_array($_POST['_field_id_array'])) {
        $field_id_array = array_map('absint', $_POST['_field_id_array']);
        
        foreach ($field_id_array as $index => $field_id) {
            update_post_meta($field_id, 'sorting_in_doc_field', $index);
        }

        $response = array('success' => true);
    }

    echo json_encode($response);
    wp_die();
}

add_action('wp_ajax_set_sorted_field_id_data', 'set_sorted_field_id_data');
add_action('wp_ajax_nopriv_set_sorted_field_id_data', 'set_sorted_field_id_data');
