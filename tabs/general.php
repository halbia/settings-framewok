<?php
/**
 * تعریف تب تنظیمات عمومی شامل نمونه‌هایی از تمام ماژول‌های موجود.
 * این فایل توسط کلاس Nader_Settings شامل (include) می‌شود.
 */

if (!defined('ABSPATH'))
    exit; // اگر مستقیماً فراخوانی شده، خارج شو.

// اطمینان از وجود کلاس اصلی Nader_Settings
if (!class_exists('Nader_Settings')) {
    error_log('Nader Settings: Nader_Settings class not found when loading general tab.');
    return;
}

// دریافت نمونه Singleton
$nader_settings = Nader_Settings::instance();

// 1. ثبت تب عمومی در چارچوب تنظیمات
$nader_settings->register_tab([
    'id'    => 'general', // شناسه منحصر به فرد تب
    'title' => 'تنظیمات عمومی (نمونه‌ها)', // عنوان تب نمایش داده شده در UI
    'order' => 5 // ترتیب نمایش تب (اعداد کمتر جلوتر نمایش داده می‌شوند)
]);


// --- ثبت پیکربندی ماژول‌های استفاده شده در این تب ---
// این فراخوانی‌ها باید در سطح بالای فایل (خارج از تابع) باشند تا هنگام include شدن اجرا شوند و پیکربندی‌ها ثبت گردند.

// نمونه Nader_Text (تک زبانه)
$nader_settings->register_module_config([
    'name'        => 'demo_text_single',
    'title'       => 'فیلد متنی (تک زبانه)',
    'description' => 'نمونه‌ای از ماژول Nader_Text در حالت تک زبانه.',
    'required'    => false,
    'multilang'   => false,
    'type'        => 'text',
    'placeholder' => 'یک متن اینجا وارد کنید...',
]);

// نمونه Nader_Text (چند زبانه)
$nader_settings->register_module_config([
    'name'        => 'demo_text_multi',
    'title'       => 'فیلد متنی (چند زبانه)',
    'description' => 'نمونه‌ای از ماژول Nader_Text در حالت چند زبانه.',
    'required'    => true, // این فیلد در همه زبان‌ها الزامی است
    'multilang'   => true,
    'type'        => 'text',
    'placeholder' => 'متن را برای هر زبان وارد کنید...',
]);

// نمونه Nader_Image
$nader_settings->register_module_config([
    'name'        => 'demo_image',
    'title'       => 'فیلد آپلود تصویر',
    'description' => 'نمونه‌ای از ماژول Nader_Image برای انتخاب یک تصویر.',
    'required'    => false,
    'multilang'   => false,
    'type'        => 'image',
    'button_text' => 'انتخاب تصویر',
]);

// نمونه Nader_Gallery (چند زبانه)
$nader_settings->register_module_config([
    'name'        => 'demo_gallery_multi',
    'title'       => 'فیلد گالری (چند زبانه)',
    'description' => 'نمونه‌ای از ماژول Nader_Gallery در حالت چند زبانه.',
    'required'    => false,
    'multilang'   => true,
    'type'        => 'gallery',
    'button_text' => 'انتخاب تصاویر گالری',
]);

// نمونه Nader_Radio
$nader_settings->register_module_config([
    'name'        => 'demo_radio',
    'title'       => 'فیلد رادیو باتن',
    'description' => 'نمونه‌ای از ماژول Nader_Radio.',
    'required'    => true,
    'multilang'   => false,
    'type'        => 'radio',
    'options'     => [
        'option1' => 'گزینه اول',
        'option2' => 'گزینه دوم',
        'option3' => 'گزینه سوم',
    ],
    'default'     => 'option2',
    'inline'      => true, // نمایش گزینه‌ها در یک خط
]);

// نمونه Nader_Checkbox
$nader_settings->register_module_config([
    'name'        => 'demo_checkbox',
    'title'       => 'فیلد چک‌باکس',
    'description' => 'نمونه‌ای از ماژول Nader_Checkbox.',
    'required'    => false,
    'multilang'   => false,
    'type'        => 'checkbox',
    'value'       => 'accepted', // مقداری که هنگام انتخاب ارسال می‌شود
    'label'       => 'من قوانین را مطالعه کرده‌ام.',
    'default'     => 0, // پیش‌فرض تیک نخورده
]);

