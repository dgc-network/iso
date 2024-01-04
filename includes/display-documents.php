<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Shortcode to display documents
function display_documents_shortcode() {
    ob_start(); // Start output buffering

    $args = array(
        'post_type'      => 'document',
        'posts_per_page' => -1,
    );
    $query = new WP_Query($args);

    if ($query->have_posts()) :?>
        <h2><?php echo __( 'Documents', 'your-text-domain' );?></h2>
        <table class="display-documents" style="width:100%;">
            <thead>
                <tr>
                    <th></th>
                    <th><?php echo __( '文件名稱', 'your-text-domain' );?></th>
                    <th><?php echo __( '編號', 'your-text-domain' );?></th>
                    <th><?php echo __( '版本', 'your-text-domain' );?></th>
                    <th><?php echo __( '發行日期', 'your-text-domain' );?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
        <?php
        $x = 0;
        while ($query->have_posts()) : $query->the_post();
            $post_id = (int) get_the_ID();
            $document_url = esc_html(get_post_meta($post_id, 'document_url', true));
            ?>
                <tr id="document-list-<?php echo $x;?>">
                    <td style="text-align:center;"><span id="btn-edit-document-<?php the_ID();?>" class="dashicons dashicons-edit"></span></td>
                    <td><a href="<?php echo $document_url;?>"><?php the_title();?></a></td>
                    <td style="text-align:center;"><?php echo esc_html(get_post_meta($post_id, 'document_number', true));?></td>
                    <td style="text-align:center;"><?php echo esc_html(get_post_meta($post_id, 'document_revision', true));?></td>
                    <td style="text-align:center;"><?php echo esc_html(get_post_meta($post_id, 'document_date', true));?></td>
                    <td style="text-align:center;"><span id="btn-del-document-<?php the_ID();?>" class="dashicons dashicons-trash"></span></td>
                </tr>
            <?php 
            $x += 1;
        endwhile;
        while ($x<50) {
            echo '<tr id="document-list-'.$x.'" style="display:none;"></tr>';
            $x += 1;
        }
        ?>
            </tbody>
            <tr><td colspan="6"><div id="btn-new-document" style="border:solid; margin:3px; text-align:center; border-radius:5px">+</div></td></tr>
        </table>
        <div id="document-dialog" title="Document dialog" style="display:none;">
        <fieldset>
            <input type="hidden" id="document-id" />
            <label for="document-title">Title:</label>
            <input type="text" id="document-title" />
            <label for="document-number">Doc.#:</label>
            <input type="text" id="document-number" />
            <label for="document-revision">Revision:</label>
            <input type="text" id="document-revision" />
            <label for="document-date">Date:</label>
            <input type="text" id="document-date" />
            <label for="document-url">URL:</label>
            <textarea id="document-url" rows="3" style="width:99%;"></textarea>
        </fieldset>
        </div>

        <?php
        wp_reset_postdata();
        
    else :
        echo '<h2>'.__( 'No documents found', 'your-text-domain' ).'</h2>';
    endif;

    return ob_get_clean(); // Return the buffered content
}
add_shortcode('display-documents', 'display_documents_shortcode');

function retrieve_documents_data($_id=0) {
    // Retrieve the documents value
    $args = array(
        'post_type'      => 'document', // Change to your custom post type if needed
        'posts_per_page' => -1, // Retrieve all posts
        'meta_query'     => array(
            'relation' => 'AND', // Combine conditions with AND
            array(
                'key'     => 'course_id', // Replace with your first meta key
                'value'   => $_id, // Replace with the desired value
                'compare' => '=', // Change to your desired comparison (e.g., '=', '>', '<', etc.)
            ),
            array(
                'key'     => 'sorting_in_course', // Replace with your second meta key
                'compare' => 'EXISTS', // Check if the second meta key exists
            ),
        ),
        'orderby'        => 'meta_value', // Order by the second meta key value
        'meta_key'       => 'sorting_in_course', // Specify the second meta key for ordering
        'order'          => 'ASC', // Change to 'DESC' for descending order
    );    
    $query = new WP_Query($args);
    return $query;
}

function get_document_list_data() {
    // Retrieve the documents data
    //$query = retrieve_documents_data($_POST['_course_id']);
    $args = array(
        'post_type'      => 'document',
        'posts_per_page' => -1,
    );
    $query = new WP_Query($args);

    $_array = array();
    if ($query->have_posts()) {
        //while ($query->have_posts()) : $query->the_post();
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = (int) get_the_ID();
            $document_url = esc_html(get_post_meta($post_id, 'document_url', true));
            $_list = array();
            $_list["document_id"] = get_the_ID();
            $_list["document_title"] = '<a href="'.$document_url.'">'.get_the_title().'</a>';
            $_list["document_number"] = esc_html(get_post_meta($post_id, 'document_number', true));
            $_list["document_revision"] = esc_html(get_post_meta($post_id, 'document_revision', true));
            $_list["document_date"] = esc_html(get_post_meta($post_id, 'document_date', true));
            $_list["document_url"] = esc_html(get_post_meta($post_id, 'document_url', true));
            array_push($_array, $_list);
        }    
        wp_reset_postdata(); // Reset post data to the main loop
    }
    wp_send_json($_array);
}
add_action( 'wp_ajax_get_document_list_data', 'get_document_list_data' );
add_action( 'wp_ajax_nopriv_get_document_list_data', 'get_document_list_data' );

