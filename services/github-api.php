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
                
    }
    $github_api = new github_api();
}
