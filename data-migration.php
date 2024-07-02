<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

        // Data migration
        function data_migration() {
            // 2024-5-14 To update the document posts based on the job posts and then 
            // redirect back to the same page without the query parameters.
            if (isset($_GET['_job_number_migration'])) {
                $args = array(
                    'post_type'      => 'document',
                    'posts_per_page' => -1,
                );
                $query = new WP_Query($args);
            
                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        $doc_id = get_the_ID();
                        $start_job = get_post_meta($doc_id, 'start_job', true);
                        $doc_number = get_post_meta($doc_id, 'doc_number', true);
            
                        $job_args = array(
                            'post_type'      => 'job',
                            'posts_per_page' => 1,
                            'post__in'       => array($start_job), // Specify the job ID to retrieve
                        );
                        $job_query = new WP_Query($job_args);
            
                        if ($job_query->have_posts()) {
                            while ($job_query->have_posts()) {
                                $job_query->the_post();
                                $job_id = get_the_ID();
                                $job_title = get_the_title();
                                $job_content = get_the_content();
                                $job_number = get_post_meta($job_id, 'job_number', true);
                                $department = get_post_meta($job_id, 'department', true);
                            }
                            // Restore global post data
                            wp_reset_postdata();
            
                            // Update document post with job title and content
                            $doc_post_args = array(
                                'ID'           => $doc_id,
                                'post_title'   => $job_title,
                                'post_content' => $job_content,
                            );
                            wp_update_post($doc_post_args);
                            update_post_meta($doc_id, 'job_number', $job_number);
                            update_post_meta($doc_id, 'department', $department);

                        } else {
                            update_post_meta($doc_id, 'job_number', $doc_number);

                        }
                    }
                }
                // Get the current URL without any query parameters
                $current_url = remove_query_arg( array_keys( $_GET ) );
                // Redirect to the URL without any query parameters
                wp_redirect( $current_url );
                exit();                
            }

            // 2024-5-13
            // Migrate the title and content for document post from job post if job_number==doc_number and update the meta "doc_id"=$job_id if meta "job_id"==$job_id
            // update the title="登錄" for each document post and add new action with the title="OK", meta "doc_id"=$doc_id, "next_job"=-1, "next_leadtime"=86400 if job_number!=doc_number
            // 1. It queries documents and iterates over each one.
            // 2. For each document, it queries for a job post with a matching job number.
            // 3. If a matching job post is found, it updates the document's title and content with the corresponding job's title and content.
            // 4. It then checks if there are any existing action posts related to the job. If there are, it updates their doc_id meta to match the job's ID.
            // 5.If no matching job post is found, it updates the document's title to "登錄" and creates a new action post with the title "OK" and specific meta values.
            if (isset($_GET['_document_job_migration'])) {
                $args = array(
                    'post_type'      => 'document',
                    'posts_per_page' => -1,
                );
                $query = new WP_Query($args);
            
                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        $doc_id = get_the_ID();
                        $doc_number = get_post_meta($doc_id, 'doc_number', true);
            
                        $job_args = array(
                            'post_type'      => 'job',
                            'posts_per_page' => 1,
                            'meta_query'     => array(
                                array(
                                    'key'     => 'job_number',
                                    'value'   => $doc_number,
                                    'compare' => '=',
                                ),
                            ),
                        );
                        $job_query = new WP_Query($job_args);
            
                        if ($job_query->have_posts()) {
                            while ($job_query->have_posts()) {
                                $job_query->the_post();
                                $job_id = get_the_ID();
                                $job_title = get_the_title();
                                $job_content = get_the_content();
                                $job_number = get_post_meta($job_id, 'job_number', true);
                                $department = get_post_meta($job_id, 'department', true);
                            }
                            // Restore global post data
                            wp_reset_postdata();
            
                            // Update document post with job title and content
                            $doc_post_args = array(
                                'ID'           => $doc_id,
                                'post_title'   => $job_title,
                                'post_content' => $job_content,
                            );
                            wp_update_post($doc_post_args);
                            update_post_meta($doc_id, 'job_number', $job_number);
                            update_post_meta($doc_id, 'department', $department);

                            $action_args = array(
                                'post_type'      => 'action',
                                'posts_per_page' => -1,
                                'meta_query'     => array(
                                    array(
                                        'key'     => 'job_id',
                                        'value'   => $job_id,
                                        'compare' => '=',
                                    ),
                                ),
                            );                        
                            $action_query = new WP_Query($action_args);
                            
                            if ($action_query->have_posts()) {
                                while ($action_query->have_posts()) {
                                    $action_query->the_post();
                                    update_post_meta(get_the_ID(), 'doc_id', $doc_id);
                                }                            
                                wp_reset_postdata();                            
                            }    

                        } else {
                            $doc_post_args = array(
                                'ID'         => $doc_id,
                                'post_title' => '登錄',
                            );
                            wp_update_post($doc_post_args);
            
                            // Set the meta values
                            $meta_values = array(
                                'doc_id'        => $doc_id,
                                'next_job'      => -1,
                                'next_leadtime' => 86400,
                            );
            
                            // Create the action post
                            $action_post = array(
                                'post_title'  => 'OK',
                                'post_content'  => 'Your post content goes here.',
                                'post_type'   => 'action', // Adjust the post type as needed
                                'post_status' => 'publish',
                                'meta_input'  => $meta_values,
                            );
                            // Insert the post into the database
                            $action_post_id = wp_insert_post($action_post);
                        }
                    }
                    // Reset post data
                    wp_reset_postdata();
                }

                // Get the current URL without any query parameters
                $current_url = remove_query_arg( array_keys( $_GET ) );
                // Redirect to the URL without any query parameters
                wp_redirect( $current_url );
                exit();

            }
            
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

        }
