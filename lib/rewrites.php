<?php
/**
 * URL rewriting
 *
 * Official Roots offers only limited support for child themes (or network installs)
 * @todo https://github.com/retlehs/roots/issues/461
 *
 * This file extends that support with the following, important, consideration: all
 * stylesheets and scripts will be loaded from the child theme (or CDN) and not the
 * parent Roots theme. 
 *
 * Rewrite:
 *   /wp-content/themes/themename/assets/css/ to /assets/css/
 *   /wp-content/themes/themename/assets/js/  to /assets/js/
 *   /wp-content/themes/themename/assets/img/ to /assets/img/
 *   /wp-content/plugins/              to /plugins/
 *
 * If you aren't using Apache, alternate configuration settings can be found in the docs.
 * @link https://github.com/retlehs/roots/blob/master/doc/rewrites.md
 */

$get_child_name = explode('/themes/', get_stylesheet_directory());
define('CHILD_PATH', RELATIVE_CONTENT_PATH . '/themes/' . next($get_child_name));

function roots_add_rewrites($content) {
  global $wp_rewrite;
  $roots_new_non_wp_rules = array(
    'assets/css/(.*)'      => CHILD_PATH . '/assets/css/$1',
    'assets/js/(.*)'       => CHILD_PATH . '/assets/js/$1',
    'assets/img/(.*)'      => CHILD_PATH . '/assets/img/$1',
    'plugins/(.*)'         => RELATIVE_PLUGIN_PATH . '/$1'
  );
  $wp_rewrite->non_wp_rules = array_merge($wp_rewrite->non_wp_rules, $roots_new_non_wp_rules);
  return $content;
}

function roots_clean_urls($content) {
  if (strpos($content, FULL_RELATIVE_PLUGIN_PATH) === 0) {
    return str_replace(FULL_RELATIVE_PLUGIN_PATH, WP_BASE . '/plugins', $content);
  } else {
    return str_replace('/' . CHILD_PATH, '', $content);
  }
}

if (!is_multisite() && get_option('permalink_structure')) {
  if (current_theme_supports('rewrites')) {
    add_action('generate_rewrite_rules', 'roots_add_rewrites');
  }

  if (!is_admin() && current_theme_supports('rewrites')) {
    $tags = array(
      'plugins_url',
      'bloginfo',
      'stylesheet_directory_uri',
      'template_directory_uri',
      'script_loader_src',
      'style_loader_src'
    );

    add_filters($tags, 'roots_clean_urls');
  }
}
