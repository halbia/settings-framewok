<?php
/**
 * ماژول فیلد متنی برای چارچوب تنظیمات نادر.
 */

if (!defined('ABSPATH'))
    exit; // اگر مستقیماً فراخوانی شده، خارج شو.

// این کلاس باید از Nader_Module که در includes/class-nader-module.php تعریف شده، ارث ببرد.
// نام کلاس باید Nader_Text باشد تا توسط load_modules پیدا شود.
class Nader_Text extends Nader_Module {

    /**
     * سازنده کلاس. آرگومان‌های پیش‌فرض ماژول متنی را تنظیم می‌کند.
     *
     * @param array $args آرایه‌ای از آرگومان‌ها که آرگومان‌های پیش‌فرض را override می‌کنند.
     */
    public function __construct(array $args = []) {
        // آرگومان‌های پیش‌فرض خاص فیلد متنی را تعریف کن
        $default_args = [
            'name'        => 'text_field', // نام پیش‌فرض اگر در آرگومان‌ها داده نشود
            'title'       => 'فیلد متنی', // عنوان پیش‌فرض
            'description' => '',
            'required'    => false,
            'default'     => '', // مقدار پیش‌فرض برای فیلد متنی معمولاً رشته خالی است
            'multilang'   => false,
            'placeholder' => '',
            'maxlength'   => '',
            'minlength'   => '', // minlength برای اعتبارسنجی در custom_validation اضافه شده است
            'pattern'     => '', // الگوی regex برای اعتبارسنجی
            'class'       => 'regular-text', // کلاس CSS پیش‌فرض برای input
            'wrapper_class' => '',
            'attributes'  => [], // ویژگی‌های HTML اضافی
        ];

        // ادغام آرگومان‌های ورودی با آرگومان‌های پیش‌فرض والد و این کلاس
        parent::__construct(wp_parse_args($args, $default_args));
    }

    /**
     * پیاده‌سازی متد انتزاعی render_field برای نمایش عنصر input متنی.
     *
     * @param string $name ویژگی 'name' کامل HTML برای فیلد (مثال: 'my_field' یا 'my_field__fa').
     * @param mixed $value مقدار فعلی فیلد برای پیش‌پر کردن input.
     */
    protected function render_field(string $name, $value): void {
        $attributes = [
            'type'        => 'text',
            'name'        => esc_attr($name), // نام کامل فیلد
            'id'          => esc_attr($name), // ID فیلد (می‌تواند همان نام باشد)
            'value'       => esc_attr($value), // مقدار فعلی فیلد
            'placeholder' => esc_attr($this->args['placeholder']),
            'maxlength'   => esc_attr($this->args['maxlength']),
            'minlength'   => esc_attr($this->args['minlength']),
            'pattern'     => esc_attr($this->args['pattern']),
            // اضافه کردن کلاس CSS پیش‌فرض و کلاس‌های ارسالی در آرگومان‌ها
            'class'       => esc_attr('nader-text-input ' . $this->args['class']),
            'dir'         => 'auto', // جهت متن خودکار
        ];

        // اضافه کردن ویژگی required اگر فیلد الزامی است
        if ($this->is_required()) {
            $attributes['required'] = 'required';
        }

        // اضافه کردن ویژگی‌های HTML اضافی از آرگومان‌ها
        if (!empty($this->args['attributes']) && is_array($this->args['attributes'])) {
            $attributes = array_merge($attributes, array_map('esc_attr', $this->args['attributes']));
        }


        // شروع تگ input
        echo '<input ';

        // رندر کردن ویژگی‌های HTML
        foreach ($attributes as $attr => $val) {
            // فقط ویژگی‌هایی که مقدار دارند را رندر کن (مخصوصاً برای maxlength, pattern و غیره)
            // یا ویژگی‌های بولی (مثل required) که مقدارشان true است.
            if (is_bool($val)) {
                if ($val) {
                    echo esc_attr($attr) . ' ';
                }
            } elseif (!empty($val) || $val === 0 || $val === '0') { // چک دقیق‌تر برای empty برای مقادیر غیر بولی
                printf('%s="%s" ', esc_attr($attr), $val);
            }
        }

        // پایان تگ input
        echo '>';
    }

