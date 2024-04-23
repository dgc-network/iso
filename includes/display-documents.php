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
        'rewrite'       => array( 'slug' => 'documents' ),
        'show_in_menu'  => false,
    );
    register_post_type( 'document', $args );
}
add_action('init', 'register_document_post_type');

function add_document_settings_metabox() {
    add_meta_box(
        'document_settings_id',
        'Document Settings',
        'document_settings_content',
        'document',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_document_settings_metabox');

function document_settings_content($post) {
    //wp_nonce_field('site_settings_nonce', 'site_settings_nonce');
    $doc_title = esc_attr(get_post_meta($post->ID, 'doc_title', true));
    ?>
    <label for="doc_title"> doc_title: </label>
    <input type="text" id="doc_title" name="doc_title" value="<?php echo $doc_title;?>" style="width:100%" >
    <?php
}

// Register doc report post type
function register_doc_report_post_type() {
    $labels = array(
        'menu_name'     => _x('doc-report', 'admin menu', 'textdomain'),
    );
    $args = array(
        'labels'        => $labels,
        'public'        => true,
        'rewrite'       => array('slug' => 'doc-reports'),
        'supports'      => array('title', 'editor', 'custom-fields'),
        'has_archive'   => true,
        'show_in_menu'  => false,
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
        'supports'      => array('title', 'editor', 'custom-fields'),
        'has_archive'   => true,
        'show_in_menu'  => false,
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
        'supports'      => array('title', 'editor', 'custom-fields'),
        'has_archive'   => true,
        //'show_in_menu'  => false,
    );
    register_post_type( 'doc-category', $args );
}
add_action('init', 'register_doc_category_post_type');

// Shortcode to display documents
function display_documents_shortcode() {
    // Migrate meta key site_id from 8699 to 8698 in document post (2024-4-18)
    if( isset($_GET['_site_id_migration']) ) {
        // Query documents with the current meta key 'site_id' set to 8699
        $args = array(
            'post_type'      => 'document',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => 'site_id',
                    'value'   => '8699',
                    'compare' => '=',
                ),
            ),
        );
        $query = new WP_Query($args);
        
        // Loop through each document post and update its meta value
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                // Update the meta value from 8699 to 8698
                update_post_meta(get_the_ID(), 'site_id', '8698', '8699');
            }
            // Reset post data
            wp_reset_postdata();
        }
    }

    // Migrate meta key doc_url to doc_frame in document (2024-3-16)
    if( isset($_GET['_doc_frame_migration']) ) {
        $args = array(
            'post_type'      => 'document',
            'posts_per_page' => -1,
        );
        $query = new WP_Query($args);
        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post();
                $doc_frame = get_post_meta(get_the_ID(), 'doc_url', true);
                update_post_meta(get_the_ID(), 'doc_frame', $doc_frame);
                endwhile;
            wp_reset_postdata();
        endif;    
    }

    // Migrate meta key editing_type to field_type in doc-field (2024-3-15)
    if( isset($_GET['_field_type_migration']) ) {
        $args = array(
            'post_type'      => 'doc-field',
            'posts_per_page' => -1,
        );
        $query = new WP_Query($args);
        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post();
                $field_type = get_post_meta(get_the_ID(), 'editing_type', true);
                update_post_meta(get_the_ID(), 'field_type', $field_type);
                endwhile;
            wp_reset_postdata();
        endif;    
    }

    // Migrate the_title to meta doc_title in document (2024-1-15)
    if( isset($_GET['_doc_title_migration']) ) {
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
        $output = '';
    
        // Get shared document if shared doc ID is set
        if (isset($_GET['_get_shared_doc_id'])) {
            $doc_id = sanitize_text_field($_GET['_get_shared_doc_id']);
            get_shared_document($doc_id);
        }
    
        // Display document details if document ID is set
        if (isset($_GET['_id'])) {
            $doc_id = sanitize_text_field($_GET['_id']);
            $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
            $output .= '<div class="ui-widget" id="result-container">';
            if ($is_doc_report) {
                $output .= display_doc_report_list($doc_id);
            } else {
                $output .= display_doc_frame_contain($doc_id);
            }
            $output .= '</div>';
        }
    
        // Display ISO document statement if initial ID is set
        if (isset($_GET['_initial'])) {
            $doc_id = sanitize_text_field($_GET['_initial']);
            $output .= '<div class="ui-widget" id="result-container">';
            $output .= display_iso_document_statement($doc_id);
            $output .= '</div>';
        }
    
        // Display document list if no specific document IDs are set
        if (!isset($_GET['_id']) && !isset($_GET['_initial'])) {
            $output .= display_document_list();
        }
    
        echo $output;
    } else {
        user_did_not_login_yet();
    }
}
add_shortcode('display-documents', 'display_documents_shortcode');

function display_document_list() {
    if (isset($_GET['_is_admin'])) {
        echo '<input type="hidden" id="is-admin" value="1" />';
    }
    $current_user_id = get_current_user_id();
    $site_id = get_user_meta($current_user_id, 'site_id', true);
    $image_url = get_post_meta($site_id, 'image_url', true);
    ?>
    <div class="ui-widget" id="result-container">
    <img src="<?php echo esc_attr($image_url)?>" style="object-fit:cover; width:30px; height:30px; margin-left:5px;" />
    <h2 style="display:inline;"><?php echo __( '文件總覽', 'your-text-domain' );?></h2>
    <fieldset>
        <div id="document-setting-dialog" title="Document setting" style="display:none">
        <fieldset>
            <input type="hidden" id="site-id" value="<?php echo $site_id;?>" />
            <label for="site-title"> Site: </label>
            <input type="text" id="site-title" value="<?php echo get_the_title($site_id);?>" class="text ui-widget-content ui-corner-all" disabled />
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
                    <th><?php echo __( '文件名稱', 'your-text-domain' );?></th>
                    <th><?php echo __( '文件版本', 'your-text-domain' );?></th>
                    <th><?php echo __( '待辦狀態', 'your-text-domain' );?></th>
                </tr>
            </thead>
            <tbody>
            <?php
            // Define the custom pagination parameters
            $posts_per_page = get_option('operation_row_counts');
            $current_page = max(1, get_query_var('paged')); // Get the current page number
            $query = retrieve_document_data($current_page);
            $total_posts = $query->found_posts;
            $total_pages = ceil($total_posts / $posts_per_page); // Calculate the total number of pages

            if ($query->have_posts()) :
                while ($query->have_posts()) : $query->the_post();
                    $doc_id = (int) get_the_ID();
                    $doc_number = get_post_meta($doc_id, 'doc_number', true);
                    $doc_title = get_post_meta($doc_id, 'doc_title', true);
                    $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
                    if ($is_doc_report==1) $doc_title = '*'.$doc_title;
                    $doc_revision = get_post_meta($doc_id, 'doc_revision', true);
                    $todo_id = get_post_meta($doc_id, 'todo_status', true);
                    $todo_status = ($todo_id) ? get_the_title($todo_id) : 'Draft';
                    $todo_status = ($todo_id==-1) ? '文件發行' : $todo_status;
                    $todo_status = ($todo_id==-2) ? '文件廢止' : $todo_status;
                    ?>
                    <tr id="edit-document-<?php echo $doc_id;?>">
                        <td style="text-align:center;"><?php echo esc_html($doc_number);?></td>
                        <td><?php echo esc_html($doc_title);?></td>
                        <td style="text-align:center;"><?php echo esc_html($doc_revision);?></td>
                        <td style="text-align:center;"><?php echo esc_html($todo_status);?></td>
                    </tr>
                    <?php
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
            </tbody>
        </table>
        <div id="new-document" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
        <?php
            // Display pagination links
            echo '<div class="pagination">';
            if ($current_page > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($current_page - 1)) . '"> < </a></span>';
            echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $current_page, $total_pages) . '</span>';
            if ($current_page < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($current_page + 1)) . '"> > </a></span>';
            echo '</div>';
        ?>
    </fieldset>
    </div>
    <?php
}

