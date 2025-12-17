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

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

require_once plugin_dir_path( __FILE__ ) . 'includes/GitHubUpdater.php';

/**
 * Main plugin class that registers and renders VDI footnotes.
 *
 * @since 1.0.0
 */
class VdiFootnotes {
	/**
	 * Singleton instance.
	 *
	 * @var VdiFootnotes
	 */
	private static $instance;

    /**
	 * Footnotes collected for the current request.
	 *
	 * @var array
	 */
	private $footnotes = array();

    /**
	 * Incremental counter for footnote references.
	 *
	 * @var int
	 */
	private $counter = 1;

    /**
	 * Plugin options pulled from the database.
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Retrieve the singleton instance.
	 *
	 * @return VdiFootnotes
	 */
	public static function getInstance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hook WordPress actions, filters, and shortcodes.
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueAssets' ) );
		add_action( 'admin_menu', array( $this, 'addSettingsPage' ) );
		add_action( 'admin_init', array( $this, 'registerSettings' ) );
		add_shortcode( 'footnote', array( $this, 'footnoteShortcode' ) );
		add_filter( 'the_content', array( $this, 'appendFootnotes' ) );
	}

	/**
	 * Load plugin text domain and cached options.
	 *
	 * @return void
	 */
	public function init() {
		load_plugin_textdomain( 'vdi-footnotes', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		$this->options = get_option( 'vdi_footnotes_options' );
	}

	/**
	 * Enqueue front-end assets and inject inline styles.
	 *
	 * @return void
	 */
	public function enqueueAssets() {
		wp_enqueue_style(
			'vdi-footnotes-style',
			plugins_url( 'assets/style.css', __FILE__ ),
			array(),
			'1.0.0'
		);

		wp_enqueue_script(
            'vdi-footnotes-script',
            plugins_url( 'assets/script.js', __FILE__ ),
            array(),
            '1.0.0',
            true
		);

		$inline_css = $this->generateInlineCss();
		wp_add_inline_style( 'vdi-footnotes-style', $inline_css );
	}

	/**
	 * Build inline CSS based on saved options.
	 *
	 * @return string
	 */
	private function generateInlineCss() {
		$css = '';

		// Font size
		if ( ! empty( $this->options['font_size'] ) ) {
			$size = absint( $this->options['font_size'] );
			$css .= ".footnote-ref { font-size: {$size}px; }";
		}

		// Text color
		if ( ! empty( $this->options['text_color'] ) ) {
			$color = sanitize_hex_color( $this->options['text_color'] );
			$css  .= ".footnote-ref, .footnote-ref a, .footnote-back { color: {$color}; }";
		}

		return $css;
	}

	/**
	 * Render a footnote reference via the [footnote] shortcode.
	 *
	 * @param array       $atts    Shortcode attributes.
	 * @param string|null $content Content wrapped by the shortcode.
	 * @return string
	 */
	public function footnoteShortcode( $atts, $content = null ) {
		$atts = shortcode_atts( array(), $atts, 'footnote' );

		if ( empty( $content ) ) {
			return '';
		}

		$note_id                     = $this->counter++;
		$this->footnotes[ $note_id ] = wp_kses_post( $content );

		$show_numbers = isset( $this->options['show_numbers'] ) ? $this->options['show_numbers'] : 1;
		$number_class = $show_numbers ? '' : ' hidden-number';

		return sprintf(
            '<sup class="footnote-ref%s"><a href="#fn%d" id="ref%d">%d</a></sup>',
            $number_class,
            $note_id,
            $note_id,
            $note_id
		);
	}

	/**
	 * Append formatted footnotes after the main content.
	 *
	 * @param string $content Current post content.
	 * @return string
	 */
	public function appendFootnotes( $content ) {
		if ( empty( $this->footnotes ) || ! is_main_query() || ! in_the_loop() ) {
			return $content; }

		if ( ! empty( $this->options['footnotes_title'] ) ) {
			$title = esc_html__( $this->options['footnotes_title'], 'vdi-footnotes' );
		} else {
			$title = __( 'Footnotes', 'vdi-footnotes' );
		}

		$footnotes_html  = '<div class="vdi-footnotes-container">';
		$footnotes_html .= '<h3>' . $title . '</h3>';
		$footnotes_html .= '<ol class="vdi-footnotes-list">';

		foreach ( $this->footnotes as $id => $note ) {
			$footnotes_html .= sprintf(
                '<li id="fn%d">%s <a href="#ref%d" class="footnote-back">↩</a></li>',
                $id,
                wp_kses_post( $note ), // Ensure HTML is preserved
                $id
			);
		}

		$footnotes_html .= '</ol></div>';

		// Reset footnotes
		$this->footnotes = array();
		$this->counter   = 1;

		return $content . $footnotes_html;
	}

	/**
	 * Register the settings page in the WordPress admin.
	 *
	 * @return void
	 */
	public function addSettingsPage() {
		add_options_page(
            __( 'VDI Footnotes Settings', 'vdi-footnotes' ),
            __( 'VDI Footnotes', 'vdi-footnotes' ),
            'manage_options',
            'vdi-footnotes',
            array( $this, 'renderSettingsPage' )
		);
	}

	/**
	 * Register plugin settings, sections, and fields.
	 *
	 * @return void
	 */
	public function registerSettings() {
		register_setting(
            'vdi_footnotes_options_group',
            'vdi_footnotes_options',
            array( $this, 'sanitizeOptions' )
		);

		add_settings_section(
            'vdi_footnotes_appearance',
            __( 'Appearance Settings', 'vdi-footnotes' ),
            null,
            'vdi-footnotes'
		);

		add_settings_field(
            'footnotes_title',
            __( 'Footnotes Section Title', 'vdi-footnotes' ),
            array( $this, 'renderFootnotesTitleField' ),
            'vdi-footnotes',
            'vdi_footnotes_appearance'
		);

		add_settings_field(
            'show_numbers',
            __( 'Show Footnote Numbers', 'vdi-footnotes' ),
            array( $this, 'renderShowNumbersField' ),
            'vdi-footnotes',
            'vdi_footnotes_appearance'
		);

		add_settings_field(
            'font_size',
            __( 'Font Size (px)', 'vdi-footnotes' ),
            array( $this, 'renderFontSizeField' ),
            'vdi-footnotes',
            'vdi_footnotes_appearance'
		);

		add_settings_field(
            'text_color',
            __( 'Text Color', 'vdi-footnotes' ),
            array( $this, 'renderTextColorField' ),
            'vdi-footnotes',
            'vdi_footnotes_appearance'
		);
	}

	/**
	 * Sanitize settings fields before saving.
	 *
	 * @param array $input Raw input from settings form.
	 * @return array
	 */
	public function sanitizeOptions( $input ) {
		$sanitized                    = array();
		$sanitized['footnotes_title'] = sanitize_text_field( $input['footnotes_title'] );
		$sanitized['show_numbers']    = isset( $input['show_numbers'] ) ? 1 : 0;
		$sanitized['font_size']       = absint( $input['font_size'] );
		$sanitized['text_color']      = sanitize_hex_color( $input['text_color'] );
		return $sanitized;
	}

	/**
	 * Render the plugin settings page.
	 *
	 * @return void
	 */
	public function renderSettingsPage() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'VDI Footnotes Settings', 'vdi-footnotes' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'vdi_footnotes_options_group' );
				do_settings_sections( 'vdi-footnotes' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render the footnotes title input field.
	 *
	 * @return void
	 */
	public function renderFootnotesTitleField() {
		$value = isset( $this->options['footnotes_title'] ) ? $this->options['footnotes_title'] : __( 'Footnotes', 'vdi-footnotes' );
		echo '<input type="text" name="vdi_footnotes_options[footnotes_title]" value="' . esc_attr( $value ) . '" placeholder="' . esc_attr__( 'Footnotes Section Title', 'vdi-footnotes' ) . '">';
	}

	/**
	 * Render the show numbers checkbox field.
	 *
	 * @return void
	 */
	public function renderShowNumbersField() {
		$checked = isset( $this->options['show_numbers'] ) ? checked( 1, $this->options['show_numbers'], false ) : '';
		echo wp_kses_post( '<input type="checkbox" name="vdi_footnotes_options[show_numbers]" value="1" ' . $checked . '>' );
	}

	/**
	 * Render the font size number field.
	 *
	 * @return void
	 */
	public function renderFontSizeField() {
		$value = isset( $this->options['font_size'] ) ? $this->options['font_size'] : '';
		echo '<input type="number" name="vdi_footnotes_options[font_size]" value="' . esc_attr( $value ) . '" min="10" max="24">';
	}

	/**
	 * Render the text color picker field.
	 *
	 * @return void
	 */
	public function renderTextColorField() {
		$value = isset( $this->options['text_color'] ) ? $this->options['text_color'] : '';
		echo '<input type="color" name="vdi_footnotes_options[text_color]" value="' . esc_attr( $value ) . '">';
	}

	// Template function for Twig/Blade compatibility
	/**
	 * Return the rendered footnotes markup.
	 *
	 * @return string
	 */
	public static function getFootnotesContent() {
		$instance = self::getInstance();
		return $instance->appendFootnotes( '' );
	}
}

// Initialize the plugin
VdiFootnotes::getInstance();

// Template tag shortcode for use in themes
if ( ! function_exists( 'vdiFootnotesDisplay' ) ) {
	/**
	 * Echo rendered footnotes for theme templates.
	 *
	 * @return void
	 */
    // phpcs:disable
	function vdiFootnotesDisplay() {
		echo VdiFootnotes::getFootnotesContent();
	}

	add_shortcode(
        'show_footnotes',
		function () {
			return VdiFootnotes::getFootnotesContent();
		}
    );
}
// phpcs:enable

$gitHubUpdater = new GitHubUpdater( plugin_dir_path( __FILE__ ) . 'vdi-footnotes.php' );
$gitHubUpdater->setChangelog( 'CHANGELOG.md' );
$gitHubUpdater->setPluginIcon( 'assets/icon-256x256.png' );
$gitHubUpdater->setPluginBannerSmall( 'assets/banner-sm.jpg' );
$gitHubUpdater->setPluginBannerLarge( 'assets/banner-lg.jpg' );
$gitHubUpdater->add();
