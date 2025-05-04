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
    'order' => 20
]);

// ثبت فیلدها
$nader_settings->register_module_config([
    'name'  => 'ajax_login_status',
    'title' => 'وضعیت ورود/عضویت ایجکسی',
    'type'  => 'toggle',
]);

$nader_settings->register_module_config([
    'name'  => 'corner_btn_status',
    'title' => 'وضعیت دکمه گوشه ای ورود/عضویت ایجکسی',
    'type'  => 'toggle'
]);

$nader_settings->register_module_config([
    'name'  => 'login_btn_bg',
    'title' => 'رنگ دکمه ورود',
    'type'  => 'color',
]);

$nader_settings->register_module_config([
    'name'  => 'login_btn_icon_color',
    'title' => 'رنگ آیکون دکمه ورود',
    'type'  => 'color',
]);

$nader_settings->register_module_config([
    'name'  => 'login_redirect',
    'title' => 'آدرس ریدایرکت پس از ورود',
    'type'  => 'text'
]);

$nader_settings->register_module_config([
    'name'  => 'logout_redirect',
    'title' => 'آدرس ریدایرکت پس از خروج',
    'type'  => 'text',
]);

$nader_settings->register_module_config([
    'name'  => 'login_form_text_top',
    'title' => 'متن بالای فرم ورود',
    'type'  => 'text',
]);

$nader_settings->register_module_config([
    'name'  => 'login_form_text_bottom',
    'title' => 'متن پایین فرم ورود',
    'type'  => 'text',
]);

$nader_settings->register_module_config([
    'name'    => 'mobile_login',
    'title'   => 'ورود با شماره موبایل',
    'type'    => 'toggle',
    'default' => 0
]);