function retrieve_document_data($current_page = 1) {
    // Define the custom pagination parameters
    $posts_per_page = get_option('operation_row_counts');
    // Calculate the offset to retrieve the posts for the current page
    $offset = ($current_page - 1) * $posts_per_page;

    $current_user_id = get_current_user_id();
    $site_id = get_user_meta($current_user_id, 'site_id', true);
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
        'posts_per_page' => $posts_per_page,
        'paged'          => $current_page,
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
        'orderby'        => 'meta_value',
        'meta_key'       => 'doc_number',
        'order'          => 'ASC',
    );

    $query = new WP_Query($args);
    return $query;
}

function display_document_dialog($doc_id=false) {
    if ($doc_id) {
        $doc_number = get_post_meta($doc_id, 'doc_number', true);
        $doc_title = get_post_meta($doc_id, 'doc_title', true);
        $doc_revision = get_post_meta($doc_id, 'doc_revision', true);
        $doc_category = get_post_meta($doc_id, 'doc_category', true);
        $start_job = get_post_meta($doc_id, 'start_job', true);
        $doc_frame = get_post_meta($doc_id, 'doc_frame', true);
        $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
        $doc_report_start_setting = get_post_meta($doc_id, 'doc_report_start_setting', true);
        $doc_report_period_time = get_post_meta($doc_id, 'doc_report_period_time', true);
        $site_id = get_post_meta($doc_id, 'site_id', true);
        $image_url = get_post_meta($site_id, 'image_url', true);

        ob_start();
        ?>
        <div style="display:flex; justify-content:space-between; margin:5px;">
            <div>
                <img src="<?php echo esc_attr($image_url)?>" style="object-fit:cover; width:30px; height:30px; margin-left:5px;" />
                <h2 style="display:inline;"><?php echo esc_html($doc_title);?></h2>
            </div>
            <div style="text-align:right; display:flex;">

            </div>
        </div>
        <input type="hidden" id="doc-id" value="<?php echo esc_attr($doc_id);?>" />
        <fieldset>
        <div style="display:flex; justify-content:space-between; margin:5px;">
            <div>
                <label for="doc-number"><?php echo __( '文件編號', 'your-text-domain' );?></label>
            </div>
            <div style="text-align:right; display:flex;">
                <span id="reset-document-<?php echo esc_attr($doc_id);?>" class="dashicons dashicons-trash button"></span>
            </div>
        </div>
        <input type="text" id="doc-number" value="<?php echo esc_html($doc_number);?>" class="text ui-widget-content ui-corner-all" />
        <label for="doc-title"><?php echo __( '文件名稱', 'your-text-domain' );?></label>
        <input type="text" id="doc-title" value="<?php echo esc_html($doc_title);?>" class="text ui-widget-content ui-corner-all" />
        <label for="doc-revision"><?php echo __( '文件版本', 'your-text-domain' );?></label>
        <input type="text" id="doc-revision" value="<?php echo esc_html($doc_revision);?>" class="text ui-widget-content ui-corner-all" />
        <label for="doc-category"><?php echo __( '文件類別', 'your-text-domain' );?></label><br>
        <select id="doc-category" class="text ui-widget-content ui-corner-all"><?php echo select_doc_category_option_data($doc_category);?></select>
        <input type="hidden" id="is-doc-report" value="<?php echo $is_doc_report;?>" />
        <div id="doc-frame-div" style="display:none;">
            <label id="doc-frame-label" class="button" for="doc-frame"><?php echo __( '文件地址', 'your-text-domain' );?></label>
            <span id="doc-frame-preview" class="dashicons dashicons-external button" style="margin-left:5px; vertical-align:text-top;"></span>
            <textarea id="doc-frame" rows="3" style="width:100%;"><?php echo $doc_frame;?></textarea>
            <label for="start-job"><?php echo __( '本文件的起始職務', 'your-text-domain' );?></label><br>
        </div>
        <div id="doc-report-div" style="display:none;">
            <label id="doc-field-label" class="button" for="doc-field"><?php echo __( '欄位設定', 'your-text-domain' );?></label>
            <span id="doc-report-preview" class="dashicons dashicons-external button" style="margin-left:5px; vertical-align:text-top;"></span>
            <?php echo display_doc_field_list($doc_id);?>
            <label for="start-job"><?php echo __( '表單上的起始職務', 'your-text-domain' );?></label><br>
        </div>
        <select id="start-job" class="text ui-widget-content ui-corner-all"><?php echo select_site_job_option_data($start_job);?></select>
        <div id="doc-report-div1" style="display:none;">            
            <label for="doc-report-start-setting"><?php echo __( '循環表單啟動設定', 'your-text-domain' );?></label><br>
            <select id="doc-report-start-setting"><?php echo select_doc_report_start_setting_option($doc_report_start_setting);?></select>
            <div id="doc-report-start-setting-div" style="display:inline;">
                <input type="number" id="doc-report-period-time" value="<?php echo $doc_report_period_time;?>" style="width:50px;" />
                <label id="doc-report-period-time-label2"><?php echo __( '', 'your-text-domain' );?></label>
                <label id="doc-report-period-time-label3"><?php echo __( '', 'your-text-domain' );?></label><br>
            </div>
        </div>
        <hr>
        <input type="button" id="save-document-button" value="<?php echo __( 'Save', 'your-text-domain' );?>" style="margin:3px;" />
        <input type="button" id="del-document-button" value="<?php echo __( 'Delete', 'your-text-domain' );?>" style="margin:3px;" />
        </fieldset>
        <?php
        $html = ob_get_clean();
        return $html;
    }
}

function select_doc_report_start_setting_option($selected_option=0) {
    $options = '<option value="0">'.__( 'None', 'your-text-domain' ).'</option>';
    $selected = ($selected_option == "1") ? 'selected' : '';
    $options .= '<option value="1" '.$selected.' />' . __( '每年', 'your-text-domain' ) . '</option>';
    $selected = ($selected_option == "2") ? 'selected' : '';
    $options .= '<option value="2" '.$selected.' />' . __( '每月', 'your-text-domain' ) . '</option>';
    $selected = ($selected_option == "3") ? 'selected' : '';
    $options .= '<option value="3" '.$selected.' />' . __( '每週', 'your-text-domain' ) . '</option>';
    $selected = ($selected_option == "4") ? 'selected' : '';
    $options .= '<option value="4" '.$selected.' />' . __( '每日', 'your-text-domain' ) . '</option>';
    return $options;
}

function get_document_dialog_data() {
    $result = array();
    if (isset($_POST['_doc_id'])) {
        $doc_id = sanitize_text_field($_POST['_doc_id']);
        $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
        $doc_report_start_setting = get_post_meta($doc_id, 'doc_report_start_setting', true);
        $todo_status = get_post_meta($doc_id, 'todo_status', true);
        if ($todo_status<1) {
            if ($todo_status==-1) {
                if ($is_doc_report) {
                    $result['html_contain'] = display_doc_report_list($doc_id);
                } else {
                    $result['html_contain'] = display_doc_frame_contain($doc_id);
                }
            } else {
                $result['html_contain'] = display_document_dialog($doc_id);
                $result['is_doc_report'] = $is_doc_report;
                $result['doc_report_start_setting'] = $doc_report_start_setting;
            }
        } else {
            if (isset($_POST['_is_admin'])) {
                $is_admin = sanitize_text_field($_POST['_is_admin']);
                if (current_user_can('administrator') && $is_admin=="1") {
                    $result['html_contain'] = display_document_dialog($doc_id);
                }
            }        
        }

    } else {
        $result['html_contain'] = 'Invalid AJAX request!';
    }
    wp_send_json($result);
}
add_action('wp_ajax_get_document_dialog_data', 'get_document_dialog_data');
add_action('wp_ajax_nopriv_get_document_dialog_data', 'get_document_dialog_data');

