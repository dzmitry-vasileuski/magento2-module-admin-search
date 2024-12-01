define([
    'jquery',
    'ko',
    'underscore',
    'uiComponent'
], function ($, ko, _, Component) {
    'use strict';

    return Component.extend({
        query: ko.observable(''),
        hits: ko.observableArray([]),
        isActive: ko.observable(false),
        isLoading: ko.observable(false),

        defaults: {
            template: 'Vasileuski_AdminSearch/search',
            searchUrl: '',
            selectors: {
                search: '.search',
                input: '.search__input',
                results: '.search__results',
                placeholder: '.search._placeholder',
                hit: '.search__hit',
            },
            templates: {
                'cart-price-rule': 'Vasileuski_AdminSearch/hit/cart-price-rule',
                'catalog-price-rule': 'Vasileuski_AdminSearch/hit/catalog-price-rule',
                category: 'Vasileuski_AdminSearch/hit/category',
                customer: 'Vasileuski_AdminSearch/hit/customer',
                order: 'Vasileuski_AdminSearch/hit/order',
                page: 'Vasileuski_AdminSearch/hit/page',
                product: 'Vasileuski_AdminSearch/hit/product',
            }
        },

        initialize() {
            this._super();
            this.initObservers();
        },

        initObservable() {
            this._super();

            this.query.subscribe(_.debounce(this.search.bind(this), 300));
            this.hits.subscribe((hits) => {
                if (hits) {
                    setTimeout(() => $(this.selectors.hit).first().focus(), 100);
                }
            });
            this.isActive.subscribe((isActive) => {
                if (!isActive) {
                    this.query('');
                    $(this.selectors.input).blur();
                }
            });

            return this;
        },

        initObservers() {
            $(document).on('keydown', (event) => {
                if ((event.ctrlKey || this.isMacOS() && event.metaKey) && event.key === 'k') {
                    this.handleCtrlKPress(event);
                } else if (event.key === 'Escape') {
                    this.handleEscapePress(event);
                } else if (event.key === 'Enter') {
                    this.handleEnterPress(event);
                } else if (event.key === 'ArrowDown') {
                    this.handleArrowDownPress(event);
                } else if (event.key === 'ArrowUp') {
                    this.handleArrowUpPress(event);
                } else {
                    if (this.isActive() && !$(event.target).is(this.selectors.input)) {
                        $(this.selectors.input).focus();
                        $(this.selectors.input).trigger('keydown', event);
                    }
                }
            });

            $(document).on('click', this.handleClickOutside.bind(this));
            $(document).on('click', this.selectors.hit, this.handleHitClick.bind(this));
            $(document).on('focus', this.selectors.input, this.handleInputFocus.bind(this));
        },

        handleCtrlKPress(event) {
            event.preventDefault();
            $(this.selectors.input).focus();
        },

        handleEscapePress() {
            this.isActive(false);
        },

        handleEnterPress(event) {
            this.handleHitClick(event);
        },

        handleArrowDownPress(event) {
            if (!this.isActive()) {
                return;
            }

            event.preventDefault();

            const $activeElement = $(document.activeElement);

            if ($activeElement.is(this.selectors.hit)) {
                $activeElement.next(this.selectors.hit).focus();
            } else if ($activeElement.is(this.selectors.input)) {
                $(this.selectors.hit).first().focus();
            }
        },

        handleArrowUpPress(event) {
            if (!this.isActive()) {
                return;
            }

            event.preventDefault();

            const $activeElement = $(document.activeElement);

            if ($activeElement.is(this.selectors.hit)) {
                const $prev = $activeElement.prev(this.selectors.hit);

                if ($prev.length) {
                    $prev.focus();
                } else {
                    $(this.selectors.input).focus();
                }
            }
        },

        handleClickOutside(event) {
            if (!this.isActive()) {
                return;
            }

            const $target = $(event.target);

            if ($target.closest(this.selectors.search).length === 0) {
                this.isActive(false);
            }
        },

        handleHitClick(event) {
            const $target = $(event.target);
            let $hit;

            if ($target.is(this.selectors.hit)) {
                $hit = $target;
            } else if ($target.closest(this.selectors.hit)) {
                $hit = $target.closest(this.selectors.hit);
            }

            if (!$hit.length) {
                return;
            }

            window.location.href = $hit.data('href');
            this.isActive(false);
            $('body').trigger('processStart');
        },

        handleInputFocus() {
            this.isActive(true);
        },

        afterRender() {
            $(this.selectors.placeholder).remove();
        },

        search() {
            const query = this.query();

            if (query.trim().length === 0) {
                this.hits([]);
                return;
            }

            this.isLoading(true);

            $.post(this.searchUrl, { query }, (response) => {
                this.hits(response);
            }).always(() => this.isLoading(false));
        },

        getHitTemplate(hit) {
            return this.templates[hit._type];
        },

        isMacOS() {
            return /Mac OS/.test(navigator.userAgent);
        },
    });
});
