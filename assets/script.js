jQuery(document).ready(function ($) {
    "use strict";

    // Main YFNF object
    window.YFNF = {
        settings: {
            animationSpeed: 300,
            autoRefreshInterval: 300000, // 5 minutes
            loadMoreAnimation: true,
            singleAccordionMode: true
        },

        cache: {
            containers: null,
            accordionHeaders: null,
            paginationButtons: null
        },

        init: function () {
            this.cacheElements();
            this.bindEvents();
            this.initAccordion();
            this.initPagination();
            this.initKeyboardNavigation();
            this.initAutoRefresh();
            this.initSearchFilter();
            this.addLoadingStates();
            this.initAccessibility();
            this.initAnimations();
        },

        cacheElements: function () {
            this.cache.containers = $('.yfnf-container');
            this.cache.accordionHeaders = $('.yfnf-accordion-header');
            this.cache.paginationButtons = $('.yfnf-btn');
        },

        bindEvents: function () {
            var self = this;

            // Rebind events on dynamic content
            $(document).on('yfnf_content_loaded', function () {
                self.cacheElements();
                self.initAccordion();
                self.initPagination();
                self.initKeyboardNavigation();
                self.initAccessibility();
            });

            // Window resize handler
            $(window).on('resize', this.debounce(function () {
                self.handleResize();
            }, 250));

            // Visibility change handler for auto-refresh
            $(document).on('visibilitychange', function () {
                if (!document.hidden) {
                    self.refreshVisibleFeeds();
                }
            });
        },

        initAccordion: function () {
            var self = this;

            $('.yfnf-accordion-header').off('click.yfnf').on('click.yfnf', function (e) {
                e.preventDefault();
                e.stopPropagation();

                var $header = $(this);
                var $content = $header.next('.yfnf-accordion-content');
                var $container = $header.closest('.yfnf-container');
                var $item = $header.closest('.yfnf-accordion-item');

                // Check single accordion mode
                var singleMode = $container.data('single-mode') !== false;

                if (singleMode && !$header.hasClass('active')) {
                    // Close all other accordions
                    $container.find('.yfnf-accordion-header').not($header).removeClass('active');
                    $container.find('.yfnf-accordion-content').not($content).removeClass('active').slideUp(self.settings.animationSpeed);
                }

                // Toggle current accordion
                $header.toggleClass('active');

                if ($content.hasClass('active')) {
                    $content.removeClass('active').slideUp(self.settings.animationSpeed, function () {
                        $item.removeClass('expanded');
                    });
                } else {
                    $content.addClass('active').slideDown(self.settings.animationSpeed, function () {
                        $item.addClass('expanded');
                        // Scroll into view if needed
                        self.scrollToElement($item);
                    });
                }

                // Track analytics
                self.trackEvent('accordion_toggle', {
                    action: $content.hasClass('active') ? 'open' : 'close',
                    symbol: $container.data('symbol'),
                    content_mode: $container.data('content-mode'),
                    show_read_more: $container.data('show-read-more')
                });
            });
        },

        initPagination: function () {
            var self = this;

            // Use event delegation for pagination buttons
            $(document).off('click.yfnf-pagination').on('click.yfnf-pagination', '.yfnf-btn', function (e) {
                e.preventDefault();

                var $btn = $(this);
                var $container = $btn.closest('.yfnf-container');
                var page = $btn.data('page');
                var symbol = $container.data('symbol');
                var perPage = $container.data('per-page');
                var isLoadMore = $btn.hasClass('yfnf-load-more');
                var isPrev = $btn.hasClass('yfnf-prev');
                var isNext = $btn.hasClass('yfnf-next');

                if ($btn.prop('disabled') || $btn.hasClass('yfnf-loading')) return;

                // Determine the page number based on button type
                if (isLoadMore) {
                    page = parseInt($container.data('current-page')) + 1;
                } else if (isPrev) {
                    page = parseInt($container.data('current-page')) - 1;
                } else if (isNext) {
                    page = parseInt($container.data('current-page')) + 1;
                }

                if (!page || page < 1) return;

                self.loadPage($container, symbol, page, perPage, isLoadMore);
            });
        },

        loadPage: function ($container, symbol, page, perPage, isLoadMore) {
            var self = this;
            var $loading = $container.find('.yfnf-loading');
            var $pagination = $container.find('.yfnf-pagination');

            // Show loading state
            $loading.show();
            $pagination.find('.yfnf-btn').prop('disabled', true).addClass('yfnf-loading');

            // Add loading class to container
            $container.addClass('yfnf-loading-state');

            // Get container settings
            var showReadMore = $container.data('show-read-more') || 'no';
            var contentMode = $container.data('content-mode') || 'full';

            $.ajax({
                url: yfnf_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'yfnf_load_more',
                    symbol: symbol,
                    page: page,
                    per_page: perPage,
                    show_read_more: showReadMore,
                    content_mode: contentMode,
                    nonce: yfnf_ajax.nonce
                },
                timeout: 15000,
                success: function (response) {
                    if (response.success && response.data.html) {
                        self.handleSuccessfulLoad($container, response.data.html, page, isLoadMore);
                    } else {
                        self.showError($container, response.data ? response.data.message : 'Failed to load news.');
                    }
                },
                error: function (xhr, status, error) {
                    var message = 'Network error occurred.';
                    if (status === 'timeout') {
                        message = 'Request timed out. Please try again.';
                    } else if (xhr.status === 404) {
                        message = 'News service not found.';
                    } else if (xhr.status >= 500) {
                        message = 'Server error occurred.';
                    }
                    self.showError($container, message);
                },
                complete: function () {
                    $loading.hide();
                    $container.removeClass('yfnf-loading-state');
                    $pagination.find('.yfnf-btn').prop('disabled', false).removeClass('yfnf-loading');
                }
            });
        },

        handleSuccessfulLoad: function ($container, html, page, isLoadMore) {
            var self = this;
            var $newContent = $(html);

            if (isLoadMore) {
                // Append new items with animation
                var $newItems = $newContent.find('.yfnf-accordion-item');
                var $accordion = $container.find('.yfnf-accordion');

                if (self.settings.loadMoreAnimation) {
                    $newItems.hide().appendTo($accordion).each(function (index) {
                        var $item = $(this);
                        setTimeout(function () {
                            $item.fadeIn(self.settings.animationSpeed);
                        }, index * 100);
                    });
                } else {
                    $accordion.append($newItems);
                }

                // Update pagination
                var $newPagination = $newContent.find('.yfnf-pagination');
                if ($newPagination.length) {
                    $container.find('.yfnf-pagination').replaceWith($newPagination);
                } else {
                    $container.find('.yfnf-pagination').fadeOut();
                }

                // Update container data
                $container.data('current-page', page);

            } else {
                // Replace entire content for regular pagination
                var $currentAccordion = $container.find('.yfnf-accordion');
                $currentAccordion.fadeOut(self.settings.animationSpeed, function () {
                    $container.html($newContent.html());
                    $container.find('.yfnf-accordion').hide().fadeIn(self.settings.animationSpeed);

                    // Update container data attributes from the new content
                    var newSymbol = $newContent.data('symbol');
                    var newPerPage = $newContent.data('per-page');
                    var newCurrentPage = $newContent.data('current-page');
                    var newTotalPages = $newContent.data('total-pages');
                    var newShowReadMore = $newContent.data('show-read-more');
                    var newContentMode = $newContent.data('content-mode');

                    $container.data('symbol', newSymbol);
                    $container.data('per-page', newPerPage);
                    $container.data('current-page', newCurrentPage);
                    $container.data('total-pages', newTotalPages);
                    $container.data('show-read-more', newShowReadMore);
                    $container.data('content-mode', newContentMode);
                });

                // Scroll to top
                self.scrollToElement($container, -100);
            }

            // Trigger content loaded event
            $(document).trigger('yfnf_content_loaded');

            // Track analytics
            self.trackEvent('page_load', {
                symbol: $container.data('symbol'),
                page: page,
                load_type: isLoadMore ? 'load_more' : 'pagination',
                content_mode: $container.data('content-mode')
            });
        },

        showError: function ($container, message) {
            var errorHtml = '<div class="yfnf-error">' +
                '<strong>Error:</strong> ' + message +
                '<button class="yfnf-retry-btn" style="margin-left: 10px; padding: 5px 10px; background: #dc2626; color: white; border: none; border-radius: 4px; cursor: pointer;">Retry</button>' +
                '</div>';

            var $accordion = $container.find('.yfnf-accordion');
            if ($accordion.length) {
                $accordion.html(errorHtml);
            } else {
                $container.append(errorHtml);
            }

            // Bind retry functionality
            $container.find('.yfnf-retry-btn').on('click', function () {
                location.reload();
            });
        },

        initKeyboardNavigation: function () {
            var self = this;

            // Make accordion headers focusable
            $('.yfnf-accordion-header').attr('tabindex', '0').attr('role', 'button').attr('aria-expanded', 'false');

            $(document).off('keydown.yfnf').on('keydown.yfnf', function (e) {
                var $focused = $('.yfnf-accordion-header:focus');
                if ($focused.length === 0) return;

                switch (e.which) {
                    case 13: // Enter
                    case 32: // Space
                        e.preventDefault();
                        $focused.click();
                        break;
                    case 38: // Up arrow
                        e.preventDefault();
                        var $prev = $focused.closest('.yfnf-accordion-item').prev().find('.yfnf-accordion-header');
                        if ($prev.length) {
                            $prev.focus();
                        }
                        break;
                    case 40: // Down arrow
                        e.preventDefault();
                        var $next = $focused.closest('.yfnf-accordion-item').next().find('.yfnf-accordion-header');
                        if ($next.length) {
                            $next.focus();
                        }
                        break;
                    case 36: // Home
                        e.preventDefault();
                        $focused.closest('.yfnf-container').find('.yfnf-accordion-header').first().focus();
                        break;
                    case 35: // End
                        e.preventDefault();
                        $focused.closest('.yfnf-container').find('.yfnf-accordion-header').last().focus();
                        break;
                }
            });
        },

        initAutoRefresh: function () {
            var self = this;

            $('.yfnf-container').each(function () {
                var $container = $(this);
                var refreshInterval = $container.data('refresh-interval');

                if (refreshInterval && refreshInterval > 0) {
                    setInterval(function () {
                        if (!document.hidden) {
                            self.refreshFeed($container);
                        }
                    }, refreshInterval * 1000);
                }
            });
        },

        refreshFeed: function ($container) {
            var symbol = $container.data('symbol');
            var perPage = $container.data('per-page');
            var showReadMore = $container.data('show-read-more') || 'no';
            var contentMode = $container.data('content-mode') || 'full';

            // Silent refresh - don't show loading spinner
            $.ajax({
                url: yfnf_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'yfnf_load_more',
                    symbol: symbol,
                    page: 1,
                    per_page: perPage,
                    show_read_more: showReadMore,
                    content_mode: contentMode,
                    nonce: yfnf_ajax.nonce
                },
                success: function (response) {
                    if (response.success) {
                        // Check if content has changed
                        var $newContent = $(response.data.html);
                        var newTitle = $newContent.find('.yfnf-accordion-title').first().text();
                        var currentTitle = $container.find('.yfnf-accordion-title').first().text();

                        if (newTitle !== currentTitle) {
                            // Content has changed, show notification
                            $container.prepend('<div class="yfnf-update-notice">📰 New articles available! <button class="yfnf-refresh-btn">Refresh</button></div>');

                            $container.find('.yfnf-refresh-btn').on('click', function () {
                                $container.find('.yfnf-update-notice').remove();
                                $container.html($newContent.html());
                                $(document).trigger('yfnf_content_loaded');
                            });
                        }
                    }
                }
            });
        },

        refreshVisibleFeeds: function () {
            var self = this;
            $('.yfnf-container').each(function () {
                var $container = $(this);
                if (self.isElementVisible($container)) {
                    self.refreshFeed($container);
                }
            });
        },

        initSearchFilter: function () {
            var self = this;

            // Add search input to containers that have the search attribute
            $('.yfnf-container[data-search="true"]').each(function () {
                var $container = $(this);
                var searchHtml = '<div class="yfnf-search-wrapper">' +
                    '<input type="text" class="yfnf-search-input" placeholder="Search articles..." />' +
                    '<button class="yfnf-search-clear">×</button>' +
                    '</div>';

                $container.find('.yfnf-header').append(searchHtml);
            });

            // Search functionality
            $(document).on('input', '.yfnf-search-input', function () {
                var searchTerm = $(this).val().toLowerCase();
                var $container = $(this).closest('.yfnf-container');

                self.filterArticles($container, searchTerm);
            });

            // Clear search
            $(document).on('click', '.yfnf-search-clear', function () {
                var $input = $(this).siblings('.yfnf-search-input');
                var $container = $(this).closest('.yfnf-container');

                $input.val('');
                self.filterArticles($container, '');
            });
        },

        filterArticles: function ($container, searchTerm) {
            var $items = $container.find('.yfnf-accordion-item');
            var visibleCount = 0;

            $items.each(function () {
                var $item = $(this);
                var title = $item.find('.yfnf-accordion-title').text().toLowerCase();
                var content = $item.find('.yfnf-content-inner').text().toLowerCase();

                if (searchTerm === '' || title.includes(searchTerm) || content.includes(searchTerm)) {
                    $item.show();
                    visibleCount++;
                } else {
                    $item.hide();
                }
            });

            // Show/hide no results message
            var $noResults = $container.find('.yfnf-no-results');
            if (visibleCount === 0 && searchTerm !== '') {
                if ($noResults.length === 0) {
                    $container.find('.yfnf-accordion').append('<div class="yfnf-no-results">🔍 No articles found matching your search.</div>');
                }
            } else {
                $noResults.remove();
            }
        },

        initAccessibility: function () {
            // Add ARIA attributes
            $('.yfnf-accordion-header').each(function () {
                var $header = $(this);
                var $content = $header.next('.yfnf-accordion-content');
                var headerId = 'yfnf-header-' + Math.random().toString(36).substr(2, 9);
                var contentId = 'yfnf-content-' + Math.random().toString(36).substr(2, 9);

                $header.attr({
                    'id': headerId,
                    'aria-controls': contentId,
                    'aria-expanded': $header.hasClass('active') ? 'true' : 'false'
                });

                $content.attr({
                    'id': contentId,
                    'aria-labelledby': headerId,
                    'role': 'region'
                });
            });

            // Update ARIA attributes on toggle
            $(document).on('click', '.yfnf-accordion-header', function () {
                var $header = $(this);
                setTimeout(function () {
                    $header.attr('aria-expanded', $header.hasClass('active') ? 'true' : 'false');
                }, 50);
            });
        },

        initAnimations: function () {
            // Intersection Observer for fade-in animations
            if ('IntersectionObserver' in window) {
                var observer = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('yfnf-fade-in');
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.1,
                    rootMargin: '0px 0px -50px 0px'
                });

                $('.yfnf-accordion-item').each(function () {
                    observer.observe(this);
                });
            }
        },

        addLoadingStates: function () {
            $('.yfnf-btn').on('click', function () {
                var $btn = $(this);
                $btn.addClass('yfnf-loading').prop('disabled', true);

                setTimeout(function () {
                    $btn.removeClass('yfnf-loading').prop('disabled', false);
                }, 3000);
            });
        },

        handleResize: function () {
            // Adjust layout for mobile
            var isMobile = $(window).width() < 768;

            $('.yfnf-container').each(function () {
                var $container = $(this);
                $container.toggleClass('yfnf-mobile', isMobile);
            });
        },

        scrollToElement: function ($element, offset) {
            offset = offset || -20;

            if ($element.length) {
                $('html, body').animate({
                    scrollTop: $element.offset().top + offset
                }, this.settings.animationSpeed);
            }
        },

        isElementVisible: function ($element) {
            var elementTop = $element.offset().top;
            var elementBottom = elementTop + $element.outerHeight();
            var viewportTop = $(window).scrollTop();
            var viewportBottom = viewportTop + $(window).height();

            return elementBottom > viewportTop && elementTop < viewportBottom;
        },

        trackEvent: function (eventName, properties) {
            // Analytics tracking (Google Analytics, etc.)
            if (typeof gtag !== 'undefined') {
                gtag('event', eventName, properties);
            }

            // Custom tracking
            if (typeof window.yfnf_track === 'function') {
                window.yfnf_track(eventName, properties);
            }

            // Console log for debugging (remove in production)
            if (window.location.hostname === 'localhost' || window.location.hostname.includes('dev')) {
                console.log('YFNF Analytics:', eventName, properties);
            }
        },

        debounce: function (func, wait, immediate) {
            var timeout;
            return function () {
                var context = this, args = arguments;
                var later = function () {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                var callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        }
    };

    // Initialize the plugin
    YFNF.init();

    // Public API
    window.YahooFinanceNewsFeed = {
        refresh: function (symbol) {
            var $container = $('.yfnf-container[data-symbol="' + symbol + '"]');
            if ($container.length) {
                YFNF.refreshFeed($container);
            }
        },

        openAccordion: function (symbol, index) {
            var $container = $('.yfnf-container[data-symbol="' + symbol + '"]');
            var $header = $container.find('.yfnf-accordion-header').eq(index);
            if ($header.length && !$header.hasClass('active')) {
                $header.click();
            }
        },

        search: function (symbol, term) {
            var $container = $('.yfnf-container[data-symbol="' + symbol + '"]');
            var $searchInput = $container.find('.yfnf-search-input');
            if ($searchInput.length) {
                $searchInput.val(term).trigger('input');
            }
        },

        toggleContentMode: function (symbol, mode) {
            var $container = $('.yfnf-container[data-symbol="' + symbol + '"]');
            if ($container.length) {
                $container.data('content-mode', mode);
                YFNF.refreshFeed($container);
            }
        },

        toggleReadMore: function (symbol, show) {
            var $container = $('.yfnf-container[data-symbol="' + symbol + '"]');
            if ($container.length) {
                $container.data('show-read-more', show ? 'yes' : 'no');
                if (show) {
                    $container.find('.yfnf-read-more').show();
                } else {
                    $container.find('.yfnf-read-more').hide();
                }
            }
        },

        getSettings: function (symbol) {
            var $container = $('.yfnf-container[data-symbol="' + symbol + '"]');
            if ($container.length) {
                return {
                    symbol: $container.data('symbol'),
                    perPage: $container.data('per-page'),
                    currentPage: $container.data('current-page'),
                    totalPages: $container.data('total-pages'),
                    contentMode: $container.data('content-mode'),
                    showReadMore: $container.data('show-read-more')
                };
            }
            return null;
        }
    };

    // Auto-initialize new content (for dynamic loading)
    var observer = new MutationObserver(function (mutations) {
        var shouldReinit = false;
        mutations.forEach(function (mutation) {
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        var $node = $(node);
                        if ($node.hasClass('yfnf-container') || $node.find('.yfnf-container').length) {
                            shouldReinit = true;
                        }
                    }
                });
            }
        });

        if (shouldReinit) {
            setTimeout(function () {
                YFNF.init();
            }, 100);
        }
    });

    // Start observing
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    // Handle Elementor preview mode
    if (typeof elementorFrontend !== 'undefined') {
        elementorFrontend.hooks.addAction('frontend/element_ready/yahoo-finance-news.default', function ($scope) {
            // Reinitialize for this specific widget
            setTimeout(function () {
                YFNF.init();
            }, 100);
        });
    }

    // Content mode switcher for admin/preview
    $(document).on('click', '.yfnf-content-mode-switcher', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var $container = $btn.closest('.yfnf-container');
        var currentMode = $container.data('content-mode') || 'full';
        var newMode = currentMode === 'full' ? 'excerpt' : 'full';

        $container.data('content-mode', newMode);
        $btn.text(newMode === 'full' ? 'Switch to Excerpt' : 'Switch to Full');

        // Refresh content
        YFNF.refreshFeed($container);
    });

    // Read more toggle for admin/preview
    $(document).on('click', '.yfnf-read-more-toggle', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var $container = $btn.closest('.yfnf-container');
        var currentState = $container.data('show-read-more') || 'no';
        var newState = currentState === 'yes' ? 'no' : 'yes';

        $container.data('show-read-more', newState);
        $btn.text(newState === 'yes' ? 'Hide Read More' : 'Show Read More');

        // Toggle read more buttons
        if (newState === 'yes') {
            $container.find('.yfnf-read-more').show();
        } else {
            $container.find('.yfnf-read-more').hide();
        }
    });

    // Enhanced error handling with retry mechanism
    $(document).on('click', '.yfnf-retry-btn', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var $container = $btn.closest('.yfnf-container');
        var symbol = $container.data('symbol');

        $btn.prop('disabled', true).text('Retrying...');

        setTimeout(function () {
            YFNF.refreshFeed($container);
            $btn.closest('.yfnf-error').fadeOut(300, function () {
                $(this).remove();
            });
        }, 1000);
    });

    // Performance monitoring
    if (window.performance && window.performance.mark) {
        window.performance.mark('yfnf-init-start');

        $(window).on('load', function () {
            window.performance.mark('yfnf-init-end');
            window.performance.measure('yfnf-init-time', 'yfnf-init-start', 'yfnf-init-end');

            var measure = window.performance.getEntriesByName('yfnf-init-time')[0];
            if (measure && window.console) {
                console.log('YFNF initialization time:', Math.round(measure.duration), 'ms');
            }
        });
    }

    // Handle offline/online states
    $(window).on('online', function () {
        $('.yfnf-container').each(function () {
            var $container = $(this);
            if ($container.find('.yfnf-error').length) {
                YFNF.refreshFeed($container);
            }
        });
    });

    $(window).on('offline', function () {
        $('.yfnf-loading').hide();
        $('.yfnf-container').removeClass('yfnf-loading-state');
    });

    // Add smooth scroll polyfill for older browsers
    if (!('scrollBehavior' in document.documentElement.style)) {
        YFNF.scrollToElement = function ($element, offset) {
            offset = offset || -20;
            if ($element.length) {
                $('html, body').animate({
                    scrollTop: $element.offset().top + offset
                }, YFNF.settings.animationSpeed);
            }
        };
    }
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = window.YFNF;
}