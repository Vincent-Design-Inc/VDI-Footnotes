# VDI Simple Footnotes

VDI Simple Footnotes is a lightweight WordPress plugin that allows you to add footnotes to your posts and pages using a simple shortcode. It displays them inline and at the bottom of the content, with expand/collapse behavior inspired by [James R. Meyer’s method](https://www.jamesrmeyer.com/otherstuff/easy-footnotes-for-web-pages).

## 📦 Features

- Shortcode-based footnotes: `[footnote note="This is a footnote."]`
- Supports links and HTML inside notes.
- Auto-generated footnote container at the bottom of the page.
- Expand/collapse highlighting behavior for smooth navigation.
- Settings page to customize font size, color, and toggle footnote numbers.
- Compatible with themes using Twig or Blade (Sage).
- Works in all modern browsers and screen sizes.
- Clean and minimal markup, no front-end bloat.

## 🚀 Installation

1. Upload the plugin to your `wp-content/plugins/` directory or install via the Plugins screen in WordPress.
2. Activate the plugin from the Plugins menu.
3. Use the `[footnote note="..."]` shortcode in your posts and pages.
4. Go to **Settings → VDI Footnotes** to customize appearance options.

## 🧩 Usage

To add a footnote:

```wordpress
This is a sentence with a footnote.[footnote note="This is the actual footnote content."]
```

Footnotes will appear at the end of the post/page in an ordered list, with clickable links and smooth scroll-to behavior.
Optional Settings

You can adjust the following options in the WordPress admin under Settings → VDI Footnotes:

- **Font Size:** Custom font size for the footnote list.
- **Font Color:** Custom color for footnote text.
- **Show Numbers in Content:** Toggle whether the superscript number or an asterisk appears in the main text.

## 📁 File Structure

```plaintext
vdi-simple-footnotes/
├── assets/
│   ├── style.css
│   └── script.js
├── vdi-simple-footnotes.php
└── README.md
```

## 🧪 Compatibility & Testing

- Tested with WordPress 6.x and PHP 7.4+.
- Works with classic themes and block-based themes.
- Compatible with Twig and Blade (Sage) themes that properly use `the_content()` via `ob_get_clean()`.
- Safe with most common plugins – avoids global conflicts.

## 🛠 Developer Notes

- All plugin logic is encapsulated in a singleton class.
- Uses core WordPress APIs and follows best practices.
- Uses `wp_enqueue_style()` and `wp_enqueue_script()` to avoid redundant asset loading.
- `wp_kses_post()` and `esc_attr()` are used for safe output handling.

## 📜 License

This plugin is open source and licensed under the GPLv2 or later.

Made with ❤️ by the VDI Dev Team
