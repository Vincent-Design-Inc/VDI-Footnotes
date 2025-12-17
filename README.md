# VDI Simple Footnotes

VDI Simple Footnotes is a lightweight WordPress plugin that allows you to add footnotes to your posts and pages using a simple shortcode. It displays them inline and at the bottom of the content, with expand/collapse behavior for better readability.

## 📦 Features

- **Shortcode-based footnotes:** Use `[efn_note]` to add footnotes inline with your content.
- **Customizable appearance:** Adjust font size, text color, and toggle footnote numbers via the settings page.
- **Auto-generated footnote container:** Automatically appends an ordered list of footnotes at the bottom of the post or page.
- **Expand/collapse tooltips:** Footnotes are displayed in tooltips when clicked, with smooth fade-in/out animations.
- **Settings page:** Easily configure appearance options in the WordPress admin under **Settings → VDI Footnotes**.
- **Twig/Blade compatibility:** Works seamlessly with themes using Twig or Blade templating engines.
- **Minimal front-end footprint:** Clean and lightweight markup with no unnecessary bloat.
- **Safe and secure:** Uses WordPress APIs for sanitization and escaping to ensure secure output.

## 🚀 Installation

1. Upload the plugin to your `wp-content/plugins/` directory or install via the Plugins screen in WordPress.
2. Activate the plugin from the Plugins menu.
3. Go to **Settings → VDI Footnotes** to customize appearance options.

## 🧩 Usage

### Adding a Footnote

To add a footnote, use the `[efn_note]` shortcode in your content:

```wordpress
This is a sentence with a footnote.[efn_note]This is the actual footnote content.[/efn_note]
```

Footnotes will appear as superscript numbers in the content and as an ordered list at the bottom of the post/page. Clicking on a footnote number will display the content in a tooltip.

Add the `[show_footnotes]` shortcode to your content where you want the footnotes to appear, generally at the bottom of the page.

### Optional Settings

You can adjust the following options in the WordPress admin under **Settings → VDI Footnotes**:

- **Footnotes Section Title:** Customize the title of the footnotes section displayed at the bottom of the page.
- **Show Numbers in Content:** Toggle whether superscript numbers or an asterisk appears in the main text.
- **Font Size:** Set a custom font size (in pixels) for the footnote list.
- **Text Color:** Choose a custom color for footnote text.

## 📁 File Structure

```plaintext
vdi-simple-footnotes/
├── assets/
│   ├── banner-lg.jpg     # Large banner for Github updater script
│   ├── banner-sm.jpg     # Small banner for Github updater script
│   ├── icon-256x256.png  # Icon for Github updater script
│   ├── script.js         # Styles for footnotes and tooltips
│   └── style.css         # JavaScript for tooltip functionality
├── includes/
│   └── GithubUpdater.php # Class to support updating the plugin from a Github repo
├── CHANGELOG.md          # Plugin changelog
├── README.md             # Plugin documentation
└── vdi-footnotes.php     # Main plugin file
```

## 🧪 Compatibility & Testing

- Tested with WordPress 6.x and PHP 8.3.
- Works with both classic and block-based themes.
- Compatible with Twig and Blade (Sage) themes that properly use `the_content()` via `ob_get_clean()`.
- Safe to use with most common plugins – avoids global conflicts.

## 🛠 Developer Notes

- **Customizable Inline Styles:** Dynamically generates inline CSS based on user settings for font size and text color.
- **Shortcode and Template Tag Support:** Use `[footnote]` for inline footnotes and the `[show_footnotes]` shortcode to display footnotes in templates.
- **Secure Output:** Uses `wp_kses_post()` and `esc_attr()` for sanitization and escaping.
- **Expandable Tooltips:** JavaScript-powered tooltips provide a smooth user experience.

## 📜 License

This plugin is open source and licensed under the GPLv2 or later.

Made with ❤️ by the VDI Dev Team
