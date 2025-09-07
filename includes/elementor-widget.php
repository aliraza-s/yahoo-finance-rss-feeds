<?php
/**
 * Complete Elementor Widget for Yahoo Finance News Feed
 */

if (!defined('ABSPATH')) {
    exit;
}

class YFNF_Elementor_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'yahoo-finance-news';
    }
    
    public function get_title() {
        return __('Yahoo Finance News', 'yahoo-finance-news');
    }
    
    public function get_icon() {
        return 'eicon-posts-ticker';
    }
    
    public function get_categories() {
        return ['general'];
    }
    
    public function get_keywords() {
        return ['finance', 'news', 'stock', 'yahoo', 'rss', 'accordion'];
    }
    
    public function get_script_depends() {
        return ['yfnf-script'];
    }
    
    public function get_style_depends() {
        return ['yfnf-styles'];
    }
    
    protected function register_controls() {
        
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content Settings', 'yahoo-finance-news'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'symbol',
            [
                'label' => __('Stock Symbol', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 'AAPL',
                'placeholder' => 'AAPL',
                'description' => __('Enter stock symbol (e.g., AAPL, GOOGL, MSFT)', 'yahoo-finance-news'),
                'dynamic' => [
                    'active' => true,
                ],
                'label_block' => false,
            ]
        );
        
        $this->add_control(
            'per_page',
            [
                'label' => __('Items Per Page', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 20,
                'min' => 1,
                'max' => 100,
                'step' => 1,
                'description' => __('Number of news articles to display per page', 'yahoo-finance-news'),
            ]
        );
        
        $this->add_control(
            'show_pagination',
            [
                'label' => __('Show Pagination', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_on' => __('Show', 'yahoo-finance-news'),
                'label_off' => __('Hide', 'yahoo-finance-news'),
                'description' => __('Enable pagination controls for multiple pages', 'yahoo-finance-news'),
            ]
        );
        
        $this->add_control(
            'single_accordion',
            [
                'label' => __('Single Accordion Mode', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_on' => __('Yes', 'yahoo-finance-news'),
                'label_off' => __('No', 'yahoo-finance-news'),
                'description' => __('Allow only one accordion item to be open at a time', 'yahoo-finance-news'),
            ]
        );
        
        $this->end_controls_section();
        
        // Content Display Section
        $this->start_controls_section(
            'content_display_section',
            [
                'label' => __('Content Display', 'yahoo-finance-news'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'content_mode',
            [
                'label' => __('Content Display Mode', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'full',
                'options' => [
                    'full' => __('Full Content - Show complete article text', 'yahoo-finance-news'),
                    'excerpt' => __('Excerpt Only - Show limited content', 'yahoo-finance-news'),
                ],
                'description' => __('Choose how much content to display in accordion', 'yahoo-finance-news'),
            ]
        );
        
        $this->add_control(
            'show_read_more',
            [
                'label' => __('Show Read More Button', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'no',
                'label_on' => __('Show', 'yahoo-finance-news'),
                'label_off' => __('Hide', 'yahoo-finance-news'),
                'description' => __('Show "Read Full Article" button that opens original source', 'yahoo-finance-news'),
            ]
        );
        
        $this->add_control(
            'content_length_notice',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<div style="background: #e8f4fd; padding: 15px; border-radius: 5px; border-left: 4px solid #2196F3; margin: 10px 0;">
                    <strong>💡 Content Display Tips:</strong><br>
                    • <strong>Full Content:</strong> Shows complete article text within accordion (keeps users on your site)<br>
                    • <strong>Excerpt Only:</strong> Shows limited content with optional read more button<br>
                    • <strong>Hide Read More:</strong> Prevents users from leaving your website<br><br>
                    <strong>🎯 Recommended:</strong> Use "Full Content" + "Hide Read More" for maximum user retention!
                </div>',
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            ]
        );
        
        $this->end_controls_section();
        
        // Display Options Section
        $this->start_controls_section(
            'display_section',
            [
                'label' => __('Display Options', 'yahoo-finance-news'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'show_source',
            [
                'label' => __('Show Source', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_on' => __('Show', 'yahoo-finance-news'),
                'label_off' => __('Hide', 'yahoo-finance-news'),
                'description' => __('Display the news source (e.g., Yahoo Finance)', 'yahoo-finance-news'),
            ]
        );
        
        $this->add_control(
            'show_date',
            [
                'label' => __('Show Date', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
                'label_on' => __('Show', 'yahoo-finance-news'),
                'label_off' => __('Hide', 'yahoo-finance-news'),
                'description' => __('Display publication date/time', 'yahoo-finance-news'),
            ]
        );
        
        $this->add_control(
            'show_excerpt',
            [
                'label' => __('Show Excerpt in Header', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'no',
                'label_on' => __('Show', 'yahoo-finance-news'),
                'label_off' => __('Hide', 'yahoo-finance-news'),
                'description' => __('Show a preview of content in the accordion header', 'yahoo-finance-news'),
            ]
        );
        
        $this->add_control(
            'excerpt_length',
            [
                'label' => __('Excerpt Length', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 100,
                'min' => 50,
                'max' => 300,
                'condition' => [
                    'show_excerpt' => 'yes',
                ],
                'description' => __('Number of characters to show in excerpt', 'yahoo-finance-news'),
            ]
        );
        
        $this->add_control(
            'enable_search',
            [
                'label' => __('Enable Search', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'no',
                'label_on' => __('Enable', 'yahoo-finance-news'),
                'label_off' => __('Disable', 'yahoo-finance-news'),
                'description' => __('Add search functionality to filter articles', 'yahoo-finance-news'),
            ]
        );
        
        $this->end_controls_section();
        
        // Style Section - General
        $this->start_controls_section(
            'general_style_section',
            [
                'label' => __('General Style', 'yahoo-finance-news'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'accordion_style',
            [
                'label' => __('Preset Style', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'default',
                'options' => [
                    'default' => __('Default (Clean & Professional)', 'yahoo-finance-news'),
                    'dark' => __('Dark Theme', 'yahoo-finance-news'),
                    'minimal' => __('Minimal (Borderless)', 'yahoo-finance-news'),
                    'modern' => __('Modern (Glassmorphism)', 'yahoo-finance-news'),
                ],
                'description' => __('Choose a pre-designed style theme', 'yahoo-finance-news'),
            ]
        );
        
        $this->add_responsive_control(
            'container_padding',
            [
                'label' => __('Container Padding', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .yfnf-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_control(
            'container_background',
            [
                'label' => __('Container Background', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .yfnf-container' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'container_border',
                'selector' => '{{WRAPPER}} .yfnf-container',
            ]
        );
        
        $this->add_responsive_control(
            'container_border_radius',
            [
                'label' => __('Border Radius', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .yfnf-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'container_box_shadow',
                'selector' => '{{WRAPPER}} .yfnf-container',
            ]
        );
        
        $this->end_controls_section();
        
        // Style Section - Header
        $this->start_controls_section(
            'header_style_section',
            [
                'label' => __('Header Style', 'yahoo-finance-news'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'header_background',
            [
                'label' => __('Header Background', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .yfnf-header' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'main_title_color',
            [
                'label' => __('Main Title Color', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .yfnf-title' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'main_title_typography',
                'label' => __('Main Title Typography', 'yahoo-finance-news'),
                'selector' => '{{WRAPPER}} .yfnf-title',
            ]
        );
        
        $this->add_control(
            'meta_color',
            [
                'label' => __('Meta Text Color', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .yfnf-meta' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .yfnf-last-updated' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'header_padding',
            [
                'label' => __('Header Padding', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .yfnf-header' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Style Section - Accordion Items
        $this->start_controls_section(
            'accordion_style_section',
            [
                'label' => __('Accordion Items', 'yahoo-finance-news'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'item_background',
            [
                'label' => __('Item Background', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .yfnf-accordion-item' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .yfnf-accordion-header' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'item_hover_background',
            [
                'label' => __('Item Hover Background', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .yfnf-accordion-item:hover' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .yfnf-accordion-header:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'item_border_color',
            [
                'label' => __('Border Color', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .yfnf-accordion-item' => 'border-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'item_border_width',
            [
                'label' => __('Border Width', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 10,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .yfnf-accordion-item' => 'border-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_control(
            'item_border_radius',
            [
                'label' => __('Border Radius', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .yfnf-accordion-item' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'item_margin',
            [
                'label' => __('Item Spacing', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .yfnf-accordion-item' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'item_box_shadow',
                'label' => __('Box Shadow', 'yahoo-finance-news'),
                'selector' => '{{WRAPPER}} .yfnf-accordion-item',
            ]
        );
        
        $this->end_controls_section();
        
        // Style Section - Accordion Titles
        $this->start_controls_section(
            'title_style_section',
            [
                'label' => __('Accordion Titles', 'yahoo-finance-news'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'title_color',
            [
                'label' => __('Title Color', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .yfnf-accordion-title' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'title_hover_color',
            [
                'label' => __('Title Hover Color', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .yfnf-accordion-header:hover .yfnf-accordion-title' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => __('Typography', 'yahoo-finance-news'),
                'selector' => '{{WRAPPER}} .yfnf-accordion-title',
            ]
        );
        
        $this->add_responsive_control(
            'title_padding',
            [
                'label' => __('Title Padding', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .yfnf-accordion-header' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Style Section - Meta Information
        $this->start_controls_section(
            'meta_style_section',
            [
                'label' => __('Meta Information', 'yahoo-finance-news'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'date_color',
            [
                'label' => __('Date Color', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .yfnf-date' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'show_date' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'source_color',
            [
                'label' => __('Source Color', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .yfnf-source' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'show_source' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'source_background',
            [
                'label' => __('Source Background', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .yfnf-source' => 'background-color: {{VALUE}};',
                ],
                'condition' => [
                    'show_source' => 'yes',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'meta_typography',
                'label' => __('Meta Typography', 'yahoo-finance-news'),
                'selector' => '{{WRAPPER}} .yfnf-date, {{WRAPPER}} .yfnf-source',
            ]
        );
        
        $this->end_controls_section();
        
        // Style Section - Content
        $this->start_controls_section(
            'content_style_section',
            [
                'label' => __('Content Area', 'yahoo-finance-news'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'content_background',
            [
                'label' => __('Content Background', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .yfnf-content-inner' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'content_color',
            [
                'label' => __('Content Color', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .yfnf-content-inner' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .yfnf-content-inner p' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'content_typography',
                'label' => __('Typography', 'yahoo-finance-news'),
                'selector' => '{{WRAPPER}} .yfnf-content-inner, {{WRAPPER}} .yfnf-content-inner p',
            ]
        );
        
        $this->add_responsive_control(
            'content_padding',
            [
                'label' => __('Content Padding', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .yfnf-content-inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Style Section - Read More Button
        $this->start_controls_section(
            'read_more_style_section',
            [
                'label' => __('Read More Button', 'yahoo-finance-news'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_read_more' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'read_more_color',
            [
                'label' => __('Button Color', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .yfnf-read-more a' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'read_more_bg_color',
            [
                'label' => __('Button Background', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .yfnf-read-more a' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'read_more_hover_color',
            [
                'label' => __('Button Hover Color', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .yfnf-read-more a:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'read_more_hover_bg',
            [
                'label' => __('Button Hover Background', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .yfnf-read-more a:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'read_more_typography',
                'selector' => '{{WRAPPER}} .yfnf-read-more a',
            ]
        );
        
        $this->add_responsive_control(
            'read_more_padding',
            [
                'label' => __('Button Padding', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .yfnf-read-more a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_control(
            'read_more_border_radius',
            [
                'label' => __('Button Border Radius', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .yfnf-read-more a' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Style Section - Toggle Button
        $this->start_controls_section(
            'toggle_style_section',
            [
                'label' => __('Toggle Button', 'yahoo-finance-news'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'toggle_background',
            [
                'label' => __('Toggle Background', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .yfnf-accordion-toggle' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'toggle_active_background',
            [
                'label' => __('Toggle Active Background', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .yfnf-accordion-header.active .yfnf-accordion-toggle' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'toggle_icon_color',
            [
                'label' => __('Toggle Icon Color', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .yfnf-icon' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'toggle_icon_active_color',
            [
                'label' => __('Toggle Icon Active Color', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .yfnf-accordion-header.active .yfnf-icon' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'toggle_size',
            [
                'label' => __('Toggle Size', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 20,
                        'max' => 60,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .yfnf-accordion-toggle' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Style Section - Pagination
        $this->start_controls_section(
            'pagination_style_section',
            [
                'label' => __('Pagination', 'yahoo-finance-news'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_pagination' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'pagination_background',
            [
                'label' => __('Pagination Background', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .yfnf-pagination' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'pagination_padding',
            [
                'label' => __('Pagination Padding', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .yfnf-pagination' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_control(
            'button_color',
            [
                'label' => __('Button Background', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .yfnf-btn' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'button_hover_color',
            [
                'label' => __('Button Hover Background', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .yfnf-btn:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'button_text_color',
            [
                'label' => __('Button Text Color', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .yfnf-btn' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'button_text_hover_color',
            [
                'label' => __('Button Text Hover Color', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .yfnf-btn:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'selector' => '{{WRAPPER}} .yfnf-btn',
            ]
        );
        
        $this->add_responsive_control(
            'button_padding',
            [
                'label' => __('Button Padding', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .yfnf-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_control(
            'button_border_radius',
            [
                'label' => __('Button Border Radius', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .yfnf-btn' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'button_spacing',
            [
                'label' => __('Button Spacing', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .yfnf-pagination' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Advanced Section
        $this->start_controls_section(
            'advanced_section',
            [
                'label' => __('Advanced Settings', 'yahoo-finance-news'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'auto_refresh',
            [
                'label' => __('Auto Refresh', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'no',
                'label_on' => __('Enable', 'yahoo-finance-news'),
                'label_off' => __('Disable', 'yahoo-finance-news'),
                'description' => __('Automatically refresh news content', 'yahoo-finance-news'),
            ]
        );
        
        $this->add_control(
            'refresh_interval',
            [
                'label' => __('Refresh Interval (minutes)', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 5,
                'min' => 1,
                'max' => 60,
                'condition' => [
                    'auto_refresh' => 'yes',
                ],
                'description' => __('How often to refresh the content', 'yahoo-finance-news'),
            ]
        );
        
        $this->add_control(
            'cache_duration',
            [
                'label' => __('Cache Duration (minutes)', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 15,
                'min' => 1,
                'max' => 1440,
                'description' => __('How long to cache the RSS feed data', 'yahoo-finance-news'),
            ]
        );
        
        $this->add_control(
            'animation_speed',
            [
                'label' => __('Animation Speed (ms)', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 300,
                'min' => 100,
                'max' => 1000,
                'description' => __('Speed of accordion open/close animations', 'yahoo-finance-news'),
            ]
        );
        
        $this->add_control(
            'custom_css_id',
            [
                'label' => __('CSS ID', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => __('Add a custom CSS ID to the container', 'yahoo-finance-news'),
            ]
        );
        
        $this->add_control(
            'custom_css_class',
            [
                'label' => __('CSS Classes', 'yahoo-finance-news'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => __('Add custom CSS classes to the container', 'yahoo-finance-news'),
            ]
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Prepare arguments with all settings
        $args = array(
            'symbol' => !empty($settings['symbol']) ? YFNF_Utils::sanitize_symbol($settings['symbol']) : 'AAPL',
        'per_page' => !empty($settings['per_page']) ? absint($settings['per_page']) : 20,
        'page' => 1,
            'style' => !empty($settings['accordion_style']) ? $settings['accordion_style'] : 'default',
            'show_source' => $settings['show_source'] === 'yes',
            'show_date' => $settings['show_date'] === 'yes',
            'show_pagination' => $settings['show_pagination'] === 'yes',
            'single_accordion' => $settings['single_accordion'] === 'yes',
            'show_excerpt' => $settings['show_pagination'] === 'yes' ? 'yes' : 'no',
            'excerpt_length' => !empty($settings['excerpt_length']) ? absint($settings['excerpt_length']) : 100,
            'show_read_more' => $settings['show_read_more'] === 'yes' ? 'yes' : 'no',
            'content_mode' => !empty($settings['content_mode']) ? $settings['content_mode'] : 'full',
            'enable_search' => $settings['enable_search'] === 'yes',
            'auto_refresh' => $settings['auto_refresh'] === 'yes',
            'refresh_interval' => !empty($settings['refresh_interval']) ? absint($settings['refresh_interval']) : 5,
            'cache_duration' => !empty($settings['cache_duration']) ? absint($settings['cache_duration']) : 15,
            'animation_speed' => !empty($settings['animation_speed']) ? absint($settings['animation_speed']) : 300,
        );
        
        // Add custom attributes
        $container_attributes = '';
        if (!empty($settings['custom_css_id'])) {
            $container_attributes .= ' id="' . esc_attr($settings['custom_css_id']) . '"';
        }
        if (!empty($settings['custom_css_class'])) {
            $container_attributes .= ' class="' . esc_attr($settings['custom_css_class']) . '"';
        }
        
        // Check if YahooFinanceNewsFeed class exists
        if (class_exists('YahooFinanceNewsFeed')) {
            $yahoo_feed = new YahooFinanceNewsFeed();
            $output = $yahoo_feed->render_news_feed($args);
            
            // Add custom attributes to container if specified
            if ($container_attributes) {
                $output = str_replace('<div class="yfnf-container', '<div' . $container_attributes . ' class="yfnf-container', $output);
            }
            
            echo $output;
        } else {
            echo '<div class="yfnf-error">
                <strong>Plugin Error:</strong> Yahoo Finance News Feed plugin is not properly loaded. 
                Please ensure the plugin is activated and configured correctly.
            </div>';
        }
    }
    
    protected function content_template() {
        ?>
        <#
        var symbol = settings.symbol || 'AAPL';
        var per_page = settings.per_page || 20;
        var style = settings.accordion_style || 'default';
        var content_mode = settings.content_mode || 'full';
        var show_read_more = settings.show_read_more || 'no';
        var show_search = settings.enable_search || 'no';
        #>
        
        <div class="yfnf-container yfnf-style-{{{ style }}}" data-symbol="{{{ symbol }}}" data-content-mode="{{{ content_mode }}}" data-show-read-more="{{{ show_read_more }}}">
            
            <div class="yfnf-header">
                <h3 class="yfnf-title">Latest News for {{{ symbol }}}</h3>
                <div class="yfnf-meta">
                    <span class="yfnf-count">{{{ per_page }}} articles per page</span>
                    <span class="yfnf-last-updated">Preview Mode - {{{ content_mode }}} content</span>
                </div>
                
                <# if ( show_search === 'yes' ) { #>
                <div class="yfnf-search-wrapper">
                    <input type="text" class="yfnf-search-input" placeholder="Search articles..." disabled>
                    <button class="yfnf-search-clear">×</button>
                </div>
                <# } #>
            </div>
            
            <div class="yfnf-accordion">
                
                <!-- First Sample Article -->
                <div class="yfnf-accordion-item">
                    <div class="yfnf-accordion-header">
                        <h4 class="yfnf-accordion-title">Sample Financial News: {{{ symbol }}} Reports Strong Quarterly Earnings</h4>
                        <div class="yfnf-accordion-meta">
                            <# if ( settings.show_date === 'yes' ) { #>
                            <span class="yfnf-date">2 hours ago</span>
                            <# } #>
                            <# if ( settings.show_source === 'yes' ) { #>
                            <span class="yfnf-source">Yahoo Finance</span>
                            <# } #>
                        </div>
                        <div class="yfnf-accordion-toggle">
                            <span class="yfnf-icon">▼</span>
                        </div>
                    </div>
                    <div class="yfnf-accordion-content">
                        <div class="yfnf-content-inner">
                            <# if ( content_mode === 'full' ) { #>
                                <p>This is a <strong>sample of full content mode</strong>. In this mode, the complete article content will be displayed within the accordion, keeping users engaged on your website without redirecting them to external sources.</p>
                                <p>The full content includes all paragraphs, detailed analysis, complete quotes from executives, financial data, market implications, and comprehensive coverage of the news story. This provides maximum value to your visitors while ensuring they remain on your site.</p>
                                <p>Key highlights from the earnings report include:</p>
                                <ul>
                                    <li>Revenue exceeded analyst expectations by 12%</li>
                                    <li>Strong performance in international markets</li>
                                    <li>Positive guidance for the upcoming quarter</li>
                                    <li>Strategic investments in emerging technologies</li>
                                </ul>
                                <p>Market analysts are responding positively to these results, with several firms raising their price targets. The company's strategic direction and execution continue to impress investors and industry observers alike.</p>
                            <# } else { #>
                                <p>This is a sample excerpt showing limited content. In excerpt mode, only a portion of the article will be displayed, typically the opening paragraphs or a summary of the key points...</p>
                            <# } #>
                            
                            <# if ( show_read_more === 'yes' ) { #>
                            <div class="yfnf-read-more">
                                <a href="#" onclick="return false;">
                                    Read Full Article →
                                </a>
                            </div>
                            <# } #>
                        </div>
                    </div>
                </div>
                
                <!-- Second Sample Article -->
                <div class="yfnf-accordion-item">
                    <div class="yfnf-accordion-header">
                        <h4 class="yfnf-accordion-title">Market Analysis: {{{ symbol }}} Stock Performance and Future Outlook</h4>
                        <div class="yfnf-accordion-meta">
                            <# if ( settings.show_date === 'yes' ) { #>
                            <span class="yfnf-date">4 hours ago</span>
                            <# } #>
                            <# if ( settings.show_source === 'yes' ) { #>
                            <span class="yfnf-source">MarketWatch</span>
                            <# } #>
                        </div>
                        <div class="yfnf-accordion-toggle">
                            <span class="yfnf-icon">▼</span>
                        </div>
                    </div>
                    <div class="yfnf-accordion-content">
                        <div class="yfnf-content-inner">
                            <p>Preview of second news article content. The actual content will be fetched from the RSS feed and displayed according to your settings.</p>
                            
                            <# if ( show_read_more === 'yes' ) { #>
                            <div class="yfnf-read-more">
                                <a href="#" onclick="return false;">
                                    Read Full Article →
                                </a>
                            </div>
                            <# } #>
                        </div>
                    </div>
                </div>
                
                <!-- Third Sample Article -->
                <div class="yfnf-accordion-item">
                    <div class="yfnf-accordion-header">
                        <h4 class="yfnf-accordion-title">Breaking: {{{ symbol }}} Announces Strategic Partnership</h4>
                        <div class="yfnf-accordion-meta">
                            <# if ( settings.show_date === 'yes' ) { #>
                            <span class="yfnf-date">6 hours ago</span>
                            <# } #>
                            <# if ( settings.show_source === 'yes' ) { #>
                            <span class="yfnf-source">Reuters</span>
                            <# } #>
                        </div>
                        <div class="yfnf-accordion-toggle">
                            <span class="yfnf-icon">▼</span>
                        </div>
                    </div>
                    <div class="yfnf-accordion-content">
                        <div class="yfnf-content-inner">
                            <p>Another sample article showing how the news feed will display multiple articles with consistent formatting and styling.</p>
                        </div>
                    </div>
                </div>
                
            </div>
            
            <# if ( settings.show_pagination === 'yes' ) { #>
            <div class="yfnf-pagination">
                <button class="yfnf-btn yfnf-prev" disabled>← Previous</button>
                <span class="yfnf-page-info">Page 1 of 3</span>
                <button class="yfnf-btn yfnf-next">Next →</button>
                <button class="yfnf-btn yfnf-load-more">Load More</button>
            </div>
            <# } #>
            
        </div>
        
        <!-- Configuration Summary -->
        <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; margin-top: 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
            <h4 style="margin: 0 0 15px 0; color: #495057; font-size: 16px;">📊 Widget Configuration Summary</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; font-size: 14px;">
                <div>
                    <strong>Stock Symbol:</strong> {{{ symbol }}}<br>
                    <strong>Articles Per Page:</strong> {{{ per_page }}}<br>
                    <strong>Content Mode:</strong> {{{ content_mode }}}<br>
                    <strong>Show Read More:</strong> {{{ show_read_more }}}
                </div>
                <div>
                    <strong>Theme Style:</strong> {{{ style }}}<br>
                    <strong>Show Pagination:</strong> {{{ settings.show_pagination }}}<br>
                    <strong>Enable Search:</strong> {{{ show_search }}}<br>
                    <strong>Single Accordion:</strong> {{{ settings.single_accordion }}}
                </div>
            </div>
            
            <# if ( content_mode === 'full' && show_read_more === 'no' ) { #>
            <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 12px; border-radius: 6px; margin-top: 15px;">
                <strong>✅ Perfect Configuration!</strong> You're using <strong>Full Content</strong> mode with <strong>Read More button hidden</strong>. This setup maximizes user retention by showing complete articles within the accordion.
            </div>
            <# } else if ( content_mode === 'excerpt' && show_read_more === 'yes' ) { #>
            <div style="background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 12px; border-radius: 6px; margin-top: 15px;">
                <strong>⚠️ Standard Configuration:</strong> You're using <strong>Excerpt</strong> mode with <strong>Read More button</strong>. This may lead users away from your site.
            </div>
            <# } else { #>
            <div style="background: #cce5ff; border: 1px solid #99d6ff; color: #004085; padding: 12px; border-radius: 6px; margin-top: 15px;">
                <strong>ℹ️ Current Configuration:</strong> Content mode: <strong>{{{ content_mode }}}</strong>, Read More: <strong>{{{ show_read_more }}}</strong>
            </div>
            <# } #>
        </div>
        <?php
    }
    
    public function render_plain_content() {
        // For export functionality
        echo 'Yahoo Finance News Feed Widget';
    }
}
?>