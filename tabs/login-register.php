<?php
/**
 * تب تنظیمات ورود/عضویت
 */

if (!defined('ABSPATH'))
    exit;

$nader_settings = Nader_Settings::instance();

// ثبت تب
$nader_settings->register_tab([
    'id'    => 'login_register',
    'title' => 'ورود/عضویت',
    'order' => 35
]);

// ثبت فیلدها
$nader_settings->register_module_config([
    'name'  => 'ajax_login_status',
    'title' => 'وضعیت ورود/عضویت ایجکسی',
    'type'  => 'toggle',
    'description' => 'غیرفعال کردن این گزینه، کل فرایند ورود اختصاصی قالب را غیرفعال خواهد کرد.',
    'default'     => true,
]);

$nader_settings->register_module_config([
    'name'        => 'corner_buttons_ajax_login_status',
    'title'       => 'وضعیت دکمه گوشه ای',
    'type'        => 'toggle',
    'description' => 'غیرفعال کردن این گزینه، فقط دکمه گوشه ای ورود را غیرفعال خواهد کرد. اما قابلیت ورود اختصاصی غیرفعال نخواهد شد. برای فعال کردن با دکمه اختصاصی ساخته شده با المنتور، این را غیرفعال کنید.',
    'default'     => true,
]);

$nader_settings->register_module_config([
    'name' => 'corner_buttons_ajax_login_button_bg',
    'title' => 'رنگ دکمه ورود',
    'type'  => 'color',
    'dependencies' => [
        'relation' => 'AND',
        'rules'    => [['field' => 'corner_buttons_ajax_login_status', 'operator' => '==', 'value' => '1']]
    ]
]);

$nader_settings->register_module_config([
    'name' => 'corner_buttons_ajax_login_button_icon_color',
    'title' => 'رنگ آیکون دکمه ورود',
    'type'  => 'color',
    'dependencies' => [
        'relation' => 'AND',
        'rules'    => [['field' => 'corner_buttons_ajax_login_status', 'operator' => '==', 'value' => '1']]
    ]
]);

$nader_settings->register_module_config([
    'name'  => 'login_redirect',
    'title' => 'آدرس ریدایرکت پس از ورود',
    'type' => 'text',
]);

$nader_settings->register_module_config([
    'name'  => 'logout_redirect',
    'title' => 'آدرس ریدایرکت پس از خروج',
    'type'  => 'text',
]);

$nader_settings->register_module_config([
    'name' => 'login_popup_text_top',
    'title' => 'متن بالای فرم ورود',
    'type'  => 'text',
]);

$nader_settings->register_module_config([
    'name' => 'login_popup_text_bottom',
    'title' => 'متن پایین فرم ورود',
    'type'  => 'text',
]);

$nader_settings->register_module_config([
    'name'        => 'sms_login',
    'title'   => 'ورود با شماره موبایل',
    'type'    => 'toggle',
    'default'     => 1,
    'description' => 'غیرفعال کردن این گزینه، فقط حالت ورود با شماره موبایل را غیرفعال خواهد کرد.'
]);

$nader_settings->register_module_config([
    'name'    => 'sms_login_service',
    'title'   => 'سرویس پیامکی',
    'type'    => 'radio',
    'inline'  => true,
    'options' => [
        'mellipayamak' => 'ملی پیامک',
        'ippanel'      => 'آی پی پنل',
        'smsir'        => 'SMS.IR',
        'kaveh-negar'  => 'کاوه نگار'
    ],
    'default' => 'mellipayamak'
]);
$nader_settings->register_module_config([
    'name'  => 'sms_login_username',
    'title' => 'نام کاربری',
    'type'  => 'text',
]);
$nader_settings->register_module_config([
    'name'  => 'sms_login_password',
    'title' => 'رمز عبور / کلید وب‌سرویس',
    'type'  => 'text',
]);
$nader_settings->register_module_config([
    'name'  => 'sms_login_sender',
    'title' => 'شماره فرستنده',
    'type'  => 'text',
]);
$nader_settings->register_module_config([
    'name'  => 'sms_login_pattern',
    'title' => 'کد وب‌سرویس خدماتی(کد پترن)',
    'type'  => 'text',
]);


