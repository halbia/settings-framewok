<?php
/**
 * تب تنظیمات عمومی
 */

if (!defined('ABSPATH'))
    exit;

$nader_settings = Nader_Settings::instance();

// ثبت تب جدید
$nader_settings->register_tab([
    'id'    => 'general',
    'title' => 'تنظیمات عمومی',
    'order' => 1
]);

// ------------------ پیکربندی ماژول‌ها ------------------
// بخش وضعیت لودینگ
$nader_settings->register_module_config([
    'name'    => 'loading_status',
    'type'    => 'toggle',
    'title'   => 'وضعیت لودینگ',
    'default' => 0
]);

$nader_settings->register_module_config([
    'name'         => 'loading_type',
    'type'         => 'select',
    'title'        => 'نوع لودینگ',
    'options'      => array_merge(['logo' => 'لوگو'], array_combine(range(1,10), array_map(fn($n) => "نوع $n", range(1,10)))),
    'dependencies' => [
        'relation' => 'AND',
        'rules' => [['field' => 'loading_status', 'operator' => '==', 'value' => '1']]
    ]
]);

$nader_settings->register_module_config([
    'name'         => 'loading_logo',
    'type'         => 'image',
    'title'        => 'لوگو لودینگ',
    'dependencies' => [
        'relation' => 'AND',
        'rules' => [
            ['field' => 'loading_type', 'operator' => '==', 'value' => 'logo'],
            ['field' => 'loading_status', 'operator' => '==', 'value' => '1']
        ]
    ]
]);

$nader_settings->register_module_config([
    'name'         => 'loading_text',
    'type'         => 'text',
    'title'        => 'متن زیر لوگو',
    'dependencies' => [
        'relation' => 'AND',
        'rules' => [
            ['field' => 'loading_type', 'operator' => '==', 'value' => 'logo'],
            ['field' => 'loading_status', 'operator' => '==', 'value' => '1']
        ]
    ]
]);

// بخش دنبال کننده موس
$nader_settings->register_module_config([
    'name'    => 'mouse_follower_status',
    'type'    => 'toggle',
    'title'   => 'وضعیت دنبال کننده موس',
    'default' => 0
]);

$nader_settings->register_module_config([
    'name'         => 'mouse_follower_color',
    'type'         => 'color',
    'title'        => 'رنگ دنبالگر موس',
    'dependencies' => [
        'relation' => 'AND',
        'rules' => [['field' => 'mouse_follower_status', 'operator' => '==', 'value' => '1']]
    ]
]);

// بخش دکمه تماس
$nader_settings->register_module_config([
    'name'    => 'contact_button_status',
    'type'    => 'toggle',
    'title'   => 'وضعیت دکمه تماس',
    'default' => 0
]);

$nader_settings->register_module_config([
    'name'         => 'contact_button_title',
    'type'         => 'text',
    'title'        => 'عنوان دکمه تماس',
    'dependencies' => [
        'relation' => 'AND',
        'rules' => [['field' => 'contact_button_status', 'operator' => '==', 'value' => '1']]
    ]
]);

$nader_settings->register_module_config([
    'name'         => 'contact_button_url',
    'type'         => 'url',
    'title'        => 'لینک دکمه تماس',
    'dependencies' => [
        'relation' => 'AND',
        'rules' => [['field' => 'contact_button_status', 'operator' => '==', 'value' => '1']]
    ],
    'attributes'   => ['pattern' => 'https?://.+']
]);

// بخش شبکه‌های اجتماعی
$social_platforms = [
    'facebook' => 'فیسبوک', 'twitter' => 'توییتر', 'linkedin' => 'لینکداین',
    'instagram' => 'اینستاگرام', 'telegram' => 'تلگرام', 'whatsapp' => 'واتساپ',
    'dribbble' => 'دریبل', 'behance' => 'بیهنس', 'github' => 'گیت هاب',
    'gitlab' => 'گیت لب', 'youtube' => 'یوتیوب', 'aparat' => 'آپارات',
    'eitaa' => 'ایتا', 'rubika' => 'روبیکا', 'bale' => 'بله',
    'igap' => 'آیگپ', 'soroushplus' => 'سروش پلاس', 'email' => 'ایمیل'
];

