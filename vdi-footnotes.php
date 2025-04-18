<?php
/**
 * Plugin Name: VDI Footnotes
 * Description: Add clean, styled footnotes to your posts using [footnote "Your footnote here"].
 * Version: 1.0
 * Author: Keith Solomon
 * Author URI: https://www.vincentdesign.ca
 */

if (!defined('ABSPATH')) { exit; }

global $vdiFootnotes;

$vdiFootnotes = [];

// --------------------
// Shortcode
// --------------------
function vdiFootnoteShortcode($atts = [], $content = null) {
  global $vdiFootnotes;

  if ($content === null) { return ''; }

  $vdiFootnotes[] = $content;
  $number = count($vdiFootnotes);

  BasicWP\consoleLog('Footnote #' . $number . ': ' . $content);

  return '<sup class="vdi-footnote text-xs align-super ml-1" id="footnote-ref-' . $number . '">
    <a href="#footnote-' . $number . '" class="vdi-footnote-link text-secondary-600 hover:underline" data-footnote="' . esc_attr($content) . '" aria-describedby="footnote-' . $number . '">' . $number . '</a>
  </sup>';
}

add_shortcode('footnote', 'vdiFootnoteShortcode');

// --------------------
// Append Footnotes
// --------------------
function appendFootnotesToContent($content) {
  global $vdiFootnotes;

  if (is_singular() && !empty($vdiFootnotes)) {
    $heading = get_option('vdi_footnotes_heading', 'Footnotes');

    $footnoteHtml = '<section class="vdi-footnotes mt-12 border-t border-primary-300 pt-6 text-sm text-primary-800">';
    $footnoteHtml .= '<h2 class="text-lg font-semibold mb-4">' . esc_html($heading) . '</h2>';
    $footnoteHtml .= '<ol class="list-decimal pl-6 space-y-2">';

    foreach ($vdiFootnotes as $index => $note) {
      $num = $index + 1;
      $footnoteHtml .= '<li id="footnote-' . $num . '" class="leading-snug">' .
        esc_html($note) .
        ' <a href="#footnote-ref-' . $num . '" class="text-secondary-600 hover:underline ml-2" aria-label="Back to reference ' . $num . '">↩</a>' .
        '</li>';
    }

    $footnoteHtml .= '</ol></section>';
    $vdiFootnotes = []; // Clear for next run

    return $content . $footnoteHtml;
  }

  return $content;
}
add_filter('the_content', 'appendFootnotesToContent');

// --------------------
// Enqueue Script
// --------------------
function enqueueFootnoteScript() {
  wp_enqueue_script(
    'vdi-footnotes',
    plugins_url('js/footnotes.js', __FILE__),
    [],
    '1.2',
    true
  );

  wp_localize_script('vdi-footnotes', 'vdiFootnoteSettings', [
    'enableInlineMobile' => (bool) get_option('vdi_enable_inline_mobile'),
    'enableSmoothScroll' => (bool) get_option('vdi_enable_smooth_scroll'),
  ]);
}
add_action('wp_enqueue_scripts', 'enqueueFootnoteScript');

// --------------------
// Admin Menu & Settings
// --------------------
add_action('admin_menu', function () {
  add_options_page(
    'VDI Footnotes Settings',
    'VDI Footnotes',
    'manage_options',
    'vdi-footnotes',
    'renderSettingsPage'
  );
});

add_action('admin_init', function () {
  register_setting('vdi_settings_group', 'vdi_enable_inline_mobile');
  register_setting('vdi_settings_group', 'vdi_enable_smooth_scroll');
  register_setting('vdi_settings_group', 'vdi_footnotes_heading');

  add_settings_section('vdi_main_section', 'Footnote Settings', null, 'vdi-footnotes');

  add_settings_field('vdi_enable_inline_mobile', 'Show inline footnotes on mobile', function () {
    $val = get_option('vdi_enable_inline_mobile');
    echo '<input type="checkbox" name="vdi_enable_inline_mobile" value="1"' . checked(1, $val, false) . '> Enable';
  }, 'vdi-footnotes', 'ef_main_section');

  add_settings_field('vdi_enable_smooth_scroll', 'Enable smooth scrolling', function () {
    $val = get_option('vdi_enable_smooth_scroll');
    echo '<input type="checkbox" name="vdi_enable_smooth_scroll" value="1"' . checked(1, $val, false) . '> Enable';
  }, 'vdi-footnotes', 'vdi_main_section');

  add_settings_field('vdi_footnotes_heading', 'Footnotes Section Heading', function () {
    $val = get_option('vdi_footnotes_heading', 'Footnotes');
    echo '<input type="text" name="vdi_footnotes_heading" value="' . esc_attr($val) . '" class="regular-text">';
  }, 'vdi-footnotes', 'vdi_main_section');
});

function renderSettingsPage() {
  ?>
  <div class="wrap">
    <h1>VDI Footnotes Settings</h1>
    <form method="post" action="options.php">
      <?php
      settings_fields('vdi_settings_group');
      do_settings_sections('vdi-footnotes');
      submit_button();
      ?>
    </form>
  </div>
  <?php
}
