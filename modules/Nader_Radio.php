<?php
/**
 * ماژول رادیو باتن برای چارچوب تنظیمات نادر.
 */

if (!defined('ABSPATH'))
    exit; // اگر مستقیماً فراخوانی شده، خارج شو.

// کلاس باید Nader_Radio نام داشته باشد و از Nader_Module ارث ببرد.
class Nader_Radio extends Nader_Module {

    /**
     * سازنده کلاس. آرگومان‌های پیش‌فرض ماژول رادیو باتن را تنظیم می‌کند.
     *
     * @param array $args آرایه‌ای از آرگومان‌ها. آرگومان 'options' الزامی است.
     */
    public function __construct(array $args = []) {
        $default_args = [
            'name'        => 'radio_field',
            'title'       => 'فیلد رادیو',
            'description' => '',
            'required'    => false,
            'default'     => null, // مقدار پیش‌فرض (باید یکی از کلیدهای options باشد)
            'multilang'   => false,
            'options'     => [], // الزامی: آرایه‌ای از گزینه‌ها (کلید => برچسب)
            'inline'      => false, // نمایش گزینه‌ها در یک خط
        ];

        parent::__construct(wp_parse_args($args, $default_args));

        // بررسی اینکه آرگومان 'options' ارائه شده و یک آرایه غیرخالی است.
        if (empty($this->args['options']) || !is_array($this->args['options'])) {
            error_log('Nader Settings: ماژول رادیو "' . $this->get_name() . '" بدون آرگومان options معتبر نمونه‌سازی شده است.');
            // در محیط توسعه می‌توانید خطا ایجاد کنید.
            // trigger_error('Radio module "' . $this->get_name() . '" requires a non-empty "options" array.', E_USER_WARNING);
            $this->args['options'] = []; // اطمینان از اینکه options همیشه آرایه است.
        }

        // اگر مقدار پیش‌فرض تنظیم نشده بود، می‌توان اولین گزینه را به عنوان پیش‌فرض در نظر گرفت.
        if ($this->args['default'] === null && !empty($this->args['options'])) {
            $this->args['default'] = key($this->args['options']); // اولین کلید به عنوان پیش‌فرض
        }
    }

    /**
     * پیاده‌سازی متد render_field برای نمایش مجموعه‌ای از رادیو باتن‌ها.
     *
     * @param string $name ویژگی 'name' کامل HTML برای تمام رادیو باتن‌ها در این گروه.
     * @param mixed $value مقدار فعلی فیلد (مقدار گزینه انتخاب شده).
     */
    protected function render_field(string $name, $value): void {
        $options = $this->args['options'];
        $current_value = (string) $value; // تبدیل به رشته برای مقایسه دقیق

        if (empty($options)) {
            echo '<p>گزینه‌ای برای این فیلد رادیو تعریف نشده است.</p>';
            return;
        }

        // تعیین کلاس wrapper برای نمایش در یک خط یا ستونی
        $wrapper_class = $this->args['inline'] ? 'nader-radio-inline' : 'nader-radio-block';

        printf('<div class="%s">', esc_attr($wrapper_class));

        foreach ($options as $option_value => $option_label) {
            $option_id = esc_attr($name) . '_' . esc_attr($option_value); // ID منحصر به فرد برای هر input/label

            // بررسی اینکه آیا این گزینه انتخاب شده است
            $checked = checked($current_value, (string) $option_value, false); // مقایسه با تبدیل به رشته

            ?>
            <div class="nader-radio-option">
                <input type="radio"
                       name="<?php echo esc_attr($name); ?>"
                       id="<?php echo $option_id; ?>"
                       value="<?php echo esc_attr($option_value); ?>"
                    <?php echo $checked; ?>
                    <?php echo $this->is_required() ? 'required' : ''; ?>
                    <?php
                    // رندر ویژگی‌های HTML اضافی از آرگومان 'attributes'
                    if (!empty($this->args['attributes']) && is_array($this->args['attributes'])) {
                        foreach (array_map('esc_attr', $this->args['attributes']) as $attr => $val) {
                            printf('%s="%s" ', $attr, $val);
                        }
                    }
                    ?>
                />
                <label for="<?php echo $option_id; ?>">
                    <?php echo esc_html($option_label); ?>
                </label>
            </div>
            <?php
        }

        echo '</div>';

        // Placeholder برای نمایش خطا
        $this->render_errors($name);
    }

    /**
     * پیاده‌سازی متد custom_validation برای اعتبارسنجی فیلد رادیو.
     * اعتبارسنجی می‌کند که مقدار ارسالی یکی از کلیدهای معتبر در آرایه options باشد (اگر فیلد الزامی نیست و مقدار ارسالی خالی نباشد).
     * اعتبارسنجی الزامی در متد validate والد انجام می‌شود که چک می‌کند آیا کلاً مقداری ارسال شده است یا خیر.
     *
     * @param mixed $value مقدار ارسالی فیلد.
     * @param string $lang کد زبان.
     * @return array آرایه‌ای از پیام‌های خطا.
     */
    protected function custom_validation($value, string $lang = ''): array {
        $errors = [];

        // اگر فیلد الزامی نیست و مقداری ارسال نشده (یا مقدار تهی است)، نیازی به اعتبارسنجی در options نیست.
        // اعتبارسنجی الزامی بودن مقدار در متد validate والد انجام می‌شود.
        $is_empty_or_null = empty($value) && $value !== '0' && $value !== 0 && $value !== false; // در نظر گرفتن مقادیر "تهی" غیر از empty
        if (!$this->is_required() && $is_empty_or_null) {
            return [];
        }


        $options = $this->args['options'];
        $submitted_value_str = (string) $value;

        // بررسی اینکه آیا مقدار ارسالی در کلیدهای آرایه options وجود دارد یا خیر.
        // array_key_exists به کلید null یا false هم اجازه وجود می‌دهد.
        // array_keys($options) فهرستی از کلیدها را می‌دهد و in_array چک می‌کند که مقدار ارسالی در آن لیست هست یا نه.
        if (!in_array($submitted_value_str, array_keys($options), true)) { // استفاده از true برای مقایسه سخت‌گیرانه نوع
            $errors[] = $this->get_error_message('invalid_option', $lang); // پیام خطای گزینه نامعتبر
        }

        return $errors;
    }

    /**
     * پیاده‌سازی متد sanitize_value برای پاکسازی مقدار انتخاب شده رادیو باتن.
     *
     * @param mixed $value مقدار ارسالی فیلد.
     * @return string مقدار پاکسازی شده.
     */
    protected function sanitize_value($value) {
        // مقدار انتخاب شده را پاکسازی متنی ساده کن.
        // در اینجا فرض می‌کنیم مقادیر options رشته یا عدد ساده هستند.
        return sanitize_text_field((string) $value);
    }

    /**
     * override کردن متد get_error_message برای افزودن پیام خطای خاص ماژول رادیو.
     */
    protected function get_error_message(string $code, string $lang = ''): string {
        $messages = [
            'invalid_option' => 'گزینه انتخاب شده نامعتبر است.',
        ];
        return $messages[$code] ?? parent::get_error_message($code, $lang);
    }

    // متد handle_submission از کلاس والد Nader_Module استفاده می‌کند.
}