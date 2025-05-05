<?php
/**
 * تب تنظیمات دکمه های گوشه ای
 */

if (!defined('ABSPATH'))
    exit;

$nader_settings = Nader_Settings::instance();

// ثبت تب
$nader_settings->register_tab([
    'id'    => 'corner_buttons',
    'title' => 'دکمه های گوشه ای',
    'order' => 30
]);

// ثبت فیلدها
$nader_settings->register_module_config([
    'name'    => 'corner_buttons_status',
    'title'   => 'وضعیت دکمه های گوشه ای',
    'type'    => 'toggle',
    'description' => 'برای همه موارد زیر اعمال می شود.',
    'default' => 1
]);

$nader_settings->register_module_config([
    'name' => 'corner_buttons_hide_in_mobile_status',
    'title'       => 'حذف در موبایل',
    'type'        => 'toggle',
    'description' => 'برای اندازه های زیر 768px اعمال می شود',
    'default'      => 0,
    'dependencies' => [
        'relation' => 'AND',
        'rules'    => [['field' => 'corner_buttons_status', 'operator' => '==', 'value' => '1']]
    ]
]);

// --- بخش سبد خرید ---
$nader_settings->register_module_config([
    'name'  => 'corner_buttons_cart_button_status',
    'title' => 'وضعیت',
    'type'    => 'toggle',
    'default' => 1
]);

$nader_settings->register_module_config([
    'name'  => 'corner_buttons_cart_button_bg',
    'title' => 'بکگراند',
    'type'    => 'color',
    'dependencies' => [
        'relation' => 'AND',
        'rules'    => [['field' => 'corner_buttons_cart_button_status', 'operator' => '==', 'value' => '1']]
    ]
]);

$nader_settings->register_module_config([
    'name'  => 'corner_buttons_cart_icon_color',
    'title' => 'رنگ آیکون',
    'type'    => 'color',
    'dependencies' => [
        'relation' => 'AND',
        'rules'    => [['field' => 'corner_buttons_cart_button_status', 'operator' => '==', 'value' => '1']]
    ]
]);

// --- بخش جستجو ---
$nader_settings->register_module_config([
    'name'  => 'corner_buttons_search_button_status',
    'title' => 'وضعیت',
    'type'    => 'toggle',
    'default' => 1
]);

$nader_settings->register_module_config([
    'name'  => 'corner_buttons_search_button_bg',
    'title' => 'بکگرند',
    'type'    => 'color',
    'dependencies' => [
        'relation' => 'AND',
        'rules'    => [['field' => 'corner_buttons_search_button_status', 'operator' => '==', 'value' => '1']]
    ]
]);

$nader_settings->register_module_config([
    'name'  => 'corner_buttons_search_icon_color',
    'title' => 'رنگ آیکون',
    'type'    => 'color',
    'dependencies' => [
        'relation' => 'AND',
        'rules'    => [['field' => 'corner_buttons_search_button_status', 'operator' => '==', 'value' => '1']]
    ]
]);

// --- بخش خروج ---
$nader_settings->register_module_config([
    'name'    => 'corner_buttons_logout_button_status',
    'title'   => 'وضعیت',
    'type'    => 'toggle',
    'default' => 1
]);

$nader_settings->register_module_config([
    'name'  => 'corner_buttons_logout_button_bg',
    'title' => 'بکگراند',
    'type'    => 'color',
    'dependencies' => [
        'relation' => 'AND',
        'rules'    => [['field' => 'corner_buttons_logout_button_status', 'operator' => '==', 'value' => '1']]
    ]
]);

$nader_settings->register_module_config([
    'name'  => 'corner_buttons_logout_icon_color',
    'title' => 'رنگ آیکون',
    'type'    => 'color',
    'dependencies' => [
        'relation' => 'AND',
        'rules'    => [['field' => 'corner_buttons_logout_button_status', 'operator' => '==', 'value' => '1']]
    ]
]);

// --- بخش بالابر ---
$nader_settings->register_module_config([
    'name'  => 'corner_buttons_scrolltop_status',
    'title' => 'وضعیت',
    'type'    => 'toggle',
    'default' => 1
]);

$nader_settings->register_module_config([
    'name'   => 'corner_buttons_scrolltop_type',
    'title'  => 'نوع',
    'type'    => 'radio',
    'inline' => true,
    'options' => [
        'simple' => 'ساده',
        'linear' => 'خطی',
    ],
    'default' => 'simple',
    'dependencies' => [
        'relation' => 'AND',
        'rules'    => [['field' => 'corner_buttons_scrolltop_status', 'operator' => '==', 'value' => '1']]
    ]
]);

$nader_settings->register_module_config([
    'name'  => 'corner_buttons_scrolltop_button_bg',
    'title' => 'بکگراند',
    'type'    => 'color',
    'dependencies' => [
        'relation' => 'AND',
        'rules'    => [['field' => 'corner_buttons_scrolltop_status', 'operator' => '==', 'value' => '1']]
    ]
]);

$nader_settings->register_module_config([
    'name'  => 'corner_buttons_scrolltop_icon_color',
    'title' => 'رنگ آیکون',
    'type'    => 'color',
    'dependencies' => [
        'relation' => 'AND',
        'rules'    => [['field' => 'corner_buttons_scrolltop_status', 'operator' => '==', 'value' => '1']]
    ]
]);

