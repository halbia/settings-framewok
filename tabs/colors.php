<?php
/**
 * تب تنظیمات رنگ بخش‌های غیر المنتوری
 */

if (!defined('ABSPATH'))
    exit;
return;
$nader_settings = Nader_Settings::instance();

// ثبت تب
$nader_settings->register_tab([
    'id'    => 'non_elementor_colors',
    'title' => 'رنگ بندی بخش های غیر المنتوری',
    'order' => 15
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
        'default'      => '',
        'enable_alpha' => true
    ]);
}

// رندر محتوا
add_action('nader_settings_tab_non_elementor_colors', function() {
    if (!class_exists('Nader_Color')) {
        echo '<p>خطا در بارگذاری ماژول رنگ</p>';
        return;
    } ?>

    <div class="nader-color-section">
        <!-- بخش عمومی -->
        <div class="nader-fields-group">
            <h4>رنگ بندی عمومی</h4>
            <div class="row">
                <div class="third">
                    <?php
                    $main_color = new Nader_Color([
                        'name'  => 'main_color',
                        'title' => 'رنگ اصلی'
                    ]);
                    $main_color->render();
                    ?>
                </div>
                <div class="third">
                    <?php
                    $background_color = new Nader_Color([
                        'name'  => 'background_color',
                        'title' => 'رنگ بکگراند'
                    ]);
                    $background_color->render();
                    ?>
                </div>
                <div class="third">
                    <?php
                    $archive_header = new Nader_Color([
                        'name'  => 'archive_header_text',
                        'title' => 'رنگ متن هدر آرشیو'
                    ]);
                    $archive_header->render();
                    ?>
                </div>
            </div>
        </div>

        <!-- منو دسکتاپ -->
        <div class="nader-fields-group">
            <h4>منو دسکتاپ</h4>
            <div class="row">
                <div class="third">
                    <?php
                    $menu_bg = new Nader_Color([
                        'name'  => 'menu_bg',
                        'title' => 'پس‌زمینه منو'
                    ]);
                    $menu_bg->render();
                    ?>
                </div>
                <div class="third">
                    <?php
                    $menu_logo = new Nader_Color([
                        'name'  => 'menu_logo_bg',
                        'title' => 'پس‌زمینه لوگو'
                    ]);
                    $menu_logo->render();
                    ?>
                </div>
                <div class="third">
                    <?php
                    $menu_btn = new Nader_Color([
                        'name'  => 'menu_btn_bg',
                        'title' => 'پس‌زمینه دکمه تماس'
                    ]);
                    $menu_btn->render();
                    ?>
                </div>
                <div class="third">
                    <?php
                    $menu_link = new Nader_Color([
                        'name'  => 'menu_link_color',
                        'title' => 'رنگ لینک‌ها'
                    ]);
                    $menu_link->render();
                    ?>
                </div>
                <div class="third">
                    <?php
                    $menu_hover = new Nader_Color([
                        'name'  => 'menu_link_hover',
                        'title' => 'رنگ هاور لینک'
                    ]);
                    $menu_hover->render();
                    ?>
                </div>
                <div class="third">
                    <?php
                    $menu_hover_bg = new Nader_Color([
                        'name'  => 'menu_link_hover_bg',
                        'title' => 'پس‌زمینه هاور لینک'
                    ]);
                    $menu_hover_bg->render();
                    ?>
                </div>
            </div>
        </div>

        <!-- هدر موبایل -->
        <div class="nader-fields-group">
            <h4>هدر بالا موبایل</h4>
            <div class="row">
                <div class="third">
                    <?php
                    $mobile_header = new Nader_Color([
                        'name'  => 'mobile_header_bg',
                        'title' => 'پس‌زمینه هدر موبایل'
                    ]);
                    $mobile_header->render();
                    ?>
                </div>
                <div class="third">
                    <?php
                    $mobile_logo = new Nader_Color([
                        'name'  => 'mobile_logo_bg',
                        'title' => 'پس‌زمینه لوگو'
                    ]);
                    $mobile_logo->render();
                    ?>
                </div>
                <div class="third">
                    <?php
                    $mobile_btn = new Nader_Color([
                        'name'  => 'mobile_menu_btn',
                        'title' => 'رنگ دکمه منو'
                    ]);
                    $mobile_btn->render();
                    ?>
                </div>
            </div>
        </div>

        <!-- منو موبایل -->
        <div class="nader-fields-group">
            <h4>منو موبایل</h4>
            <div class="row">
                <div class="third">
                    <?php
                    $mobile_menu_bg = new Nader_Color([
                        'name'  => 'mobile_menu_bg',
                        'title' => 'پس‌زمینه منو'
                    ]);
                    $mobile_menu_bg->render();
                    ?>
                </div>
                <div class="third">
                    <?php
                    $mobile_menu_logo = new Nader_Color([
                        'name'  => 'mobile_menu_logo_bg',
                        'title' => 'پس‌زمینه لوگو'
                    ]);
                    $mobile_menu_logo->render();
                    ?>
                </div>
                <div class="third">
                    <?php
                    $mobile_menu_text = new Nader_Color([
                        'name'  => 'mobile_menu_text',
                        'title' => 'رنگ متن'
                    ]);
                    $mobile_menu_text->render();
                    ?>
                </div>
                <div class="third">
                    <?php
                    $mobile_menu_link = new Nader_Color([
                        'name'  => 'mobile_menu_link',
                        'title' => 'رنگ لینک'
                    ]);
                    $mobile_menu_link->render();
                    ?>
                </div>
                <div class="third">
                    <?php
                    $mobile_menu_link_bg = new Nader_Color([
                        'name'  => 'mobile_menu_link_bg',
                        'title' => 'پس‌زمینه لینک'
                    ]);
                    $mobile_menu_link_bg->render();
                    ?>
                </div>
                <div class="third">
                    <?php
                    $mobile_menu_close = new Nader_Color([
                        'name'  => 'mobile_menu_close_btn',
                        'title' => 'پس‌زمینه دکمه بستن'
                    ]);
                    $mobile_menu_close->render();
                    ?>
                </div>
            </div>
        </div>

        <!-- فوتر -->
        <div class="nader-fields-group">
            <h4>فوتر</h4>
            <div class="row">
                <div class="third">

                    <?php
                    $footer_bg = new Nader_Color([
                        'name'  => 'footer_bg',
                        'title' => 'پس‌زمینه فوتر'
                    ]);
                    $footer_bg->render();
                    ?>
                </div>
                <div class="third">
                    <?php
                    $footer_text = new Nader_Color([
                        'name'  => 'footer_text',
                        'title' => 'رنگ متن فوتر'
                    ]);
                    $footer_text->render();
                    ?>
                </div>
            </div>
        </div>
    </div>

<?php });