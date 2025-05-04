<?php
/**
 * کلاس پایه برای تمام ماژول‌های تنظیمات نادر (انواع فیلدها).
 */

if (!defined('ABSPATH'))
    exit; // اگر مستقیماً فراخوانی شده، خارج شو.

abstract class Nader_Module {

    protected $args = []; // آرگومان‌های تعریف‌کننده ماژول (نام، عنوان، مقدار پیش‌فرض و غیره)
    // ما خطاها را در آرایه $errors ذخیره نمی‌کنیم، بلکه آن‌ها را از handle_submission برمی‌گردانیم تا کلاس اصلی تجمیع کند.
    // protected $errors = [];

    /**
     * سازنده کلاس. آرگومان‌های ماژول را تنظیم می‌کند.
     *
     * @param array $args آرایه‌ای از آرگومان‌ها برای ماژول.
     */
    public function __construct(array $args = []) {
        // تعریف آرگومان‌های پایه و ادغام با آرگومان‌های ارائه شده
        $this->args = wp_parse_args($args, [
            'name'        => '', // الزامی: نام منحصر به فرد برای فیلد تنظیمات (مثال: 'site_title')
            'title'       => '', // اختیاری: عنوانی که در رابط کاربری نمایش داده می‌شود
            'description' => '', // اختیاری: توضیحات یا متن راهنما
            'required'    => false, // اختیاری: آیا این فیلد الزامی است؟
            'default'     => null, // اختیاری: مقدار پیش‌فرض اگر تنظیماتی ذخیره نشده باشد. استفاده از null امکان تشخیص از رشته خالی ذخیره شده را می‌دهد.
            'multilang'   => false, // اختیاری: آیا این فیلد چندزبانه است؟
            'options'     => [], // اختیاری: برای انواع select, radio, checkbox
            'placeholder' => '', // اختیاری: متن جایگزین (Placeholder) برای فیلدهای متنی
            'class'       => '', // اختیاری: کلاس‌های CSS اضافی برای عنصر ورودی (input)
            'wrapper_class' => '', // اختیاری: کلاس‌های CSS اضافی برای wrapper فیلد
            'attributes'  => [], // اختیاری: آرایه‌ای از ویژگی‌های HTML اضافی برای عنصر ورودی
            'dependencies' => []
        ]);

        // اطمینان از تنظیم بودن نام و عنوان
        if (empty($this->args['name'])) {
            error_log('Nader Settings: ماژول بدون نام نمونه‌سازی شده است.');
        }
        if (empty($this->args['title'])) {
//            error_log('Nader Settings: ماژول "' . $this->args['name'] . '" بدون عنوان نمونه‌سازی شده است.');
        }
    }

    /**
     * رندر کردن HTML برای فیلد(های) ماژول.
     * این متد مسئول رندر کردن wrapper و فراخوانی متدهای رندر فیلد خاص است.
     * مقادیر فعلی را به صورت خودکار واکشی می‌کند.
     */
    final public function render(): void {
        // اکشن قبل از رندر ماژول
        do_action('nader_module_before_render', $this->args['name'], $this);

        if ($this->is_multilang()) {
            $this->render_multilang_fields();
        } else {
            $this->render_single_field();
        }

        // اکشن بعد از رندر ماژول
        do_action('nader_module_after_render', $this->args['name'], $this);
    }

    /**
     * رندر کردن wrapper و فیلد داخلی برای یک فیلد تک‌زبانه.
     */
    private function render_single_field(): void {
        $field_name = $this->get_field_name(); // دریافت نام کامل فیلد (نام اصلی)
        $current_value = $this->get_field_value(); // دریافت مقدار ذخیره شده

        $this->render_field_wrapper_start($field_name); // شروع wrapper فیلد
        $this->render_field_label(); // رندر عنوان فیلد
        $this->render_description(); // رندر توضیحات
        $this->render_field($field_name, $current_value); // متد انتزاعی که توسط کلاس‌های فرزند پیاده‌سازی می‌شود
        $this->render_errors($field_name); // رندر Placeholder برای خطاها (توسط JS پر می‌شود)
        $this->render_field_wrapper_end(); // پایان wrapper فیلد
    }