$nader_settings->register_module_config([
    'name'    => 'sms_service',
    'title'   => 'سرویس پیامکی',
    'type'    => 'radio',
    'options' => [
        'meli-payamak' => 'ملی پیامک',
        'ip-panel'     => 'آی پی پنل',
        'sms-ir'       => 'اس ام اس . آی آر',
        'kaveh-negar'  => 'کاوه نگار'
    ],
    'default' => 'meli-payamak'
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
add_action('nader_settings_tab_login_register', function() {
    ?>
    <div class="nader-login-settings">

        <?php
        $ajax_login = new Nader_Toggle([
            'name'        => 'ajax_login_status',
            'title'       => 'وضعیت ورود/عضویت ایجکسی اختصاصی',
            'description' => 'غیرفعال کردن این، کل بخش ورود اختصاصی را غیرفعال می‌کند',
            'default'     => 1
        ]);
        $ajax_login->render();
        ?>


        <div class="nader-fields-group" style="margin-top: 28px">
            <h4>دکمه شناور گوشه ای</h4>
            <div class="row">
                <div class="third">
                    <?php
                    $corner_btn = new Nader_Toggle([
                        'name'        => 'corner_btn_status',
                        'title'       => 'وضعیت دکمه گوشه ای',
                        'description' => 'برای حذف بخش عضویت، از تنظیمات وردپرس -> عمومی، نام نویسی را غیرفعال کنید',
                        'default'     => 1
                    ]);
                    $corner_btn->render();
                    ?>
                </div>
                <div class="third">
                    <?php
                    $btn_color = new Nader_Color([
                        'name'  => 'login_btn_bg',
                        'title' => 'رنگ دکمه ورود'
                    ]);
                    $btn_color->render();
                    ?>
                </div>
                <div class="third">
                    <?php
                    $icon_color = new Nader_Color([
                        'name'  => 'login_btn_icon_color',
                        'title' => 'رنگ آیکون دکمه ورود'
                    ]);
                    $icon_color->render();
                    ?>
                </div>
            </div>
        </div>


        <!-- ریدایرکت‌ها -->
        <div class="nader-fields-group">
            <h4>ریدایرکت</h4>
            <div class="row">
                <div class="half">
                    <?php
                    $login_redirect = new Nader_Text([
                        'name'        => 'login_redirect',
                        'title'       => 'آدرس ریدایرکت پس از ورود',
                        'description' => 'برای صفحه اصلی خالی بگذارید. به http یا https بودن سایت دقت کنید!'
                    ]);
                    $login_redirect->render();
                    ?>
                </div>
                <div class="half">
                    <?php
                    $logout_redirect = new Nader_Text([
                        'name'        => 'logout_redirect',
                        'title'       => 'آدرس ریدایرکت پس از خروج',
                        'description' => 'برای صفحه اصلی خالی بگذارید. به http یا https بودن سایت دقت کنید!'
                    ]);
                    $logout_redirect->render();
                    ?>
                </div>
            </div>
        </div>

        <!-- متون فرم -->
        <div class="nader-fields-group">
            <h4>متون فرم ورود</h4>
            <div class="row">
                <div class="half">
                    <?php
                    $top_text = new Nader_Text([
                        'name'  => 'login_form_text_top',
                        'title' => 'متن بالای فرم ورود'
                    ]);
                    $top_text->render();
                    ?>
                </div>
                <div class="half">
                    <?php
                    $bottom_text = new Nader_Text([
                        'name'  => 'login_form_text_bottom',
                        'title' => 'متن پایین فرم ورود'
                    ]);
                    $bottom_text->render();
                    ?>
                </div>
            </div>
        </div>

        <!-- ورود با موبایل -->
        <div class="nader-fields-group">
            <h4>ورود/عضویت با موبایل</h4>
            <div class="row">
                <div class="half">
                    <?php
                    $mobile_login = new Nader_Toggle([
                        'name'  => 'mobile_login',
                        'title' => 'ورود با شماره موبایل'
                    ]);
                    $mobile_login->render();
                    ?>
                </div>
                <div class="half sms_services">
                    <?php
                    $sms_service = new Nader_Radio([
                        'name'         => 'sms_service',
                        'title'        => 'سرویس پیامکی',
                        'options'      => [
                            'meli-payamak' => 'ملی پیامک',
                            'ip-panel'     => 'آی پی پنل',
                            'sms-ir'       => 'اس ام اس . آی آر',
                            'kaveh-negar'  => 'کاوه نگار'
                        ],
                        'dependencies' => [
                            'relation' => 'AND',
                            'rules'    => [
                                [
                                    'field'    => 'mobile_login',
                                    'operator' => '==',
                                    'value'    => '1'
                                ]
                            ]
                        ]
                    ]);
                    $sms_service->render();
                    ?>
                </div>
            </div>

            <div class="row sms_services_fields">
                <div class="full">
                    <?php
                    $bottom_text = new Nader_Text([
                        'name'         => 'sms_login_username',
                        'title'        => 'نام کاربری',
                        'dependencies' => [
                            'relation' => 'AND',
                            'rules'    => [
                                [
                                    'field'    => 'mobile_login',
                                    'operator' => '==',
                                    'value'    => '1'
                                ]
                            ]
                        ]
                    ]);
                    $bottom_text->render();
                    ?>
                </div>
                <div class="full">
                    <?php
                    $bottom_text = new Nader_Text([
                        'name'         => 'sms_login_password',
                        'title'        => 'رمز عبور / کلید وب‌سرویس',
                        'dependencies' => [
                            'relation' => 'AND',
                            'rules'    => [
                                [
                                    'field'    => 'mobile_login',
                                    'operator' => '==',
                                    'value'    => '1'
                                ]
                            ]
                        ]
                    ]);
                    $bottom_text->render();
                    ?>
                </div>
                <div class="full">
                    <?php
                    $bottom_text = new Nader_Text([
                        'name'         => 'sms_login_sender',
                        'title'        => 'شماره فرستنده',
                        'dependencies' => [
                            'relation' => 'AND',
                            'rules'    => [
                                [
                                    'field'    => 'mobile_login',
                                    'operator' => '==',
                                    'value'    => '1'
                                ]
                            ]
                        ]
                    ]);
                    $bottom_text->render();
                    ?>
                </div>
                <div class="full">
                    <?php
                    $bottom_text = new Nader_Text([
                        'name'         => 'sms_login_pattern',
                        'title'        => 'کد وب‌سرویس خدماتی(کد پترن)',
                        'dependencies' => [
                            'relation' => 'AND',
                            'rules'    => [
                                [
                                    'field'    => 'mobile_login',
                                    'operator' => '==',
                                    'value'    => '1'
                                ]
                            ]
                        ]
                    ]);
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