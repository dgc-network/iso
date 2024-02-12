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
    // Migration the_title to meta doc_title
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
        //ob_start();
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
                <?php echo display_doc_field_list(false, $site_id);?>                
            </fieldset>
            </div>
        
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                    <select id="select-category"><?php echo select_doc_category_option_data($_GET['_category']);?></select>
                </div>
                <div style="text-align:right; display:flex;">
                    <input type="text" id="search-document" style="display:inline" placeholder="Search..." />
                    <span id="document-setting" style="margin-left:5px;" class="dashicons dashicons-admin-generic button"></span>
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
                        $todo_id = get_post_meta($post_id, 'todo_status', true);
                        $todo_status = ($todo_id) ? get_the_title($todo_id) : 'Draft';
                        $is_deleting = get_post_meta($post_id, 'is_deleting', true);
                        $del_status = ($is_deleting) ? '<span style="color:red;">(Deleting)</span>' : '';
                        ?>
                        <tr class="document-list-<?php echo $x;?>" id="edit-document-<?php the_ID();?>">
                            <td style="text-align:center;"><?php echo esc_html(get_post_meta($post_id, 'doc_number', true));?></td>
                            <td><?php echo esc_html(get_post_meta($post_id, 'doc_title', true));?></td>
                            <td style="text-align:center;"><?php echo esc_html(get_post_meta($post_id, 'doc_revision', true));?></td>
                            <td style="text-align:center;"><?php echo esc_html($todo_status.$del_status);?></td>
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
        //$html = ob_get_clean();
        //return $html;
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

function get_document_dialog_data() {
    // Check if the action has been set
    $result = array();
    if (isset($_POST['action']) && $_POST['action'] === 'get_document_dialog_data') {
        $doc_id = (int)sanitize_text_field($_POST['_doc_id']);
        $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
        $todo_status = get_post_meta($doc_id, 'todo_status', true);
        $doc_url = get_post_meta($doc_id, 'doc_url', true);
        if ($todo_status==-1) {
            if ($is_doc_report) {
                $result['html_contain'] = display_doc_report_list($doc_id);
            } else {
                wp_redirect($doc_url);
            }
        } else {
            $result['html_contain'] = display_document_dialog($doc_id);
        }
        //wp_die();
    } else {
        // Handle invalid AJAX request
        //echo 'Invalid AJAX request!';
        //wp_die();
        $result['html_contain'] = 'Invalid AJAX request!';
    }
    wp_send_json($result);
}
add_action('wp_ajax_get_document_dialog_data', 'get_document_dialog_data');
add_action('wp_ajax_nopriv_get_document_dialog_data', 'get_document_dialog_data');

