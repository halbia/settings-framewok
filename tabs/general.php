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
    'default' => 1
]);

$nader_settings->register_module_config([
    'name'         => 'loading_type',
    'type'         => 'select',
    'title'        => 'نوع لودینگ',
    'default' => 'logo',
    'options' => array_merge(
        ['logo' => 'لوگو'],
        [
            'type-1'  => 'نوع 1',
            'type-2'  => 'نوع 2',
            'type-3'  => 'نوع 3',
            'type-4'  => 'نوع 4',
            'type-5'  => 'نوع 5',
            'type-6'  => 'نوع 6',
            'type-7'  => 'نوع 7',
            'type-8'  => 'نوع 8',
            'type-9'  => 'نوع 9',
            'type-10' => 'نوع 10',
        ]
    ),
    'dependencies' => [
        'relation' => 'AND',
        'rules' => [['field' => 'loading_status', 'operator' => '==', 'value' => '1']]
    ]
]);

$nader_settings->register_module_config([
    'name'         => 'loading_logo',
    'type'         => 'image',
    'title'        => 'لوگو لودینگ',
    'required' => true,
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
    'default' => 1
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

$nader_settings->register_module_config([
    'name'    => 'breadcrumb_status',
    'type'    => 'toggle',
    'title'   => 'وضعیت مسیر کاربر',
    'default' => 1
]);

// بخش کلمات کلیدی
$nader_settings->register_module_config([
    'name'   => 'search_keywords',
    'type'   => 'repeater',
    'item_label'   => 'کلمه کلیدی',
    'title'  => 'کلمات کلیدی جستجو',
    'fields' => [
        [
            'name'  => 'keyword',
            'type'  => 'text',
            'title' => ''
        ]
    ]
]);

// ------------------ رندر تب ------------------
add_action('nader_settings_tab_general', function($nader_settings) {
    ?>
    <div class="nader-fields-group">
        <h4>لودینگ</h4>
        <div class="row">
            <div class="half">
                <?php (new Nader_Toggle($nader_settings->get_registered_module_config('loading_status')))->render(); ?>
            </div>
            <div class="half">
                <?php (new Nader_Select($nader_settings->get_registered_module_config('loading_type')))->render(); ?>
            </div>
            <div class="half">
                <?php (new Nader_Image($nader_settings->get_registered_module_config('loading_logo')))->render(); ?>
            </div>
            <div class="half">
                <?php (new Nader_Text($nader_settings->get_registered_module_config('loading_text')))->render(); ?>
            </div>
        </div>
    </div>

    <hr/>

    <div class="nader-fields-group">
        <h4>دنبال کننده موس</h4>
        <div class="row">
            <div class="half">
                <?php (new Nader_Toggle($nader_settings->get_registered_module_config('mouse_follower_status')))->render(); ?>
            </div>
            <div class="half">
                <?php (new Nader_Color($nader_settings->get_registered_module_config('mouse_follower_color')))->render(); ?>
            </div>
        </div>
    </div>

        <hr/>
    <?php (new Nader_Toggle($nader_settings->get_registered_module_config('breadcrumb_status')))->render(); ?>
        <hr/>
    <?php
    (new Nader_Repeater($nader_settings->get_registered_module_config('search_keywords')))->render();

}, 10, 1);