// --- بخش دکمه های دلخواه ---
$nader_settings->register_module_config([
    'name'    => 'corner_buttons_custom_buttons_style',
    'title'   => 'نحوه نمایش دکمه ها',
    'type'    => 'radio',
    'inline'    => true,
    'options' => [
        'fixed' => 'ثابت در سمت چپ',
        'popup' => 'پاپ آپ',
    ],
    'default' => 'fixed'
]);

$nader_settings->register_module_config([
    'name' => 'corner_buttons_custom_buttons_popup_title',
    'title'       => 'عنوان باکس پاپ آپ',
    'type'        => 'text',
    'placeholder' => 'دکمه های سریع',
    'dependencies' => [
        'relation' => 'AND',
        'rules'    => [['field' => 'corner_buttons_custom_buttons_style', 'operator' => '==', 'value' => 'popup']]
    ]
]);

// --- بخش تکرارشونده ---
$nader_settings->register_module_config([
    'name'   => 'corner_buttons_custom_buttons',
    'title'  => 'دکمه های دلخواه',
    'type'   => 'repeater',
    'item_label'   => 'دکمه',
    'fields' => [
        [
            'name'        => 'link',
            'title'       => 'لینک',
            'type'        => 'text',
            'placeholder' => 'https://example.com'
        ],
        [
            'name'        => 'title',
            'title'       => 'عنوان',
            'type'        => 'text',
            'placeholder' => 'دکمه جدید'
        ],
        [
            'name'        => 'icon',
            'title'       => 'آیکون',
            'type'        => 'textarea',
            'rows'  =>  3
        ],
        [
            'name'    => 'button_color',
            'title'   => 'رنگ دکمه',
            'type'    => 'color',
        ],
        [
            'name'    => 'icon_color',
            'title'   => 'رنگ آیکون',
            'type'    => 'color',
        ]
    ]
]);

// رندر محتوا
add_action('nader_settings_tab_corner_buttons', function($nader_settings) {
    ?>

    <div class="nader-fields-group">
        <div class="row">
            <div class="half"><?php (new Nader_Toggle($nader_settings->get_registered_module_config('corner_buttons_status')))->render(); ?></div>
            <div class="half"><?php (new Nader_Toggle($nader_settings->get_registered_module_config('corner_buttons_hide_in_mobile_status')))->render(); ?></div>
        </div>
    </div>


    <div class="nader-fields-group">
        <h4>دکمه سبد خرید</h4>
        <div class="row">
            <div class="half"><?php (new Nader_Toggle($nader_settings->get_registered_module_config('corner_buttons_cart_button_status')))->render(); ?></div>
            <div class="quarter">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('corner_buttons_cart_button_bg')))->render(); ?>
            </div>
            <div class="quarter">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('corner_buttons_cart_icon_color')))->render(); ?>
            </div>
        </div>
    </div>


    <div class="nader-fields-group">
        <h4>دکمه جستجو</h4>
        <div class="row">
            <div class="half"><?php (new Nader_Toggle($nader_settings->get_registered_module_config('corner_buttons_search_button_status')))->render(); ?></div>
            <div class="quarter">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('corner_buttons_search_button_bg')))->render(); ?>
            </div>
            <div class="quarter">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('corner_buttons_search_icon_color')))->render(); ?>
            </div>
        </div>
    </div>


    <div class="nader-fields-group">
        <h4>دکمه خروج</h4>
        <div class="row">
            <div class="half"><?php (new Nader_Toggle($nader_settings->get_registered_module_config('corner_buttons_logout_button_status')))->render(); ?></div>
            <div class="quarter">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('corner_buttons_logout_button_bg')))->render(); ?>
            </div>
            <div class="quarter">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('corner_buttons_logout_icon_color')))->render(); ?>
            </div>
        </div>
    </div>


    <div class="nader-fields-group">
        <h4>دکمه بالابر</h4>
        <div class="row">
            <div class="quarter">
                <?php (new Nader_Toggle($nader_settings->get_registered_module_config('corner_buttons_scrolltop_status')))->render(); ?>
            </div>
            <div class="quarter">
                <?php (new Nader_Radio($nader_settings->get_registered_module_config('corner_buttons_scrolltop_type')))->render(); ?>
            </div>
            <div class="quarter">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('corner_buttons_scrolltop_button_bg')))->render(); ?>
            </div>
            <div class="quarter">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('corner_buttons_scrolltop_icon_color')))->render(); ?>
            </div>
        </div>
    </div>


    <div class="nader-fields-group">
        <h4>دکمه های سفارشی</h4>
        <div class="row">
            <div class="half">
                <?php (new Nader_Radio($nader_settings->get_registered_module_config('corner_buttons_custom_buttons_style')))->render(); ?>
            </div>
            <div class="half">
                <?php (new Nader_Text($nader_settings->get_registered_module_config('corner_buttons_custom_buttons_popup_title')))->render(); ?>
            </div>
        </div>
        <div class="row">
            <div class="full corner-buttons-custom-button">
                <?php (new Nader_Repeater($nader_settings->get_registered_module_config('corner_buttons_custom_buttons')))->render(); ?>
            </div>
        </div>
    </div>

    <style>
        .corner-buttons-custom-button .repeater-item{
            background: #fff;
        }

        .corner-buttons-custom-button .repeater-item input[type=text],.corner-buttons-custom-button .repeater-item textarea{
            background: #eee;
        }
    </style>

    <?php
});