function set_document_dialog_data() {
    if( isset($_POST['_doc_id']) ) {
        // Update the Document data
        $doc_id = sanitize_text_field($_POST['_doc_id']);
        update_post_meta( $doc_id, 'doc_number', sanitize_text_field($_POST['_doc_number']));
        update_post_meta( $doc_id, 'doc_title', sanitize_text_field($_POST['_doc_title']));
        update_post_meta( $doc_id, 'doc_revision', sanitize_text_field($_POST['_doc_revision']));
        update_post_meta( $doc_id, 'doc_category', sanitize_text_field($_POST['_doc_category']));
        update_post_meta( $doc_id, 'start_job', sanitize_text_field($_POST['_start_job']));
        update_post_meta( $doc_id, 'doc_frame', $_POST['_doc_frame']);
        update_post_meta( $doc_id, 'is_doc_report', sanitize_text_field($_POST['_is_doc_report']));
        update_post_meta( $doc_id, 'doc_report_start_setting', sanitize_text_field($_POST['_doc_report_start_setting']));
        update_post_meta( $doc_id, 'doc_report_period_time', sanitize_text_field($_POST['_doc_report_period_time']));
    } else {
        $current_user_id = get_current_user_id();
        $site_id = get_user_meta($current_user_id, 'site_id', true);
        $new_post = array(
            'post_title'    => 'No title',
            'post_content'  => 'Your post content goes here.',
            'post_status'   => 'publish',
            'post_author'   => $current_user_id,
            'post_type'     => 'document',
        );    
        $post_id = wp_insert_post($new_post);
        update_post_meta( $post_id, 'site_id', $site_id);
        update_post_meta( $post_id, 'doc_number', '-');
        update_post_meta( $post_id, 'doc_revision', 'A');
        update_post_meta( $post_id, 'doc_report_period_time', 1);
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_set_document_dialog_data', 'set_document_dialog_data' );
add_action( 'wp_ajax_nopriv_set_document_dialog_data', 'set_document_dialog_data' );

function del_document_dialog_data() {
    $response = wp_delete_post($_POST['_doc_id'], true);
    wp_send_json($response);
}
add_action( 'wp_ajax_del_document_dialog_data', 'del_document_dialog_data' );
add_action( 'wp_ajax_nopriv_del_document_dialog_data', 'del_document_dialog_data' );

// document additional stuffs
function reset_document_todo_status() {
    $response = array();
    if( isset($_POST['_doc_id']) ) {
        $doc_id = sanitize_text_field($_POST['_doc_id']);
        delete_post_meta($doc_id, 'todo_status');
        delete_post_meta($doc_id, 'due_date');
        delete_post_meta($doc_id, 'start_job');
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_reset_document_todo_status', 'reset_document_todo_status' );
add_action( 'wp_ajax_nopriv_reset_document_todo_status', 'reset_document_todo_status' );

function count_doc_category($doc_category){
    $current_user_id = get_current_user_id();
    $site_id = get_user_meta($current_user_id, 'site_id', true);

    $args = array(
        'post_type'      => 'document',
        'posts_per_page' => -1,
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'     => 'doc_category',
                'value'   => $doc_category,
                'compare' => '=',
            ),
            array(
                'key'     => 'site_id',
                'value'   => $site_id,
                'compare' => '=',
            ),
        ),
    );    
    $query = new WP_Query($args);    
    $count = $query->found_posts;
    return $count;
}

function display_iso_document_statement($doc_id){
    $doc_title = get_post_meta($doc_id, 'doc_title', true);
    $doc_number = get_post_meta($doc_id, 'doc_number', true);
    $doc_revision = get_post_meta($doc_id, 'doc_revision', true);
    //$doc_site = get_post_meta($doc_id, 'site_id', true);
    $category_id = get_post_meta($doc_id, 'doc_category', true);
    $doc_category = get_the_title( $category_id );
    $count_category = count_doc_category($category_id);
    //$count_category = count_doc_category($category_id, $doc_site);
    $current_user_id = get_current_user_id();
    $site_id = get_user_meta($current_user_id, 'site_id', true);
    $image_url = get_post_meta($site_id, 'image_url', true);
    ob_start();
    ?>    
    <div style="display:flex; justify-content:space-between; margin:5px;">
        <div>
            <img src="<?php echo esc_attr($image_url)?>" style="object-fit:cover; width:30px; height:30px; margin-left:5px;" />
            <span><?php echo esc_html($doc_number);?></span>
            <h2 style="display:inline;"><?php echo esc_html($doc_title);?></h2>
            <span><?php echo esc_html($doc_revision);?></span>            
        </div>
    </div>

    <input type="hidden" id="doc-id" value="<?php echo esc_attr($doc_id);?>" />
    <input type="hidden" id="doc-category" value="<?php echo esc_attr($doc_category);?>" />
    <input type="hidden" id="doc-category-id" value="<?php echo esc_attr($category_id);?>" />
    <input type="hidden" id="doc-site-id" value="<?php echo esc_attr($doc_site);?>" />
    <input type="hidden" id="count-category" value="<?php echo esc_attr($count_category);?>" />
    <input type="hidden" id="site-id" value="<?php echo esc_attr($site_id);?>" />

    <fieldset>
        <label for="site-title"><?php echo __( '單位組織名稱(Site)', 'your-text-domain' );?></label>
        <input type="text" id="site-title" value="<?php echo get_the_title($site_id);?>" class="text ui-widget-content ui-corner-all" />
        <div id="site-hint" style="display:none; color:#999;"></div>

        <?php
        $args = array(
            'post_type'      => 'doc-report',
            'posts_per_page' => -1,
            //'paged'          => (get_query_var('paged')) ? get_query_var('paged') : 1,
            'meta_query'     => array(
                array(
                    'key'     => 'doc_id',
                    'value'   => $doc_id,
                    'compare' => '='
                ),
            ),
            'orderby'    => 'meta_value',
            'meta_key'   => 'index',
            'order'      => 'ASC',
        );
        $query = new WP_Query($args);
                
        if ($query->have_posts()) {
            while ($query->have_posts()) : $query->the_post();
                $report_id = get_the_ID();
                $index = get_post_meta($report_id, 'index', true);
                $description = get_post_meta($report_id, 'description', true);
                $is_checkbox = get_post_meta($report_id, 'is_checkbox', true);
                $is_url = get_post_meta($report_id, 'is_url', true);
                $is_bold = get_post_meta($report_id, 'is_bold', true);
                if ($is_checkbox==1) echo '<input type="checkbox" id="'.$index.'" checked /> 適用';
                if ($is_url) {
                    echo '<span class="is-url">：<a href="'.$is_url.'">'.$description.'</a></span><br>';
                } else {
                    if ($is_bold==1) echo '<b>';
                    echo $description;
                    if ($is_bold==1) echo '</b>';
                    echo '<br>';
                }
            endwhile;                
            wp_reset_postdata();
        }
        ?>
    </fieldset>
    <button id="initial-next-step" class="button" style="margin:5px;"><?php echo __( '下ㄧ步(Next)', 'your-text-domain' );?></button>
    <?php
    $html = ob_get_clean();
    return $html;
}

function set_new_site_by_title() {
    $response = array('success' => false, 'error' => 'Invalid data format');
    if (isset($_POST['_new_site_title'])) {
        // Sanitize input values
        $new_site_title = sanitize_text_field($_POST['_new_site_title']);
        
        // Check if a site with the same title already exists
        $existing_site = get_page_by_title($new_site_title, OBJECT, 'site');
        
        if ($existing_site) {
            // A site with the same title already exists
            $response['error'] = 'A site with the same title already exists.';
        } else {
            // Insert the new site
            $current_user_id = get_current_user_id();
            $new_site_args = array(
                'post_title'    => $new_site_title,
                'post_status'   => 'publish',
                'post_author'   => $current_user_id,
                'post_type'     => 'site',
            );
            $new_site_id = wp_insert_post($new_site_args);
            
            if (is_wp_error($new_site_id)) {
                // Error occurred while inserting the site
                $response['error'] = $new_site_id->get_error_message();
            } else {
                // Successfully created a new site
                $response['new_site_id'] = $new_site_id;
                $response['success'] = 'Completed to create a new site';
            }
        }
    }
    wp_send_json($response);
}
add_action('wp_ajax_set_new_site_by_title', 'set_new_site_by_title');
add_action('wp_ajax_nopriv_set_new_site_by_title', 'set_new_site_by_title');

function set_initial_iso_document() {
    $response = array('success' => false, 'error' => 'Invalid data format');

    if (isset($_POST['_doc_category_id']) && isset($_POST['_doc_site_id'])) {
        $doc_category = sanitize_text_field($_POST['_doc_category_id']);
        $site_id = sanitize_text_field($_POST['_doc_site_id']);
        // Retrieve documents based on doc_category_id and doc_site_id
        $args = array(
            'post_type'      => 'document',
            'posts_per_page' => -1,
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'     => 'doc_category',
                    'value'   => $doc_category,
                    'compare' => '=',
                ),
                array(
                    'key'     => 'site_id',
                    'value'   => $site_id,
                    'compare' => '=',
                ),
            ),
        );
        
        $query = new WP_Query($args);
        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post();
                get_shared_document(get_the_ID());
            endwhile;
            wp_reset_postdata();
            $response = array('success' => true);
        endif;
    }
    wp_send_json($response);
}
add_action('wp_ajax_set_initial_iso_document', 'set_initial_iso_document');
add_action('wp_ajax_nopriv_set_initial_iso_document', 'set_initial_iso_document');

