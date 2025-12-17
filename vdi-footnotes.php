<?php
  /*
  Plugin Name: VDI Simple Footnotes
  Description: A lightweight footnote system with shortcode support and customizable appearance.
  Version: 1.0.0
  Plugin URI: https://github.com/Vincent-Design-Inc/VDI-Footnotes
  Update URI: https://github.com/Vincent-Design-Inc/VDI-Footnotes
  Tested up to:
  Author: Keith Solomon
  Author URI: https://vincentdesign.ca
  Text Domain: vdi-footnotes
  */

  namespace VDIFootnotes;

  class VDISimpleFootnotes {
  private $footnotes = [];
  private $footnoteCount = 1;
  private $listRendered = false;

  public function __construct() {
    add_shortcode('efn_note', [$this, 'renderFootnote']);
    add_shortcode('efn_list', function() {
      $this->listRendered = true;
      return $this->getFootnotesHtml();
    });

    add_filter('the_content', [$this, 'resetFootnoteCount'], 5);
    add_filter('the_content', [$this, 'appendFootnotes'], 99);
    add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
  }

  public function resetFootnoteCount($content) {
    $this->footnotes = [];
    $this->footnoteCount = 1;
    $this->listRendered = false;
    return $content;
  }

  public function renderFootnote($atts, $content = '') {
    $atts = $atts ?: [];
    $content = trim($content);

    $refId = 'fnref-' . $this->footnoteCount;
    $noteId = 'fn' . $this->footnoteCount;

    $this->footnotes[] = [
      'note' => $content,
      'refId' => $refId,
      'noteId' => $noteId
    ];

    $output = '<sup class="footnote-ref" id="' . esc_attr($refId) . '" data-note="' . esc_attr($content) . '">';
    $output .= '<a href="#' . esc_attr($noteId) . '" aria-describedby="footnote-label" tabindex="0" role="button" aria-haspopup="dialog">' . esc_html($this->footnoteCount) . '</a>';
    $output .= '</sup>';

    $this->footnoteCount++;

    return $output;
  }

  public function appendFootnotes($content) {
    if (empty($this->footnotes) || $this->listRendered) { return $content; }
    return $content . $this->getFootnotesHtml();
  }

  public function getFootnotesHtml() {
    if (empty($this->footnotes)) { return ''; }

    $output = '<div class="vdi-footnotes-container" role="doc-endnotes">';
    $output .= '<strong id="footnote-label" class="screen-reader-text">Footnotes</strong>';
    $output .= '<ol class="vdi-footnotes-list">';

    foreach ($this->footnotes as $index => $fn) {
      $output .= '<li id="' . esc_attr($fn['noteId']) . '">';
      $output .= wp_kses_post($fn['note']);
      $output .= ' <a href="#' . esc_attr($fn['refId']) . '" class="footnote-back" aria-label="Back to footnote ' . ($index + 1) . '">&#8617;</a>';
      $output .= '</li>';
    }

    $output .= '</ol></div>';

    return $output;
  }

  public function enqueueAssets() {
    wp_enqueue_style('vdi-footnotes-style', plugin_dir_url(__FILE__) . 'assets/style.css');
    wp_enqueue_script('vdi-footnotes-script', plugin_dir_url(__FILE__) . 'assets/script.js', [], false, true);
  }
}

$GLOBALS['vdi_simple_footnotes'] = new VDISimpleFootnotes();

function vdiOutputFootnotes() {
  if (!empty($GLOBALS['vdi_simple_footnotes'])) {
    $GLOBALS['vdi_simple_footnotes']->listRendered = true;
    echo $GLOBALS['vdi_simple_footnotes']->getFootnotesHtml();
  }
}
