<?php
/**
 * تب تنظیمات فروشگاه
 */

if (!defined('ABSPATH'))
    exit;

$nader_settings = Nader_Settings::instance();

// ثبت تب جدید
$nader_settings->register_tab([
    'id'    => 'shop',
    'title' => 'فروشگاه',
    'order' => 6
]);

// ثبت ماژول‌ها
$nader_settings->register_module_config([
    'name'        => 'ajax_add_to_cart',
    'type'        => 'toggle',
    'title'       => 'افزودن به سبد خرید ایجکسی',
    'description' => 'فعالسازی افزودن به سبد خرید بدون ریلود صفحه',
    'default'     => 1
]);

$nader_settings->register_module_config([
    'name'    => 'archive_products_per_page',
    'type'    => 'number',
    'title'   => 'تعداد محصولات در صفحه آرشیو',
    'default' => 12,
    'min'     => 1,
    'max'     => 100
]);

// ثبت استایل‌های کارت محصول
$product_styles = [
    'style1' => 'استایل مدرن',
    'style2' => 'استایل مینیمال',
    'style3' => 'استایل کلاسیک'
];

$nader_settings->register_module_config([
    'name'    => 'archive_product_style',
    'type'    => 'select',
    'title'   => 'استایل کارت محصول (آرشیو)',
    'options' => $product_styles,
    'default' => 'style1'
]);

$nader_settings->register_module_config([
    'name'    => 'related_product_style',
    'type'    => 'select',
    'title'   => 'استایل کارت محصول مرتبط',
    'options' => $product_styles,
    'default' => 'style2'
]);

$nader_settings->register_module_config([
    'name'    => 'cart_related_style',
    'type'    => 'select',
    'title'   => 'استایل محصولات مشابه (سبد خرید)',
    'options' => $product_styles,
    'default' => 'style3'
]);

$nader_settings->register_module_config([
    'name'        => 'free_product_text',
    'type'        => 'text',
    'title'       => 'متن جایگزین قیمت صفر',
    'default'     => 'رایگان',
    'placeholder' => 'متن پیشفرض: رایگان'
]);

// بخش مدیریت محصولات بدون قیمت
$nader_settings->register_module_config([
    'name'    => 'empty_price_behavior',
    'type'    => 'radio',
    'title'   => 'رفتار با محصولات بدون قیمت',
    'options' => [
        'none'   => 'عدم نمایش',
        'text'   => 'نمایش متن',
        'button' => 'نمایش دکمه'
    ],
    'default' => 'none'
]);

$nader_settings->register_module_config([
    'name'         => 'empty_price_text',
    'type'         => 'text',
    'title'        => 'متن جایگزین',
    'default'      => 'تماس بگیرید',
    'dependencies' => [
        'relation' => 'AND',
        'rules'    => [
            [
                'field'    => 'empty_price_behavior',
                'operator' => '==',
                'value'    => 'text'
            ]
        ]
    ]
]);

$nader_settings->register_module_config([
    'name'         => 'empty_price_button',
    'type'         => 'text',
    'title'        => 'عنوان دکمه',
    'default'      => 'درخواست قیمت',
    'dependencies' => [
        'relation' => 'AND',
        'rules'    => [
            [
                'field'    => 'empty_price_behavior',
                'operator' => '==',
                'value'    => 'button'
            ]
        ]
    ]
]);

$nader_settings->register_module_config([
    'name'         => 'empty_price_button_url',
    'type'         => 'url',
    'title'        => 'لینک دکمه',
    'required' => true,
    'dependencies' => [
        'relation' => 'AND',
        'rules'    => [
            [
                'field'    => 'empty_price_behavior',
                'operator' => '==',
                'value'    => 'button'
            ]
        ]
    ]
]);

// بخش مقایسه محصولات
$nader_settings->register_module_config([
    'name'    => 'enable_product_comparison',
    'type'    => 'toggle',
    'title'   => 'فعالسازی مقایسه محصولات',
    'default' => 0
]);

$nader_settings->register_module_config([
    'name'         => 'comparison_page',
    'type'         => 'choose',
    'title'        => 'صفحه مقایسه محصولات',
    'query'        => [
        'type'      => 'post',
        'post_type' => 'page'
    ],
    'dependencies' => [
        'relation' => 'AND',
        'rules'    => [
            [
                'field'    => 'enable_product_comparison',
                'operator' => '==',
                'value'    => '1'
            ]
        ]
    ]
]);