function get_shared_document($doc_id){
    $current_user_id = get_current_user_id();
    $site_id = get_user_meta($current_user_id, 'site_id', true);
    // Insert the post into the database
    $new_post = array(
        'post_title'    => 'No title',
        'post_content'  => 'Your post content goes here.',
        'post_status'   => 'publish',
        'post_author'   => $current_user_id,
        'post_type'     => 'document',
    );    
    $post_id = wp_insert_post($new_post);

    $doc_title = get_post_meta($doc_id, 'doc_title', true);
    $doc_number = get_post_meta($doc_id, 'doc_number', true);
    $doc_revision = get_post_meta($doc_id, 'doc_revision', true);
    $doc_category = get_post_meta($doc_id, 'doc_category', true);
    $doc_frame = get_post_meta($doc_id, 'doc_frame', true);
    $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
    update_post_meta( $post_id, 'site_id', $site_id);
    update_post_meta( $post_id, 'doc_title', $doc_title);
    update_post_meta( $post_id, 'doc_number', $doc_number);
    update_post_meta( $post_id, 'doc_revision', $doc_revision);
    update_post_meta( $post_id, 'doc_category', $doc_category);
    update_post_meta( $post_id, 'doc_frame', $doc_frame);
    update_post_meta( $post_id, 'is_doc_report', $is_doc_report);

    if ($is_doc_report==1){
        $params = array(
            'doc_id'     => $doc_id,
        );                
        $query = retrieve_doc_field_data($params);
        if ($query->have_posts()) {
            while ($query->have_posts()) : $query->the_post();
                $field_name = get_post_meta(get_the_ID(), 'field_name', true);
                $field_title = get_post_meta(get_the_ID(), 'field_title', true);
                $field_type = get_post_meta(get_the_ID(), 'field_type', true);
                $default_value = get_post_meta(get_the_ID(), 'default_value', true);
                $listing_style = get_post_meta(get_the_ID(), 'listing_style', true);
                $sorting_key = get_post_meta(get_the_ID(), 'sorting_key', true);
                $new_post = array(
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'doc-field',
                );    
                $field_id = wp_insert_post($new_post);
                update_post_meta( $field_id, 'doc_id', $post_id);
                update_post_meta( $field_id, 'field_name', $field_name);
                update_post_meta( $field_id, 'field_title', $field_title);
                update_post_meta( $field_id, 'field_type', $field_type);
                update_post_meta( $field_id, 'default_value', $default_value);
                update_post_meta( $field_id, 'listing_style', $listing_style);
                update_post_meta( $field_id, 'sorting_key', $sorting_key);
            endwhile;
            wp_reset_postdata();
        }    
    }
}

function set_doc_unpublished_data() {
    $response = array('success' => false, 'error' => 'Invalid data format');

    if (isset($_POST['_doc_id'])) {
        $doc_id = sanitize_text_field($_POST['_doc_id']);
        // Delete the specified meta key
        delete_post_meta($doc_id, 'todo_status');
        $response = array('success' => true);
    }

    echo json_encode($response);
    wp_die();
}
add_action('wp_ajax_set_doc_unpublished_data', 'set_doc_unpublished_data');
add_action('wp_ajax_nopriv_set_doc_unpublished_data', 'set_doc_unpublished_data');


/*
function select_start_job_option_data($selected_option=0) {
    $current_user_id = get_current_user_id();
    $site_id = get_user_meta($current_user_id, 'site_id', true);
    $user_job_ids_array = get_user_meta($current_user_id, 'user_job_ids', true);
    $options = '<option value="0">Select job</option>';
    foreach ($user_job_ids_array as $job_id) {
        $job_site = get_post_meta($job_id, 'site_id', true);
        $job_number = get_post_meta($job_id, 'job_number', true);
        $job_title = get_the_title($job_id).'('.$job_number.')';
        if ($job_site==$site_id) {
            $selected = ($selected_option == $job_id) ? 'selected' : '';
            $options .= '<option value="' . esc_attr($job_id) . '" '.$selected.' />' . esc_html($job_title) . '</option>';    
        }
    }
    return $options;
}
*/
function select_doc_category_option_data($selected_option=0) {
    $query = retrieve_doc_category_data();
    $options = '<option value="">Select category</option>';
    while ($query->have_posts()) : $query->the_post();
        $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
        $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html(get_the_title()) . '</option>';
    endwhile;
    wp_reset_postdata();
    return $options;
}

// doc-field
function display_doc_field_keys($doc_id=false, $site_id=false) {
    if ($doc_id) $params = array('doc_id' => $doc_id);
    if ($site_id) $params = array('site_id' => $site_id);
    $query = retrieve_doc_field_data($params);
    $_array = array();
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            $_list = array();
            $_list["field_name"] = get_post_meta(get_the_ID(), 'field_name', true);
            $_list["field_type"] = get_post_meta(get_the_ID(), 'field_type', true);
            array_push($_array, $_list);
        endwhile;
        wp_reset_postdata();
    }    
    return $_array;
}

