# OSBuzz E-commerce Project

## Project Structure

```
osbuzz/
├── app/                    # Main application directory
│   ├── base.php           # Database connection and core functions
│   ├── head.php           # Common header template
│   ├── foot.php           # Common footer template  
│   ├── index.php          # Homepage
│   ├── signuphead.php     # Signup page header
│   ├── css/               # Stylesheets
│   │   ├── app.css        # Main application styles
│   │   └── profile-edit.css # Profile editing styles
│   ├── js/                # JavaScript files
│   │   └── app.js         # Main application scripts
│   ├── images/            # Image assets
│   │   ├── default-avatar.png
│   │   ├── logo.png
│   │   ├── Menu/          # Menu category images
│   │   └── Products/      # Product images
│   ├── lib/               # PHP libraries
│   │   ├── SimpleImage.php # Image processing
│   │   └── SimplePager.php # Pagination
│   ├── photos/            # User uploaded photos
│   └── page/              # Application pages (organized by functionality)
│       ├── admin/         # Admin panel pages
│       │   ├── admin_product.php
│       │   ├── admin_product_add.php
│       │   ├── admin_product_edit.php
│       │   ├── admin_product_delete.php
│       │   ├── admin_product_variants.php
│       │   ├── admin_user.php
│       │   ├── admin_user_add.php
│       │   ├── admin_user_edit.php
│       │   ├── admin_user_delete.php
│       │   ├── variantAdd.php
│       │   ├── variantEdit.php
│       │   └── variantDelete.php
│       ├── categories/    # Product category pages
│       │   ├── basketball.php
│       │   ├── casual.php
│       │   ├── formal.php
│       │   ├── running.php
│       │   └── other.php
│       ├── user/          # User account management
│       │   ├── login.php
│       │   ├── logout.php
│       │   ├── signup.php
│       │   ├── signup_pass.php
│       │   ├── profile.php
│       │   ├── profileEdit.php
│       │   └── changePass.php
│       └── shop/          # Shopping and product features
│           ├── cart.php
│           ├── product_detail.php
│           ├── search.php
│           └── sales.php
├── database/              # Database files
│   └── osbuzz.sql         # Database schema
└── docs/                  # Documentation
    └── README.md          # This file
```

## Features

- **Multi-photo product system**: Products can have multiple photos with one designated as main
- **Category system**: Running, Casual, Formal, Basketball, Other categories
- **Admin panel**: Complete product and user management
- **User authentication**: Registration, login, profile management
- **Search functionality**: Product search with filters
- **Responsive design**: Mobile-friendly interface
- **Shopping cart**: Add to cart functionality

## Database Schema

### Products Table
- Multi-photo support via `product_photos` table
- Category-based organization
- Variant support for sizes and colors

### Users Table  
- User authentication and profiles
- Photo upload support

## Recent Updates

1. **CSS Cleanup**: Reduced CSS from 1369 to 800 lines (40% reduction)
2. **Multi-photo System**: Implemented product photo gallery functionality
3. **Category Alignment**: Aligned categories with database structure
4. **Header Redesign**: Modern responsive header with search functionality
5. **Navigation Update**: Sales-focused navigation system
