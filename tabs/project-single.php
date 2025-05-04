<?php
/**
 * تب تنظیمات ادامه مطلب پروژه‌ها
 */

if (!defined('ABSPATH'))
    exit;

$nader_settings = Nader_Settings::instance();

// ثبت تب جدید
$nader_settings->register_tab([
    'id'    => 'project_continue',
    'title' => 'ادامه مطلب پروژه‌ها',
    'order' => 8
]);

// ثبت ماژول‌ها
$nader_settings->register_module_config([
    'name'  => 'project_author_button',
    'type'  => 'toggle',
    'title' => 'نمایش نویسنده پروژه',
]);

$nader_settings->register_module_config([
    'name'  => 'project_views_button',
    'type'  => 'toggle',
    'title' => 'نمایش بازدید پروژه',
]);

$nader_settings->register_module_config([
    'name'  => 'project_comments_button',
    'type'  => 'toggle',
    'title' => 'نمایش نظرات پروژه',
]);

$nader_settings->register_module_config([
    'name'     => 'project_pages',
    'type'     => 'choose',
    'title'    => 'برگه پروژه‌ها',
    'multiple' => false,
    'query'    => [
        'type'      => 'post',
        'post_type' => 'page'
    ],
]);

$nader_settings->register_module_config([
    'name'  => 'content_tab_title',
    'type'  => 'text',
    'title' => 'عنوان تب محتوا',
]);

$nader_settings->register_module_config([
    'name'  => 'order_form_tab_title',
    'type'  => 'text',
    'title' => 'عنوان تب فرم سفارش',
]);

$nader_settings->register_module_config([
    'name'     => 'project_order_form',
    'type'     => 'choose',
    'title'    => 'فرم سفارش پروژه',
    'multiple' => false,
    'query'    => [
        'type'      => 'post',
        'post_type' => 'page'
    ],
]);

$nader_settings->register_module_config([
    'name'   => 'project_custom_fields',
    'type'   => 'repeater',
    'title'  => 'زمینه های دلخواه پروژه',
    'fields' => [
        [
            'name'     => 'title',
            'type'     => 'text',
            'title'    => 'عنوان آیکون',
            'required' => true
        ],
        [
            'name'        => 'key',
            'type'        => 'text',
            'required'    => true,
            'title'       => 'کلید شناسایی',
            'pattern'     => '^[a-z0-9_]+$',
            'description' => 'فقط حروف کوچک، اعداد و زیرخط مجاز است'
        ],
        [
            'name'     => 'label',
            'type'     => 'text',
            'required' => true,
            'title'    => 'برچسب نمایشی'
        ],
        [
            'name'        => 'icon_code',
            'type'        => 'textarea',
            'title'       => 'کد آیکون',
            'description' => 'کد SVG آیکون',
            'required'    => true,
            'rows'        => 3
        ]
    ]
]);


// رندر محتوای تب
add_action('nader_settings_tab_project_continue', function($nader_settings_instance) {
    // بررسی وجود کلاس‌های ماژول
    if (!class_exists('Nader_Toggle') || !class_exists('Nader_Choose') || !class_exists('Nader_Text')) {
        echo '<p>خطا در بارگذاری ماژول‌های ضروری</p>';
        return;
    }

    (new Nader_Toggle([
        'name'      => 'project_author_button',
        'title'     => 'نمایش نویسنده پروژه',
        'default'   => 1,
        'label_on'  => 'فعال',
        'label_off' => 'غیرفعال'
    ]))->render();
    echo '<hr>';

    (new Nader_Toggle([
        'name'    => 'project_views_button',
        'title'   => 'نمایش بازدید پروژه',
        'default' => 1
    ]))->render();
    echo '<hr>';

    (new Nader_Toggle([
        'name'    => 'project_comments_button',
        'title'   => 'نمایش نظرات پروژه',
        'default' => 1
    ]))->render();
    echo '<hr>';

    // انتخاب برگه پروژه‌ها
    (new Nader_Choose([
        'name'        => 'project_pages',
        'title'       => 'برگه‌ لیست پروژه ها',
        'multiple'    => false,
        'query'       => [
            'type'      => 'post',
            'post_type' => 'page'
        ],
        'placeholder' => 'جستجوی برگه...'
    ]))->render();
    echo '<hr>';

    // عنوان تب محتوا
    (new Nader_Text([
        'name'        => 'content_tab_title',
        'title'       => 'عنوان بخش محتوا',
        'placeholder' => 'عنوان دلخواه را وارد کنید',
        'default'     => 'جزئیات پروژه'
    ]))->render();
    echo '<hr>';

    // عنوان تب فرم سفارش
    (new Nader_Text([
        'name'        => 'order_form_tab_title',
        'title'       => 'عنوان بخش سفارش',
        'placeholder' => 'عنوان دلخواه را وارد کنید',
        'default'     => 'سفارش پروژه'
    ]))->render();
    echo '<hr>';

    // انتخاب فرم سفارش پروژه
    (new Nader_Choose([
        'name'        => 'project_order_form',
        'title'       => 'فرم سفارش پروژه',
        'description' => 'صفحه مربوط به فرم درخواست پروژه',
        'multiple'    => false,
        'query'       => [
            'type'      => 'post',
            'post_type' => 'page'
        ],
        'placeholder' => 'جستجوی فرم...'
    ]))->render();

    (new Nader_Repeater([
        'name'        => 'project_custom_fields',
        'type'        => 'repeater',
        'title'       => 'زمینه های دلخواه پروژه',
        'description' => 'زمینه های دلخواه پروژه ها را اینجا مدیریت کنید.',
        'fields'      => [
            [
                'name'     => 'title',
                'type'     => 'text',
                'title'    => 'عنوان آیکون',
                'required' => true
            ],
            [
                'name'        => 'key',
                'type'        => 'text',
                'title'       => 'کلید شناسایی',
                'pattern'     => '^[a-z0-9_]+$',
                'required'    => true,
                'description' => 'فقط حروف کوچک، اعداد و زیرخط مجاز است'
            ],
            [
                'name'     => 'label',
                'type'     => 'text',
                'required' => true,
                'title'    => 'برچسب نمایشی'
            ],
            [
                'name'        => 'icon_code',
                'type'        => 'textarea',
                'title'       => 'کد آیکون',
                'description' => 'کد SVG آیکون',
                'required'    => true,
                'rows'        => 3
            ]
        ]
    ]))->render();

}, 10, 1);