function display_doc_field_list($doc_id=false, $site_id=false) {
    ob_start();
    ?>
    <div id="fields-container">
    <fieldset>
        <table style="width:100%;">
            <thead>
                <tr>
                    <th><?php echo __( 'Field', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Title', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Type', 'your-text-domain' );?></th>
                    <th><?php echo __( 'Style', 'your-text-domain' );?></th>
                </tr>
            </thead>
            <tbody id="sortable-doc-field-list">
                <?php
                $x = 0;
                if ($doc_id) $params = array('doc_id' => $doc_id);
                if ($site_id) $params = array('site_id' => $site_id);                
                $query = retrieve_doc_field_data($params);
                if ($query->have_posts()) {
                    while ($query->have_posts()) : $query->the_post();
                        echo '<tr class="doc-field-list-'.$x.'" id="edit-doc-field-'.esc_attr(get_the_ID()).'" data-field-id="'.esc_attr(get_the_ID()).'">';
                        echo '<td style="text-align:center;">'.esc_html(get_post_meta(get_the_ID(), 'field_name', true)).'</td>';
                        echo '<td style="text-align:center;">'.esc_html(get_post_meta(get_the_ID(), 'field_title', true)).'</td>';
                        echo '<td style="text-align:center;">'.esc_html(get_post_meta(get_the_ID(), 'field_type', true)).'</td>';
                        echo '<td style="text-align:center;">'.esc_html(get_post_meta(get_the_ID(), 'listing_style', true)).'</td>';
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
        <div id="new-doc-field" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
    </fieldset>
    </div>
    <?php display_doc_field_dialog();?>
    <?php
    $html = ob_get_clean();
    return $html;    
}

function retrieve_doc_field_data($params = array()) {
    $args = array(
        'post_type'      => 'doc-field',
        'posts_per_page' => -1,
        'meta_key'       => 'sorting_key',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
    );

    if (!empty($params['doc_id'])) {
        $args['meta_query'][] = array(
            'key'   => 'doc_id',
            'value' => $params['doc_id'],
        );
    }

    if (!empty($params['site_id'])) {
        $args['meta_query'][] = array(
            'key'   => 'site_id',
            'value' => $params['site_id'],
        );
    }

    if (!empty($params['is_listing'])) {
        $args['meta_query'][] = array(
            'key'     => 'listing_style',
            'value'   => '',
            'compare' => '!=',
        );
    }

    if (!empty($params['is_editing'])) {
        $args['meta_query'][] = array(
            'key'     => 'field_type',
            'value'   => '',
            'compare' => '!=',
        );
    }

    $query = new WP_Query($args);
    return $query;
}

function get_doc_field_list_data() {
    $result = array();
    if (isset($_POST['_doc_id'])) {
        $doc_id = sanitize_text_field($_POST['_doc_id']);
        $result['html_contain'] = display_doc_field_list($doc_id);
    } else {
        $result['html_contain'] = 'Invalid AJAX request!';
    }
    wp_send_json($result);
}
add_action('wp_ajax_get_doc_field_list_data', 'get_doc_field_list_data');
add_action('wp_ajax_nopriv_get_doc_field_list_data', 'get_doc_field_list_data');

function display_doc_field_dialog(){
    ?>
    <div id="doc-field-dialog" title="Field dialog" style="display:none;">
    <fieldset>
        <input type="hidden" id="field-id" />
        <label for="field-name"><?php echo __( '欄位名稱：', 'your-text-domain' );?></label>
        <input type="text" id="field-name" class="text ui-widget-content ui-corner-all" />
        <label for="field-title"><?php echo __( '欄位顯示：', 'your-text-domain' );?></label>
        <input type="text" id="field-title" class="text ui-widget-content ui-corner-all" />
        <label for="field-type"><?php echo __( '欄位型態：', 'your-text-domain' );?></label>
        <select id="field-type" class="text ui-widget-content ui-corner-all">
            <option value="text"><?php echo __( '文字型態', 'your-text-domain' );?></option>
            <option value="number"><?php echo __( '數字型態', 'your-text-domain' );?></option>
            <option value="date"><?php echo __( '日期型態', 'your-text-domain' );?></option>
            <option value="checkbox"><?php echo __( '檢查框', 'your-text-domain' );?></option>
            <option value="radio"><?php echo __( '多選一', 'your-text-domain' );?></option>
            <option value="textarea"><?php echo __( '文字區域', 'your-text-domain' );?></option>
        </select>
        <label for="listing-style"><?php echo __( '列表排列：', 'your-text-domain' );?></label>
        <select id="listing-style" class="text ui-widget-content ui-corner-all">
            <option value="left"><?php echo __( '靠左', 'your-text-domain' );?></option>
            <option value="center"><?php echo __( '置中', 'your-text-domain' );?></option>
            <option value="right"><?php echo __( '靠右', 'your-text-domain' );?></option>
            <option value=""></option>
        </select>
        <label for="default-value"><?php echo __( '初始值：', 'your-text-domain' );?></label>
        <input type="text" id="default-value" class="text ui-widget-content ui-corner-all" />
        <label for="order-field"><?php echo __( '排列順序：', 'your-text-domain' );?></label>
        <select id="order-field" class="text ui-widget-content ui-corner-all">
            <option value="ASC"><?php echo __( '由小到大', 'your-text-domain' );?></option>
            <option value="DESC"><?php echo __( '由大到小', 'your-text-domain' );?></option>
            <option value=""></option>
        </select>
    </fieldset>
    </div>
    <?php
}

function get_doc_field_dialog_data() {
    $response = array();
    if( isset($_POST['_field_id']) ) {
        $field_id = (int)sanitize_text_field($_POST['_field_id']);
        $response["field_name"] = esc_html(get_post_meta($field_id, 'field_name', true));
        $response["field_title"] = esc_html(get_post_meta($field_id, 'field_title', true));
        $response["field_type"] = get_post_meta($field_id, 'field_type', true);
        $response["default_value"] = esc_html(get_post_meta($field_id, 'default_value', true));
        $response["listing_style"] = get_post_meta($field_id, 'listing_style', true);
        $response["order_field"] = get_post_meta($field_id, 'order_field', true);
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_get_doc_field_dialog_data', 'get_doc_field_dialog_data' );
add_action( 'wp_ajax_nopriv_get_doc_field_dialog_data', 'get_doc_field_dialog_data' );

function set_doc_field_dialog_data() {
    if( isset($_POST['_field_id']) ) {
        // Update the post into the database
        $field_id = sanitize_text_field($_POST['_field_id']);
        update_post_meta( $field_id, 'field_name', sanitize_text_field($_POST['_field_name']));
        update_post_meta( $field_id, 'field_title', sanitize_text_field($_POST['_field_title']));
        update_post_meta( $field_id, 'field_type', sanitize_text_field($_POST['_field_type']));
        update_post_meta( $field_id, 'default_value', sanitize_text_field($_POST['_default_value']));
        update_post_meta( $field_id, 'listing_style', sanitize_text_field($_POST['_listing_style']));
        update_post_meta( $field_id, 'order_field', sanitize_text_field($_POST['_order_field']));
    } else {
        // Insert the post into the database
        $current_user_id = get_current_user_id();
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
        update_post_meta( $post_id, 'field_type', 'text');
        update_post_meta( $post_id, 'listing_style', 'center');
        update_post_meta( $post_id, 'sorting_key', -1);
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
            update_post_meta( $field_id, 'sorting_key', $index);
        }
        $response = array('success' => true);
    }

    echo json_encode($response);
    wp_die();
}
add_action('wp_ajax_set_sorted_field_id_data', 'set_sorted_field_id_data');
add_action('wp_ajax_nopriv_set_sorted_field_id_data', 'set_sorted_field_id_data');

// doc-frame
function display_doc_frame_contain($doc_id=false) {
    ob_start();
    $doc_title = get_post_meta($doc_id, 'doc_title', true);
    $doc_number = get_post_meta($doc_id, 'doc_number', true);
    $doc_revision = get_post_meta($doc_id, 'doc_revision', true);
    $doc_frame = get_post_meta($doc_id, 'doc_frame', true);
    $site_id = get_post_meta($doc_id, 'site_id', true);
    $image_url = get_post_meta($site_id, 'image_url', true);
    $signature_record_list = get_signature_record_list($site_id, $doc_id);
    $$html_contain = $signature_record_list['html'];
    ?>    
    <div style="display:flex; justify-content:space-between; margin:5px;">
        <div>
            <img src="<?php echo esc_attr($image_url)?>" style="object-fit:cover; width:30px; height:30px; margin-left:5px;" />
            <span><?php echo esc_html($doc_number);?></span>
            <h2 style="display:inline;"><?php echo esc_html($doc_title);?></h2>
            <span><?php echo esc_html($doc_revision);?></span>
        </div>
        <div style="text-align:right; display:flex;">
            <button id="share-document" style="margin-right:5px; font-size:small;" class="button"><?php echo __('文件分享', 'your-text-domain')?></button>
            <button id="signature-record" style="margin-right:5px; font-size:small;" class="button"><?php echo __('簽核記錄', 'your-text-domain')?></button>
            <span id='doc-unpublished' style='margin-left:5px;' class='dashicons dashicons-trash button'></span>
        </div>
    </div>

    <input type="hidden" id="doc-id" value="<?php echo $doc_id;?>" />
    
    <div id="signature-record-div" style="display:none;"><fieldset><?php echo $$html_contain;?></fieldset></div>
    
    <fieldset style="overflow-x:auto; white-space:nowrap;">
    <?php
    $html = ob_get_clean();
    return $html.'<div style="display:inline-block;">'.$doc_frame.'</div></fieldset>';
}

function get_doc_frame_contain() {
    $result = array();
    if (isset($_POST['_doc_id'])) {
        $doc_id = sanitize_text_field($_POST['_doc_id']);
        $result['html_contain'] = display_doc_frame_contain($doc_id);
    } else {
        $result['html_contain'] = 'Invalid AJAX request!';
    }
    wp_send_json($result);
}
add_action('wp_ajax_get_doc_frame_contain', 'get_doc_frame_contain');
add_action('wp_ajax_nopriv_get_doc_frame_contain', 'get_doc_frame_contain');

// doc-report
function display_doc_report_list($doc_id=false, $search_doc_report=false) {
    $doc_title = get_post_meta($doc_id, 'doc_title', true);
    $doc_number = get_post_meta($doc_id, 'doc_number', true);
    $doc_revision = get_post_meta($doc_id, 'doc_revision', true);
    $site_id = get_post_meta($doc_id, 'site_id', true);
    $image_url = get_post_meta($site_id, 'image_url', true);
    $signature_record_list = get_signature_record_list($site_id, $doc_id);
    $html_contain = $signature_record_list['html'];
    ob_start();
    ?>    
    <div style="display:flex; justify-content:space-between; margin:5px;">
        <div>
            <img src="<?php echo esc_attr($image_url)?>" style="object-fit:cover; width:30px; height:30px; margin-left:5px;" />
            <span><?php echo esc_html($doc_number);?></span>
            <h2 style="display:inline;"><?php echo esc_html($doc_title);?></h2>
            <span><?php echo esc_html($doc_revision);?></span>            
        </div>
        <div style="text-align:right; display:flex;">
            <button id="share-document" style="margin-right:5px; font-size:small;" class="button"><?php echo __('文件分享', 'your-text-domain')?></button>
            <button id="signature-record" style="margin-right:5px; font-size:small;" class="button"><?php echo __('簽核記錄', 'your-text-domain')?></button>
            <span id='doc-unpublished' style='margin-left:5px;' class='dashicons dashicons-trash button'></span>
        </div>
    </div>

    <input type="hidden" id="doc-id" value="<?php echo $doc_id;?>" />
    
    <div id="signature-record-div" style="display:none;"><fieldset><?php echo $html_contain;?></fieldset></div>

    <fieldset>
        <div id="doc-report-setting-dialog" title="Doc-report setting" style="display:none">
            <fieldset>
                <label for="doc-title"> Document: </label>
                <input type="text" id="doc-title" value="<?php echo $doc_title;?>" class="text ui-widget-content ui-corner-all" disabled />
                <label for="doc-field-setting"> Field setting: </label>
                <?php echo display_doc_field_list($doc_id);?>
                <div class="separator"></div>
                <label for="doc-report-rows">Doc-report rows: </label>
                <input type="text" id="doc-report-rows" value="<?php echo get_option('doc_report_rows');?>" />
            </fieldset>
        </div>        

        <div style="display:flex; justify-content:space-between; margin:5px;">
            <div>
                <select id="select-doc-report-function">
                    <option value="">Select action</option>
                    <option value="duplicate">Duplicate</option>
                </select>
            </div>
            <div style="text-align:right; display:flex;">
                <input type="text" id="search-doc-report" style="display:inline" placeholder="Search..." />
                <span id="doc-report-setting" style="margin-left:5px;" class="dashicons dashicons-admin-generic button"></span>
            </div>
        </div>

        <table style="width:100%;">
            <thead>
                <?php
                $params = array(
                    'doc_id'     => $doc_id,
                    'is_listing'  => true,
                );                
                $query = retrieve_doc_field_data($params);
                if ($query->have_posts()) {
                    echo '<tr>';
                    while ($query->have_posts()) : $query->the_post();
                        $field_title = get_post_meta(get_the_ID(), 'field_title', true);
                        echo '<th>'.esc_html($field_title).'</th>';
                    endwhile;
                    echo '<th>'. __( '待辦', 'your-text-domain' ).'</th>';
                    echo '</tr>';
                    wp_reset_postdata();
                }
                ?>
            </thead>
            <tbody>
                <?php
                $query = retrieve_doc_report_list_data($doc_id, $search_doc_report);
                if ($query->have_posts()) {
                    while ($query->have_posts()) : $query->the_post();
                        $report_id = get_the_ID();
                        echo '<tr id="edit-doc-report-'.$report_id.'">';
                        // Reset the inner loop before using it again
                        $params = array(
                            'doc_id'     => $doc_id,
                            'is_listing'  => true,
                        );                
                        $inner_query = retrieve_doc_field_data($params);
                        if ($inner_query->have_posts()) {
                            while ($inner_query->have_posts()) : $inner_query->the_post();
                                $field_name = get_post_meta(get_the_ID(), 'field_name', true);
                                $field_type = get_post_meta(get_the_ID(), 'field_type', true);
                                $listing_style = get_post_meta(get_the_ID(), 'listing_style', true);
                                $field_value = get_post_meta($report_id, $field_name, true);
                                echo '<td style="text-align:'.$listing_style.';">';
                                if ($field_type=='checkbox') {
                                    $is_checked = ($field_value==1) ? 'checked' : '';
                                    echo '<input type="checkbox" '.$is_checked.' />';
                                } elseif ($field_type=='radio') {
                                    echo get_radio_checked_value($doc_id, $field_name, $report_id);
                                } else {
                                    echo esc_html($field_value);
                                }
                                echo '</td>';
                            endwhile;                
                            // Reset only the inner loop's data
                            wp_reset_postdata();
                        }
                        $todo_id = get_post_meta($report_id, 'todo_status', true);
                        $todo_status = ($todo_id) ? get_the_title($todo_id) : 'Draft';
                        $todo_status = ($todo_id==-1) ? '文件發行' : $todo_status;
                        $todo_status = ($todo_id==-2) ? '文件廢止' : $todo_status;
                        echo '<td style="text-align:center;">'.esc_html($todo_status).'</td>';
                        echo '</tr>';
                    endwhile;                
                    // Reset the main query's data
                    wp_reset_postdata();
                }
                ?>
            </tbody>
        </table>
        <div id="new-doc-report" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
    </fieldset>
    <?php
    $html = ob_get_clean();
    return $html;
}

function get_radio_checked_value($doc_id, $field_name, $report_id) {
    // Define the query arguments
    $args = array(
        'post_type'      => 'doc-field',
        'posts_per_page' => -1,
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'     => 'doc_id',
                'value'   => $doc_id,
                'compare' => '='
            ),
            array(
                'key'     => 'field_type',
                'value'   => 'radio',
                'compare' => '='
            ),
            array(
                'key'     => 'field_name',
                'value'   => substr($field_name, 0, 5),
                'compare' => 'LIKE'
            ),
        ),
    );
    $query = new WP_Query($args);

    // Check if there are any posts found
    if ($query->have_posts()) {
        $x = '';
        while ($query->have_posts()) : $query->the_post();
            $field_name     = get_post_meta(get_the_ID(), 'field_name', true);
            $default_value  = get_post_meta(get_the_ID(), 'default_value', true);
            $field_value    = get_post_meta($report_id, $field_name, true);
            $x .= '('.$field_value.')'.$default_value;
            if ($field_value == 1) {
                return $default_value;
            }
        endwhile;

        // Reset post data
        wp_reset_postdata();

        return $x.'Not found';
    } else {
        // If no matching post is found, return false or any default value
        return false;
    }
}

