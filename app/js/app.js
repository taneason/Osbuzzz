// ============================================================================
// General Functions
// ============================================================================



// ============================================================================
// Page Load (jQuery)
// ============================================================================

$(() => {

    // Autofocus
    $('form :input:not(button):first').focus();
    $('.err:first').prev().focus();
    $('.err:first').prev().find(':input:first').focus();
    
    // Confirmation message
    $('[data-confirm]').on('click', e => {
        const text = e.target.dataset.confirm || 'Are you sure?';
        if (!confirm(text)) {
            e.preventDefault();
            e.stopImmediatePropagation();
        }
    });

    // Initiate GET request
    $('[data-get]').on('click', e => {
        e.preventDefault();
        const url = e.target.dataset.get;
        location = url || location;
    });

    // Initiate POST request
    $('[data-post]').on('click', e => {
        e.preventDefault();
        const url = e.target.dataset.post;
        const f = $('<form>').appendTo(document.body)[0];
        f.method = 'POST';
        f.action = url || location;
        f.submit();
    });

    // Reset form
    $('[type=reset]').on('click', e => {
        e.preventDefault();
        location = location;
    });

    // Auto uppercase
    $('[data-upper]').on('input', e => {
        const a = e.target.selectionStart;
        const b = e.target.selectionEnd;
        e.target.value = e.target.value.toUpperCase();
        e.target.setSelectionRange(a, b);
    });

    // Shop filters functionality
    window.updateShopFilters = function() {
        const sort = $('#sort-filter').val();
        const category = $('#category-filter').val();
        const minPrice = $('#min-price').val();
        const maxPrice = $('#max-price').val();
        
        let url = '?page=1';
        
        // For sales page
        if (category && category !== '0') url += '&category=' + category;
        if (sort && sort !== 'newest') url += '&sort=' + sort;
        if (minPrice) url += '&min_price=' + minPrice;
        if (maxPrice) url += '&max_price=' + maxPrice;
        
        window.location.href = url;
    };

    // Category page filters
    window.updateCategoryFilters = function(categoryId) {
        const sort = $('#sort-filter').val();
        const minPrice = $('#min-price').val();
        const maxPrice = $('#max-price').val();
        
        let url = '?id=' + categoryId + '&page=1';
        if (sort && sort !== 'newest') url += '&sort=' + sort;
        if (minPrice) url += '&min_price=' + minPrice;
        if (maxPrice) url += '&max_price=' + maxPrice;
        
        window.location.href = url;
    };

    // Search page filters
    window.updateSearchFilters = function(searchQuery) {
        const category = $('#category-filter').val();
        const sort = $('#sort-filter').val();
        const minPrice = $('#min-price').val();
        const maxPrice = $('#max-price').val();
        
        let url = '?q=' + encodeURIComponent(searchQuery) + '&page=1';
        if (category && category !== '0') url += '&category=' + category;
        if (sort && sort !== 'newest') url += '&sort=' + sort;
        if (minPrice) url += '&min_price=' + minPrice;
        if (maxPrice) url += '&max_price=' + maxPrice;
        
        window.location.href = url;
    };

    // Clear filters functionality
    window.clearShopFilters = function() {
        window.location.href = '?page=1';
    };

    window.clearCategoryFilters = function(categoryId) {
        window.location.href = '?id=' + categoryId;
    };

    window.clearSearchFilters = function(searchQuery) {
        window.location.href = '?q=' + encodeURIComponent(searchQuery);
    };

    // Filter controls event handlers
    $(document).on('change', '#sort-filter, #category-filter', function() {
        const pathname = window.location.pathname;
        if (pathname.includes('sales.php')) {
            updateShopFilters();
        } else if (pathname.includes('category.php')) {
            const categoryId = new URLSearchParams(window.location.search).get('id');
            updateCategoryFilters(categoryId);
        } else if (pathname.includes('search.php')) {
            const searchQuery = new URLSearchParams(window.location.search).get('q');
            updateSearchFilters(searchQuery);
        }
    });

    // Apply filters button
    $(document).on('click', '.filter-btn', function() {
        const pathname = window.location.pathname;
        if (pathname.includes('sales.php')) {
            updateShopFilters();
        } else if (pathname.includes('category.php')) {
            const categoryId = new URLSearchParams(window.location.search).get('id');
            updateCategoryFilters(categoryId);
        } else if (pathname.includes('search.php')) {
            const searchQuery = new URLSearchParams(window.location.search).get('q');
            updateSearchFilters(searchQuery);
        }
    });

    // Clear filters button
    $(document).on('click', '.clear-btn', function() {
        const pathname = window.location.pathname;
        if (pathname.includes('sales.php')) {
            clearShopFilters();
        } else if (pathname.includes('category.php')) {
            const categoryId = new URLSearchParams(window.location.search).get('id');
            clearCategoryFilters(categoryId);
        } else if (pathname.includes('search.php')) {
            const searchQuery = new URLSearchParams(window.location.search).get('q');
            clearSearchFilters(searchQuery);
        }
    });


});