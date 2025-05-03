<?php
/**
 * ماژول Select (لیست انتخابی) برای چارچوب تنظیمات نادر
 */

if (!defined('ABSPATH'))
    exit; // اگر مستقیماً فراخوانی شده، خارج شو.

class Nader_Select extends Nader_Module {

    /**
     * سازنده کلاس. آرگومان‌های پیش‌فرض ماژول Select را تنظیم می‌کند.
     */
    public function __construct(array $args = []) {
        $default_args = [
            'name'         => 'select_field',
            'title'        => 'فیلد انتخابی',
            'description'  => '',
            'required'     => false,
            'default'      => '',
            'multilang'    => false,
            'multiple'     => false, // امکان انتخاب چندگزینه
            'options'      => [], // آرایه‌ای از گزینه‌ها (value => label)
            'placeholder'  => 'یک گزینه انتخاب کنید...',
            'class'        => 'nader-select',
            'attributes'   => [], // ویژگی‌های HTML اضافی
            'allow_custom' => false // امکان وارد کردن مقدار دستی
        ];

        parent::__construct(wp_parse_args($args, $default_args));

        // اعتبارسنجی اولیه
        if (empty($this->args['options']) || !is_array($this->args['options'])) {
            error_log('Nader Select: ماژول بدون گزینه‌های معتبر نمونه‌سازی شده است.');
            $this->args['options'] = [];
        }
    }

    /**
     * پیاده‌سازی متد render_field برای نمایش تگ select
     */
    protected function render_field(string $name, $value): void {
        $is_multiple = (bool) $this->args['multiple'];
        $current_values = $is_multiple ? (array) $value : [$value];
        $attributes = [
            'name'     => $name . ($is_multiple ? '[]' : ''),
            'id'       => $name,
            'class'    => $this->args['class'],
            'multiple' => $is_multiple ? 'multiple' : null,
            'data-placeholder' => $this->args['placeholder']
        ];

        // ویژگی‌های اضافی
        if (!empty($this->args['attributes'])) {
            $attributes = array_merge($attributes, $this->args['attributes']);
        }

        // اگر الزامی است
        if ($this->is_required() && !$is_multiple) {
            $attributes['required'] = 'required';
        }

        // اگر اجازه مقدار دستی داده شده
        if ($this->args['allow_custom']) {
            $attributes['data-allow-custom'] = 'true';
        }

        echo '<select ';
        foreach ($attributes as $attr => $val) {
            if ($val !== null) {
                printf('%s="%s" ', esc_attr($attr), esc_attr($val));
            }
        }
        echo '>';

        // گزینه placeholder
        if ($this->args['placeholder']) {
            printf(
                '<option value="">%s</option>',
                esc_html($this->args['placeholder'])
            );
        }

        // گزینه‌های اصلی
        foreach ($this->args['options'] as $option_value => $label) {
            $selected = in_array($option_value, $current_values) ? 'selected' : '';
            printf(
                '<option value="%s" %s>%s</option>',
                esc_attr($option_value),
                $selected,
                esc_html($label)
            );
        }

        // گزینه سفارشی اگر وجود دارد
        if ($this->args['allow_custom'] && $value && !array_key_exists($value, $this->args['options'])) {
            printf(
                '<option value="%s" selected>%s</option>',
                esc_attr($value),
                esc_html($value)
            );
        }

        echo '</select>';

        $this->render_errors($name);
    }

    /**
     * اعتبارسنجی سفارشی
     */
    protected function custom_validation($value, string $lang = ''): array {
        $errors = [];
        $is_multiple = $this->args['multiple'];
        $submitted_values = $is_multiple ? (array) $value : [$value];

        if ($this->is_required() && empty(array_filter($submitted_values))) {
            $errors[] = $this->get_error_message('required', $lang);
            return $errors;
        }

        foreach ($submitted_values as $val) {
            if (!empty($val) && !array_key_exists($val, $this->args['options']) && !$this->args['allow_custom']) {
                $errors[] = sprintf('گزینه "%s" نامعتبر است.', esc_html($val));
            }
        }

        return $errors;
    }

    /**
     * پاکسازی مقدار
     */
    protected function sanitize_value($value) {
        $is_multiple = $this->args['multiple'];
        $clean_values = $is_multiple ? (array) $value : [$value];
        $allowed = array_keys($this->args['options']);

        // اگر اجازه مقدار دستی داده شده
        if ($this->args['allow_custom']) {
            $clean_values = array_map('sanitize_text_field', $clean_values);
        } else {
            $clean_values = array_intersect($clean_values, $allowed);
        }

        return $is_multiple ? $clean_values : reset($clean_values);
    }

    /**
     * پیام‌های خطای سفارشی
     */
    protected function get_error_message(string $code, string $lang = ''): string {
        $messages = [
            'invalid_option' => 'گزینه انتخاب شده در لیست معتبر نیست.'
        ];
        return $messages[$code] ?? parent::get_error_message($code, $lang);
    }
}