    /**
     * رندر کردن wrapper و فیلدهای داخلی برای یک فیلد چندزبانه.
     */
    private function render_multilang_fields(): void {
        $languages = Nader_Settings::instance()->get_active_languages();
        $base_name = $this->get_name();

        // شروع wrapper برای گروه فیلدهای چندزبانه
        $this->render_field_wrapper_start($base_name, true);

        // رندر عنوان اصلی برای گروه چندزبانه
        $this->render_field_label();
        $this->render_description();

        // کانتینر برای فیلدهای هر زبان
        echo '<div class="nader-multilang-fields">';
        foreach ($languages as $lang) {
            $field_name = $this->get_field_name($lang); // دریافت نام کامل فیلد (نام اصلی__کد_زبان)
            $current_value = $this->get_field_value($lang); // دریافت مقدار ذخیره شده برای این زبان
            $this->render_language_field_start($lang, $field_name);
            $this->render_language_label($lang); // عنوان مخصوص زبان
            $this->render_field($field_name, $current_value); // متد انتزاعی برای عنصر ورودی واقعی
            $this->render_errors($field_name); // رندر Placeholder برای خطاها (با نام کامل فیلد)
            $this->render_language_field_end();
        }
        echo '</div>'; // پایان کانتینر فیلدهای زبان

        $this->render_field_wrapper_end(); // پایان wrapper اصلی برای گروه چندزبانه
    }

    /**
     * متد انتزاعی: رندر کردن عنصر ورودی/فیلد HTML خاص.
     * توسط کلاس‌های ماژول فرزند (مثال: Text, Textarea, Select) پیاده‌سازی می‌شود.
     *
     * @param string $name ویژگی 'name' کامل HTML برای فیلد (شامل __lang برای چندزبانه).
     * @param mixed $value مقدار فعلی فیلد برای پیش‌پر کردن ورودی.
     */
    abstract protected function render_field(string $name, $value): void;

    /**
     * شروع wrapper برای یک فیلد یا گروه فیلد چندزبانه.
     *
     * @param string $name نام فیلد (نام اصلی برای تک‌زبانه، نام اصلی برای گروه چندزبانه).
     * @param bool $is_multilang آیا این wrapper برای گروه چندزبانه است؟
     */
    protected function render_field_wrapper_start(string $name, bool $is_multilang = false): void {
        $classes = ['nader-field-wrapper'];
        $classes[] = 'nader-field-' . str_replace('_', '-', $this->get_type()); // کلاس بر اساس نوع ماژول (مثال: nader-field-text)
        if (!empty($this->args['wrapper_class'])) {
            $classes[] = $this->args['wrapper_class'];
        }
        if ($is_multilang) {
            $classes[] = 'nader-multilang-wrapper';
        }

        $dependency_data = '';
        if (!empty($this->args['dependencies'])) {
            $dependency_data = 'data-dependencies="' . esc_attr(json_encode($this->args['dependencies'])) . '"';
        }

        echo '<div class="' . esc_attr(implode(' ', $classes)) . '" ' . $dependency_data . '>';
    }

    /**
     * پایان wrapper فیلد.
     */
    protected function render_field_wrapper_end(): void {
        echo '</div>';
    }

    /**
     * شروع wrapper برای یک فیلد زبان خاص در داخل گروه چندزبانه.
     *
     * @param string $lang کد زبان.
     * @param string $field_name نام کامل فیلد (با پسوند زبان).
     */
    protected function render_language_field_start(string $lang, string $field_name): void {
        printf(
            '<div class="nader-lang-field nader-lang-%s" data-lang="%s" data-full-name="%s">',
            esc_attr($lang),
            esc_attr($lang),
            esc_attr($field_name) // نام کامل فیلد (مثال: site_title__fa)
        );
    }

    /**
     * پایان wrapper فیلد زبان خاص.
     */
    protected function render_language_field_end(): void {
        echo '</div>';
    }


    /**
     * رندر کردن عنوان فیلد.
     */
    protected function render_field_label(): void {
        if (empty($this->args['title'])) {
            return;
        }

        echo '<label class="nader-field-label">';
        echo esc_html($this->args['title']); // عنوان اصلی ماژول

        if ($this->is_required()) {
            echo '<span class="required">*</span>'; // نشانگر الزامی بودن
        }

        echo '</label>';
    }

