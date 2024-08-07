<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (!class_exists('erp_cards')) {
    class erp_cards {
        // Class constructor
        public function __construct() {
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_erp_cards_scripts' ) );
            add_action( 'init', array( $this, 'register_iso_category_post_type' ) );
            add_action( 'init', array( $this, 'register_audit_item_post_type' ) );
            add_action( 'init', array( $this, 'register_customer_card_post_type' ) );
            add_action( 'init', array( $this, 'register_vendor_card_post_type' ) );
            add_action( 'init', array( $this, 'register_product_card_post_type' ) );
            add_action( 'init', array( $this, 'register_equipment_card_post_type' ) );
            add_action( 'init', array( $this, 'register_instrument_card_post_type' ) );
            add_action( 'init', array( $this, 'register_department_card_post_type' ) );

            add_action( 'wp_ajax_get_iso_category_dialog_data', array( $this, 'get_iso_category_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_iso_category_dialog_data', array( $this, 'get_iso_category_dialog_data' ) );
            add_action( 'wp_ajax_set_iso_category_dialog_data', array( $this, 'set_iso_category_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_iso_category_dialog_data', array( $this, 'set_iso_category_dialog_data' ) );
            add_action( 'wp_ajax_del_iso_category_dialog_data', array( $this, 'del_iso_category_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_iso_category_dialog_data', array( $this, 'del_iso_category_dialog_data' ) );

            add_action( 'wp_ajax_get_audit_item_dialog_data', array( $this, 'get_audit_item_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_audit_item_dialog_data', array( $this, 'get_audit_item_dialog_data' ) );
            add_action( 'wp_ajax_set_audit_item_dialog_data', array( $this, 'set_audit_item_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_audit_item_dialog_data', array( $this, 'set_audit_item_dialog_data' ) );
            add_action( 'wp_ajax_del_audit_item_dialog_data', array( $this, 'del_audit_item_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_audit_item_dialog_data', array( $this, 'del_audit_item_dialog_data' ) );

            add_action( 'wp_ajax_sort_audit_item_list_data', array( $this, 'sort_audit_item_list_data' ) );
            add_action( 'wp_ajax_nopriv_sort_audit_item_list_data', array( $this, 'sort_audit_item_list_data' ) );

            add_action( 'wp_ajax_get_customer_card_dialog_data', array( $this, 'get_customer_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_customer_card_dialog_data', array( $this, 'get_customer_card_dialog_data' ) );
            add_action( 'wp_ajax_set_customer_card_dialog_data', array( $this, 'set_customer_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_customer_card_dialog_data', array( $this, 'set_customer_card_dialog_data' ) );
            add_action( 'wp_ajax_del_customer_card_dialog_data', array( $this, 'del_customer_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_customer_card_dialog_data', array( $this, 'del_customer_card_dialog_data' ) );

            add_action( 'wp_ajax_get_vendor_card_dialog_data', array( $this, 'get_vendor_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_vendor_card_dialog_data', array( $this, 'get_vendor_card_dialog_data' ) );
            add_action( 'wp_ajax_set_vendor_card_dialog_data', array( $this, 'set_vendor_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_vendor_card_dialog_data', array( $this, 'set_vendor_card_dialog_data' ) );
            add_action( 'wp_ajax_del_vendor_card_dialog_data', array( $this, 'del_vendor_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_vendor_card_dialog_data', array( $this, 'del_vendor_card_dialog_data' ) );

            add_action( 'wp_ajax_get_product_card_dialog_data', array( $this, 'get_product_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_product_card_dialog_data', array( $this, 'get_product_card_dialog_data' ) );
            add_action( 'wp_ajax_set_product_card_dialog_data', array( $this, 'set_product_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_product_card_dialog_data', array( $this, 'set_product_card_dialog_data' ) );
            add_action( 'wp_ajax_del_product_card_dialog_data', array( $this, 'del_product_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_product_card_dialog_data', array( $this, 'del_product_card_dialog_data' ) );

            add_action( 'wp_ajax_get_equipment_card_dialog_data', array( $this, 'get_equipment_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_equipment_card_dialog_data', array( $this, 'get_equipment_card_dialog_data' ) );
            add_action( 'wp_ajax_set_equipment_card_dialog_data', array( $this, 'set_equipment_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_equipment_card_dialog_data', array( $this, 'set_equipment_card_dialog_data' ) );
            add_action( 'wp_ajax_del_equipment_card_dialog_data', array( $this, 'del_equipment_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_equipment_card_dialog_data', array( $this, 'del_equipment_card_dialog_data' ) );

            add_action( 'wp_ajax_get_instrument_card_dialog_data', array( $this, 'get_instrument_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_instrument_card_dialog_data', array( $this, 'get_instrument_card_dialog_data' ) );
            add_action( 'wp_ajax_set_instrument_card_dialog_data', array( $this, 'set_instrument_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_instrument_card_dialog_data', array( $this, 'set_instrument_card_dialog_data' ) );
            add_action( 'wp_ajax_del_instrument_card_dialog_data', array( $this, 'del_instrument_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_instrument_card_dialog_data', array( $this, 'del_instrument_card_dialog_data' ) );

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

        function enqueue_erp_cards_scripts() {
            wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', '', '1.13.2');
            wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), null, true);
            $version = time(); // Update this version number when you make changes
            wp_enqueue_script('erp-cards', plugins_url('erp-cards.js', __FILE__), array('jquery'), $version);
            wp_localize_script('erp-cards', 'ajax_object', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('erp-cards-nonce'), // Generate nonce
            ));                
        }

        function copy_doc_category_to_iso_category() {
            // Define the categories to match
            $parent_categories = array('economic-growth', 'environmental-protection', 'social-responsibility');
        
            // Retrieve the posts of type 'doc-category' with the specified 'parent-category' meta values
            $args = array(
                'post_type'      => 'doc-category',
                'posts_per_page' => -1,
                'meta_query'     => array(
                    array(
                        'key'     => 'parent_category',
                        'value'   => $parent_categories,
                        'compare' => 'IN',
                    ),
                ),
            );
        
            $query = new WP_Query($args);
        
            // Create an array to map old parent_category values to new iso-category IDs
            $category_mapping = array();
        
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
        
                    // Get the current post ID and data
                    $current_post_id = get_the_ID();
                    $current_post    = get_post($current_post_id);
        
                    // Prepare the new post data
                    $new_post = array(
                        'post_title'    => $current_post->post_title,
                        'post_content'  => $current_post->post_content,
                        'post_status'   => 'publish', // or $current_post->post_status if you want to keep the same status
                        'post_author'   => $current_post->post_author,
                        'post_type'     => 'iso-category',
                        'post_date'     => $current_post->post_date,
                        'post_date_gmt' => $current_post->post_date_gmt,
                    );
        
                    // Insert the new post and get the new post ID
                    $new_post_id = wp_insert_post($new_post);
        
                    if ($new_post_id) {
                        // Get all meta data for the current post
                        $post_meta = get_post_meta($current_post_id);
        
                        // Copy each meta field to the new post
                        foreach ($post_meta as $meta_key => $meta_values) {
                            foreach ($meta_values as $meta_value) {
                                add_post_meta($new_post_id, $meta_key, $meta_value);
                            }
                        }
        
                        // Map the old parent_category value to the new iso-category post ID
                        $category_mapping[$current_post_id] = $new_post_id;
                    }
                }
        
                // Reset post data
                wp_reset_postdata();
            }
        
            // Update audit-item posts
            $audit_items = get_posts(array(
                'post_type' => 'audit-item',
                'posts_per_page' => -1,
            ));
        
            foreach ($audit_items as $audit_item) {
                $audit_item_id = $audit_item->ID;
                $current_category_id = get_post_meta($audit_item_id, 'category_id', true);
        
                if (isset($category_mapping[$current_category_id])) {
                    update_post_meta($audit_item_id, 'category_id', $category_mapping[$current_category_id]);
                }
            }
        }
        
        // iso-category
        function register_iso_category_post_type() {
            $labels = array(
                'menu_name'     => _x('iso-category', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
                'show_in_menu'  => false,
            );
            register_post_type( 'iso-category', $args );
        }
        
        function display_iso_category_list() {
            $profiles_class = new display_profiles();
            $is_site_admin = $profiles_class->is_site_admin();
            if (current_user_can('administrator')) {
                ob_start();
                // Check if the user is administrator
                ?>
                <?php echo display_iso_helper_logo();?>
                <h2 style="display:inline;"><?php echo __( 'ISO類別', 'your-text-domain' );?></h2>

                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div><?php $profiles_class->display_select_profile();?></div>
                    <div style="text-align:right"></div>                        
                </div>

                <fieldset>
                    <table class="ui-widget" style="width:100%;">
                        <thead>
                            <th><?php echo __( 'Category', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Description', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Parent', 'your-text-domain' );?></th>
                        </thead>
                        <tbody>
                        <?php
                        $query = $this->retrieve_iso_category_data();
                        if ($query->have_posts()) :
                            while ($query->have_posts()) : $query->the_post();
                                $category_url = get_post_meta(get_the_ID(), 'category_url', true);
                                $parent_category = get_post_meta(get_the_ID(), 'parent_category', true);
                                ?>
                                <tr id="edit-iso-category-<?php the_ID();?>">
                                    <td style="text-align:center;"><?php the_title();?></td>
                                    <td><?php the_content();?></td>
                                    <td style="text-align:center;"><?php echo $parent_category;?></td>
                                </tr>
                                <?php 
                            endwhile;
                            wp_reset_postdata();
                        endif;
                        ?>
                        </tbody>
                    </table>
                    <div id="new-iso-category" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                </fieldset>
                <div id="iso-category-dialog" title="Category dialog"></div>
                <?php
                return ob_get_clean();        
            } else {
                display_no_permission_page();
            }
        }

        function retrieve_iso_category_data() {
            $args = array(
                'post_type'      => 'iso-category',
                'posts_per_page' => -1,        
            );
            $query = new WP_Query($args);
            return $query;
        }

        function display_iso_category_dialog($paged=1, $category_id=false) {
            $category_title = get_the_title($category_id);
            $category_content = get_post_field('post_content', $category_id);
            $category_url = get_post_meta($category_id, 'category_url', true);
            $parent_category = get_post_meta($category_id, 'parent_category', true);
            ob_start();
            ?>
            <fieldset>
                <input type="hidden" id="category-id" value="<?php echo esc_attr($category_id);?>" />
                <label for="category-title"><?php echo __( 'Category: ', 'your-text-domain' );?></label>
                <input type="text" id="category-title" value="<?php echo esc_attr($category_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="category-content"><?php echo __( 'Description: ', 'your-text-domain' );?></label>
                <textarea id="category-content" rows="5" style="width:100%;"><?php echo esc_html($category_content);?></textarea>
                <?php
                if (current_user_can('administrator')) {                    
                    ?>
                    <label for="audit-item-list"><?php echo __( 'Audit items: ', 'your-text-domain' );?></label>
                    <?php echo $this->display_audit_item_list($paged, $category_id);?>
                    <label for="category-url"><?php echo __( 'URL: ', 'your-text-domain' );?></label>
                    <input type="text" id="category-url" value="<?php echo esc_attr($category_url);?>" class="text ui-widget-content ui-corner-all" />
                    <?php
                }
                ?>
                <label for="parent-category"><?php echo __( 'Parent: ', 'your-text-domain' );?></label>
                <select id="parent-category" class="text ui-widget-content ui-corner-all"><?php echo $this->select_parent_category_options($parent_category);?></select>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_iso_category_dialog_data() {
            $response = array();
            $category_id = sanitize_text_field($_POST['_category_id']);
            $paged = sanitize_text_field($_POST['paged']);
            $response['html_contain'] = $this->display_iso_category_dialog($paged, $category_id);
            wp_send_json($response);
        }

        function set_iso_category_dialog_data() {
            if( isset($_POST['_category_id']) ) {
                $category_id = sanitize_text_field($_POST['_category_id']);
                $category_url = sanitize_text_field($_POST['_category_url']);
                $parent_category = sanitize_text_field($_POST['_parent_category']);
                $data = array(
                    'ID'           => $category_id,
                    'post_title'   => sanitize_text_field($_POST['_category_title']),
                    'post_content' => sanitize_text_field($_POST['_category_content']),
                );
                wp_update_post( $data );
                update_post_meta($category_id, 'category_url', $category_url);
                update_post_meta($category_id, 'parent_category', $parent_category);
            } else {
                $current_user_id = get_current_user_id();
                $new_post = array(
                    'post_title'    => 'New category',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'iso-category',
                );    
                $post_id = wp_insert_post($new_post);
            }
            $response = array('html_contain' => $this->display_iso_category_list());
            wp_send_json($response);
        }

        function del_iso_category_dialog_data() {
            wp_delete_post($_POST['_category_id'], true);
            $response = array('html_contain' => $this->display_iso_category_list());
            wp_send_json($response);
        }

        function select_iso_category_options($selected_option=0) {
            $query = $this->retrieve_iso_category_data();
            $options = '<option value="">Select iso category</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html(get_the_title()) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        function select_parent_category_options($selected_option=0) {
            $options = '<option value="">Select category</option>';
            $economic_selected = ($selected_option == 'economic-growth') ? 'selected' : '';
            $environmental_selected = ($selected_option == 'environmental-protection') ? 'selected' : '';
            $social_selected = ($selected_option == 'social-responsibility') ? 'selected' : '';
            $options .= '<option value="economic-growth" '.$economic_selected.'>' . __( 'Economic Growth', 'your-text-domain' ) . '</option>';
            $options .= '<option value="environmental-protection" '.$environmental_selected.'>' . __( 'environmental protection', 'your-text-domain' ) . '</option>';
            $options .= '<option value="social-responsibility" '.$social_selected.'>' . __( 'social responsibility', 'your-text-domain' ) . '</option>';    
            return $options;
        }

        // audit-item
        function register_audit_item_post_type() {
            $labels = array(
                'menu_name'     => _x('Clause', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
                'show_in_menu'  => false,
            );
            register_post_type( 'audit-item', $args );
        }

        function display_audit_item_list($paged=1, $category_id=false) {
            ob_start();
            ?>
            <div id="audit-item-list">
            <fieldset>
            <table style="width:100%;">
                <thead>
                    <tr>
                        <th><?php echo __( 'Type', 'your-text-domain' );?></th>
                        <th style="width:85%;"><?php echo __( 'Items', 'your-text-domain' );?></th>
                        <th><?php echo __( 'Clause', 'your-text-domain' );?></th>
                        <th><?php echo __( 'Report', 'your-text-domain' );?></th>
                    </tr>
                </thead>
                <tbody id="sortable-audit-item-list">
                <?php
                
                //$paged = max(1, get_query_var('paged')); // Get the current page number
                $query = $this->retrieve_audit_item_list_data($paged, $category_id);
                $total_posts = $query->found_posts;
                $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages

                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post();
                        $audit_item_title = get_the_title();
                        $audit_content = get_post_field('post_content', get_the_ID());
                        $clause_no = get_post_meta(get_the_ID(), 'clause_no', true);
                        $display_on_report_only = get_post_meta(get_the_ID(), 'display_on_report_only', true);
                        $is_checked = ($display_on_report_only) ? 'checked' : '';
                        $is_radio_option = get_post_meta(get_the_ID(), 'is_radio_option', true);
                        $field_type = get_post_meta(get_the_ID(), 'field_type', true);
                        if ($field_type=='heading') {
                            $audit_item_title = '<b>'.$audit_item_title.'</b>';
                            $clause_no = '<b>'.$clause_no.'</b>';
                            $field_type='';
                        }
                        ?>
                        <tr id="edit-audit-item-<?php the_ID();?>" data-audit-id="<?php echo esc_attr(get_the_ID());?>">
                            <td style="text-align:center;"><?php echo esc_html($field_type);?></td>
                            <td><?php echo $audit_item_title;?></td>
                            <td style="text-align:center;"><?php echo $clause_no;?></td>
                            <td style="text-align:center;"><input type="checkbox" <?php echo $is_checked;?> /></td>
                        </tr>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
                </tbody>
            </table>
            <div id="new-audit-item" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
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
            <div id="audit-item-dialog" title="Clause dialog"></div>
            <?php
            return ob_get_clean();
        }

        function retrieve_audit_item_list_data($paged=1, $category_id=false, $display_on_report_only=true) {
            $args = array(
                'post_type'      => 'audit-item',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
                'meta_query'     => array(),
                'meta_key'       => 'sorting_key',
                'orderby'        => 'meta_value_num', // Specify meta value as numeric
                'order'          => 'ASC', // Sorting order (ascending)
            );
        
            if ($paged == 0) {
                $args['posts_per_page'] = -1; // Retrieve all posts if $paged is 0
            }

            // Add category_id to meta_query if it is not false
            if ($category_id !== false) {
                $args['meta_query'][] = array(
                    array(
                        'key'   => 'category_id',
                        'value' => $category_id,
                    ),
                );
            }

            if ($display_on_report_only == false) {
                $args['meta_query'][] = array(
                    'relation' => 'OR',
                    array(
                        'key'     => 'display_on_report_only',
                        'value'   => '0',
                        'compare' => '='
                    ),
                    array(
                        'key'     => 'display_on_report_only',
                        'compare' => 'NOT EXISTS'
                    )
                );
            }

            $query = new WP_Query($args);
            return $query;
        }

        function display_audit_item_dialog($audit_id=false) {
            $category_id = get_post_meta($audit_id, 'category_id', true);
            $clause_no = get_post_meta($audit_id, 'clause_no', true);
            $audit_item_title = get_the_title($audit_id);
            $field_type = get_post_meta($audit_id, 'field_type', true);
            $audit_content = get_post_field('post_content', $audit_id);
            $display_on_report_only = get_post_meta($audit_id, 'display_on_report_only', true);
            $is_radio_option = get_post_meta($audit_id, 'is_radio_option', true);
            ob_start();
            ?>
            <fieldset>
                <input type="hidden" id="audit-id" value="<?php echo esc_attr($audit_id);?>" />
                <label for="audit-title"><?php echo __( 'Item: ', 'your-text-domain' );?></label>
                <input type="text" id="audit-title" value="<?php echo esc_attr($audit_item_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="field-type"><?php echo __( 'Type: ', 'your-text-domain' );?></label>
                <select id="field-type" class="text ui-widget-content ui-corner-all">
                    <option value="text" <?php echo ($field_type=='text') ? 'selected' : ''?>><?php echo __( 'Text', 'your-text-domain' );?></option>
                    <option value="radio" <?php echo ($field_type=='radio') ? 'selected' : ''?>><?php echo __( 'Radio', 'your-text-domain' );?></option>
                    <option value="heading" <?php echo ($field_type=='heading') ? 'selected' : ''?>><?php echo __( 'Heading', 'your-text-domain' );?></option>
                    <option value="textarea" <?php echo ($field_type=='textarea') ? 'selected' : ''?>><?php echo __( 'Textarea', 'your-text-domain' );?></option>
                </select>
                <label for="audit-content"><?php echo __( 'Description: ', 'your-text-domain' );?></label>
                <textarea id="audit-content" rows="3" style="width:100%;"><?php echo esc_textarea($audit_content);?></textarea>
                <label for="clause-no"><?php echo __( 'Clause No: ', 'your-text-domain' );?></label>
                <input type="text" id="clause-no" value="<?php echo esc_attr($clause_no);?>" class="text ui-widget-content ui-corner-all" />
                <input type="checkbox" id="is-report-only" <?php echo ($display_on_report_only) ? 'checked' : '';?> />
                <label for="is-report-only"><?php echo __( 'Display on report only', 'your-text-domain' );?></label>
                <input type="checkbox" id="is-checkbox" <?php echo ($is_radio_option) ? 'checked' : '';?> />
                <label for="is-checkbox"><?php echo __( 'Is radio option', 'your-text-domain' );?></label>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_audit_item_dialog_data() {
            $response = array();
            $audit_id = sanitize_text_field($_POST['_audit_id']);
            $response['html_contain'] = $this->display_audit_item_dialog($audit_id);
            wp_send_json($response);
        }

        function set_audit_item_dialog_data() {
            $category_id = sanitize_text_field($_POST['_category_id']);
            if( isset($_POST['_audit_id']) ) {
                $audit_id = sanitize_text_field($_POST['_audit_id']);
                $clause_no = sanitize_text_field($_POST['_clause_no']);
                $field_type = sanitize_text_field($_POST['_field_type']);
                $display_on_report_only = sanitize_text_field($_POST['_display_on_report_only']);
                $is_radio_option = sanitize_text_field($_POST['_is_radio_option']);
                $data = array(
                    'ID'           => $audit_id,
                    'post_title'   => sanitize_text_field($_POST['_audit_title']),
                    'post_content' => sanitize_textarea_field($_POST['_audit_content']),
                );
                wp_update_post( $data );
                update_post_meta($audit_id, 'category_id', $category_id);
                update_post_meta($audit_id, 'clause_no', $clause_no);
                update_post_meta($audit_id, 'field_type', $field_type);
                update_post_meta($audit_id, 'display_on_report_only', $display_on_report_only);
                update_post_meta($audit_id, 'is_radio_option', $is_radio_option);
            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $new_post = array(
                    'post_title'    => 'New item',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'audit-item',
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'category_id', $category_id);
                update_post_meta($post_id, 'sorting_key', -1);
            }
            $paged = sanitize_text_field($_POST['paged']);
            $response = array('html_contain' => $this->display_audit_item_list($paged, $category_id));
            wp_send_json($response);
        }

        function del_audit_item_dialog_data() {
            $category_id = sanitize_text_field($_POST['_category_id']);
            $paged = sanitize_text_field($_POST['paged']);
            wp_delete_post($_POST['_audit_id'], true);
            $response = array('html_contain' => $this->display_audit_item_list($paged, $category_id));
            wp_send_json($response);
        }

        function sort_audit_item_list_data() {
            $response = array('success' => false, 'error' => 'Invalid data format');
            if (isset($_POST['_audit_id_array']) && is_array($_POST['_audit_id_array'])) {
                $audit_id_array = array_map('absint', $_POST['_audit_id_array']);        
                foreach ($audit_id_array as $index => $audit_id) {
                    update_post_meta($audit_id, 'sorting_key', $index);
                }
                $response = array('success' => true);
            }
            wp_send_json($response);
        }

        function select_audit_item_options($selected_option=0, $category_id=false) {
            $paged = 0;
            $query = $this->retrieve_audit_item_list_data($paged, $category_id);
            $options = '<option value="">Select '.get_the_title($category_id).' audit item</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $clause_no = get_post_meta(get_the_ID(), 'clause_no', true);
                $field_type = get_post_meta(get_the_ID(), 'field_type', true);
                if ($field_type=='heading'){
                    $audit_item = '<b>'.get_the_title().'</b>';
                } else {
                    $audit_item = get_the_title().' '.$clause_no;
                }
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . $audit_item . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        // customer-card
        function register_customer_card_post_type() {
            $labels = array(
                'menu_name'     => _x('Customer', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
                'show_in_menu'  => false,
            );
            register_post_type( 'customer-card', $args );
        }

        function display_customer_card_list() {
            ob_start();
            $profiles_class = new display_profiles();
            $is_site_admin = $profiles_class->is_site_admin();

            if ($is_site_admin || current_user_can('administrator')) {
                // Check if the user is administrator
                ?>
                <?php echo display_iso_helper_logo();?>
                <h2 style="display:inline;"><?php echo __( '客戶列表', 'your-text-domain' );?></h2>

                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div><?php $profiles_class->display_select_profile(4);?></div>
                    <div style="text-align:right; display:flex;">
                        <input type="text" id="search-customer" style="display:inline" placeholder="Search..." />
                    </div>
                </div>

                <fieldset>
                    <table class="ui-widget" style="width:100%;">
                        <thead>
                            <th><?php echo __( 'Code', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Title', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Description', 'your-text-domain' );?></th>
                        </thead>
                        <tbody>
                        <?php
                        $paged = max(1, get_query_var('paged')); // Get the current page number
                        $query = $this->retrieve_customer_card_data($paged);
                        $total_posts = $query->found_posts;
                        $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages
                        if ($query->have_posts()) :
                            while ($query->have_posts()) : $query->the_post();
                                $customer_code = get_post_meta(get_the_ID(), 'customer_code', true);
                                ?>
                                <tr id="edit-customer-card-<?php the_ID();?>">
                                    <td style="text-align:center;"><?php echo $customer_code;?></td>
                                    <td><?php the_title();?></td>
                                    <td><?php the_content();?></td>
                                </tr>
                                <?php 
                            endwhile;
                            wp_reset_postdata();
                        endif;
                        ?>
                        </tbody>
                    </table>
                    <div id="new-customer-card" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                    <div class="pagination">
                        <?php
                        // Display pagination links
                        if ($paged > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged - 1)) . '"> < </a></span>';
                        echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $paged, $total_pages) . '</span>';
                        if ($paged < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged + 1)) . '"> > </a></span>';
                        ?>
                    </div>

                </fieldset>
                <div id="customer-card-dialog" title="Customer dialog"></div>
                <?php
            } else {
                ?>
                <p><?php echo __( 'You do not have permission to access this page.', 'your-text-domain' );?></p>
                <?php
            }
            return ob_get_clean();
        }

        function retrieve_customer_card_data($paged = 1) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            
            $args = array(
                'post_type'      => 'customer-card',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
                'meta_query'     => array(
                    array(
                        'key'   => 'site_id',
                        'value' => $site_id,
                    ),
                ),
                'meta_key'       => 'customer_code', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value
                'order'          => 'ASC', // Sorting order (ascending)
            );

            if ($paged == 0) {
                $args['posts_per_page'] = -1; // Retrieve all posts if $paged is 0
            }

            // Sanitize and handle search query
            $search_query = isset($_GET['_search']) ? sanitize_text_field($_GET['_search']) : '';
            if (!empty($search_query)) {
                $args['paged'] = 1;
                $args['s'] = $search_query;
            }
        
            $query = new WP_Query($args);
        
            // Check if query is empty and search query is not empty
            if (!$query->have_posts() && !empty($search_query)) {
                // Add meta query for searching across all meta keys
                $meta_keys = get_post_type_meta_keys('customer-card');
                $meta_query_all_keys = array('relation' => 'OR');
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

        function display_customer_card_dialog($customer_id=false) {
            $customer_code = get_post_meta($customer_id, 'customer_code', true);
            $customer_title = get_the_title($customer_id);
            $customer_content = get_post_field('post_content', $customer_id);
            $company_phone = get_post_meta($customer_id, 'company_phone', true);
            $company_fax = get_post_meta($customer_id, 'company_fax', true);
            ob_start();
            ?>
            <fieldset>
                <input type="hidden" id="customer-id" value="<?php echo esc_attr($customer_id);?>" />
                <label for="customer-code"><?php echo __( 'Code: ', 'your-text-domain' );?></label>
                <input type="text" id="customer-code" value="<?php echo esc_attr($customer_code);?>" class="text ui-widget-content ui-corner-all" />
                <label for="customer-title"><?php echo __( 'Title: ', 'your-text-domain' );?></label>
                <input type="text" id="customer-title" value="<?php echo esc_attr($customer_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="customer-content"><?php echo __( 'Description: ', 'your-text-domain' );?></label>
                <textarea id="customer-content" rows="3" style="width:100%;"><?php echo esc_html($customer_content);?></textarea>
                <?php
                // transaction data vs card key/value
                $key_pairs = array(
                    '_customer'   => $customer_id,
                );
                $profiles_class = new display_profiles();
                $profiles_class->get_transactions_by_key_value($key_pairs);
                ?>
                <label for="company-phone"><?php echo __( 'Phone: ', 'your-text-domain' );?></label>
                <input type="text" id="company-phone" value="<?php echo esc_attr($company_phone);?>" class="text ui-widget-content ui-corner-all" />
                <label for="company-fax"><?php echo __( 'Fax: ', 'your-text-domain' );?></label>
                <input type="text" id="company-fax" value="<?php echo esc_attr($company_fax);?>" class="text ui-widget-content ui-corner-all" />
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_customer_card_dialog_data() {
            $customer_id = sanitize_text_field($_POST['_customer_id']);
            $response = array('html_contain' => $this->display_customer_card_dialog($customer_id));
            wp_send_json($response);
        }

        function set_customer_card_dialog_data() {
            if( isset($_POST['_customer_id']) ) {
                $customer_id = sanitize_text_field($_POST['_customer_id']);
                $customer_code = sanitize_text_field($_POST['_customer_code']);
                $company_phone = sanitize_text_field($_POST['_company_phone']);
                $company_fax = sanitize_text_field($_POST['_company_fax']);
                $data = array(
                    'ID'           => $customer_id,
                    'post_title'   => sanitize_text_field($_POST['_customer_title']),
                    'post_content' => sanitize_text_field($_POST['_customer_content']),
                );
                wp_update_post( $data );
                update_post_meta($customer_id, 'customer_code', $customer_code);
                update_post_meta($customer_id, 'company_phone', $company_phone);
                update_post_meta($customer_id, 'company_fax', $company_fax);
            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $new_post = array(
                    'post_title'    => 'New customer',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'customer-card',
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'site_id', $site_id);
                update_post_meta($post_id, 'customer_code', time());
            }
            $response = array('html_contain' => $this->display_customer_card_list());
            wp_send_json($response);
        }

        function del_customer_card_dialog_data() {
            wp_delete_post($_POST['_customer_id'], true);
            $response = array('html_contain' => $this->display_customer_card_list());
            wp_send_json($response);
        }

        function select_customer_card_options($selected_option=0) {
            $query = $this->retrieve_customer_card_data();
            $options = '<option value="">Select customer</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html(get_the_title()) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        // Register vendor-card post type
        function register_vendor_card_post_type() {
            $labels = array(
                'menu_name'     => _x('Vendor', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
                'show_in_menu'  => false,
            );
            register_post_type( 'vendor-card', $args );
        }

        function display_vendor_card_list() {
            ob_start();
            $profiles_class = new display_profiles();
            $is_site_admin = $profiles_class->is_site_admin();

            if ($is_site_admin || current_user_can('administrator')) {
                // Check if the user is administrator
                ?>
                <?php echo display_iso_helper_logo();?>
                <h2 style="display:inline;"><?php echo __( '供應商列表', 'your-text-domain' );?></h2>

                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div><?php $profiles_class->display_select_profile(5);?></div>
                    <div style="text-align:right; display:flex;">
                        <input type="text" id="search-vendor" style="display:inline" placeholder="Search..." />
                    </div>
                </div>

                <fieldset>
                    <table class="ui-widget" style="width:100%;">
                        <thead>
                            <th><?php echo __( 'Code', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Title', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Description', 'your-text-domain' );?></th>
                        </thead>
                        <tbody>
                        <?php
                        $paged = max(1, get_query_var('paged')); // Get the current page number
                        $query = $this->retrieve_vendor_card_data($paged);
                        $total_posts = $query->found_posts;
                        $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages
                        if ($query->have_posts()) :
                            while ($query->have_posts()) : $query->the_post();
                                $vendor_code = get_post_meta(get_the_ID(), 'vendor_code', true);
                                ?>
                                <tr id="edit-vendor-card-<?php the_ID();?>">
                                    <td style="text-align:center;"><?php echo $vendor_code;?></td>
                                    <td><?php the_title();?></td>
                                    <td><?php the_content();?></td>
                                </tr>
                                <?php 
                            endwhile;
                            wp_reset_postdata();
                        endif;
                        ?>
                        </tbody>
                    </table>
                    <div id="new-vendor-card" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                    <div class="pagination">
                        <?php
                        // Display pagination links
                        if ($paged > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged - 1)) . '"> < </a></span>';
                        echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $paged, $total_pages) . '</span>';
                        if ($paged < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged + 1)) . '"> > </a></span>';
                        ?>
                    </div>

                </fieldset>
                <div id="vendor-card-dialog" title="Vendor dialog"></div>
                <?php
            } else {
                ?>
                <p><?php echo __( 'You do not have permission to access this page.', 'your-text-domain' );?></p>
                <?php
            }
            return ob_get_clean();
        }

        function retrieve_vendor_card_data($paged = 1) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            
            $args = array(
                'post_type'      => 'vendor-card',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
                'meta_query'     => array(
                    array(
                        'key'   => 'site_id',
                        'value' => $site_id,
                    ),
                ),
                'meta_key'       => 'vendor_code', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value
                'order'          => 'ASC', // Sorting order (ascending)
            );
        
            if ($paged == 0) {
                $args['posts_per_page'] = -1; // Retrieve all posts if $paged is 0
            }
        
            // Sanitize and handle search query
            $search_query = isset($_GET['_search']) ? sanitize_text_field($_GET['_search']) : '';
            if (!empty($search_query)) {
                $args['paged'] = 1;
                $args['s'] = $search_query;
            }
        
            $query = new WP_Query($args);
        
            // Check if query is empty and search query is not empty
            if (!$query->have_posts() && !empty($search_query)) {
                // Add meta query for searching across all meta keys
                $meta_keys = get_post_type_meta_keys('vendor-card');
                $meta_query_all_keys = array('relation' => 'OR');
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

        function display_vendor_card_dialog($vendor_id=false) {
            $vendor_code = get_post_meta($vendor_id, 'vendor_code', true);
            $vendor_title = get_the_title($vendor_id);
            $vendor_content = get_post_field('post_content', $vendor_id);
            $company_phone = get_post_meta($vendor_id, 'company_phone', true);
            $company_fax = get_post_meta($vendor_id, 'company_fax', true);
            ob_start();
            ?>
            <fieldset>
                <input type="hidden" id="vendor-id" value="<?php echo esc_attr($vendor_id);?>" />
                <label for="vendor-code"><?php echo __( 'Code: ', 'your-text-domain' );?></label>
                <input type="text" id="vendor-code" value="<?php echo esc_attr($vendor_code);?>" class="text ui-widget-content ui-corner-all" />
                <label for="vendor-title"><?php echo __( 'Title: ', 'your-text-domain' );?></label>
                <input type="text" id="vendor-title" value="<?php echo esc_attr($vendor_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="vendor-content"><?php echo __( 'Description: ', 'your-text-domain' );?></label>
                <textarea id="vendor-content" rows="3" style="width:100%;"><?php echo esc_html($vendor_content);?></textarea>
                <?php
                // transaction data vs card key/value
                $key_pairs = array(
                    '_vendor'   => $vendor_id,
                );
                $profiles_class = new display_profiles();
                $profiles_class->get_transactions_by_key_value($key_pairs);
                ?>
                <label for="company-phone"><?php echo __( 'Phone: ', 'your-text-domain' );?></label>
                <input type="text" id="company-phone" value="<?php echo esc_attr($company_phone);?>" class="text ui-widget-content ui-corner-all" />
                <label for="company-fax"><?php echo __( 'Fax: ', 'your-text-domain' );?></label>
                <input type="text" id="company-fax" value="<?php echo esc_attr($company_fax);?>" class="text ui-widget-content ui-corner-all" />
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_vendor_card_dialog_data() {
            $vendor_id = sanitize_text_field($_POST['_vendor_id']);
            $response = array('html_contain' => $this->display_vendor_card_dialog($vendor_id));
            wp_send_json($response);
        }

        function set_vendor_card_dialog_data() {
            if( isset($_POST['_vendor_id']) ) {
                $vendor_id = sanitize_text_field($_POST['_vendor_id']);
                $vendor_code = sanitize_text_field($_POST['_vendor_code']);
                $company_phone = sanitize_text_field($_POST['_company_phone']);
                $company_fax = sanitize_text_field($_POST['_company_fax']);
                $data = array(
                    'ID'           => $vendor_id,
                    'post_title'   => sanitize_text_field($_POST['_vendor_title']),
                    'post_content' => sanitize_text_field($_POST['_vendor_content']),
                );
                wp_update_post( $data );
                update_post_meta($vendor_id, 'vendor_code', $vendor_code);
                update_post_meta($vendor_id, 'company_phone', $company_phone);
                update_post_meta($vendor_id, 'company_fax', $company_fax);
            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $new_post = array(
                    'post_title'    => 'New vendor',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'vendor-card',
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'site_id', $site_id);
                update_post_meta($post_id, 'vendor_code', time());
            }
            $response = array('html_contain' => $this->display_vendor_card_list());
            wp_send_json($response);
        }

        function del_vendor_card_dialog_data() {
            wp_delete_post($_POST['_vendor_id'], true);
            $response = array('html_contain' => $this->display_vendor_card_list());
            wp_send_json($response);
        }

        function select_vendor_card_options($selected_option=0) {
            $query = $this->retrieve_vendor_card_data();
            $options = '<option value="">Select vendor</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html(get_the_title()) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        // Register product-card post type
        function register_product_card_post_type() {
            $labels = array(
                'menu_name'     => _x('Product', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
                'show_in_menu'  => false,
            );
            register_post_type( 'product-card', $args );
        }

        function display_product_card_list() {
            ob_start();
            $profiles_class = new display_profiles();
            $is_site_admin = $profiles_class->is_site_admin();

            if ($is_site_admin || current_user_can('administrator')) {
                // Check if the user is administrator
                ?>
                <?php echo display_iso_helper_logo();?>
                <h2 style="display:inline;"><?php echo __( '產品列表', 'your-text-domain' );?></h2>

                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div><?php $profiles_class->display_select_profile(6);?></div>
                    <div style="text-align:right; display:flex;">
                        <input type="text" id="search-product" style="display:inline" placeholder="Search..." />
                    </div>
                </div>

                <fieldset>
                    <table class="ui-widget" style="width:100%;">
                        <thead>
                            <th><?php echo __( 'Code', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Title', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Description', 'your-text-domain' );?></th>
                        </thead>
                        <tbody>
                        <?php
                        $paged = max(1, get_query_var('paged')); // Get the current page number
                        $query = $this->retrieve_product_card_data($paged);
                        $total_posts = $query->found_posts;
                        $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages
                        if ($query->have_posts()) :
                            while ($query->have_posts()) : $query->the_post();
                                $product_code = get_post_meta(get_the_ID(), 'product_code', true);
                                ?>
                                <tr id="edit-product-card-<?php the_ID();?>">
                                    <td style="text-align:center;"><?php echo $product_code;?></td>
                                    <td><?php the_title();?></td>
                                    <td><?php the_content();?></td>
                                </tr>
                                <?php 
                            endwhile;
                            wp_reset_postdata();
                        endif;
                        ?>
                        </tbody>
                    </table>
                    <div id="new-product-card" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                    <div class="pagination">
                        <?php
                        // Display pagination links
                        if ($paged > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged - 1)) . '"> < </a></span>';
                        echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $paged, $total_pages) . '</span>';
                        if ($paged < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged + 1)) . '"> > </a></span>';
                        ?>
                    </div>

                </fieldset>
                <div id="product-card-dialog" title="Product dialog"></div>
                <?php
            } else {
                ?>
                <p><?php echo __( 'You do not have permission to access this page.', 'your-text-domain' );?></p>
                <?php
            }
            return ob_get_clean();
        }

        function retrieve_product_card_data($paged = 1) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            
            $args = array(
                'post_type'      => 'product-card',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
                'meta_query'     => array(
                    array(
                        'key'   => 'site_id',
                        'value' => $site_id,
                    ),
                ),
                'meta_key'       => 'product_code', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value
                'order'          => 'ASC', // Sorting order (ascending)
            );
        
            if ($paged == 0) {
                $args['posts_per_page'] = -1; // Retrieve all posts if $paged is 0
            }
        
            // Sanitize and handle search query
            $search_query = isset($_GET['_search']) ? sanitize_text_field($_GET['_search']) : '';
            if (!empty($search_query)) {
                $args['paged'] = 1;
                $args['s'] = $search_query;
            }
        
            $query = new WP_Query($args);
        
            // Check if query is empty and search query is not empty
            if (!$query->have_posts() && !empty($search_query)) {
                // Add meta query for searching across all meta keys
                $meta_keys = get_post_type_meta_keys('product-card');
                $meta_query_all_keys = array('relation' => 'OR');
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

        function display_product_card_dialog($product_id=false) {
            $product_code = get_post_meta($product_id, 'product_code', true);
            $product_title = get_the_title($product_id);
            $product_content = get_post_field('post_content', $product_id);
            ob_start();
            ?>
            <fieldset>
                <input type="hidden" id="product-id" value="<?php echo esc_attr($product_id);?>" />
                <label for="product-code"><?php echo __( 'Code: ', 'your-text-domain' );?></label>
                <input type="text" id="product-code" value="<?php echo esc_attr($product_code);?>" class="text ui-widget-content ui-corner-all" />
                <label for="product-title"><?php echo __( 'Title: ', 'your-text-domain' );?></label>
                <input type="text" id="product-title" value="<?php echo esc_attr($product_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="product-content"><?php echo __( 'Description: ', 'your-text-domain' );?></label>
                <textarea id="product-content" rows="3" style="width:100%;"><?php echo esc_html($product_content);?></textarea>
                <?php
                // transaction data vs card key/value
                $key_pairs = array(
                    '_product'   => $product_id,
                );
                $profiles_class = new display_profiles();
                $profiles_class->get_transactions_by_key_value($key_pairs);
                ?>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_product_card_dialog_data() {
            $product_id = sanitize_text_field($_POST['_product_id']);
            $response = array('html_contain' => $this->display_product_card_dialog($product_id));
            wp_send_json($response);
        }

        function set_product_card_dialog_data() {
            if( isset($_POST['_product_id']) ) {
                $product_id = sanitize_text_field($_POST['_product_id']);
                $product_code = sanitize_text_field($_POST['_product_code']);
                $data = array(
                    'ID'           => $product_id,
                    'post_title'   => sanitize_text_field($_POST['_product_title']),
                    'post_content' => sanitize_text_field($_POST['_product_content']),
                );
                wp_update_post( $data );
                update_post_meta($product_id, 'product_code', $product_code);
            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $new_post = array(
                    'post_title'    => 'New product',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'product-card',
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'site_id', $site_id);
                update_post_meta($post_id, 'product_code', time());
            }
            $response = array('html_contain' => $this->display_product_card_list());
            wp_send_json($response);
        }

        function del_product_card_dialog_data() {
            wp_delete_post($_POST['_product_id'], true);
            $response = array('html_contain' => $this->display_product_card_list());
            wp_send_json($response);
        }

        function select_product_card_options($selected_option=0) {
            $query = $this->retrieve_product_card_data();
            $options = '<option value="">Select product</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html(get_the_title()) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        // Register equipment-card post type
        function register_equipment_card_post_type() {
            $labels = array(
                'menu_name'     => _x('Equipment', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
                'show_in_menu'  => false,
            );
            register_post_type( 'equipment-card', $args );
        }

        function display_equipment_card_list() {
            ob_start();
            $profiles_class = new display_profiles();
            $is_site_admin = $profiles_class->is_site_admin();

            if ($is_site_admin || current_user_can('administrator')) {
                // Check if the user is administrator
                ?>
                <?php echo display_iso_helper_logo();?>
                <h2 style="display:inline;"><?php echo __( '設備列表', 'your-text-domain' );?></h2>

                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div><?php $profiles_class->display_select_profile(7);?></div>
                    <div style="text-align:right; display:flex;">
                        <input type="text" id="search-equipment" style="display:inline" placeholder="Search..." />
                    </div>
                </div>

                <fieldset>
                    <table class="ui-widget" style="width:100%;">
                        <thead>
                            <th><?php echo __( 'Code', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Title', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Description', 'your-text-domain' );?></th>
                        </thead>
                        <tbody>
                        <?php
                        $paged = max(1, get_query_var('paged')); // Get the current page number
                        $query = $this->retrieve_equipment_card_data($paged);
                        $total_posts = $query->found_posts;
                        $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages
                        if ($query->have_posts()) :
                            while ($query->have_posts()) : $query->the_post();
                                $equipment_code = get_post_meta(get_the_ID(), 'equipment_code', true);
                                ?>
                                <tr id="edit-equipment-card-<?php the_ID();?>">
                                    <td style="text-align:center;"><?php echo $equipment_code;?></td>
                                    <td><?php the_title();?></td>
                                    <td><?php the_content();?></td>
                                </tr>
                                <?php 
                            endwhile;
                            wp_reset_postdata();
                        endif;
                        ?>
                        </tbody>
                    </table>
                    <div id="new-equipment-card" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                    <div class="pagination">
                        <?php
                        // Display pagination links
                        if ($paged > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged - 1)) . '"> < </a></span>';
                        echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $paged, $total_pages) . '</span>';
                        if ($paged < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged + 1)) . '"> > </a></span>';
                        ?>
                    </div>

                </fieldset>
                <div id="equipment-card-dialog" title="Equipment dialog"></div>
                <?php
            } else {
                ?>
                <p><?php echo __( 'You do not have permission to access this page.', 'your-text-domain' );?></p>
                <?php
            }
            return ob_get_clean();
        }

        function retrieve_equipment_card_data($paged = 1) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            
            $args = array(
                'post_type'      => 'equipment-card',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
                'meta_query'     => array(
                    array(
                        'key'   => 'site_id',
                        'value' => $site_id,
                    ),
                ),
                'meta_key'       => 'equipment_code', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value
                'order'          => 'ASC', // Sorting order (ascending)
            );
        
            if ($paged == 0) {
                $args['posts_per_page'] = -1; // Retrieve all posts if $paged is 0
            }
        
            // Sanitize and handle search query
            $search_query = isset($_GET['_search']) ? sanitize_text_field($_GET['_search']) : '';
            if (!empty($search_query)) {
                $args['paged'] = 1;
                $args['s'] = $search_query;
            }
        
            $query = new WP_Query($args);
        
            // Check if query is empty and search query is not empty
            if (!$query->have_posts() && !empty($search_query)) {
                // Add meta query for searching across all meta keys
                $meta_keys = get_post_type_meta_keys('equipment-card');
                $meta_query_all_keys = array('relation' => 'OR');
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

        function display_equipment_card_dialog($equipment_id=false) {
            $equipment_code = get_post_meta($equipment_id, 'equipment_code', true);
            $equipment_title = get_the_title($equipment_id);
            $equipment_content = get_post_field('post_content', $equipment_id);
            ob_start();
            ?>
            <fieldset>
                <input type="hidden" id="equipment-id" value="<?php echo esc_attr($equipment_id);?>" />
                <label for="equipment-code"><?php echo __( 'Code: ', 'your-text-domain' );?></label>
                <input type="text" id="equipment-code" value="<?php echo esc_attr($equipment_code);?>" class="text ui-widget-content ui-corner-all" />
                <label for="equipment-title"><?php echo __( 'Title: ', 'your-text-domain' );?></label>
                <input type="text" id="equipment-title" value="<?php echo esc_attr($equipment_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="equipment-content"><?php echo __( 'Description: ', 'your-text-domain' );?></label>
                <textarea id="equipment-content" rows="3" style="width:100%;"><?php echo esc_html($equipment_content);?></textarea>
                <?php
                // transaction data vs card key/value
                $key_pairs = array(
                    '_equipment'   => $equipment_id,
                );
                $profiles_class = new display_profiles();
                $profiles_class->get_transactions_by_key_value($key_pairs);
                ?>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_equipment_card_dialog_data() {
            $equipment_id = sanitize_text_field($_POST['_equipment_id']);
            $response = array('html_contain' => $this->display_equipment_card_dialog($equipment_id));
            wp_send_json($response);
        }

        function set_equipment_card_dialog_data() {
            if( isset($_POST['_equipment_id']) ) {
                $equipment_id = sanitize_text_field($_POST['_equipment_id']);
                $equipment_code = sanitize_text_field($_POST['_equipment_code']);
                $data = array(
                    'ID'           => $equipment_id,
                    'post_title'   => sanitize_text_field($_POST['_equipment_title']),
                    'post_content' => sanitize_text_field($_POST['_equipment_content']),
                );
                wp_update_post( $data );
                update_post_meta($equipment_id, 'equipment_code', $equipment_code);
            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $new_post = array(
                    'post_title'    => 'New equipment',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'equipment-card',
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'site_id', $site_id);
                update_post_meta($post_id, 'equipment_code', time());
            }
            $response = array('html_contain' => $this->display_equipment_card_list());
            wp_send_json($response);
        }

        function del_equipment_card_dialog_data() {
            wp_delete_post($_POST['_equipment_id'], true);
            $response = array('html_contain' => $this->display_equipment_card_list());
            wp_send_json($response);
        }

        function select_equipment_card_options($selected_option=0) {
            $query = $this->retrieve_equipment_card_data();
            $options = '<option value="">Select equipment</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html(get_the_title()) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        // Register instrument-card post type
        function register_instrument_card_post_type() {
            $labels = array(
                'menu_name'     => _x('Instrument', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
                'show_in_menu'  => false,
            );
            register_post_type( 'instrument-card', $args );
        }

        function display_instrument_card_list() {
            ob_start();
            $profiles_class = new display_profiles();
            $is_site_admin = $profiles_class->is_site_admin();

            if ($is_site_admin || current_user_can('administrator')) {
                // Check if the user is administrator
                ?>
                <?php echo display_iso_helper_logo();?>
                <h2 style="display:inline;"><?php echo __( '儀器列表', 'your-text-domain' );?></h2>

                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div><?php $profiles_class->display_select_profile(8);?></div>
                    <div style="text-align:right; display:flex;">
                        <input type="text" id="search-instrument" style="display:inline" placeholder="Search..." />
                    </div>
                </div>

                <fieldset>
                    <table class="ui-widget" style="width:100%;">
                        <thead>
                            <th><?php echo __( 'Code', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Title', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Description', 'your-text-domain' );?></th>
                        </thead>
                        <tbody>
                        <?php
                        $paged = max(1, get_query_var('paged')); // Get the current page number
                        $query = $this->retrieve_instrument_card_data($paged);
                        $total_posts = $query->found_posts;
                        $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages
                        if ($query->have_posts()) :
                            while ($query->have_posts()) : $query->the_post();
                                $instrument_code = get_post_meta(get_the_ID(), 'instrument_code', true);
                                ?>
                                <tr id="edit-instrument-card-<?php the_ID();?>">
                                    <td style="text-align:center;"><?php echo $instrument_code;?></td>
                                    <td><?php the_title();?></td>
                                    <td><?php the_content();?></td>
                                </tr>
                                <?php 
                            endwhile;
                            wp_reset_postdata();
                        endif;
                        ?>
                        </tbody>
                    </table>
                    <div id="new-instrument-card" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                    <div class="pagination">
                        <?php
                        // Display pagination links
                        if ($paged > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged - 1)) . '"> < </a></span>';
                        echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $paged, $total_pages) . '</span>';
                        if ($paged < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged + 1)) . '"> > </a></span>';
                        ?>
                    </div>

                </fieldset>
                <div id="instrument-card-dialog" title="Instrument dialog"></div>
                <?php
            } else {
                ?>
                <p><?php echo __( 'You do not have permission to access this page.', 'your-text-domain' );?></p>
                <?php
            }
            return ob_get_clean();
        }

        function retrieve_instrument_card_data($paged = 1) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            
            $args = array(
                'post_type'      => 'instrument-card',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
                'meta_query'     => array(
                    array(
                        'key'   => 'site_id',
                        'value' => $site_id,
                    ),
                ),
                'meta_key'       => 'instrument_code', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value
                'order'          => 'ASC', // Sorting order (ascending)
            );
        
            if ($paged == 0) {
                $args['posts_per_page'] = -1; // Retrieve all posts if $paged is 0
            }
        
            // Sanitize and handle search query
            $search_query = isset($_GET['_search']) ? sanitize_text_field($_GET['_search']) : '';
            if (!empty($search_query)) {
                $args['paged'] = 1;
                $args['s'] = $search_query;
            }
        
            $query = new WP_Query($args);
        
            // Check if query is empty and search query is not empty
            if (!$query->have_posts() && !empty($search_query)) {
                // Add meta query for searching across all meta keys
                $meta_keys = get_post_type_meta_keys('instrument-card');
                $meta_query_all_keys = array('relation' => 'OR');
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

        function display_instrument_card_dialog($instrument_id=false) {
            $instrument_code = get_post_meta($instrument_id, 'instrument_code', true);
            $instrument_title = get_the_title($instrument_id);
            $instrument_content = get_post_field('post_content', $instrument_id);
            ob_start();
            ?>
            <fieldset>
                <input type="hidden" id="instrument-id" value="<?php echo esc_attr($instrument_id);?>" />
                <label for="instrument-code"><?php echo __( 'Code: ', 'your-text-domain' );?></label>
                <input type="text" id="instrument-code" value="<?php echo esc_attr($instrument_code);?>" class="text ui-widget-content ui-corner-all" />
                <label for="instrument-title"><?php echo __( 'Title: ', 'your-text-domain' );?></label>
                <input type="text" id="instrument-title" value="<?php echo esc_attr($instrument_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="instrument-content"><?php echo __( 'Description: ', 'your-text-domain' );?></label>
                <textarea id="instrument-content" rows="3" style="width:100%;"><?php echo esc_html($instrument_content);?></textarea>
                <?php
                // transaction data vs card key/value
                $key_pairs = array(
                    '_instrument'   => $instrument_id,
                );
                $profiles_class = new display_profiles();
                $profiles_class->get_transactions_by_key_value($key_pairs);
                ?>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_instrument_card_dialog_data() {
            $instrument_id = sanitize_text_field($_POST['_instrument_id']);
            $response = array('html_contain' => $this->display_instrument_card_dialog($instrument_id));
            wp_send_json($response);
        }

        function set_instrument_card_dialog_data() {
            if( isset($_POST['_instrument_id']) ) {
                $instrument_id = sanitize_text_field($_POST['_instrument_id']);
                $instrument_code = sanitize_text_field($_POST['_instrument_code']);
                $data = array(
                    'ID'           => $instrument_id,
                    'post_title'   => sanitize_text_field($_POST['_instrument_title']),
                    'post_content' => sanitize_text_field($_POST['_instrument_content']),
                );
                wp_update_post( $data );
                update_post_meta($instrument_id, 'instrument_code', $instrument_code);
            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $new_post = array(
                    'post_title'    => 'New instrument',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'instrument-card',
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'site_id', $site_id);
                update_post_meta($post_id, 'instrument_code', time());
            }
            $response = array('html_contain' => $this->display_instrument_card_list());
            wp_send_json($response);
        }

        function del_instrument_card_dialog_data() {
            wp_delete_post($_POST['_instrument_id'], true);
            $response = array('html_contain' => $this->display_instrument_card_list());
            wp_send_json($response);
        }

        function select_instrument_card_options($selected_option=0) {
            $query = $this->retrieve_instrument_card_data();
            $options = '<option value="">Select instrument</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html(get_the_title()) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        // Register department-card post type
        function register_department_card_post_type() {
            $labels = array(
                'menu_name'     => _x('Department', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
                'show_in_menu'  => false,
            );
            register_post_type( 'department-card', $args );
        }

        function display_department_card_list() {
            ob_start();
            $profiles_class = new display_profiles();
            $is_site_admin = $profiles_class->is_site_admin();

            if ($is_site_admin || current_user_can('administrator')) {
                // Check if the user is administrator
                ?>
                <?php echo display_iso_helper_logo();?>
                <h2 style="display:inline;"><?php echo __( '部門列表', 'your-text-domain' );?></h2>

                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div><?php $profiles_class->display_select_profile(9);?></div>
                    <div style="text-align:right; display:flex;">
                        <input type="text" id="search-department" style="display:inline" placeholder="Search..." />
                    </div>
                </div>

                <fieldset>
                    <table class="ui-widget" style="width:100%;">
                        <thead>
                            <th><?php echo __( 'Code', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Title', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Description', 'your-text-domain' );?></th>
                        </thead>
                        <tbody>
                        <?php
                        $paged = max(1, get_query_var('paged')); // Get the current page number
                        $query = $this->retrieve_department_card_data($paged);
                        $total_posts = $query->found_posts;
                        $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages
                        if ($query->have_posts()) :
                            while ($query->have_posts()) : $query->the_post();
                                $department_code = get_post_meta(get_the_ID(), 'department_code', true);
                                ?>
                                <tr id="edit-department-card-<?php the_ID();?>">
                                    <td style="text-align:center;"><?php echo $department_code;?></td>
                                    <td><?php the_title();?></td>
                                    <td><?php the_content();?></td>
                                </tr>
                                <?php 
                            endwhile;
                            wp_reset_postdata();
                        endif;
                        ?>
                        </tbody>
                    </table>
                    <div id="new-department-card" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
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
            } else {
                ?>
                <p><?php echo __( 'You do not have permission to access this page.', 'your-text-domain' );?></p>
                <?php
            }
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
                'meta_key'       => 'department_code', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value
                'order'          => 'ASC', // Sorting order (ascending)
            );
        
            if ($paged == 0) {
                $args['posts_per_page'] = -1; // Retrieve all posts if $paged is 0
            }
        
            // Sanitize and handle search query
            $search_query = isset($_GET['_search']) ? sanitize_text_field($_GET['_search']) : '';
            if (!empty($search_query)) {
                $args['paged'] = 1;
                $args['s'] = $search_query;
            }
        
            $query = new WP_Query($args);
        
            // Check if query is empty and search query is not empty
            if (!$query->have_posts() && !empty($search_query)) {
                // Add meta query for searching across all meta keys
                $meta_keys = get_post_type_meta_keys('department-card');
                $meta_query_all_keys = array('relation' => 'OR');
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
            $department_code = get_post_meta($department_id, 'department_code', true);
            $department_title = get_the_title($department_id);
            $department_content = get_post_field('post_content', $department_id);
            ob_start();
            ?>
            <fieldset>
                <input type="hidden" id="department-id" value="<?php echo esc_attr($department_id);?>" />
                <label for="department-code"><?php echo __( 'Code: ', 'your-text-domain' );?></label>
                <input type="text" id="department-code" value="<?php echo esc_attr($department_code);?>" class="text ui-widget-content ui-corner-all" />
                <label for="department-title"><?php echo __( 'Title: ', 'your-text-domain' );?></label>
                <input type="text" id="department-title" value="<?php echo esc_attr($department_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="department-content"><?php echo __( 'Description: ', 'your-text-domain' );?></label>
                <textarea id="department-content" rows="3" style="width:100%;"><?php echo esc_html($department_content);?></textarea>
                <label for="department-members"><?php echo __( '部門成員：', 'your-text-domain' );?></label>
                <?php echo $this->display_department_user_list($department_id);?>
                <?php
                // transaction data vs card key/value
                $key_pairs = array(
                    '_department'   => $department_id,
                );
                $profiles_class = new display_profiles();
                $profiles_class->get_transactions_by_key_value($key_pairs);
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
                $department_id = sanitize_text_field($_POST['_department_id']);
                $department_code = sanitize_text_field($_POST['_department_code']);
                $data = array(
                    'ID'           => $department_id,
                    'post_title'   => sanitize_text_field($_POST['_department_title']),
                    'post_content' => sanitize_text_field($_POST['_department_content']),
                );
                wp_update_post( $data );
                update_post_meta($department_id, 'department_code', $department_code);
            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $new_post = array(
                    'post_title'    => 'New department',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'department-card',
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'site_id', $site_id);
                update_post_meta($post_id, 'department_code', time());
            }
            $response = array('html_contain' => $this->display_department_card_list());
            wp_send_json($response);
        }

        function del_department_card_dialog_data() {
            wp_delete_post($_POST['_department_id'], true);
            $response = array('html_contain' => $this->display_department_card_list());
            wp_send_json($response);
        }

        function select_department_card_options($selected_option=0) {
            $query = $this->retrieve_department_card_data();
            $options = '<option value="">Select department</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html(get_the_title()) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        function display_department_user_list($department_id=false) {
            $user_ids = array();            
            if ($department_id==false) {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                    $meta_query_args = array(
                    array(
                        'key'     => 'site_id',
                        'value'   => $site_id,
                        'compare' => '=',
                    ),
                );
                $users = get_users(array('meta_query' => $meta_query_args));
                foreach ($users as $user) {
                    $user_ids[] = $user->ID;
                }    
            } else {
                $user_ids = get_post_meta($department_id, 'user_ids', true);
            }
            ob_start();
            ?>
            <div id="department-user-list">
                <fieldset style="margin-top:5px;">
                    <table class="ui-widget" style="width:100%;">
                        <thead>
                            <th><?php echo __( 'Name', 'your-text-domain' );?></th>
                            <th><?php echo __( 'Email', 'your-text-domain' );?></th>
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
            $response = array();
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
        
        // employees
        function select_multiple_employees_options($selected_options = array()) {
            if (!is_array($selected_options)) {
                $selected_options = array();
            }

            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
        
            // Retrieve users based on site_id
            $meta_query_args = array(
                array(
                    'key'     => 'site_id',
                    'value'   => $site_id,
                    'compare' => '=',
                ),
            );
            $users = get_users(array('meta_query' => $meta_query_args));
        
            // Initialize options HTML
            $options = '';
        
            // Loop through the users
            foreach ($users as $user) {
                // Check if the current user ID is in the selected options array
                $selected = in_array($user->ID, $selected_options) ? 'selected' : '';
                $options .= '<option value="' . esc_attr($user->ID) . '" ' . $selected . '>' . esc_html($user->display_name) . '</option>';
            }
        
            // Return the options HTML
            return $options;
        }
    }
    $cards_class = new erp_cards();
}