    /**
     * پیاده‌سازی متد custom_validation برای افزودن قوانین اعتبارسنجی خاص فیلد متنی.
     *
     * @param mixed $value مقدار ارسالی فیلد.
     * @param string $lang کد زبان (برای فیلدهای چندزبانه).
     * @return array آرایه‌ای از پیام‌های خطای سفارشی.
     */
    protected function custom_validation($value, string $lang = ''): array {
        $errors = [];
        $value_str = (string) $value; // اطمینان از کار با رشته برای توابع متنی

        // اگر فیلد الزامی نیست و خالی است، نیازی به اعتبارسنجی طول یا الگو نیست.
        // اعتبارسنجی الزامی در متد validate والد انجام می‌شود.
        if (!$this->is_required() && empty($value_str)) {
            return [];
        }


        // اعتبارسنجی طول حداقل
        if (!empty($this->args['minlength']) && mb_strlen($value_str) < (int)$this->args['minlength']) {
            $errors[] = sprintf('حداقل طول مجاز %s کاراکتر است.', $this->args['minlength']);
        }

        // اعتبارسنجی طول حداکثر
        if (!empty($this->args['maxlength']) && mb_strlen($value_str) > (int)$this->args['maxlength']) {
            $errors[] = sprintf('حداکثر طول مجاز %s کاراکتر است.', $this->args['maxlength']);
        }

        // اعتبارسنجی الگوی regex
        // pattern باید یک رشته معتبر regex باشد، مثال: '/^[a-zA-Z0-9]+$/'
        if (!empty($this->args['pattern']) && is_string($this->args['pattern'])) {
            // بررسی می‌کنیم که آیا الگوی regex معتبر است
            if (@preg_match($this->args['pattern'], null) === false) {
                error_log('Nader Settings: الگوی regex نامعتبر برای فیلد "' . $this->get_name() . '": ' . $this->args['pattern']);
                // در محیط توسعه می‌توانید خطا دهید
                // trigger_error('Regex pattern is invalid for field "' . $this->get_name() . '"', E_USER_WARNING);
            } elseif (!preg_match($this->args['pattern'], $value_str)) {
                // از get_error_message برای دریافت پیام خطا استفاده کن
                $errors[] = $this->get_error_message('pattern_mismatch', $lang);
            }
        }


        // می‌توانید اعتبارسنجی‌های دیگر مخصوص فیلد متنی را اینجا اضافه کنید.
        // مثال: چک کردن اینکه فقط شامل حروف فارسی باشد برای زبان فارسی
        /*
        if ($lang === 'fa' && !empty($value_str) && !preg_match('/^[\p{Arabic}\s\d\p{P}]+$/u', $value_str)) { // اضافه کردن اعداد و علائم نگارشی
             $errors[] = 'فقط کاراکترهای فارسی، اعداد و علائم نگارشی مجاز است.';
        }
        */


        return $errors; // برگرداندن آرایه‌ای از خطاهای پیدا شده
    }

    /**
     * پیاده‌سازی متد sanitize_value برای پاکسازی مقدار فیلد متنی.
     *
     * @param mixed $value مقدار ارسالی فیلد.
     * @return string مقدار پاکسازی شده.
     */
    protected function sanitize_value($value) {
        // استفاده از sanitize_text_field برای پاکسازی ورودی متنی
        // اگر فیلد می‌تواند شامل خطوط جدید باشد (مثل textarea)، از sanitize_textarea_field استفاده کنید.
        // اطمینان از اینکه ورودی رشته است قبل از پاکسازی
        return sanitize_text_field(trim((string) $value)); // حذف فضای خالی ابتدا و انتها و پاکسازی
    }

    /**
     * override کردن متد get_error_message برای افزودن پیام‌های خطای خاص فیلد متنی.
     *
     * @param string $code کد خطا.
     * @param string $lang کد زبان.
     * @return string پیام خطا.
     */
    protected function get_error_message(string $code, string $lang = ''): string {
        $messages = [
            'pattern_mismatch' => 'قالب ورودی صحیح نیست.',
            // می‌توانید پیام‌های خاص دیگر مربوط به Nader_Text را اینجا اضافه کنید.
            // مثال: 'minlength' => sprintf('حداقل طول %s کاراکتر است', $this->args['minlength']), // اگر در custom_validation استفاده شود.
            // مثال: 'maxlength' => sprintf('حداکثر طول %s کاراکتر است', $this->args['maxlength']),
        ];

        // ابتدا پیام‌های خاص این ماژول را چک کن، سپس به پیام‌های خطای پایه در کلاس والد رجوع کن.
        // فیلتر کردن پیام‌های خطا (با وجود درخواست عدم استفاده از __())
        $message = $messages[$code] ?? parent::get_error_message($code, $lang);

        // فیلتر برای پیام خطای خاص این ماژول و کد خطا
        $message = apply_filters("nader_module_error_message_{$this->get_name()}_{$code}", $message, $this);

        // فیلتر عمومی برای کد خطا
        $message = apply_filters("nader_module_error_message_{$code}", $message, $this);


        return $message;
    }


    // متد handle_submission در کلاس والد پیاده‌سازی شده و برای Nader_Text کار می‌کند،
    // چون فقط نیاز به validate و sanitize مقادیر تکی یا مقادیر تکی در هر زبان دارد.
    // اگر یک ماژول ساختار داده‌ای پیچیده‌تری داشت (مانند Repeater)، نیاز بود handle_submission را override کند.

}
