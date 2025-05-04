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
    'order' => 10
]);

foreach ([
             'post_single_author'   => 'نویسنده',
             'post_single_category' => 'دسته‌بندی',
             'post_single_views'    => 'بازدید',
             'post_single_date'     => 'تاریخ',
             'post_single_comments' => 'نظرات',
         ] as $id => $title) {
    // ثبت ماژول‌ها با پارامترهای کامل
    $nader_settings->register_module_config([
        'name'    => $id,
        'type'    => 'toggle',
        'title'   => 'نمایش ' . $title,
        'default' => 1,
    ]);
}


$nader_settings->register_module_config([
    'name'        => 'continue_pages',
    'type'        => 'choose',
    'title'       => 'انتخاب برگه وبلاگ',
    'multiple'    => false,
    'query'       => [
        'type'      => 'post',
        'post_type' => 'page'
    ],
    'placeholder' => 'صفحه‌ای را جستجو کنید...',
    'description' => 'این برگه به عنوان برگه وبلاگ در مسیریابی سایت استفاده میشود'
]);

// رندر محتوای تب با استفاده از ماژول‌های ثبت شده
add_action('nader_settings_tab_post_continue', function($nader_settings_instance) {

    // انتخاب برگه
    (new Nader_Choose(
        $nader_settings_instance->get_registered_module_config('continue_pages')
    ))->render();

    ?>

    <p class="nader-settings-notice">
        تنظیمات زیر برای ادامه مطلب در حالت غیر المنتوری است! در غیر اینصورت باید با خود المنتور تغییر دهید. راهنمایی بیشتر در منوی -> <b>پیشخوان قالب نادر</b>
    </p>

    <?php

    foreach ([
                 'post_single_author',
                 'post_single_category',
                 'post_single_views',
                 'post_single_date',
                 'post_single_comments',
             ] as $tab) {

        echo '<hr>';

        (new Nader_Toggle(
            $nader_settings_instance->get_registered_module_config($tab)
        ))->render();

    }

}, 10, 1);