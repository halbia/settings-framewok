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
    'name'        => 'disable_projects',
    'title'       => 'غیرفعال سازی پروژه‌ها',
    'type'        => 'toggle',
]);

$nader_settings->register_module_config([
    'name'        => 'project_slug',
    'title'       => 'Slug پروژه‌ها',
    'type'        => 'text',
]);

$nader_settings->register_module_config([
    'name'        => 'disable_teams',
    'title'       => 'غیرفعال سازی کارمندان',
    'type'        => 'toggle',
]);

$nader_settings->register_module_config([
    'name'        => 'team_slug',
    'title'       => 'Slug کارمندان',
    'type'        => 'text',
]);

// رندر محتوای تب
add_action('nader_settings_tab_misc', function($nader_settings_instance) {
    if (!class_exists('Nader_Toggle') || !class_exists('Nader_Text')) {
        echo '<p>خطا در بارگذاری ماژول‌ها</p>';
        return;
    }

    // غیرفعال سازی پروژه‌ها
    $toggle_projects = new Nader_Toggle([
        'name' => 'disable_projects',
        'title' => 'غیرفعال سازی پروژه‌ها'
    ]);
    $toggle_projects->render();

    // Slug پروژه‌ها
    $project_slug = new Nader_Text([
        'name' => 'project_slug',
        'title' => 'Slug پروژه‌ها',
        'dependencies' => [
            'relation' => 'AND',
            'rules' => [
                [
                    'field' => 'disable_projects',
                    'operator' => '==',
                    'value' => '0'
                ]
            ]
        ]
    ]);
    $project_slug->render();

    // غیرفعال سازی کارمندان
    $toggle_teams = new Nader_Toggle([
        'name' => 'disable_teams',
        'title' => 'غیرفعال سازی کارمندان'
    ]);
    $toggle_teams->render();

    // Slug کارمندان
    $team_slug = new Nader_Text([
        'name' => 'team_slug',
        'title' => 'Slug کارمندان',
        'dependencies' => [
            'relation' => 'AND',
            'rules' => [
                [
                    'field' => 'disable_teams',
                    'operator' => '==',
                    'value' => '0'
                ]
            ]
        ]
    ]);
    $team_slug->render();
});