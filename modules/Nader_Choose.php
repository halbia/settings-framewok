<?php
/**
 * ماژول انتخابگر ایجکسی (جستجو در پست‌ها، ترم‌ها، کاربران) برای چارچوب تنظیمات نادر.
 */

if (!defined('ABSPATH'))
    exit; // اگر مستقیماً فراخوانی شده، خارج شو.

class Nader_Choose extends Nader_Module {

    /**
     * سازنده کلاس. آرگومان‌های پیش‌فرض ماژول انتخابگر را تنظیم می‌کند.
     */
    public function __construct(array $args = []) {
        $default_args = [
            'name'        => 'choose_field',
            'title'       => 'فیلد انتخابگر',
            'description' => '',
            'required'    => false,
            'default'     => '', // پیش‌فرض می‌تواند یک شناسه تکی یا آرایه خالی باشد.
            'multilang'   => false,
            'multiple'    => false, // آیا اجازه انتخاب چندین گزینه را می‌دهد؟
            'query'       => [], // الزامی: آرایه پیکربندی کوئری (type, post_type, taxonomy, role و...)
            'placeholder' => 'برای جستجو تایپ کنید...', // متن placeholder در فیلد جستجو
            'class'       => 'nader-choose-select', // کلاس CSS برای اتصال JS (الزامی برای JS)
            'attributes'  => [], // ویژگی‌های HTML اضافی برای تگ select
            'initial_value_label' => '', // برچسب اولیه برای مقدار پیش‌فرض/ذخیره شده اگر از قبل انتخاب شده بود
        ];

        parent::__construct(wp_parse_args($args, $default_args));

        // بررسی آرگومان الزامی 'query' و 'type' در آن
        if (empty($this->args['query']) || !is_array($this->args['query']) || empty($this->args['query']['type'])) {
            error_log('Nader Settings: ماژول Choose "' . $this->get_name() . '" بدون آرگومان query معتبر نمونه‌سازی شده است.');
            // در محیط توسعه می‌توانید خطا ایجاد کنید.
            // trigger_error('Choose module "' . $this->get_name() . '" requires a valid "query" array with a "type".', E_USER_WARNING);
            $this->args['query'] = ['type' => '']; // برای جلوگیری از خطا
        }

        // اطمینان از اینکه مقدار پیش‌فرض با حالت multiple همخوانی دارد.
        if ($this->args['multiple'] && !is_array($this->args['default'])) {
            $this->args['default'] = [];
        } elseif (!$this->args['multiple'] && is_array($this->args['default'])) {
            $this->args['default'] = reset($this->args['default']); // اولین عنصر آرایه را بگیر
        }

        // اگر فیلد الزامی است و multiple نیست و مقدار پیش‌فرض empty است، default را روی رشته خالی تنظیم کن
        // تا required HTML attribute عمل کند، اما validate/sanitize آن را بپذیرند اگر مقدار ارسال نشد.
        // اگر required و multiple است، آرایه خالی معتبر است.
    }

