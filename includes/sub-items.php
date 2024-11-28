<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (!class_exists('sub_items')) {
    class sub_items {
        // Class constructor
        public function __construct() {
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_sub_items_scripts' ) );
            //add_action( 'init', array( $this, 'register_embedded_post_type' ) );
            //add_action( 'init', array( $this, 'register_doc_category_post_type' ) );

            add_action( 'wp_ajax_get_embedded_dialog_data', array( $this, 'get_embedded_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_embedded_dialog_data', array( $this, 'get_embedded_dialog_data' ) );
            add_action( 'wp_ajax_set_embedded_dialog_data', array( $this, 'set_embedded_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_embedded_dialog_data', array( $this, 'set_embedded_dialog_data' ) );
            add_action( 'wp_ajax_del_embedded_dialog_data', array( $this, 'del_embedded_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_embedded_dialog_data', array( $this, 'del_embedded_dialog_data' ) );
            
            add_action( 'wp_ajax_duplicate_embedded_dialog_data', array( $this, 'duplicate_embedded_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_duplicate_embedded_dialog_data', array( $this, 'duplicate_embedded_dialog_data' ) );

            add_action( 'wp_ajax_get_sub_item_dialog_data', array( $this, 'get_sub_item_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_sub_item_dialog_data', array( $this, 'get_sub_item_dialog_data' ) );
            add_action( 'wp_ajax_set_sub_item_dialog_data', array( $this, 'set_sub_item_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_sub_item_dialog_data', array( $this, 'set_sub_item_dialog_data' ) );
            add_action( 'wp_ajax_del_sub_item_dialog_data', array( $this, 'del_sub_item_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_sub_item_dialog_data', array( $this, 'del_sub_item_dialog_data' ) );

            add_action( 'wp_ajax_sort_sub_item_list_data', array( $this, 'sort_sub_item_list_data' ) );
            add_action( 'wp_ajax_nopriv_sort_sub_item_list_data', array( $this, 'sort_sub_item_list_data' ) );

            add_action( 'wp_ajax_get_sub_report_dialog_data', array( $this, 'get_sub_report_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_sub_report_dialog_data', array( $this, 'get_sub_report_dialog_data' ) );
            add_action( 'wp_ajax_set_sub_report_dialog_data', array( $this, 'set_sub_report_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_sub_report_dialog_data', array( $this, 'set_sub_report_dialog_data' ) );
            add_action( 'wp_ajax_del_sub_report_dialog_data', array( $this, 'del_sub_report_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_sub_report_dialog_data', array( $this, 'del_sub_report_dialog_data' ) );

            add_action( 'wp_ajax_get_doc_category_dialog_data', array( $this, 'get_doc_category_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_doc_category_dialog_data', array( $this, 'get_doc_category_dialog_data' ) );
            add_action( 'wp_ajax_set_doc_category_dialog_data', array( $this, 'set_doc_category_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_doc_category_dialog_data', array( $this, 'set_doc_category_dialog_data' ) );
            add_action( 'wp_ajax_del_doc_category_dialog_data', array( $this, 'del_doc_category_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_doc_category_dialog_data', array( $this, 'del_doc_category_dialog_data' ) );

            add_action( 'wp_ajax_get_iso_category_dialog_data', array( $this, 'get_iso_category_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_iso_category_dialog_data', array( $this, 'get_iso_category_dialog_data' ) );
            add_action( 'wp_ajax_set_iso_category_dialog_data', array( $this, 'set_iso_category_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_iso_category_dialog_data', array( $this, 'set_iso_category_dialog_data' ) );
            add_action( 'wp_ajax_del_iso_category_dialog_data', array( $this, 'del_iso_category_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_iso_category_dialog_data', array( $this, 'del_iso_category_dialog_data' ) );
        }

        function enqueue_sub_items_scripts() {
            wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', '', '1.13.2');
            wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), null, true);

            wp_enqueue_script('sub-items', plugins_url('js/sub-items.js', __FILE__), array('jquery'), time());
            wp_localize_script('sub-items', 'ajax_object', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('sub-items-nonce'), // Generate nonce
            ));                
        }

        // embedded
        function register_embedded_post_type() {
            $labels = array(
                'menu_name'     => _x('embedded', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
            );
            register_post_type( 'embedded', $args );
        }
        
        function display_embedded_list() {
            ob_start();
            $profiles_class = new display_profiles();
            ?>
            <?php echo display_iso_helper_logo();?>
            <h2 style="display:inline;"><?php echo __( '嵌入項目', 'your-text-domain' );?></h2>

            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div><?php $profiles_class->display_select_profile('embedded');?></div>
                <div style="text-align: right"></div>                        
            </div>

            <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <th><?php echo __( 'Number', 'your-text-domain' );?></th>
                        <th><?php echo __( 'Title', 'your-text-domain' );?></th>
                        <th><?php echo __( 'ISO', 'your-text-domain' );?></th>
                    </thead>
                    <tbody>
                    <?php
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_embedded_data($paged);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $embedded_code = get_post_meta(get_the_ID(), 'embedded_code', true);
                            $iso_category = get_post_meta(get_the_ID(), 'iso_category', true);
                            ?>
                            <tr id="edit-embedded-<?php the_ID();?>">
                                <td style="text-align:center;"><?php echo esc_html($embedded_code);?></td>
                                <td><?php the_title();?></td>
                                <td style="text-align:center;"><?php echo get_the_title($iso_category);?></td>
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
            <div id="embedded-dialog" title="Sub form dialog"></div>
            <?php
            return ob_get_clean();
        }

        function retrieve_embedded_data($paged = 1) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            
            $args = array(
                'post_type'      => 'embedded',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
                'meta_query'     => array(
                    'relation' => 'OR',
                    array(
                        'key'     => 'is_private',
                        'compare' => 'NOT EXISTS', // Condition to check if the meta key does not exist
                    ),
                    array(
                        'key'     => 'is_private',
                        'value'   => '0',
                        'compare' => '=' // Condition to check if the meta value is 0
                    ),
                    array(
                        'relation' => 'AND',
                        array(
                            'key'     => 'is_private',
                            'value'   => '1',
                            'compare' => '='
                        ),
                        array(
                            'key'   => 'site_id',
                            'value' => $site_id,
                            'compare' => '='
                        )                        
                    ),
                ),

                'meta_key'       => 'embedded_code', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value
                'order'          => 'DESC', // Sorting order (ascending)

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
                // Remove the initial search query
                unset($args['s']);

                // Add meta query for searching across all meta keys
                $meta_keys = get_post_type_meta_keys('embedded');
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

        function display_embedded_dialog($embedded_id=false) {
            ob_start();
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $embedded_title = get_the_title($embedded_id);
            $embedded_code = get_post_meta($embedded_id, 'embedded_code', true);
            $iso_category = get_post_meta($embedded_id, 'iso_category', true);
            $embedded_site = get_post_meta($embedded_id, 'site_id', true);
            $is_private = get_post_meta($embedded_id, 'is_private', true);
            $is_private_checked = ($is_private==1) ? 'checked' : '';
            ?>
            <fieldset>
                <input type="hidden" id="embedded-id" value="<?php echo esc_attr($embedded_id);?>" />
                <input type="hidden" id="is-site-admin" value="<?php echo esc_attr(is_site_admin());?>" />
                <label for="embedded-code"><?php echo __( 'Number: ', 'your-text-domain' );?></label>
                <input type="text" id="embedded-code" value="<?php echo esc_attr($embedded_code);?>" class="text ui-widget-content ui-corner-all" />
                <label for="embedded-title"><?php echo __( 'Title: ', 'your-text-domain' );?></label>
                <input type="text" id="embedded-title" value="<?php echo esc_attr($embedded_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="sub-item-list"><?php echo __( 'Items: ', 'your-text-domain' );?></label>
                <div id="sub-item-list">
                    <?php echo $this->display_sub_item_list($embedded_id);?>
                </div>
                <label for="iso-category"><?php echo __( 'ISO: ', 'your-text-domain' );?></label>
                <select id="iso-category" class="text ui-widget-content ui-corner-all"><?php echo $this->select_iso_category_options($iso_category);?></select>
                <?php if ($embedded_site==$site_id || current_user_can('administrator')) {?>
                    <div>
                        <input type="checkbox" id="is-private" <?php echo $is_private_checked;?> /> 
                        <label for="is-private"><?php echo __( 'Is private', 'your-text-domain' );?></label>
                    </div>
                <?php }?>
            </fieldset>
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
                $embedded_code = (isset($_POST['_embedded_code'])) ? sanitize_text_field($_POST['_embedded_code']) : '';
                $embedded_title = (isset($_POST['_embedded_title'])) ? sanitize_text_field($_POST['_embedded_title']) : '';
                $iso_category = (isset($_POST['_iso_category'])) ? sanitize_text_field($_POST['_iso_category']) : 0;
                $is_private = (isset($_POST['_is_private'])) ? sanitize_text_field($_POST['_is_private']) : 0;
                $data = array(
                    'ID'           => $embedded_id,
                    'post_title'   => $embedded_title,
                );
                wp_update_post( $data );
                update_post_meta($embedded_id, 'embedded_code', $embedded_code);
                update_post_meta($embedded_id, 'iso_category', $iso_category);
                update_post_meta($embedded_id, 'is_private', $is_private);
            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $new_post = array(
                    'post_type'     => 'embedded',
                    'post_title'    => 'New embedded',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'site_id', $site_id);
                update_post_meta($post_id, 'embedded_code', time());
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
                $embedded_code = (isset($_POST['_embedded_code'])) ? sanitize_text_field($_POST['_embedded_code']) : '';
                $embedded_title = (isset($_POST['_embedded_title'])) ? sanitize_text_field($_POST['_embedded_title']) : '';
                $iso_category = (isset($_POST['_iso_category'])) ? sanitize_text_field($_POST['_iso_category']) : 0;
                // Create the post
                $new_post = array(
                    'post_type'     => 'embedded',
                    'post_title'    => $embedded_title.'(Duplicated)',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'site_id', $site_id);
                update_post_meta($post_id, 'embedded_code', time());
                update_post_meta($post_id, 'iso_category', $iso_category);
                update_post_meta($post_id, 'is_private', 1);

                $query = $this->retrieve_sub_item_list_data($embedded_id);
                if ($query->have_posts()) {
                    while ($query->have_posts()) : $query->the_post();
                        $sub_item_type = get_post_meta(get_the_ID(), 'sub_item_type', true);
                        $sub_item_default = get_post_meta(get_the_ID(), 'sub_item_default', true);
                        $sub_item_code = get_post_meta(get_the_ID(), 'sub_item_code', true);
                        $sorting_key = get_post_meta(get_the_ID(), 'sorting_key', true);
                        $new_sub_item = array(
                            'post_type'     => 'sub-item',
                            'post_title'    => get_the_title(),
                            'post_content'  => get_the_content(),
                            'post_status'   => 'publish',
                            'post_author'   => $current_user_id,
                        );    
                        $sub_item_id = wp_insert_post($new_sub_item);
                        update_post_meta($sub_item_id, 'sub_item_type', $sub_item_type);
                        update_post_meta($sub_item_id, 'sub_item_default', $sub_item_default);
                        update_post_meta($sub_item_id, 'sub_item_code', $sub_item_code);
                        update_post_meta($sub_item_id, 'sorting_key', $sorting_key);
                        update_post_meta($sub_item_id, 'embedded_id', $post_id);
                    endwhile;
                    wp_reset_postdata();
                }
            }
            $response = array('html_contain' => $this->display_embedded_list());
            wp_send_json($response);
        }

        function select_embedded_options($selected_option=0) {
            $query = $this->retrieve_embedded_data(0);
            $options = '<option value="">Select an option</option>';
            while ($query->have_posts()) : $query->the_post();
                $embedded_code = get_post_meta(get_the_ID(), 'embedded_code', true);
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html(get_the_title()) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }
        
        function get_embedded_post_id_by_code($embedded_code=false) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            // Define the query arguments
            $args = array(
                'post_type'  => 'embedded',
                'meta_query' => array(
                    array(
                        'key'   => 'site_id',
                        'value' => $site_id,
                        'compare' => '=',            // Comparison operator
                    ),
                    array(
                        'key'   => 'embedded_code',  // Meta key
                        'value' => $embedded_code,   // Meta value to match
                        'compare' => '=',            // Comparison operator
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

        // sub-item
        function register_sub_item_post_type() {
            $labels = array(
                'menu_name'     => _x('sub-item', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
            );
            register_post_type( 'sub-item', $args );
        }

        function display_sub_item_list($embedded_id=false) {
            ob_start();
            ?>
            <fieldset>
            <table style="width:100%;">
                <thead>
                    <tr>
                        <th><?php echo __( 'Items', 'your-text-domain' );?></th>
                        <th><?php echo __( 'Type', 'your-text-domain' );?></th>
                        <th><?php echo __( 'Default', 'your-text-domain' );?></th>
                    </tr>
                </thead>
                <tbody id="sortable-sub-item-list">
                <?php
                $query = $this->retrieve_sub_item_list_data($embedded_id);
                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post();
                        $sub_item_title = get_the_title();
                        $sub_item_code = get_post_meta(get_the_ID(), 'sub_item_code', true);
                        $sub_item_type = get_post_meta(get_the_ID(), 'sub_item_type', true);
                        $sub_item_default = get_post_meta(get_the_ID(), 'sub_item_default', true);
                        if ($sub_item_type=='heading') {
                            if (!$sub_item_default) {
                                $sub_item_code = '<b>'.$sub_item_code.'</b>';
                                $sub_item_title = '<b>'.$sub_item_title.'</b>';    
                            }
                            $sub_item_type='';
                            $sub_item_default='';
                        }
                        ?>
                        <tr id="edit-sub-item-<?php the_ID();?>" data-sub-item-id="<?php echo esc_attr(get_the_ID());?>">
                            <td><?php echo $sub_item_title;?></td>
                            <td style="text-align:center;"><?php echo esc_html($sub_item_type);?></td>
                            <td style="text-align:center;"><?php echo esc_html($sub_item_default);?></td>
                        </tr>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
                </tbody>
            </table>
            <?php if (is_site_admin()) {?>
                <div id="new-sub-item" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
            <?php }?>
            </fieldset>
            <div id="sub-item-dialog" title="Sub item dialog"></div>
            <?php
            return ob_get_clean();
        }

        function retrieve_sub_item_list_data($embedded_id=false) {
            $args = array(
                'post_type'      => 'sub-item',
                'posts_per_page' => -1,
                'meta_key'       => 'sorting_key',
                'orderby'        => 'meta_value_num', // Specify meta value as numeric
                'order'          => 'ASC', // Sorting order (ascending)
            );
            // Add sunform_id to meta_query if it is not false
            if ($embedded_id !== false) {
                $args['meta_query'][] = array(
                    array(
                        'key'   => 'embedded_id',
                        'value' => $embedded_id,
                    ),
                );
            }
            $query = new WP_Query($args);
            return $query;
        }

        function display_sub_item_dialog($sub_item_id=false) {
            ob_start();
            $sub_item_title = get_the_title($sub_item_id);
            $sub_item_type = get_post_meta($sub_item_id, 'sub_item_type', true);
            $sub_item_default = get_post_meta($sub_item_id, 'sub_item_default', true);
            $sub_item_code = get_post_meta($sub_item_id, 'sub_item_code', true);
            ?>
            <fieldset>
                <input type="hidden" id="sub-item-id" value="<?php echo esc_attr($sub_item_id);?>" />
                <input type="hidden" id="is-site-admin" value="<?php echo esc_attr(is_site_admin());?>" />
                <label for="sub-item-title"><?php echo __( 'Item: ', 'your-text-domain' );?></label>
                <textarea id="sub-item-title" rows="3" style="width:100%;"><?php echo $sub_item_title;?></textarea>
                <label for="sub-item-type"><?php echo __( 'Type: ', 'your-text-domain' );?></label>
                <select id="sub-item-type" class="text ui-widget-content ui-corner-all">
                    <option value="heading" <?php echo ($sub_item_type=='heading') ? 'selected' : ''?>><?php echo __( 'Heading', 'your-text-domain' );?></option>
                    <option value="checkbox" <?php echo ($sub_item_type=='checkbox') ? 'selected' : ''?>><?php echo __( 'Checkbox', 'your-text-domain' );?></option>
                    <option value="text" <?php echo ($sub_item_type=='text') ? 'selected' : ''?>><?php echo __( 'Text', 'your-text-domain' );?></option>
                    <option value="textarea" <?php echo ($sub_item_type=='textarea') ? 'selected' : ''?>><?php echo __( 'Textarea', 'your-text-domain' );?></option>
                    <option value="number" <?php echo ($sub_item_type=='number') ? 'selected' : ''?>><?php echo __( 'Number', 'your-text-domain' );?></option>
                    <option value="radio" <?php echo ($sub_item_type=='radio') ? 'selected' : ''?>><?php echo __( 'Radio', 'your-text-domain' );?></option>
                    <option value="_product" <?php echo ($sub_item_type=='_product') ? 'selected' : ''?>><?php echo __( '_product', 'your-text-domain' );?></option>
                </select>
                <label for="sub-item-default"><?php echo __( 'Default: ', 'your-text-domain' );?></label>
                <input type="text" id="sub-item-default" value="<?php echo esc_attr($sub_item_default);?>" class="text ui-widget-content ui-corner-all" />
                <label for="sub-item-code"><?php echo __( 'Note: ', 'your-text-domain' );?></label>
                <input type="text" id="sub-item-code" value="<?php echo esc_attr($sub_item_code);?>" class="text ui-widget-content ui-corner-all" />
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_sub_item_dialog_data() {
            $response = array();
            $sub_item_id = sanitize_text_field($_POST['_sub_item_id']);
            $response['html_contain'] = $this->display_sub_item_dialog($sub_item_id);
            wp_send_json($response);
        }

        function set_sub_item_dialog_data() {
            $embedded_id = sanitize_text_field($_POST['_embedded_id']);
            if( isset($_POST['_sub_item_id']) ) {
                $sub_item_id = sanitize_text_field($_POST['_sub_item_id']);
                $sub_item_code = sanitize_text_field($_POST['_sub_item_code']);
                $sub_item_type = sanitize_text_field($_POST['_sub_item_type']);
                $sub_item_default = sanitize_text_field($_POST['_sub_item_default']);
                $data = array(
                    'ID'           => $sub_item_id,
                    'post_title'   => sanitize_text_field($_POST['_sub_item_title']),
                );
                wp_update_post( $data );
                update_post_meta($sub_item_id, 'sub_item_code', $sub_item_code);
                update_post_meta($sub_item_id, 'sub_item_type', $sub_item_type);
                update_post_meta($sub_item_id, 'sub_item_default', $sub_item_default);
            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $new_post = array(
                    'post_title'    => 'New item',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'sub-item',
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'embedded_id', $embedded_id);
                update_post_meta($post_id, 'sorting_key', 999);
            }
            $response = array('html_contain' => $this->display_sub_item_list($embedded_id));
            wp_send_json($response);
        }

        function del_sub_item_dialog_data() {
            $embedded_id = sanitize_text_field($_POST['_embedded_id']);
            wp_delete_post($_POST['_sub_item_id'], true);
            $response = array('html_contain' => $this->display_sub_item_list($embedded_id));
            wp_send_json($response);
        }

        function sort_sub_item_list_data() {
            $response = array('success' => false, 'error' => 'Invalid data format');
            if (isset($_POST['_sub_item_id_array']) && is_array($_POST['_sub_item_id_array'])) {
                $sub_item_id_array = array_map('absint', $_POST['_sub_item_id_array']);        
                foreach ($sub_item_id_array as $index => $sub_item_id) {
                    update_post_meta($sub_item_id, 'sorting_key', $index);
                }
                $response = array('success' => true);
            }
            wp_send_json($response);
        }

        function select_sub_item_options($selected_option=false, $embedded_id=false) {
            $query = $this->retrieve_sub_item_list_data($embedded_id);
            $options = '<option value="">Select '.get_the_title($embedded_id).'</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $sub_item_code = get_post_meta(get_the_ID(), 'sub_item_code', true);
                $sub_item_type = get_post_meta(get_the_ID(), 'sub_item_type', true);
                if ($sub_item_type=='heading'){
                    $sub_item_title = '<b>'.get_the_title().'</b>';
                } else {
                    $sub_item_title = $sub_item_code.' '.get_the_title();
                    $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . $sub_item_title . '</option>';
                }
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        function get_sub_item_contains($field_id=false, $sub_item_id=false, $sub_item_value=false) {
            $sub_item_title = get_the_title($sub_item_id);
            $sub_item_code = get_post_meta($sub_item_id, 'sub_item_code', true);
            $sub_item_type = get_post_meta($sub_item_id, 'sub_item_type', true);
            $sub_item_default = get_post_meta($sub_item_id, 'sub_item_default', true);
            if ($sub_item_type=='heading') {
                $sub_item_default = ($sub_item_default) ? $sub_item_default : 'b';
                ?>
                <div><<?php echo esc_html($sub_item_default);?>><?php echo esc_html($sub_item_title.' '.$sub_item_code);?></<?php echo esc_html($sub_item_default);?>></div>
                <?php
            } elseif ($sub_item_type=='checkbox') {
                $is_checked = ($sub_item_value==1) ? 'checked' : '';
                ?>
                <div>
                <input type="checkbox" class="sub-item-class" id="<?php echo esc_attr($field_id.$sub_item_id);?>" <?php echo $is_checked;?> /> <?php echo $sub_item_code.' '.$sub_item_title?>
                </div>
                <?php
            } elseif ($sub_item_type=='textarea') {
                ?>
                <label for="<?php echo esc_attr($field_id.$sub_item_id);?>"><?php echo esc_html($sub_item_title.' '.$sub_item_code);?></label>
                <textarea class="sub-item-class" id="<?php echo esc_attr($field_id.$sub_item_id);?>" rows="3" style="width:100%;"><?php echo esc_html($sub_item_value);?></textarea>
                <?php
            } elseif ($sub_item_type=='text') {
                ?>
                <label for="<?php echo esc_attr($field_id.$sub_item_id);?>"><?php echo esc_html($sub_item_title.' '.$sub_item_code);?></label>
                <input type="text" id="<?php echo esc_attr($field_id.$sub_item_id);?>" value="<?php echo esc_html($sub_item_value);?>" class="text ui-widget-content ui-corner-all sub-item-class" />
                <?php
            } elseif ($sub_item_type=='number') {
                ?>
                <label for="<?php echo esc_attr($field_id.$sub_item_id);?>"><?php echo esc_html($sub_item_title.' '.$sub_item_code);?></label>
                <input type="number" id="<?php echo esc_attr($field_id.$sub_item_id);?>" value="<?php echo esc_html($sub_item_value);?>" class="number ui-widget-content ui-corner-all sub-item-class" />
                <?php
            } elseif ($sub_item_type=='radio') {
                $is_checked = ($sub_item_value==1) ? 'checked' : '';
                ?>
                <div>
                <input type="radio" class="sub-item-class" id="<?php echo esc_attr($field_id.$sub_item_id);?>" name="<?php echo esc_attr(substr($field_id, 0, 5));?>" <?php echo $is_checked;?> /> <?php echo $sub_item_title.' '.$sub_item_code?>
                </div>
                <?php
            } else {
                ?>
                <div><?php echo $sub_item_title.' '.$sub_item_code?></div>
                <?php
            }
        }

        function get_sub_item_ids($embedded_id=false) {
            $args = array(
                'post_type'  => 'sub-item',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key'   => 'embedded_id',
                        'value' => $embedded_id,
                        'compare' => '='
                    ),
                    array(
                        'key'   => 'sub_item_type',
                        'value' => 'heading',
                        'compare' => '!='
                    )
                ),
                'fields' => 'ids' // Only retrieve the post IDs
            );        
            $query = new WP_Query($args);        
            // Retrieve the post IDs
            $post_ids = $query->posts;        
            wp_reset_postdata();        
            return $post_ids;
        }

        function get_sub_item_field_keys($doc_id=false) {
            if ($doc_id) $params = array('doc_id' => $doc_id);
            $documents_class = new display_documents();
            $query = $documents_class->retrieve_doc_field_data($params);
            $_array = array();
            if ($query->have_posts()) {
                while ($query->have_posts()) : $query->the_post();
                    $field_type = get_post_meta(get_the_ID(), 'field_type', true);
                    $default_value = get_post_meta(get_the_ID(), 'default_value', true);

                    if ($field_type=='_embedded'||$field_type=='_planning'||$field_type=='_select') {
                        if ($default_value) {
                            $embedded_id = $this->get_embedded_post_id_by_code($default_value);
                            $inner_query = $this->retrieve_sub_item_list_data($embedded_id);
                            if ($inner_query->have_posts()) :
                                while ($inner_query->have_posts()) : $inner_query->the_post();
                                    $_list = array();
                                    $_list["embedded_id"] = $embedded_id;
                                    $_list["sub_item_id"] = get_the_ID();
                                    $_list["sub_item_type"] = get_post_meta(get_the_ID(), 'sub_item_type', true);
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

        function get_sub_report_keys($embedded_id=false) {
            $_array = array();
            $inner_query = $this->retrieve_sub_item_list_data($embedded_id);
            if ($inner_query->have_posts()) :
                while ($inner_query->have_posts()) : $inner_query->the_post();
                    $_list = array();
                    $_list["sub_item_id"] = get_the_ID();
                    $_list["sub_item_type"] = get_post_meta(get_the_ID(), 'sub_item_type', true);
                    array_push($_array, $_list);

                endwhile;
                wp_reset_postdata();
            endif;
            return $_array;
        }

        // sub-report
        function display_sub_report_list($embedded_id=false, $report_id=false) {
            ob_start();
            ?>
            <input type="hidden" id="embedded-id" value="<?php echo esc_attr($embedded_id);?>">
            <fieldset>
            <table style="width:100%;">
                <thead>
                <tr>
                <th>#</th>
                <?php                
                $query = $this->retrieve_sub_item_list_data($embedded_id);
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
                $sub_report_query = $this->retrieve_sub_report_list_data($report_id);
                if ($sub_report_query->have_posts()) :
                    while ($sub_report_query->have_posts()) : $sub_report_query->the_post();
                        ?><tr id="edit-sub-report-<?php the_ID();?>"><td style="text-align:center;"></td><?php
                        $query = $this->retrieve_sub_item_list_data($embedded_id);
                        if ($query->have_posts()) :
                            while ($query->have_posts()) : $query->the_post();
                                $field_type = get_post_meta(get_the_ID(), 'sub_item_type', true);
                                $field_value = get_post_meta(get_the_ID(), $embedded_id.get_the_ID(), true);
                                $text_align = ($field_type=='number') ? 'style="text-align:center;"' : '';
                                if ($field_type=='_product') {
                                    $product_code = get_post_meta($field_value, 'product_code', true);
                                    $field_value = get_the_title($field_value).'('.$product_code.')'; 
                                }
                                ?><td <?php echo $text_align;?>><?php echo esc_html($field_value);?></td><?php
                            endwhile;
                            wp_reset_postdata();
                        endif;
                        ?></tr><?php
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
                </tbody>
            </table>
            <?php if (is_site_admin()) {?>
                <div id="new-sub-report" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
            <?php }?>
            </fieldset>
            <div id="sub-report-dialog" title="Sub report dialog"></div>
            <?php
            return ob_get_clean();
        }

        function retrieve_sub_report_list_data($report_id=false) {
            $args = array(
                'post_type'      => 'sub-report',
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

        function display_sub_report_dialog($sub_report_id=false, $embedded_id=false) {
            ob_start();
            $report_id = get_post_meta($sub_report_id, 'report_id', true);
            ?>
            <fieldset>
                <input type="hidden" id="sub-report-id" value="<?php echo esc_attr($sub_report_id);?>" />
                <input type="hidden" id="is-site-admin" value="<?php echo esc_attr(is_site_admin());?>" />
                <?php
                $query = $this->retrieve_sub_item_list_data($embedded_id);
                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post();
                        ?>
                        <label for="<?php echo esc_attr($embedded_id.get_the_ID());?>"><?php echo esc_html(get_the_title());?></label>
                        <?php
                        if ($sub_report_id) {
                            $field_value = get_post_meta($sub_report_id, $embedded_id.get_the_ID(), true);
                        } else {
                            $field_value = get_post_meta(get_the_ID(), 'sub_item_default', true);
                        }
                        $field_type = get_post_meta(get_the_id(), 'sub_item_type', true);
                        if ($field_type=='_product') {
                            $cards_class = new erp_cards();
                            ?>
                            <select id="<?php echo esc_attr($embedded_id.get_the_ID());?>" class="text ui-widget-content ui-corner-all"><?php echo $cards_class->select_product_card_options($field_value);?></select>
                            <?php
                        } else {
                            ?>
                            <input type="<?php echo esc_attr($field_type);?>" id="<?php echo esc_attr($embedded_id.get_the_ID());?>" value="<?php echo esc_html($field_value);?>"  class="text ui-widget-content ui-corner-all" />
                            <?php    
                        }
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_sub_report_dialog_data() {
            $sub_report_id = sanitize_text_field($_POST['_sub_report_id']);
            $embedded_id = sanitize_text_field($_POST['_embedded_id']);
            $response = array('html_contain' => $this->display_sub_report_dialog($sub_report_id, $embedded_id));
            $response['sub_report_fields'] = $this->get_sub_report_keys($embedded_id);
            wp_send_json($response);
        }

        function set_sub_report_dialog_data() {
            $report_id = sanitize_text_field($_POST['_report_id']);
            $embedded_id = sanitize_text_field($_POST['_embedded_id']);
            if( isset($_POST['_sub_report_id']) ) {
                // Update the post
                $sub_report_id = sanitize_text_field($_POST['_sub_report_id']);
                $query = $this->retrieve_sub_item_list_data($embedded_id);
                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post();
                        $field_value = $_POST[$embedded_id.get_the_id()];
                        update_post_meta($sub_report_id, $embedded_id.get_the_id(), $field_value);
                    endwhile;
                    wp_reset_postdata();
                endif;
            } else {
                // Create the post
                $current_user_id = get_current_user_id();
                $new_post = array(
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'sub-report',
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'report_id', $report_id);

            }
            $response = array('html_contain' => $this->display_sub_report_list($embedded_id, $report_id));
            wp_send_json($response);
        }

        function del_sub_report_dialog_data() {
            wp_delete_post($_POST['_sub_report_id'], true);
            $embedded_id = sanitize_text_field($_POST['_embedded_id']);
            $report_id = sanitize_text_field($_POST['_report_id']);
            $response = array('html_contain' => $this->display_sub_report_list($embedded_id, $report_id));
            wp_send_json($response);
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
            <?php echo display_iso_helper_logo();?>
            <h2 style="display:inline;"><?php echo __( '文件類別', 'your-text-domain' );?></h2>

            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div><?php $profiles_class->display_select_profile('doc-category');?></div>
                <div style="text-align: right"></div>                        
            </div>

            <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <th><?php echo __( 'Category', 'your-text-domain' );?></th>
                        <th><?php echo __( 'Description', 'your-text-domain' );?></th>
                        <th><?php echo __( 'ISO', 'your-text-domain' );?></th>
                    </thead>
                    <tbody>
                    <?php
                    $query = $this->retrieve_doc_category_data();
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $iso_category = get_post_meta(get_the_ID(), 'iso_category', true);
                            ?>
                            <tr id="edit-doc-category-<?php the_ID();?>">
                                <td style="text-align:center;"><?php the_title();?></td>
                                <td><?php the_content();?></td>
                                <td style="text-align:center;"><?php echo get_the_title($iso_category);?></td>
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
            <div id="doc-category-dialog" title="Category dialog"></div>
            <?php
            return ob_get_clean();
        }

        function retrieve_doc_category_data() {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $args = array(
                'post_type'      => 'doc-category',
                'posts_per_page' => -1,        
                'meta_query'     => array(
                    array(
                        'key'   => 'site_id',
                        'value' => $site_id,
                    ),
                ),
                'orderby'        => 'title',  // Order by post title
                'order'          => 'ASC',    // Order in ascending order (or use 'DESC' for descending)

            );
            $query = new WP_Query($args);
            return $query;
        }

        function display_doc_category_dialog($category_id=false) {
            ob_start();
            $cards_class = new erp_cards();
            $category_title = get_the_title($category_id);
            $category_content = get_post_field('post_content', $category_id);
            $iso_category = get_post_meta($category_id, 'iso_category', true);
            ?>
            <fieldset>
                <input type="hidden" id="category-id" value="<?php echo esc_attr($category_id);?>" />
                <input type="hidden" id="is-site-admin" value="<?php echo esc_attr(is_site_admin());?>" />
                <label for="category-title"><?php echo __( 'Category: ', 'your-text-domain' );?></label>
                <input type="text" id="category-title" value="<?php echo esc_attr($category_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="category-content"><?php echo __( 'Description: ', 'your-text-domain' );?></label>
                <textarea id="category-content" rows="5" style="width:100%;"><?php echo esc_html($category_content);?></textarea>
                <label for="iso-category"><?php echo __( 'ISO: ', 'your-text-domain' );?></label>
                <select id="iso-category" class="text ui-widget-content ui-corner-all"><?php echo $this->select_iso_category_options($iso_category);?></select>
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
                $category_id = sanitize_text_field($_POST['_category_id']);
                $category_url = sanitize_text_field($_POST['_category_url']);
                $iso_category = sanitize_text_field($_POST['_iso_category']);
                $data = array(
                    'ID'           => $category_id,
                    'post_title'   => sanitize_text_field($_POST['_category_title']),
                    'post_content' => $_POST['_category_content'],
                );
                wp_update_post( $data );
                update_post_meta($category_id, 'iso_category', $iso_category);
            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $new_post = array(
                    'post_title'    => 'New category',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'doc-category',
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'site_id', $site_id);
            }
            $response = array('html_contain' => $this->display_doc_category_list());
            wp_send_json($response);
        }

        function del_doc_category_dialog_data() {
            wp_delete_post($_POST['_category_id'], true);
            $response = array('html_contain' => $this->display_doc_category_list());
            wp_send_json($response);
        }

        function select_doc_category_options($selected_option=0) {
            $query = $this->retrieve_doc_category_data();
            $options = '<option value="">Select category</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html(get_the_title()) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }
        
        // iso-category
        function register_iso_category_post_type() {
            $labels = array(
                'menu_name'     => _x('iso-category', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
            );
            register_post_type( 'iso-category', $args );
        }
        
        function display_iso_category_list() {
            ob_start();
            $profiles_class = new display_profiles();
            if (current_user_can('administrator')) {
                ?>
                <?php echo display_iso_helper_logo();?>
                <h2 style="display:inline;"><?php echo __( 'ISO類別', 'your-text-domain' );?></h2>

                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div><?php $profiles_class->display_select_profile('iso-category');?></div>
                    <div style="text-align:right"></div>                        
                </div>

                <fieldset>
                    <table class="ui-widget" style="width:100%;">
                        <thead>
                            <th><?php echo __( 'ISO', 'your-text-domain' );?></th>
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
            } else {
                ?>
                <p><?php echo __( 'You do not have permission to access this page.', 'your-text-domain' );?></p>
                <?php
            }
            return ob_get_clean();
        }

        function retrieve_iso_category_data() {
            $args = array(
                'post_type'      => 'iso-category',
                'posts_per_page' => -1,        
                'orderby'        => 'title',  // Order by post title
                'order'          => 'ASC',    // Order in ascending order (or use 'DESC' for descending)
            );
            $query = new WP_Query($args);
            return $query;
        }

        function display_iso_category_dialog($category_id=false) {
            $category_title = get_the_title($category_id);
            $category_content = get_post_field('post_content', $category_id);
            $category_url = get_post_meta($category_id, 'category_url', true);
            $parent_category = get_post_meta($category_id, 'parent_category', true);
            $embedded = get_post_meta($category_id, 'embedded', true);
            ob_start();
            ?>
            <fieldset>
                <input type="hidden" id="category-id" value="<?php echo esc_attr($category_id);?>" />
                <label for="category-title"><?php echo __( 'Title: ', 'your-text-domain' );?></label>
                <input type="text" id="category-title" value="<?php echo esc_attr($category_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="category-content"><?php echo __( 'Description: ', 'your-text-domain' );?></label>
                <textarea id="category-content" rows="5" style="width:100%;"><?php echo esc_html($category_content);?></textarea>
                <label for="category-url"><?php echo __( 'URL: ', 'your-text-domain' );?></label>
                <input type="text" id="category-url" value="<?php echo esc_attr($category_url);?>" class="text ui-widget-content ui-corner-all" />
                <label for="parent-category"><?php echo __( 'Parent: ', 'your-text-domain' );?></label>
                <select id="parent-category" class="text ui-widget-content ui-corner-all"><?php echo $this->select_parent_category_options($parent_category);?></select>
                <label for="embedded"><?php echo __( 'Statement: ', 'your-text-domain' );?></label>
                <select id="embedded" class="text ui-widget-content ui-corner-all"><?php echo $this->select_embedded_options($embedded);?></select>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_iso_category_dialog_data() {
            $response = array();
            $category_id = sanitize_text_field($_POST['_category_id']);
            $response['html_contain'] = $this->display_iso_category_dialog($category_id);
            wp_send_json($response);
        }

        function set_iso_category_dialog_data() {
            if( isset($_POST['_category_id']) ) {
                $category_id = sanitize_text_field($_POST['_category_id']);
                $category_url = sanitize_text_field($_POST['_category_url']);
                $parent_category = sanitize_text_field($_POST['_parent_category']);
                $embedded = sanitize_text_field($_POST['_embedded']);
                $data = array(
                    'ID'           => $category_id,
                    'post_title'   => sanitize_text_field($_POST['_category_title']),
                    'post_content' => $_POST['_category_content'],
                );
                wp_update_post( $data );
                update_post_meta($category_id, 'category_url', $category_url);
                update_post_meta($category_id, 'parent_category', $parent_category);
                update_post_meta($category_id, 'embedded', $embedded);
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

        function get_iso_category_post_id_by_code($code) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            // Define the query arguments
            $args = array(
                'post_type'  => 'iso-category',
                'meta_query' => array(
                    array(
                        'key'   => 'site_id',
                        'value' => $site_id,
                    ),
                    array(
                        'key'   => 'category_code',  // Meta key
                        'value' => $code,            // Meta value to match
                        'compare' => '=',            // Comparison operator
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
    }
    $items_class = new sub_items();
}