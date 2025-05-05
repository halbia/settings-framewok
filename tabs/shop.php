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
    'order' => 20
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
    'max'  => 40,
]);

// ثبت استایل‌های کارت محصول
$product_styles = [
    'card-simple' => 'پیشفرض',
    'card-2'      => 'کارت 2',
    'card-3'      => 'کارت 3',
];

foreach ([
             'shop_archive_card_style'        => 'محصول برگه آرشیو',
             'related_products_card_style'    => 'محصولات مرتبط (در صفحه محصول)',
             'cross_sell_products_card_style' => 'محصولات مشابه (در صفحه سبد خرید)',
             'up_sell_products_card_style'    => 'محصول تشویق برای خرید (در صفحه سبد خرید)',
         ] as $id => $title) {
    $nader_settings->register_module_config([
        'name'    => $id,
        'type'    => 'select',
        'title'   => $title,
        'options' => $product_styles,
        'default' => 'style1'
    ]);
}


$nader_settings->register_module_config([
    'name'        => 'free_product_text',
    'type'        => 'text',
    'title'       => 'متن جایگزین قیمت صفر',
    'default'     => 'رایگان',
    'description' => 'فقط برای محصولاتی که قیمت آنها 0 است.',
    'placeholder' => 'متن پیشفرض: رایگان'
]);

// بخش مدیریت محصولات بدون قیمت
$nader_settings->register_module_config([
    'name'    => 'empty_price_behavior',
    'type'    => 'radio',
    'inline' => true,
    'title'   => 'رفتار با محصولات بدون قیمت',
    'description' => 'فقط برای محصولاتی که قیمت آنها خالی است.',
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
    'name' => 'empty_price_button_title',
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
    'description' => 'یک برگه بسازید، شرتکد [ae_compare_products] را در آن قرار داده و آن را از اینجا انتخاب کنید.',
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

    ?>

    <div class="nader-fields-group">
        <h4>استایل کارت ها</h4>
        <div class="row">
            <div class="quarter">
                <?php
                foreach ([
                    'shop_archive_card_style',
                    'related_products_card_style',
                    'cross_sell_products_card_style',
                    'up_sell_products_card_style',
                ] as $item) {

                (new Nader_Select($nader_settings_instance->get_registered_module_config($item)))->render();

                ?>
            </div>
            <div class="quarter">
                <?php } ?>
            </div>
        </div>
    </div>

    <?php
    echo '<hr/>';

    (new Nader_Text($nader_settings_instance->get_registered_module_config('archive_products_per_page')))->render();
    echo '<hr/>';

    (new Nader_Toggle($nader_settings_instance->get_registered_module_config('ajax_add_to_cart')))->render();
    echo '<hr/>';

    (new Nader_Text($nader_settings_instance->get_registered_module_config('free_product_text')))->render();

    echo '<hr/>';

    (new Nader_Radio($nader_settings_instance->get_registered_module_config('empty_price_behavior')))->render();


    (new Nader_Text($nader_settings_instance->get_registered_module_config('empty_price_text')))->render();

    (new Nader_Text($nader_settings_instance->get_registered_module_config('empty_price_button_title')))->render();

    (new Nader_Text($nader_settings_instance->get_registered_module_config('empty_price_button_url')))->render();

    echo '<hr/>';

    // بخش مقایسه محصولات
    (new Nader_Toggle($nader_settings_instance->get_registered_module_config('enable_product_comparison')))->render();

    (new Nader_Choose($nader_settings_instance->get_registered_module_config('comparison_page')))->render();

    ?>

    <button class="nader-settings-ajax-create-compare-products-page button button-secondary" type="button" style="margin-top: 8px">ساخت خودکار برگه مقایسه</button>

    <?php

}, 10, 1);