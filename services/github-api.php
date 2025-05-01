<?php
/**
 * Copyright 2022 dgc.network
 *
 * dgc.network licenses this file to you under the Apache License,
 * version 2.0 (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at:
 *
 *   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if (!class_exists('github_api')) {
    class github_api {

        private $github_api_token;
    
        public function __construct() {
            add_action('admin_init', array( $this, 'github_api_register_settings' ) );
            $this->github_api_token = get_option('github_api_token');
        }
    
        function github_api_register_settings() {
            // Register GitHub API section
            add_settings_section(
                'github-api-section-settings',
                'Github API Settings',
                array( $this, 'github_api_section_settings_callback' ),
                'web-service-settings'
            );
        
            // Register fields for GitHub API section
            add_settings_field(
                'github_api_token',
                'GitHub API Token',
                array( $this, 'github_api_token_callback' ),
                'web-service-settings',
                'github-api-section-settings'
            );
            register_setting('web-service-settings', 'github_api_token');
        }
        
        function github_api_section_settings_callback() {
            echo '<p>Settings for GitHub API.</p>';
        }
        
        function github_api_token_callback() {
            $value = get_option('github_api_token');
            echo '<input type="text" id="github_api_token" name="github_api_token" style="width:100%;" value="' . esc_attr($value) . '" />';
        }

        // Fetch GitHub document
        function fetch_github_doc($doc_id) {
            $owner = 'iso-helper';
            $repo = 'docs-repo';
            $path = 'docs/' . $doc_id . '.html';
            $token = $this->github_api_token;
        
            $url = "https://api.github.com/repos/$owner/$repo/contents/$path";
            error_log("GitHub fetch URL: $url"); // Debug log
        
            $response = wp_remote_get($url, [
                'headers' => [
                    'User-Agent' => 'WP-GitHub',
                    'Authorization' => "token $token"
                ]
            ]);
        
            $code = wp_remote_retrieve_response_code($response);
            if ($code !== 200) {
                error_log("GitHub fetch failed: HTTP $code - " . print_r(json_decode(wp_remote_retrieve_body($response), true), true));
                return false;
            }
        
            $body = json_decode(wp_remote_retrieve_body($response), true);
            if (!isset($body['content'])) {
                error_log("GitHub fetch error: 'content' key missing. Body: " . print_r($body, true));
                return false;
            }
        
            return base64_decode($body['content']);
        }
/*        
        function fetch_github_doc($doc_id) {
            $owner = 'iso-helper';
            $repo = 'docs-repo';
            $path = 'docs/' . $doc_id . '.html';
            $token = $this->github_api_token;
        
            $url = "https://api.github.com/repos/$owner/$repo/contents/$path";
            $response = wp_remote_get($url, [
                'headers' => [
                    'User-Agent' => 'WP-GitHub',
                    'Authorization' => "token $token"
                ]
            ]);
        
            if (is_wp_error($response)) {
                error_log("GitHub fetch error: " . $response->get_error_message());
                return null;
            }
        
            $code = wp_remote_retrieve_response_code($response);
            $body = json_decode(wp_remote_retrieve_body($response), true);
        
            if ($code === 200 && isset($body['content'])) {
                return base64_decode($body['content']);
            } else {
                error_log("GitHub fetch failed: HTTP $code - " . print_r($body, true));
                return null;
            }
        }
/*        
        function fetch_github_doc($doc_id) {
            $owner = 'iso-helper';
            $repo = 'docs-repo';
            //$path = 'docs/'.$doc_id.'.md';
            $path = 'docs/'.$doc_id.'.html';
            $token = $this->github_api_token;

            $url = "https://api.github.com/repos/$owner/$repo/contents/$path";
            $response = wp_remote_get($url, [
                'headers' => [
                    'User-Agent' => 'WP-GitHub',
                    'Authorization' => "token $token"
                ]
            ]);
            $body = json_decode(wp_remote_retrieve_body($response), true);
            return base64_decode($body['content']);
        }
*/
        function update_github_doc($new_content, $doc_id) {
            $owner = 'iso-helper';
            $repo = 'docs-repo';
            $path = 'docs/' . $doc_id . '.html';
            $token = $this->github_api_token;
        
            $get_url = "https://api.github.com/repos/$owner/$repo/contents/$path";
            $headers = [
                'User-Agent' => 'WP-GitHub',
                'Authorization' => "token $token"
            ];
        
            // Try to get current file SHA
            $get_response = wp_remote_get($get_url, ['headers' => $headers]);
            $response_data = json_decode(wp_remote_retrieve_body($get_response), true);
        
            // Check for existing SHA (file exists)
            $is_existing_file = isset($response_data['sha']);
            $sha = $is_existing_file ? $response_data['sha'] : null;
        
            // Prepare data for PUT request
            $data = [
                'message' => $is_existing_file ? 'Update from WordPress' : 'Create new file from WordPress',
                'content' => base64_encode($new_content),
            ];
            if ($sha) {
                $data['sha'] = $sha; // Only include SHA if updating
            }
        
            // PUT request to create/update the file
            $put_response = wp_remote_request($get_url, [
                'method' => 'PUT',
                'headers' => array_merge($headers, ['Content-Type' => 'application/json']),
                'body' => json_encode($data)
            ]);
            
            if (is_wp_error($put_response)) {
                error_log('GitHub PUT Error: ' . $put_response->get_error_message());
                return false;
            }
            
            $response_code = wp_remote_retrieve_response_code($put_response);
            $response_body = wp_remote_retrieve_body($put_response);
            
            if ($response_code >= 200 && $response_code < 300) {
                return true;
            } else {
                error_log("GitHub PUT Failed: $response_code - $response_body");
                return false;
            }
        }

        function get_github_file_revision($doc_id) {
            $owner = 'iso-helper';
            $repo = 'docs-repo';
            //$path = 'docs/'.$doc_id.'.md';
            $path = 'docs/'.$doc_id.'.html';
            $branch = 'main';
            $token = $this->github_api_token;
        
            $url = "https://api.github.com/repos/$owner/$repo/commits?path=$path&sha=$branch&per_page=1";
        
            $args = [
                'headers' => [
                    'Accept' => 'application/vnd.github+json',
                    'User-Agent' => 'WordPress-GitHub-Integration',
                ]
            ];
        
            if (!empty($token)) {
                $args['headers']['Authorization'] = 'token ' . $token;
            }
        
            $response = wp_remote_get($url, $args);
        
            if (is_wp_error($response)) {
                return false;
            }
        
            $body = json_decode(wp_remote_retrieve_body($response), true);
        
            if (!empty($body[0]['sha'])) {
                return $body[0]['sha']; // Latest commit SHA
            }
        
            return false;
        }
        
    }
    $github_api = new github_api();
}
