<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (!class_exists('sub_items')) {
    class sub_items {
        // Class constructor
        public function __construct() {
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_sub_items_scripts' ) );
            add_action( 'init', array( $this, 'register_sub_category_post_type' ) );

            add_action( 'wp_ajax_get_doc_category_dialog_data', array( $this, 'get_doc_category_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_doc_category_dialog_data', array( $this, 'get_doc_category_dialog_data' ) );
            add_action( 'wp_ajax_set_doc_category_dialog_data', array( $this, 'set_doc_category_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_doc_category_dialog_data', array( $this, 'set_doc_category_dialog_data' ) );
            add_action( 'wp_ajax_del_doc_category_dialog_data', array( $this, 'del_doc_category_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_doc_category_dialog_data', array( $this, 'del_doc_category_dialog_data' ) );

            add_action( 'wp_ajax_get_sub_category_dialog_data', array( $this, 'get_sub_category_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_sub_category_dialog_data', array( $this, 'get_sub_category_dialog_data' ) );
            add_action( 'wp_ajax_set_sub_category_dialog_data', array( $this, 'set_sub_category_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_sub_category_dialog_data', array( $this, 'set_sub_category_dialog_data' ) );
            add_action( 'wp_ajax_del_sub_category_dialog_data', array( $this, 'del_sub_category_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_sub_category_dialog_data', array( $this, 'del_sub_category_dialog_data' ) );

            add_action( 'wp_ajax_get_sub_item_dialog_data', array( $this, 'get_sub_item_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_sub_item_dialog_data', array( $this, 'get_sub_item_dialog_data' ) );
            add_action( 'wp_ajax_set_sub_item_dialog_data', array( $this, 'set_sub_item_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_sub_item_dialog_data', array( $this, 'set_sub_item_dialog_data' ) );
            add_action( 'wp_ajax_del_sub_item_dialog_data', array( $this, 'del_sub_item_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_sub_item_dialog_data', array( $this, 'del_sub_item_dialog_data' ) );

            add_action( 'wp_ajax_get_sub_items_from_selection', array( $this, 'get_sub_items_from_selection' ) );
            add_action( 'wp_ajax_nopriv_get_sub_items_from_selection', array( $this, 'get_sub_items_from_selection' ) );
            
            add_action( 'wp_ajax_sort_sub_item_list_data', array( $this, 'sort_sub_item_list_data' ) );
            add_action( 'wp_ajax_nopriv_sort_sub_item_list_data', array( $this, 'sort_sub_item_list_data' ) );

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

            wp_enqueue_script('sub-items', plugins_url('sub-items.js', __FILE__), array('jquery'), time());
            wp_localize_script('sub-items', 'ajax_object', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('sub-items-nonce'), // Generate nonce
            ));                
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
            $profiles_class = new display_profiles();
            $is_site_admin = $profiles_class->is_site_admin();
            if (current_user_can('administrator')) $is_site_admin = true;
            ob_start();
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
                <?php if ($is_site_admin) {?>
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

        function display_doc_category_dialog($paged=1, $category_id=false) {
            ob_start();
            $cards_class = new erp_cards();
            //$items_class = new sub_items();
            $profiles_class = new display_profiles();
            $is_site_admin = $profiles_class->is_site_admin();
            if (current_user_can('administrator')) $is_site_admin = true;
            $category_title = get_the_title($category_id);
            $category_content = get_post_field('post_content', $category_id);
            $iso_category = get_post_meta($category_id, 'iso_category', true);
            ?>
            <fieldset>
                <input type="hidden" id="category-id" value="<?php echo esc_attr($category_id);?>" />
                <input type="hidden" id="is-site-admin" value="<?php echo esc_attr($is_site_admin);?>" />
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
            $paged = sanitize_text_field($_POST['paged']);
            $response['html_contain'] = $this->display_doc_category_dialog($paged, $category_id);
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

        // sub-category
        function register_sub_category_post_type() {
            $labels = array(
                'menu_name'     => _x('sub-category', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
            );
            register_post_type( 'sub-category', $args );
        }
        
        function display_sub_category_list() {
            $profiles_class = new display_profiles();
            $is_site_admin = $profiles_class->is_site_admin();
            if (current_user_can('administrator')) $is_site_admin = true;
            ob_start();
            ?>
            <?php echo display_iso_helper_logo();?>
            <h2 style="display:inline;"><?php echo __( '子項目類別', 'your-text-domain' );?></h2>

            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div><?php $profiles_class->display_select_profile('sub-category');?></div>
                <div style="text-align: right"></div>                        
            </div>

            <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <th><?php echo __( 'Code', 'your-text-domain' );?></th>
                        <th><?php echo __( 'Category', 'your-text-domain' );?></th>
                        <th><?php echo __( 'ISO', 'your-text-domain' );?></th>
                    </thead>
                    <tbody>
                    <?php
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_sub_category_data($paged);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / get_option('operation_row_counts')); // Calculate the total number of pages
                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $category_code = get_post_meta(get_the_ID(), 'category_code', true);
                            $iso_category = get_post_meta(get_the_ID(), 'iso_category', true);
                            ?>
                            <tr id="edit-sub-category-<?php the_ID();?>">
                                <td style="text-align:center;"><?php echo esc_html($category_code);?></td>
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
                <?php if ($is_site_admin) {?>
                    <div id="new-sub-category" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
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
            <div id="sub-category-dialog" title="Category dialog"></div>
            <?php
            return ob_get_clean();
        }

        function retrieve_sub_category_data($paged = 1) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            
            $args = array(
                'post_type'      => 'sub-category',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
                'meta_query'     => array(
                    array(
                        'key'   => 'site_id',
                        'value' => $site_id,
                    ),
                ),
                'meta_key'       => 'category_code', // Meta key for sorting
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
                $meta_keys = get_post_type_meta_keys('sub-category');
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

        function display_sub_category_dialog($category_id=false) {
            ob_start();
            $profiles_class = new display_profiles();
            $is_site_admin = $profiles_class->is_site_admin();
            if (current_user_can('administrator')) $is_site_admin = true;
            $category_title = get_the_title($category_id);
            $category_code = get_post_meta($category_id, 'category_code', true);
            $iso_category = get_post_meta($category_id, 'iso_category', true);
            ?>
            <fieldset>
                <input type="hidden" id="category-id" value="<?php echo esc_attr($category_id);?>" />
                <input type="hidden" id="is-site-admin" value="<?php echo esc_attr($is_site_admin);?>" />
                <label for="category-code"><?php echo __( 'Code: ', 'your-text-domain' );?></label>
                <input type="text" id="category-code" value="<?php echo esc_attr($category_code);?>" class="text ui-widget-content ui-corner-all" />
                <label for="category-title"><?php echo __( 'Category: ', 'your-text-domain' );?></label>
                <input type="text" id="category-title" value="<?php echo esc_attr($category_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="sub-item-list"><?php echo __( 'Items: ', 'your-text-domain' );?></label>
                <?php echo $this->display_sub_item_list($category_id);?>
                <label for="iso-category"><?php echo __( 'ISO: ', 'your-text-domain' );?></label>
                <select id="iso-category" class="text ui-widget-content ui-corner-all"><?php echo $this->select_iso_category_options($iso_category);?></select>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_sub_category_dialog_data() {
            $response = array();
            $category_id = sanitize_text_field($_POST['_category_id']);
            //$paged = sanitize_text_field($_POST['paged']);
            $response['html_contain'] = $this->display_sub_category_dialog($category_id);
            wp_send_json($response);
        }

        function set_sub_category_dialog_data() {
            if( isset($_POST['_category_id']) ) {
                $category_id = sanitize_text_field($_POST['_category_id']);
                $category_code = sanitize_text_field($_POST['_category_code']);
                $iso_category = sanitize_text_field($_POST['_iso_category']);
                $data = array(
                    'ID'           => $category_id,
                    'post_title'   => sanitize_text_field($_POST['_category_title']),
                );
                wp_update_post( $data );
                update_post_meta($category_id, 'category_code', $category_code);
                update_post_meta($category_id, 'iso_category', $iso_category);
            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $new_post = array(
                    'post_title'    => 'New category',
                    'post_content'  => 'Your post content goes here.',
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                    'post_type'     => 'sub-category',
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'site_id', $site_id);
                update_post_meta($post_id, 'category_code', time());
            }
            $response = array('html_contain' => $this->display_sub_category_list());
            wp_send_json($response);
        }

        function del_sub_category_dialog_data() {
            wp_delete_post($_POST['_category_id'], true);
            $response = array('html_contain' => $this->display_sub_category_list());
            wp_send_json($response);
        }

        function select_sub_category_options($selected_option=0) {
            $query = $this->retrieve_sub_category_data();
            $options = '<option value="">Select category</option>';
            while ($query->have_posts()) : $query->the_post();
                $category_code = get_post_meta(get_the_ID(), 'category_code', true);
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . esc_html(get_the_title().'('.$category_code.')') . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }
        
        function get_sub_category_post_id_by_code($code) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            // Define the query arguments
            $args = array(
                'post_type'  => 'sub-category',
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

        function display_sub_item_list($category_id=false) {
            $profiles_class = new display_profiles();
            $is_site_admin = $profiles_class->is_site_admin();
            if (current_user_can('administrator')) $is_site_admin = true;
            ob_start();
            ?>
            <div id="sub-item-list">
            <fieldset>
            <table style="width:100%;">
                <thead>
                    <tr>
                        <th><?php echo __( '#', 'your-text-domain' );?></th>
                        <th><?php echo __( 'Items', 'your-text-domain' );?></th>
                        <th><?php echo __( 'Type', 'your-text-domain' );?></th>
                        <th><?php echo __( 'Default', 'your-text-domain' );?></th>
                    </tr>
                </thead>
                <tbody id="sortable-sub-item-list">
                <?php
                
                $query = $this->retrieve_sub_item_list_data($category_id);
                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post();
                        $sub_item_title = get_the_title();
                        $sub_item_code = get_post_meta(get_the_ID(), 'sub_item_code', true);
                        $sub_item_type = get_post_meta(get_the_ID(), 'sub_item_type', true);
                        $sub_item_default = get_post_meta(get_the_ID(), 'sub_item_default', true);
                        if ($sub_item_type=='heading') {
                            $sub_item_code = '<b>'.$sub_item_code.'</b>';
                            $sub_item_title = '<b>'.$sub_item_title.'</b>';
                            $sub_item_type='';
                            $sub_item_default='';
                        }
                        if ($sub_item_type=='label') {
                            $sub_item_type='';
                            $sub_item_default='';
                        }
                        ?>
                        <tr id="edit-sub-item-<?php the_ID();?>" data-sub-item-id="<?php echo esc_attr(get_the_ID());?>">
                            <td style="text-align:center;"><?php echo $sub_item_code;?></td>
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
            <?php if ($is_site_admin) {?>
                <div id="new-sub-item" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
            <?php }?>
            </fieldset>
            </div>
            <div id="sub-item-dialog" title="Check item dialog"></div>
            <?php
            return ob_get_clean();
        }

        function retrieve_sub_item_list_data($category_id=false) {
            $args = array(
                'post_type'      => 'sub-item',
                'posts_per_page' => -1,
                'meta_key'       => 'sorting_key',
                'orderby'        => 'meta_value_num', // Specify meta value as numeric
                'order'          => 'ASC', // Sorting order (ascending)
            );

            // Add category_id to meta_query if it is not false
            if ($category_id !== false) {
                $args['meta_query'][] = array(
                    array(
                        'key'   => 'category_id',
                        'value' => $category_id,
                    ),
                );
            }

            $query = new WP_Query($args);
            return $query;
        }

        function display_sub_item_dialog($sub_item_id=false) {
            ob_start();
            $category_id = get_post_meta($sub_item_id, 'category_id', true);
            $sub_item_code = get_post_meta($sub_item_id, 'sub_item_code', true);
            $sub_item_title = get_the_title($sub_item_id);
            $sub_item_type = get_post_meta($sub_item_id, 'sub_item_type', true);
            ?>
            <fieldset>
                <input type="hidden" id="sub-item-id" value="<?php echo esc_attr($sub_item_id);?>" />
                <input type="hidden" id="is-site-admin" value="<?php echo esc_attr($is_site_admin);?>" />
                <label for="sub-item-code"><?php echo __( '#: ', 'your-text-domain' );?></label>
                <input type="text" id="sub-item-code" value="<?php echo esc_attr($sub_item_code);?>" class="text ui-widget-content ui-corner-all" />
                <label for="sub-item-title"><?php echo __( 'Item: ', 'your-text-domain' );?></label>
                <input type="text" id="sub-item-title" value="<?php echo esc_attr($sub_item_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="sub-item-type"><?php echo __( 'Type: ', 'your-text-domain' );?></label>
                <select id="sub-item-type" class="text ui-widget-content ui-corner-all">
                    <option value="heading" <?php echo ($sub_item_type=='heading') ? 'selected' : ''?>><?php echo __( 'Heading', 'your-text-domain' );?></option>
                    <option value="checkbox" <?php echo ($sub_item_type=='checkbox') ? 'selected' : ''?>><?php echo __( 'Checkbox', 'your-text-domain' );?></option>
                    <option value="text" <?php echo ($sub_item_type=='text') ? 'selected' : ''?>><?php echo __( 'Text', 'your-text-domain' );?></option>
                    <option value="textarea" <?php echo ($sub_item_type=='textarea') ? 'selected' : ''?>><?php echo __( 'Textarea', 'your-text-domain' );?></option>
                    <option value="radio" <?php echo ($sub_item_type=='radio') ? 'selected' : ''?>><?php echo __( 'Radio', 'your-text-domain' );?></option>
                    <option value="label" <?php echo ($sub_item_type=='label') ? 'selected' : ''?>><?php echo __( 'Label', 'your-text-domain' );?></option>
                </select>
                <label for="sub-item-default"><?php echo __( 'Default: ', 'your-text-domain' );?></label>
                <input type="text" id="sub-item-default" value="<?php echo esc_attr($sub_item_default);?>" class="text ui-widget-content ui-corner-all" />
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
            $category_id = sanitize_text_field($_POST['_category_id']);
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
                update_post_meta($post_id, 'category_id', $category_id);
                update_post_meta($post_id, 'sorting_key', 999);
            }
            $response = array('html_contain' => $this->display_sub_item_list($category_id));
            wp_send_json($response);
        }

        function del_sub_item_dialog_data() {
            $category_id = sanitize_text_field($_POST['_category_id']);
            wp_delete_post($_POST['_sub_item_id'], true);
            $response = array('html_contain' => $this->display_sub_item_list($category_id));
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

        function select_sub_item_options($selected_option=false, $category_id=false) {
            $query = $this->retrieve_sub_item_list_data($category_id);
            $options = '<option value="">'.get_the_title($category_id).'</option>';
            while ($query->have_posts()) : $query->the_post();
                $selected = ($selected_option == get_the_ID()) ? 'selected' : '';
                $sub_item_code = get_post_meta(get_the_ID(), 'sub_item_code', true);
                $sub_item_type = get_post_meta(get_the_ID(), 'sub_item_type', true);
                if ($sub_item_type=='heading'){
                    $sub_item_title = '<b>'.get_the_title().'</b>';
                } else {
                    $sub_item_title = $sub_item_code.' '.get_the_title();
                }
                $options .= '<option value="' . esc_attr(get_the_ID()) . '" '.$selected.' />' . $sub_item_title . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        function get_sub_item_contains($sub_item_id=false, $field_id=false) {
            $field_name = get_post_meta($field_id, 'field_name', true);
            $sub_item_title = get_the_title($sub_item_id);
            $sub_item_code = get_post_meta($sub_item_id, 'sub_item_code', true);
            $sub_item_type = get_post_meta($sub_item_id, 'sub_item_type', true);
            $sub_item_default = get_post_meta($sub_item_id, 'sub_item_default', true);
            $default_value = get_post_meta($sub_item_id, 'sub_item_default', true);
            if ($sub_item_type=='heading') {
                ?>
                <b><?php echo $sub_item_code.' '.$sub_item_title?></b><br>
                <?php
            } elseif ($sub_item_type=='checkbox') {
                $is_checked = ($default_value==1) ? 'checked' : '';
                ?>
                <input type="checkbox" id="<?php echo esc_attr($field_name.$sub_item_id);?>" <?php echo $is_checked;?> /> <?php echo $sub_item_code.' '.$sub_item_title?><br>
                <?php
            } elseif ($sub_item_type=='textarea') {
                ?>
                <label for="<?php echo esc_attr($field_name.$sub_item_id);?>"><?php echo esc_html($sub_item_code.' '.$sub_item_title);?></label>
                <textarea id="<?php echo esc_attr($field_name.$sub_item_id);?>" rows="3" style="width:100%;"><?php echo esc_html($default_value);?></textarea>
                <?php
            } elseif ($sub_item_type=='text') {
                ?>
                <label for="<?php echo esc_attr($field_name.$sub_item_id);?>"><?php echo esc_html($sub_item_code.' '.$sub_item_title);?></label>
                <input type="text" id="<?php echo esc_attr($field_name.$sub_item_id);?>" value="<?php echo esc_html($default_value);?>"  class="text ui-widget-content ui-corner-all" />
                <?php
            } elseif ($sub_item_type=='radio') {
                $is_checked = ($default_value==1) ? 'checked' : '';
                ?>
                <input type="radio" id="<?php echo esc_attr($field_name.$sub_item_id);?>" name="<?php echo esc_attr(substr($field_name, 0, 5));?>" <?php echo $is_checked;?> /> <?php echo $sub_item_code.' '.$sub_item_title?><br>
                <?php
            } else {
                ?>
                <?php echo $sub_item_code.' '.$sub_item_title?><br>
                <?php
            }

        }

        function get_doc_field_id_by_meta($doc_id, $field_type) {
            // Set up the query arguments
            $args = array(
                'post_type'  => 'doc-field',
                'meta_query' => array(
                    'relation' => 'AND', // Both conditions must be true
                    array(
                        'key'     => 'doc_id',
                        'value'   => $doc_id,
                        'compare' => '='
                    ),
                    array(
                        'key'     => 'field_type',
                        'value'   => $field_type,
                        'compare' => '='
                    )
                ),
                'fields' => 'ids', // Only return the post ID
                'posts_per_page' => 1 // Limit to one result
            );
        
            // Perform the query
            $query = new WP_Query($args);
        
            // Check if a post was found and return the ID
            if ($query->have_posts()) {
                return $query->posts[0]; // Return the first (and only) result
            } else {
                return false; // No post found
            }
        }

        function get_sub_items_from_selection() {
            ob_start();
            $response = array();
            $report_id = sanitize_text_field($_POST['_report_id']);
            $doc_id = get_post_meta($report_id, 'doc_id', true);
            $field_id = $this->get_doc_field_id_by_meta($doc_id, '_sub_item');

            $sub_item_id = sanitize_text_field($_POST['_sub_item_id']);
            if ($sub_item_id) {
                $this->get_sub_item_contains($sub_item_id, $field_id);
            }

            $category_id = sanitize_text_field($_POST['_category_id']);
            if ($category_id) {
                $query = $this->retrieve_sub_item_list_data($category_id);
                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post();
                        $this->get_sub_item_contains(get_the_ID(), $field_id);
                    endwhile;
                    wp_reset_postdata();
                endif;
    
            }
            $response['html_contain'] = ob_get_clean();
            wp_send_json($response);
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
            $profiles_class = new display_profiles();
            //$is_site_admin = $profiles_class->is_site_admin();
            if (current_user_can('administrator')) {
                // Check if the user is administrator
                ob_start();
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
                return ob_get_clean();
            } else {
                display_no_permission_page();
            }
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
                    'post_content' => $_POST['_category_content'],
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
/*
        // audit-item
        function register_audit_item_post_type() {
            $labels = array(
                'menu_name'     => _x('Audit', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
            );
            register_post_type( 'audit-item', $args );
        }

        function display_audit_item_list($paged=1, $category_id=false) {
            $profiles_class = new display_profiles();
            //$is_site_admin = $profiles_class->is_site_admin();
            //if (current_user_can('administrator')) $is_site_admin = true;
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
                
                $paged = max(1, get_query_var('paged')); // Get the current page number
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
            <?php if (current_user_can('administrator')) {?>
                <div id="new-audit-item" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
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
            ob_start();
            $category_id = get_post_meta($audit_id, 'category_id', true);
            $clause_no = get_post_meta($audit_id, 'clause_no', true);
            $audit_item_title = get_the_title($audit_id);
            $field_type = get_post_meta($audit_id, 'field_type', true);
            $audit_content = get_post_field('post_content', $audit_id);
            $display_on_report_only = get_post_meta($audit_id, 'display_on_report_only', true);
            $is_radio_option = get_post_meta($audit_id, 'is_radio_option', true);
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
                    'post_content' => $_POST['_audit_content'],
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
*/
    }
    $items_class = new sub_items();
}