function display_document_dialog($doc_id) {
    $site_id = get_post_meta($doc_id, 'site_id', true);
    $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
    ob_start();
    echo '<h2>Document</h2>';
    echo '<input type="hidden" id="doc-id" value="'.$doc_id.'" />';
    echo '<input type="hidden" id="is-doc-report" value="'.$is_doc_report.'" />';
    echo '<fieldset>';
    // Get all existing meta data for the specified post ID
    $all_meta = get_post_meta($doc_id);
    // Output or manipulate the meta data as needed
    foreach ($all_meta as $key => $values) {
        if ($key!='site_id')
        if ($key!='is_doc_report')
        if ($key!='todo_status')
        if ($key!='doc_date')

        foreach ($values as $value) {
            if ($key=='doc_url') {
                if ($is_doc_report==1) {
                    echo '<label id="doc-field-setting" class="button" for="doc_url">'.__( '欄位設定', 'your-text-domain' ).'</label>';
                    echo '<span id="doc-report-preview" <span class="dashicons dashicons-external button" style="margin-left:5px; vertical-align:text-top;"></span>';
                    echo '<textarea id="doc_url" rows="3" style="width:100%; display:none;">' . $value . '</textarea>';
                    echo '<div id="doc-field-list-dialog">';
                    echo display_doc_field_list($doc_id);
                    echo '</div>';
                } else {
                    echo '<label id="doc-field-setting" class="button" for="doc_url">'.__( '文件地址', 'your-text-domain' ).'</label>';
                    echo '<span id="doc-url-preview" <span class="dashicons dashicons-external button" style="margin-left:5px; vertical-align:text-top;"></span>';
                    echo '<textarea id="doc_url" rows="3" style="width:100%;">' . $value . '</textarea>';
                    echo '<div id="doc-field-list-dialog" style="display:none;">';
                    echo display_doc_field_list($doc_id);
                    echo '</div>';
                }
            } else {
                echo '<label for="'.$key.'">'.translate_custom_strings($key).'</label>';
                switch (true) {
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
    echo '<input type="button" id="save-document-button" value="'.__( 'Save', 'your-text-domain' ).'" style="margin:3px;" />';
    echo '<input type="button" id="del-document-button" value="'.__( 'Delete', 'your-text-domain' ).'" style="margin:3px;" />';
    echo '</fieldset>';
    $html = ob_get_clean();
    return $html;
}

function select_start_job_option_data($selected_job=0, $site_id=0) {
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
        // Update the Document data
        $data = array(
            'ID'         => $_POST['_doc_id'],
            'meta_input' => array(
                'doc_title'   => $_POST['_doc_title'],
                'doc_number'   => $_POST['_doc_number'],
                'doc_revision' => $_POST['_doc_revision'],
                'doc_url'      => $_POST['_doc_url'],
                'is_doc_report' => $_POST['_is_doc_report'],
                'start_job'    => $start_job,
                'start_leadtime' => $start_leadtime,
                'doc_category'  => $_POST['_doc_category'],
            )
        );
        wp_update_post( $data );
        set_next_job_and_actions($start_job, 0, $doc_id, $start_leadtime);
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
    $result = wp_delete_post($_POST['_doc_id'], true);
    wp_send_json($result);
}
add_action( 'wp_ajax_del_document_dialog_data', 'del_document_dialog_data' );
add_action( 'wp_ajax_nopriv_del_document_dialog_data', 'del_document_dialog_data' );

// doc-field
function display_doc_field_list($doc_id=false, $site_id=false) {
    ob_start();
    ?>
    <fieldset>
        <table style="width:100%;">
            <thead>
                <tr>
                    <th><?php echo __( 'Field', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Title', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Type', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Default', 'your-text-domain' );?></th>
                </tr>
            </thead>
            <tbody id="sortable-doc-field-list">
                <?php
                $x = 0;
                $query = retrieve_doc_field_list_data($doc_id, $site_id);
                if ($query->have_posts()) {
                    while ($query->have_posts()) : $query->the_post();
                        echo '<tr class="doc-field-list-'.$x.'" id="edit-doc-field-'.esc_attr(get_the_ID()).'" data-field-id="'.esc_attr(get_the_ID()).'">';
                        echo '<td style="text-align:center;">'.esc_html(get_post_meta(get_the_ID(), 'field_name', true)).'</td>';
                        echo '<td style="text-align:center;">'.esc_html(get_post_meta(get_the_ID(), 'field_title', true)).'</td>';
                        echo '<td style="text-align:center;">'.esc_html(get_post_meta(get_the_ID(), 'editing_type', true)).'</td>';
                        echo '<td style="text-align:center;">'.esc_html(get_post_meta(get_the_ID(), 'default_value', true)).'</td>';
                        echo '</tr>';
                        $x += 1;
                    endwhile;
                    wp_reset_postdata();
                }
                while ($x<50) {
                    echo '<tr class="doc-field-list-'.$x.'" style="display:none;"></tr>';
                    $x += 1;
                }
                ?>
            </tbody>
        </table>
        <input type ="button" id="new-doc-field" value="+" style="width:100%; margin:3px; border-radius:5px; font-size:small;" />
    </fieldset>
    <?php display_doc_field_dialog();?>
    <?php
    $html = ob_get_clean();
    return $html;    
}

function retrieve_is_listing_doc_field_data($doc_id=0) {
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
                'key'     => 'listing_style',
                'value'   => '',
                'compare' => '!=',
            ),
        ),
        'meta_key'  => 'sorting_key',
        'orderby'   => 'meta_value',
        'order'     => 'ASC',
    );
    $query = new WP_Query($args);
    return $query;
}

function retrieve_is_editing_doc_field_data($doc_id=0) {
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
                'key'     => 'editing_type',
                'value'   => '',
                'compare' => '!=',
            ),
        ),
        'meta_key'  => 'sorting_key',
        'orderby'   => 'meta_value',
        'order'     => 'ASC',
    );
    $query = new WP_Query($args);
    return $query;
}

