<?php
/**
 * تب تنظیمات رنگ بخش‌های غیر المنتوری
 */

if (!defined('ABSPATH'))
    exit;

$nader_settings = Nader_Settings::instance();

// ثبت تب
$nader_settings->register_tab([
    'id'    => 'non_elementor_colors',
    'title' => 'رنگ بندی بخش های غیر المنتوری',
    'order' => 45
]);

// ثبت تمام فیلدهای رنگ
$color_fields = [
    // بخش عمومی
    'main_color'            => ['title' => 'رنگ اصلی'],
    'background_color'      => ['title' => 'رنگ بکگراند'],
    'archive_header_text'   => ['title' => 'رنگ متن هدر آرشیو'],

    // منو دسکتاپ
    'menu_bg'               => ['title' => 'بکگراند منو'],
    'menu_logo_bg'          => ['title' => 'بکگراند لوگو'],
    'menu_btn_bg'           => ['title' => 'بکگراند دکمه تماس'],
    'menu_link_color'       => ['title' => 'رنگ اولیه لینک'],
    'menu_link_hover'       => ['title' => 'رنگ هاور لینک'],
    'menu_link_hover_bg'    => ['title' => 'بکگراند هاور لینک'],

    // هدر موبایل
    'mobile_header_bg'      => ['title' => 'بکگراند هدر موبایل'],
    'mobile_logo_bg'        => ['title' => 'بکگراند لوگو'],
    'mobile_menu_btn'       => ['title' => 'رنگ دکمه منو'],

    // منو موبایل
    'mobile_menu_bg'        => ['title' => 'بکگراند منو'],
    'mobile_menu_logo_bg'   => ['title' => 'بکگراند لوگو'],
    'mobile_menu_text'      => ['title' => 'رنگ متن'],
    'mobile_menu_link'      => ['title' => 'رنگ لینک'],
    'mobile_menu_link_bg'   => ['title' => 'بکگراند لینک'],
    'mobile_menu_close_btn' => ['title' => 'بکگراند دکمه بستن'],

    // فوتر
    'footer_bg'             => ['title' => 'بکگراند فوتر'],
    'footer_text'           => ['title' => 'رنگ متن فوتر']
];

foreach ($color_fields as $name => $args) {
    $nader_settings->register_module_config([
        'name'         => $name,
        'title'        => $args['title'],
        'type'         => 'color',
    ]);
}

// رندر محتوا
add_action('nader_settings_tab_non_elementor_colors', function($nader_settings) { ?>

    <!-- بخش عمومی -->
    <div class="nader-fields-group">
        <h4>رنگ بندی عمومی</h4>
        <div class="row">
            <div class="third">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('main_color')))->render(); ?>
            </div>
            <div class="third">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('background_color')))->render(); ?>
            </div>
            <div class="third">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('archive_header_text')))->render(); ?>
            </div>
        </div>
    </div>

    <hr>

    <!-- منو دسکتاپ -->
    <div class="nader-fields-group">
        <h4>منو دسکتاپ</h4>
        <div class="row">
            <div class="third">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('menu_bg')))->render(); ?>
            </div>
            <div class="third">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('menu_logo_bg')))->render(); ?>
            </div>
            <div class="third">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('menu_btn_bg')))->render(); ?>
            </div>
            <div class="third">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('menu_link_color')))->render(); ?>
            </div>
            <div class="third">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('menu_link_hover')))->render(); ?>
            </div>
            <div class="third">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('menu_link_hover_bg')))->render(); ?>
            </div>
        </div>
    </div>

    <hr>

    <!-- هدر موبایل -->
    <div class="nader-fields-group">
        <h4>هدر بالا موبایل</h4>
        <div class="row">
            <div class="third">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('mobile_header_bg')))->render(); ?>
            </div>
            <div class="third">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('mobile_logo_bg')))->render(); ?>
            </div>
            <div class="third">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('mobile_menu_btn')))->render(); ?>
            </div>
        </div>
    </div>

    <hr>

    <!-- منو موبایل -->
    <div class="nader-fields-group">
        <h4>منو موبایل</h4>
        <div class="row">
            <div class="third">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('mobile_menu_bg')))->render(); ?>
            </div>
            <div class="third">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('mobile_menu_logo_bg')))->render(); ?>
            </div>
            <div class="third">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('mobile_menu_text')))->render(); ?>
            </div>
            <div class="third">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('mobile_menu_link')))->render(); ?>
            </div>
            <div class="third">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('mobile_menu_link_bg')))->render(); ?>
            </div>
            <div class="third">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('mobile_menu_close_btn')))->render(); ?>
            </div>
        </div>
    </div>

    <hr>

    <!-- فوتر -->
    <div class="nader-fields-group">
        <h4>فوتر</h4>
        <div class="row">
            <div class="third">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('footer_bg')))->render(); ?>
            </div>
            <div class="third">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('footer_text')))->render(); ?>
            </div>
            <div class="third">
            </div>
        </div>
    </div>

<?php });
