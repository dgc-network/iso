<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (!class_exists('erp_cards')) {
    class erp_cards {
        // Class constructor
        public function __construct() {
            //add_shortcode( 'display-customers', array( $this, 'display_shortcode' ) );
            //add_shortcode( 'display-customers', array( $this, 'display_customer_list' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_erp_cards_scripts' ) );

            //add_action( 'wp_ajax_get_customer_card_list_data', array( $this, 'get_customer_card_list_data' ) );
            //add_action( 'wp_ajax_nopriv_get_customer_card_list_data', array( $this, 'get_customer_card_list_data' ) );
            add_action( 'wp_ajax_get_customer_card_dialog_data', array( $this, 'get_customer_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_customer_card_dialog_data', array( $this, 'get_customer_card_dialog_data' ) );
            add_action( 'wp_ajax_set_customer_card_dialog_data', array( $this, 'set_customer_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_customer_card_dialog_data', array( $this, 'set_customer_card_dialog_data' ) );
            add_action( 'wp_ajax_del_customer_card_dialog_data', array( $this, 'del_customer_card_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_customer_card_dialog_data', array( $this, 'del_customer_card_dialog_data' ) );
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
                //'show_in_rest'  => true,
                'show_in_menu'  => false,
            );
            register_post_type( 'customer-card', $args );
        }

        function display_customer_card_list() {
            ob_start();
            $current_user_id = get_current_user_id();
            $current_user = get_userdata($current_user_id);
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $image_url = get_post_meta($site_id, 'image_url', true);
            $profiles_class = new display_profiles();
            $is_site_admin = $profiles_class->is_site_admin();

            if ($is_site_admin || current_user_can('administrator')) {
                // Check if the user is administrator
                ?>
                <img src="<?php echo esc_attr($image_url)?>" style="object-fit:cover; width:30px; height:30px; margin-left:5px;" />
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
                        $query = $this->retrieve_customer_card_data();
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
/*        
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

            if ($paged==0) $args['posts_per_page'] = -1;

            $search_query = sanitize_text_field($_GET['_search']);
            if ($search_query) $args['paged'] = 1;
            if ($search_query) $args['s'] = $search_query;

            $query = new WP_Query($args);

            // Check if $query is empty and search query is not empty
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
*/
        function display_customer_card_dialog($customer_id=false) {
            $customer_code = get_post_meta($customer_id, 'customer_code', true);
            $customer_title = get_the_title($customer_id);
            $customer_content = get_post_field('post_content', $customer_id);
            ob_start();
            ?>
            <fieldset>
                <input type="hidden" id="customer-id" value="<?php echo esc_attr($customer_id);?>" />
                <label for="customer-code"><?php echo __( 'Parent: ', 'your-text-domain' );?></label>
                <input type="text" id="customer-code" value="<?php echo esc_attr($customer_code);?>" class="text ui-widget-content ui-corner-all" />
                <label for="customer-title"><?php echo __( 'Title: ', 'your-text-domain' );?></label>
                <input type="text" id="customer-title" value="<?php echo esc_attr($customer_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="customer-content"><?php echo __( 'Description: ', 'your-text-domain' );?></label>
                <textarea id="customer-content" rows="3" style="width:100%;"><?php echo esc_html($customer_content);?></textarea>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_customer_card_dialog_data() {
            $response = array();
            $customer_id = sanitize_text_field($_POST['_customer_id']);
            $response['html_contain'] = $this->display_customer_card_dialog($customer_id);
            wp_send_json($response);
        }

        function set_customer_card_dialog_data() {
            //$response = array();
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
            //$response = array();
            wp_delete_post($_POST['_customer_id'], true);
            $response = array('html_contain' => $this->display_customer_card_list());
            wp_send_json($response);
        }

        function select_customer_cards($selected_option=0) {
            $query = $this->retrieve_customer_card_data();
            //$options = '<select>';
            $options = '<option value="">Select customer</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html(get_the_title()) . '</option>';
            endwhile;
            wp_reset_postdata();
            //$options .= '</select>';
            return $options;
        }

    }
    $cards_class = new erp_cards();
}


