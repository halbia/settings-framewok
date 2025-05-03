<?php
/**
 * تب تنظیمات امنیتی
 */

if (!defined('ABSPATH')) exit;

$nader_settings = Nader_Settings::instance();

// ثبت تب امنیت
$nader_settings->register_tab([
    'id'    => 'security',
    'title' => 'تنظیمات امنیتی',
    'order' => 10
]);

// ثبت پیکربندی ماژول‌ها
$nader_settings->register_module_config([
    'name'        => 'disable_xmlrpc',
    'title'       => 'غیرفعال سازی XML-RPC',
    'type'        => 'toggle',
    'default'     => 0
]);

$nader_settings->register_module_config([
    'name'        => 'disable_rest_api',
    'title'       => 'غیرفعال سازی REST API پیشفرض',
    'type'        => 'toggle',
    'default'     => 0
]);

$nader_settings->register_module_config([
    'name'        => 'disable_feeds',
    'title'       => 'غیرفعال سازی فیدها',
    'type'        => 'toggle',
    'default'     => 0
]);

$nader_settings->register_module_config([
    'name'        => 'hide_wp_version',
    'title'       => 'پنهان سازی نسخه وردپرس',
    'type'        => 'toggle',
    'default'     => 0
]);

$nader_settings->register_module_config([
    'name'        => 'disable_file_editor',
    'title'       => 'غیرفعال سازی ویرایشگر فایل',
    'type'        => 'toggle',
    'default'     => 0
]);

$nader_settings->register_module_config([
    'name'        => 'allowed_roles',
    'title'       => 'نقش‌های مجاز',
    'type'        => 'choose',
    'description' => 'لیست نقش‌های مجاز در سیستم',
    'required'    => false,
    'multilang'   => false,
    'multiple'    => true,
    'query'       => [
        'type' => 'role',
    ],
    'placeholder' => 'نقش‌ها را انتخاب کنید'
]);

// رندر محتوای تب
add_action('nader_settings_tab_security', function($nader_settings_instance) {
    if (!class_exists('Nader_Toggle') || !class_exists('Nader_Choose')) {
        echo '<p>خطا در بارگذاری ماژول‌ها</p>';
        return;
    }

    // نمونه‌سازی و رندر Toggle ها
    $toggle_xmlrpc = new Nader_Toggle([
        'name' => 'disable_xmlrpc',
        'title' => 'غیرفعال سازی XML-RPC'
    ]);
    $toggle_xmlrpc->render();

    $toggle_rest_api = new Nader_Toggle([
        'name' => 'disable_rest_api',
        'title' => 'غیرفعال سازی REST API'
    ]);
    $toggle_rest_api->render();

    $toggle_feeds = new Nader_Toggle([
        'name' => 'disable_feeds',
        'title' => 'غیرفعال سازی فیدها'
    ]);
    $toggle_feeds->render();

    $toggle_version = new Nader_Toggle([
        'name' => 'hide_wp_version',
        'title' => 'پنهان سازی نسخه وردپرس'
    ]);
    $toggle_version->render();

    $toggle_editor = new Nader_Toggle([
        'name' => 'disable_file_editor',
        'title' => 'غیرفعال سازی ویرایشگر'
    ]);
    $toggle_editor->render();

    // نمونه‌سازی و رندر Choose برای نقش‌ها
    $choose_roles = new Nader_Choose([
        'name' => 'allowed_roles',
        'title' => 'نقش‌های مجاز',
        'description' => 'لیست نقش‌های مجاز در سیستم',
        'multiple' => true,
        'query' => [
            'type' => 'role',
        ],
        'placeholder' => 'نقش‌ها را انتخاب کنید'
    ]);
    $choose_roles->render();
});