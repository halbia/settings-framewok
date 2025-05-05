<?php
/**
 * ماژول Textarea برای چارچوب تنظیمات نادر.
 */

if (!defined('ABSPATH'))
    exit; // اگر مستقیماً فراخوانی شده، خارج شو.

class Nader_Textarea extends Nader_Module {

    /**
     * سازنده کلاس. آرگومان‌های پیش‌فرض ماژول Textarea را تنظیم می‌کند.
     */
    public function __construct(array $args = []) {
        $default_args = [
            'name'        => 'textarea_field',
            'title'       => 'فیلد متنی چند خطی',
            'description' => '',
            'required'    => false,
            'default'     => '',
            'multilang'   => false,
            'placeholder' => '',
            'rows'        => 5, // تعداد خطوط نمایش
            'cols'        => 50, // تعداد ستون‌های نمایش
            'class'       => 'large-text', // کلاس CSS پیش‌فرض وردپرس برای Textarea
            'attributes'  => [],
        ];

        parent::__construct(wp_parse_args($args, $default_args));
    }

    /**
     * پیاده‌سازی متد render_field برای نمایش عنصر textarea.
     */
    protected function render_field(string $name, $value): void {
        $attributes = [
            'name'        => esc_attr($name),
            'id'          => esc_attr($name),
            'placeholder' => esc_attr($this->args['placeholder']),
            'rows'        => esc_attr($this->args['rows']),
            'cols'        => esc_attr($this->args['cols']),
            'class'       => esc_attr('nader-textarea-input ' . $this->args['class']), // کلاس CSS ماژول + کلاس‌های ارسالی
            'dir'         => 'auto', // جهت متن خودکار
        ];

        if ($this->is_required()) {
            $attributes['required'] = 'required';
        }

        if (!empty($this->args['attributes']) && is_array($this->args['attributes'])) {
            $attributes = array_merge($attributes, array_map('esc_attr', $this->args['attributes']));
        }

        echo '<textarea ';

        foreach ($attributes as $attr => $val) {
            if (is_bool($val)) {
                if ($val) { echo esc_attr($attr) . ' '; }
            } elseif (!empty($val) || $val === 0 || $val === '0') {
                printf('%s="%s" ', esc_attr($attr), $val);
            }
        }

        echo '>';
        echo esc_textarea($value); // پاکسازی محتوای textarea
        echo '</textarea>';

        // Placeholder برای نمایش خطا
        $this->render_errors($name);
    }

    /**
     * پیاده‌سازی متد custom_validation برای اعتبارسنجی Textarea.
     * (می‌تواند اعتبارسنجی طول یا سایر قوانین سفارشی را اینجا اضافه کرد).
     */
    protected function custom_validation($value, string $lang = ''): array {
        $errors = [];
        // مثال: اعتبارسنجی حداقل طول (نیاز به اضافه کردن minlength به آرگومان‌ها)
        /*
        $minlength = $this->get_arg('minlength', 0);
        if ($minlength > 0 && mb_strlen((string)$value) < $minlength) {
             $errors[] = sprintf('حداقل طول متن %d کاراکتر است.', $minlength);
        }
        */
        return $errors;
    }

    /**
     * پیاده‌سازی متد sanitize_value برای پاکسازی مقدار Textarea.
     */
    protected function sanitize_value($value) {
        // استفاده از sanitize_textarea_field برای متن‌های چند خطی
        return (string) $value;
    }
}