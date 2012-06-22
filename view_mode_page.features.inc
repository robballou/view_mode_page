<?php
/**
 * Implements hook_hook_function
 */
function view_mode_page_pattern_features_export_options() {
  $options = array();
  
  $patterns = module_invoke('view_mode_page', 'get_patterns');
  foreach ($patterns as $pattern) {
    $name = $pattern->content_type .'='. $pattern->view_mode;
    $options[$name] = $pattern->content_type .': '. $pattern->view_mode;
  }
  
  return $options;
}

/**
 * Implements hook_
 */
function view_mode_page_pattern_features_export($data, &$export, $module_name) {
  $export['dependencies']['view_mode_page'] = 'view_mode_page';
  foreach ($data as $component) {
    $export['features']['mymodule_config'][$component] = $component;
  }
  return array();
}