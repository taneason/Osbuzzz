<?php
/**
 * OSBuzz E-commerce Configuration
 * 
 * This file contains project-wide configuration settings
 */

// Project Information
define('PROJECT_NAME', 'OSBuzz');
define('PROJECT_VERSION', '2.0.0');
define('PROJECT_DESCRIPTION', 'Modern E-commerce Platform for Sports Footwear');

// File Structure Configuration
define('APP_ROOT', __DIR__ . '/app');
define('PAGES_ROOT', APP_ROOT . '/page');
define('ADMIN_ROOT', PAGES_ROOT . '/admin');
define('CSS_ROOT', APP_ROOT . '/css');
define('JS_ROOT', APP_ROOT . '/js');
define('IMAGES_ROOT', APP_ROOT . '/images');
define('PHOTOS_ROOT', APP_ROOT . '/photos');
define('LIB_ROOT', APP_ROOT . '/lib');
define('DATABASE_ROOT', __DIR__ . '/database');
define('DOCS_ROOT', __DIR__ . '/docs');

// Category Configuration
$categories = [
    'running' => 'Running',
    'casual' => 'Casual', 
    'formal' => 'Formal',
    'basketball' => 'Basketball',
    'other' => 'Other'
];

// Feature Flags
define('MULTI_PHOTO_ENABLED', true);
define('ADMIN_PANEL_ENABLED', true);
define('USER_REGISTRATION_ENABLED', true);
define('SEARCH_ENABLED', true);
define('CART_ENABLED', true);

// File Upload Configuration
define('MAX_PHOTO_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_PHOTO_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('MAX_PHOTOS_PER_PRODUCT', 10);

// Development Settings
define('DEBUG_MODE', true);
define('SHOW_SQL_ERRORS', true);

// Version History
$version_history = [
    '2.0.0' => [
        'date' => '2024-01-15',
        'changes' => [
            'CSS cleanup and optimization (40% reduction)',
            'Multi-photo product system implementation',
            'Category system alignment with database',
            'Header redesign with search functionality',
            'File structure reorganization',
            'Admin panel enhancements'
        ]
    ],
    '1.0.0' => [
        'date' => '2023-12-01',
        'changes' => [
            'Initial project setup',
            'Basic product management',
            'User authentication system',
            'Shopping cart functionality'
        ]
    ]
];
?>
