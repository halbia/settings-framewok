<?php

/**
 * هندلر درخواست AJAX برای جستجو در ماژول Choose.
 * نتایج جستجو (پست‌ها، ترم‌ها یا کاربران) را به صورت JSON برمی‌گرداند.
 */
add_action('wp_ajax_nader_choose_search', function ()
{
    // بررسی Nonce برای امنیت
    check_ajax_referer('nader_settings_nonce', 'nonce');

    // بررسی قابلیت کاربر (مثلاً قابلیت مدیریت گزینه‌ها برای دسترسی به تنظیمات)
    if (!current_user_can('manage_options')) {
        wp_send_json_error('دسترسی غیرمجاز.', 403);
    }

    // دریافت عبارت جستجو از درخواست
    $search_term = sanitize_text_field($_GET['term'] ?? '');      // SelectWoo عبارت جستجو را در پارامتر 'term' می‌فرستد.

    // دریافت آرگومان‌های کوئری و نوع از درخواست
    // این آرگومان‌ها باید توسط JS از data-query-args فیلد select خوانده و ارسال شوند.
    $query_args_json = stripslashes($_GET['query_args'] ?? '{}'); // دریافت به صورت JSON string
    $query_args = json_decode($query_args_json, true);            // تبدیل JSON به آرایه PHP

    if (empty($query_args) || !is_array($query_args) || empty($query_args['type'])) {
        wp_send_json_error('آرگومان‌های کوئری نامعتبر.', 400);
    }

    $results = []; // آرایه‌ای برای نگهداری نتایج جستجو (با فرمت {id: ..., text: ...})
    $query_type = $query_args['type'];
    $limit = 20; // تعداد حداکثر نتایج برای جلوگیری از بار زیاد

    // اجرای کوئری بر اساس نوع
    switch ($query_type) {
        case 'post':
            $post_type = $query_args['post_type'] ?? 'post';
            $post_statuses = $query_args['post_status'] ?? ['publish']; // وضعیت‌های پست مورد جستجو

            $posts = get_posts([
                'post_type'      => $post_type,
                's'              => $search_term, // عبارت جستجو
                'posts_per_page' => $limit,
                'post_status'    => $post_statuses,
                'fields'         => 'ids', // فقط شناسه‌ها را دریافت کن
            ]);
            if (!empty($posts)) {
                // برای نمایش عنوان، باید دوباره واکشی شوند یا در کوئری اول 'fields' را حذف کنید.
                // روش بهینه تر: فقط شناسه‌ها را بگیرید و سپس اطلاعات کامل را برای نمایش واکشی کنید.
                $posts_full = get_posts([
                    'post_type'      => $post_type,
                    'post__in'       => $posts, // واکشی بر اساس شناسه‌های پیدا شده
                    'posts_per_page' => -1,
                    'orderby'        => 'post__in', // حفظ ترتیب
                    'post_status'    => $post_statuses, // دوباره وضعیت‌ها را چک کن
                ]);
                foreach ($posts_full as $post) {
                    $results[] = ['id'   => $post->ID,
                                  'text' => esc_html($post->post_title) . ' (' . esc_html($post->post_type) . ')'
                    ]; // نمایش عنوان و نوع پست
                }
            }
            break;

        case 'taxonomy':
            $taxonomies = $query_args['taxonomy'] ?? [];
            if (empty($taxonomies)) {
                wp_send_json_error('تاکسونومی برای جستجو مشخص نشده است.', 400);
            }
            $taxonomies = (array)$taxonomies;
            $taxonomies = array_filter($taxonomies, 'is_string');

            $terms = get_terms([
                'taxonomy'   => $taxonomies,
                'search'     => $search_term, // عبارت جستجو
                'number'     => $limit,
                'hide_empty' => false, // نمایش ترم‌های خالی هم در نتایج جستجو
            ]);
            if (!is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $results[] = ['id'   => $term->term_id,
                                  'text' => esc_html($term->name) . ' (' . esc_html($term->taxonomy) . ')'
                    ]; // نمایش نام ترم و تاکسونومی
                }
            }
            break;

        case 'user':
            $args = [
                'search'         => '*' . $search_term . '*', // جستجو در فیلدهای نام کاربری، ایمیل و نمایش نام
                'search_columns' => ['user_login', 'user_email', 'display_name'],
                'number'         => $limit,
            ];
            // می‌توانید آرگومان role از query را اضافه کنید
            if (!empty($query_args['role'])) {
                $args['role__in'] = (array)$query_args['role'];
            }

            $users = get_users($args);
            foreach ($users as $user) {
                $results[] = ['id'   => $user->ID,
                              'text' => esc_html($user->display_name) . ' (' . esc_html($user->user_login) . ')'
                ]; // نمایش نمایش نام و نام کاربری
            }
            break;

        default:
            // نوع کوئری نامعتبر
            wp_send_json_error('نوع کوئری نامعتبر.', 400);
            break;
    }

    // ارسال نتایج به صورت JSON با فرمت مورد انتظار SelectWoo ({results: [...], pagination: {more: false}})
    wp_send_json([
        'results'    => $results,
        'pagination' => ['more' => false] // در این مثال، pagination پیاده سازی نشده است.
    ]);
});