// نمونه Nader_Range_Slider
$nader_settings->register_module_config([
    'name'        => 'demo_range_slider',
    'title'       => 'فیلد اسلایدر محدوده',
    'description' => 'نمونه‌ای از ماژول Nader_Range_Slider.',
    'required'    => false,
    'multilang'   => false,
    'type'        => 'range_slider',
    'min'         => 0,
    'max'         => 200,
    'step'        => 10,
    'default'     => 50,
    'unit'        => ' واحد', // اضافه کردن واحد نمایشی
    'show_value'  => true,
]);

// نمونه Nader_Textarea (تک زبانه)
$nader_settings->register_module_config([
    'name'        => 'demo_textarea_single',
    'title'       => 'فیلد Textarea (تک زبانه)',
    'description' => 'نمونه‌ای از ماژول Nader_Textarea در حالت تک زبانه.',
    'required'    => false,
    'multilang'   => false,
    'type'        => 'textarea',
    'rows'        => 3,
    'placeholder' => 'متن چند خطی اینجا...',
]);

// نمونه Nader_Textarea (چند زبانه)
$nader_settings->register_module_config([
    'name'        => 'demo_textarea_multi',
    'title'       => 'فیلد Textarea (چند زبانه)',
    'description' => 'نمونه‌ای از ماژول Nader_Textarea در حالت چند زبانه.',
    'required'    => false,
    'multilang'   => true,
    'type'        => 'textarea',
    'rows'        => 4,
    'placeholder' => 'متن چند خطی برای هر زبان...',
]);

// نمونه Nader_Wp_Editor (چند زبانه)
$nader_settings->register_module_config([
    'name'          => 'demo_wp_editor_multi',
    'title'         => 'فیلد WP Editor (چند زبانه)',
    'description'   => 'نمونه‌ای از ماژول Nader_Wp_Editor.',
    'required'      => false,
    'multilang'     => true,
    'type'          => 'wp_editor',
    'editor_height' => 250,
    'media_buttons' => true,
    'teeny'         => false,
]);

// نمونه Nader_Color
$nader_settings->register_module_config([
    'name'         => 'demo_color',
    'title'        => 'فیلد انتخابگر رنگ',
    'description'  => 'نمونه‌ای از ماژول Nader_Color.',
    'required'     => false,
    'multilang'    => false,
    'type'         => 'color',
    'default'      => '#ff0000', // رنگ قرمز
    'enable_alpha' => true, // فعال کردن شفافیت
    'palettes'     => true, // نمایش پالت رنگ‌ها
]);

// نمونه Nader_Toggle
$nader_settings->register_module_config([
    'name'        => 'demo_toggle',
    'title'       => 'فیلد سوئیچ',
    'description' => 'نمونه‌ای از ماژول Nader_Toggle.',
    'required'    => false,
    'multilang'   => false,
    'type'        => 'toggle',
    'default'     => 1, // پیش‌فرض روشن
    'label_on'    => 'فعال',
    'label_off'   => 'غیرفعال',
]);

// نمونه Nader_Choose (پست‌ها - تکی)
$nader_settings->register_module_config([
    'name'        => 'demo_choose_post',
    'title'       => 'انتخاب نوشته (تکی)',
    'description' => 'نمونه‌ای از ماژول Nader_Choose برای انتخاب یک نوشته.',
    'required'    => false,
    'multilang'   => false,
    'multiple'    => false,
    'type'        => 'choose',
    'query'       => [
        'type'        => 'post',
        'post_type'   => ['post'], // فقط نوشته‌ها
        'post_status' => ['publish'], // فقط منتشر شده‌ها
    ],
    'placeholder' => 'نوشته‌ای را جستجو کنید...',
]);

// نمونه Nader_Choose (ترم‌ها - چندگانه)
$nader_settings->register_module_config([
    'name'        => 'demo_choose_term_multi',
    'title'       => 'انتخاب دسته‌بندی‌ها (چندگانه)',
    'description' => 'نمونه‌ای از ماژول Nader_Choose برای انتخاب چند دسته‌بندی.',
    'required'    => false,
    'multilang'   => false,
    'multiple'    => true,
    'type'        => 'choose',
    'query'       => [
        'type'     => 'taxonomy',
        'taxonomy' => ['category'], // فقط دسته‌بندی‌ها
    ],
    'placeholder' => 'دسته‌بندی را جستجو کنید...',
]);