function retrieve_doc_report_list_data($doc_id = false, $search_doc_report = false) {
    $args = array(
        'post_type'      => 'doc-report',
        'posts_per_page' => 30,
        'paged'          => (get_query_var('paged')) ? get_query_var('paged') : 1,
        'meta_query'     => array(
            'relation' => 'AND',
            array(
                'key'     => 'doc_id',
                'value'   => $doc_id,
                'compare' => '='
            ),
        ),
        'orderby'        => array(), // Initialize orderby parameter as an array
    );

    $order_field_name = ''; // Initialize variable to store the meta key for ordering
    $order_field_value = ''; // Initialize variable to store the order direction

    if ($search_doc_report) {
        $args['meta_query'][] = array(
            'relation' => 'OR',
        );
    }

    $inner_query = retrieve_doc_field_data(array('doc_id' => $doc_id));

    if ($inner_query->have_posts()) {
        while ($inner_query->have_posts()) : $inner_query->the_post();
            $field_name = get_post_meta(get_the_ID(), 'field_name', true);
            $order_field_value = get_post_meta(get_the_ID(), 'order_field', true);

            // Check if the order_field_value is valid
            if ($order_field_value === 'ASC' || $order_field_value === 'DESC') {
                // Add the field_name and order_field_value to orderby array
                $args['orderby'][$field_name] = $order_field_value;
                
                $order_field_name = $field_name; // Assign the field_name if order_field_value is valid
            }

            if ($search_doc_report) {
                $args['meta_query'][1][] = array( // Append to the OR relation
                    'key'     => $field_name,
                    'value'   => $search_doc_report,
                    'compare' => 'LIKE',
                );
            }
        endwhile;

        // Reset only the inner loop's data
        wp_reset_postdata();
    }

    $args['orderby'] = array(
        'index' => 'ASC',
    );

    $args['orderby']  = 'meta_value';
    $args['order']    = 'ASC';    
    $args['meta_key'] = $order_field_name;

    $query = new WP_Query($args);
    return $query;
}