    /**
     * پیاده‌سازی متد render_field برای نمایش تگ select.
     * این select در ابتدا فقط شامل مقدار ذخیره شده (اگر وجود دارد) خواهد بود.
     * گزینه‌های دیگر از طریق جستجوی ایجکسی بارگذاری می‌شوند.
     */
    protected function render_field(string $name, $value): void {
        $current_value = $value; // مقدار ذخیره شده
        $is_multiple = (bool)$this->args['multiple'];
        $query_args = $this->args['query']; // پیکربندی کوئری

        // تبدیل مقدار ذخیره شده به آرایه شناسه‌ها (برای استفاده راحت‌تر)
        $selected_ids = [];
        if ($is_multiple && is_array($current_value)) {
            $selected_ids = array_filter(array_map('intval', $current_value), function($id) { return $id > 0; });
        } elseif (!$is_multiple && (is_numeric($current_value) && (int)$current_value > 0)) {
            $selected_ids = [(int)$current_value];
        }


        // دریافت اطلاعات اولیه برای مقادیر ذخیره شده (شناسه و برچسب نمایش)
        // این برای نمایش مقادیر انتخاب شده در زمان بارگذاری صفحه لازم است،
        // زیرا SelectWoo نیاز به این اطلاعات برای مقادیر اولیه دارد.
        $initial_options = [];
        if (!empty($selected_ids)) {
            // فراخوانی تابع کمکی برای واکشی اطلاعات بر اساس نوع و شناسه‌ها
            $initial_items = $this->get_initial_selected_items($selected_ids, $query_args['type']);
            foreach($initial_items as $item) {
                $initial_options[(string)$item['id']] = $item['text'];
            }
        }


        $attributes = [
            'name'        => esc_attr($name) . ($is_multiple ? '[]' : ''), // نام با [] برای multiple
            'id'          => esc_attr($name), // ID فیلد
            'class'       => esc_attr('nader-choose-select ' . $this->args['class']), // کلاس اصلی + کلاس‌های ارسالی
            'data-placeholder' => esc_attr($this->args['placeholder']), // Placeholder برای SelectWoo
            'data-query-args' => esc_attr(json_encode($query_args)), // ارسال آرگومان‌های کوئری به JS
            'dir'         => 'rtl', // جهت راست به چپ برای فیلد انتخابگر
        ];

        if ($is_multiple) {
            $attributes['multiple'] = 'multiple'; // ویژگی multiple برای تگ select
        }

        if ($this->is_required() && !$is_multiple) {
            // برای انتخابگر تکی الزامی: اگر مقدار ذخیره نشده، required HTML attribute را اضافه کن.
            // برای انتخابگر چندگانه الزامی: اگر آرایه خالی است، توسط validate در PHP چک می‌شود، HTML attribute لازم نیست.
            if (empty($selected_ids)) {
                $attributes['required'] = 'required';
            }
        }


        // اضافه کردن ویژگی‌های HTML اضافی
        if (!empty($this->args['attributes']) && is_array($this->args['attributes'])) {
            $attributes = array_merge($attributes, array_map('esc_attr', $this->args['attributes']));
        }

        echo '<select ';

        foreach ($attributes as $attr => $val) {
            if (is_bool($val)) {
                if ($val) { echo esc_attr($attr) . ' '; }
            } elseif (!empty($val) || $val === 0 || $val === '0' || $val === []) { // شامل آرایه خالی برای data-query-args
                printf('%s="%s" ', esc_attr($attr), $val);
            }
        }

        echo '>';

        // اضافه کردن مقادیر اولیه/ذخیره شده به عنوان گزینه‌های select
        // این برای SelectWoo لازم است تا مقادیر انتخاب شده فعلی را نمایش دهد.
        if (!empty($initial_options)) {
            foreach ($initial_options as $id => $label) {
                // گزینه باید هم مقدار (value) و هم متن (label) داشته باشد.
                $selected = selected(true, true, false); // چون اینها مقادیر انتخاب شده اولیه هستند، تگ selected را اضافه کن.
                printf('<option value="%s" %s>%s</option>', esc_attr($id), $selected, esc_html($label));
            }
        }


        echo '</select>';

        // Placeholder برای نمایش خطا
        $this->render_errors($name);

        // نکته: نیاز به کد جاوا اسکریپت در admin.js برای فعال کردن SelectWoo بر روی تگ select با کلاس .nader-choose-select
        // و پیکربندی ایجکس آن برای فراخوانی اکشن wp_ajax_nader_choose_search.
    }

    /**
     * دریافت اطلاعات (شناسه و برچسب) برای آیتم‌های انتخاب شده اولیه.
     * بر اساس نوع کوئری (پست، ترم، کاربر) اطلاعات لازم را از وردپرس واکشی می‌کند.
     *
     * @param array $ids آرایه‌ای از شناسه‌های آیتم‌های انتخاب شده.
     * @param string $type نوع کوئری ('post', 'taxonomy', 'user').
     * @return array آرایه‌ای از آرایه‌ها با کلیدهای 'id' و 'text'.
     */
    private function get_initial_selected_items(array $ids, string $type): array {
        $items = [];

        if (empty($ids)) {
            return $items;
        }

        switch ($type) {
            case 'post':
                $posts = get_posts([
                    'post_type'      => $this->args['query']['post_type'] ?? 'post', // استفاده از post_type از آرگومان query یا پیش‌فرض 'post'
                    'post__in'       => $ids,
                    'posts_per_page' => -1,
                    'orderby'        => 'post__in', // حفظ ترتیب شناسه‌ها
                    'ignore_sticky_posts' => true,
                    'post_status'    => ['publish', 'private', 'draft', 'pending', 'future'], // شامل وضعیت‌های مختلف پست
                ]);
                foreach ($posts as $post) {
                    $items[] = ['id' => $post->ID, 'text' => esc_html($post->post_title)];
                }
                break;

            case 'taxonomy':
                $taxonomies = $this->args['query']['taxonomy'] ?? [];
                if (empty($taxonomies)) {
                    error_log('Nader Settings: ماژول Choose "' . $this->get_name() . '" از نوع taxonomy بدون مشخص کردن taxonomy استفاده شده است.');
                    break;
                }
                // اطمینان از اینکه taxonomies آرایه‌ای از رشته‌ها است
                $taxonomies = (array) $taxonomies;
                $taxonomies = array_filter($taxonomies, 'is_string');

                $terms = get_terms([
                    'taxonomy'   => $taxonomies,
                    'include'    => $ids,
                    'hide_empty' => false, // نمایش ترم‌های خالی هم
                ]);
                if (!is_wp_error($terms)) {
                    foreach ($terms as $term) {
                        // نمایش نام ترم و تاکسونومی آن
                        $items[] = ['id' => $term->term_id, 'text' => esc_html($term->name) . ' (' . esc_html($term->taxonomy) . ')'];
                    }
                }
                break;

            case 'user':
                $args = [
                    'include' => $ids,
                    'number'  => -1,
                ];
                // می‌توانید آرگومان role از query را اضافه کنید
                if (!empty($this->args['query']['role'])) {
                    $args['role__in'] = (array) $this->args['query']['role'];
                }
                $users = get_users($args);
                foreach ($users as $user) {
                    $items[] = ['id' => $user->ID, 'text' => esc_html($user->display_name)]; // یا user_login, user_email
                }
                break;

            default:
                // نوع کوئری نامعتبر
                break;
        }

        return $items;
    }


