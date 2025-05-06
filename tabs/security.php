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
    'order' => 40
]);

// ثبت پیکربندی ماژول‌ها
$nader_settings->register_module_config([
    'name'        => 'disable_xmlrpc',
    'title'       => 'غیرفعال سازی XML-RPC',
    'type'        => 'toggle',
    'description' => 'سرویس XML-RPC را غیرفعال می‌کند',
    'default'     => 1
]);

$nader_settings->register_module_config([
    'name'        => 'disable_rest_api',
    'title'       => 'غیرفعال سازی REST API پیشفرض',
    'type'        => 'toggle',
    'description' => 'دسترسی به REST API پیشفرض وردپرس را محدود می‌کند',
    'default'     => 1
]);

$nader_settings->register_module_config([
    'name'        => 'disable_feeds',
    'title'       => 'غیرفعال سازی فیدها',
    'type'        => 'toggle',
    'description' => 'فیدهای RSS/Atom را غیرفعال می‌کند',
    'default'     => 1
]);

$nader_settings->register_module_config([
    'name'        => 'hide_wp_version',
    'title'       => 'پنهان سازی نسخه وردپرس',
    'type'        => 'toggle',
    'description' => 'نسخه وردپرس را از کدهای HTML حذف می‌کند',
    'default'     => 1
]);

$nader_settings->register_module_config([
    'name'        => 'disable_file_editor',
    'title'       => 'غیرفعال سازی ویرایشگر فایل',
    'type'        => 'toggle',
    'description' => 'ویرایشگر فایل در پیشخوان را غیرفعال می‌کند',
    'default'     => 1
]);

$nader_settings->register_module_config([
    'name'        => 'allowed_roles',
    'title'       => 'نقش‌های مجاز',
    'type'        => 'choose',
    'description' => 'لیست نقش‌های مجاز برای دسترسی به پنل داشبورد وردپرس',
    'required'    => false,
    'multilang'   => false,
    'multiple'    => true,
    'query'       => [
        'type' => 'role',
    ],
    'placeholder' => 'نقش‌ها را انتخاب کنید'
]);

// رندر محتوای تب
add_action('nader_settings_tab_security', function($nader_settings) {
    ?>

        <div class="row">
            <div class="half"><?php (new Nader_Toggle($nader_settings->get_registered_module_config('disable_xmlrpc')))->render(); ?></div>
            <div class="half"><?php (new Nader_Toggle($nader_settings->get_registered_module_config('disable_rest_api')))->render(); ?></div>
        </div>
        <div class="row">
            <div class="half"><?php (new Nader_Toggle($nader_settings->get_registered_module_config('disable_feeds')))->render(); ?></div>
            <div class="half"><?php (new Nader_Toggle($nader_settings->get_registered_module_config('hide_wp_version')))->render(); ?></div>
        </div>
        <div class="row">
            <div class="full"><?php (new Nader_Toggle($nader_settings->get_registered_module_config('disable_file_editor')))->render(); ?></div>
        </div>
        <div class="row">
            <div class="full"><?php (new Nader_Choose($nader_settings->get_registered_module_config('allowed_roles')))->render(); ?></div>
        </div>

    <?php
});