function display_doc_report_dialog($report_id=false) {

    $todo_status = get_post_meta($report_id, 'todo_status', true);
    $start_job = get_post_meta($report_id, 'start_job', true);

    $doc_id = get_post_meta($report_id, 'doc_id', true);
    $doc_title = get_post_meta($doc_id, 'doc_title', true);
    if ($report_id) $doc_title .= '(Report#'.$report_id.')';

    $site_id = get_post_meta($doc_id, 'site_id', true);
    $image_url = get_post_meta($site_id, 'image_url', true);
    $signature_record_list = get_signature_record_list($site_id, false, $report_id);
    $html_contain = $signature_record_list['html'];

    $params = array(
        'doc_id'     => $doc_id,
        'is_editing'  => true,
    );                
    $query = retrieve_doc_field_data($params);

    ob_start();
    ?>
    <div style="display:flex; justify-content:space-between; margin:5px;">
        <div>
            <img src="<?php echo esc_attr($image_url)?>" style="object-fit:cover; width:30px; height:30px; margin-left:5px;" />
            <h2 style="display:inline;"><?php echo esc_html($doc_title);?></h2>
        </div>
        <div style="text-align:right; display:flex;">        
        <?php if ($todo_status==-1){?>
            <button id="duplicate-doc-report-<?php echo $report_id;?>" style="margin-right:5px; font-size:small;" class="button"><?php echo __('複製記錄', 'your-text-domain')?></button>
            <button id="signature-record" style="margin-right:5px; font-size:small;" class="button"><?php echo __('簽核記錄', 'your-text-domain')?></button>
            <span id='doc-report-unpublished' style='margin-left:5px;' class='dashicons dashicons-trash button'></span>
        <?php }?>
        </div>
    </div>

    <div id="report-signature-record-div" style="display:none;"><fieldset><?php echo $html_contain;?></fieldset></div>

    <input type="hidden" id="report-id" value="<?php echo esc_attr($report_id);?>" />
    <input type="hidden" id="doc-id" value="<?php echo esc_attr($doc_id);?>" />
    <fieldset>
    <?php
    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            $field_name = get_post_meta(get_the_ID(), 'field_name', true);
            $field_title = get_post_meta(get_the_ID(), 'field_title', true);
            $field_type = get_post_meta(get_the_ID(), 'field_type', true);
            $default_value = get_post_meta(get_the_ID(), 'default_value', true);
            if ($is_doc) {
                $field_value = get_post_meta($doc_id, $field_name, true);
            } else {
                $field_value = get_post_meta($report_id, $field_name, true);
            }
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
                    <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label><br>
                    <?php
                    break;
    
                case ($field_type=='radio'):
                    if ($prev_field_name!=substr($field_name, 0, 5)) $x = 0;
                    if ($x==0) echo '<label>'.esc_html($field_title).'</label><br>';
                    $field_value = get_post_meta($report_id, $field_name, true);
                    $is_checked = ($field_value==1) ? 'checked' : '';
                    ?>                    
                    <input type="radio" id="<?php echo esc_attr($field_name);?>" name="<?php echo esc_attr(substr($field_name, 0, 5));?>" <?php echo $is_checked;?> />
                    <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($default_value);?></label><br>
                    <?php
                    $prev_field_name=substr($field_name, 0, 5);
                    $x += 1;
                    break;
    
                case ($field_type=='date'):
                    ?>
                    <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label>
                    <input type="text" id="<?php echo esc_attr($field_name);?>" value="<?php echo esc_html($field_value);?>" class="text ui-widget-content ui-corner-all datepicker" />
                    <?php
                    break;
    
                case ($field_type=='number'):
                    ?>
                    <label for="<?php echo esc_attr($field_name);?>"><?php echo esc_html($field_title);?></label>
                    <input type="number" id="<?php echo esc_attr($field_name);?>" value="<?php echo esc_html($field_value);?>" class="text ui-widget-content ui-corner-all" />
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
    <hr>
    <?php
    if (!$todo_status){
        ?>
        <div style="display:flex; justify-content:space-between; margin:5px;">
        <div>
        <?php
        $query = retrieve_job_action_list_data($start_job);        
        if ($query->have_posts()) {
            while ($query->have_posts()) : $query->the_post();
                $next_job = get_post_meta(get_the_ID(), 'next_job', true);
                $job_title = get_the_title().':'.get_the_title($next_job);
                echo '<input type="button" id="doc-report-dialog-button-'.get_the_ID().'" value="'.$job_title.'" style="margin:5px;" />';
            endwhile;
            wp_reset_postdata();
        }
        ?>
        </div>
        <div style="text-align:right; display:flex;">
            <input type="button" id="del-doc-report-<?php echo $report_id;?>" value="<?php echo __( 'Delete', 'your-text-domain' );?>" style="margin:3px;" />
            <input type="button" id="duplicate-doc-report-<?php echo $report_id;?>" value="<?php echo __( 'Duplicate', 'your-text-domain' );?>" style="margin:3px;" />
        </div>
        </div>
        <?php
    }
    ?>
    </fieldset>
    <?php
    $html = ob_get_clean();
    return $html;
}