    /**
     * رندر کردن عنوان فیلد مخصوص یک زبان (برای فیلدهای چندزبانه).
     *
     * @param string $lang کد زبان.
     */
    protected function render_language_label(string $lang): void {
        // می‌توانید عنوان زبان را به عنوان اصلی اضافه کنید یا فقط کد زبان را نمایش دهید
        // مثال: 'عنوان سایت (FA)'
        $label_text = strtoupper(esc_html($lang));

        echo '<label class="nader-lang-label">';
        echo $label_text;

        if ($this->is_required()) {
            echo '<span class="required">*</span>';
        }

        echo '</label>';
    }


    /**
     * رندر کردن توضیحات فیلد.
     */
    protected function render_description(): void {
        if (!empty($this->args['description'])) {
            printf(
                '<p class="description">%s</p>',
                $this->args['description']
            );
        }
    }

    /**
     * رندر کردن کانتینر Placeholder برای خطاهای اعتبارسنجی.
     * این کانتینر توسط JS در سمت کلاینت با پیام‌های خطا پر می‌شود.
     *
     * @param string $field_name نام کامل فیلد (با پسوند زبان برای چندزبانه).
     */
    protected function render_errors(string $field_name): void {
        $error_id = $field_name . '-errors'; // ساخت یک ID برای کانتینر خطاها بر اساس نام کامل فیلد
        printf('<ul id="%s" class="nader-errors"></ul>', esc_attr($error_id)); // ul را با display: none شروع می‌کنیم و JS آن را نشان می‌دهد
    }


    // --- بخش مدیریت ارسال داده (Validation و Processing) ---

    /**
     * پردازش داده‌های ارسال شده برای این ماژول (اعتبارسنجی و پاکسازی).
     * این متد از کلاس Nader_Settings::save_settings فراخوانی می‌شود.
     * مسئول استخراج داده‌های مربوط به خود از آرایه کلی، اعتبارسنجی و پاکسازی آن‌ها است.
     *
     * @param array $submitted_data آرایه کامل داده‌های ارسال شده از فرم (خروجی parse_str).
     * @return array آرایه‌ای شامل 'processed_data' (داده‌های پاکسازی شده) و 'errors' (آرایه‌ای از پیام‌های خطا با کلید نام کامل فیلد).
     */
    public function handle_submission(array $submitted_data): array {
        $processed_data = [];
        $module_errors = []; // خطاها مخصوص فیلد(های) این ماژول

        if ($this->is_multilang()) {
            // پردازش فیلد چندزبانه
            $base_name = $this->get_name();
            $languages = Nader_Settings::instance()->get_active_languages();

            foreach ($languages as $lang) {
                $full_name = $this->get_field_name($lang); // مثال: site_title__fa
                // دریافت مقدار ارسالی برای این زبان از آرایه کلی $submitted_data
                // استفاده از رشته خالی اگر فیلد در داده‌های ارسالی موجود نبود
                $value = $submitted_data[$full_name] ?? '';

                // اعتبارسنجی مقدار این زبان
                $validation_errors = $this->validate($value, $lang);
                if (!empty($validation_errors)) {
                    // ذخیره خطاها با کلید نام کامل فیلد (برای نمایش در UI)
                    $module_errors[$full_name] = $validation_errors;
                } else {
                    // اگر اعتبارسنجی موفق بود، مقدار را پاکسازی (Sanitize) کن
                    $processed_value = $this->sanitize_value($value);
                    // ذخیره مقدار پاکسازی شده در processed_data با کلید نام کامل فیلد
                    $processed_data[$full_name] = $processed_value;
                }
            }

        } else {
            // پردازش فیلد تک‌زبانه
            $full_name = $this->get_field_name(); // نام اصلی فیلد
            // دریافت مقدار ارسالی از آرایه کلی $submitted_data
            $value = $submitted_data[$full_name] ?? '';

            // اعتبارسنجی مقدار
            $validation_errors = $this->validate($value);
            if (!empty($validation_errors)) {
                // ذخیره خطاها با کلید نام کامل فیلد
                $module_errors[$full_name] = $validation_errors;
            } else {
                // اگر اعتبارسنجی موفق بود، مقدار را پاکسازی کن
                $processed_value = $this->sanitize_value($value);
                // ذخیره مقدار پاکسازی شده در processed_data با کلید نام کامل فیلد
                $processed_data[$full_name] = $processed_value;
            }
        }

        // برگرداندن داده‌های پردازش شده و خطاهای مربوط به این ماژول
        return [
            'processed_data' => $processed_data,
            'errors'         => $module_errors,
        ];
    }