    /**
     * پیاده‌سازی متد custom_validation برای اعتبارسنجی فیلد انتخابگر.
     * اعتبارسنجی می‌کند که شناسه‌های ارسالی معتبر باشند.
     *
     * @param mixed $value مقدار ارسالی فیلد (شناسه تکی یا آرایه‌ای از شناسه‌ها).
     * @param string $lang کد زبان.
     * @return array آرایه‌ای از پیام‌های خطا.
     */
    protected function custom_validation($value, string $lang = ''): array {
        $errors = [];
        $is_multiple = (bool)$this->args['multiple'];
        $query_type = $this->args['query']['type'] ?? '';

        $submitted_ids = $is_multiple ? (array) $value : [$value];
        $submitted_ids = array_filter(array_map('intval', $submitted_ids), function($id) { return $id > 0; }); // فقط شناسه‌های عددی مثبت

        // اگر فیلد الزامی است و هیچ شناسه معتبری انتخاب نشده
        if ($this->is_required() && empty($submitted_ids)) {
            $errors[] = $this->get_error_message('required', $lang);
            return $errors; // اگر الزامی و خالی بود، اعتبارسنجی بیشتر لازم نیست.
        }

        // اگر الزامی نیست و خالی است، اعتبارسنجی بیشتر لازم نیست.
        if (!$this->is_required() && empty($submitted_ids)) {
            return [];
        }


        // اختیاری: اعتبارسنجی دقیق‌تر اینکه شناسه‌های ارسالی واقعا در وردپرس وجود دارند و از نوع صحیح هستند.
        // این می‌تواند در حجم بالا کند باشد، اما امنیت و اعتبار داده‌ها را افزایش می‌دهد.
        /*
        $valid_ids_count = 0;
        $initial_items = $this->get_initial_selected_items($submitted_ids, $query_type); // استفاده مجدد از متد کمکی
        if (!empty($initial_items)) {
             $valid_ids_count = count($initial_items);
        }

        if ($valid_ids_count !== count($submitted_ids)) {
             // این نشان می‌دهد برخی از شناسه‌های ارسالی نامعتبر یا حذف شده‌اند.
             $errors[] = 'برخی از موارد انتخاب شده نامعتبر یا حذف شده‌اند.';
        }
        */


        return $errors;
    }

    /**
     * پیاده‌سازی متد sanitize_value برای پاکسازی مقدار انتخاب شده.
     * شناسه‌ها را به صورت عدد صحیح (تکی یا آرایه‌ای) برمی‌گرداند.
     */
    protected function sanitize_value($value) {
        $is_multiple = (bool)$this->args['multiple'];

        if ($is_multiple) {
            // برای multiple، آرایه‌ای از شناسه‌های عددی صحیح برگردان.
            $sanitized_ids = is_array($value) ? array_map('intval', $value) : [];
            // فیلتر کردن شناسه‌های نامعتبر (اعداد صحیح مثبت)
            return array_filter($sanitized_ids, function($id) { return $id > 0; });
        } else {
            // برای تکی، شناسه عددی صحیح یا 0 برگردان.
            $sanitized_id = is_numeric($value) ? (int)$value : 0;
            // اگر شناسه 0 یا کمتر بود و فیلد الزامی نبود، null یا رشته خالی هم می‌تواند گزینه باشد.
            // اما برای consistency بهتر است 0 را برگردانیم اگر مقدار معتبر نبود.
            return ($sanitized_id > 0) ? $sanitized_id : 0;
        }
    }
}