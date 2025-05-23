<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (!class_exists('embedded_items')) {
    class embedded_items {
        // Class constructor
        public function __construct() {
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_embedded_items_scripts' ) );
            //add_action( 'init', array( $this, 'register_doc_category_post_type' ) );

            add_action( 'wp_ajax_get_embedded_dialog_data', array( $this, 'get_embedded_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_embedded_dialog_data', array( $this, 'get_embedded_dialog_data' ) );
            add_action( 'wp_ajax_set_embedded_dialog_data', array( $this, 'set_embedded_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_embedded_dialog_data', array( $this, 'set_embedded_dialog_data' ) );
            add_action( 'wp_ajax_del_embedded_dialog_data', array( $this, 'del_embedded_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_embedded_dialog_data', array( $this, 'del_embedded_dialog_data' ) );
            
            add_action( 'wp_ajax_duplicate_embedded_dialog_data', array( $this, 'duplicate_embedded_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_duplicate_embedded_dialog_data', array( $this, 'duplicate_embedded_dialog_data' ) );

            add_action( 'wp_ajax_get_embedded_item_dialog_data', array( $this, 'get_embedded_item_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_embedded_item_dialog_data', array( $this, 'get_embedded_item_dialog_data' ) );
            add_action( 'wp_ajax_set_embedded_item_dialog_data', array( $this, 'set_embedded_item_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_embedded_item_dialog_data', array( $this, 'set_embedded_item_dialog_data' ) );
            add_action( 'wp_ajax_del_embedded_item_dialog_data', array( $this, 'del_embedded_item_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_embedded_item_dialog_data', array( $this, 'del_embedded_item_dialog_data' ) );

            add_action( 'wp_ajax_sort_embedded_item_list_data', array( $this, 'sort_embedded_item_list_data' ) );
            add_action( 'wp_ajax_nopriv_sort_embedded_item_list_data', array( $this, 'sort_embedded_item_list_data' ) );

            add_action( 'wp_ajax_get_line_report_dialog_data', array( $this, 'get_line_report_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_line_report_dialog_data', array( $this, 'get_line_report_dialog_data' ) );
            add_action( 'wp_ajax_set_line_report_dialog_data', array( $this, 'set_line_report_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_line_report_dialog_data', array( $this, 'set_line_report_dialog_data' ) );
            add_action( 'wp_ajax_del_line_report_dialog_data', array( $this, 'del_line_report_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_line_report_dialog_data', array( $this, 'del_line_report_dialog_data' ) );

            add_action( 'wp_ajax_get_doc_category_dialog_data', array( $this, 'get_doc_category_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_doc_category_dialog_data', array( $this, 'get_doc_category_dialog_data' ) );
            add_action( 'wp_ajax_set_doc_category_dialog_data', array( $this, 'set_doc_category_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_doc_category_dialog_data', array( $this, 'set_doc_category_dialog_data' ) );
            add_action( 'wp_ajax_del_doc_category_dialog_data', array( $this, 'del_doc_category_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_doc_category_dialog_data', array( $this, 'del_doc_category_dialog_data' ) );

            add_action( 'wp_ajax_get_iso_standard_dialog_data', array( $this, 'get_iso_standard_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_iso_standard_dialog_data', array( $this, 'get_iso_standard_dialog_data' ) );
            add_action( 'wp_ajax_set_iso_standard_dialog_data', array( $this, 'set_iso_standard_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_iso_standard_dialog_data', array( $this, 'set_iso_standard_dialog_data' ) );
            add_action( 'wp_ajax_del_iso_standard_dialog_data', array( $this, 'del_iso_standard_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_iso_standard_dialog_data', array( $this, 'del_iso_standard_dialog_data' ) );

            add_shortcode('display-iso-standard-contains', array( $this, 'display_iso_standard_contains' ) );

            add_action( 'wp_ajax_get_department_card_dialog_data', array( $this, 'get_department_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_department_card_dialog_data', array( $this, 'get_department_card_dialog_data' ) );
            add_action( 'wp_ajax_set_department_card_dialog_data', array( $this, 'set_department_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_department_card_dialog_data', array( $this, 'set_department_card_dialog_data' ) );
            add_action( 'wp_ajax_del_department_card_dialog_data', array( $this, 'del_department_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_department_card_dialog_data', array( $this, 'del_department_card_dialog_data' ) );

            add_action( 'wp_ajax_get_department_user_list_data', array( $this, 'get_department_user_list_data' ) );
            add_action( 'wp_ajax_nopriv_get_department_user_list_data', array( $this, 'get_department_user_list_data' ) );
            add_action( 'wp_ajax_add_department_user_dialog_data', array( $this, 'add_department_user_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_add_department_user_dialog_data', array( $this, 'add_department_user_dialog_data' ) );
            add_action( 'wp_ajax_del_department_user_dialog_data', array( $this, 'del_department_user_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_department_user_dialog_data', array( $this, 'del_department_user_dialog_data' ) );
        }

        function enqueue_embedded_items_scripts() {
            wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', '', '1.13.2');
            wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), null, true);

            wp_enqueue_script('embedded-items', plugins_url('js/embedded-items.js', __FILE__), array('jquery'), time());
            wp_localize_script('embedded-items', 'ajax_object', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('embedded-items-nonce'), // Generate nonce
            ));                
        }

        // embedded
        function display_embedded_list() {
            ob_start();
            $documents_class = new display_documents();
            ?>
            <div class="ui-widget" id="result-container">
            <?php echo display_iso_helper_logo();?>
            <h2 style="display:inline;"><?php echo __( 'Embedded Items', 'textdomain' );?></h2>

            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                    <select id="select-category"><?php echo $this->select_doc_category_options('embedded');?></select>
                </div>
                <div style="text-align: right">
                    <input type="text" id="search-embedded" placeholder="<?php echo __( 'Search...', 'textdomain' );?>" style="margin:5px;" />
                </div>                        
            </div>

            <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <th><?php echo __( 'No.', 'textdomain' );?></th>
                        <th><?php echo __( 'Title', 'textdomain' );?></th>
                        <th><?php echo __( 'Public', 'textdomain' );?></th>
                    </thead>
                    <tbody>
                    <?php
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $query = $documents_class->retrieve_document_data($paged, 'embedded');
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $embedded_id = get_the_ID();
                            $embedded_title = get_the_title();
                            $embedded_number = get_post_meta($embedded_id, 'doc_number', true);
                            $is_public = get_post_meta($embedded_id, 'is_public', true);
                            ?>
                            <tr id="edit-embedded-<?php echo $embedded_id;?>">
                                <td style="text-align:center;"><?php echo esc_html($embedded_number);?></td>
                                <td><?php echo $embedded_title;?></td>
                                <td style="text-align:center;"><?php echo esc_html(($is_public==1) ? 'V' : '');?></td>
                            </tr>
                            <?php 
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
                <?php if (is_site_admin()) {?>
                    <div id="new-embedded" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                <?php }?>
                <div class="pagination">
                    <?php
                    // Display pagination links
                    if ($paged > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged - 1)) . '"> < </a></span>';
                    echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $paged, $total_pages) . '</span>';
                    if ($paged < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged + 1)) . '"> > </a></span>';
                    ?>
                </div>
            </fieldset>
            </div>
            <?php
            return ob_get_clean();
        }

        function display_embedded_dialog($embedded_id=false) {
            ob_start();
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $documents_class = new display_documents();
            $prev_embedded_id = $documents_class->get_previous_doc_id($embedded_id); // Fetch the previous ID
            $next_embedded_id = $documents_class->get_next_doc_id($embedded_id);     // Fetch the next ID
            $embedded_title = get_the_title($embedded_id);
            $embedded_number = get_post_meta($embedded_id, 'doc_number', true);
            $embedded_site = get_post_meta($embedded_id, 'site_id', true);
            $is_public = get_post_meta($embedded_id, 'is_public', true);
            $is_public_checked = ($is_public==1) ? 'checked' : '';
            ?>
            <div class="ui-widget" id="result-container">
            <?php echo display_iso_helper_logo();?>
            <h2 style="display:inline;"><?php echo __( 'Embedded Items', 'textdomain' );?></h2>
            <input type="hidden" id="embedded-id" value="<?php echo esc_attr($embedded_id);?>" />
            <input type="hidden" id="prev-embedded-id" value="<?php echo esc_attr($prev_embedded_id); ?>" />
            <input type="hidden" id="next-embedded-id" value="<?php echo esc_attr($next_embedded_id); ?>" />
            <input type="hidden" id="is-site-admin" value="<?php echo esc_attr(is_site_admin());?>" />

            <fieldset>
                <label for="embedded-number"><?php echo __( 'No.', 'textdomain' );?></label>
                <input type="text" id="embedded-number" value="<?php echo esc_attr($embedded_number);?>" class="text ui-widget-content ui-corner-all" />
                <label for="embedded-title"><?php echo __( 'Title', 'textdomain' );?></label>
                <input type="text" id="embedded-title" value="<?php echo esc_attr($embedded_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="embedded-item-list"><?php echo __( 'Items', 'textdomain' );?></label>
                <div id="embedded-item-list">
                    <?php echo $this->display_embedded_item_list($embedded_id);?>
                </div>
                <?php //if ($embedded_site==$site_id || current_user_can('administrator')) {?>
                <?php if (current_user_can('administrator')) {?>
                    <div>
                        <input type="checkbox" id="is-public" <?php echo $is_public_checked;?> /> 
                        <label for="is-public"><?php echo __( 'Is public', 'textdomain' );?></label>
                    </div>
                <?php }?>
                <hr>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                        <?php if (is_site_admin()) {?>
                            <input type="button" id="save-embedded-button" value="<?php echo __( 'Save', 'textdomain' );?>" style="margin:3px;" />
                            <input type="button" id="del-embedded-button" value="<?php echo __( 'Delete', 'textdomain' );?>" style="margin:3px;" />
                        <?php }?>
                    </div>
                    <div style="text-align: right">
                    <?php if (is_site_admin()) {?>
                        <input type="button" id="duplicate-embedded-button" value="<?php echo __( 'Duplicate', 'textdomain' );?>" style="margin:3px;" />
                    <?php }?>
                    <input type="button" id="exit-embedded-dialog" value="<?php echo __( 'Exit', 'textdomain' );?>" style="margin:5px;" />
                    </div>
                </div>
            </fieldset>
            </div>
            <?php
            return ob_get_clean();
        }

        function get_embedded_dialog_data() {
            $response = array();
            if( isset($_POST['_embedded_id']) ) {
                $embedded_id = (isset($_POST['_embedded_id'])) ? sanitize_text_field($_POST['_embedded_id']) : '';
                $response['html_contain'] = $this->display_embedded_dialog($embedded_id);
            }
            wp_send_json($response);
        }

        function set_embedded_dialog_data() {
            if( isset($_POST['_embedded_id']) ) {
                $embedded_id = (isset($_POST['_embedded_id'])) ? sanitize_text_field($_POST['_embedded_id']) : '';
                $embedded_number = (isset($_POST['_embedded_number'])) ? sanitize_text_field($_POST['_embedded_number']) : '';
                $embedded_title = (isset($_POST['_embedded_title'])) ? sanitize_text_field($_POST['_embedded_title']) : '';
                $is_public = (isset($_POST['_is_public'])) ? sanitize_text_field($_POST['_is_public']) : 0;
                $data = array(
                    'ID'           => $embedded_id,
                    'post_title'   => $embedded_title,
                );
                wp_update_post( $data );
                update_post_meta($embedded_id, 'doc_number', $embedded_number);
                update_post_meta($embedded_id, 'is_public', $is_public);
            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $new_post = array(
                    'post_type'     => 'document',
                    'post_title'    => __( 'New Embedded', 'textdomain' ),
                    'post_content'  => __( 'Your post content goes here.', 'textdomain' ),
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'site_id', $site_id);
                update_post_meta($post_id, 'doc_number', '-');
                update_post_meta($post_id, 'is_doc_report', 1);
            }
            $response = array('html_contain' => $this->display_embedded_list());
            wp_send_json($response);
        }

        function del_embedded_dialog_data() {
            wp_delete_post($_POST['_embedded_id'], true);
            $response = array('html_contain' => $this->display_embedded_list());
            wp_send_json($response);
        }

        function duplicate_embedded_dialog_data() {
            if( isset($_POST['_embedded_id']) ) {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $embedded_id = (isset($_POST['_embedded_id'])) ? sanitize_text_field($_POST['_embedded_id']) : 0;
                $embedded_number = (isset($_POST['_embedded_number'])) ? sanitize_text_field($_POST['_embedded_number']) : '';
                $embedded_title = (isset($_POST['_embedded_title'])) ? sanitize_text_field($_POST['_embedded_title']) : '';
                // Create the post
                $new_post = array(
                    'post_type'     => 'document',
                    'post_title'    => $embedded_title.'('.__( 'Duplicated', 'textdomain' ).')',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'site_id', $site_id);
                update_post_meta($post_id, 'doc_number', '-');

                $documents_class = new display_documents();
                $query = $documents_class->retrieve_doc_field_data(array('doc_id' => $embedded_id));
                if ($query->have_posts()) {
                    while ($query->have_posts()) : $query->the_post();
                        $field_id = get_the_ID();
                        $field_type = get_post_meta($field_id, 'field_type', true);
                        $default_value = get_post_meta($field_id, 'default_value', true);
                        $field_note = get_post_meta($field_id, 'field_note', true);
                        $sorting_key = get_post_meta($field_id, 'sorting_key', true);
                        $new_embedded_item = array(
                            'post_type'     => 'doc-field',
                            'post_title'    => get_the_title(),
                            'post_content'  => get_the_content(),
                            'post_status'   => 'publish',
                            'post_author'   => $current_user_id,
                        );    
                        $embedded_item_id = wp_insert_post($new_embedded_item);
                        update_post_meta($embedded_item_id, 'field_type', $field_type);
                        update_post_meta($embedded_item_id, 'default_value', $default_value);
                        update_post_meta($embedded_item_id, 'field_note', $field_note);
                        update_post_meta($embedded_item_id, 'sorting_key', $sorting_key);
                        update_post_meta($embedded_item_id, 'doc_id', $post_id);
                    endwhile;
                    wp_reset_postdata();
                }
            }
            $response = array('html_contain' => $this->display_embedded_list());
            wp_send_json($response);
        }

        function select_embedded_options($selected_option=0) {
            $documents_class = new display_documents();
            $query = $documents_class->retrieve_document_data(0, 'embedded');
            $options = '<option value="">'.__( 'Select Option', 'textdomain' ).'</option>';
            while ($query->have_posts()) : $query->the_post();
                $embedded_id = get_the_ID();
                $embedded_title = get_the_title();
                $embedded_number = get_post_meta($embedded_id, 'doc_number', true);
                $selected = ($selected_option == $embedded_id) ? 'selected' : '';
                $options .= '<option value="' . esc_attr($embedded_id) . '" '.$selected.' >' . esc_html($embedded_title.'('.$embedded_number.')') . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        // embedded-item
        function display_embedded_item_list($embedded_id=false) {
            ob_start();
            ?>
            <fieldset>
            <table style="width:100%;">
                <thead>
                    <tr>
                        <th><?php echo __( 'Item', 'textdomain' );?></th>
                        <th><?php echo __( 'Type', 'textdomain' );?></th>
                        <th><?php echo __( 'Default', 'textdomain' );?></th>
                    </tr>
                </thead>
                <tbody id="sortable-embedded-item-list">
                <?php
                $paged = max(1, get_query_var('paged')); // Get the current page number
                $documents_class = new display_documents();
                $query = $documents_class->retrieve_doc_field_data(array('doc_id' => $embedded_id));
                $total_posts = $query->found_posts;
                $total_pages = ceil($total_posts / get_option('operation_row_counts'));

                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post();
                        $embedded_item_id = get_the_ID();
                        $embedded_item_title = get_the_title();
                        $field_note = get_post_meta($embedded_item_id, 'field_note', true);
                        $field_type = get_post_meta($embedded_item_id, 'field_type', true);
                        $documents_class = new display_documents();
                        $type_name = $documents_class->get_field_type_data($field_type);
                        $default_value = get_post_meta($embedded_item_id, 'default_value', true);
                        $embedded_doc = get_post_meta($embedded_item_id, 'embedded_doc', true);
                        if ($embedded_doc) $default_value = get_the_title($embedded_doc);
                        if ($field_type=='heading') {
                            if (!$default_value) {
                                $field_note = '<b>'.$field_note.'</b>';
                                $embedded_item_title = '<b>'.$embedded_item_title.'</b>';    
                            }
                            $field_type='';
                            $default_value='';
                        }
                        ?>
                        <tr id="edit-item-<?php echo $embedded_item_id;?>" data-embedded-item-id="<?php echo esc_attr($embedded_item_id);?>">
                            <td><?php echo $embedded_item_title;?></td>
                            <td style="text-align:center;"><?php echo esc_html($type_name);?></td>
                            <td style="text-align:center;"><?php echo esc_html($default_value);?></td>
                        </tr>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
                </tbody>
            </table>
            <?php if (is_site_admin()) {?>
                <div id="new-embedded-item" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
            <?php }?>
            <div class="pagination">
                <?php
                // Display pagination links
                //if ($paged > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged - 1)) . '"> < </a></span>';
                //echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $paged, $total_pages) . '</span>';
                //if ($paged < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged + 1)) . '"> > </a></span>';
                ?>
            </div>
            </fieldset>
            <div id="embedded-item-dialog" title="embedded-item-dialog"></div>
            <?php
            return ob_get_clean();
        }

        function display_embedded_item_dialog($embedded_item_id=false) {
            ob_start();
            $documents_class = new display_documents();
            $embedded_item_title = get_the_title($embedded_item_id);
            $field_type = get_post_meta($embedded_item_id, 'field_type', true);
            $default_value = get_post_meta($embedded_item_id, 'default_value', true);
            $embedded_doc = get_post_meta($embedded_item_id, 'embedded_doc', true);
            $listing_style = get_post_meta($embedded_item_id, 'listing_style', true);            
            $field_note = get_post_meta($embedded_item_id, 'field_note', true);
            ?>
            <fieldset>
                <input type="hidden" id="embedded-item-id" value="<?php echo esc_attr($embedded_item_id);?>" />
                <input type="hidden" id="is-site-admin" value="<?php echo esc_attr(is_site_admin());?>" />
                <label for="embedded-item-title"><?php echo __( 'Item', 'textdomain' );?></label>
                <textarea id="embedded-item-title" rows="2" style="width:100%;"><?php echo $embedded_item_title;?></textarea>
                <label for="field-type"><?php echo __( 'Field Type', 'textdomain' );?></label>
                <select id="field-type" class="select ui-widget-content ui-corner-all">
                <?php $types = $documents_class->get_field_type_data();?>
                <?php foreach ($types as $value => $label): ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php echo ($field_type === $value) ? 'selected' : ''; ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
                </select>
                <div id="embedded-selection" style="display:none">
                    <label for="embedded-doc"><?php echo __( 'Embedded Document', 'textdomain' );?></label>
                    <select id="embedded-doc" class="select ui-widget-content ui-corner-all">
                        <?php echo $documents_class->select_document_list_options($embedded_doc, 'embedded');?>
                    </select>
                </div>
                <label for="default-value"><?php echo __( 'Default', 'textdomain' );?></label>
                <textarea id="default-value" rows="2" style="width:100%;"><?php echo $default_value;?></textarea>
                <label for="listing-style"><?php echo __( 'Align', 'textdomain' );?></label>
                <select id="listing-style" class="select ui-widget-content ui-corner-all">
                <?php $styles = $documents_class->get_listing_style_data();?>
                <?php foreach ($styles as $value => $label): ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php echo ($listing_style === $value) ? 'selected' : ''; ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
                </select>
                <label for="embedded-item-note"><?php echo __( 'Note', 'textdomain' );?></label>
                <textarea id="embedded-item-note" rows="2" style="width:100%;"><?php echo $field_note;?></textarea>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_embedded_item_dialog_data() {
            $response = array();
            $embedded_item_id = sanitize_text_field($_POST['_embedded_item_id']);
            $response['html_contain'] = $this->display_embedded_item_dialog($embedded_item_id);
            wp_send_json($response);
        }

        function set_embedded_item_dialog_data() {
            $embedded_id = sanitize_text_field($_POST['_embedded_id']);
            if( isset($_POST['_embedded_item_id']) ) {
                $embedded_item_id = sanitize_text_field($_POST['_embedded_item_id']);
                $field_note = isset($_POST['_field_note']) ? sanitize_text_field($_POST['_field_note']) : '';
                $field_type = isset($_POST['_field_type']) ? sanitize_text_field($_POST['_field_type']) : '';
                $default_value = isset($_POST['_default_value']) ? sanitize_text_field($_POST['_default_value']) : '';
                $embedded_doc = isset($_POST['_embedded_doc']) ? sanitize_text_field($_POST['_embedded_doc']) : '';
                $listing_style = isset($_POST['_listing_style']) ? sanitize_text_field($_POST['_listing_style']) : '';
                $data = array(
                    'ID'           => $embedded_item_id,
                    'post_title'   => isset($_POST['_embedded_item_title']) ? sanitize_text_field($_POST['_embedded_item_title']) : '',
                );
                wp_update_post( $data );
                update_post_meta($embedded_item_id, 'field_note', $field_note);
                update_post_meta($embedded_item_id, 'field_type', $field_type);
                update_post_meta($embedded_item_id, 'default_value', $default_value);
                update_post_meta($embedded_item_id, 'embedded_doc', $embedded_doc);
                update_post_meta($embedded_item_id, 'listing_style', $listing_style);
            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $new_post = array(
                    'post_type'     => 'doc-field',
                    'post_title'    => __( 'New Item', 'textdomain' ),
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'doc_id', $embedded_id);
                update_post_meta($post_id, 'field_type', 'text');
                update_post_meta($post_id, 'listing_style', 'center');
                update_post_meta($post_id, 'sorting_key', 999);
            }
            $response = array('html_contain' => $this->display_embedded_item_list($embedded_id));
            wp_send_json($response);
        }

        function del_embedded_item_dialog_data() {
            $embedded_id = sanitize_text_field($_POST['_embedded_id']);
            wp_delete_post($_POST['_embedded_item_id'], true);
            $response = array('html_contain' => $this->display_embedded_item_list($embedded_id));
            wp_send_json($response);
        }

        function sort_embedded_item_list_data() {
            $response = array('success' => false, 'error' => 'Invalid data format');
            if (isset($_POST['_embedded_item_id_array']) && is_array($_POST['_embedded_item_id_array'])) {
                $embedded_item_id_array = array_map('absint', $_POST['_embedded_item_id_array']);        
                foreach ($embedded_item_id_array as $index => $embedded_item_id) {
                    update_post_meta($embedded_item_id, 'sorting_key', $index);
                }
                $response = array('success' => true);
            }
            wp_send_json($response);
        }

        function get_embedded_item_keys($doc_id=false) {
            $_array = array();
            $documents_class = new display_documents();
            $query = $documents_class->retrieve_doc_field_data(array('doc_id' => $doc_id));
            if ($query->have_posts()) {
                while ($query->have_posts()) : $query->the_post();
                    $field_id = get_the_ID();
                    $field_type = get_post_meta($field_id, 'field_type', true);
                    $default_value = get_post_meta($field_id, 'default_value', true);
                    $embedded_doc = get_post_meta($field_id, 'embedded_doc', true);

                    if ($field_type=='_embedded' && $embedded_doc) {
                        if ($embedded_doc) {
                            $documents_class = new display_documents();
                            $inner_query = $documents_class->retrieve_doc_field_data(array('doc_id' => $embedded_doc));
                            if ($inner_query->have_posts()) :
                                while ($inner_query->have_posts()) : $inner_query->the_post();
                                    $embedded_item_id = get_the_ID();
                                    $_list = array();
                                    $_list["embedded_id"] = $embedded_doc;
                                    $_list["embedded_item_id"] = $embedded_item_id;
                                    $_list["field_type"] = get_post_meta($embedded_item_id, 'field_type', true);
                                    array_push($_array, $_list);
                                endwhile;
                                wp_reset_postdata();
                            endif;
                        }
                    }
                endwhile;
                wp_reset_postdata();
            }    
            return $_array;
        }

        // line-report
        function display_line_report_list($embedded_id=false, $report_id=false) {
            ob_start();
            if ($report_id) $todo_status = get_post_meta($report_id, 'todo_status', true);
            else $report_id=$embedded_id;
            ?>
            <input type="hidden" id="embedded-id" value="<?php echo esc_attr($embedded_id);?>" />
            <input type="hidden" id="report-id" value="<?php echo esc_attr($report_id);?>" />
            <fieldset>
            <table style="width:100%;">
                <thead>
                <tr>
                <?php
                $documents_class = new display_documents();
                $query = $documents_class->retrieve_doc_field_data(array('doc_id' => $embedded_id));
                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post();
                        ?>
                        <th><?php the_title();?></th>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
                </tr>
                </thead>

                <tbody>
                <?php
                $line_report_query = $this->retrieve_line_report_data($report_id);
                if ($line_report_query->have_posts()) :
                    while ($line_report_query->have_posts()) : $line_report_query->the_post();
                        $line_report_id = get_the_ID();
                        if ($todo_status) {
                            ?><tr><?php
                        } else {
                            ?><tr id="edit-line-report-<?php echo $line_report_id;?>"><?php
                        }

                        $documents_class = new display_documents();
                        $params = array(
                            'embedded_doc_id' => $report_id,
                            'todo_id' => $line_report_id,
                        );                
                        $documents_class->get_doc_field_contain_list($params);
                        ?></tr><?php
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
                </tbody>
            </table>
            <?php if (is_site_admin() && !$todo_status) {?>
                <div id="new-line-report" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
            <?php }?>
            </fieldset>
            <div id="line-report-dialog" title="line dialog"></div>
            <?php
            return ob_get_clean();
        }

        function retrieve_line_report_data($report_id=false) {
            $args = array(
                'post_type'      => 'line-report',
                'posts_per_page' => -1,
                'meta_query'     => array(
                    array(
                        'key'   => 'report_id',
                        'value' => $report_id,
                    ),
                ),
            );
            $query = new WP_Query($args);
            return $query;
        }

        function display_line_report_dialog($line_report_id=false, $embedded_id=false) {
            ob_start();
            $report_id = get_post_meta($line_report_id, 'report_id', true);
            $report_id = ($report_id) ? $report_id : $embedded_id;
            ?>
            <fieldset>
                <input type="hidden" id="line-report-id" value="<?php echo esc_attr($line_report_id);?>" />
                <input type="hidden" id="is-site-admin" value="<?php echo esc_attr(is_site_admin());?>" />
                <?php
                $documents_class = new display_documents();
                $params = array(
                    'embedded_doc_id' => $report_id,
                    'todo_id' => $line_report_id,
                );                
                $documents_class->get_doc_field_contains($params);
                ?>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_line_report_dialog_data() {
            $response = array();
            $line_report_id = (isset($_POST['_line_report_id'])) ? sanitize_text_field($_POST['_line_report_id']) : 0;
            $embedded_id = (isset($_POST['_embedded_id'])) ? sanitize_text_field($_POST['_embedded_id']) : 0;
            $response['html_contain'] = $this->display_line_report_dialog($line_report_id, $embedded_id);
            $response['line_report_fields'] = $this->get_line_report_field_keys($embedded_id);
            wp_send_json($response);
        }

        function set_line_report_dialog_data() {
            $embedded_id = (isset($_POST['_embedded_id'])) ? sanitize_text_field($_POST['_embedded_id']) : 0;
            if( isset($_POST['_line_report_id']) ) {
                $line_report_id = sanitize_text_field($_POST['_line_report_id']);
            } else {
                // Create the post
                $new_post = array(
                    'post_type'     => 'line-report',
                    'post_status'   => 'publish',
                    'post_author'   => get_current_user_id(),
                );    
                $line_report_id = wp_insert_post($new_post);
                update_post_meta($line_report_id, 'report_id', $embedded_id);
            }

            // Update the post
            //$query = $this->retrieve_embedded_item_data($embedded_id, 0);
            $documents_class = new display_documents();
            $query = $documents_class->retrieve_doc_field_data(array('doc_id' => $embedded_id));
            if ($query->have_posts()) :
                while ($query->have_posts()) : $query->the_post();
                    $embedded_item_id = get_the_ID();
                    $field_value = $_POST[$embedded_item_id];
                    update_post_meta($line_report_id, $embedded_item_id, $field_value);
                endwhile;
                wp_reset_postdata();
            endif;

            $response = array('html_contain' => $this->display_line_report_list($embedded_id));
            wp_send_json($response);
        }

        function del_line_report_dialog_data() {
            wp_delete_post($_POST['_line_report_id'], true);
            $embedded_id = (isset($_POST['_embedded_id'])) ? sanitize_text_field($_POST['_embedded_id']) : 0;
            $response = array('html_contain' => $this->display_line_report_list($embedded_id));
            wp_send_json($response);
        }

        function get_line_report_field_keys($embedded_id=false) {
            $_array = array();
            $documents_class = new display_documents();
            $query = $documents_class->retrieve_doc_field_data(array('doc_id' => $embedded_id));
            if ($query->have_posts()) :
                while ($query->have_posts()) : $query->the_post();
                    $embedded_item_id = get_the_ID();
                    $_list = array();
                    $_list["embedded_item_id"] = $embedded_item_id;
                    $_list["field_type"] = get_post_meta($embedded_item_id, 'field_type', true);
                    array_push($_array, $_list);

                endwhile;
                wp_reset_postdata();
            endif;
            return $_array;
        }

        // doc-category
        function register_doc_category_post_type() {
            $labels = array(
                'menu_name'     => _x('doc-category', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
            );
            register_post_type( 'doc-category', $args );
        }
        
        function display_doc_category_list() {
            ob_start();
            $profiles_class = new display_profiles();
            ?>
            <div class="ui-widget" id="result-container">
            <?php echo display_iso_helper_logo();?>
            <h2 style="display:inline;"><?php echo __( 'Categories', 'textdomain' );?></h2>

            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div><?php $profiles_class->display_select_profile('doc-category');?></div>
                <div style="text-align: right"></div>                        
            </div>

            <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <th><?php echo __( 'Category', 'textdomain' );?></th>
                        <th><?php echo __( 'Description', 'textdomain' );?></th>
                        <th><?php echo __( 'ISO', 'textdomain' );?></th>
                    </thead>
                    <tbody>
                    <?php
                    $query = $this->retrieve_doc_category_data();
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $category_id = get_the_ID();
                            $category_title = get_the_title();
                            $category_content = get_the_content();
                            $iso_standard = get_post_meta($category_id, 'iso_standard', true);
                            $iso_title = get_the_title($iso_standard);
                            ?>
                            <tr id="edit-doc-category-<?php echo $category_id;?>">
                                <td style="text-align:center;"><?php echo $category_title;?></td>
                                <td><?php echo $category_content;?></td>
                                <td style="text-align:center;"><?php echo $iso_title;?></td>
                            </tr>
                            <?php 
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
                <?php if (is_site_admin()) {?>
                    <div id="new-doc-category" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                <?php }?>
            </fieldset>
            </div>
            <div id="doc-category-dialog" title="Category dialog"></div>
            <?php
            return ob_get_clean();
        }

        function retrieve_doc_category_data($is_connector = false) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
        
            $meta_query = array(
                'relation' => 'OR',
                array(
                    'relation' => 'AND',
                    array( // username not set or empty
                        'relation' => 'OR',
                        array(
                            'key'     => 'api_username',
                            'compare' => 'NOT EXISTS',
                        ),
                        array(
                            'key'     => 'api_username',
                            'value'   => '',
                            'compare' => '=',
                        ),
                    ),
                    array( // password not set or empty
                        'relation' => 'OR',
                        array(
                            'key'     => 'api_password',
                            'compare' => 'NOT EXISTS',
                        ),
                        array(
                            'key'     => 'api_password',
                            'value'   => '',
                            'compare' => '=',
                        ),
                    ),
                    array(
                        'key'     => 'site_id',
                        'value'   => $site_id,
                    ),
                ),
            );
        
            if (current_user_can('administrator') || $is_connector) {
                $meta_query[] = array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'api_username',
                        'compare' => 'EXISTS',
                    ),
                    array(
                        'key'     => 'api_password',
                        'compare' => 'EXISTS',
                    ),
                );
            }
        
            $args = array(
                'post_type'      => 'doc-category',
                'posts_per_page' => -1,
                'meta_query'     => $meta_query,
                'orderby'        => 'title',
                'order'          => 'ASC',
            );
        
            return new WP_Query($args);
        }

        function display_doc_category_dialog($category_id=false) {
            ob_start();
            $category_title = get_the_title($category_id);
            $category_content = get_post_field('post_content', $category_id);
            $iso_standard = get_post_meta($category_id, 'iso_standard', true);
            $api_username = get_post_meta($category_id, 'api_username', true);
            $api_password = get_post_meta($category_id, 'api_password', true);
            ?>
            <fieldset>
                <input type="hidden" id="category-id" value="<?php echo esc_attr($category_id);?>" />
                <input type="hidden" id="is-site-admin" value="<?php echo esc_attr(is_site_admin());?>" />
                <label for="category-title"><?php echo __( 'Category', 'textdomain' );?></label>
                <input type="text" id="category-title" value="<?php echo esc_attr($category_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="category-content"><?php echo __( 'Description', 'textdomain' );?></label>
                <textarea id="category-content" rows="5" style="width:100%;"><?php echo esc_html($category_content);?></textarea>
                <label for="iso-standard"><?php echo __( 'ISO', 'textdomain' );?></label>
                <select id="iso-standard" class="select ui-widget-content ui-corner-all"><?php echo $this->select_iso_standard_options($iso_standard);?></select>
                <?php if (current_user_can('administrator')) {?>
                    <label for="api-username"><?php echo __( 'API username', 'textdomain' );?></label>
                    <input type="text" id="api-username" value="<?php echo esc_attr($api_username);?>" class="text ui-widget-content ui-corner-all" />
                    <label for="api-password"><?php echo __( 'API password', 'textdomain' );?></label>
                    <input type="password" id="api-password" value="<?php echo esc_attr($api_password);?>" class="text ui-widget-content ui-corner-all" />
                <?php }?>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_doc_category_dialog_data() {
            $response = array();
            $category_id = sanitize_text_field($_POST['_category_id']);
            $response['html_contain'] = $this->display_doc_category_dialog($category_id);
            wp_send_json($response);
        }

        function set_doc_category_dialog_data() {
            if( isset($_POST['_category_id']) ) {
                $category_id = (isset($_POST['_category_id'])) ? sanitize_text_field($_POST['_category_id']) : 0;
                $category_title = (isset($_POST['_category_title'])) ? sanitize_text_field($_POST['_category_title']) : '';
                $api_username = (isset($_POST['_api_username'])) ? sanitize_text_field($_POST['_api_username']) : '';
                $api_password = (isset($_POST['_api_password'])) ? sanitize_text_field($_POST['_api_password']) : '';
                $iso_standard = (isset($_POST['_iso_standard'])) ? sanitize_text_field($_POST['_iso_standard']) : 0;
                $data = array(
                    'ID'           => $category_id,
                    'post_title'   => $category_title,
                    'post_content' => $_POST['_category_content'],
                );
                wp_update_post( $data );
                update_post_meta($category_id, 'iso_standard', $iso_standard);
                update_post_meta($category_id, 'api_username', $api_username);
                update_post_meta($category_id, 'api_password', $api_password);

                $params = array(
                    'log_message' => sprintf(
                        __( 'Category %s has been updated.', 'textdomain' ),
                        $category_title
                    ),                    
                    'action_title' => 'Update',
                    'category_id' => $category_id,
                );
                $todo_class = new to_do_list();
                $todo_class->set_transaction_log($params);    

            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $new_post = array(
                    'post_type'     => 'doc-category',
                    'post_title'    => '-',
                    'post_content'  => __( 'Your post content goes here.', 'textdomain' ),
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'site_id', $site_id);
            }
            $response = array('html_contain' => $this->display_doc_category_list());
            wp_send_json($response);
        }

        function del_doc_category_dialog_data() {
            $category_id = isset($_POST['_category_id']) ? sanitize_text_field($_POST['_category_id']) : 0;
            $params = array(
                'log_message' => sprintf(
                    __( 'Category %s has been deleted.', 'textdomain' ),
                    get_the_title($category_id)
                ),                
                'action_title' => 'Delete',
                'category_id' => $category_id,
            );
            $todo_class = new to_do_list();
            $todo_class->set_transaction_log($params);    

            wp_delete_post($_POST['_category_id'], true);
            $response = array('html_contain' => $this->display_doc_category_list());
            wp_send_json($response);
        }

        function select_doc_category_options($selected_option=false, $is_connector=false) {
            $query = $this->retrieve_doc_category_data($is_connector);
            $options = '<option value="">'.__( 'Select Option', 'textdomain' ).'</option>';
            while ($query->have_posts()) : $query->the_post();
                $category_id = get_the_ID();
                $category_title = get_the_title();
                $selected = ($selected_option == $category_id) ? 'selected' : '';
                $options .= '<option value="' . esc_attr($category_id) . '" '.$selected.' >' . esc_html($category_title) . '</option>';
            endwhile;
            wp_reset_postdata();
            if (!$is_connector) {
                $selected = ($selected_option=="embedded") ? 'selected' : '';
                $options .= '<option value="embedded" '.$selected.'>'.__( 'Embedded Items', 'textdomain' ).'</option>';
                $selected = ($selected_option=="doc-category") ? 'selected' : '';
                $options .= '<option value="doc-category" '.$selected.'>'.__( 'Categories', 'textdomain' ).'</option>';
            }
            return $options;
        }
        
        // iso-standard
        function register_iso_standard_post_type() {
            $labels = array(
                'menu_name'     => _x('iso-standard', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
            );
            register_post_type( 'iso-standard', $args );
        }
        
        function display_iso_standard_contains($atts) {
            ob_start();
            // Extract and sanitize the shortcode attributes
            $atts = shortcode_atts(array(
                'parent_category' => false,
            ), $atts);
        
            $parent_category = $atts['parent_category'];
        
            $meta_query = array(
                'relation' => 'OR',
            );
        
            if ($parent_category) {
                $meta_query[] = array(
                    'key'   => 'parent_category',
                    'value' => $parent_category,
                );
            }
        
            $args = array(
                'post_type'      => 'iso-standard',
                'posts_per_page' => -1,
                'meta_query'     => $meta_query,
            );
        
            $query = new WP_Query($args);
        
            while ($query->have_posts()) : $query->the_post();
                $category_id = get_the_ID();
                $category_url = get_post_meta($category_id, 'category_url', true);
                $embedded = get_post_meta($category_id, 'embedded', true);
                $start_ai_url = '/display-documents/?_start_ai=' . $category_id;
                ?>
                <div class="iso-standard-content">
                    <?php the_content(); ?>
                    <div class="wp-block-buttons">
                        <div class="wp-block-button">
                            <a class="wp-block-button__link wp-element-button" href="<?php echo esc_url($start_ai_url); ?>"><?php echo sprintf(__( 'Launch %s AI Coaching', 'textdomain' ), get_the_title()); ?></a>
                        </div>
                    </div>
                    <!-- Spacer -->
                    <div style="height: 20px;"></div> <!-- Adjust the height as needed -->
                </div>
                <?php
            endwhile;
            wp_reset_postdata();
            return ob_get_clean();
        }
                
        function display_iso_standard_list() {
            ob_start();
            $profiles_class = new display_profiles();
            if (current_user_can('administrator')) {
                ?>
                <?php echo display_iso_helper_logo();?>
                <h2 style="display:inline;"><?php echo __( 'ISO standard', 'textdomain' );?></h2>

                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div><?php $profiles_class->display_select_profile('iso-standard');?></div>
                    <div style="text-align:right"></div>                        
                </div>

                <fieldset>
                    <table class="ui-widget" style="width:100%;">
                        <thead>
                            <th><?php echo __( 'ISO', 'textdomain' );?></th>
                            <th><?php echo __( 'Description', 'textdomain' );?></th>
                            <th><?php echo __( 'Parent', 'textdomain' );?></th>
                        </thead>
                        <tbody>
                        <?php
                        $query = $this->retrieve_iso_standard_data();
                        if ($query->have_posts()) :
                            while ($query->have_posts()) : $query->the_post();
                                $category_id = get_the_ID();
                                $category_title = get_the_title();
                                $category_content = get_the_content();
                                $parent_category = get_post_meta($category_id, 'parent_category', true);
                                ?>
                                <tr id="edit-iso-standard-<?php echo $category_id;?>">
                                    <td style="text-align:center;"><?php echo $category_title;?></td>
                                    <td><?php echo $category_content;?></td>
                                    <td style="text-align:center;"><?php echo $parent_category;?></td>
                                </tr>
                                <?php 
                            endwhile;
                            wp_reset_postdata();
                        endif;
                        ?>
                        </tbody>
                    </table>
                    <div id="new-iso-standard" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                </fieldset>
                <div id="iso-standard-dialog" title="Category dialog"></div>
                <?php
            } else {
                ?>
                <p><?php echo __( 'You do not have permission to access this page.', 'textdomain' );?></p>
                <?php
            }
            return ob_get_clean();
        }

        function retrieve_iso_standard_data() {
            $args = array(
                'post_type'      => 'iso-standard',
                'posts_per_page' => -1,        
                'orderby'        => 'title',  // Order by post title
                'order'          => 'ASC',    // Order in ascending order (or use 'DESC' for descending)
            );
            $query = new WP_Query($args);
            return $query;
        }

        function display_iso_standard_dialog($category_id=false) {
            $category_title = get_the_title($category_id);
            $category_content = get_post_field('post_content', $category_id);
            $category_url = get_post_meta($category_id, 'category_url', true);
            $parent_category = get_post_meta($category_id, 'parent_category', true);
            ob_start();
            ?>
            <fieldset>
                <input type="hidden" id="category-id" value="<?php echo esc_attr($category_id);?>" />
                <label for="category-title"><?php echo __( 'Title', 'textdomain' );?></label>
                <input type="text" id="category-title" value="<?php echo esc_attr($category_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="category-content"><?php echo __( 'Description', 'textdomain' );?></label>
                <textarea id="category-content" rows="5" style="width:100%;"><?php echo esc_html($category_content);?></textarea>
<?php /*                
                <label for="category-url"><?php echo __( 'URL', 'textdomain' );?></label>
                <input type="text" id="category-url" value="<?php echo esc_attr($category_url);?>" class="text ui-widget-content ui-corner-all" />
*/ ?>
                <label for="parent-category"><?php echo __( 'Parent', 'textdomain' );?></label>
                <select id="parent-category" class="select ui-widget-content ui-corner-all"><?php echo $this->select_parent_category_options($parent_category);?></select>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_iso_standard_dialog_data() {
            $response = array();
            $category_id = sanitize_text_field($_POST['_category_id']);
            $response['html_contain'] = $this->display_iso_standard_dialog($category_id);
            wp_send_json($response);
        }

        function set_iso_standard_dialog_data() {
            if( isset($_POST['_category_id']) ) {
                $category_id = sanitize_text_field($_POST['_category_id']);
                $category_title = isset($_POST['_category_title']) ? sanitize_text_field($_POST['_category_title']) : '';
                //$category_url = isset($_POST['_category_url']) ? sanitize_text_field($_POST['_category_url']) : '';
                $parent_category = isset($_POST['_parent_category']) ? sanitize_text_field($_POST['_parent_category']) : '';
                $data = array(
                    'ID'           => $category_id,
                    'post_title'   => $category_title,
                    'post_content' => $_POST['_category_content'],
                );
                wp_update_post( $data );
                //update_post_meta($category_id, 'category_url', $category_url);
                update_post_meta($category_id, 'parent_category', $parent_category);
            } else {
                $current_user_id = get_current_user_id();
                $new_post = array(
                    'post_type'     => 'iso-standard',
                    'post_title'    => '-',
                    'post_content'  => __( 'Your post content goes here.', 'textdomain' ),
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                );    
                $post_id = wp_insert_post($new_post);
            }
            $response = array('html_contain' => $this->display_iso_standard_list());
            wp_send_json($response);
        }

        function del_iso_standard_dialog_data() {
            wp_delete_post($_POST['_category_id'], true);
            $response = array('html_contain' => $this->display_iso_standard_list());
            wp_send_json($response);
        }

        function select_iso_standard_options($selected_option=0) {
            $query = $this->retrieve_iso_standard_data();
            $options = '<option value="">'.__( 'Select Option', 'textdomain' ).'</option>';
            while ($query->have_posts()) : $query->the_post();
                $category_id = get_the_ID();
                $category_title = get_the_title();
                $selected = ($selected_option == $category_id) ? 'selected' : '';
                $options .= '<option value="' . esc_attr($category_id) . '" '.$selected.' >' . esc_html($category_title) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        function select_parent_category_options($selected_option=0) {
            $options = '<option value="">'.__( 'Select Option', 'textdomain' ).'</option>';
            $economic_selected = ($selected_option == 'economic-growth') ? 'selected' : '';
            $environmental_selected = ($selected_option == 'environmental-protection') ? 'selected' : '';
            $social_selected = ($selected_option == 'social-responsibility') ? 'selected' : '';
            $options .= '<option value="economic-growth" '.$economic_selected.'>' . __( 'Economic Growth', 'textdomain' ) . '</option>';
            $options .= '<option value="environmental-protection" '.$environmental_selected.'>' . __( 'environmental protection', 'textdomain' ) . '</option>';
            $options .= '<option value="social-responsibility" '.$social_selected.'>' . __( 'social responsibility', 'textdomain' ) . '</option>';    
            return $options;
        }

        function get_iso_standard_post_id_by_code($code) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            // Define the query arguments
            $args = array(
                'post_type'  => 'iso-standard',
                'meta_query' => array(
                    array(
                        'key'   => 'site_id',
                        'value' => $site_id,
                    ),
                    array(
                        'key'   => 'category_code',  // Meta key
                        'value' => $code,            // Meta value to match
                    ),
                ),
                'fields' => 'ids', // Return only post IDs
                'posts_per_page' => 1, // Limit to one post
            );
        
            // Run the query
            $query = new WP_Query($args);
        
            // Check if any posts are found
            if ($query->have_posts()) {
                // Get the first post ID
                $post_id = $query->posts[0];
                return $post_id;
            }
        
            // Return null if no matching post is found
            return null;
        }

        // department-card post
        function register_department_card_post_type() {
            $labels = array(
                'menu_name'     => _x('Department', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
            );
            register_post_type( 'department-card', $args );
        }

        function display_department_card_list() {
            ob_start();
            $profiles_class = new display_profiles();
            ?>
            <?php echo display_iso_helper_logo();?>
            <h2 style="display:inline;"><?php echo __( 'Departments', 'textdomain' );?></h2>

            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div><?php $profiles_class->display_select_profile('department-card');?></div>
                <div style="text-align:right; display:flex;">
                    <input type="text" id="search-department" style="display:inline" placeholder="<?php echo __( 'Search...', 'textdomain' );?>" />
                </div>
            </div>

            <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <th><?php echo __( 'No.', 'textdomain' );?></th>
                        <th><?php echo __( 'Title', 'textdomain' );?></th>
                        <th><?php echo __( 'Description', 'textdomain' );?></th>
                    </thead>
                    <tbody>
                    <?php
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_department_card_data($paged);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $department_id = get_the_ID();
                            $department_title = get_the_title();
                            $department_content = get_the_content();
                            $department_number = get_post_meta($department_id, 'department_number', true);
                            ?>
                            <tr id="edit-department-card-<?php echo $department_id;?>">
                                <td style="text-align:center;"><?php echo $department_number;?></td>
                                <td style="text-align:center;"><?php echo $department_title;?></td>
                                <td><?php echo $department_content;?></td>
                            </tr>
                            <?php 
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
                <?php if (is_site_admin()) {?>
                    <div id="new-department-card" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                <?php }?>
                <div class="pagination">
                    <?php
                    // Display pagination links
                    if ($paged > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged - 1)) . '"> < </a></span>';
                    echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $paged, $total_pages) . '</span>';
                    if ($paged < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged + 1)) . '"> > </a></span>';
                    ?>
                </div>

            </fieldset>
            <div id="department-card-dialog" title="Department dialog"></div>
            <?php
            return ob_get_clean();
        }

        function retrieve_department_card_data($paged = 1) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            
            $args = array(
                'post_type'      => 'department-card',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
                'meta_query'     => array(
                    array(
                        'key'   => 'site_id',
                        'value' => $site_id,
                    ),
                ),
                'meta_key'       => 'department_number', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value
                'order'          => 'ASC', // Sorting order (ascending)
            );
        
            if ($paged == 0) {
                $args['posts_per_page'] = -1; // Retrieve all posts if $paged is 0
            }
        
            // Sanitize and handle search query
            $search_query = isset($_GET['_search']) ? sanitize_text_field($_GET['_search']) : '';
            if (!empty($search_query)) {
                $args['s'] = $search_query;
            }
            $query = new WP_Query($args);
        
            // Check if query is empty and search query is not empty
            if (!$query->have_posts() && !empty($search_query)) {
                // Remove the initial search query
                unset($args['s']);
                // Add meta query for searching across all meta keys
                $meta_query_all_keys = array('relation' => 'OR');
                $meta_keys = get_post_type_meta_keys('department-card');
                foreach ($meta_keys as $meta_key) {
                    $meta_query_all_keys[] = array(
                        'key'     => $meta_key,
                        'value'   => $search_query,
                        'compare' => 'LIKE',
                    );
                }
                $args['meta_query'][] = $meta_query_all_keys;
                $query = new WP_Query($args);
            }
        
            return $query;
        }

        function display_department_card_dialog($department_id=false) {
            ob_start();
            $department_number = get_post_meta($department_id, 'department_number', true);
            $department_title = get_the_title($department_id);
            $department_content = get_post_field('post_content', $department_id);
            ?>
            <fieldset>
                <input type="hidden" id="department-id" value="<?php echo esc_attr($department_id);?>" />
                <input type="hidden" id="is-site-admin" value="<?php echo esc_attr(is_site_admin());?>" />
                <label for="department-number"><?php echo __( 'No.', 'textdomain' );?></label>
                <input type="text" id="department-number" value="<?php echo esc_attr($department_number);?>" class="text ui-widget-content ui-corner-all" />
                <label for="department-title"><?php echo __( 'Title', 'textdomain' );?></label>
                <input type="text" id="department-title" value="<?php echo esc_attr($department_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="department-content"><?php echo __( 'Description', 'textdomain' );?></label>
                <textarea id="department-content" rows="3" style="width:100%;"><?php echo esc_html($department_content);?></textarea>
                <label for="department-members"><?php echo __( 'Department Members', 'textdomain' );?></label>
                <?php echo $this->display_department_user_list($department_id);?>
                <?php
                // transaction data vs key/value
                $key_value_pair = array(
                    '_department'   => $department_id,
                );
                $documents_class = new display_documents();
                $documents_class->display_transaction_report_for_master($key_value_pair);
                ?>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_department_card_dialog_data() {
            $department_id = sanitize_text_field($_POST['_department_id']);
            $response = array('html_contain' => $this->display_department_card_dialog($department_id));
            wp_send_json($response);
        }

        function set_department_card_dialog_data() {
            if( isset($_POST['_department_id']) ) {
                $department_id = (isset($_POST['_department_id'])) ? sanitize_text_field($_POST['_department_id']) : 0;
                $department_title = (isset($_POST['_department_title'])) ? sanitize_text_field($_POST['_department_title']) : '';
                $department_number = (isset($_POST['_department_number'])) ? sanitize_text_field($_POST['_department_number']) : '';
                $data = array(
                    'ID'           => $department_id,
                    'post_title'   => $department_title,
                    'post_content' => $_POST['_department_content'],
                );
                wp_update_post( $data );
                update_post_meta($department_id, 'department_number', $department_number);

                $params = array(
                    'log_message' => sprintf(
                        __( 'Department %s has been updated.', 'textdomain' ),
                        $department_title
                    ),                    
                    'action_title' => 'Update',
                    'department_id' => $department_id,
                );
                $todo_class = new to_do_list();
                $todo_class->set_transaction_log($params);    

            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $new_post = array(
                    'post_title'    => __( 'New Department', 'textdomain' ),
                    'post_content'  => __( 'Your post content goes here.', 'textdomain' ),
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'department-card',
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'site_id', $site_id);
                update_post_meta($post_id, 'department_number', time());
            }
            $response = array('html_contain' => $this->display_department_card_list());
            wp_send_json($response);
        }

        function del_department_card_dialog_data() {
            $department_id = (isset($_POST['_department_id'])) ? sanitize_text_field($_POST['_department_id']) : 0;
            $params = array(
                'log_message' => sprintf(
                    __( 'Department %s has been deleted.', 'textdomain' ),
                    get_the_title($department_id)
                ),                
                'action_title' => 'Delete',
                'department_id' => $department_id,
            );
            $todo_class = new to_do_list();
            $todo_class->set_transaction_log($params);    

            wp_delete_post($_POST['_department_id'], true);
            $response = array('html_contain' => $this->display_department_card_list());
            wp_send_json($response);
        }

        function select_department_card_options($selected_option=0) {
            $query = $this->retrieve_department_card_data(0);
            $options = '<option value="">'.__( 'Select Option', 'textdomain' ).'</option>';
            while ($query->have_posts()) : $query->the_post();
                $department_id = get_the_ID();
                $department_title = get_the_title();
                $selected = ($selected_option == $department_id) ? 'selected' : '';
                $options .= '<option value="' . esc_attr($department_id) . '" '.$selected.' >' . esc_html($department_title) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        function display_department_user_list($department_id=false) {
            ob_start();
            $user_ids = array();            
            if ($department_id==false) {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                    $meta_query_args = array(
                    array(
                        'key'     => 'site_id',
                        'value'   => $site_id,
                    ),
                );
                $users = get_users(array('meta_query' => $meta_query_args));
                foreach ($users as $user) {
                    $user_ids[] = $user->ID;
                }    
            } else {
                $user_ids = get_post_meta($department_id, 'user_ids', true);
            }
            ?>
            <div id="department-user-list">
                <fieldset style="margin-top:5px;">
                    <table class="ui-widget" style="width:100%;">
                        <thead>
                            <th><?php echo __( 'Name', 'textdomain' );?></th>
                            <th><?php echo __( 'Email', 'textdomain' );?></th>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($user_ids as $user_id) {
                            $user_data = get_userdata($user_id);
                            ?>
                            <tr id="edit-department-user-<?php echo $user_id; ?>">
                                <td style="text-align:center;"><?php echo $user_data->display_name; ?></td>
                                <td style="text-align:center;"><?php echo $user_data->user_email; ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                    <div id="new-department-user" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                </fieldset>
            </div>
            <div id="department-user-dialog" title="User dialog"></div>
            <?php
            return ob_get_clean();
        }

        function get_department_user_list_data() {
            $response = array('html_contain' => $this->display_department_user_list());
            wp_send_json($response);
        }

        function add_department_user_dialog_data() {
            $response = array();
        
            // Check if both _user_id and _department_id are set and valid
            if (isset($_POST['_user_id']) && isset($_POST['_department_id'])) {
                $user_id = absint($_POST['_user_id']);
                $department_id = absint($_POST['_department_id']);
        
                // Retrieve the current user_ids meta value
                $user_ids = get_post_meta($department_id, 'user_ids', true);
        
                // If there are no user_ids, initialize an empty array
                if (!$user_ids) {
                    $user_ids = array();
                }
        
                // Check if the user_id is not already in the user_ids array
                if (!in_array($user_id, $user_ids)) {
                    // Add the user_id to the user_ids array
                    $user_ids[] = $user_id;
        
                    // Update the user_ids meta value
                    update_post_meta($department_id, 'user_ids', $user_ids);
        
                    $response['success'] = true;
                    $response['message'] = 'User ID added successfully.';
                } else {
                    $response['success'] = false;
                    $response['message'] = 'User ID already exists.';
                }
            } else {
                $response['success'] = false;
                $response['message'] = 'Invalid user ID or department ID.';
            }
        
            $department_id = absint($_POST['_department_id']);
            $response = array('html_contain' => $this->display_department_user_list($department_id));
            wp_send_json($response);
        }

        function del_department_user_dialog_data() {
            $response = array();
        
            // Check if both _user_id and _department_id are set and valid
            if (isset($_POST['_user_id']) && isset($_POST['_department_id'])) {
                $user_id = absint($_POST['_user_id']);
                $department_id = absint($_POST['_department_id']);
        
                // Retrieve the current user_ids meta value
                $user_ids = get_post_meta($department_id, 'user_ids', true);
        
                // If there are no user_ids, initialize an empty array
                if (!$user_ids) {
                    $user_ids = array();
                }
        
                // Check if the user_id is in the user_ids array
                if (in_array($user_id, $user_ids)) {
                    // Remove the user_id from the user_ids array
                    $user_ids = array_diff($user_ids, array($user_id));
        
                    // Update the user_ids meta value
                    update_post_meta($department_id, 'user_ids', $user_ids);
        
                    $response['success'] = true;
                    $response['message'] = 'User ID removed successfully.';
                } else {
                    $response['success'] = false;
                    $response['message'] = 'User ID does not exist.';
                }
            } else {
                $response['success'] = false;
                $response['message'] = 'Invalid user ID or department ID.';
            }
        
            $department_id = absint($_POST['_department_id']);
            $response = array('html_contain' => $this->display_department_user_list($department_id));
            wp_send_json($response);
        }
    }
    $items_class = new embedded_items();
}