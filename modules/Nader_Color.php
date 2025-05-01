<?php
/**
 * ماژول Color Picker برای چارچوب تنظیمات نادر.
 */

if (!defined('ABSPATH'))
    exit; // اگر مستقیماً فراخوانی شده، خارج شو.

class Nader_Color extends Nader_Module {

    /**
     * سازنده کلاس. آرگومان‌های پیش‌فرض ماژول Color را تنظیم می‌کند.
     */
    public function __construct(array $args = []) {
        $default_args = [
            'name'        => 'color_field',
            'title'       => 'فیلد رنگ',
            'description' => '',
            'required'    => false,
            'default'     => '', // مقدار پیش‌فرض می‌تواند کد رنگ (مثال: #ffffff) باشد.
            'multilang'   => false,
            'class'       => 'nader-color-picker', // کلاس CSS برای اتصال JS وردپرس
            'attributes'  => [], // ویژگی‌های اضافی input
            'enable_alpha' => false, // فعال کردن انتخاب شفافیت (نیاز به JS وردپرس پیشرفته‌تر)
            'palettes' => true, // نمایش پالت رنگ‌های پیش‌فرض وردپرس
        ];

        parent::__construct(wp_parse_args($args, $default_args));
    }

    /**
     * پیاده‌سازی متد render_field برای نمایش فیلد ورودی رنگ.
     * جاوا اسکریپت وردپرس انتخابگر رنگ را به این فیلد متصل می‌کند (بر اساس کلاس CSS).
     */
    protected function render_field(string $name, $value): void {
        $current_value = esc_attr($value);

        $attributes = [
            'type'        => 'text', // ورودی متنی برای نمایش کد رنگ
            'name'        => esc_attr($name),
            'id'          => esc_attr($name),
            'value'       => $current_value,
            'class'       => esc_attr('nader-text-input ' . $this->args['class']), // کلاس CSS ماژول + کلاس مخصوص Color Picker وردپرس
            'data-alpha'  => $this->args['enable_alpha'] ? 'true' : 'false', // ویژگی داده برای فعال کردن شفافیت در JS وردپرس
            'data-palettes' => $this->args['palettes'] ? 'true' : 'false', // ویژگی داده برای نمایش پالت
            'dir'         => 'ltr', // جهت چپ به راست برای کدهای رنگ
        ];

        if ($this->is_required()) {
            $attributes['required'] = 'required';
        }

        if (!empty($this->args['attributes']) && is_array($this->args['attributes'])) {
            $attributes = array_merge($attributes, array_map('esc_attr', $this->args['attributes']));
        }

        echo '<input ';

        foreach ($attributes as $attr => $val) {
            if (is_bool($val)) {
                if ($val) { echo esc_attr($attr) . ' '; }
            } elseif (!empty($val) || $val === 0 || $val === '0') {
                printf('%s="%s" ', esc_attr($attr), $val);
            }
        }

        echo '/>'; // تگ input خود بسته شونده

        // Placeholder برای نمایش خطا
        $this->render_errors($name);

        // نکته: نیاز به کد جاوا اسکریپت در admin.js برای فراخوانی jQuery(selector).wpColorPicker() بر روی input با کلاس CSS.
    }

    /**
     * پیاده‌سازی متد custom_validation برای اعتبارسنجی کد رنگ.
     * اعتبارسنجی می‌کند که مقدار ارسالی یک کد رنگ معتبر باشد (مثلاً هگز دسیمال).
     */
    protected function custom_validation($value, string $lang = ''): array {
        $errors = [];
        $value_str = (string) $value;

        // اگر الزامی نیست و خالی است، اعتبارسنجی بیشتری لازم نیست.
        if (!$this->is_required() && empty($value_str)) {
            return [];
        }

        // الگوی regex برای کدهای رنگ هگز دسیمال (بدون یا با #، با/بدون آلفا)
        $hex_pattern = '/^#?([a-f0-9]{3,4}|[a-f0-9]{6}|[a-f0-9]{8})$/i'; // شامل 3، 4، 6 یا 8 کاراکتر هگز

        if (!preg_match($hex_pattern, $value_str)) {
            $errors[] = 'فرمت کد رنگ وارد شده نامعتبر است. (مثال: #RRGGBB یا #RRGGBBAA)';
        }
        // می‌توانید اعتبارسنجی‌های دیگر (مثل RGB/RGBA) را اینجا اضافه کنید.

        return $errors;
    }

    /**
     * پیاده‌سازی متد sanitize_value برای پاکسازی کد رنگ.
     * کد رنگ را به صورت هگز با علامت # در ابتدا برمی‌گرداند و مطمئن می‌شود حروف کوچک هستند.
     */
    protected function sanitize_value($value) {
        $value_str = trim((string) $value);
        $value_str = ltrim($value_str, '#'); // حذف علامت # اگر وجود دارد

        // اطمینان از اینکه فقط کاراکترهای هگز و طول صحیح باقی مانده است.
        if (preg_match('/^[a-f0-9]{3,8}$/i', $value_str)) { // چک 3 تا 8 کاراکتر هگز
            // تبدیل به طول استاندارد 6 یا 8 اگر 3 یا 4 کاراکتر بود.
            if (strlen($value_str) === 3) {
                $value_str = $value_str[0] . $value_str[0] . $value_str[1] . $value_str[1] . $value_str[2] . $value_str[2];
            } elseif (strlen($value_str) === 4 && $this->args['enable_alpha']) {
                $value_str = $value_str[0] . $value_str[0] . $value_str[1] . $value_str[1] . $value_str[2] . $value_str[2] . $value_str[3] . $value_str[3];
            } elseif (strlen($value_str) === 4 && !$this->args['enable_alpha']) {
                // اگر شفافیت فعال نیست، 4 کاراکتر آلفا دار نامعتبر است، یا به 6 کاراکتر بدون آلفا تبدیل کن.
                // فعلاً آن را نامعتبر در نظر می‌گیریم یا رشته خالی برمی‌گردانیم.
                return ''; // یا می‌توانید خطا اضافه کنید
            }

            // برگرداندن با # و حروف کوچک
            return '#' . strtolower($value_str);
        }

        // اگر نامعتبر بود، رشته خالی برگردان.
        return '';
    }
}
