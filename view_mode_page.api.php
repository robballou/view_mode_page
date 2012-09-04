<?php

function hook_view_mode_page_get_patterns($results, $content_type, $view_mode) {
  return $results;
}

function hook_view_mode_page_pre_view($node, $content_type, $view_mode, $pattern) {
  return $node;
}

function hook_view_mode_page_post_view($node, $view, $content_type, $view_mode, $pattern) {
  return $view;
}

function hook_view_mode_page_pattern_added($content_type, $view_mode, $pattern, $result) {
  return;
}

function hook_view_mode_page_patterns_deleted($content_type = NULL, $view_mode = NULL, $pattern = NULL) {
  return;
}