$nader_settings->register_module_config([
    'name'    => 'social_media_status',
    'type'    => 'toggle',
    'title'   => 'وضعیت لیست شبکه‌های اجتماعی',
    'default' => 1
]);
foreach ($social_platforms as $key => $title) {
    $nader_settings->register_module_config([
        'name'         => "social_{$key}",
        'type'         => 'text',
        'title'        => $title,
        'dependencies' => [
            'relation' => 'AND',
            'rules' => [['field' => 'social_media_status', 'operator' => '==', 'value' => '1']]
        ]
    ]);
}

// بخش کلمات کلیدی
$nader_settings->register_module_config([
    'name'   => 'search_keywords',
    'type'   => 'repeater',
    'title'  => 'کلمات کلیدی جستجو',
    'fields' => [
        [
            'name'  => 'keyword',
            'type'  => 'text',
            'title' => 'کلمه کلیدی'
        ]
    ]
]);

// ------------------ رندر تب ------------------
add_action('nader_settings_tab_general', function($nader_settings_instance) {
    // بررسی وجود کلاس‌های ضروری
    $required_classes = ['Nader_Toggle','Nader_Select','Nader_Image','Nader_Text','Nader_Color','Nader_Repeater'];
    foreach ($required_classes as $class) {
        if (!class_exists($class)) {
            echo "<div class='nader-error'>خطا: کلاس {$class} یافت نشد!</div>";
            return;
        }
    }
    $nader_settings = Nader_Settings::instance();
    $social_platforms = [
        'facebook' => 'فیسبوک', 'twitter' => 'توییتر', 'linkedin' => 'لینکداین',
        'instagram' => 'اینستاگرام', 'telegram' => 'تلگرام', 'whatsapp' => 'واتساپ',
        'dribbble' => 'دریبل', 'behance' => 'بیهنس', 'github' => 'گیت هاب',
        'gitlab' => 'گیت لب', 'youtube' => 'یوتیوب', 'aparat' => 'آپارات',
        'eitaa' => 'ایتا', 'rubika' => 'روبیکا', 'bale' => 'بله',
        'igap' => 'آیگپ', 'soroushplus' => 'سروش پلاس', 'email' => 'ایمیل'
    ];

    // بخش لودینگ
    (new Nader_Toggle($nader_settings->get_registered_module_config('loading_status')))->render();
    (new Nader_Select($nader_settings->get_registered_module_config('loading_type')))->render();
    (new Nader_Image($nader_settings->get_registered_module_config('loading_logo')))->render();
    (new Nader_Text($nader_settings->get_registered_module_config('loading_text')))->render();
    echo '<hr>';

    // بخش دنبال کننده موس
    (new Nader_Toggle($nader_settings->get_registered_module_config('mouse_follower_status')))->render();
    (new Nader_Color($nader_settings->get_registered_module_config('mouse_follower_color')))->render();
    echo '<hr>';

    // بخش دکمه تماس
    (new Nader_Toggle($nader_settings->get_registered_module_config('contact_button_status')))->render();
    (new Nader_Text($nader_settings->get_registered_module_config('contact_button_title')))->render();
    (new Nader_Text($nader_settings->get_registered_module_config('contact_button_url')))->render();
    echo '<hr>';

    // بخش شبکه‌های اجتماعی
    (new Nader_Toggle($nader_settings->get_registered_module_config('social_media_status')))->render();
    foreach ($social_platforms as $key => $title) {
        (new Nader_Text($nader_settings->get_registered_module_config("social_{$key}")))->render();
    }
    echo '<hr>';

    // بخش کلمات کلیدی
    (new Nader_Repeater($nader_settings->get_registered_module_config('search_keywords')))->render();

}, 10, 1);