function get_document_dialog_data() {
    $response = array();
    if( isset($_POST['_document_id']) ) {
        $response["document_title"] = get_the_title($_POST['_document_id']);
        $response["document_number"] = esc_html(get_post_meta($_POST['_document_id'], 'document_number', true));
        $response["document_revision"] = esc_html(get_post_meta($_POST['_document_id'], 'document_revision', true));
        $response["document_date"] = esc_html(get_post_meta($_POST['_document_id'], 'document_date', true));
        $response["document_url"] = esc_html(get_post_meta($_POST['_document_id'], 'document_url', true));
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_get_document_dialog_data', 'get_document_dialog_data' );
add_action( 'wp_ajax_nopriv_get_document_dialog_data', 'get_document_dialog_data' );

function set_document_dialog_data() {
    if( isset($_POST['_document_id']) ) {
        $data = array(
            'ID'         => $_POST['_document_id'],
            'post_title' => $_POST['_document_title'],
            'meta_input' => array(
                'document_number'   => $_POST['_document_number'],
                'document_revision' => $_POST['_document_revision'],
                'document_date'     => $_POST['_document_date'],
                'document_url'     => $_POST['_document_url'],
            )
        );
        wp_update_post( $data );
    } else {
        $current_user = wp_get_current_user();
        // Set up the post data
        $new_post = array(
            'post_title'    => 'New document',
            'post_content'  => 'Your post content goes here.',
            'post_status'   => 'publish', // Publish the post immediately
            'post_author'   => $current_user->ID, // Use the user ID of the author
            'post_type'     => 'document', // Change to your custom post type if needed
        );    
        // Insert the post into the database
        $post_id = wp_insert_post($new_post);
    }
    wp_send_json($response);
}
add_action( 'wp_ajax_set_document_dialog_data', 'set_document_dialog_data' );
add_action( 'wp_ajax_nopriv_set_document_dialog_data', 'set_document_dialog_data' );

function del_document_dialog_data() {
    // Delete the post
    $result = wp_delete_post($_POST['_document_id'], true); // Set the second parameter to true to force delete    
    wp_send_json($result);
}
add_action( 'wp_ajax_del_document_dialog_data', 'del_document_dialog_data' );
add_action( 'wp_ajax_nopriv_del_document_dialog_data', 'del_document_dialog_data' );


// Shortcode to display
function g01_01_shortcode() {
    ob_start(); // Start output buffering

    $args = array(
        'post_type'      => 'record',
        'posts_per_page' => -1,
    );
    $query = new WP_Query($args);

    echo '';
    if ($query->have_posts()) : ?>
        <h2><?php echo __( '衛生管理日誌', 'your-text-domain' );?></h2>
        <table class="g01-01-table" style="width:100%;">
            <thead>
                <tr>
                    <th></th>
                    <th><?php echo __( '區域', 'your-text-domain' );?></th>
                    <th><?php echo __( '檢查項目', 'your-text-domain' );?></th>
                    <th><?php echo __( '檢查日期', 'your-text-domain' );?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
        <?php
        while ($query->have_posts()) : $query->the_post();
            ?>
                <tr>
                    <td style="text-align:center;"><span id="btn-edit-g01-01-<?php the_ID();?>" class="dashicons dashicons-edit"></span></td>
                    <td><?php the_ID();?></td>
                    <td><?php the_title();?></td>
                    <td><?php echo esc_html(get_post_meta(get_the_ID(), 'checked_date', true));?></td>
                    <td style="text-align:center;"><span id="btn-del-g01-01-<?php the_ID();?>" class="dashicons dashicons-trash"></span></td>
                </tr>
            <?php 
        endwhile; ?>
        </table>
        <?php
        wp_reset_postdata();
        
    else :
        echo '<h2>'.__( 'No records found', 'your-text-domain' ).'</h2>';
    endif;

    return ob_get_clean(); // Return the buffered content
}
add_shortcode('g01-01', 'g01_01_shortcode');

// Handle "Add to Cart" AJAX request
function add_to_cart_ajax() {
    //check_ajax_referer('add_to_cart_nonce', 'nonce');

    $product_id = isset($_POST['formData']['product_id']) ? intval($_POST['formData']['product_id']) : 0;
    $quantity = isset($_POST['formData']['quantity']) ? intval($_POST['formData']['quantity']) : 1;

    // Perform server-side operations
    // Add the product to the shopping cart
    session_start();
    if (!isset($_SESSION['cart'])) $_SESSION['cart']=array();
    $cart_item_key = array_search($product_id, array_column($_SESSION['cart'], 'product_id'));

    if ($cart_item_key !== false) {
        // Update quantity if product is already in the cart
        $_SESSION['cart'][$cart_item_key]['quantity'] += $quantity;
    } else {
        // Add the product to the cart
        $cart_item = array(
            'product_id' => $product_id,
            'quantity' => $quantity,
        );
        $_SESSION['cart'][] = $cart_item;
    }

    // Return a JSON response
    $response = array(
        'success' => true,
        'message' => 'Item added to the cart successfully!',
        'cart_url' => '/cart/', // Replace with the actual URL
    );

    wp_send_json($response);
}
add_action('wp_ajax_add_to_cart_ajax', 'add_to_cart_ajax');
add_action('wp_ajax_nopriv_add_to_cart_ajax', 'add_to_cart_ajax');
