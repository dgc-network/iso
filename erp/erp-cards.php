<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (!class_exists('erp_cards')) {
    class erp_cards {
        // Class constructor
        public function __construct() {
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_erp_cards_scripts' ) );

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

        // Register customer-card post type
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
                $profiles_class->get_transactions_by_card_key_value($key_pairs);
                ?>
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
                $data = array(
                    'ID'           => $customer_id,
                    'post_title'   => sanitize_text_field($_POST['_customer_title']),
                    'post_content' => sanitize_text_field($_POST['_customer_content']),
                );
                wp_update_post( $data );
                update_post_meta($customer_id, 'customer_code', $customer_code);
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
                $profiles_class->get_transactions_by_card_key_value($key_pairs);
                ?>
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
                $data = array(
                    'ID'           => $vendor_id,
                    'post_title'   => sanitize_text_field($_POST['_vendor_title']),
                    'post_content' => sanitize_text_field($_POST['_vendor_content']),
                );
                wp_update_post( $data );
                update_post_meta($vendor_id, 'vendor_code', $vendor_code);
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
                $profiles_class->get_transactions_by_card_key_value($key_pairs);
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
                $profiles_class->get_transactions_by_card_key_value($key_pairs);
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
                $profiles_class->get_transactions_by_card_key_value($key_pairs);
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
                <?php
                // transaction data vs card key/value
                $key_pairs = array(
                    '_department'   => $department_id,
                );
                $profiles_class = new display_profiles();
                $profiles_class->get_transactions_by_card_key_value($key_pairs);
                ?>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_department_card_dialog_data() {
            $instrument_id = sanitize_text_field($_POST['_department_id']);
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

        // employee-card
        function select_employee_card_options($selected_option=0) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);

            $users = get_users(); // Initialize with all users
            $meta_query_args = array(
                array(
                    'key'     => 'site_id',
                    'value'   => $site_id,
                    'compare' => '=',
                ),
            );
            $users = get_users(array('meta_query' => $meta_query_args));

            // Loop through the users
            $options = '<option value="">Select employee</option>';
            foreach ($users as $user) {
                $selected = ($selected_option == $user->ID) ? 'selected' : '';
                $options .= '<option value="' . esc_attr($user->ID) . '" '.$selected.' />' . esc_html($user->display_name) . '</option>';
            }
            return $options;
        }        
    }
    $cards_class = new erp_cards();
}