function retrieve_doc_field_list_data($doc_id=false, $site_id=false) {
    $args = array(
        'post_type'      => 'doc-field',
        'posts_per_page' => -1,
        'meta_key'       => 'sorting_key',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
    );
    
    if ($doc_id) {
        $args['meta_query'][] = array(
            'key'   => 'doc_id',
            'value' => $doc_id,
        );
    }
    
    if ($site_id) {
        $args['meta_query'][] = array(
            'key'   => 'site_id',
            'value' => $site_id,
        );
    }
    
    $query = new WP_Query($args);
    return $query;
}

function get_doc_field_list_data() {
    // Retrieve the value
    if (isset($_POST['_doc_id'])) {
        $doc_id = (int) $_POST['_doc_id'];
        $query = retrieve_doc_field_list_data($doc_id);
    } elseif (isset($_POST['_site_id'])) {
        $site_id = (int) $_POST['_site_id'];
        $query = retrieve_doc_field_list_data(false, $site_id);
    }

    $_array = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            $_list = array();
            $_list["field_id"] = get_the_ID();
            $_list["field_name"] = esc_html(get_post_meta(get_the_ID(), 'field_name', true));
            $_list["field_title"] = esc_html(get_post_meta(get_the_ID(), 'field_title', true));
            $_list["editing_type"] = esc_html(get_post_meta(get_the_ID(), 'editing_type', true));
            $_list["default_value"] = esc_html(get_post_meta(get_the_ID(), 'default_value', true));
            array_push($_array, $_list);
        endwhile;
        wp_reset_postdata();
    }
    wp_send_json($_array);
}
add_action('wp_ajax_get_doc_field_list_data', 'get_doc_field_list_data');
add_action('wp_ajax_nopriv_get_doc_field_list_data', 'get_doc_field_list_data');

function display_doc_field_dialog(){
    //ob_start();
    ?>
    <div id="doc-field-dialog" title="Field dialog" style="display:none;">
    <fieldset>
        <input type="hidden" id="field-id" />
        <label for="field-name">Name:</label>
        <input type="text" id="field-name" class="text ui-widget-content ui-corner-all" />
        <label for="field-title">Title:</label>
        <input type="text" id="field-title" class="text ui-widget-content ui-corner-all" />
        <label for="listing-style">Style:</label>
        <input type="text" id="listing-style" class="text ui-widget-content ui-corner-all" />
        <label for="editing-type">Type:</label>
        <input type="text" id="editing-type" class="text ui-widget-content ui-corner-all" />
        <label for="default-value">Deafult:</label>
        <input type="text" id="default-value" class="text ui-widget-content ui-corner-all" />
    </fieldset>
    </div>
    <?php
    //$html = ob_get_clean();
    //return $html;    
}

