<?php
/**
 * تب تنظیمات دکمه های گوشه ای
 */

if (!defined('ABSPATH'))
    exit;

return;

$nader_settings = Nader_Settings::instance();

// ثبت تب
$nader_settings->register_tab([
    'id'    => 'corner_buttons',
    'title' => 'دکمه های گوشه ای',
    'order' => 25
]);

// ثبت فیلدها
$nader_settings->register_module_config([
    'name'    => 'corner_buttons_status',
    'title'   => 'وضعیت دکمه های گوشه ای',
    'type'    => 'toggle',
    'default' => 1
]);

$nader_settings->register_module_config([
    'name'        => 'hide_mobile_buttons',
    'title'       => 'حذف در موبایل',
    'type'        => 'toggle',
    'description' => 'برای اندازه های زیر 768px اعمال می شود',
    'default'     => 0
]);

// --- بخش سبد خرید ---
$nader_settings->register_module_config([
    'name'    => 'cart_button_status',
    'title'   => 'وضعیت دکمه سبد خرید',
    'type'    => 'toggle',
    'default' => 1
]);

$nader_settings->register_module_config([
    'name'    => 'cart_button_color',
    'title'   => 'رنگ دکمه سبد خرید',
    'type'    => 'color',
    'default' => '#4CAF50'
]);

$nader_settings->register_module_config([
    'name'    => 'cart_icon_color',
    'title'   => 'رنگ آیکون سبد خرید',
    'type'    => 'color',
    'default' => '#FFFFFF'
]);

// --- بخش جستجو ---
$nader_settings->register_module_config([
    'name'    => 'search_button_status',
    'title'   => 'وضعیت دکمه جستجو',
    'type'    => 'toggle',
    'default' => 1
]);

$nader_settings->register_module_config([
    'name'    => 'search_button_color',
    'title'   => 'رنگ دکمه جستجو',
    'type'    => 'color',
    'default' => '#2196F3'
]);

$nader_settings->register_module_config([
    'name'    => 'search_icon_color',
    'title'   => 'رنگ آیکون جستجو',
    'type'    => 'color',
    'default' => '#FFFFFF'
]);

// --- بخش خروج ---
$nader_settings->register_module_config([
    'name'    => 'logout_button_status',
    'title'   => 'وضعیت دکمه خروج',
    'type'    => 'toggle',
    'default' => 0
]);

$nader_settings->register_module_config([
    'name'    => 'logout_button_color',
    'title'   => 'رنگ دکمه خروج',
    'type'    => 'color',
    'default' => '#F44336'
]);

$nader_settings->register_module_config([
    'name'    => 'logout_icon_color',
    'title'   => 'رنگ آیکون خروج',
    'type'    => 'color',
    'default' => '#FFFFFF'
]);

// --- بخش بالابر ---
$nader_settings->register_module_config([
    'name'    => 'scrolltop_status',
    'title'   => 'وضعیت دکمه بالابر',
    'type'    => 'toggle',
    'default' => 1
]);

$nader_settings->register_module_config([
    'name'    => 'scrolltop_type',
    'title'   => 'نوع دکمه بالابر',
    'type'    => 'radio',
    'options' => [
        'simple' => 'ساده',
        'linear' => 'خطی',
    ],
    'default' => 'simple'
]);

$nader_settings->register_module_config([
    'name'    => 'scrolltop_button_color',
    'title'   => 'رنگ دکمه بالابر',
    'type'    => 'color',
    'default' => '#607D8B'
]);

$nader_settings->register_module_config([
    'name'    => 'scrolltop_icon_color',
    'title'   => 'رنگ آیکون بالابر',
    'type'    => 'color',
    'default' => '#FFFFFF'
]);

// --- بخش دکمه های دلخواه ---
$nader_settings->register_module_config([
    'name'    => 'custom_buttons_style',
    'title'   => 'نحوه نمایش دکمه ها',
    'type'    => 'radio',
    'options' => [
        'dropdown' => 'منوی آبشاری',
        'inline'   => 'ردیف افقی',
        'floating' => 'شناور'
    ],
    'default' => 'dropdown'
]);

$nader_settings->register_module_config([
    'name'        => 'custom_buttons_title',
    'title'       => 'عنوان باکس پاپ آپ',
    'type'        => 'text',
    'placeholder' => 'دکمه های سریع'
]);

