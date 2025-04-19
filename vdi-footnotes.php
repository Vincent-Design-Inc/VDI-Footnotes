<?php
/*
Plugin Name: VDI Simple Footnotes
Description: A lightweight footnote system with shortcode support and customizable appearance.
Version: 1.0.0
Author: Keith Solomon
Author URI: https://vincentdesign.ca
Text Domain: vdi-footnotes
Domain Path: /languages
*/

if (!defined('ABSPATH')) { exit; }

class VdiFootnotes {
  private static $instance;
  private $footnotes = array();
  private $counter = 1;
  private $options;

  public static function getInstance() {
    if (!self::$instance) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  private function __construct() {
    add_action('init', array($this, 'init'));
    add_action('wp_enqueue_scripts', array($this, 'enqueueAssets'));
    add_action('admin_menu', array($this, 'addSettingsPage'));
    add_action('admin_init', array($this, 'registerSettings'));
    add_shortcode('footnote', array($this, 'footnoteShortcode'));
    add_filter('the_content', array($this, 'appendFootnotes'));
  }

  public function init() {
    load_plugin_textdomain('vdi-footnotes', false, dirname(plugin_basename(__FILE__)) . '/languages');
    $this->options = get_option('vdi_footnotes_options');
  }

  public function enqueueAssets() {
    wp_enqueue_style(
      'vdi-footnotes-style',
      plugins_url('assets/style.css', __FILE__),
      array(),
      '1.0.0'
    );

    wp_enqueue_script(
      'vdi-footnotes-script',
      plugins_url('assets/script.js', __FILE__),
      array(),
      '1.0.0',
      true
    );

    $inline_css = $this->generateInlineCss();
    wp_add_inline_style('vdi-footnotes-style', $inline_css);
  }

  private function generateInlineCss() {
    $css = '';

    // Font size
    if (!empty($this->options['font_size'])) {
      $size = absint($this->options['font_size']);
      $css .= ".footnote-ref { font-size: {$size}px; }";
    }

    // Text color
    if (!empty($this->options['text_color'])) {
      $color = sanitize_hex_color($this->options['text_color']);
      $css .= ".footnote-ref, .footnote-ref a, .footnote-back { color: {$color}; }";
    }

    return $css;
  }

  public function footnoteShortcode($atts, $content = null) {
    $atts = shortcode_atts(array(), $atts, 'footnote');

    if (empty($content)) {
        return '';
    }

    $note_id = $this->counter++;
    $this->footnotes[$note_id] = wp_kses_post($content);

    $show_numbers = isset($this->options['show_numbers']) ? $this->options['show_numbers'] : 1;
    $number_class = $show_numbers ? '' : ' hidden-number';

    return sprintf(
        '<sup class="footnote-ref%s"><a href="#fn%d" id="ref%d">%d</a></sup>',
        $number_class,
        $note_id,
        $note_id,
        $note_id
    );
  }

  public function appendFootnotes($content) {
    if (empty($this->footnotes) || !is_main_query() || !in_the_loop()) {
        return $content;
    }

    $footnotes_html = '<div class="vdi-footnotes-container">';
    $footnotes_html .= '<h3>' . esc_html__('Footnotes', 'vdi-footnotes') . '</h3>';
    $footnotes_html .= '<ol class="vdi-footnotes-list">';

    foreach ($this->footnotes as $id => $note) {
      $footnotes_html .= sprintf(
        '<li id="fn%d">%s <a href="#ref%d" class="footnote-back">↩</a></li>',
        $id,
        wp_kses_post($note), // Ensure HTML is preserved
        $id
      );
    }

    $footnotes_html .= '</ol></div>';

    // Reset footnotes
    $this->footnotes = array();
    $this->counter = 1;

    return $content . $footnotes_html;
  }

  public function addSettingsPage() {
    add_options_page(
      __('Easy Footnotes Settings', 'vdi-footnotes'),
      __('Easy Footnotes', 'vdi-footnotes'),
      'manage_options',
      'vdi-footnotes',
      array($this, 'renderSettingsPage')
    );
  }

  public function registerSettings() {
    register_setting(
      'vdi_footnotes_options_group',
      'vdi_footnotes_options',
      array($this, 'sanitizeOptions')
    );

    add_settings_section(
      'vdi_footnotes_appearance',
      __('Appearance Settings', 'vdi-footnotes'),
      null,
      'vdi-footnotes'
    );

    add_settings_field(
      'show_numbers',
      __('Show Footnote Numbers', 'vdi-footnotes'),
      array($this, 'renderShowNumbersField'),
      'vdi-footnotes',
      'vdi_footnotes_appearance'
    );

    add_settings_field(
      'font_size',
      __('Font Size (px)', 'vdi-footnotes'),
      array($this, 'renderFontSizeField'),
      'vdi-footnotes',
      'vdi_footnotes_appearance'
    );

    add_settings_field(
      'text_color',
      __('Text Color', 'vdi-footnotes'),
      array($this, 'renderTextColorField'),
      'vdi-footnotes',
      'vdi_footnotes_appearance'
    );
  }

  public function sanitizeOptions($input) {
    $sanitized = array();
    $sanitized['show_numbers'] = isset($input['show_numbers']) ? 1 : 0;
    $sanitized['font_size'] = absint($input['font_size']);
    $sanitized['text_color'] = sanitize_hex_color($input['text_color']);
    return $sanitized;
  }

  public function renderSettingsPage() {
    ?>
    <div class="wrap">
      <h1><?php esc_html_e('Easy Footnotes Settings', 'vdi-footnotes'); ?></h1>
      <form method="post" action="options.php">
        <?php
        settings_fields('vdi_footnotes_options_group');
        do_settings_sections('vdi-footnotes');
        submit_button();
        ?>
      </form>
    </div>
    <?php
  }

  public function renderShowNumbersField() {
    $checked = isset($this->options['show_numbers']) ? checked(1, $this->options['show_numbers'], false) : '';
    echo '<input type="checkbox" name="vdi_footnotes_options[show_numbers]" value="1" ' . $checked . '>';
  }

  public function renderFontSizeField() {
    $value = isset($this->options['font_size']) ? $this->options['font_size'] : '';
    echo '<input type="number" name="vdi_footnotes_options[font_size]" value="' . esc_attr($value) . '" min="10" max="24">';
  }

  public function renderTextColorField() {
    $value = isset($this->options['text_color']) ? $this->options['text_color'] : '';
    echo '<input type="color" name="vdi_footnotes_options[text_color]" value="' . esc_attr($value) . '">';
  }

  // Template function for Twig/Blade compatibility
  public static function getFootnotesContent() {
    $instance = self::getInstance();
    return $instance->appendFootnotes('');
  }
}

// Initialize the plugin
VdiFootnotes::getInstance();

// Template tag for use in themes
if (!function_exists('vdiFootnotesDisplay')) {
  function vdiFootnotesDisplay() {
    echo VdiFootnotes::getFootnotesContent();
  }
}
