<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (!class_exists('display_documents')) {
    class display_documents {
        // Class constructor
        public function __construct() {
            add_shortcode( 'display-documents', array( $this, 'display_documents'  ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_display_document_scripts' ) );
            add_action( 'wp_enqueue_scripts', array( $this,'add_mermaid_script' ) );
            //add_action( 'init', array( $this, 'register_document_post_type' ) );
            //add_action( 'init', array( $this, 'register_doc_report_post_type' ) );
            //add_action( 'init', array( $this, 'register_doc_field_post_type' ) );

            add_action( 'wp_ajax_set_document_dialog_data', array( $this, 'set_document_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_document_dialog_data', array( $this, 'set_document_dialog_data' ) );
            add_action( 'wp_ajax_del_document_dialog_data', array( $this, 'del_document_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_document_dialog_data', array( $this, 'del_document_dialog_data' ) );

            add_action( 'wp_ajax_get_doc_content_data', array( $this, 'get_doc_content_data' ) );
            add_action( 'wp_ajax_nopriv_get_doc_content_data', array( $this, 'get_doc_content_data' ) );

            add_action( 'wp_ajax_get_doc_report_dialog_data', array( $this, 'get_doc_report_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_doc_report_dialog_data', array( $this, 'get_doc_report_dialog_data' ) );
            add_action( 'wp_ajax_set_doc_report_dialog_data', array( $this, 'set_doc_report_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_doc_report_dialog_data', array( $this, 'set_doc_report_dialog_data' ) );
            add_action( 'wp_ajax_del_doc_report_dialog_data', array( $this, 'del_doc_report_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_doc_report_dialog_data', array( $this, 'del_doc_report_dialog_data' ) );

            add_action( 'wp_ajax_duplicate_doc_report_data', array( $this, 'duplicate_doc_report_data' ) );
            add_action( 'wp_ajax_nopriv_duplicate_doc_report_data', array( $this, 'duplicate_doc_report_data' ) );

            add_action( 'wp_ajax_get_doc_field_dialog_data', array( $this, 'get_doc_field_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_get_doc_field_dialog_data', array( $this, 'get_doc_field_dialog_data' ) );
            add_action( 'wp_ajax_set_doc_field_dialog_data', array( $this, 'set_doc_field_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_set_doc_field_dialog_data', array( $this, 'set_doc_field_dialog_data' ) );
            add_action( 'wp_ajax_del_doc_field_dialog_data', array( $this, 'del_doc_field_dialog_data' ) );
            add_action( 'wp_ajax_nopriv_del_doc_field_dialog_data', array( $this, 'del_doc_field_dialog_data' ) );

            add_action( 'wp_ajax_sort_doc_field_list_data', array( $this, 'sort_doc_field_list_data' ) );
            add_action( 'wp_ajax_nopriv_sort_doc_field_list_data', array( $this, 'sort_doc_field_list_data' ) );

            add_action( 'wp_ajax_reset_doc_report_todo_status', array( $this, 'reset_doc_report_todo_status' ) );
            add_action( 'wp_ajax_nopriv_reset_doc_report_todo_status', array( $this, 'reset_doc_report_todo_status' ) );                                                                    

            add_action( 'wp_ajax_set_iso_start_ai_data', array( $this, 'set_iso_start_ai_data' ) );
            add_action( 'wp_ajax_nopriv_set_iso_start_ai_data', array( $this, 'set_iso_start_ai_data' ) );
        }

        function enqueue_display_document_scripts() {
            wp_enqueue_style('jquery-ui-style', 'https://code.jquery.com/ui/1.13.2/themes/smoothness/jquery-ui.css', '', '1.13.2');
            wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), null, true);        

            wp_enqueue_script('display-documents', plugins_url('js/display-documents.js', __FILE__), array('jquery'), time());
            wp_localize_script('display-documents', 'ajax_object', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('display-documents-nonce'), // Generate nonce
            ));                
        }

        function add_mermaid_script() {
            // Add Mermaid script
            wp_enqueue_script('mermaid', 'https://cdn.jsdelivr.net/npm/mermaid@11/dist/mermaid.min.js', array(), null, true);
        }

        // Shortcode to display
        function display_documents() {
            // Check if the user is logged in
            if (!is_user_logged_in()) user_is_not_logged_in();
            elseif (is_site_not_configured()) get_NDA_assignment();
            else {
                // Display document list if no specific parameters are existed
                if (
                    (!isset($_GET['_category']) || $_GET['_category'] != 'embedded') &&
                    !isset($_GET['_doc_id']) &&
                    !isset($_GET['_duplicate_document']) &&
                    !isset($_GET['_start_ai'])
                ) {
                    echo $this->display_document_list();
                }

                $items_class = new embedded_items();
                if (isset($_GET['_category']) && $_GET['_category'] === 'embedded') {
                    if (isset($_GET['_embedded_id'])) echo $items_class->display_embedded_dialog($_GET['_embedded_id']);
                    else echo $items_class->display_embedded_list();
                }
                
                // Display ISO statement
                if (isset($_GET['_start_ai'])) {
                    $iso_category_id = sanitize_text_field($_GET['_start_ai']);
                    $paged = 1;
                    if (isset($_GET['_paged'])) {
                        $paged = sanitize_text_field($_GET['_paged']);
                    }
                    echo $this->display_iso_start_ai_content($iso_category_id, $paged);
                }

                // Display document dialog if doc_id is existed
                if (isset($_GET['_doc_id'])) {
                    $doc_id = sanitize_text_field($_GET['_doc_id']);
                    $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
                    if (isset($_GET['_report_id'])) {
                        $report_id = sanitize_text_field($_GET['_report_id']);
                        echo $this->display_doc_report_dialog($report_id);
                    } else {
                        if (is_site_admin() && (!isset($_GET['_is_doc_report']) || $_GET['_is_doc_report'] != 1)) {
                            echo $this->display_document_dialog($doc_id);
                        } else {
                            if ($is_doc_report == 1 || (isset($_GET['_is_doc_report']) && $_GET['_is_doc_report'] == 1)) {
                                echo $this->display_doc_report_list(array('doc_id' => $doc_id));
                            } else {
                                echo $this->display_doc_content($doc_id);
                            }
                        }
                    }
                }

                // Get shared document if shared doc ID is existed
                if (isset($_GET['_duplicate_document'])) {
                    $doc_id = sanitize_text_field($_GET['_duplicate_document']);
                    $this->generate_draft_document_data($doc_id);
                }
            }
        }

        // document
        function register_document_post_type() {
            $labels = array(
                'menu_name'     => _x('Documents', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
            );
            register_post_type( 'document', $args );
        }

        function display_document_list($embedded=false) {
            if (isset($_GET['_is_admin'])) {
                echo '<input type="hidden" id="is-admin" value="1" />';
            }
            $doc_category = isset($_GET['_category']) ? sanitize_text_field($_GET['_category']) : 0;
            $items_class = new embedded_items();
            ?>
            <div class="ui-widget" id="result-container">
                <?php echo display_iso_helper_logo();?>
                <h2 style="display:inline;"><?php echo __( 'Documents', 'textdomain' );?></h2>

                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                        <select id="select-category"><?php echo $items_class->select_doc_category_options($doc_category);?></select>
                    </div>
                    <div style="text-align:right; display:flex;">
                        <input type="text" id="search-document" style="display:inline" placeholder="<?php echo __( 'Search...', 'textdomain' );?>" />
                        <span id="document-setting-button" style="margin-left:5px;" class="dashicons dashicons-admin-generic button"></span>
                    </div>
                </div>

                <fieldset>
                <table class="ui-widget" style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo __( 'No.', 'textdomain' );?></th>
                            <th><?php echo __( 'Title', 'textdomain' );?></th>
                            <th><?php echo __( 'Rev.', 'textdomain' );?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $paged = max(1, get_query_var('paged')); // Get the current page number
                    $query = $this->retrieve_document_data($paged);
                    $total_posts = $query->found_posts;
                    $total_pages = ceil($total_posts / get_option('operation_row_counts'));

                    if ($query->have_posts()) :
                        while ($query->have_posts()) : $query->the_post();
                            $doc_id = get_the_ID();
                            $doc_number = get_post_meta($doc_id, 'doc_number', true);
                            $doc_title = get_the_title();
                            $doc_revision = get_post_meta($doc_id, 'doc_revision', true);
                            $doc_category = get_post_meta($doc_id, 'doc_category', true);
                            $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
                            $is_embedded_doc = get_post_meta($doc_id, 'is_embedded_doc', true);

                            if (!$doc_category) {
                                $doc_number = '<span style="color:red;">' . $doc_number . '</span>';
                            }

                            if ($is_doc_report == 1) {
                                $doc_title = '<span style="color:blue;">*' . $doc_title . '</span>';
                            }

                            if ($is_embedded_doc) {
                                $doc_title = '<span style="color:blue;">*' . $doc_title . '</span>';
                            }

                            ?>
                            <tr id="edit-document-<?php the_ID();?>">
                                <td style="text-align:center;"><?php echo $doc_number;?></td>
                                <td><?php echo $doc_title;?></td>
                                <td style="text-align:center;"><?php echo esc_html($doc_revision);?></td>
                            </tr>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                    endif;
                    ?>
                    </tbody>
                </table>
                <?php if (is_site_admin()) {?>
                    <div id="new-document" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
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
        }
        
        function retrieve_document_data($paged=1, $is_doc_report=2) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $args = array(
                'post_type'      => 'document',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
                'meta_query'     => array(
                    'relation' => 'AND',
                ),
                'orderby'        => 'meta_value',
                'meta_key'       => 'doc_number',
                'order'          => 'ASC',
            );

            if ($paged == 0) {
                $args['posts_per_page'] = -1; // Retrieve all posts if $paged is 0
            }

            if ($is_doc_report == 0) {
                $args['meta_query'][] = array(
                    'relation' => 'OR',
                    array(
                        'key'     => 'is_doc_report',
                        'compare' => 'NOT EXISTS',
                    ),
                    array(
                        'key'     => 'is_doc_report',
                        'value'   => 0,
                    )
                );
            }

            if ($is_doc_report == 1) {
                $args['meta_query'][] = array(
                    'key'     => 'is_doc_report',
                    'value'   => 1,
                );
            }

            if ($is_doc_report == 'embedded') {
                $args['meta_query'][] = array(
                    'key'     => 'is_embedded_doc',
                    'value'   => 1,
                );
                if (!current_user_can('administrator')) {
                    $args['meta_query'][] = array(
                        'relation' => 'OR', // Sub-condition for is_public
                        array(
                            'key'     => 'is_public',
                            'value'   => '1',
                        ),
                        array(
                            'relation' => 'AND',
                            array(
                                'key'     => 'is_public',
                                'compare' => 'NOT EXISTS', // Condition to check if the meta key does not exist
                            ),
                            array(
                                'key'     => 'site_id',
                                'value'   => $site_id,
                            ),
                        ),
                        array(
                            'relation' => 'AND',
                            array(
                                'key'     => 'is_public',
                                'value'   => '0',
                            ),
                            array(
                                'key'     => 'site_id',
                                'value'   => $site_id,
                            ),
                        ),
                    );
                }
    
            } else {
                $args['meta_query'][] = array(
                    'key'     => 'site_id',
                    'value'   => $site_id,
                );

                $select_category = (isset($_GET['_category'])) ? sanitize_text_field($_GET['_category']) : 0;
                if ($select_category) {
                    $args['meta_query'][] = array(
                        'key'     => 'doc_category',
                        'value'   => $select_category,
                    );
                }    
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
                $meta_keys = get_post_type_meta_keys('document');
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
        
        function get_previous_doc_id($current_doc_id) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            
            // Get the current document's `doc_number`
            $current_doc_number = get_post_meta($current_doc_id, 'doc_number', true);
        
            if (!$current_doc_number) {
                return null; // Return null if the current doc_number is not set
            }
        
            $args = array(
                'post_type'      => 'document',
                'posts_per_page' => 1,
                'meta_key'       => 'doc_number', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value as a string
                'order'          => 'DESC', // Descending order to get the previous document
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'site_id',
                        'value'   => $site_id,
                    ),
                    array(
                        'key'     => 'doc_number',
                        'value'   => $current_doc_number,
                        'compare' => '<', // Find `doc_number` less than the current one
                        'type'    => 'CHAR', // Treat `doc_number` as a string
                    ),
                ),
            );
        
            $query = new WP_Query($args);
        
            // Return the previous document ID or null if no previous document is found
            return $query->have_posts() ? $query->posts[0]->ID : null;
        }

        function get_next_doc_id($current_doc_id) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            
            // Get the current document's `doc_number`
            $current_doc_number = get_post_meta($current_doc_id, 'doc_number', true);
        
            if (!$current_doc_number) {
                return null; // Return null if the current doc_number is not set
            }
        
            $args = array(
                'post_type'      => 'document',
                'posts_per_page' => 1,
                'meta_key'       => 'doc_number', // Meta key for sorting
                'orderby'        => 'meta_value', // Sort by meta value as a string
                'order'          => 'ASC', // Ascending order to get the next document
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'site_id',
                        'value'   => $site_id,
                    ),
                    array(
                        'key'     => 'doc_number',
                        'value'   => $current_doc_number,
                        'compare' => '>', // Find `doc_number` greater than the current one
                        'type'    => 'CHAR', // Treat `doc_number` as a string
                    ),
                ),
            );
        
            $query = new WP_Query($args);
        
            // Return the next document ID or null if no next document is found
            return $query->have_posts() ? $query->posts[0]->ID : null;
        }

        function display_document_dialog($doc_id=false) {
            ob_start();
            $todo_class = new to_do_list();
            $items_class = new embedded_items();
            $profiles_class = new display_profiles();

            $prev_doc_id = $this->get_previous_doc_id($doc_id); // Fetch the previous ID
            $next_doc_id = $this->get_next_doc_id($doc_id);     // Fetch the next ID

            $doc_title = get_the_title($doc_id);
            $doc_content = get_post_field('post_content', $doc_id);
            $doc_number = get_post_meta($doc_id, 'doc_number', true);
            $doc_revision = get_post_meta($doc_id, 'doc_revision', true);
            $doc_category = get_post_meta($doc_id, 'doc_category', true);
            $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
            $is_report_display = ($is_doc_report==1) ? '' : 'display:none;';
            $is_content_display = ($is_doc_report==1) ? 'display:none;' : '';
            //$department_id = get_post_meta($doc_id, 'department_id', true);
            $api_endpoint = get_post_meta($doc_id, 'api_endpoint', true);
            $is_embedded_doc = get_post_meta($doc_id, 'is_embedded_doc', true);
            $is_embedded_doc_checked = ($is_embedded_doc==1) ? 'checked' : '';
            $is_public = get_post_meta($doc_id, 'is_public', true);
            $is_public_checked = ($is_public==1) ? 'checked' : '';
            $todo_list_only = get_post_meta($doc_id, 'todo_list_only', true);
            $is_todo_list_only = ($todo_list_only==1) ? 'checked' : '';

            $content = (isset($_GET['_prompt'])) ? generate_content($doc_title.' '.$_GET['_prompt']) : '';

            ?>
            <div class="ui-widget" id="result-container">
            <div>
                <?php echo display_iso_helper_logo();?>
                <h2 style="display:inline;"><?php echo esc_html($doc_title);?></h2>
                <input type="hidden" id="doc-id" value="<?php echo esc_attr($doc_id);?>" />
                <input type="hidden" id="prev-doc-id" value="<?php echo esc_attr($prev_doc_id); ?>" />
                <input type="hidden" id="next-doc-id" value="<?php echo esc_attr($next_doc_id); ?>" />
            </div>

            <fieldset>
                <label for="doc-number"><?php echo __( 'Document Number', 'textdomain' );?></label>
                <input type="text" id="doc-number" value="<?php echo esc_html($doc_number);?>" class="text ui-widget-content ui-corner-all" />
                <label for="doc-title"><?php echo __( 'Document Title', 'textdomain' );?></label>
                <input type="text" id="doc-title" value="<?php echo esc_html($doc_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="doc-revision"><?php echo __( 'Document Rev.', 'textdomain' );?></label>
                <input type="text" id="doc-revision" value="<?php echo esc_html($doc_revision);?>" class="text ui-widget-content ui-corner-all" />
                <label for="doc-category"><?php echo __( 'Categories', 'textdomain' );?></label><br>
                <select id="doc-category" class="select ui-widget-content ui-corner-all">
                    <?php echo $items_class->select_doc_category_options($doc_category);?>
                </select>

                <input type="hidden" id="is-doc-report" value="<?php echo $is_doc_report;?>" />

                <div id="doc-content-div" style="<?php echo $is_content_display;?>">
                    <label id="doc-content-label" class="button" for="doc-content"><?php echo __( 'Document Content', 'textdomain' );?></label>
                    <?php if (is_site_admin()) {?>
                        <input type="button" id="doc-content-preview" value="<?php echo __( 'Preview', 'textdomain' );?>" style="margin:3px;font-size:small;" />
                    <?php }?>
                    <textarea id="doc-content" class="visual-editor"><?php echo $doc_content;?></textarea>
                </div>

                <div id="doc-report-div" style="<?php echo $is_report_display;?>">
                    <label id="doc-field-label" class="button" for="doc-field"><?php echo __( 'Field Settings', 'textdomain' );?></label>
                    <?php if (is_site_admin()) {?>
                        <input type="button" id="doc-report-preview" value="<?php echo __( 'Preview', 'textdomain' );?>" style="margin:3px;font-size:small;" />
                    <?php }?>
                    <?php echo $this->display_doc_field_list($doc_id);?>
                    <label id="doc-report-job-setting" class="button"><?php echo __( 'Job Settings', 'textdomain' );?></label>
                
                    <div id="mermaid-div">
                        <pre class="mermaid">
                            graph TD 
                            <?php                        
                            $query = $profiles_class->retrieve_site_action_data(0, $doc_id, true);
                            if ($query->have_posts()) :
                                while ($query->have_posts()) : $query->the_post();
                                    $action_id = get_the_ID();
                                    $action_title = get_the_title();
                                    $current_job = get_post_meta($action_id, 'doc_id', true);
                                    $current_job_title = get_the_title($current_job);
                                    $current_job_title = str_replace(' ', '-', $current_job_title);
                                    $next_job = get_post_meta($action_id, 'next_job', true);
                                    $next_job_title = get_the_title($next_job);
                                    $next_job_title = str_replace(' ', '-', $next_job_title);
                                    ?>
                                    <?php echo $current_job_title;?>-->|<?php echo $action_title;?>|<?php echo $next_job_title;?>;
                                    <?php
                                endwhile;
                                wp_reset_postdata();
                            endif;    
                            ?>
                        </pre>
                    </div>

                    <div id="job-setting-div" style="display:none;">
                        <label for="job-content"><?php echo __( '職務內容', 'textdomain' );?></label>
                        <textarea id="job-content" class="visual-editor"><?php echo $doc_content;?></textarea>
                        <label for="action-list"><?php echo __( '動作設定', 'textdomain' );?></label>
                        <div id="site-action-list">
                            <?php echo $profiles_class->display_site_action_list(false, $doc_id);?>
                        </div>
<?php /*                        
                        <label for="department-id"><?php echo __( '部門', 'textdomain' );?></label>
                        <select id="department-id" class="select ui-widget-content ui-corner-all"><?php echo $items_class->select_department_card_options($department_id);?></select>
*/?>
                    </div>

                    <label id="system-doc-label" class="button"><?php echo __( '系統文件設定', 'textdomain' );?></label>
                    <fieldset id="system-doc-div" style="display:none;">
                        <label for="api-endpoint"><?php echo __( 'API endpoint', 'textdomain' );?></label>
                        <input type="text" id="api-endpoint" value="<?php echo esc_html($api_endpoint);?>" class="text ui-widget-content ui-corner-all" />
                        <input type="checkbox" id="is-embedded-doc" <?php echo esc_html($is_embedded_doc_checked);?> />
                        <label for="is-embedded-doc"><?php echo __( 'Embedded Item', 'textdomain' );?></label><br>
                        <input type="checkbox" id="is-public" <?php echo esc_html($is_public_checked);?> />
                        <label for="is-public"><?php echo __( 'Is public', 'textdomain' );?></label><br>
                        <input type="checkbox" id="todo-list-only" <?php echo esc_html($is_todo_list_only);?> />
                        <label for="todo-list-only"><?php echo __( 'Display in Todo-list only', 'textdomain' );?></label>
                    </fieldset>
                </div>

                <?php
                    // transaction data vs card key/value
                    $this->display_transactions_by_key_value_pair(['_document' => $doc_id]);
                ?>

                <br><br>
                <div class="content">
                    <?php echo $content;?>
                    <div style="margin:1em; padding:10px; border:solid; border-radius:1.5rem;">
                        <input type="text" id="ask-gemini" placeholder="<?php echo __( 'Ask Gemini', 'textdomain' );?>" class="text ui-widget-content ui-corner-all" />
                    </div>
                </div>            

                <hr>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                        <?php if (is_site_admin()) {?>
                            <input type="button" id="save-document-button" value="<?php echo __( 'Save', 'textdomain' );?>" style="margin:3px;" />
                            <input type="button" id="del-document-button" value="<?php echo __( 'Delete', 'textdomain' );?>" style="margin:3px;" />
                        <?php }?>
                    </div>
                    <div style="text-align: right">
                        <input type="button" id="exit-document-dialog" value="<?php echo __( 'Exit', 'textdomain' );?>" style="margin:5px;" />
                    </div>
                </div>
            </fieldset>
            </div>
            <?php
            return ob_get_clean();
        }

        function set_document_dialog_data() {
            $response = array();
            if( isset($_POST['_doc_id']) ) {
                // Update the Document data
                $doc_id = (isset($_POST['_doc_id'])) ? sanitize_text_field($_POST['_doc_id']) : 0;
                $doc_title = (isset($_POST['_doc_title'])) ? sanitize_text_field($_POST['_doc_title']) : '';
                $doc_number = (isset($_POST['_doc_number'])) ? sanitize_text_field($_POST['_doc_number']) : '';
                $doc_revision = (isset($_POST['_doc_revision'])) ? sanitize_text_field($_POST['_doc_revision']) : '';
                $doc_category = (isset($_POST['_doc_category'])) ? sanitize_text_field($_POST['_doc_category']) : 0;
                //$department_id = (isset($_POST['_department_id'])) ? sanitize_text_field($_POST['_department_id']) : 0;
                $is_doc_report = (isset($_POST['_is_doc_report'])) ? sanitize_text_field($_POST['_is_doc_report']) : 0;
                $doc_content = ($is_doc_report==1) ? $_POST['_job_content'] : $_POST['_doc_content'];
                $api_endpoint = (isset($_POST['_api_endpoint'])) ? sanitize_text_field($_POST['_api_endpoint']) : '';
                $is_embedded_doc = (isset($_POST['_is_embedded_doc'])) ? sanitize_text_field($_POST['_is_embedded_doc']) : 0;
                $is_public = (isset($_POST['_is_public'])) ? sanitize_text_field($_POST['_is_public']) : 0;
                $todo_list_only = (isset($_POST['_todo_list_only'])) ? sanitize_text_field($_POST['_todo_list_only']) : 0;
                $doc_post_args = array(
                    'ID'           => $doc_id,
                    'post_title'   => $doc_title,
                    'post_content' => $doc_content,
                );
                wp_update_post($doc_post_args);
                update_post_meta($doc_id, 'doc_number', $doc_number);
                update_post_meta($doc_id, 'doc_revision', $doc_revision);
                update_post_meta($doc_id, 'doc_category', $doc_category);
                update_post_meta($doc_id, 'is_doc_report', $is_doc_report);
                //update_post_meta($doc_id, 'department_id', $department_id);
                update_post_meta($doc_id, 'api_endpoint', $api_endpoint);
                update_post_meta($doc_id, 'is_embedded_doc', $is_embedded_doc);
                update_post_meta($doc_id, 'is_public', $is_public);
                update_post_meta($doc_id, 'todo_list_only', $todo_list_only);

                $params = array(
                    'log_message' => sprintf(
                        __( 'Document %s has been updated.', 'textdomain' ),
                        $doc_title
                    ),                    
                    'action_title' => 'Update',
                    'doc_id' => $doc_id,
                );
                $todo_class = new to_do_list();
                $todo_class->set_transaction_log($params);    

            } else {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $new_post = array(
                    'post_type'     => 'document',
                    'post_title'    => __( 'New Document', 'textdomain' ),
                    'post_content'  => __( 'Your post content goes here.', 'textdomain' ),
                    'post_status'   => 'publish',
                    'post_author'   => $current_user_id,
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'site_id', $site_id);
                update_post_meta($post_id, 'doc_number', '-');
                update_post_meta($post_id, 'doc_revision', __( 'draft', 'textdomain' ));
                update_post_meta($post_id, 'is_doc_report', 0);
            }
            wp_send_json($response);
        }
        
        function del_document_dialog_data() {
            $response = array();
            $doc_id = (isset($_POST['_doc_id'])) ? sanitize_text_field($_POST['_doc_id']) : 0;
            $doc_number = get_post_meta($doc_id, 'doc_number', true);
            $params = array(
                'log_message' => sprintf(
                    __( 'Document %s has been deleted.', 'textdomain' ),
                    get_the_title($doc_id)
                ),                
                'action_title' => 'Delete',
                'doc_id' => $doc_id,
            );
            $todo_class = new to_do_list();
            $todo_class->set_transaction_log($params);    

            wp_delete_post($_POST['_doc_id'], true);
            wp_send_json($response);
        }

        // doc-content
        function display_doc_content($doc_id=false) {
            ob_start();
            $doc_title = get_the_title($doc_id);
            $doc_number = get_post_meta($doc_id, 'doc_number', true);
            $doc_revision = get_post_meta($doc_id, 'doc_revision', true);
            $doc_content = get_post_field('post_content', $doc_id);
            ?>
            <div class="ui-widget" id="result-container">
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                    <?php echo display_iso_helper_logo();?>
                    <span><?php echo esc_html($doc_number);?></span>
                    <h2 style="display:inline;"><?php echo esc_html($doc_title);?></h2>
                    <span><?php echo esc_html($doc_revision);?></span>
                </div>
                <div style="text-align:right; display:flex;">
                </div>
            </div>

            <input type="hidden" id="doc-id" value="<?php echo $doc_id;?>" />

            <fieldset>
                <?php echo $doc_content;?>
            </fieldset>

            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                    <input type="button" id="share-document" value="<?php echo __( 'Share', 'textdomain' );?>" style="margin:3px;" />
                </div>
                <div style="text-align:right; display:flex;">
                    <input type="button" id="exit-doc-content" value="<?php echo __( 'Exit', 'textdomain' );?>" style="margin:3px;" />
                </div>
            </div>
            </div>
            <?php
            return ob_get_clean();
        }

        function get_doc_content_data() {
            $response = array();
            if (isset($_POST['_doc_id'])) {
                $doc_id = sanitize_text_field($_POST['_doc_id']);
                $response['html_contain'] = $this->display_doc_content($doc_id);
            }
            wp_send_json($response);
        }

        // doc-report
        function register_doc_report_post_type() {
            $labels = array(
                'menu_name'     => _x('doc-report', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
            );
            register_post_type( 'doc-report', $args );
        }

        function display_doc_report_list($params) {
            ob_start();
            $profiles_class = new display_profiles();
            $doc_id = isset($params['doc_id']) ? $params['doc_id'] : 0;
            $doc_title = get_the_title($doc_id);
            $doc_number = get_post_meta($doc_id, 'doc_number', true);
            $doc_revision = get_post_meta($doc_id, 'doc_revision', true);
            ?>
            <div class="ui-widget" id="result-container">
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                    <?php echo display_iso_helper_logo();?>
                    <span><?php echo esc_html($doc_number);?></span>
                    <h2 style="display:inline;"><?php echo esc_html($doc_title);?></h2>
                    <span><?php echo esc_html($doc_revision);?></span>            
                </div>
                <div style="text-align:right; display:flex;">
                </div>
            </div>
        
            <input type="hidden" id="doc-id" value="<?php echo $doc_id;?>" />
            
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div></div>
                <div style="text-align:right; display:flex;">
                    <input type="text" id="search-doc-report" style="display:inline" placeholder="<?php echo __( 'Search...', 'textdomain' );?>" />
                    <span id="doc-field-setting-button" style="margin-left:5px;" class="dashicons dashicons-admin-generic button"></span>
                </div>
            </div>

            <div id="doc-field-setting-dialog" title="Field setting" style="display:none">
                <fieldset>
                    <label for="doc-field-setting"><?php echo __( 'Field setting', 'textdomain' );?></label>
                    <?php echo $this->display_doc_field_list($doc_id);?>
                </fieldset>
            </div>        

            <fieldset>
                <?php
                $paged = max(1, get_query_var('paged')); // Get the current page number
                $params['paged'] = $paged;
                $query = $this->retrieve_doc_report_data($params);
                $total_posts = $query->found_posts;
                $total_pages = ceil($total_posts / get_option('operation_row_counts'));

                $this->get_doc_report_inner_list($params);
                ?>
                <div class="pagination">
                    <?php
                    // Display pagination links
                    if ($paged > 1) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged - 1)) . '"> < </a></span>';
                    echo '<span class="page-numbers">' . sprintf(__('Page %d of %d', 'textdomain'), $paged, $total_pages) . '</span>';
                    if ($paged < $total_pages) echo '<span class="button"><a href="' . esc_url(get_pagenum_link($paged + 1)) . '"> > </a></span>';
                    ?>
                </div>
            </fieldset>

            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                    <input type="button" id="export-to-excel" value="<?php echo __( 'Export to Excel', 'textdomain' );?>" style="margin:3px;" />
                    <style>
                    /* Hide button on mobile devices */
                    @media screen and (max-width: 768px) {
                        #export-to-excel {
                            display: none;
                        }
                    }
                    </style>
                </div>
                <div style="text-align:right; display:flex;">
                    <input type="button" id="exit-doc-report-list" value="<?php echo __( 'Exit', 'textdomain' );?>" style="margin:3px;" />
                </div>
            </div>
            </div>
            <?php
            return ob_get_clean();
        }

        function get_field_contain_list_display($params=array()) {
            $embedded_doc_id = isset($params['embedded_doc_id']) ? $params['embedded_doc_id'] : 0;
            $report_id = isset($params['report_id']) ? $params['report_id'] : 0;
            if ($embedded_doc_id) {
                $params['doc_id'] = $embedded_doc_id;
            }
            $inner_query = $this->retrieve_doc_field_data($params);
            if ($inner_query->have_posts()) {
                while ($inner_query->have_posts()) : $inner_query->the_post();
                    $field_id = get_the_ID();
                    $field_type = get_post_meta($field_id, 'field_type', true);
                    $listing_style = get_post_meta($field_id, 'listing_style', true);
                    $field_value = get_post_meta($report_id, $field_id, true);
                    $is_checked = ($field_value==1) ? 'checked' : '';
                    echo '<td style="text-align:'.$listing_style.';">';
                    if ($field_type=='checkbox') {
                        if ($field_value==1) echo 'V';
                    } elseif ($field_type=='radio') {
                        if ($field_value==1) echo 'V';
                    } elseif ($field_type=='_employee'||$field_type=='_employees') {
                        if (is_array($field_value)) {
                            $user_names = array(); // Array to hold user display names
                            // Loop through each selected user ID
                            foreach ($field_value as $user_id) {
                                // Get user data
                                $user = get_userdata($user_id);
                                // Check if the user data is retrieved successfully
                                if ($user) {
                                    // Add the user's display name to the array
                                    $user_names[] = esc_html($user->display_name);
                                } else {
                                    // Optionally handle the case where user data is not found
                                    $user_names[] = 'User not found for ID: ' . esc_html($user_id);
                                }
                            }
                            // Display the user names as a comma-separated list
                            echo implode(', ', $user_names);
                        } else {
                            // Handle the case where $field_value is not an array
                            // Get user data
                            $user = get_userdata($field_value);
                            // Check if the user data is retrieved successfully
                            if ($user) {
                                // Add the user's display name to the array
                                echo esc_html($user->display_name);
                            } else {
                                // Optionally handle the case where user data is not found
                                echo 'User not found for ID: ' . esc_html($field_value);
                            }
                        }
                    } elseif ($field_type=='_select') {
                        echo esc_html(get_the_title($field_value));
                    } elseif ($field_type=='_document') {
                        $doc_title = get_the_title($field_value);
                        $doc_number = get_post_meta($field_value, 'doc_number', true);
                        $doc_revision = get_post_meta($field_value, 'doc_revision', true);
                        echo esc_html($doc_number.'-'.$doc_title.'-'.$doc_revision);
                    } elseif ($field_type=='_department') {
                        echo esc_html(get_the_title($field_value));
                    } elseif ($field_type=='_iot_device') {
                        $iot_device = get_post_meta($field_value, 'iot_device', true);
                        echo esc_html(get_the_title($field_value));
                    } else {
                        echo esc_html($field_value);
                    }
                    echo '</td>';
                endwhile;                
                wp_reset_postdata();
            }
        }

        function get_doc_report_inner_list($params) {
            $params['is_listing'] = true;
            ?>
            <table style="width:100%;">
                <thead>
                    <?php
                    $query = $this->retrieve_doc_field_data($params);
                    if ($query->have_posts()) {
                        echo '<tr>';
                        while ($query->have_posts()) : $query->the_post();
                            $field_title = get_the_title();
                            echo '<th>'.esc_html($field_title).'</th>';
                        endwhile;
                        //if (current_user_can('administrator')) {
                        //    echo '<th>'. __( 'Todo', 'textdomain' ).'</th>';
                        //}
                        echo '</tr>';
                        wp_reset_postdata();
                    }
                    ?>
                </thead>
                <tbody>
                    <?php
                    $query = $this->retrieve_doc_report_data($params);
                    if ($query->have_posts()) {
                        while ($query->have_posts()) : $query->the_post();
                            $report_id = get_the_ID();
                            echo '<tr id="edit-doc-report-'.$report_id.'">';

                            $params['report_id'] = $report_id;
                            $this->get_field_contain_list_display($params);

                            //if (current_user_can('administrator')) {
                                $next_job = get_post_meta($report_id, 'todo_status', true);
                                $todo_status = ($next_job) ? get_the_title($next_job) : 'Draft';
                            //    echo '<td style="text-align:center;">'.esc_html($todo_status).'</td>';
                            //}
                            echo '</tr>';
                        endwhile;                
                        wp_reset_postdata();
                    }
                    ?>
                </tbody>
            </table>
            <?php
        }

        function retrieve_doc_report_data($params) {
            // Construct the meta query array
            $meta_query = array(
                'relation' => 'AND',
                array(
                    'relation' => 'OR',
                ),
                array(
                    'relation' => 'OR',
                ),
            );
    
            if (!empty($params['doc_id'])) {
                $doc_id = $params['doc_id'];
                $meta_query[1][] = array(
                    'key'   => 'doc_id',
                    'value' => $doc_id,
                );
            }

            if (!empty($params['key_value_pair'])) {
                $meta_query[1][] = array(
                    'relation' => 'OR', // Either condition can be true
                    array(
                        'key'     => 'todo_status',
                        'compare' => 'NOT EXISTS', // Condition for when the meta doesn't exist
                    ),
                    array(
                        'key'     => 'todo_status',
                        'value'   => '0',
                        'compare' => '!=', // Condition for when todo_status is not equal to 0
                    ),
                );
            }

            if (!empty($params['paged'])) {
                $paged = $params['paged'];
            } else {
                $paged = 1;
            }

            $args = array(
                //'post_type'      => 'doc-report',
                'post_type'      => 'todo',
                'posts_per_page' => get_option('operation_row_counts'),
                'paged'          => $paged,
                'meta_query'     => $meta_query,
                'orderby' => 'date',
                'order' => 'DESC',
            );

            if (!empty($params['todo_in_summary'])) {
                $todo_in_summary = $params['todo_in_summary'];
                $report_ids = array();
                foreach ($todo_in_summary as $todo_id) {
                    $report_id = get_post_meta($todo_id, 'prev_report_id', true);
                    $report_ids[] = $report_id;
                }
                $args['post__in'] = $report_ids;
            }

            $inner_query = $this->retrieve_doc_field_data(array('doc_id' => $doc_id));
            if ($inner_query->have_posts()) {
                while ($inner_query->have_posts()) : $inner_query->the_post();
                    $field_id = get_the_ID();
                    $field_type = get_post_meta($field_id, 'field_type', true);

                    if (!empty($params['key_value_pair'])) {
                        $key_value_pair = $params['key_value_pair'];
                        foreach ($key_value_pair as $key => $value) {
                            if ($key==$field_type) {
                                if ($field_type=='_employees') {
                                    if (is_array($value)) {
                                        foreach ($value as $val) {
                                            $args['meta_query'][0][] = array(
                                                'key'     => $field_id,
                                                'value'   => sprintf(':"%s";', (string)$val),
                                                'compare' => 'LIKE', // Use 'LIKE' to match any part of the serialized array
                                            );
                                        }
                                    } else {
                                        // If $value is not an array, treat it as a single value
                                        $args['meta_query'][0][] = array(
                                            'key'     => $field_id,
                                            'value'   => sprintf(':"%s";', (string)$value),
                                            'compare' => 'LIKE', // Use 'LIKE' to match any part of the serialized array
                                        );
                                    }
                                } else {
                                    $args['meta_query'][0][] = array(
                                        'key'   => $field_id,
                                        'value' => (string)$value,
                                    );
                                }
                            }
                        }    
                    }

                    if (isset($_GET['_search'])) {
                        $search_doc_report = sanitize_text_field($_GET['_search']);
                        $args['meta_query'][1][] = array( // Append to the OR relation
                            'key'     => $field_id,
                            'value'   => $search_doc_report,
                            'compare' => 'LIKE',
                        );
                    }
                endwhile;
                // Reset only the inner loop's data
                wp_reset_postdata();
            }
            $query = new WP_Query($args);
            return $query;
        }

        function get_previous_report_id($current_report_id) {
            $doc_id = get_post_meta($current_report_id, 'doc_id', true);
        
            if (!$doc_id) {
                return null; // Return null if the 'doc_id' meta is not set
            }
        
            $args = array(
                //'post_type'      => 'doc-report',
                'post_type'      => 'todo',
                'posts_per_page' => 1,
                'orderby'        => 'date', // Sort by post date
                'order'          => 'ASC', // Find the earliest report after the current one
                'meta_query'     => array(
                    array(
                        'key'   => 'doc_id',
                        'value' => $doc_id,
                    ),
                ),
                'date_query'     => array(
                    array(
                        'after' => get_post_field('post_date', $current_report_id), // Get posts after the current report's date
                        'inclusive' => false,
                    ),
                ),
            );
        
            $query = new WP_Query($args);
        
            // Return the previous report ID or null if no match is found
            return $query->have_posts() ? $query->posts[0]->ID : null;
        }

        function get_next_report_id($current_report_id) {
            $doc_id = get_post_meta($current_report_id, 'doc_id', true);
        
            if (!$doc_id) {
                return null; // Return null if the 'doc_id' meta is not set
            }
        
            $args = array(
                //'post_type'      => 'doc-report',
                'post_type'      => 'todo',
                'posts_per_page' => 1,
                'orderby'        => 'date', // Sort by post date
                'order'          => 'DESC', // Find the latest report before the current one
                'meta_query'     => array(
                    array(
                        'key'   => 'doc_id',
                        'value' => $doc_id,
                    ),
                ),
                'date_query'     => array(
                    array(
                        'before' => get_post_field('post_date', $current_report_id), // Get posts before the current report's date
                        'inclusive' => false,
                    ),
                ),
            );
        
            $query = new WP_Query($args);
        
            // Return the next report ID or null if no match is found
            return $query->have_posts() ? $query->posts[0]->ID : null;
        }

        function display_doc_report_dialog($report_id=false) {
            ob_start();
            $prev_report_id = $this->get_previous_report_id($report_id); // Fetch the previous ID
            $next_report_id = $this->get_next_report_id($report_id);     // Fetch the next ID
            $doc_id = get_post_meta($report_id, 'doc_id', true);
            $doc_title = get_the_title($doc_id);
            $doc_number = get_post_meta($doc_id, 'doc_number', true);
            $todo_status = get_post_meta($report_id, 'todo_status', true);
            ?>
            <div class="ui-widget" id="result-container">
            <div style="display:flex; justify-content:space-between; margin:5px;">
                <div>
                    <?php echo display_iso_helper_logo();?>
                    <h2 style="display:inline;"><?php echo esc_html($doc_title.'('.$doc_number.')');?></h2>
                </div>
                <div style="text-align:right; display:flex;">
                    <span id='reset-doc-report-<?php echo esc_attr($report_id);?>' style='margin-left:5px;' class='dashicons dashicons-trash button'></span>
                </div>
            </div>

            <input type="hidden" id="prev-report-id" value="<?php echo esc_attr($prev_report_id); ?>" />
            <input type="hidden" id="next-report-id" value="<?php echo esc_attr($next_report_id); ?>" />
            <input type="hidden" id="report-id" value="<?php echo esc_attr($report_id);?>" />
            <input type="hidden" id="doc-id" value="<?php echo esc_attr($doc_id);?>" />
            <fieldset>
                <?php
                $params = array(
                    'doc_id'    => $doc_id,
                    'report_id' => $report_id,
                );                
                $this->get_doc_field_contains($params);

                $content = (isset($_GET['_prompt'])) ? generate_content($doc_title.' '.$_GET['_prompt']) : '';
                ?>
                <br><br>
                <div class="content">
                    <?php echo $content;?>
                    <div style="margin:1em; padding:10px; border:solid; border-radius:1.5rem;">
                        <input type="text" id="ask-gemini" placeholder="<?php echo __( 'Ask Gemini', 'textdomain' );?>" class="text ui-widget-content ui-corner-all" />
                    </div>
                </div>            
                
                <hr>
                <?php
                // Action buttons
                if (empty($todo_status)){
                    ?>
                    <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                    <?php
                    $profiles_class = new display_profiles();
                    $query = $profiles_class->retrieve_site_action_data(0, $doc_id);
                    if ($query->have_posts()) {
                        while ($query->have_posts()) : $query->the_post();
                            $action_id = get_the_ID();
                            if ($profiles_class->is_user_action($action_id)) {
                                $action_title = get_the_title();
                                echo '<input type="button" id="doc-report-dialog-button-'.$action_id.'" value="'.$action_title.'" style="margin:5px;" />';
                            }
                        endwhile;
                        wp_reset_postdata();
                    }
                    ?>
                    </div>
                    <div style="text-align:right; display:flex;">
                    <?php if (is_site_admin()) {?>
<?php /*                        
                        <input type="button" id="save-doc-report-<?php echo $report_id;?>" value="<?php echo __( 'Save', 'textdomain' );?>" style="margin:3px;" />
*/?>                        
                        <input type="button" id="del-doc-report-<?php echo $report_id;?>" value="<?php echo __( 'Delete', 'textdomain' );?>" style="margin:3px;" />
                    <?php }?>
                        <input type="button" id="exit-doc-report-dialog" value="<?php echo __( 'Exit', 'textdomain' );?>" style="margin:3px;" />
                    </div>
                    </div>
                    <?php
                } else {
                    ?>
                    <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                        <input type="button" id="action-log-button" value="<?php echo __('Sign-off Record', 'textdomain')?>" style="margin:3px;" />
<?php /*                        
                        <input type="button" id="duplicate-doc-report-<?php echo $report_id;?>" value="<?php echo __( 'Duplicate', 'textdomain' );?>" style="margin:3px;" />
*/?>
                    </div>
                    <div style="text-align:right;">
                        <input type="button" id="exit-doc-report-dialog" value="<?php echo __( 'Exit', 'textdomain' );?>" style="margin:5px;" />
                    </div>
                    </div>
                    <?php
                }
                ?>
            </fieldset>

            <?php
            $doc_id = get_post_meta($report_id, 'doc_id', true);
            $is_embedded_doc = get_post_meta($doc_id, 'is_embedded_doc', true);
            if ($is_embedded_doc) {
                // transaction data vs card key/value
                //$this->display_transactions_by_key_value_pair(['_select' => $doc_id,]);
                $this->display_transactions_by_key_value_pair(['_select' => $report_id,]);
            }
            ?>

            <div id="report-action-log-div" style="display:none;">
                <?php $todo_class = new to_do_list();?>
                <?php echo $todo_class->get_transaction_log_inner_list($report_id);?>
            </div>
            
            </div>
            <?php
            return ob_get_clean();
        }
        
        function get_doc_report_dialog_data() {
            $response = array();
            if (isset($_POST['_report_id'])) {
                $report_id = sanitize_text_field($_POST['_report_id']);
                $is_admin = isset($_POST['_is_admin']) ? sanitize_text_field($_POST['_is_admin']) : false;

                // Retrieve meta data for report
                $doc_id = get_post_meta($report_id, 'doc_id', true);
                $todo_status = get_post_meta($report_id, 'todo_status', true);
                $_document = get_post_meta($report_id, '_document', true);

                // Determine content based on document type and status
                if ($_document && $todo_status == -1 && !$is_admin) {
                    $is_doc_report = get_post_meta($_document, 'is_doc_report', true);
                    if ($is_doc_report) {
                        $response['html_contain'] = $this->display_doc_report_list(array('doc_id' => $_document));
                    } else {
                        $response['html_contain'] = $this->display_doc_content($_document);
                    }
                } else {
                    $doc_id = get_post_meta($report_id, 'doc_id', true);
                    $response['doc_field_keys'] = $this->get_doc_field_keys($doc_id);
                    $items_class = new embedded_items();
                    $response['embedded_item_keys'] = $items_class->get_embedded_item_keys($doc_id);
                }
            }
            wp_send_json($response);
        }

        function set_doc_report_dialog_data() {
            $response = array();
            if (isset($_POST['_report_id'])) {
                // Update the existing post
                $report_id = sanitize_text_field($_POST['_report_id']);
                $doc_id = get_post_meta($report_id, 'doc_id', true);
                $is_embedded_doc = get_post_meta($doc_id, 'is_embedded_doc', true);

                $post_data = array(
                    'ID'           => $report_id,
                    'post_title'   => $_POST['_post_title'],
                    'post_content' => $_POST['_post_content'],
                );        
                wp_update_post($post_data);
                update_post_meta($report_id, '_post_number', $_POST['_post_number']);

                if ($is_embedded_doc) {
                    $embedded_doc_title = get_the_title($doc_id);
                    if (stripos($embedded_doc_title, 'customer') !== false || 
                        stripos($embedded_doc_title, '客戶') !== false || 
                        stripos($embedded_doc_title, '顧客') !== false || 
                        stripos($embedded_doc_title, '廠商') !== false || 
                        stripos($embedded_doc_title, '供應商') !== false || 
                        stripos($embedded_doc_title, 'vendor') !== false) {
                        // Code to execute if $system_doc includes 'customer' or 'vendor', case-insensitive
                        $this->update_site_profile($report_id);
                    }
                }

                //$todo_class = new to_do_list();
                $this->update_doc_field_contains(array('report_id' => $report_id));
/*
                $action_id = isset($_POST['_action_id']) ? sanitize_text_field($_POST['_action_id']) : 0;
                $proceed_to_todo = isset($_POST['_proceed_to_todo']) ? sanitize_text_field($_POST['_proceed_to_todo']) : 0;
        
                if ($proceed_to_todo == 1) {
                    $params = array(
                        'action_id' => $action_id,
                        'report_id' => $report_id,
                    );        
                    $todo_class = new to_do_list();
                    $todo_class->set_transaction_log($params);
                }
*/                    
            }
            wp_send_json($response);
        }

        function del_doc_report_dialog_data() {
            $response = array();
            wp_delete_post($_POST['_report_id'], true);
            wp_send_json($response);
        }

        function select_doc_report_options($selected_option=false, $embedded_id=false) {
            $query = $this->retrieve_doc_report_data(array('doc_id' => $embedded_id));
            $options = '<option value="">'.__( 'Select Option', 'textdomain' ).'</option>';
            while ($query->have_posts()) : $query->the_post();
                $report_id = get_the_ID();
                $report_title = get_the_title();
                $selected = ($selected_option == $report_id) ? 'selected' : '';
                $options .= '<option value="' . esc_attr($report_id) . '" '.$selected.' >' . $report_title . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        function duplicate_doc_report_data() {
            $response = array();
            if( isset($_POST['_report_id']) ) {
                // Create the post
                $new_post = array(
                    'post_type'     => 'doc-report',
                    'post_status'   => 'publish',
                    'post_author'   => get_current_user_id(),
                );    
                $post_id = wp_insert_post($new_post);
                $report_id = sanitize_text_field($_POST['_report_id']);
                $doc_id = get_post_meta($report_id, 'doc_id', true);
                update_post_meta($post_id, 'doc_id', $doc_id);

                $query = $this->retrieve_doc_field_data(array('doc_id' => $doc_id));
                if ($query->have_posts()) {
                    $field_id = get_the_ID();
                    while ($query->have_posts()) : $query->the_post();
                        update_post_meta($post_id, $field_id, $_POST[$field_id]);
                    endwhile;
                    wp_reset_postdata();
                }
            }
            wp_send_json($response);
        }

        function update_site_profile($_id=false) {
            if (empty($_id)) {
                return false; // Ensure the ID is provided
            }

            // Get the title of the post
            $_title = get_the_title($_id);
            if (empty($_title)) {
                return false; // Ensure the title is valid
            }

            // Sanitize the input title
            $sanitized_title = sanitize_text_field($_title);
        
            // Query to find a matching post
            $args = array(
                'post_type'      => 'site-profile',
                'posts_per_page' => 1,
                'title'          => $sanitized_title, // Match the current title
            );
        
            $query = new WP_Query($args);
        
            if ($query->have_posts()) {
                // If a matching post exists, update its title
                $query->the_post();
                $post_id = get_the_ID();
                wp_update_post(array(
                    'ID'         => $post_id,
                    'post_title' => $sanitized_title, // Update with the sanitized title
                ));
                wp_reset_postdata();
                return $post_id; // Return the updated post ID
            } else {
                // If no matching post exists, create a new post
                $post_id = wp_insert_post(array(
                    'post_type'   => 'site-profile',
                    'post_title'  => $sanitized_title, // Insert with the sanitized title
                    'post_status' => 'publish', // Set to published
                ));
                return $post_id; // Return the new post ID
            }
        }
        
        function display_transactions_by_key_value_pair($key_value_pair = array()) {
            error_log('display_transactions_by_key_value_pair: '.print_r($key_value_pair, true));
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            if (!empty($key_value_pair)) {
                foreach ($key_value_pair as $key => $value) {
                    $args = array(
                        'post_type'  => 'doc-field',
                        'posts_per_page' => -1, // Retrieve all posts
                        'meta_query' => array(
                            array(
                                'key'   => 'field_type',
                                'value' => $key,
                            )
                        ),
                        'fields' => 'ids' // Only return post IDs
                    );
                    // Execute the query
                    $query = new WP_Query($args);

                    $doc_ids = array();
                    if ($query->have_posts()) {
                        foreach ($query->posts as $field_id) {
                            $doc_id = get_post_meta($field_id, 'doc_id', true);
                            $doc_title = get_the_title($doc_id);
                            $doc_site = get_post_meta($doc_id, 'site_id', true);
                            // Ensure the doc ID is unique
                            if (!isset($doc_ids[$doc_id]) && $doc_site == $site_id) {                                
                                $doc_ids[$doc_id] = $doc_title; // Use doc_id as key to ensure uniqueness
                                $params = array(
                                    'doc_id'         => $doc_id,
                                    'key_value_pair' => $key_value_pair,
                                );
                                $doc_report = $this->retrieve_doc_report_data($params);
                                if ($doc_report->have_posts()) {
                                    echo $doc_title;
                                    echo '<fieldset>';
                                    $this->get_doc_report_inner_list($params);
                                    echo '</fieldset>';    
                                }        
                            }
                        }
                        return $query->posts; // Return the array of post IDs
                    }
                }    
            }
            return array(); // Return an empty array if no posts are found
        }

        // doc-field
        function register_doc_field_post_type() {
            $labels = array(
                'menu_name'     => _x('doc-field', 'admin menu', 'textdomain'),
            );
            $args = array(
                'labels'        => $labels,
                'public'        => true,
            );
            register_post_type( 'doc-field', $args );
        }

        function retrieve_doc_field_data($params = array()) {
            $args = array(
                'post_type'      => 'doc-field',
                'posts_per_page' => -1,
                'meta_key'       => 'sorting_key',
                'orderby'        => 'meta_value_num', // Specify meta value as numeric
                'order'          => 'ASC',
            );

            if (!empty($params['doc_id'])) {
                $args['meta_query'][] = array(
                    'key'   => 'doc_id',
                    'value' => $params['doc_id'],
                );
            }

            if (!empty($params['is_listing'])) {
                $args['meta_query'][] = array(
                    'key'     => 'listing_style',
                    'value'   => '.',
                    'compare' => '!=',
                );
            }

            $query = new WP_Query($args);
            return $query;
        }

        function get_listing_style_data($style = false) {
            $styles = [
                '.' => __('請選擇', 'textdomain'),
                'left' => __('靠左', 'textdomain'),
                'center' => __('置中', 'textdomain'),
                'right' => __('靠右', 'textdomain'),
            ];
        
            if ($style === '.') {
                return ''; // Return an empty string if $key is '.'
            }
        
            if ($style !== false && isset($styles[$style])) {
                return $styles[$style];
            }
        
            return $styles;
        }

        function get_field_type_data($field_type = false) {
            $field_types = [
                'text' => __('Text', 'textdomain'),
                'textarea' => __('Textarea', 'textdomain'),
                'number' => __('Number', 'textdomain'),
                'date' => __('Date', 'textdomain'),
                'time' => __('Time', 'textdomain'),
                'checkbox' => __('Checkbox', 'textdomain'),
                'radio' => __('Radio', 'textdomain'),
                'heading' => __('Heading', 'textdomain'),
                'canvas' => __('Canvas', 'textdomain'),
                '_embedded' => __('Embedded', 'textdomain'),
                '_line_list' => __('Line List', 'textdomain'),
                '_select' => __('Select', 'textdomain'),
                //'_iot_device' => __('IoT devices', 'textdomain'),
                '_document' => __('Document', 'textdomain'),
                '_doc_report' => __('Report', 'textdomain'),
                //'_department' => __('Department', 'textdomain'),
                '_employee' => __('Employee', 'textdomain'),
                'image' => __('Image', 'textdomain'),
                'video' => __('Video', 'textdomain'),
            ];

            // If $field_type is set and exists in $field_types, return the label
            if ($field_type !== false && isset($field_types[$field_type])) {
                return $field_types[$field_type];
            }
        
            // Return the array of all Field Types if no specific $field_type is set
            return $field_types;
        }

        function display_doc_field_list($doc_id=false) {
            ob_start();
            ?>
            <div id="fields-container">
            <fieldset>
                <table style="width:100%;">
                    <thead>
                        <tr>
                            <th><?php echo __( 'Title', 'textdomain' );?></th>
                            <th><?php echo __( 'Type', 'textdomain' );?></th>
                            <th><?php echo __( 'Default', 'textdomain' );?></th>
                            <th><?php echo __( 'Align', 'textdomain' );?></th>
                        </tr>
                    </thead>
                    <tbody id="sortable-doc-field-list">
                        <?php
                        $query = $this->retrieve_doc_field_data(array('doc_id' => $doc_id));
                        if ($query->have_posts()) {
                            while ($query->have_posts()) : $query->the_post();
                                $field_id = get_the_ID();
                                $field_title = get_the_title();
                                $field_type = get_post_meta($field_id, 'field_type', true);
                                $type_name = $this->get_field_type_data($field_type);
                                $default_value = get_post_meta($field_id, 'default_value', true);
                                $embedded_doc = get_post_meta($field_id, 'embedded_doc', true);
                                if ($embedded_doc) $default_value = get_the_title($embedded_doc);
                                $listing_style = get_post_meta($field_id, 'listing_style', true);
                                $style = $this->get_listing_style_data($listing_style);
                                echo '<tr id="edit-doc-field-'.esc_attr($field_id).'" data-field-id="'.esc_attr($field_id).'">';

                                if ($field_type=='heading' && $default_value=='') {
                                    echo '<td style="text-align:center;"><b>'.esc_html($field_title).'</b></td>';
                                } else {
                                    echo '<td style="text-align:center;">'.esc_html($field_title).'</td>';
                                }
                                echo '<td style="text-align:center;">'.esc_html($type_name).'</td>';
                                echo '<td style="text-align:center;">'.esc_html($default_value).'</td>';
                                echo '<td style="text-align:center;">'.esc_html($style).'</td>';

                                echo '</tr>';
                            endwhile;
                            wp_reset_postdata();
                        }
                        ?>
                    </tbody>
                </table>
                <?php if (is_site_admin()) {?>
                    <div id="new-doc-field" class="button" style="border:solid; margin:3px; text-align:center; border-radius:5px; font-size:small;">+</div>
                <?php }?>
            </fieldset>
            </div>
            <div id="doc-field-dialog" title="Field dialog"></div>
            <?php
            return ob_get_clean();
        }

        function display_doc_field_dialog($field_id=false) {
            ob_start();
            $field_title = get_the_title($field_id);
            $field_type = get_post_meta($field_id, 'field_type', true);
            $default_value = get_post_meta($field_id, 'default_value', true);
            $listing_style = get_post_meta($field_id, 'listing_style', true);
            $embedded_doc = get_post_meta($field_id, 'embedded_doc', true);
            ?>
            <fieldset>
                <input type="hidden" id="field-id" value="<?php echo esc_attr($field_id);?>" />
                <input type="hidden" id="is-site-admin" value="<?php echo esc_attr(is_site_admin());?>" />
                <label for="field-title"><?php echo __( 'Field Title', 'textdomain' );?></label>
                <input type="text" id="field-title" value="<?php echo esc_attr($field_title);?>" class="text ui-widget-content ui-corner-all" />
                <label for="field-type"><?php echo __( 'Field Type', 'textdomain' );?></label>
                <?php $types = $this->get_field_type_data();?>
                <select id="field-type" class="select ui-widget-content ui-corner-all">
                <?php foreach ($types as $value => $label): ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php echo ($field_type == $value) ? 'selected' : ''; ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
                </select>
                <div id="embedded-selection" style="display:none">
                    <label for="embedded-doc"><?php echo __( 'Embedded Document', 'textdomain' );?></label>
                    <select id="embedded-doc" class="select ui-widget-content ui-corner-all">
                        <?php echo $this->select_document_list_options($embedded_doc, 'embedded');?>
                    </select>
                </div>
                <label for="default-value"><?php echo __( 'Default', 'textdomain' );?></label>
                <input type="text" id="default-value" value="<?php echo esc_attr($default_value);?>" class="text ui-widget-content ui-corner-all" />
                <?php $styles = $this->get_listing_style_data();?>
                <label for="listing-style"><?php echo __( 'Align', 'textdomain' ); ?></label>
                <select id="listing-style" class="select ui-widget-content ui-corner-all">
                <?php foreach ($styles as $value => $label): ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php echo ($listing_style === $value) ? 'selected' : ''; ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
                </select>
            </fieldset>
            <?php
            return ob_get_clean();
        }

        function get_doc_field_dialog_data() {
            $response = array();
            if( isset($_POST['_field_id']) ) {
                $field_id = sanitize_text_field($_POST['_field_id']);
                $response['html_contain'] = $this->display_doc_field_dialog($field_id);
            }
            wp_send_json($response);
        }

        function set_doc_field_dialog_data() {
            $response = array();
            $doc_id = (isset($_POST['_doc_id'])) ? sanitize_text_field($_POST['_doc_id']) : 0;
            if( isset($_POST['_field_id']) ) {
                // Update the post
                $field_id = sanitize_text_field($_POST['_field_id']);
                $field_title = isset($_POST['_field_title']) ? sanitize_text_field($_POST['_field_title']) : '';
                $field_type = isset($_POST['_field_type']) ? sanitize_text_field($_POST['_field_type']) : '';
                $default_value = isset($_POST['_default_value']) ? sanitize_text_field($_POST['_default_value']) : '';
                $listing_style = isset($_POST['_listing_style']) ? sanitize_text_field($_POST['_listing_style']) : '';
                $embedded_doc = isset($_POST['_embedded_doc']) ? sanitize_text_field($_POST['_embedded_doc']) : '';
                wp_update_post(array(
                    'ID'          => $field_id,
                    'post_title'  => $field_title,
                    'meta_input'  => array(
                        'field_type'    => $field_type,
                        'default_value' => $default_value,
                        'listing_style' => $listing_style,
                        'embedded_doc' => $embedded_doc,
                    )
                ));
            } else {
                // Create the post
                $new_post = array(
                    'post_type'     => 'doc-field',
                    'post_title'    => __( 'New Field', 'textdomain' ),
                    'post_status'   => 'publish',
                    'post_author'   => get_current_user_id(),
                );    
                $post_id = wp_insert_post($new_post);
                update_post_meta($post_id, 'doc_id', $doc_id);
                update_post_meta($post_id, 'field_type', 'text');
                update_post_meta($post_id, 'listing_style', 'center');
                update_post_meta($post_id, 'sorting_key', 999);
            }
            $response['html_contain'] = $this->display_doc_field_list($doc_id);
            wp_send_json($response);
        }

        function del_doc_field_dialog_data() {
            $response = array();
            wp_delete_post($_POST['_field_id'], true);
            $doc_id = sanitize_text_field($_POST['_doc_id']);
            $response['html_contain'] = $this->display_doc_field_list($doc_id);
            wp_send_json($response);
        }

        function sort_doc_field_list_data() {
            $response = array('success' => false, 'error' => 'Invalid data format');
            if (isset($_POST['_field_id_array']) && is_array($_POST['_field_id_array'])) {
                $field_id_array = array_map('absint', $_POST['_field_id_array']);        
                foreach ($field_id_array as $index => $field_id) {
                    update_post_meta($field_id, 'sorting_key', $index);
                }
                $response = array('success' => true);
            }
            wp_send_json($response);
        }

        function get_doc_field_keys($doc_id=false) {
            if ($doc_id) $params = array('doc_id' => $doc_id);
            $query = $this->retrieve_doc_field_data($params);
            $_array = array();
            if ($query->have_posts()) {
                while ($query->have_posts()) : $query->the_post();
                    $field_id = get_the_ID();
                    $field_type = get_post_meta($field_id, 'field_type', true);
                    $default_value = get_post_meta($field_id, 'default_value', true);
                    $_list = array();
                    $_list["field_id"] = $field_id;
                    $_list["field_type"] = $field_type;
                    $_list["default_value"] = $default_value;
                    array_push($_array, $_list);
                endwhile;
                wp_reset_postdata();
            }    
            return $_array;
        }

        function get_doc_field_default_value($field_id=false, $user_id=false) {
            // Ensure $field_id is provided
            if (!$field_id) {
                return false; // Return false or handle the error as needed
            }
            // Get the current user ID
            if (!$user_id) {
                $user_id = get_current_user_id();
            }
            // Get and sanitize the field name and default value
            $default_value = sanitize_text_field(get_post_meta($field_id, 'default_value', true));
            $field_type = sanitize_text_field(get_post_meta($field_id, 'field_type', true));

            if ($field_type=='date' && $default_value === 'today') $default_value = wp_date('Y-m-d', time());
            if ($field_type=='_employee' && $default_value === 'me') $default_value = $user_id;
            if ($field_type=='_employees' && $default_value === 'me') $default_value = array($user_id);
            if ($default_value=='_post_number') $default_value=time();
            if ($default_value=='_post_title') $default_value='';
            if ($default_value=='_post_content') $default_value='';

            return $default_value;
        }

        function get_doc_field_contains($params=array()) {
            $doc_id = isset($params['doc_id']) ? $params['doc_id'] : 0;
            $report_id = isset($params['report_id']) ? $params['report_id'] : 0;
            $prev_report_id = isset($params['prev_report_id']) ? $params['prev_report_id'] : 0;
            $todo_id = isset($params['todo_id']) ? $params['todo_id'] : 0;
            $is_todo = isset($params['is_todo']) ? $params['is_todo'] : 0;
            $embedded_doc_id = isset($params['embedded_doc_id']) ? $params['embedded_doc_id'] : 0;
            $line_report_id = isset($params['line_report_id']) ? $params['line_report_id'] : 0;

            if ($embedded_doc_id) {
                $params['doc_id'] = $embedded_doc_id;
            }
            $query = $this->retrieve_doc_field_data($params);
            if ($query->have_posts()) {
                while ($query->have_posts()) : $query->the_post();
                    $field_id = get_the_ID();
                    $field_title = get_the_title($field_id);
                    $field_type = get_post_meta($field_id, 'field_type', true);
                    $default_value = get_post_meta($field_id, 'default_value', true);
                    $embedded_doc = get_post_meta($field_id, 'embedded_doc', true);

                    if ($report_id) {
                        $field_value = get_post_meta($report_id, $field_id, true);
                        $todo_status = get_post_meta($report_id, 'todo_status', true);
                    } elseif ($prev_report_id) {
                        $field_value = get_post_meta($prev_report_id, $field_id, true);
                        $todo_status = get_post_meta($prev_report_id, 'todo_status', true);
                        $report_id = $prev_report_id;
                    } else {
                        $field_value = $this->get_doc_field_default_value($field_id);
                    }
                    error_log('Get '.$field_type . '('. $field_id . ') value: ' . $field_value . ' report_id: ' . $report_id . ' prev_report_id: ' . $prev_report_id);

                    switch (true) {
                        case ($field_type=='_employee'):
                            ?>
                            <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                            <select id="<?php echo esc_attr($field_id);?>" class="select ui-widget-content ui-corner-all"><?php echo $this->select_multiple_employees_options(array($field_value));?></select>
                            <?php 
                            break;

                        case ($field_type=='_employees'):
                            ?>
                            <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                            <select multiple id="<?php echo esc_attr($field_id);?>" class="select ui-widget-content ui-corner-all"><?php echo $this->select_multiple_employees_options($field_value);?></select>
                            <?php 
                            break;

                        case ($field_type=='_embedded'):
                            if ($embedded_doc) {
                                ?>
                                <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                                <input type="hidden" id="<?php echo esc_attr($field_id); ?>" value="<?php echo esc_attr($embedded_doc);?>" />
                                <div id="sub-form">
                                    <fieldset>
                                    <?php
                                    $params = array(
                                        'embedded_doc_id' => $embedded_doc,
                                        'report_id' => $report_id,
                                    );
                                    $this->get_doc_field_contains($params);
                                    ?>
                                    </fieldset>
                                </div>
                                <?php
                            }
                            break;

                        case ($field_type=='_line_list'):
                            if ($embedded_doc) {
                                $items_class = new embedded_items();
                                ?>
                                <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                                <div id="line-report-list">
                                    <?php
                                    if ($report_id) echo $items_class->display_line_report_list($embedded_doc, $report_id);
                                    elseif ($prev_report_id) echo $items_class->display_line_report_list($embedded_doc, $prev_report_id);
                                    else echo $items_class->display_line_report_list($embedded_doc);
                                    ?>
                                </div>
                                <?php
                            }
                            break;

                        case ($field_type=='_select'):
                            if ($embedded_doc) {
                                ?>
                                <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                                <select id="<?php echo esc_attr($field_id);?>" class="select ui-widget-content ui-corner-all">
                                    <?php echo $this->select_doc_report_options($field_value, $embedded_doc);?>
                                </select>
                                <?php
                            }
                            break;

                        case ($field_type=='_doc_report'):
                            ?>
                            <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                            <select id="<?php echo esc_attr($field_id);?>" class="select ui-widget-content ui-corner-all">
                                <?php echo $this->select_document_list_options($field_value, 1);?>
                            </select>
                            <?php
                            break;

                        case ($field_type=='_document'):
                            ?>
                            <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                            <select id="<?php echo esc_attr($field_id);?>" class="select ui-widget-content ui-corner-all">
                                <?php echo $this->select_document_list_options($field_value, 0);?>
                            </select>
                            <?php
                            break;

                        case ($field_type=='_department'):
                            $items_class = new embedded_items();
                            ?>
                            <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                            <select id="<?php echo esc_attr($field_id);?>" class="select ui-widget-content ui-corner-all"><?php echo $items_class->select_department_card_options($field_value);?></select>
                            <?php
                            break;
    
                        case ($field_type=='_iot_device'):
                            $iot_messages = new iot_messages();
                            ?>
                            <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                            <select id="<?php echo esc_attr($field_id);?>" class="select ui-widget-content ui-corner-all"><?php echo $iot_messages->select_iot_device_options($field_value);?></select>
                            <?php
                            break;
                        case ($field_type=='video'):
                            echo '<label class="video-button button" for="'.esc_attr($field_id).'">'.esc_html($field_title).'</label>';
                            $field_value = ($field_value) ? $field_value : get_option('default_video_url');
                            echo '<div style="display:flex;" class="video-display" id="'.esc_attr($field_id.'_video').'">'.$field_value.'</div>';
                            echo '<textarea class="video-url" id="'.esc_attr($field_id).'" rows="3" style="width:100%; display:none;" >'.esc_html($field_value).'</textarea>';
                            break;

                        case ($field_type=='image'):
                            echo '<label class="image-button button" for="'.esc_attr($field_id).'">'.esc_html($field_title).'</label>';
                            $field_value = ($field_value) ? $field_value : get_option('default_image_url');
                            echo '<img style="width:100%;" class="image-display" src="'.$field_value.'" />';
                            echo '<textarea class="image-url" id="'.esc_attr($field_id).'" rows="3" style="width:100%; display:none;" >'.esc_html($field_value).'</textarea>';
                            break;

                        case ($field_type=='canvas'):
                            ?>
                            <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                            <div id="signature-image-div">
                                <?php if ($field_value) {?>
                                    <div><img id="<?php echo esc_attr($field_id);?>" src="<?php echo esc_attr($field_value);?>" alt="Signature Image" /></div>
                                <?php }?>
                                <?php if (!isset($todo_status)) {?>
                                    <button id="redraw-signature" style="margin:3px;"><?php echo __( 'Redraw', 'textdomain' );?></button>
                                <?php }?>
                            </div>
                            <div style="display:none;" id="signature-pad-div">
                                <div>
                                    <canvas id="signature-pad" width="500" height="200" style="border:1px solid #000;"></canvas>
                                </div>
                                <button id="clear-signature" style="margin:3px;">Clear</button>
                            </div>
                            <?php
                            break;

                        case ($field_type=='heading'):
                            $default_value = ($default_value) ? $default_value : 'b';
                            ?>
                            <div><<?php echo esc_html($default_value);?>><?php echo esc_html($field_title);?></<?php echo esc_html($default_value);?>></div>
                            <?php
                            break;

                        case ($field_type=='textarea'):
                            ?>
                            <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                            <textarea id="<?php echo esc_attr($field_id);?>" rows="3" style="width:100%;"><?php echo esc_html($field_value);?></textarea>
                            <?php    
                            break;

                        case ($field_type=='checkbox'):
                            $is_checked = ($field_value==1) ? 'checked' : '';
                            ?>
                            <div>
                            <input type="checkbox" id="<?php echo esc_attr($field_id);?>" <?php echo $is_checked;?> />
                            <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                            </div>
                            <?php
                            break;

                        case ($field_type=='radio'):
                            $is_checked = ($field_value==1) ? 'checked' : '';
                            ?>
                            <div>
                            <input type="radio" id="<?php echo esc_attr($field_id);?>" name="<?php echo esc_attr(substr(time(), 0, 5));?>" <?php echo $is_checked;?> />
                            <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                            </div>
                            <?php
                            break;

                        case ($field_type=='date'):
                            ?>
                            <div>
                            <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                            <input type="date" id="<?php echo esc_attr($field_id);?>" value="<?php echo esc_html($field_value);?>" class="ui-widget-content ui-corner-all" />
                            </div>
                            <?php
                            break;

                        case ($field_type=='time'):
                            ?>
                            <div>
                            <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                            <input type="time" id="<?php echo esc_attr($field_id);?>" value="<?php echo esc_html($field_value);?>" class="ui-widget-content ui-corner-all" />
                            </div>
                            <?php
                            break;

                        case ($field_type=='number'):
                            ?>
                            <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                            <input type="number" id="<?php echo esc_attr($field_id);?>" value="<?php echo esc_html($field_value);?>" class="text ui-widget-content ui-corner-all" />
                            <?php
                            break;

                        default:
                            ?>
                            <label for="<?php echo esc_attr($field_id);?>"><?php echo esc_html($field_title);?></label>
                            <input type="text" id="<?php echo esc_attr($field_id);?>" value="<?php echo esc_html($field_value);?>" class="text ui-widget-content ui-corner-all" />
                            <?php    
                            break;
                    }
                endwhile;
                wp_reset_postdata();
            }
        }

        function update_doc_field_contains($params=array()) {
            //$documents_class = new display_documents();
            $report_id = isset($params['report_id']) ? $params['report_id'] : 0;
            $is_default = isset($params['is_default']) ? $params['is_default'] : false;
            $user_id = isset($params['user_id']) ? $params['user_id'] : 0;
            $doc_id = get_post_meta($report_id, 'doc_id', true);
            $query = $this->retrieve_doc_field_data(array('doc_id' => $doc_id));
            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    // standard fields
                    $field_id = get_the_ID();
                    $field_type = get_post_meta($field_id, 'field_type', true);
                    $default_value = get_post_meta($field_id, 'default_value', true);
                    $embedded_doc = get_post_meta($field_id, 'embedded_doc', true);
                    if ($is_default) {
                        $field_value = $this->get_doc_field_default_value($field_id, $user_id);
                    } else {
                        $field_value = $_POST[$field_id];
                    }

                    if (!empty($field_value)) {
                        update_post_meta($report_id, $field_id, $field_value);
                        error_log('Update '.$field_type . '('. $field_id . ') value: ' . $field_value . ' for report_id: ' . $report_id);
    
                        // special field-type
                        if ($field_type=='_employees'){
                            $employee_ids = get_post_meta($report_id, '_employees', true);
                            // Ensure $employee_ids is an array, or initialize it as an empty array
                            if (!is_array($employee_ids)) {
                                $employee_ids = array();
                            }
            
                            if ($default_value=='me'){
                                $current_user_id = get_current_user_id();
                                // Check if the $current_user_id is not already in the $employee_ids array
                                if (!in_array($current_user_id, $employee_ids)) {
                                    // Add the value to the $employee_ids array
                                    $employee_ids = array($current_user_id);
                                }
                            } else {
                                // Loop through each value in $field_value to check and add to $employee_ids
                                foreach ($field_value as $value) {
                                    // Check if the value is not already in the $employee_ids array
                                    if (!in_array($value, $employee_ids)) {
                                        // Add the value to the $employee_ids array
                                        $employee_ids[] = $value;
                                    }
                                }    
                            }
                            update_post_meta($report_id, '_employees', $employee_ids);
                        }
            
                        if ($field_type=='_employee'){
                            update_post_meta($report_id, '_employee', $field_value);
                        }
            
                        if ($field_type=='_document'){
                            update_post_meta($report_id, '_document', $field_value);
                        }
/*            
                        if ($field_type=='_department'){
                            update_post_meta($report_id, '_department', $field_value);
                        }
*/            
                        if ($field_type=='_embedded'){
                            if ($embedded_doc) {
                                $inner_query = $documents_class->retrieve_doc_field_data(array('doc_id' => $embedded_doc));
                                if ($inner_query->have_posts()) :
                                    while ($inner_query->have_posts()) : $inner_query->the_post();
                                        $embedded_id = get_the_ID();
                                        $embedded_item_value = $_POST[$embedded_id];
                                        update_post_meta($report_id, $embedded_id, $embedded_item_value);
                                        error_log('Update '.$field_type . '('. $embedded_id . ') value: ' . $embedded_item_value . ' for report_id: ' . $report_id);
                                    endwhile;
                                    wp_reset_postdata();
                                endif;
                            }
                        }
            
                        if ($field_type=='_line_list'){
                            if ($embedded_doc) {
                                $inner_query = $items_class->retrieve_line_report_data($embedded_doc);
                                if ($inner_query->have_posts()) :
                                    while ($inner_query->have_posts()) : $inner_query->the_post();
                                        $embedded_id = get_the_ID();
                                        update_post_meta($embedded_id, 'report_id', $report_id);
                                    endwhile;
                                    wp_reset_postdata();
                                endif;
                            }
                        }

                        if ($field_type=='_select'){
                            if ($embedded_doc) {
                                update_post_meta($report_id, '_select', $field_value);
                            }
                        }

                    }
                }
                wp_reset_postdata();
            }
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

        // document misc
        function select_document_list_options($selected_option=false, $is_doc_report=false) {
            $query = $this->retrieve_document_data(0, $is_doc_report);
            $options = '<option value="">'.__( 'Select Option', 'textdomain' ).'</option>';
            while ($query->have_posts()) : $query->the_post();
                $doc_id = get_the_ID();
                $doc_title = get_the_title($doc_id);
                $doc_number = get_post_meta($doc_id, 'doc_number', true);
                $doc_revision = get_post_meta($doc_id, 'doc_revision', true);
                $selected = ($selected_option == $doc_id) ? 'selected' : '';
                $options .= '<option value="' . esc_attr($doc_id) . '" '.$selected.' >' . esc_html($doc_number.'-'.$doc_title) . '</option>';
            endwhile;
            wp_reset_postdata();
            return $options;
        }

        function get_iso_helper_documents_by_iso_category($iso_category_id) {
            // Step 1: Get the 'site-profile' post with the title 'iso-helper.com'
            $args = array(
                'post_type'   => 'site-profile',
                'post_status' => 'publish', // Only look for published pages
                'title'       => 'iso-helper.com',
                'numberposts' => 1,         // Limit the number of results to one
            );            
            $posts = get_posts($args); // get_posts returns an array

            // Ensure there's a post returned
            if (!empty($posts)) {
                $site_id = $posts[0]->ID; // Retrieve the ID of the first post
            } else {
                return new WP_Query(); // Return an empty query if no 'site-profile' found
            }

            // Step 2: Get the IDs from the 'doc-category' post type where 'iso_category' meta = $iso_category_id and 'site_id' = $site_id
            $doc_category_query = new WP_Query(array(
                'post_type'  => 'doc-category',
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'site_id',
                        'value'   => $site_id,
                    ),
                    array(
                        'key'     => 'iso_category',
                        'value'   => $iso_category_id,
                    ),
                ),
                'posts_per_page' => -1, // Retrieve all matching posts from 'doc-category'
                'fields' => 'ids', // Retrieve only the post IDs for efficiency
            ));

            // Step 3: Check if we found posts in 'doc-category'
            if ($doc_category_query->have_posts()) {
                $doc_category_ids = $doc_category_query->posts; // Get all IDs of 'doc-category' posts
                wp_reset_postdata(); // Reset post data after query

                // Step 4: Use the retrieved doc-category IDs to query the 'document' post type
                $document_query = new WP_Query(array(
                    'post_type'  => 'document',
                    'meta_query' => array(
                        array(
                            'key'     => 'doc_category',
                            'value'   => $doc_category_ids,
                            'compare' => 'IN' // Match any of the retrieved 'doc_category' IDs
                        ),
                    ),
                    'posts_per_page' => -1, // Retrieve all matching posts
                    'meta_key' => 'doc_number', // Sort by 'doc_number' meta field
                    'orderby'  => 'meta_value', // Order by meta value
                    'order'    => 'ASC', // Sort in ascending order
                ));
                // Return the query object
                return $document_query;
            } else {
                // If no 'doc-category' posts are found, return an empty WP_Query
                return new WP_Query(); // Empty query object
            }
        }

        function display_iso_start_ai_content($iso_category_id=false, $paged=1) {
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $embedded_id = get_post_meta($iso_category_id, 'embedded', true);
            $iso_category_title = get_the_title($iso_category_id);
            ?>
            <div class="ui-widget" id="result-container">
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <div>
                        <?php echo display_iso_helper_logo();?>
                        <h2 style="display:inline;"><?php echo esc_html($iso_category_title.' '.__( '啟動AI輔導', 'textdomain' ));?></h2>
                    </div>
                </div>
                <input type="hidden" id="iso-category-title" value="<?php echo esc_attr($iso_category_title);?>" />
                <input type="hidden" id="iso-category-id" value="<?php echo esc_attr($iso_category_id);?>" />            
                <fieldset>
                    <?php
                    if ($paged==1) {
                        $prompt = isset($_GET['_prompt']) ? $_GET['_prompt'] : __( 'The file list conforms to the High-Level Structure (HLS)', 'textdomain' );
                        $content = generate_content($iso_category_title.' '.$prompt);

                        // Suppress warnings for invalid HTML
                        libxml_use_internal_errors(true);

                        // Create a new DOMDocument instance
                        $dom = new DOMDocument('1.0', 'UTF-8');

                        // Ensure the content is UTF-8 encoded
                        if (!mb_check_encoding($content, 'UTF-8')) {
                            $content = mb_convert_encoding($content, 'UTF-8', 'auto');
                        }
                        
                        // Add the necessary structure if the content is partial HTML
                        $content = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . $content . '</body></html>';
                        
                        // Load the HTML content into the DOMDocument
                        $dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

                        // Initialize an array to store extracted content
                        $content_lines = [];
                        
                        // Extract content from <p> tags
                        foreach ($dom->getElementsByTagName('p') as $p) {
                            // Get the inner HTML of the <p> element
                            $innerHTML = '';
                            foreach ($p->childNodes as $child) {
                                $innerHTML .= $dom->saveHTML($child);
                            }
                        
                            // Replace <br> tags with <li> tags
                            $html_with_li = preg_replace('/<br\s*\/?>/i', '</li><li>', $innerHTML);
                        
                            // Wrap the content in <ul> tags to ensure valid HTML
                            $html_with_li_wrapped = "<ul><li>$html_with_li</li></ul>";
                        
                            // Load the updated HTML into a new DOMDocument
                            $tempDom = new DOMDocument('1.0', 'UTF-8');
                            @$tempDom->loadHTML('<?xml encoding="UTF-8">' . $html_with_li_wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                        
                            // Extract content from <li> tags
                            foreach ($tempDom->getElementsByTagName('li') as $li) {
                                $line = trim($li->textContent);
                                if (!empty($line)) {
                                    $content_lines[] = $line;
                                    //$content_lines[] = '<li>'.$line.'</li>';
                                }
                            }
                        }

                        // Remove duplicates and reset keys
                        $content_lines = array_values(array_unique($content_lines));

                        ?>
                        <div class="content">                            
                            <?php //echo $content;?>
                            <?php
                            foreach ($content_lines as $line) {
                                $line = trim($line); // Ensure the line is trimmed
                                $starts_with_number_or_roman = preg_match('/^(I{1,3}|IV|V|VI|\d+)/i', $line); // Check for numbers or Roman numerals
                                $ends_with_colon = preg_match('/[:：]$/', $line); // Check if the string ends with ':' or '：'
                            
                                //if (strlen($line) < 100) {
                                    if ($starts_with_number_or_roman || $ends_with_colon) {
                                        // Display the string without a link
                                        echo "<p>" . htmlspecialchars($line) . "</p>";
                                    } else {
                                        // Create a link for the string
                                        $prompt = urlencode($line); // URL-encode the prompt
                                        $link = "/display-documents?_start_ai=$iso_category_id&_paged=2&_prompt=$prompt";
                                        $link = home_url($link);
                                        echo "<li><a href=\"$link\" target=\"_blank\">" . htmlspecialchars($line) . "</a></li>";
                                    }
                                //}
                            }
                            ?>
                            <fieldset>
                                <?php
                                $query = $this->get_iso_helper_documents_by_iso_category($iso_category_id);
                                if ($query->have_posts()) :
                                    while ($query->have_posts()) : $query->the_post();
                                        $doc_id = get_the_ID();
                                        $doc_title = get_the_title();
                                        ?>
                                        <div>
                                            <input type="checkbox" class="copy-document-class" id="<?php echo $doc_id;?>" checked />
                                            <label for="<?php echo $doc_id;?>"><?php echo $doc_title;?></label>
                                        </div>
                                        <?php
                                    endwhile;
                                    wp_reset_postdata();
                                    if (is_site_admin()) {?>
                                        <button id="proceed-to-copy" class="button" style="margin:5px; width:99%;"><?php echo __( 'Copy the checked documents from iso-helper.com', 'textdomain' );?></button>
                                    <?php }
                                endif;
                                ?>
                            </fieldset>
                            <div style="margin:1em; padding:10px; border:solid; border-radius:1.5rem;">
                                <input type="text" id="ask-gemini" placeholder="<?php echo __( 'Ask Gemini', 'textdomain' );?>" class="text ui-widget-content ui-corner-all" />
                            </div>
                        </div>
                        <?php
                    } else {
                        $prompt = isset($_GET['_prompt']) ? $_GET['_prompt'] : __( '適用性聲明書', 'textdomain' );
                        $content = generate_content($iso_category_title.' '.$prompt);
                        $items_class = new embedded_items();
                        ?>
                        <div class="content">
                            <fieldset>
                                <label for="draft-title"><?php echo __( 'Title', 'textdomain' );?></label><br>
                                <input type="text" id="draft-title" value="<?php echo $iso_category_title.' '.$prompt;?>" class="text ui-widget-content ui-corner-all" />
                                <label for="draft-category"><?php echo __( 'Category', 'textdomain' );?></label><br>
                                <select id="draft-category" class="select ui-widget-content ui-corner-all"><?php echo $items_class->select_doc_category_options();?></select>
                                <label for="draft-content"><?php echo __( 'Content', 'textdomain' );?></label><br>
                                <textarea id="draft-content" class="visual-editor"><?php echo $content;?></textarea>
                                <?php if (is_site_admin()) {?>
                                    <p><input type="button" id="save-draft" value="<?php echo __( 'Generate draft', 'textdomain' );?>" /></p>
                                <?php }?>
                            </fieldset>
                            <div style="margin:1em; padding:10px; border:solid; border-radius:1.5rem;">
                                <input type="text" id="ask-gemini" placeholder="<?php echo __( 'Ask Gemini', 'textdomain' );?>" class="text ui-widget-content ui-corner-all" />
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </fieldset>
                <div style="display:flex; justify-content:space-between; margin:5px;">
                    <?php if ($paged==1) {?>
                        <div>
                            <button id="exit-statement" class="button" style="margin:5px;"><?php echo __( 'Back', 'textdomain' );?></button>
                        </div>
                        <div style="text-align: right">
                            <button id="statement-page1-next-step" class="button" style="margin:5px;"><?php echo __( 'Next', 'textdomain' );?></button>
                        </div>
                    <?php } else {?>
                        <div>
                            <button id="statement-page2-prev-step" class="button" style="margin:5px;"><?php echo __( 'Back', 'textdomain' );?></button>
                        </div>
                        <div style="text-align: right">
                            <button id="exit-statement" class="button" style="margin:5px;"><?php echo __( 'OK', 'textdomain' );?></button>
                        </div>
                    <?php }?>
                </div>
            </div>
            <?php
        }

        function set_iso_start_ai_data() {
            $response = array('success' => false, 'error' => 'Invalid data format');

            if (isset($_POST['_keyValuePairs']) && is_array($_POST['_keyValuePairs'])) {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $keyValuePairs = $_POST['_keyValuePairs'];
                $processedKeyValuePairs = [];
                foreach ($keyValuePairs as $pair) {
                    foreach ($pair as $field_key => $field_value) {
                        // Sanitize the key and value
                        $field_key = sanitize_text_field($field_key);
                        $field_value = sanitize_text_field($field_value);
                        // Update post meta
                        update_post_meta($site_id, $field_key, $field_value);
                        // Add the sanitized pair to the processed array
                        $processedKeyValuePairs[$field_key] = $field_value;
                    }
                }
                $response = array('success' => true, 'data' => $processedKeyValuePairs);

            } elseif (isset($_POST['_draft_title']) && isset($_POST['_draft_content'])) {
                $current_user_id = get_current_user_id();
                $site_id = get_user_meta($current_user_id, 'site_id', true);
                $draft_title = sanitize_text_field($_POST['_draft_title']);
                $draft_category = sanitize_text_field($_POST['_draft_category']);
                $draft_content = $_POST['_draft_content'];
                $draft_post = array(
                    'post_type'    => 'document',
                    'post_title'   => $draft_title,
                    'post_content' => $draft_content,
                    'post_status'  => 'publish',
                    'post_author'  => $current_user_id,
                );
                $draft_id = wp_insert_post($draft_post);
                update_post_meta($draft_id, 'site_id', $site_id);
                update_post_meta($draft_id, 'doc_number', '-');
                update_post_meta($draft_id, 'doc_revision', __( 'draft', 'textdomain' ));
                update_post_meta($draft_id, 'doc_category', $draft_category);

                $params = array(
                    'log_message' => sprintf( __( 'Draft %s has been created.', 'textdomain' ), esc_html( $draft_title ) ),
                    'action_title' => 'Create',
                    'doc_id' => $draft_id,
                );
                $todo_class = new to_do_list();
                $todo_class->set_transaction_log($params);

                $response = array('success' => true, 'data' => $draft_id);

            } elseif (isset($_POST['_duplicated_ids'])) {
                $duplicated_ids = $_POST['_duplicated_ids'];
                foreach ($duplicated_ids as $duplicated_id) {
                    if ($this->current_site_is_iso_helper()) {
                        if (current_user_can('administrator')) {
                            $this->generate_draft_document_data($duplicated_id);
                        }
                    }
                    else {
                        $this->generate_draft_document_data($duplicated_id);
                    }
                }
                $response = array('success' => true, 'data' => $duplicated_ids);
            }
            wp_send_json($response);
        }
        
        function current_site_is_iso_helper() {
            // Query the "site-profile" post type to find a post with the title "iso-helper.com"
            $args = array(
                'post_type'      => 'site-profile',
                'title'          => 'iso-helper.com',
                'posts_per_page' => 1,
                'fields'         => 'ids' // Only fetch the post ID
            );
            $query = new WP_Query($args);

            // Check if we have any matching "site-profile" posts
            if ($query->have_posts()) {
                //return "Matching 'site-profile' post found for site_id: $site_id with title 'iso-helper.com'.";
                return true;
            } else {
                //return "No matching 'site-profile' post found for site_id: $site_id with title 'iso-helper.com'.";
                return false;
            }
        }

        function generate_draft_document_data($doc_id=false){
            $current_user_id = get_current_user_id();
            $site_id = get_user_meta($current_user_id, 'site_id', true);
            $doc_title = get_the_title($doc_id);
            $doc_number = get_post_meta($doc_id, 'doc_number', true);
            $is_doc_report = get_post_meta($doc_id, 'is_doc_report', true);
            // Create the post
            $new_post = array(
                'post_type'     => 'document',
                'post_title'    => $doc_title,
                'post_content'  => get_post_field('post_content', $doc_id),
                'post_status'   => 'publish',
                'post_author'   => $current_user_id,
            );    
            $post_id = wp_insert_post($new_post);

            update_post_meta($post_id, 'site_id', $site_id);
            update_post_meta($post_id, 'doc_number', $doc_number);
            update_post_meta($post_id, 'doc_revision', 'draft');
            update_post_meta($post_id, 'is_doc_report', $is_doc_report);

            $params = array(
                'log_message' => sprintf( __( 'Draft %s has been created.', 'textdomain' ), esc_html( $doc_title ) ),
                'action_title' => 'Create',
                'doc_id' => $doc_id,
            );
            $todo_class = new to_do_list();
            $todo_class->set_transaction_log($params);

            // Create the Action List for $post_id
            $profiles_class = new display_profiles();
            $query = $profiles_class->retrieve_site_action_data(0, $doc_id);
            if ($query->have_posts()) {
                while ($query->have_posts()) : $query->the_post();
                    $action_id = get_the_ID();
                    $new_post = array(
                        'post_type'     => 'action',
                        'post_title'    => get_the_title(),
                        'post_content'  => get_the_content(),
                        'post_status'   => 'publish',
                        'post_author'   => $current_user_id,
                    );    
                    $new_action_id = wp_insert_post($new_post);
                    $new_next_job = get_post_meta($action_id, 'next_job', true);
                    $new_next_leadtime = get_post_meta($action_id, 'next_leadtime', true);
                    update_post_meta($new_action_id, 'doc_id', $post_id);
                    update_post_meta($new_action_id, 'next_job', $new_next_job);
                    update_post_meta($new_action_id, 'next_leadtime', $new_next_leadtime);
                endwhile;
                wp_reset_postdata();
            }

            if ($is_doc_report==1){
                $query = $this->retrieve_doc_field_data(array('doc_id' => $doc_id));
                if ($query->have_posts()) {
                    while ($query->have_posts()) : $query->the_post();
                        $field_id = get_the_ID();
                        $field_title = get_the_title();
                        $field_type = get_post_meta($field_id, 'field_type', true);
                        $default_value = get_post_meta($field_id, 'default_value', true);
                        $listing_style = get_post_meta($field_id, 'listing_style', true);
                        $sorting_key = get_post_meta($field_id, 'sorting_key', true);
                        $new_post = array(
                            'post_type'     => 'doc-field',
                            'post_title'    => $field_title,
                            'post_status'   => 'publish',
                            'post_author'   => $current_user_id,
                        );    
                        $new_field_id = wp_insert_post($new_post);
                        update_post_meta($new_field_id, 'doc_id', $post_id);
                        update_post_meta($new_field_id, 'field_type', $field_type);
                        update_post_meta($new_field_id, 'default_value', $default_value);
                        update_post_meta($new_field_id, 'listing_style', $listing_style);
                        update_post_meta($new_field_id, 'sorting_key', $sorting_key);
                    endwhile;
                    wp_reset_postdata();
                }    
            }
        }
        
        function reset_doc_report_todo_status() {
            $response = array();
            if( isset($_POST['_report_id']) ) {
                $report_id = sanitize_text_field($_POST['_report_id']);
                delete_post_meta($report_id, 'todo_status');
            }
            wp_send_json($response);
        }

        function update_document_revision($post_id) {
            if (get_post_type($post_id) !== 'document') {
                return;
            }
        
            // Get the current revision
            $current_revision = get_post_meta($post_id, 'doc_revision', true);
        
            // Define revision update rules
            $revision_rules = [
                'draft' => '1.0',
                '1.0'   => '1.1',
                '1.1'   => '1.2',
                '2.0'   => '2.1',
                'A'     => 'B'
            ];
        
            // Update revision based on rules
            if (isset($revision_rules[$current_revision])) {
                $new_revision = $revision_rules[$current_revision];
            } elseif (is_numeric($current_revision)) {
                // Handle other numerical versions (e.g., 3.0 → 3.1)
                $new_revision = number_format((float) $current_revision + 0.1, 1);
            } elseif (ctype_alpha($current_revision) && strlen($current_revision) === 1) {
                // Handle letter versions (e.g., C → D)
                $new_revision = chr(ord($current_revision) + 1);
            } else {
                // Default case: do not update
                return;
            }
        
            // Update the post meta with the new revision
            update_post_meta($post_id, 'doc_revision', $new_revision);
        
            // Log the update
            error_log("Updated doc_revision for post {$post_id}: {$current_revision} → {$new_revision}");
        }
    }
    $documents_class = new display_documents();
}