    /**
     * اعتبارسنجی مقدار فیلد.
     * شامل چک‌های پایه (الزامی بودن) و فراخوانی custom_validation.
     *
     * @param mixed $value مقدار ارسالی فیلد.
     * @param string $lang کد زبان (برای فیلدهای چندزبانه).
     * @return array آرایه‌ای از پیام‌های خطا.
     */
    public function validate($value, string $lang = ''): array {
        $errors = [];

        // اعتبارسنجی فیلد الزامی
        // چک می‌کنیم که مقدار خالی نباشد (برای رشته، آرایه، null). '0' و 0 نباید خالی محسوب شوند.
        $is_empty = empty($value) && !is_numeric($value);
        if ($this->is_required() && $is_empty) {
            $errors[] = $this->get_error_message('required', $lang);
        }

        // فراخوانی متد custom_validation در کلاس فرزند برای اعتبارسنجی‌های خاص‌تر
        // custom_validation باید آرایه‌ای از پیام‌های خطا برگرداند.
        $custom_errors = $this->custom_validation($value, $lang);
        if (!empty($custom_errors) && is_array($custom_errors)) {
            $errors = array_merge($errors, $custom_errors);
        }

        // فیلتر نهایی بر روی خطاها (به افزونه‌های دیگر اجازه تغییر می‌دهد)
        return apply_filters('nader_module_validation_errors', $errors, $this->args['name'], $value, $lang, $this);
    }

    /**
     * متد برای پیاده‌سازی اعتبارسنجی‌های سفارشی در کلاس‌های فرزند.
     * کلاس‌های فرزند باید این متد را برای اضافه کردن قوانین اعتبارسنجی خاص خود پیاده‌سازی کنند.
     * باید آرایه‌ای از پیام‌های خطا برگرداند.
     *
     * @param mixed $value مقدار ارسالی فیلد.
     * @param string $lang کد زبان (برای فیلدهای چندزبانه).
     * @return array آرایه‌ای از پیام‌های خطای سفارشی.
     */
    protected function custom_validation($value, string $lang = ''): array {
        // کلاس‌های فرزند باید این متد را پیاده‌سازی کرده و آرایه‌ای از پیام‌های خطا برگردانند.
        return [];
    }


    /**
     * پاکسازی (Sanitize) مقدار فیلد.
     * کلاس‌های فرزند می‌توانند این متد را برای پاکسازی‌های خاص‌تر override کنند.
     * این متد برای فیلدهای ساده تک‌زبانه یا هر عنصر تکی در فیلدهای پیچیده فراخوانی می‌شود.
     *
     * @param mixed $value مقدار ارسالی فیلد.
     * @return mixed مقدار پاکسازی شده.
     */
    protected function sanitize_value($value) {
        // پیش‌فرض: پاکسازی متن ساده. برای انواع دیگر override شود.
        // برای فیلدهای پیچیده مانند Repeater که آرایه‌ای از داده‌ها را ارسال می‌کنند،
        // منطق sanitize ممکن است پیچیده‌تر باشد و در override این متد یا در handle_submission ماژول Repeater انجام شود.
        if (is_array($value)) {
            // اگر آرایه بود، روی عناصر آن پاکسازی انجام بده
            return array_map('sanitize_text_field', $value);
        }
        return sanitize_text_field(trim((string) $value)); // حذف فضای خالی ابتدا و انتها و پاکسازی متن
    }


    /**
     * دریافت پیام خطای محلی‌سازی شده بر اساس کد.
     * کلاس‌های فرزند می‌توانند این متد را override کرده و پیام‌های خطای خاص خود را اضافه کنند.
     * از این متد در validate یا custom_validation استفاده می‌شود.
     *
     * @param string $code کد خطا (مثال: 'required').
     * @param string $lang کد زبان (برای پیام‌های خطای مخصوص زبان).
     * @return string پیام خطا.
     */
    protected function get_error_message(string $code, string $lang = ''): string {
        $messages = [
            'required' => 'پر کردن این فیلد الزامی است.', // پیام خطای فیلد الزامی
            'invalid'  => 'مقدار وارد شده نامعتبر است.', // پیام خطای عمومی نامعتبر بودن
            // پیام‌های خطای پایه دیگر را اینجا اضافه کنید.
        ];

        // ابتدا پیام‌های خاص این ماژول را چک کن، سپس به پیام‌های خطای پایه در کلاس والد رجوع کن.
        // فیلتر کردن پیام‌های خطا (با وجود درخواست عدم استفاده از __())
        $message = $messages[$code] ?? 'خطای اعتبارسنجی ناشناخته.';

        // فیلتر برای پیام خطای خاص این ماژول و کد خطا
        $message = apply_filters("nader_module_error_message_{$this->get_name()}_{$code}", $message, $this);

        // فیلتر عمومی برای کد خطا
        $message = apply_filters("nader_module_error_message_{$code}", $message, $this);

        return $message;
    }