// رندر محتوا
add_action('nader_settings_tab_login_register', function($nader_settings) {
    ?>
    <div class="nader-login-settings">

        <?php
        $ajax_login = new Nader_Toggle($nader_settings->get_registered_module_config('ajax_login_status'));
        $ajax_login->render();
        ?>

        <hr>

        <div class="nader-fields-group">
            <h4>دکمه گوشه ای ورود/عضویت</h4>
            <div class="row">
                <div class="third">
                    <?php
                    $corner_btn = new Nader_Toggle($nader_settings->get_registered_module_config('corner_buttons_ajax_login_status'));
                    $corner_btn->render();
                    ?>
                </div>
                <div class="third">
                    <?php
                    $btn_color = new Nader_Color($nader_settings->get_registered_module_config('corner_buttons_ajax_login_button_bg'));
                    $btn_color->render();
                    ?>
                </div>
                <div class="third">
                    <?php
                    $icon_color = new Nader_Color($nader_settings->get_registered_module_config('corner_buttons_ajax_login_button_icon_color'));
                    $icon_color->render();
                    ?>
                </div>
            </div>
        </div>

        <hr>

        <!-- ریدایرکت‌ها -->
        <div class="nader-fields-group">
            <h4>ریدایرکت</h4>
            <div class="row">
                <div class="half">
                    <?php
                    $login_redirect = new Nader_Text($nader_settings->get_registered_module_config('login_redirect'));
                    $login_redirect->render();
                    ?>
                </div>
                <div class="half">
                    <?php
                    $logout_redirect = new Nader_Text($nader_settings->get_registered_module_config('logout_redirect'));
                    $logout_redirect->render();
                    ?>
                </div>
            </div>
        </div>

        <hr>

        <!-- متون فرم -->
        <div class="nader-fields-group">
            <h4>متون فرم ورود</h4>
            <div class="row">
                <div class="half">
                    <?php
                    $top_text = new Nader_Text($nader_settings->get_registered_module_config('login_popup_text_top'));
                    $top_text->render();
                    ?>
                </div>
                <div class="half">
                    <?php
                    $bottom_text = new Nader_Text($nader_settings->get_registered_module_config('login_popup_text_bottom'));
                    $bottom_text->render();
                    ?>
                </div>
            </div>
        </div>

        <hr>

        <!-- ورود با موبایل -->
        <div class="nader-fields-group">
            <h4>ورود/عضویت با موبایل</h4>
            <div class="row">
                <div class="half">
                    <?php
                    $mobile_login = new Nader_Toggle($nader_settings->get_registered_module_config('sms_login'));
                    $mobile_login->render();
                    ?>
                </div>
                <div class="half sms_services">
                    <?php
                    $sms_service = new Nader_Radio($nader_settings->get_registered_module_config('sms_login_service'));
                    $sms_service->render();
                    ?>
                </div>
            </div>

            <div class="row sms_services_fields">
                <div class="full">
                    <?php
                    $bottom_text = new Nader_Text($nader_settings->get_registered_module_config('sms_login_username'));
                    $bottom_text->render();
                    ?>
                </div>
                <div class="full">
                    <?php
                    $bottom_text = new Nader_Text($nader_settings->get_registered_module_config('sms_login_password'));
                    $bottom_text->render();
                    ?>
                </div>
                <div class="full">
                    <?php
                    $bottom_text = new Nader_Text($nader_settings->get_registered_module_config('sms_login_sender'));
                    $bottom_text->render();
                    ?>
                </div>
                <div class="full">
                    <?php
                    $bottom_text = new Nader_Text($nader_settings->get_registered_module_config('sms_login_pattern'));
                    $bottom_text->render();
                    ?>
                </div>
            </div>
        </div>

        <style>
            .sms_services .nader-radio-block {
                display: flex;
                column-gap: 20px;
                row-gap: 8px;
                flex-wrap: wrap;
            }
            .sms_services_fields {

            }
        </style>

    </div>

    <?php
});