<?php
/**
 * @file
 * Integrate View Mode Page with the Features module
 */

/**
 * Implements hook_features_export_options().
 *
 * @return array
 */
function view_mode_page_pattern_features_export_options() {
  $options = array();
  
  $patterns = module_invoke('view_mode_page', 'get_patterns');
  foreach ($patterns as $pattern) {
    $name = $pattern->content_type . ':' . $pattern->view_mode;
    $options[$name] = $pattern->content_type . ': ' . $pattern->view_mode;
  }
  
  return $options;
}

/**
 * Implements hook_features_export().
 *
 * @param array $data An array of machine names for the component in question to be exported.
 * @param array &$export By reference. An array of all components to be exported with a given feature. Component objects that should be exported should be added to this array.
 * @param string $module_name The name of the feature module to be generated.
 *
 * @return array
 */
function view_mode_page_pattern_features_export($data, &$export, $module_name) {
  $pipe = array();
  $export['dependencies']['view_mode_page'] = 'view_mode_page';
  foreach ($data as $component) {
    $export['features']['view_mode_page_pattern'][$component] = $component;
    if (!isset($pipe['pattern'])) {
      $pipe['pattern'] = array();
    }
    $pipe['pattern'][] = $component;
  }
  return $pipe;
}

/**
 * Implements hook_features_export_render().
 *
 * @param string $module_name The name of the feature module to be exported.
 * @param array $data An array of machine name identifiers for the objects to be rendered.
 * @param array $export The full export array of the current feature being exported. This is only passed when hook_features_export_render() is invoked for an actual feature update or recreate, not during state checks or other operations.
 *
 * @return array
 */
function view_mode_page_pattern_features_export_render($module_name, $data, $export = NULL) {
  $code = array();
  $code[] = '  $vmp_config = array();';
  $code[] = '';
  foreach ($data as $sys_name) {
    list($content_type, $view_mode) = explode(':', $sys_name);
    $item = module_invoke('view_mode_page', 'get_patterns', $content_type, $view_mode);
    $code[] = '  $vmp_config[] = ' . features_var_export($item, '  ') . ';';
  }
  $code[] = '  return $vmp_config;';
  $code = implode("\n", $code);
  return array('view_mode_page_pattern_default' => $code);
}

/**
 * Implements hook_features_export_rebuild().
 *
 * @param string $module The name of the feature module whose components should be rebuilt.
 *
 * @return void
 */
function view_mode_page_pattern_features_rebuild($module) {
  $items = module_invoke($module, 'view_mode_page_pattern_default');
  foreach ($items as $item) {
    db_merge('view_mode_page')
      ->key(array(
        'content_type' => $item[0]['content_type'], 
        'view_mode' => $item[0]['view_mode'], 
        'url_pattern' => $item[0]['url_pattern'],
      ))
      ->execute();
  }
}

/**
 * Implements hook_features_revert().
 *
 * Calls rebuild
 *
 * @param string $module The name of the feature module whose components should be reverted.
 *
 * @return TRUE
 */
function view_mode_page_pattern_features_revert($module) {
  view_mode_page_pattern_features_rebuild($module);
  return TRUE;
}