add_action('nader_settings_tab_shop', function($nader_settings_instance) {
    // بخش عمومی
    (new Nader_Toggle([
        'name'        => 'ajax_add_to_cart',
        'title'       => 'افزودن به سبد خرید ایجکسی',
        'description' => 'فعالسازی افزودن به سبد خرید بدون ریلود صفحه',
        'default'     => 1
    ]))->render();

    (new Nader_Text([
        'name'    => 'archive_products_per_page',
        'title'   => 'تعداد محصولات در صفحه آرشیو',
        'default' => 12,
        'type'    => 'number',
        'min'     => 1,
        'max'     => 100
    ]))->render();

    $product_styles = [
        'style1' => 'استایل مدرن',
        'style2' => 'استایل مینیمال',
        'style3' => 'استایل کلاسیک'
    ];

    (new Nader_Select([
        'name'    => 'archive_product_style',
        'title'   => 'استایل کارت محصول (آرشیو)',
        'options' => $product_styles,
        'default' => 'style1'
    ]))->render();

    (new Nader_Select([
        'name'    => 'related_product_style',
        'title'   => 'استایل کارت محصول مرتبط',
        'options' => $product_styles,
        'default' => 'style2'
    ]))->render();

    (new Nader_Select([
        'name'    => 'cart_related_style',
        'title'   => 'استایل محصولات مشابه (سبد خرید)',
        'options' => $product_styles,
        'default' => 'style3'
    ]))->render();

    (new Nader_Text([
        'name'        => 'free_product_text',
        'title'       => 'متن جایگزین قیمت صفر',
        'default'     => 'رایگان',
        'placeholder' => 'متن پیشفرض: رایگان'
    ]))->render();

    echo '<div class="nader-section-divider"></div>';

    // بخش محصولات بدون قیمت
    $price_behavior = new Nader_Radio([
        'name'    => 'empty_price_behavior',
        'title'   => 'رفتار با محصولات بدون قیمت',
        'options' => [
            'none'   => 'عدم نمایش',
            'text'   => 'نمایش متن',
            'button' => 'نمایش دکمه'
        ],
        'default' => 'none'
    ]);
    $price_behavior->render();

    (new Nader_Text([
        'name'         => 'empty_price_text',
        'title'        => 'متن جایگزین',
        'default'      => 'تماس بگیرید',
        'dependencies' => [
            'relation' => 'AND',
            'rules'    => [
                [
                    'field'    => 'empty_price_behavior',
                    'operator' => '==',
                    'value'    => 'text'
                ]
            ]
        ]
    ]))->render();

    (new Nader_Text([
        'name'         => 'empty_price_button',
        'title'        => 'عنوان دکمه',
        'default'      => 'درخواست قیمت',
        'dependencies' => [
            'relation' => 'AND',
            'rules'    => [
                [
                    'field'    => 'empty_price_behavior',
                    'operator' => '==',
                    'value'    => 'button'
                ]
            ]
        ]
    ]))->render();

    (new Nader_Text([
        'name'         => 'empty_price_button_url',
        'title'        => 'لینک دکمه',
        'type'         => 'url',
        'required' => true,
        'dependencies' => [
            'relation' => 'AND',
            'rules'    => [
                [
                    'field'    => 'empty_price_behavior',
                    'operator' => '==',
                    'value'    => 'button'
                ]
            ]
        ]
    ]))->render();

    echo '<div class="nader-section-divider"></div>';

    // بخش مقایسه محصولات
    $comparison_toggle = new Nader_Toggle([
        'name'    => 'enable_product_comparison',
        'title'   => 'فعالسازی مقایسه محصولات',
        'default' => 0
    ]);
    $comparison_toggle->render();

    (new Nader_Choose([
        'name'         => 'comparison_page',
        'title'        => 'صفحه مقایسه محصولات',
        'query'        => [
            'type'      => 'post',
            'post_type' => 'page'
        ],
        'dependencies' => [
            'relation' => 'AND',
            'rules'    => [
                [
                    'field'    => 'enable_product_comparison',
                    'operator' => '==',
                    'value'    => '1'
                ]
            ]
        ]
    ]))->render();

}, 10, 1);