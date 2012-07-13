<?php
/**
 * @file
 * Integrate View Mode Page with the Features module
 */

/**
 * Implements hook_features_export_options().
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
  menu_rebuild();
}

/**
 * Implements hook_features_revert().
 */
function view_mode_page_pattern_features_revert($module) {
  view_mode_page_pattern_features_rebuild($module);
  return TRUE;
}