// نمونه Nader_Choose (کاربران - تکی)
$nader_settings->register_module_config([
    'name'        => 'demo_choose_user',
    'title'       => 'انتخاب کاربر (تکی)',
    'description' => 'نمونه‌ای از ماژول Nader_Choose برای انتخاب یک کاربر.',
    'required'    => false,
    'multilang'   => false,
    'multiple'    => false,
    'type'        => 'choose',
    'query'       => [
        'type' => 'user',
        'role' => ['administrator', 'editor', 'author'], // نقش‌های مورد نظر
    ],
    'placeholder' => 'کاربری را جستجو کنید...',
]);

$nader_settings->register_module_config([
    'name'        => 'demo_image_select_url_simple', // نام جدید برای تمایز
    'title'       => 'انتخاب تصویر (URL ساده)',
    'description' => 'نمونه‌ای از ماژول Nader_Image_Select با استفاده از لینک تصویر ساده.',
    'required'    => false,
    'multilang'   => false,
    'multiple'    => false, // یا true برای انتخاب چند تصویر
    'type'        => 'image_select', // <-- نوع ماژول
    'options'     => [
        'layout_left_simple'  => 'http://nader2mo.test/wp-content/uploads/2025/04/04f7f08065849a994ea5c4edea017cd4.jpg',
        'layout_right_simple' => 'http://nader2mo.test/wp-content/uploads/2025/04/04f7f08065849a994ea5c4edea017cd4.jpg',
        'layout_full_simple'  => 'http://nader2mo.test/wp-content/uploads/2025/04/04f7f08065849a994ea5c4edea017cd4.jpg',
    ],
    'default'     => 'layout_right_simple', // مقدار پیش‌فرض (value)
]);

$nader_settings->register_module_config([
    'name'   => 'team',
    'type' => 'repeater',
    'fields' => [
        ['name' => 'name', 'type' => 'text', 'title' => 'نام'],
        ['name' => 'photo', 'type' => 'image', 'title' => 'عکس'],
        ['name' => 'color', 'type' => 'color', 'title' => 'رنگ']
    ]
]);


