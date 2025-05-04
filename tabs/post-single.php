<?php
/**
 * تب تنظیمات ادامه مطلب نوشته‌ها
 */

if (!defined('ABSPATH'))
    exit;

$nader_settings = Nader_Settings::instance();

// ثبت تب جدید
$nader_settings->register_tab([
    'id'    => 'post_continue',
    'title' => 'ادامه مطلب نوشته‌ها',
    'order' => 7
]);

// ثبت ماژول‌ها
$nader_settings->register_module_config([
    'name'        => 'post_single_author',
    'type'        => 'toggle',
    'title'       => 'نویسنده',
    'default'     => 1
]);

$nader_settings->register_module_config([
    'name'        => 'post_single_category',
    'type'        => 'toggle',
    'title'       => 'دسته‌بندی',
    'default'     => 1
]);

$nader_settings->register_module_config([
    'name'        => 'post_single_views',
    'type'        => 'toggle',
    'title'       => 'بازدید',
    'default'     => 1
]);

$nader_settings->register_module_config([
    'name'        => 'post_single_date',
    'type'        => 'toggle',
    'title'       => 'تاریخ',
    'default'     => 1
]);

$nader_settings->register_module_config([
    'name'        => 'post_single_comments',
    'type'        => 'toggle',
    'title'       => 'نظرات',
    'default'     => 1
]);

$nader_settings->register_module_config([
    'name'        => 'continue_pages',
    'type'        => 'choose',
    'title'       => 'انتخاب برگه وبلاگ',
    'multiple'    => false,
    'query'       => [
        'type'      => 'post',
        'post_type' => 'page'
    ],
    'placeholder' => 'صفحه‌ای را جستجو کنید...'
]);

// رندر محتوای تب
add_action('nader_settings_tab_post_continue', function($nader_settings_instance) {
    // بررسی وجود کلاس‌های ماژول
    if (!class_exists('Nader_Toggle') || !class_exists('Nader_Choose')) {
        echo '<p>خطا در بارگذاری ماژول‌های ضروری</p>';
        return;
    }

    // نویسنده
    (new Nader_Toggle([
        'name'    => 'post_single_author',
        'title'   => 'نمایش نویسنده',
        'default' => 1,
        'label_on' => 'فعال',
        'label_off' => 'غیرفعال'
    ]))->render();
    echo '<hr>';

    // دسته‌بندی
    (new Nader_Toggle([
        'name'    => 'post_single_category',
        'title'   => 'نمایش دسته‌بندی',
        'default' => 1
    ]))->render();
    echo '<hr>';

    // بازدید
    (new Nader_Toggle([
        'name'    => 'post_single_views',
        'title'   => 'نمایش بازدید',
        'default' => 1
    ]))->render();
    echo '<hr>';

    // تاریخ
    (new Nader_Toggle([
        'name'    => 'post_single_date',
        'title'   => 'نمایش تاریخ',
        'default' => 1
    ]))->render();
    echo '<hr>';

    // نظرات
    (new Nader_Toggle([
        'name'    => 'post_single_comments',
        'title'   => 'نمایش نظرات',
        'default' => 1
    ]))->render();
    echo '<hr>';

    // انتخاب برگه‌ها
    (new Nader_Choose([
        'name'        => 'continue_pages',
        'title'       => 'انتخاب برگه وبلاگ',
        'multiple'    => false,
        'query'       => [
            'type'      => 'post',
            'post_type' => 'page'
        ],
        'placeholder' => 'صفحه‌ای را جستجو کنید...'
    ]))->render();

}, 10, 1);