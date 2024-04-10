<?php
/******************
* Paperless NGX Data Collection
* ------------------------------
* This function collects data from Paperless NGX, a document management system, for dashboard display.
* It fetches information about the documents, including their total count, document types, and tags.
*
* Collected Data:
* - Total number of documents
* - Number of documents by type (e.g., invoice, receipt, contract)
* - Number of documents by tag (e.g., work, personal, finance)
*
* Data not collected but available for extension:
* - Detailed document information (title, date, correspondent, content)
* - Document file metadata (file type, size, checksum)
* - Document notes and comments
* - User activity and audit logs
* - Storage usage and quota information
* - Paperless NGX configuration and settings
*
* Opportunities for additional data collection:
* - Document processing status (e.g., pending, processed, failed)
* - OCR accuracy and confidence scores
* - Document search and retrieval metrics
* - User collaboration and sharing data
* - Integration with external services (e.g., cloud storage, email)
*
* Requirements:
* - Paperless NGX API should be accessible via the provided API URL.
* - API authentication using either an API key or username and password.
*
* Parameters:
* - $api_url: The base URL of the Paperless NGX API.
* - $api_key: The API key for authentication (if using API key authentication).
* - $username: The username for authentication (if using username/password authentication).
* - $password: The password for authentication (if using username/password authentication).
* - $service_id: The ID of the service being monitored.
*
* Error Handling:
* - Captures any errors encountered during the API request process.
* - Stores error messages and timestamps for troubleshooting.
*
* Example of fetched_data structure:
* {
*   "total_documents": 100,
*   "document_types": {
*     "invoice": 40,
*     "receipt": 30,
*     "contract": 20,
*     "other": 10
*   },
*   "document_tags": {
*     "work": 60,
*     "personal": 25,
*     "finance": 15
*   }
* }
*******************/
function homelab_fetch_paperless_data($api_url, $api_key = '', $username = '', $password = '', $service_id) {
    $api_url = rtrim($api_url, '/');
    $endpoints = array(
      'documents' => '/api/documents/',
      'document_types' => '/api/document_types/',
      'tags' => '/api/tags/',
    );
    
    $fetched_data = array();
    $error_message = null;
    $error_timestamp = null;
  
    // Set up authentication headers
    $headers = array(
      'Content-Type' => 'application/json',
    );
    if (!empty($api_key)) {
      $headers['Authorization'] = 'Token ' . $api_key;
    } elseif (!empty($username) && !empty($password)) {
      $headers['Authorization'] = 'Basic ' . base64_encode($username . ':' . $password);
    }
  
    foreach ($endpoints as $key => $endpoint) {
      $url = $api_url . $endpoint;
      $args = array(
        'headers' => $headers,
      );
  
      $response = wp_remote_get($url, $args);
      if (is_wp_error($response)) {
        $error_message = "API request failed for endpoint '{$key}': " . $response->get_error_message();
        $error_timestamp = current_time('mysql');
        continue;
      }
  
      $data = json_decode(wp_remote_retrieve_body($response), true);
  
      if ($key === 'documents') {
        $fetched_data['total_documents'] = count($data['results']);
      } elseif ($key === 'document_types') {
        $document_types = array();
        foreach ($data['results'] as $document_type) {
          $document_types[$document_type['name']] = $document_type['document_count'];
        }
        $fetched_data['document_types'] = $document_types;
      } elseif ($key === 'tags') {
        $document_tags = array();
        foreach ($data['results'] as $tag) {
          $document_tags[$tag['name']] = $tag['document_count'];
        }
        $fetched_data['document_tags'] = $document_tags;
      }
    }
  
    homelab_save_service_data($service_id, $fetched_data, $error_message, $error_timestamp);
    return $fetched_data;
  }