// 2. تعریف محتوای تب عمومی برای رندر کردن
// این بخش با استفاده از اکشن پویا 'nader_settings_tab_{tab_id}' انجام می‌شود.
add_action('nader_settings_tab_general', function($nader_settings_instance) {
    // اطمینان از وجود کلاس‌های ماژولی که در اینجا استفاده می‌کنید
    if (!class_exists('Nader_Text') || !class_exists('Nader_Image') || !class_exists('Nader_Gallery') || !class_exists('Nader_Radio') || !class_exists('Nader_Checkbox') || !class_exists('Nader_Range_Slider') || !class_exists('Nader_Textarea') || !class_exists('Nader_Wp_Editor') || !class_exists('Nader_Color') || !class_exists('Nader_Toggle') || !class_exists('Nader_Choose')) {
        error_log('Nader Settings: Required module classes not found for General tab rendering.');
        echo '<p>مشکل در بارگذاری کلاس‌های ماژول. لطفاً لاگ‌های خطا را بررسی کنید.</p>';
        return;
    }

    // رندر هر نمونه ماژول

    // Nader_Text (تک زبانه)
    $text_single_field = new Nader_Text([
        'name'        => 'demo_text_single',
        'title'       => 'فیلد متنی (تک زبانه)',
        'description' => 'نمونه‌ای از ماژول Nader_Text در حالت تک زبانه.',
        'placeholder' => 'یک متن اینجا وارد کنید...',
    ]);
    $text_single_field->render();
    echo '<hr>'; // جداکننده

    // Nader_Text (چند زبانه)
    $text_multi_field = new Nader_Text([
        'name'        => 'demo_text_multi',
        'title'       => 'فیلد متنی (چند زبانه)',
        'description' => 'نمونه‌ای از ماژول Nader_Text در حالت چند زبانه.',
        'required'    => true,
        'multilang'   => true,
        'placeholder' => 'متن را برای هر زبان وارد کنید...',
    ]);
    $text_multi_field->render();
    echo '<hr>'; // جداکننده

    // Nader_Image
    $image_field = new Nader_Image([
        'name'        => 'demo_image',
        'title'       => 'فیلد آپلود تصویر',
        'description' => 'نمونه‌ای از ماژول Nader_Image برای انتخاب یک تصویر.',
        'button_text' => 'انتخاب تصویر نمونه',
    ]);
    $image_field->render();
    echo '<hr>'; // جداکننده

    // Nader_Gallery (چند زبانه)
    $gallery_multi_field = new Nader_Gallery([
        'name'        => 'demo_gallery_multi',
        'title'       => 'فیلد گالری (چند زبانه)',
        'description' => 'نمونه‌ای از ماژول Nader_Gallery در حالت چند زبانه.',
        'multilang'   => true,
        'button_text' => 'انتخاب تصاویر نمونه گالری',
    ]);
    $gallery_multi_field->render();
    echo '<hr>'; // جداکننده

    // Nader_Radio
    $radio_field = new Nader_Radio([
        'name'        => 'demo_radio',
        'title'       => 'فیلد رادیو باتن',
        'description' => 'نمونه‌ای از ماژول Nader_Radio.',
        'required'    => true,
        'options'     => [
            'option1' => 'گزینه اول',
            'option2' => 'گزینه دوم',
            'option3' => 'گزینه سوم',
        ],
        'default'     => 'option2',
        'inline'      => true,
    ]);
    $radio_field->render();
    echo '<hr>'; // جداکننده

    // Nader_Checkbox
    $checkbox_field = new Nader_Checkbox([
        'name'        => 'demo_checkbox',
        'title'       => 'فیلد چک‌باکس',
        'description' => 'نمونه‌ای از ماژول Nader_Checkbox.',
        'value'       => 'accepted',
        'label'       => 'من نمونه قوانین را مطالعه کرده‌ام.',
    ]);
    $checkbox_field->render();
    echo '<hr>'; // جداکننده

    // Nader_Range_Slider
    $range_slider_field = new Nader_Range_Slider([
        'name'        => 'demo_range_slider',
        'title'       => 'فیلد اسلایدر محدوده',
        'description' => 'نمونه‌ای از ماژول Nader_Range_Slider.',
        'min'         => 0,
        'max'         => 200,
        'step'        => 10,
        'default'     => 50,
        'unit'        => ' واحد',
    ]);
    $range_slider_field->render();
    echo '<hr>'; // جداکننده

    // Nader_Textarea (تک زبانه)
    $textarea_single_field = new Nader_Textarea([
        'name'        => 'demo_textarea_single',
        'title'       => 'فیلد Textarea (تک زبانه)',
        'description' => 'نمونه‌ای از ماژول Nader_Textarea در حالت تک زبانه.',
        'rows'        => 3,
        'placeholder' => 'متن چند خطی اینجا...',
    ]);
    $textarea_single_field->render();
    echo '<hr>'; // جداکننده

    // Nader_Textarea (چند زبانه)
    $textarea_multi_field = new Nader_Textarea([
        'name'        => 'demo_textarea_multi',
        'title'       => 'فیلد Textarea (چند زبانه)',
        'description' => 'نمونه‌ای از ماژول Nader_Textarea در حالت چند زبانه.',
        'multilang'   => true,
        'rows'        => 4,
        'placeholder' => 'متن چند خطی برای هر زبان...',
    ]);
    $textarea_multi_field->render();
    echo '<hr>'; // جداکننده

    // Nader_Wp_Editor (چند زبانه)
    $wp_editor_multi_field = new Nader_Wp_Editor([
        'name'          => 'demo_wp_editor_multi',
        'title'         => 'فیلد WP Editor (چند زبانه)',
        'description'   => 'نمونه‌ای از ماژول Nader_Wp_Editor.',
        'multilang'     => true,
        'editor_height' => 250,
        'media_buttons' => true,
    ]);
    $wp_editor_multi_field->render();
    echo '<hr>'; // جداکننده

    // Nader_Color
    $color_field = new Nader_Color([
        'name'         => 'demo_color',
        'title'        => 'فیلد انتخابگر رنگ',
        'description'  => 'نمونه‌ای از ماژول Nader_Color.',
        'default'      => '#ff0000',
        'enable_alpha' => true,
    ]);
    $color_field->render();
    echo '<hr>'; // جداکننده

    // Nader_Toggle
    $toggle_field = new Nader_Toggle([
        'name'        => 'demo_toggle',
        'title'       => 'فیلد سوئیچ',
        'description' => 'نمونه‌ای از ماژول Nader_Toggle.',
        'default'     => 1,
        'label_on'    => 'روشن',
        'label_off'   => 'خاموش',
    ]);
    $toggle_field->render();
    echo '<hr>'; // جداکننده

    // Nader_Choose (پست‌ها - تکی)
    $choose_post_field = new Nader_Choose([
        'name'        => 'demo_choose_post',
        'title'       => 'انتخاب نوشته (تکی)',
        'description' => 'نمونه‌ای از ماژول Nader_Choose برای انتخاب یک نوشته.',
        'multilang'   => false, // یا true اگر نوشته ویژه برای هر زبان متفاوت است
        'multiple'    => false,
        'query'       => [
            'type'        => 'post',
            'post_type'   => ['post', 'page'],
            'post_status' => ['publish', 'private'],
        ],
        'placeholder' => 'نوشته‌ای را جستجو کنید...',

        'dependencies' => [
            'relation' => 'AND',
            'rules' => [
                [
                    'field' => 'demo_toggle',
                    'operator' => '==',
                    'value' => '1'
                ]
            ]
        ]
    ]);
    $choose_post_field->render();
    echo '<hr>'; // جداکننده

    // Nader_Choose (ترم‌ها - چندگانه)
    $choose_term_multi_field = new Nader_Choose([
        'name'        => 'demo_choose_term_multi',
        'title'       => 'انتخاب دسته‌بندی‌ها (چندگانه)',
        'description' => 'نمونه‌ای از ماژول Nader_Choose برای انتخاب چند دسته‌بندی.',
        'multilang'   => false,
        'multiple'    => true,
        'query'       => [
            'type'     => 'taxonomy',
            'taxonomy' => ['category', 'post_tag'],
        ],
        'placeholder' => 'دسته‌بندی یا برچسبی را جستجو کنید...',
    ]);
    $choose_term_multi_field->render();
    echo '<hr>'; // جداکننده

    // Nader_Choose (کاربران - تکی)
    $choose_user_field = new Nader_Choose([
        'name'        => 'demo_choose_user',
        'title'       => 'انتخاب کاربر (تکی)',
        'description' => 'نمونه‌ای از ماژول Nader_Choose برای انتخاب یک کاربر.',
        'multilang'   => false,
        'multiple'    => false,
        'query'       => [
            'type' => 'user',
            'role' => ['administrator', 'editor'],
        ],
        'placeholder' => 'کاربری را جستجو کنید...',
    ]);
    $choose_user_field->render();
    echo '<hr>'; // جداکننده

    $image_select_url_simple_field = new Nader_Image_Select([
        'name'        => 'demo_image_select_url_simple',
        'title'       => 'انتخاب تصویر (URL ساده)',
        'description' => 'نمونه‌ای از ماژول Nader_Image_Select با استفاده از لینک تصویر ساده.',
        // سایر آرگومان‌ها مطابق با register_module_config
        'multiple'    => false,
        'options'     => [
            'layout_left_simple'  => 'http://nader2mo.test/wp-content/uploads/2025/04/04f7f08065849a994ea5c4edea017cd4.jpg',
            'layout_right_simple' => 'http://nader2mo.test/wp-content/uploads/2025/04/04f7f08065849a994ea5c4edea017cd4.jpg',
            'layout_full_simple'  => 'http://nader2mo.test/wp-content/uploads/2025/04/04f7f08065849a994ea5c4edea017cd4.jpg',
        ],
        'default'     => 'layout_right_simple',
    ]);
    $image_select_url_simple_field->render();
    echo '<hr>'; // جداکننده

    // رندر فیلد
    $repeater = new Nader_Repeater([
        'name' => 'team',
        'fields' => [
            ['name' => 'name', 'type' => 'text', 'title' => 'نام'],
            ['name' => 'photo', 'type' => 'image', 'title' => 'عکس'],
            ['name' => 'color', 'type' => 'color', 'title' => 'رنگ']
        ]
    ]);
    $repeater->render();

}, 10, 1); // پایان add_action برای تب عمومی