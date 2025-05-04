<?php
$nader_settings = Nader_Settings::instance();

// ثبت تب متفرقه
$nader_settings->register_tab([
    'id'    => 'header_footer_none_elementor',
    'title' => 'هدر و فوتر غیر المنتوری',
    'order' => 5
]);


// بخش دکمه تماس
$nader_settings->register_module_config([
    'name'    => 'contact_button_status',
    'type'    => 'toggle',
    'title'   => 'وضعیت دکمه تماس',
    'default' => 1
]);

$nader_settings->register_module_config([
    'name'         => 'contact_button_title',
    'type'         => 'text',
    'title'        => 'عنوان دکمه تماس',
    'required'     => true,
    'dependencies' => [
        'relation' => 'AND',
        'rules'    => [['field' => 'contact_button_status', 'operator' => '==', 'value' => '1']]
    ]
]);

$nader_settings->register_module_config([
    'name'         => 'contact_button_url',
    'type'         => 'url',
    'title'        => 'لینک دکمه تماس',
    'required'     => true,
    'dependencies' => [
        'relation' => 'AND',
        'rules'    => [['field' => 'contact_button_status', 'operator' => '==', 'value' => '1']]
    ],
    'attributes'   => ['pattern' => 'https?://.+']
]);

// بخش شبکه‌های اجتماعی
$social_platforms = [
    'facebook'    => 'فیسبوک',
    'twitter'     => 'توییتر',
    'linkedin'    => 'لینکداین',
    'instagram'   => 'اینستاگرام',
    'telegram'    => 'تلگرام',
    'whatsapp'    => 'واتساپ',
    'dribbble'    => 'دریبل',
    'behance'     => 'بیهنس',
    'github'      => 'گیت هاب',
    'gitlab'      => 'گیت لب',
    'youtube'     => 'یوتیوب',
    'aparat'      => 'آپارات',
    'eitaa'       => 'ایتا',
    'rubika'      => 'روبیکا',
    'bale'        => 'بله',
    'igap'        => 'آیگپ',
    'soroushplus' => 'سروش پلاس',
    'email'       => 'ایمیل'
];

$nader_settings->register_module_config([
    'name'    => 'social_media_status',
    'type'    => 'toggle',
    'title'   => 'وضعیت شبکه‌های اجتماعی',
    'default' => 1
]);
foreach ($social_platforms as $key => $title) {
    $nader_settings->register_module_config([
        'name'         => "social_{$key}",
        'type'         => 'text',
        'title'        => $title,
        'dependencies' => [
            'relation' => 'AND',
            'rules'    => [['field' => 'social_media_status', 'operator' => '==', 'value' => '1']]
        ]
    ]);
}

$nader_settings->register_module_config([
    'name'        => 'copyright_text',
    'type'        => 'textarea',
    'title'       => 'متن کپی رایت',
]);

add_action('nader_settings_tab_header_footer_none_elementor', function($nader_settings) {

    $social_platforms = [
        'facebook'    => 'فیسبوک',
        'twitter'     => 'توییتر',
        'linkedin'    => 'لینکداین',
        'instagram'   => 'اینستاگرام',
        'telegram'    => 'تلگرام',
        'whatsapp'    => 'واتساپ',
        'dribbble'    => 'دریبل',
        'behance'     => 'بیهنس',
        'github'      => 'گیت هاب',
        'gitlab'      => 'گیت لب',
        'youtube'     => 'یوتیوب',
        'aparat'      => 'آپارات',
        'eitaa'       => 'ایتا',
        'rubika'      => 'روبیکا',
        'bale'        => 'بله',
        'igap'        => 'آیگپ',
        'soroushplus' => 'سروش پلاس',
        'email'       => 'ایمیل'
    ];

    ?>

    <div class="nader-fields-group">
        <h4>دکمه تماس در هدر غیر المنتوری</h4>
        <div class="row">
            <div class="third">
                <?php (new Nader_Toggle($nader_settings->get_registered_module_config('contact_button_status')))->render(); ?>
            </div>
            <div class="third">
                <?php (new Nader_Text($nader_settings->get_registered_module_config('contact_button_title')))->render(); ?>
            </div>
            <div class="third">
                <?php (new Nader_Text($nader_settings->get_registered_module_config('contact_button_url')))->render(); ?>
            </div>
        </div>
    </div>

    <hr/>

    <div class="nader-fields-group">
        <h4>شبکه های اجتماعی در منوی موبایل غیر المنتوری</h4>
        <div class="row">
            <div class="full">
                <?php (new Nader_Toggle($nader_settings->get_registered_module_config('social_media_status')))->render(); ?>
            </div>
        </div>

        <div class="row">
            <?php foreach ($social_platforms as $key => $title) { ?>
                <div class="quarter">
                    <?php (new Nader_Text($nader_settings->get_registered_module_config("social_{$key}")))->render(); ?>
                </div>
            <?php } ?>
        </div>
    </div>

    <hr/>

    <?php (new Nader_Textarea($nader_settings->get_registered_module_config("copyright_text")))->render(); ?>
    <p class="nader-settings-notice" style="margin-top: 8px;">
        این متن فقط در فوتر ساده غیر المنتوری نمایش داده خواهد شد.<br /> برای فوترهایی که با المنتور ساخته شده اند، لطفا فوتر را با المنتور باز کرده و تغییر دهید!
    </p>
    <?php
});