    // --- متدهای کمکی برای دسترسی به آرگومان‌ها و مقادیر ---

    /**
     * دریافت نام اصلی ماژول.
     *
     * @return string نام ماژول.
     */
    public function get_name(): string {
        return $this->args['name'];
    }

    /**
     * دریافت عنوان ماژول.
     *
     * @return string عنوان ماژول.
     */
    public function get_title(): string {
        return $this->args['title'];
    }

    /**
     * دریافت نوع ماژول بر اساس نام کلاس (مثال: 'text' از Nader_Text).
     *
     * @return string نوع ماژول.
     */
    public function get_type(): string {
        $class_name = get_class($this);
        // حذف پیشوند 'Nader_' و تبدیل به حروف کوچک و جایگزینی _ با -
        $type = str_replace('Nader_', '', $class_name);
        return strtolower(str_replace('_', '-', $type));
    }


    /**
     * دریافت مقدار پیش‌فرض ماژول.
     *
     * @return mixed مقدار پیش‌فرض.
     */
    public function get_default() {
        return $this->args['default'];
    }

    /**
     * بررسی اینکه آیا فیلد الزامی است.
     *
     * @return bool
     */
    public function is_required(): bool {
        return (bool)$this->args['required'];
    }

    /**
     * بررسی اینکه آیا فیلد چندزبانه است.
     *
     * @return bool
     */
    public function is_multilang(): bool {
        return (bool)$this->args['multilang'];
    }

    /**
     * دریافت آرگومان‌های ماژول.
     *
     * @return array آرایه آرگومان‌ها.
     */
    public function get_args(): array {
        return $this->args;
    }

    /**
     * دریافت یک آرگومان خاص ماژول.
     *
     * @param string $key کلید آرگومان.
     * @param mixed $default مقدار پیش‌فرض اگر کلید یافت نشد.
     * @return mixed
     */
    public function get_arg(string $key, $default = null) {
        return $this->args[$key] ?? $default;
    }


    /**
     * دریافت نام کامل فیلد (برای ویژگی 'name' در HTML و کلید ذخیره‌سازی)
     * برای فیلدهای چندزبانه، پسوند زبان اضافه می‌شود.
     *
     * @param string $lang کد زبان (فقط برای فیلدهای چندزبانه استفاده می‌شود).
     * @return string نام کامل فیلد.
     */
    public function get_field_name(string $lang = ''): string {
        $base_name = $this->get_name();
        if ($this->is_multilang() && $lang) {
            return "{$base_name}__{$lang}";
        }
        return $base_name;
    }

    /**
     * دریافت مقدار ذخیره شده فعلی برای این فیلد.
     * این متد از کلاس اصلی Nader_Settings برای خواندن تنظیمات استفاده می‌کند.
     *
     * @param string $lang کد زبان (برای فیلدهای چندزبانه).
     * @return mixed مقدار ذخیره شده یا مقدار پیش‌فرض (که توسط get_setting اعمال می‌شود).
     */
    public function get_field_value(string $lang = '') {
        $settings = Nader_Settings::instance();
        $name = $this->get_field_name($lang); // دریافت نام کامل فیلد (مثال: site_title__fa)
        // از متد get_setting در کلاس اصلی برای دریافت مقدار استفاده کن.
        // این متد مقدار پیش‌فرض ماژول را اعمال می‌کند اگر مقداری ذخیره نشده باشد.
        return $settings->get_setting($name, $this->get_default());
    }

    // می‌توانید متدهای کمکی دیگری اینجا اضافه کنید
    // مثل get_attribute($key, $default = null) و غیره.
}