function set_doc_report_dialog_data() {
    if( isset($_POST['_report_id']) ) {
        // Update the Document data
        $report_id = sanitize_text_field($_POST['_report_id']);
        $doc_id = get_post_meta($report_id, 'doc_id', true);
        $params = array(
            'doc_id'     => $doc_id,
        );                
        $query = retrieve_doc_field_data($params);
        if ($query->have_posts()) {
            while ($query->have_posts()) : $query->the_post();
                $field_name = get_post_meta(get_the_ID(), 'field_name', true);
                $field_value = sanitize_text_field($_POST[$field_name]);
                update_post_meta( $report_id, $field_name, $field_value);
            endwhile;
            wp_reset_postdata();
        }
    } else {
        // Insert the post into the database
        $current_user_id = get_current_user_id();
        $new_post = array(
            'post_status'   => 'publish',
            'post_author'   => $current_user_id,
            'post_type'     => 'doc-report',
        );    
        $post_id = wp_insert_post($new_post);
        $doc_id = sanitize_text_field($_POST['_doc_id']);
        $start_job = get_post_meta($doc_id, 'start_job', true);
        update_post_meta( $post_id, 'doc_id', $doc_id);
        update_post_meta( $post_id, 'start_job', $start_job);
        $params = array(
            'doc_id'     => $doc_id,
        );                
        $query = retrieve_doc_field_data($params);
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

function set_todo_in_doc_report() {
    if ( isset($_POST['_action_id']) && isset($_POST['_report_id']) ) {
        $current_user_id = get_current_user_id();
        $action_id = sanitize_text_field($_POST['_action_id']);
        $report_id = sanitize_text_field($_POST['_report_id']);

        // Insert the To-do list for current job_id
        $job_id = get_post_meta($action_id, 'job_id', true);
        $new_post = array(
            'post_title'    => get_the_title($job_id),
            'post_status'   => 'publish',
            'post_author'   => $current_user_id,
            'post_type'     => 'todo',
        );    
        $todo_id = wp_insert_post($new_post);
        update_post_meta( $todo_id, 'job_id', $job_id);
        update_post_meta( $todo_id, 'report_id', $report_id);
        update_post_meta( $todo_id, 'submit_user', $current_user_id);
        update_post_meta( $todo_id, 'submit_action', $action_id);
        update_post_meta( $todo_id, 'submit_time', time());

        // set next todo and actions
        $params = array(
            'action_id' => $action_id,
            //'doc_id'    => $doc_id,
            //'report_id' => $report_id,
        );        
        set_next_todo_and_actions($params);
/*
        // Insert the To-do list for next_job
        $next_job      = get_post_meta($action_id, 'next_job', true);
        $next_leadtime = get_post_meta($action_id, 'next_leadtime', true);
        $new_post = array(
            'post_title'    => get_the_title($next_job),
            'post_status'   => 'publish',
            'post_author'   => $current_user_id,
            'post_type'     => 'todo',
        );    
        $new_todo_id = wp_insert_post($new_post);
        update_post_meta( $new_todo_id, 'job_id', $next_job);
        update_post_meta( $new_todo_id, 'report_id', $report_id); //??
        update_post_meta( $new_todo_id, 'todo_due', time()+$next_leadtime);
        update_post_meta( $report_id, 'todo_status', $new_todo_id);    
        notice_the_responsible_persons($new_todo_id);

        // Insert the Action list for next_job
        $query = retrieve_job_action_list_data($next_job);
        if ($query->have_posts()) {
            while ($query->have_posts()) : $query->the_post();
                $new_post = array(
                    'post_title'    => get_the_title(),
                    'post_content'  => get_post_field('post_content', get_the_ID()),
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'action',
                );    
                $new_action_id = wp_insert_post($new_post);
                $new_next_job = get_post_meta(get_the_ID(), 'next_job', true);
                $new_next_leadtime = get_post_meta(get_the_ID(), 'next_leadtime', true);
                update_post_meta( $new_action_id, 'todo_id', $new_todo_id);
                update_post_meta( $new_action_id, 'next_job', $new_next_job);
                update_post_meta( $new_action_id, 'next_leadtime', $new_next_leadtime);
            endwhile;
            wp_reset_postdata();
        }
*/        
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_set_todo_in_doc_report', 'set_todo_in_doc_report' );
add_action( 'wp_ajax_nopriv_set_todo_in_doc_report', 'set_todo_in_doc_report' );

function duplicate_doc_report_dialog_data() {
    if( isset($_POST['_report_id']) ) {
        // Insert the post into the database
        $current_user_id = get_current_user_id();
        $new_post = array(
            'post_status'   => 'publish',
            'post_author'   => $current_user_id,
            'post_type'     => 'doc-report',
        );    
        $post_id = wp_insert_post($new_post);
        $report_id = sanitize_text_field($_POST['_report_id']);
        $doc_id = get_post_meta($report_id, 'doc_id', true);
        $start_job = get_post_meta($report_id, 'start_job', true);
        update_post_meta( $post_id, 'doc_id', $doc_id);
        update_post_meta( $post_id, 'start_job', $start_job);

        $params = array(
            'doc_id'     => $doc_id,
        );                
        $query = retrieve_doc_field_data($params);
        if ($query->have_posts()) {
            while ($query->have_posts()) : $query->the_post();
                $field_name = get_post_meta(get_the_ID(), 'field_name', true);
                $field_value = sanitize_text_field($_POST[$field_name]);
                update_post_meta( $post_id, $field_name, $field_value);
            endwhile;
            wp_reset_postdata();
        }
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_duplicate_doc_report_dialog_data', 'duplicate_doc_report_dialog_data' );
add_action( 'wp_ajax_nopriv_duplicate_doc_report_dialog_data', 'duplicate_doc_report_dialog_data' );

function get_doc_report_list_data() {
    $result = array();
    if (isset($_POST['_doc_id'])) {
        $doc_id = sanitize_text_field($_POST['_doc_id']);
        $search_doc_report = sanitize_text_field($_POST['_search_doc_report']);
        if ($search_doc_report) {
            $result['html_contain'] = display_doc_report_list($doc_id, $search_doc_report);
        } else {
            $result['html_contain'] = display_doc_report_list($doc_id);
        }
    } else {
        $result['html_contain'] = 'Invalid AJAX request!';
    }
    wp_send_json($result);
}
add_action( 'wp_ajax_get_doc_report_list_data', 'get_doc_report_list_data' );
add_action( 'wp_ajax_nopriv_get_doc_report_list_data', 'get_doc_report_list_data' );

function get_doc_report_dialog_data() {
    $result = array();
    if (isset($_POST['_report_id'])) {
        $report_id = sanitize_text_field($_POST['_report_id']);
        $todo_status = get_post_meta($report_id, 'todo_status', true);
        if ($todo_status<1) {
            $result['html_contain'] = display_doc_report_dialog($report_id);
            $doc_id = get_post_meta($report_id, 'doc_id', true);
            $result['doc_id'] = $doc_id;
            $result['doc_fields'] = display_doc_field_keys($doc_id);
        }
    } else {
        $result['html_contain'] = 'Invalid AJAX request!';
    }
    wp_send_json($result);
}
add_action('wp_ajax_get_doc_report_dialog_data', 'get_doc_report_dialog_data');
add_action('wp_ajax_nopriv_get_doc_report_dialog_data', 'get_doc_report_dialog_data');

function del_doc_report_dialog_data() {
    $result = wp_delete_post($_POST['_report_id'], true);
    wp_send_json($result);
}
add_action( 'wp_ajax_del_doc_report_dialog_data', 'del_doc_report_dialog_data' );
add_action( 'wp_ajax_nopriv_del_doc_report_dialog_data', 'del_doc_report_dialog_data' );
