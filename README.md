=== Yahoo Finance News Feed ===
Contributors: Ali Raza
Tags: yahoo finance, news, stock market, rss, accordion, elementor, widget, financial news, stock news
Requires at least: 5.8
Tested up to: 6.4
Stable tag: 1.0.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display dynamic Yahoo Finance news feeds with beautiful accordion layouts, pagination, and full Elementor integration.

== Description ==

A powerful WordPress plugin that displays Yahoo Finance news feeds with a modern, customizable accordion interface. Perfect for financial websites, stock market blogs, and investment platforms.

**Key Features:**
- 📰 Real-time Yahoo Finance RSS integration
- 🎨 4 beautiful design styles (Default, Dark, Minimal, Modern)
- 🔧 Full Elementor widget support with live preview
- 📱 Fully responsive & mobile-friendly design
- ⚡ AJAX pagination & load more functionality
- 🔍 Built-in search functionality
- ♿ WCAG 2.1 accessibility compliant
- 📊 Multiple display modes (Full content/Excerpt)
- 🔄 Smart caching system (15-minute default)
- 🌐 REST API endpoints for developers
- 🎯 Shortcode support with extensive parameters

**Premium Features Included:**
- Alpha Vantage API fallback support
- Custom WordPress widget
- Advanced styling controls
- Auto-refresh capability
- Keyboard navigation support
- Print-friendly styles
- RTL language support

== Installation ==

1. Upload the `yahoo-finance-news-feed` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure your settings at Settings > Yahoo Finance News
4. Add news feed using shortcodes or Elementor widget

== Configuration ==

1. **Set Default Symbol**: Go to Settings > Yahoo Finance News and set your default stock symbol (e.g., AAPL, GOOGL, MSFT)
2. **Adjust Display Settings**: Configure items per page, cache duration, and default style
3. **API Key (Optional)**: Add your Alpha Vantage API key for backup news source (get free key from alphavantage.co)

== Frequently Asked Questions ==

= What stock symbols can I use? =
You can use any valid stock symbol accepted by Yahoo Finance (e.g., AAPL, GOOGL, MSFT, TSLA, AMZN).

= Why is my news feed not displaying? =
1. Check if the stock symbol is valid
2. Ensure your server can connect to Yahoo Finance RSS feeds
3. Check for any firewall restrictions on your server

= How can I get more reliable news data? =
Register for a free API key at Alpha Vantage and add it in the plugin settings for backup news source.

= Can I use multiple news feeds on the same page? =
Yes! You can use multiple shortcodes with different symbols on the same page.

= How do I customize the appearance? =
You can choose from 4 built-in styles or add custom CSS to your theme.

== Screenshots ==
1. News feed accordion in default style
2. Dark theme variant with expanded article
3. Plugin settings page with configuration options
4. Elementor widget live preview
5. Mobile responsive layout
6. Pagination and search functionality

== Changelog ==

= 1.0.1 =
* Fixed: Pagination AJAX handling
* Fixed: Full content display restrictions
* Improved: RSS feed reliability
* Added: Enhanced error handling
* Added: Better mobile responsiveness

= 1.0.0 =
* Initial release
* Yahoo Finance RSS integration
* Elementor widget support
* Four design styles
* Shortcode implementation
* Admin settings panel

== Upgrade Notice ==

= 1.0.1 =
This update fixes critical pagination issues and ensures full content display from RSS feeds. Recommended for all users.

== Usage ==

**Shortcode:**
`[yahoo_news_feed symbol="AAPL" per_page="10" page="1" style="dark" show_read_more="yes" content_mode="full"]`

**PHP Function:**
`echo do_shortcode('[yahoo_news_feed symbol="AAPL"]');`

**Elementor Widget:**
1. Edit page with Elementor
2. Search for "Yahoo Finance News"
3. Drag widget to desired location
4. Configure symbol and display settings

== Parameters ==

* `symbol` - Stock symbol (default: from settings)
* `per_page` - Items per page (default: 20)
* `page` - Starting page number (default: 1)
* `style` - Design style: default, dark, minimal, modern (default: from settings)
* `show_read_more` - Show read more button: yes/no (default: from settings)
* `content_mode` - Content display: full/excerpt (default: from settings)

== Advanced Customization ==

**Add Custom CSS:**
```css
.yfnf-container {
    max-width: 1200px;
    margin: 0 auto;
}
.yfnf-accordion-title {
    font-family: 'Your Font', sans-serif;
}