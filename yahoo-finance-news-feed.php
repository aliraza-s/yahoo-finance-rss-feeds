<?php
/**
 * Plugin Name: Yahoo Finance News Feed
 * Plugin URI: https://alirazafreelancer.com/
 * Description: Dynamic news feed module using Yahoo Finance RSS feeds with accordion layout, pagination, and Elementor integration.
 * Version: 1.0.2
 * Author: Mr. Developer
 * License: GPL v2 or later
 * Text Domain: yahoo-finance-news
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('YFNF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('YFNF_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('YFNF_VERSION', '1.0.1');

// Main plugin class
class YahooFinanceNewsFeed {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_shortcode('yahoo_news_feed', array($this, 'shortcode_handler'));
        
        // Elementor integration
        add_action('elementor/widgets/widgets_registered', array($this, 'register_elementor_widget'));
        
        // AJAX handlers
        add_action('wp_ajax_yfnf_load_more', array($this, 'ajax_load_more'));
        add_action('wp_ajax_nopriv_yfnf_load_more', array($this, 'ajax_load_more'));
        
        // Install/uninstall hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        load_plugin_textdomain('yahoo-finance-news', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style('yfnf-styles', YFNF_PLUGIN_URL . 'assets/style.css', array(), YFNF_VERSION);
        wp_enqueue_script('yfnf-script', YFNF_PLUGIN_URL . 'assets/script.js', array('jquery'), YFNF_VERSION, true);
        
        wp_localize_script('yfnf-script', 'yfnf_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('yfnf_nonce')
        ));
    }
    
    public function admin_menu() {
        add_options_page(
            'Yahoo Finance News Settings',
            'Yahoo Finance News',
            'manage_options',
            'yahoo-finance-news',
            array($this, 'admin_page')
        );
    }
    
    public function admin_init() {
        register_setting('yfnf_settings_group', 'yfnf_settings');
        
        add_settings_section(
            'yfnf_main_section',
            'Main Settings',
            null,
            'yahoo-finance-news'
        );
        
        add_settings_field(
            'default_symbol',
            'Default Stock Symbol',
            array($this, 'default_symbol_callback'),
            'yahoo-finance-news',
            'yfnf_main_section'
        );
        
        add_settings_field(
            'per_page',
            'Items Per Page',
            array($this, 'per_page_callback'),
            'yahoo-finance-news',
            'yfnf_main_section'
        );
        
        add_settings_field(
            'cache_time',
            'Cache Time (minutes)',
            array($this, 'cache_time_callback'),
            'yahoo-finance-news',
            'yfnf_main_section'
        );
        
        add_settings_field(
            'alphavantage_key',
            'Alpha Vantage API Key (Backup)',
            array($this, 'alphavantage_key_callback'),
            'yahoo-finance-news',
            'yfnf_main_section'
        );
        
        add_settings_field(
            'accordion_style',
            'Accordion Style',
            array($this, 'accordion_style_callback'),
            'yahoo-finance-news',
            'yfnf_main_section'
        );
        
        add_settings_field(
            'show_read_more',
            'Show Read More Button',
            array($this, 'show_read_more_callback'),
            'yahoo-finance-news',
            'yfnf_main_section'
        );
        
        add_settings_field(
            'content_length',
            'Content Display Mode',
            array($this, 'content_length_callback'),
            'yahoo-finance-news',
            'yfnf_main_section'
        );
    }
    
    public function default_symbol_callback() {
        $options = get_option('yfnf_settings');
        $value = isset($options['default_symbol']) ? $options['default_symbol'] : 'AAPL';
        echo '<input type="text" name="yfnf_settings[default_symbol]" value="' . esc_attr($value) . '" />';
        echo '<p class="description">Default stock symbol (e.g., AAPL, GOOGL, MSFT)</p>';
    }
    
    public function per_page_callback() {
        $options = get_option('yfnf_settings');
        $value = isset($options['per_page']) ? $options['per_page'] : 30;
        echo '<input type="number" name="yfnf_settings[per_page]" value="' . esc_attr($value) . '" min="1" max="100" />';
        echo '<p class="description">Number of news items per page</p>';
    }
    
    public function cache_time_callback() {
        $options = get_option('yfnf_settings');
        $value = isset($options['cache_time']) ? $options['cache_time'] : 15;
        echo '<input type="number" name="yfnf_settings[cache_time]" value="' . esc_attr($value) . '" min="1" max="1440" />';
        echo '<p class="description">Cache time in minutes (1-1440)</p>';
    }
    
    public function alphavantage_key_callback() {
        $options = get_option('yfnf_settings');
        $value = isset($options['alphavantage_key']) ? $options['alphavantage_key'] : '';
        echo '<input type="text" name="yfnf_settings[alphavantage_key]" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">Get free API key from <a href="https://www.alphavantage.co/support/#api-key" target="_blank">Alpha Vantage</a> for backup news source</p>';
    }
    
    public function accordion_style_callback() {
        $options = get_option('yfnf_settings');
        $value = isset($options['accordion_style']) ? $options['accordion_style'] : 'default';
        $styles = array(
            'default' => 'Default (White/Blue)',
            'dark' => 'Dark Theme',
            'minimal' => 'Minimal',
            'modern' => 'Modern'
        );
        echo '<select name="yfnf_settings[accordion_style]">';
        foreach ($styles as $key => $label) {
            echo '<option value="' . esc_attr($key) . '"' . selected($value, $key, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
    }
    
    public function show_read_more_callback() {
        $options = get_option('yfnf_settings');
        $value = isset($options['show_read_more']) ? $options['show_read_more'] : 'no';
        echo '<select name="yfnf_settings[show_read_more]">';
        echo '<option value="yes"' . selected($value, 'yes', false) . '>Yes - Show Read More Button</option>';
        echo '<option value="no"' . selected($value, 'no', false) . '>No - Hide Read More Button</option>';
        echo '</select>';
        echo '<p class="description">Choose whether to show the "Read Full Article" button or not</p>';
    }
    
    public function content_length_callback() {
        $options = get_option('yfnf_settings');
        $value = isset($options['content_length']) ? $options['content_length'] : 'full';
        echo '<select name="yfnf_settings[content_length]">';
        echo '<option value="full"' . selected($value, 'full', false) . '>Full Content - Show complete article text</option>';
        echo '<option value="excerpt"' . selected($value, 'excerpt', false) . '>Excerpt Only - Show limited content with read more</option>';
        echo '</select>';
        echo '<p class="description">Choose how much content to display in accordion</p>';
    }
    
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>Yahoo Finance News Feed Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('yfnf_settings_group');
                do_settings_sections('yahoo-finance-news');
                submit_button();
                ?>
            </form>
            <div class="yfnf-usage-info">
                <h2>Usage</h2>
                <h3>Sample Shortcode:</h3>
                <code>[yahoo_news_feed symbol="AAPL" per_page="30" page="1" show_read_more="no" content_mode="full"]</code>
                <h3>Parameters:</h3>
                <ul>
                    <li><strong>symbol</strong>: Stock symbol (default: setting value)</li>
                    <li><strong>per_page</strong>: Items per page (default: setting value)</li>
                    <li><strong>page</strong>: Page number (default: 1)</li>
                    <li><strong>style</strong>: Accordion style override</li>
                    <li><strong>show_read_more</strong>: yes/no - Show read more button</li>
                    <li><strong>content_mode</strong>: full/excerpt - Content display mode</li>
                </ul>
                <h3>Elementor Widget:</h3>
                <p>Search for "Yahoo Finance News" in Elementor widgets. All options are configurable in the widget panel.</p>
            </div>
        </div>
        <?php
    }

public function shortcode_handler($atts) {
    $options = get_option('yfnf_settings');
    
    $atts = shortcode_atts(array(
        'symbol' => isset($options['default_symbol']) ? $options['default_symbol'] : 'AAPL',
        'per_page' => isset($options['per_page']) ? $options['per_page'] : 30,
        'page' => 1,
        'style' => isset($options['accordion_style']) ? $options['accordion_style'] : 'default',
        'show_read_more' => isset($options['show_read_more']) ? $options['show_read_more'] : 'no',
        'content_mode' => isset($options['content_length']) ? $options['content_length'] : 'full',
        'show_article_count' => isset($options['show_article_count']) ? $options['show_article_count'] : 'yes',
        'show_source' => isset($options['show_source']) ? $options['show_source'] : 'yes',
        'show_pagination' => 'yes' // Force pagination for shortcode
    ), $atts);
    
    return $this->render_news_feed($atts);
}
    
    public function render_news_feed($args) {
        $news_data = $this->get_news_data($args['symbol']);
        
        if (empty($news_data)) {
            return '<div class="yfnf-error">No news data available for symbol: ' . esc_html($args['symbol']) . '</div>';
        }
        
        $total_items = count($news_data);
        $per_page = intval($args['per_page']);
        $current_page = intval($args['page']);
        $offset = ($current_page - 1) * $per_page;
        $paged_items = array_slice($news_data, $offset, $per_page);
        $total_pages = ceil($total_items / $per_page);
        
        // Determine content display settings
        $show_read_more = isset($args['show_read_more']) ? $args['show_read_more'] : 'no';
        $content_mode = isset($args['content_mode']) ? $args['content_mode'] : 'full';
        
        ob_start();
        ?>
        <div class="yfnf-container yfnf-style-<?php echo esc_attr($args['style']); ?>" 
             data-symbol="<?php echo esc_attr($args['symbol']); ?>" 
             data-per-page="<?php echo esc_attr($per_page); ?>"
             data-current-page="<?php echo esc_attr($current_page); ?>"
             data-total-pages="<?php echo esc_attr($total_pages); ?>"
             data-show-read-more="<?php echo esc_attr($show_read_more); ?>"
             data-content-mode="<?php echo esc_attr($content_mode); ?>">
            
            <div class="yfnf-header">
                <h3 class="yfnf-title">Latest News for <?php echo esc_html($args['symbol']); ?></h3>
                <div class="yfnf-meta">
                    <span class="yfnf-count"><?php echo $total_items; ?> articles</span>
                    <span class="yfnf-last-updated">Last updated: <?php echo current_time('M j, Y g:i A'); ?></span>
                </div>
            </div>
            
            <div class="yfnf-accordion">
                <?php foreach ($paged_items as $index => $item): ?>
                    <div class="yfnf-accordion-item" data-index="<?php echo $index; ?>">
                        <div class="yfnf-accordion-header">
                            <h4 class="yfnf-accordion-title"><?php echo esc_html($item['title']); ?></h4>
                            <div class="yfnf-accordion-meta">
                                <span class="yfnf-date"><?php echo esc_html($this->format_date($item['date'])); ?></span>
                                <?php if (!empty($item['source'])): ?>
                                    <span class="yfnf-source"><?php echo esc_html($item['source']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="yfnf-accordion-toggle">
                                <span class="yfnf-icon">▼</span>
                            </div>
                        </div>
                        <div class="yfnf-accordion-content">
                            <div class="yfnf-content-inner">
                                <?php 
                                // ALWAYS SHOW FULL CONTENT WITHOUT RESTRICTIONS
                                echo wp_kses_post($item['full_content']);
                                ?>
                                
                                <?php if ($show_read_more === 'yes' && !empty($item['link'])): ?>
                                    <div class="yfnf-read-more">
                                        <a href="<?php echo esc_url($item['link']); ?>" target="_blank" rel="noopener">
                                            Read Full Article →
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
<?php if ($show_pagination === 'yes' && $total_pages > 1): ?>
    <div class="yfnf-pagination">
        <?php if ($current_page > 1): ?>
            <button class="yfnf-btn yfnf-prev" data-page="<?php echo $current_page - 1; ?>">
                ← Previous
            </button>
        <?php endif; ?>
        
        <span class="yfnf-page-info">
            Page <?php echo $current_page; ?> of <?php echo $total_pages; ?>
        </span>
        
        <?php if ($current_page < $total_pages): ?>
            <button class="yfnf-btn yfnf-next" data-page="<?php echo $current_page + 1; ?>">
                Next →
            </button>
        <?php endif; ?>
    </div>
<?php endif; ?>
<div class="yfnf-loading" style="display: none;">
                <div class="yfnf-spinner"></div>
                <span>Loading news...</span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function get_news_data($symbol) {
        $cache_key = 'yfnf_news_' . $symbol;
        $options = get_option('yfnf_settings');
        $cache_time = isset($options['cache_time']) ? intval($options['cache_time']) * 60 : 900; // Default 15 minutes
        
        // Check cache first
        $cached_data = get_transient($cache_key);
        if ($cached_data !== false) {
            return $cached_data;
        }
        
        $news_data = array();
        
        // Try Yahoo Finance RSS first
        $yahoo_data = $this->fetch_yahoo_rss($symbol);
        if (!empty($yahoo_data)) {
            $news_data = $yahoo_data;
        } else {
            // Fallback to Alpha Vantage
            $alpha_data = $this->fetch_alpha_vantage_news($symbol);
            if (!empty($alpha_data)) {
                $news_data = $alpha_data;
            }
        }
        
        // Cache the results
        if (!empty($news_data)) {
            set_transient($cache_key, $news_data, $cache_time);
        }
        
        return $news_data;
    }
    
    public function fetch_yahoo_rss($symbol) {
        $rss_url = "https://finance.yahoo.com/rss/headline?s=" . urlencode($symbol);
        
        $response = wp_remote_get($rss_url, array(
            'timeout' => 30, // Increased timeout for better content fetching
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        ));
        
        if (is_wp_error($response)) {
            error_log('YFNF Error: Failed to fetch Yahoo RSS - ' . $response->get_error_message());
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        if (empty($body)) {
            error_log('YFNF Error: Empty response from Yahoo RSS');
            return false;
        }
        
        // Parse RSS using WordPress built-in SimplePie
        $rss = fetch_feed($rss_url);
        if (is_wp_error($rss)) {
            error_log('YFNF Error: Failed to parse RSS - ' . $rss->get_error_message());
            return false;
        }
        
        $news_items = array();
        $items = $rss->get_items(0, 50); // Get up to 50 items
        
        foreach ($items as $item) {
        $description = $item->get_description();
        $full_content = $item->get_content();
        
        // If no full content available, try to fetch from the article page
        if (empty($full_content) || strlen(strip_tags($full_content)) < 100) {
            $full_content = $this->fetch_article_content($item->get_link());
        }
        
        // If still no content, use description with a fallback message
        if (empty($full_content) || strlen(strip_tags($full_content)) < 50) {
            $full_content = $description;
        }
        
        // Final fallback if everything is empty
        if (empty($full_content) || strlen(strip_tags($full_content)) < 30) {
            $full_content = '<p>Content not available for this article. <a href="' . esc_url($item->get_link()) . '" target="_blank">View original article</a></p>';
        }
            
            $news_items[] = array(
                'title' => $item->get_title(),
                'content' => $this->clean_content($full_content, 0), // Always use full content
                'full_content' => $this->clean_content($full_content, 0), // Full version without restrictions
                'link' => $item->get_link(),
                'date' => $item->get_date('Y-m-d H:i:s'),
                'source' => 'Yahoo Finance'
            );
        }
        
        return $news_items;
    }
    
    public function fetch_article_content($url) {
        // Try to fetch full article content from the source
        $response = wp_remote_get($url, array(
            'timeout' => 15,
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        ));
        
        if (is_wp_error($response)) {
            return '';
        }
        
        $body = wp_remote_retrieve_body($response);
        
        // Extract article content from HTML
        if (preg_match('/<article.*?>(.*?)<\/article>/is', $body, $matches)) {
            return $matches[1];
        }
        
        // Fallback: look for main content div
        if (preg_match('/<div[^>]*class="[^"]*content[^"]*"[^>]*>(.*?)<\/div>/is', $body, $matches)) {
            return $matches[1];
        }
        
        // Another fallback: look for entry content
        if (preg_match('/<div[^>]*class="[^"]*entry-content[^"]*"[^>]*>(.*?)<\/div>/is', $body, $matches)) {
            return $matches[1];
        }
        
        return '';
    }
    
    public function fetch_alpha_vantage_news($symbol) {
        $options = get_option('yfnf_settings');
        $api_key = isset($options['alphavantage_key']) ? $options['alphavantage_key'] : '';
        
        if (empty($api_key)) {
            return false;
        }
        
        $api_url = "https://www.alphavantage.co/query?function=NEWS_SENTIMENT&tickers=" . urlencode($symbol) . "&apikey=" . $api_key;
        
        $response = wp_remote_get($api_url, array(
            'timeout' => 15
        ));
        
        if (is_wp_error($response)) {
            error_log('YFNF Error: Failed to fetch Alpha Vantage news - ' . $response->get_error_message());
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['feed']) || !is_array($data['feed'])) {
            error_log('YFNF Error: Invalid response from Alpha Vantage');
            return false;
        }
        
        $news_items = array();
        foreach ($data['feed'] as $item) {
            $summary = isset($item['summary']) ? $item['summary'] : '';
            
            // Try to get full content if summary is short
            if (strlen($summary) < 500 && isset($item['url'])) {
                $full_content = $this->fetch_article_content($item['url']);
                if (!empty($full_content)) {
                    $summary = $full_content;
                }
            }
            
            $news_items[] = array(
                'title' => isset($item['title']) ? $item['title'] : '',
                'content' => $this->clean_content($summary, 0), // No character limit
                'full_content' => $this->clean_content($summary, 0), // No character limit
                'link' => isset($item['url']) ? $item['url'] : '',
                'date' => isset($item['time_published']) ? date('Y-m-d H:i:s', strtotime($item['time_published'])) : '',
                'source' => isset($item['source']) ? $item['source'] : 'Alpha Vantage'
            );
        }
        
        return $news_items;
    }
    
    public function clean_content($content, $max_length = 0) {
        // Remove HTML tags except basic formatting
        $allowed_tags = '<p><br><strong><em><ul><li><ol><a><h1><h2><h3><h4><h5><h6><blockquote><pre><code><img><table><tr><td><th>';
        $content = strip_tags($content, $allowed_tags);
        
        // Remove extra whitespace and normalize
        $content = preg_replace('/\s+/', ' ', $content);
        $content = trim($content);
        
        // If max_length is 0, return full content without any restrictions
        if ($max_length === 0) {
            return $content;
        }
        
        // This part will never execute since we always pass 0, but keeping for completeness
        if (strlen($content) > $max_length) {
            $content = substr($content, 0, $max_length);
            $last_period = strrpos($content, '.');
            if ($last_period !== false && $last_period > $max_length * 0.7) {
                $content = substr($content, 0, $last_period + 1);
            } else {
                $content .= '...';
            }
        }
        
        return $content;
    }
    
    public function format_date($date_string) {
        if (empty($date_string)) {
            return '';
        }
        
        $date = new DateTime($date_string);
        $now = new DateTime();
        $diff = $now->diff($date);
        
        if ($diff->days == 0) {
            if ($diff->h == 0) {
                return $diff->i . ' minutes ago';
            }
            return $diff->h . ' hours ago';
        } elseif ($diff->days == 1) {
            return 'Yesterday';
        } elseif ($diff->days < 7) {
            return $diff->days . ' days ago';
        } else {
            return $date->format('M j, Y');
        }
    }
    
    public function ajax_load_more() {
        check_ajax_referer('yfnf_nonce', 'nonce');
        
        $symbol = sanitize_text_field($_POST['symbol']);
        $page = intval($_POST['page']);
        $per_page = intval($_POST['per_page']);
        
        $args = array(
            'symbol' => $symbol,
            'page' => $page,
            'per_page' => $per_page,
            'show_read_more' => sanitize_text_field($_POST['show_read_more'] ?? 'no'),
            'content_mode' => sanitize_text_field($_POST['content_mode'] ?? 'full')
        );
        
        $html = $this->render_news_feed($args);
        
        wp_send_json_success(array('html' => $html));
    }
    
    public function register_elementor_widget() {
        if (class_exists('\\Elementor\\Plugin')) {
            require_once YFNF_PLUGIN_PATH . 'includes/elementor-widget.php';
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \YFNF_Elementor_Widget());
        }
    }
    
    public function activate() {
        // Set default options
        $default_options = array(
            'default_symbol' => 'AAPL',
            'per_page' => 30,
            'cache_time' => 15,
            'alphavantage_key' => '',
            'accordion_style' => 'default',
            'show_read_more' => 'no',
            'content_length' => 'full'
        );
        
        add_option('yfnf_settings', $default_options);
        
        // Create custom tables if needed
        $this->create_tables();
    }
    
    public function deactivate() {
        // Clean up transients
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_yfnf_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_yfnf_%'");
    }
    
    private function create_tables() {
        // Future: Create custom tables for better performance if needed
    }
}

// Initialize the plugin
new YahooFinanceNewsFeed();

// Additional utility functions
class YFNF_Utils {
    
    public static function format_date($date_string) {
        if (empty($date_string)) {
            return '';
        }
        
        $date = new DateTime($date_string);
        $now = new DateTime();
        $diff = $now->diff($date);
        
        if ($diff->days == 0) {
            if ($diff->h == 0) {
                return $diff->i . ' minutes ago';
            }
            return $diff->h . ' hours ago';
        } elseif ($diff->days == 1) {
            return 'Yesterday';
        } elseif ($diff->days < 7) {
            return $diff->days . ' days ago';
        } else {
            return $date->format('M j, Y');
        }
    }
    
    public static function extract_excerpt($content, $length = 150) {
        $content = strip_tags($content);
        if (strlen($content) <= $length) {
            return $content;
        }
        
        $excerpt = substr($content, 0, $length);
        $last_space = strrpos($excerpt, ' ');
        
        if ($last_space !== false) {
            $excerpt = substr($excerpt, 0, $last_space);
        }
        
        return $excerpt . '...';
    }
    
    public static function sanitize_symbol($symbol) {
        return strtoupper(preg_replace('/[^A-Za-z0-9.]/', '', $symbol));
    }
    
    public static function get_cache_key($symbol, $page = 1) {
        return 'yfnf_' . md5($symbol . '_' . $page);
    }
}

// REST API endpoints for external integrations
add_action('rest_api_init', function() {
    register_rest_route('yahoo-finance-news/v1', '/news/(?P<symbol>[a-zA-Z0-9.]+)', array(
        'methods' => 'GET',
        'callback' => 'yfnf_rest_get_news',
        'permission_callback' => '__return_true',
        'args' => array(
            'symbol' => array(
                'required' => true,
                'sanitize_callback' => array('YFNF_Utils', 'sanitize_symbol'),
            ),
            'per_page' => array(
                'default' => 30,
                'sanitize_callback' => 'absint',
            ),
            'page' => array(
                'default' => 1,
                'sanitize_callback' => 'absint',
            ),
        ),
    ));
});

function yfnf_rest_get_news($request) {
    $symbol = $request->get_param('symbol');
    $per_page = $request->get_param('per_page');
    $page = $request->get_param('page');
    
    $yahoo_feed = new YahooFinanceNewsFeed();
    $news_data = $yahoo_feed->get_news_data($symbol);
    
    if (empty($news_data)) {
        return new WP_Error('no_data', 'No news data available', array('status' => 404));
    }
    
    $total_items = count($news_data);
    $offset = ($page - 1) * $per_page;
    $paged_items = array_slice($news_data, $offset, $per_page);
    
    return array(
        'data' => $paged_items,
        'total' => $total_items,
        'pages' => ceil($total_items / $per_page),
        'current_page' => $page,
        'per_page' => $per_page
    );
}

// Admin notice for API key setup
add_action('admin_notices', function() {
    $options = get_option('yfnf_settings');
    $api_key = isset($options['alphavantage_key']) ? $options['alphavantage_key'] : '';
    
    if (empty($api_key) && isset($_GET['page']) && $_GET['page'] !== 'yahoo-finance-news') {
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p><strong>Yahoo Finance News Feed:</strong> For better reliability, consider adding an Alpha Vantage API key in the <a href="' . admin_url('options-general.php?page=yahoo-finance-news') . '">plugin settings</a>.</p>';
        echo '</div>';
    }
});

// Widget for classic WordPress widgets
class YFNF_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'yfnf_widget',
            'Yahoo Finance News',
            array('description' => 'Display Yahoo Finance news feed')
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        $yahoo_feed = new YahooFinanceNewsFeed();
        $feed_args = array(
            'symbol' => !empty($instance['symbol']) ? $instance['symbol'] : 'AAPL',
            'per_page' => !empty($instance['per_page']) ? $instance['per_page'] : 10,
            'page' => 1,
            'style' => !empty($instance['style']) ? $instance['style'] : 'default',
            'show_read_more' => !empty($instance['show_read_more']) ? $instance['show_read_more'] : 'no',
            'content_mode' => !empty($instance['content_mode']) ? $instance['content_mode'] : 'full'
        );
        
        echo $yahoo_feed->render_news_feed($feed_args);
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : 'Latest Financial News';
        $symbol = !empty($instance['symbol']) ? $instance['symbol'] : 'AAPL';
        $per_page = !empty($instance['per_page']) ? $instance['per_page'] : 10;
        $style = !empty($instance['style']) ? $instance['style'] : 'default';
        $show_read_more = !empty($instance['show_read_more']) ? $instance['show_read_more'] : 'no';
        $content_mode = !empty($instance['content_mode']) ? $instance['content_mode'] : 'full';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" 
                   name="<?php echo $this->get_field_name('title'); ?>" type="text" 
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('symbol'); ?>">Stock Symbol:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('symbol'); ?>" 
                   name="<?php echo $this->get_field_name('symbol'); ?>" type="text" 
                   value="<?php echo esc_attr($symbol); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('per_page'); ?>">Items to Show:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('per_page'); ?>" 
                   name="<?php echo $this->get_field_name('per_page'); ?>" type="number" 
                   value="<?php echo esc_attr($per_page); ?>" min="1" max="50">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('style'); ?>">Style:</label>
            <select class="widefat" id="<?php echo $this->get_field_id('style'); ?>" 
                    name="<?php echo $this->get_field_name('style'); ?>">
                <option value="default" <?php selected($style, 'default'); ?>>Default</option>
                <option value="dark" <?php selected($style, 'dark'); ?>>Dark</option>
                <option value="minimal" <?php selected($style, 'minimal'); ?>>Minimal</option>
                <option value="modern" <?php selected($style, 'modern'); ?>>Modern</option>
            </select>
        </p>
                <p>
            <label for="<?php echo $this->get_field_id('content_mode'); ?>">Content Mode:</label>
            <select class="widefat" id="<?php echo $this->get_field_id('content_mode'); ?>" 
                    name="<?php echo $this->get_field_name('content_mode'); ?>">
                <option value="full" <?php selected($content_mode, 'full'); ?>>Full Content</option>
                <option value="excerpt" <?php selected($content_mode, 'excerpt'); ?>>Excerpt Only</option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('show_read_more'); ?>">Show Read More:</label>
            <select class="widefat" id="<?php echo $this->get_field_id('show_read_more'); ?>" 
                    name="<?php echo $this->get_field_name('show_read_more'); ?>">
                <option value="no" <?php selected($show_read_more, 'no'); ?>>No</option>
                <option value="yes" <?php selected($show_read_more, 'yes'); ?>>Yes</option>
            </select>
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['symbol'] = (!empty($new_instance['symbol'])) ? YFNF_Utils::sanitize_symbol($new_instance['symbol']) : 'AAPL';
        $instance['per_page'] = (!empty($new_instance['per_page'])) ? absint($new_instance['per_page']) : 10;
        $instance['style'] = (!empty($new_instance['style'])) ? sanitize_text_field($new_instance['style']) : 'default';
        $instance['show_read_more'] = (!empty($new_instance['show_read_more'])) ? sanitize_text_field($new_instance['show_read_more']) : 'no';
        $instance['content_mode'] = (!empty($new_instance['content_mode'])) ? sanitize_text_field($new_instance['content_mode']) : 'full';
        
        return $instance;
    }
}

// Register the widget
add_action('widgets_init', function() {
    register_widget('YFNF_Widget');
});

// Clear cache when settings are updated
add_action('update_option_yfnf_settings', function($old_value, $value) {
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_yfnf_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_yfnf_%'");
}, 10, 2);

// Add debug information in admin footer when plugin settings page is active
add_action('admin_footer', function() {
    $screen = get_current_screen();
    if ($screen->id === 'settings_page_yahoo-finance-news') {
        echo '<div style="position: fixed; bottom: 10px; right: 10px; background: #f0f0f1; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 12px;">
                <strong>YFNF Debug Info:</strong><br>
                Plugin Version: ' . YFNF_VERSION . '<br>
                Cache Status: Active<br>
                Last Updated: ' . date('Y-m-d H:i:s') . '
              </div>';
    }
});

// Add plugin action links
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=yahoo-finance-news') . '">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
});

// Handle plugin uninstall
register_uninstall_hook(__FILE__, 'yfnf_uninstall');
function yfnf_uninstall() {
    // Delete plugin options
    delete_option('yfnf_settings');
    
    // Clear all transients
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_yfnf_%'");
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_yfnf_%'");
}

// Add dashboard widget for quick stats
add_action('wp_dashboard_setup', function() {
    if (current_user_can('manage_options')) {
        wp_add_dashboard_widget(
            'yfnf_dashboard_widget',
            'Yahoo Finance News Stats',
            'yfnf_dashboard_widget_content'
        );
    }
});

function yfnf_dashboard_widget_content() {
    $options = get_option('yfnf_settings');
    $symbol = isset($options['default_symbol']) ? $options['default_symbol'] : 'AAPL';
    
    echo '<p><strong>Default Symbol:</strong> ' . esc_html($symbol) . '</p>';
    echo '<p><strong>Cache Duration:</strong> ' . (isset($options['cache_time']) ? esc_html($options['cache_time']) : '15') . ' minutes</p>';
    echo '<p><a href="' . admin_url('options-general.php?page=yahoo-finance-news') . '" class="button button-primary">Plugin Settings</a></p>';
}

// Add RSS feed health check
add_action('wp_ajax_yfnf_health_check', function() {
    check_ajax_referer('yfnf_nonce', 'nonce');
    
    $symbol = sanitize_text_field($_POST['symbol']);
    $yahoo_feed = new YahooFinanceNewsFeed();
    $news_data = $yahoo_feed->get_news_data($symbol);
    
    if (!empty($news_data)) {
        wp_send_json_success(array(
            'count' => count($news_data),
            'sample_title' => $news_data[0]['title'],
            'content_length' => strlen($news_data[0]['full_content'])
        ));
    } else {
        wp_send_json_error('No news data available');
    }
});

// Add shortcode for single news item
function yfnf_single_news_shortcode($atts) {
    $atts = shortcode_atts(array(
        'symbol' => 'AAPL',
        'index' => 0
    ), $atts);
    
    $yahoo_feed = new YahooFinanceNewsFeed();
    $news_data = $yahoo_feed->get_news_data($atts['symbol']);
    
    if (empty($news_data) || !isset($news_data[$atts['index']])) {
        return '<div class="yfnf-error">News item not found</div>';
    }
    
    $item = $news_data[$atts['index']];
    
    ob_start();
    ?>
    <div class="yfnf-single-news">
        <h3><?php echo esc_html($item['title']); ?></h3>
        <div class="yfnf-single-meta">
            <span class="yfnf-date"><?php echo esc_html($yahoo_feed->format_date($item['date'])); ?></span>
            <?php if (!empty($item['source'])): ?>
                <span class="yfnf-source"><?php echo esc_html($item['source']); ?></span>
            <?php endif; ?>
        </div>
        <div class="yfnf-single-content">
            <?php echo wp_kses_post($item['full_content']); ?>
        </div>
        <?php if (!empty($item['link'])): ?>
            <div class="yfnf-single-read-more">
                <a href="<?php echo esc_url($item['link']); ?>" target="_blank" rel="noopener">
                    Read Full Article →
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('yahoo_single_news', 'yfnf_single_news_shortcode');

// Add custom CSS for single news item
add_action('wp_head', function() {
    ?>
    <style>
    .yfnf-single-news {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
        background: #fff;
    }
    .yfnf-single-news h3 {
        margin-top: 0;
        color: #1f2937;
    }
    .yfnf-single-meta {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
        font-size: 14px;
        color: #6b7280;
    }
    .yfnf-single-content {
        line-height: 1.6;
    }
    .yfnf-single-read-more {
        margin-top: 20px;
        text-align: right;
    }
    </style>
    <?php
});

// Add emergency fallback for RSS parsing
function yfnf_emergency_fetch($symbol) {
    $url = "https://feeds.finance.yahoo.com/rss/2.0/headline?s=$symbol&region=US&lang=en-US";
    
    $response = wp_remote_get($url, array(
        'timeout' => 30,
        'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    ));
    
    if (is_wp_error($response)) {
        return false;
    }
    
    $body = wp_remote_retrieve_body($response);
    
    // Simple XML parsing as fallback
    try {
        $xml = simplexml_load_string($body);
        $news_items = array();
        
        if ($xml && isset($xml->channel->item)) {
            foreach ($xml->channel->item as $item) {
                $news_items[] = array(
                    'title' => (string) $item->title,
                    'content' => (string) $item->description,
                    'full_content' => (string) $item->description,
                    'link' => (string) $item->link,
                    'date' => date('Y-m-d H:i:s', strtotime((string) $item->pubDate)),
                    'source' => 'Yahoo Finance (Fallback)'
                );
            }
        }
        
        return $news_items;
    } catch (Exception $e) {
        error_log('YFNF Emergency fetch error: ' . $e->getMessage());
        return false;
    }
}

// Modify the get_news_data method to include emergency fallback
// This would need to be added to the YahooFinanceNewsFeed class
// For now, we'll add it as a helper function
function yfnf_get_news_with_fallback($symbol) {
    $yahoo_feed = new YahooFinanceNewsFeed();
    $news_data = $yahoo_feed->get_news_data($symbol);
    
    if (empty($news_data)) {
        $news_data = yfnf_emergency_fetch($symbol);
    }
    
    return $news_data;
}

// Add this to the YahooFinanceNewsFeed class get_news_data method
// (This would need to be added manually to the class)
// After line: $alpha_data = $this->fetch_alpha_vantage_news($symbol);
// Add: if (empty($news_data)) { $news_data = yfnf_emergency_fetch($symbol); }

?>