function get_doc_field_dialog_data() {
    $response = array();
    if( isset($_POST['_field_id']) ) {
        $field_id = (int)sanitize_text_field($_POST['_field_id']);
        $response["field_name"] = esc_html(get_post_meta($field_id, 'field_name', true));
        $response["field_title"] = esc_html(get_post_meta($field_id, 'field_title', true));
        $response["listing_style"] = esc_html(get_post_meta($field_id, 'listing_style', true));
        $response["editing_type"] = esc_html(get_post_meta($field_id, 'editing_type', true));
        $response["default_value"] = esc_html(get_post_meta($field_id, 'default_value', true));
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_get_doc_field_dialog_data', 'get_doc_field_dialog_data' );
add_action( 'wp_ajax_nopriv_get_doc_field_dialog_data', 'get_doc_field_dialog_data' );

function set_doc_field_dialog_data() {
    $current_user_id = get_current_user_id();
    if( isset($_POST['_field_id']) ) {
        // Update the post into the database
        $field_id = (int)sanitize_text_field($_POST['_field_id']);
        update_post_meta( $field_id, 'field_name', sanitize_text_field($_POST['_field_name']));
        update_post_meta( $field_id, 'field_title', sanitize_text_field($_POST['_field_title']));
        update_post_meta( $field_id, 'listing_style', sanitize_text_field($_POST['_listing_style']));
        update_post_meta( $field_id, 'editing_type', sanitize_text_field($_POST['_editing_type']));
        update_post_meta( $field_id, 'default_value', sanitize_text_field($_POST['_default_value']));
    } else {
        // Insert the post into the database
        $new_post = array(
            'post_status'   => 'publish',
            'post_author'   => $current_user_id,
            'post_type'     => 'doc-field',
        );    
        $post_id = wp_insert_post($new_post);
        if (isset($_POST['_site_id'])) update_post_meta( $post_id, 'site_id', sanitize_text_field($_POST['_site_id']));
        if (isset($_POST['_doc_id'])) update_post_meta( $post_id, 'doc_id', sanitize_text_field($_POST['_doc_id']));
        update_post_meta( $post_id, 'field_name', 'new_field');
        update_post_meta( $post_id, 'field_title', 'Field title');
        update_post_meta( $post_id, 'sorting_key', -1);
        update_post_meta( $post_id, 'listing_style', 'text-align:center;');
        update_post_meta( $post_id, 'editing_type', 'text');
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_set_doc_field_dialog_data', 'set_doc_field_dialog_data' );
add_action( 'wp_ajax_nopriv_set_doc_field_dialog_data', 'set_doc_field_dialog_data' );

function del_doc_field_dialog_data() {
    $result = wp_delete_post($_POST['_field_id'], true);
    wp_send_json($result);
}
add_action( 'wp_ajax_del_doc_field_dialog_data', 'del_doc_field_dialog_data' );
add_action( 'wp_ajax_nopriv_del_doc_field_dialog_data', 'del_doc_field_dialog_data' );

function set_sorted_field_id_data() {
    $response = array('success' => false, 'error' => 'Invalid data format');

    if (isset($_POST['_field_id_array']) && is_array($_POST['_field_id_array'])) {
        $field_id_array = array_map('absint', $_POST['_field_id_array']);        
        foreach ($field_id_array as $index => $field_id) {
            update_post_meta($field_id, 'sorting_key', $index);
        }
        $response = array('success' => true);
    }

    echo json_encode($response);
    wp_die();
}
add_action('wp_ajax_set_sorted_field_id_data', 'set_sorted_field_id_data');
add_action('wp_ajax_nopriv_set_sorted_field_id_data', 'set_sorted_field_id_data');

// doc-report
function display_doc_report_list($doc_id) {
    ob_start();
    $doc_title = esc_html(get_post_meta($doc_id, 'doc_title', true));
    ?>
    <h2><?php echo $doc_title;?></h2>
    <input type="hidden" id="doc-id" value="<?php echo $doc_id;?>" />
    <fieldset>
        <div style="display:flex; justify-content:space-between; margin:5px;">
            <div>
                <select id="select-category"><?php echo select_doc_category_option_data($_GET['_category']);?></select>
            </div>
            <div style="text-align:right; display:flex;">
                <input type="text" id="search-document" style="display:inline" placeholder="Search..." />
                <span id="doc-report-setting" style="margin-left:5px;" class="dashicons dashicons-admin-generic button"></span>
            </div>
        </div>

        <table style="width:100%;">
            <thead>
                <?php
                $query = retrieve_is_listing_doc_field_data($doc_id);
                if ($query->have_posts()) {
                    echo '<tr>';
                    while ($query->have_posts()) : $query->the_post();
                        echo '<th>';
                        echo esc_html(get_post_meta(get_the_ID(), 'field_title', true));
                        echo '</th>';
                    endwhile;
                    echo '</tr>';
                    wp_reset_postdata();
                }
                ?>
            </thead>
            <tbody>
                <?php
                $query = retrieve_doc_report_list_data($doc_id);
                if ($query->have_posts()) {
                    while ($query->have_posts()) : $query->the_post();
                        $report_id = get_the_ID();
                        echo '<tr id="edit-doc-report-'.$report_id.'">';                
                        // Reset the inner loop before using it again
                        $inner_query = retrieve_is_listing_doc_field_data($doc_id);                
                        if ($inner_query->have_posts()) {
                            while ($inner_query->have_posts()) : $inner_query->the_post();
                                $field_name = get_post_meta(get_the_ID(), 'field_name', true);
                                $listing_style = get_post_meta(get_the_ID(), 'listing_style', true);
                                echo '<td style="'.$listing_style.'">';
                                echo esc_html(get_post_meta($report_id, $field_name, true));
                                echo '</td>';
                            endwhile;                
                            // Reset only the inner loop's data
                            wp_reset_postdata();
                        }                
                        echo '</tr>';
                    endwhile;                
                    // Reset the main query's data
                    wp_reset_postdata();
                }
                ?>
            </tbody>
        </table>
        <input type="button" id="new-doc-report" value="+" style="width:100%; margin:3px; border-radius:5px; font-size:small;" />
    </fieldset>
    <?php
    $html = ob_get_clean();
    return $html;
}

function display_doc_report_dialog($report_id) {
    $doc_id = get_post_meta($report_id, 'doc_id', true);
    $doc_title = esc_html(get_post_meta($doc_id, 'doc_title', true));
    $site_id = get_post_meta($doc_id, 'site_id', true);
    ob_start();
    ?>
    <h2><?php echo $doc_title;?></h2>
    <input type="hidden" id="report-id" value="<?php echo $report_id;?>" />
    <input type="hidden" id="doc-id" value="<?php echo $doc_id;?>" />
    <fieldset>
    <div style="text-align: right">
    <span id="doc-field-setting" style="margin-left:5px;" class="dashicons dashicons-admin-generic button"></span>
    </div>
    <?php
    $query = retrieve_is_editing_doc_field_data($doc_id);
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            $field_name = get_post_meta(get_the_ID(), 'field_name', true);
            $field_title = get_post_meta(get_the_ID(), 'field_title', true);
            $field_type = get_post_meta(get_the_ID(), 'editing_type', true);
            $field_value = get_post_meta($report_id, $field_name, true);
            switch (true) {
                case ($field_type=='textarea'):
                    ?>
                    <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label>
                    <textarea id="<?php echo esc_attr($field_name);?>" rows="3" style="width:100%;"><?php echo esc_html($field_value);?></textarea>
                    <?php
                    break;

                case ($field_type=='checkbox'):
                    $is_checked = ($field_value==1) ? 'checked' : '';
                    ?>
                    <input type="checkbox" id="<?php echo esc_attr($field_name);?>" <?php echo $is_checked;?> />
                    <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label>
                    <?php
                    break;

                default:
                    ?>
                    <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label>
                    <input type="text" id="<?php echo esc_attr($field_name);?>" value="<?php echo esc_html($field_value);?>" class="text ui-widget-content ui-corner-all" />
                    <?php
                    break;
            }

        endwhile;
        wp_reset_postdata();
    }
    ?>
        <label for="start-job"><?php echo __( '起始職務', 'your-text-domain' );?></label>
        <select id="start-job" class="text ui-widget-content ui-corner-all"><?php echo select_start_job_option_data($value, $site_id);?></select>
        <label for="start-leadtime"><?php echo __( '前置時間', 'your-text-domain' );?></label>
        <input type="text" id="start-leadtime" value="86400" class="text ui-widget-content ui-corner-all" />
        <label for="doc-category"><?php echo __( '文件類別', 'your-text-domain' );?></label>
        <select id="doc-category" class="text ui-widget-content ui-corner-all"><?php echo select_doc_category_option_data($value);?></select>
        <hr>
        <input type="button" id="save-doc-report-button" value="<?php echo __( 'Save', 'your-text-domain' );?>" style="margin:3px;" />
        <input type="button" id="del-doc-report-button" value="<?php echo __( 'Delete', 'your-text-domain' );?>" style="margin:3px;" />
    </fieldset>
    <?php
    $html = ob_get_clean();
    return $html;
}

function set_doc_report_dialog_data() {
    $current_user_id = get_current_user_id();
    if( isset($_POST['_report_id']) ) {
        $report_id = (int) sanitize_text_field($_POST['_report_id']);
        $start_job = sanitize_text_field($_POST['_start_job']);
        $start_leadtime = sanitize_text_field($_POST['_start_leadtime']);
        // Update the Document data
        $query = retrieve_doc_field_list_data($_POST['_doc_id'], $_POST['_site_id']);
        if ($query->have_posts()) {
            while ($query->have_posts()) : $query->the_post();
                $field_name = get_post_meta(get_the_ID(), 'field_name', true);
                $field_value = sanitize_text_field($_POST[$field_name]);
                update_post_meta( $report_id, $field_name, $field_value);
            endwhile;
            wp_reset_postdata();
        }
        set_next_job_and_actions($start_job, 0, $report_id, $start_leadtime);

    } else {
        // Insert the post into the database
        $new_post = array(
            'post_status'   => 'publish',
            'post_author'   => $current_user_id,
            'post_type'     => 'doc-report',
        );    
        $post_id = wp_insert_post($new_post);
        update_post_meta( $post_id, 'doc_id', sanitize_text_field($_POST['_doc_id']));

        $query = retrieve_doc_field_list_data($_POST['_doc_id'], $_POST['_site_id']);
        if ($query->have_posts()) {
            while ($query->have_posts()) : $query->the_post();
                $field_name = get_post_meta(get_the_ID(), 'field_name', true);
                $default_value = get_post_meta(get_the_ID(), 'default_value', true);
                update_post_meta( $post_id, $field_name, $default_value);
            endwhile;
            wp_reset_postdata();
        }
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_set_doc_report_dialog_data', 'set_doc_report_dialog_data' );
add_action( 'wp_ajax_nopriv_set_doc_report_dialog_data', 'set_doc_report_dialog_data' );

function retrieve_doc_report_list_data($doc_id=0) {

    $args = array(
        'post_type'      => 'doc-report',
        'posts_per_page' => 30,
        'paged'          => (get_query_var('paged')) ? get_query_var('paged') : 1,
    );
    
    if ($doc_id) {
        $args['meta_query'][] = array(
            'key'   => 'doc_id',
            'value' => $doc_id,
        );
    }
    
    if ($site_id) {
        $args['meta_query'][] = array(
            'key'   => 'site_id',
            'value' => $site_id,
        );
    }
    
    $query = new WP_Query($args);
    return $query;
}

function get_doc_report_list_data() {
    $result = array();
    if (isset($_POST['action']) && $_POST['action'] === 'get_doc_report_list_data') {
        $doc_id = (int)sanitize_text_field($_POST['_doc_id']);
        $result['html_contain'] = display_doc_report_list($doc_id);
    } else {
        $result['html_contain'] = 'Invalid AJAX request!';
    }
    wp_send_json($result);
}
add_action( 'wp_ajax_get_doc_report_list_data', 'get_doc_report_list_data' );
add_action( 'wp_ajax_nopriv_get_doc_report_list_data', 'get_doc_report_list_data' );

function get_doc_report_dialog_data() {
    $result = array();
    if (isset($_POST['action']) && $_POST['action'] === 'get_doc_report_dialog_data') {
        $report_id = (int)sanitize_text_field($_POST['_report_id']);
        $doc_id = get_post_meta($report_id, 'doc_id', true);
        $result['html_contain'] = display_doc_report_dialog($report_id);

        if (isset($doc_id)) $query = retrieve_doc_field_list_data($doc_id);    
        $_array = array();
        if ($query->have_posts()) {
            while ($query->have_posts()) : $query->the_post();
                $_list = array();
                $field_name = get_post_meta(get_the_ID(), 'field_name', true);
                $_list["field_name"] = esc_html($field_name);
                array_push($_array, $_list);
            endwhile;
            wp_reset_postdata();
        }    
        $result['doc_fields'] = $_array;
    } else {
        $result['html_contain'] = 'Invalid AJAX request!';
    }
    wp_send_json($result);
}
add_action('wp_ajax_get_doc_report_dialog_data', 'get_doc_report_dialog_data');
add_action('wp_ajax_nopriv_get_doc_report_dialog_data', 'get_doc_report_dialog_data');

