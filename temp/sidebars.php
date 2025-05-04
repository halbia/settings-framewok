<?php
/**
 * تب مدیریت سایدبارها
 */

if (!defined('ABSPATH'))
    exit;

$nader_settings = Nader_Settings::instance();

// ثبت تب جدید
$nader_settings->register_tab([
    'id'    => 'sidebars',
    'title' => 'سایدبارها',
    'order' => 5
]);

// ثبت ماژول‌های سایدبار
$sidebars = [
    'post'      => 'سایدبار پست',
    'page'      => 'سایدبار برگه',
    'employee'  => 'سایدبار کارمند',
    'archive'   => 'سایدبار آرشیو',
    'shop'      => 'سایدبار فروشگاه',
    'product'   => 'سایدبار محصول'
];

foreach ($sidebars as $key => $title) {
    $nader_settings->register_module_config([
        'name'        => 'sidebar_' . $key,
        'type'        => 'toggle',
        'title'       => $title,
        'description' => 'فعال/غیرفعال کردن ' . $title,
        'default'     => 0,
        'label_on'    => 'فعال',
        'label_off'   => 'غیرفعال'
    ]);
}

// رندر محتوای تب
add_action('nader_settings_tab_sidebars', function($nader_settings_instance) {
    if (!class_exists('Nader_Toggle')) {
        echo '<p class="error">ماژول Toggle یافت نشد!</p>';
        return;
    }

    $sidebars = [
        'post'      => 'سایدبار پست',
        'page'      => 'سایدبار برگه',
        'employee'  => 'سایدبار کارمند',
        'archive'   => 'سایدبار آرشیو',
        'shop'      => 'سایدبار فروشگاه',
        'product'   => 'سایدبار محصول'
    ];

    foreach ($sidebars as $key => $title) {
        $field = new Nader_Toggle([
            'name'        => 'sidebar_' . $key,
            'title'       => $title,
            'description' => 'وضعیت نمایش ' . $title,
            'label_on'    => 'فعال',
            'label_off'   => 'غیرفعال'
        ]);

        $field->render();
        echo '<hr>';
    }
}, 10, 1);
