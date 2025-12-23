jQuery(document).ready(function($) {
    'use strict';

    /**
     * Frequently Bought Together Widget Handler
     */
    class FBTWidget {
        constructor(wrapper) {
            this.$wrapper = $(wrapper);
            this.$checkboxes = this.$wrapper.find('.fbt-product-checkbox');
            this.$totalAmount = this.$wrapper.find('.fbt-total-amount');
            this.$pointInfo = this.$wrapper.find('.fbt-point-info');
            this.$addToCartBtn = this.$wrapper.find('.fbt-add-to-cart-btn');
            this.$selectedCount = null;
            this.initialTotal = parseFloat(this.$totalAmount.data('total')) || 0;
            
            this.init();
        }

        init() {
            // Checkbox change event
            this.$checkboxes.on('change', (e) => this.handleCheckboxChange(e));
            
            // Add to cart button click event
            this.$addToCartBtn.on('click', () => this.handleAddToCart());
            
            // Add selected count badge if it doesn't exist
            this.addSelectedCountBadge();
            
            // Update selected count badge only (without recalculating price)
            const selectedCount = this.$checkboxes.filter(':checked').length;
            this.updateSelectedCount(selectedCount);
        }

        /**
         * Handle checkbox change
         */
        handleCheckboxChange(e) {
            const $checkbox = $(e.target);
            const $productItem = $checkbox.closest('.fbt-product-item');
            
            if ($checkbox.is(':checked')) {
                $productItem.removeClass('unchecked');
            } else {
                $productItem.addClass('unchecked');
            }
            
            this.updateTotal();
        }

        /**
         * Update total price and points
         */
        updateTotal() {
            let totalInclTax = 0;
            const selectedProducts = [];
            
            this.$checkboxes.each((index, checkbox) => {
                const $checkbox = $(checkbox);
                if ($checkbox.is(':checked')) {
                    // Use tax-included price
                    const priceInclTax = parseFloat($checkbox.data('price-incl-tax')) || 0;
                    totalInclTax += priceInclTax;
                    selectedProducts.push($checkbox.data('product-id'));
                }
            });
            
            // Update total amount (tax included)
            this.formatPrice(totalInclTax, this.$totalAmount);
            
            // Update points (1% of total) - only if point info element exists
            if (this.$pointInfo.length && this.$pointInfo.is(':visible')) {
                const points = Math.floor(totalInclTax * 0.01);
                this.$pointInfo.text(`ポイントの合計: ${points}pt`);
            }
            
            // Update selected count badge
            this.updateSelectedCount(selectedProducts.length);
            
            // Disable/enable button based on selection
            if (selectedProducts.length === 0) {
                this.$addToCartBtn.prop('disabled', true);
            } else {
                this.$addToCartBtn.prop('disabled', false);
            }
        }

        /**
         * Format price with WooCommerce format
         */
        formatPrice(price, $element) {
            // Use WordPress AJAX to format price properly
            $.ajax({
                url: fbtAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'fbt_format_price',
                    price: price,
                    nonce: fbtAjax.nonce
                },
                success: function(response) {
                    if (response.success && response.data.formatted) {
                        $element.html(response.data.formatted);
                    } else {
                        // Fallback formatting
                        $element.text('¥' + price.toLocaleString('ja-JP'));
                    }
                },
                error: function() {
                    // Fallback formatting
                    $element.text('¥' + price.toLocaleString('ja-JP'));
                }
            });
        }

        /**
         * Add selected count badge
         */
        addSelectedCountBadge() {
            const $label = this.$wrapper.find('.fbt-total-label');
            if ($label.length && !this.$selectedCount) {
                this.$selectedCount = $('<span class="fbt-selected-count"></span>');
                $label.after(this.$selectedCount);
            }
        }

        /**
         * Update selected count badge
         */
        updateSelectedCount(count) {
            if (this.$selectedCount) {
                this.$selectedCount.text(count);
            }
        }

        /**
         * Handle add to cart
         */
        handleAddToCart() {
            const selectedProductIds = [];
            
            this.$checkboxes.each((index, checkbox) => {
                const $checkbox = $(checkbox);
                if ($checkbox.is(':checked')) {
                    selectedProductIds.push($checkbox.data('product-id'));
                }
            });
            
            if (selectedProductIds.length === 0) {
                this.showMessage('商品を選択してください', 'error');
                return;
            }
            
            // Disable button and show loading state
            this.$addToCartBtn.prop('disabled', true).addClass('loading');
            
            // Remove previous messages
            this.$wrapper.find('.fbt-message').remove();
            
            // Send AJAX request
            $.ajax({
                url: fbtAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'fbt_add_to_cart',
                    product_ids: selectedProductIds,
                    nonce: fbtAjax.nonce
                },
                success: (response) => {
                    this.$addToCartBtn.removeClass('loading');
                    
                    if (response.success) {
                        this.showMessage(response.data.message, 'success');
                        
                        // Update cart count if WooCommerce cart widget exists
                        this.updateCartCount();
                        
                        // Optional: Redirect to cart after delay
                        if (response.data.cart_url) {
                            setTimeout(() => {
                                window.location.href = response.data.cart_url;
                            }, 1500);
                        }
                    } else {
                        this.showMessage(response.data.message || fbtAjax.error, 'error');
                        this.$addToCartBtn.prop('disabled', false);
                    }
                },
                error: () => {
                    this.$addToCartBtn.removeClass('loading').prop('disabled', false);
                    this.showMessage(fbtAjax.error, 'error');
                }
            });
        }

        /**
         * Show message
         */
        showMessage(message, type) {
            const $message = $('<div class="fbt-message ' + type + '">' + message + '</div>');
            this.$wrapper.find('.fbt-summary').append($message);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                $message.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }

        /**
         * Update WooCommerce cart count
         */
        updateCartCount() {
            // Trigger WooCommerce fragment refresh
            if (typeof wc_add_to_cart_params !== 'undefined') {
                $(document.body).trigger('wc_fragment_refresh');
            }
            
            // Also trigger added_to_cart event
            $(document.body).trigger('added_to_cart');
        }
    }

    /**
     * Initialize all FBT widgets on the page
     */
    function initFBTWidgets() {
        $('.fbt-widget-wrapper').each(function() {
            if (!$(this).data('fbt-initialized')) {
                new FBTWidget(this);
                $(this).data('fbt-initialized', true);
            }
        });
    }

    // Initialize on page load
    initFBTWidgets();

    // Re-initialize after Elementor preview refresh
    $(window).on('elementor/frontend/init', function() {
        elementorFrontend.hooks.addAction('frontend/element_ready/widget', function($scope) {
            if ($scope.find('.fbt-widget-wrapper').length) {
                initFBTWidgets();
            }
        });
    });

    // Re-initialize after AJAX content load (if needed)
    $(document).on('ajaxComplete', function() {
        initFBTWidgets();
    });

    /**
     * Product search functionality for Elementor editor
     * (This would be used if we implement live search in the editor control)
     */
    window.FBTProductSearch = {
        search: function(term, callback) {
            $.ajax({
                url: fbtAjax.ajaxurl,
                type: 'GET',
                data: {
                    action: 'fbt_search_products',
                    q: term,
                    nonce: fbtAjax.nonce
                },
                success: function(response) {
                    if (response.success && response.data) {
                        callback(response.data);
                    } else {
                        callback([]);
                    }
                },
                error: function() {
                    callback([]);
                }
            });
        }
    };

    /**
     * Accessibility improvements
     */
    function improveAccessibility() {
        // Add ARIA labels to checkboxes
        $('.fbt-product-checkbox').each(function() {
            const $checkbox = $(this);
            const $productTitle = $checkbox.closest('.fbt-product-item').find('.fbt-product-title');
            
            if ($productTitle.length && !$checkbox.attr('aria-label')) {
                $checkbox.attr('aria-label', 'この商品を選択: ' + $productTitle.text().trim());
            }
        });

        // Add keyboard support
        $('.fbt-product-item').on('keydown', function(e) {
            // Space key toggles checkbox
            if (e.key === ' ' && e.target === this) {
                e.preventDefault();
                $(this).find('.fbt-product-checkbox').trigger('click');
            }
        });
    }

    // Apply accessibility improvements
    improveAccessibility();

    /**
     * Smooth scroll to cart (optional enhancement)
     */
    $(document).on('click', '.fbt-add-to-cart-btn', function() {
        // Could add smooth scroll to cart or other UX enhancements here
    });

});

/**
 * Add AJAX handler for price formatting
 */
jQuery(document).ready(function($) {
    // This is handled on the server side via WordPress AJAX
});
