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
    'order' => 15
]);

// ثبت ماژول‌های Toggle با حلقه
foreach ([
             'project_author_button'   => 'نویسنده پروژه',
             'project_views_button'    => 'بازدید پروژه',
             'project_comments_button' => 'نظرات پروژه'
         ] as $id => $title) {
    $nader_settings->register_module_config([
        'name'      => $id,
        'type'      => 'toggle',
        'title'     => 'نمایش ' . $title,
        'default'   => 1,
        'label_on'  => 'فعال',
        'label_off' => 'غیرفعال'
    ]);
}

// ثبت ماژول‌های متنی
foreach ([
             'content_tab_title'    => ['عنوان تب محتوا', 'جزئیات پروژه'],
             'order_form_tab_title' => ['عنوان تب فرم سفارش', 'سفارش پروژه']
         ] as $id => [$title, $default]) {
    $nader_settings->register_module_config([
        'name'        => $id,
        'type'        => 'text',
        'title'       => $title,
        'placeholder' => 'عنوان دلخواه را وارد کنید',
        'default'     => $default
    ]);
}

// ثبت ماژول‌های خاص
$nader_settings->register_module_config([
    'name'        => 'project_order_form',
    'type'        => 'choose',
    'title'       => 'فرم سفارش پروژه',
    'description' => 'صفحه مربوط به فرم درخواست پروژه',
    'multiple'    => false,
    'query'       => [
        'type'      => 'post',
        'post_type' => 'elementor_library'
    ],
    'placeholder' => 'جستجوی فرم...'
]);

$nader_settings->register_module_config([
    'name'         => 'project_custom_fields',
    'type'         => 'repeater',
    'title'        => 'زمینه های دلخواه پروژه',
    'item_label'   => 'زمینه دلخواه',
    'button_title' => 'افزودن زمینه دلخواه',
    'description'  => 'زمینه های دلخواه پروژه ها را اینجا مدیریت کنید.',
    'fields'       => [
        [
            'name'     => 'title',
            'type'     => 'text',
            'description' => 'text',
            'title'       => 'عنوان',
            'required' => true
        ],
        [
            'name'        => 'key',
            'type'        => 'text',
            'title'       => 'کلید شناسایی',
            'pattern'     => '^[a-z0-9_]+$',
            'required'    => true,
            'description' => 'فقط حروف کوچک، اعداد و زیرخط مجاز است - اطلاعات با استفاده از این کلید در دیتابیس ذخیره میشوند'
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

    (new Nader_Repeater(
        $nader_settings_instance->get_registered_module_config('project_custom_fields')
    ))->render();

    ?>

    <p class="nader-settings-notice">
        تنظیمات زیر برای ادامه مطلب در حالت غیر المنتوری است! در غیر اینصورت باید با خود المنتور تغییر دهید. راهنمایی بیشتر در منوی -> <b>پیشخوان قالب نادر</b>
    </p>

    <?php

    // رندر ماژول‌های Toggle
    foreach ([
                 'project_author_button',
                 'project_views_button',
                 'project_comments_button'
             ] as $module_id) {
        (new Nader_Toggle(
            $nader_settings_instance->get_registered_module_config($module_id)
        ))->render();
        echo '<hr>';
    }

    // رندر ماژول‌های متنی
    foreach ([
                 'content_tab_title',
                 'order_form_tab_title'
             ] as $module_id) {
        (new Nader_Text(
            $nader_settings_instance->get_registered_module_config($module_id)
        ))->render();
        echo '<hr>';
    }

    // رندر ماژول‌های خاص
    (new Nader_Choose(
        $nader_settings_instance->get_registered_module_config('project_order_form')
    ))->render();

}, 10, 1);