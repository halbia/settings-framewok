<?php
if (!defined('ABSPATH'))
    exit;
$nader_settings = Nader_Settings::instance();

// ثبت تب متفرقه
$nader_settings->register_tab([
    'id'    => 'misc',
    'title' => 'متفرقه',
    'order' => 100
]);

// ثبت پیکربندی ماژول‌ها
$nader_settings->register_module_config([
    'name'    => 'enable_projects',
    'title'   => 'فعال باشد؟',
    'type'        => 'toggle',
    'default' => 1
]);

$nader_settings->register_module_config([
    'name'        => 'project_slug',
    'title'       => 'Slug پروژه‌ها',
    'type'        => 'text',
    'default'     => 'project',
    'dependencies' => [
        'relation' => 'AND',
        'rules'    => [
            [
                'field'    => 'enable_projects',
                'operator' => '==',
                'value'    => 1
            ]
        ]
    ]
]);

$nader_settings->register_module_config([
    'name'    => 'enable_teams',
    'title'   => 'فعال باشد؟',
    'type'        => 'toggle',
    'default' => 1,
]);

$nader_settings->register_module_config([
    'name'        => 'team_slug',
    'title'       => 'Slug کارمندان',
    'default'     => 'team',
    'type'        => 'text',
    'dependencies' => [
        'relation' => 'AND',
        'rules'    => [
            [
                'field'    => 'enable_teams',
                'operator' => '==',
                'value'    => 1
            ]
        ]
    ]
]);

// رندر محتوای تب
add_action('nader_settings_tab_misc', function($nader_settings) {

    ?>

    <div class="nader-fields-group">
        <h4>پست تایپ پروژه ها</h4>
        <div class="row">
            <div class="half">
                <?php (new Nader_Toggle($nader_settings->get_registered_module_config('enable_projects')))->render(); ?>
            </div>
            <div class="half">
                <?php (new Nader_Text($nader_settings->get_registered_module_config('project_slug')))->render(); ?>
            </div>
        </div>
    </div>

    <hr>

    <div class="nader-fields-group">
        <h4>پست تایپ کارمندان</h4>
        <div class="row">
            <div class="half">
                <?php (new Nader_Toggle($nader_settings->get_registered_module_config('enable_teams')))->render(); ?>
            </div>
            <div class="half">
                <?php (new Nader_Text($nader_settings->get_registered_module_config('team_slug')))->render(); ?>
            </div>
        </div>
    </div>

    <?php
});