// --- بخش تکرارشونده ---
$nader_settings->register_module_config([
    'name'   => 'custom_buttons',
    'title'  => 'دکمه های دلخواه',
    'type'   => 'repeater',
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
            'type'        => 'text',
            'placeholder' => 'fa fa-star'
        ],
        [
            'name'    => 'button_color',
            'title'   => 'رنگ دکمه',
            'type'    => 'color',
            'default' => '#9C27B0'
        ],
        [
            'name'    => 'icon_color',
            'title'   => 'رنگ آیکون',
            'type'    => 'color',
            'default' => '#FFFFFF'
        ]
    ]
]);

// رندر محتوا
add_action('nader_settings_tab_corner_buttons', function() {
    ?>
    <div class="nader-corner-buttons">
        <!-- بخش اصلی -->
        <div class="nader-section">
            <?php
            $status = new Nader_Toggle(['name' => 'corner_buttons_status', 'title' => 'فعالسازی کلی دکمه های گوشه ای']);
            $status->render();

            $mobile = new Nader_Toggle(['name' => 'hide_mobile_buttons', 'title' => 'مخفی کردن در موبایل']);
            $mobile->render();
            ?>
        </div>

        <!-- سبد خرید -->
        <div class="nader-section">
            <h4>سبد خرید</h4>
            <?php
            $cart_status = new Nader_Toggle(['name' => 'cart_button_status', 'title' => 'فعالسازی دکمه']);
            $cart_status->render();

            $cart_color = new Nader_Color(['name' => 'cart_button_color', 'title' => 'رنگ دکمه']);
            $cart_color->render();

            $cart_icon = new Nader_Color(['name' => 'cart_icon_color', 'title' => 'رنگ آیکون']);
            $cart_icon->render();
            ?>
        </div>

        <!-- جستجو -->
        <div class="nader-section">
            <h4>جستجو</h4>
            <?php
            $search_status = new Nader_Toggle(['name' => 'search_button_status', 'title' => 'فعالسازی دکمه']);
            $search_status->render();

            $search_color = new Nader_Color(['name' => 'search_button_color', 'title' => 'رنگ دکمه']);
            $search_color->render();

            $search_icon = new Nader_Color(['name' => 'search_icon_color', 'title' => 'رنگ آیکون']);
            $search_icon->render();
            ?>
        </div>

        <!-- خروج -->
        <div class="nader-section">
            <h4>خروج</h4>
            <?php
            $logout_status = new Nader_Toggle(['name' => 'logout_button_status', 'title' => 'فعالسازی دکمه']);
            $logout_status->render();

            $logout_color = new Nader_Color(['name' => 'logout_button_color', 'title' => 'رنگ دکمه']);
            $logout_color->render();

            $logout_icon = new Nader_Color(['name' => 'logout_icon_color', 'title' => 'رنگ آیکون']);
            $logout_icon->render();
            ?>
        </div>

        <!-- بالابر -->
        <div class="nader-section">
            <h4>دکمه بالابر</h4>
            <?php
            $scroll_status = new Nader_Toggle(['name' => 'scrolltop_status', 'title' => 'فعالسازی']);
            $scroll_status->render();

            $scroll_type = new Nader_Radio([
                'name'    => 'scrolltop_type',
                'title'   => 'نوع دکمه',
                'options' => [
                    'simple' => 'ساده',
                    'linear' => 'خطی',
                ],
                'default' => 'simple'
            ]);
            $scroll_type->render();

            $scroll_color = new Nader_Color(['name' => 'scrolltop_button_color', 'title' => 'رنگ دکمه']);
            $scroll_color->render();

            $scroll_icon = new Nader_Color(['name' => 'scrolltop_icon_color', 'title' => 'رنگ آیکون']);
            $scroll_icon->render();
            ?>
        </div>

        <!-- دکمه های دلخواه -->
        <div class="nader-section">
            <h4>دکمه های سفارشی</h4>
            <?php
            $style = new Nader_Radio(['name' => 'custom_buttons_style', 'title' => 'سبک نمایش']);
            $style->render();

            $title = new Nader_Text(['name' => 'custom_buttons_title', 'title' => 'عنوان پاپ آپ']);
            $title->render();

            $repeater = new Nader_Repeater(['name' => 'custom_buttons', 'title' => 'مدیریت دکمه ها']);
            $repeater->render();
            ?>
        </div>
    </div>

    <style>
        .nader-corner-buttons {
            max-width: 1000px;
            margin: 0 auto;
        }

        .nader-section {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.03);
        }

        .nader-section h4 {
            color: #1d2327;
            font-size: 1.2em;
            margin: 0 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f1;
        }

        .nader-field-wrapper {
            margin-bottom: 15px;
        }
    </style>
    <?php
});