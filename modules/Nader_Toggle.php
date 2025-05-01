<?php
/**
 * ماژول سوئیچ روشن/خاموش با ظاهر کشویی (Nader_Toggle).
 */

if (!defined('ABSPATH'))
    exit; // اگر مستقیماً فراخوانی شده، خارج شو.

class Nader_Toggle extends Nader_Module {

    /**
     * سازنده کلاس. آرگومان‌های پیش‌فرض ماژول سوئیچ را تنظیم می‌کند.
     */
    public function __construct(array $args = []) {
        $default_args = [
            'name'        => 'toggle_field',
            'title'       => 'فیلد سوئیچ',
            'description' => '',
            'required'    => false,
            'default'     => 0, // پیش‌فرض خاموش (مقدار 0)
            'multilang'   => false, // معمولاً چندزبانه نیست
            'value'       => '1', // مقداری که هنگام روشن بودن Checkbox ذخیره می‌شود (می‌تواند هر چیزی باشد، '1' رایج است)
            'label_on'    => 'روشن', // متن برای حالت روشن (فعلاً فقط برای کامنت یا استفاده احتمالی آینده/دسترسی‌پذیری)
            'label_off'   => 'خاموش', // متن برای حالت خاموش (فعلاً فقط برای کامنت یا استفاده احتمالی آینده/دسترسی‌پذیری)
        ];

        parent::__construct(wp_parse_args($args, $default_args));

        // اطمینان از اینکه مقدار پیش‌فرض 0 یا برابر با مقدار روشن است.
        // این به اعتبارسنجی کمک می‌کند اما اجباری نیست اگر sanitize_value قوی باشد.
    }

    /**
     * پیاده‌سازی متد render_field برای نمایش سوئیچ کشویی.
     * Checkbox واقعی مخفی شده و از label و span برای ظاهر سوئیچ استفاده می‌شود.
     */
    protected function render_field(string $name, $value): void {
        $current_value = (string) $value; // مقدار ذخیره شده (معمولاً '0' یا '1')
        $option_value = (string) ($this->args['value'] ?? '1'); // مقداری که Checkbox هنگام تیک خوردن خواهد داشت

        // برای یک سوئیچ کشویی استاندارد، ما فقط به Checkbox ورودی و عناصر بصری نیاز داریم.
        // عناصر بصری توسط CSS بر روی label و span.slider استایل می‌شوند.
        // Checkbox باید قابل کلیک باشد، بنابراین آن را داخل label قرار می‌دهیم.
        ?>
        <label class="nader-toggle-switch">
            <input type="checkbox"
                   name="<?php echo esc_attr($name); ?>"
                   id="<?php echo esc_attr($name); ?>"
                   value="<?php echo esc_attr($option_value); ?>"
                <?php checked($current_value, $option_value); ?>
                <?php echo $this->is_required() ? 'required' : ''; ?>
                   class="nader-toggle-input" />
            <span class="slider round"></span>
        </label>

        <?php $this->render_errors($name); ?>

        <?php
        // توجه: متن label_on و label_off در این پیاده سازی بصری رندر نمی‌شوند
        // مگر اینکه بخواهید آن‌ها را در داخل یا کنار سوئیچ نمایش دهید
        // که نیاز به استایل CSS اضافی دارد.
        // در حال حاضر، تمرکز بر روی ظاهر سوئیچ کشویی استاندارد است.
    }


    /**
     * پیاده‌سازی متد custom_validation برای اعتبارسنجی فیلد سوئیچ.
     * اعتبارسنجی می‌کند که مقدار ارسالی، value معتبر (خالی، 0 یا value روشن) باشد.
     */
    protected function custom_validation($value, string $lang = ''): array {
        $errors = [];
        $option_value = (string) ($this->args['value'] ?? '1'); // مقدار روشن بودن
        $submitted_value = (string) $value;

        // اگر فیلد الزامی است و مقدار ارسالی خالی است (یعنی تیک نخورده ولی required بوده)
        // توجه: Checkbox های بدون تیک در ارسال فرم وجود ندارند، مگر اینکه value='0' را بفرستیم
        // یا hidden input کمکی داشته باشیم. پیاده سازی فعلی Nader_Module این را مدیریت می‌کند
        // که اگر فیلد در $_POST نباشد و required باشد، خطا بدهد.
        // در اینجا فقط چک می‌کنیم اگر مقداری ارسال شده، معتبر باشد (خالی، 0 یا option_value).
        if (!empty($submitted_value) && $submitted_value !== '0' && $submitted_value !== $option_value) {
            $errors[] = sprintf('مقدار "%s" انتخاب شده برای فیلد سوئیچ نامعتبر است.', esc_html($submitted_value));
        }


        // اعتبارسنجی الزامی بودن توسط validate در کلاس Nader_Module مدیریت می‌شود.
        // validate چک می‌کند که آیا مقدار $value (که از handle_submission و sanitize_value می‌آید) خالی است
        // و required=true است یا خیر.

        return $errors;
    }

    /**
     * پیاده‌سازی متد sanitize_value برای پاکسازی مقدار انتخاب شده.
     * مقدار را به 0 (خاموش) یا value روشن (معمولاً 1) پاکسازی می‌کند.
     */
    protected function sanitize_value($value) {
        $option_value = (string) ($this->args['value'] ?? '1'); // مقدار روشن بودن
        $submitted_value = (string) $value;

        // اگر مقدار ارسالی برابر با value روشن بود، همان value روشن را برگردان.
        if ($submitted_value === $option_value) {
            return $option_value;
        }

        // اگر مقدار ارسالی '0' بود، 0 را برگردان.
        if ($submitted_value === '0') {
            return '0'; // برگرداندن به صورت رشته '0' یا عدد 0 بستگی به نحوه استفاده شما دارد. رشته رایج‌تر است.
        }

        // در غیر این صورت (مقدار خالی، نامعتبر یا هر چیز دیگر)، 0 را برگردان (به معنای خاموش).
        return '0'; // مقدار پیش‌فرض خاموش
    }

    /**
     * override کردن متد get_error_message برای افزودن پیام خطای خاص ماژول Toggle.
     */
    protected function get_error_message(string $code, string $lang = ''): string {
        $messages = [
            // 'invalid_value' => 'مقدار سوئیچ نامعتبر است.', // مثال برای کد خطای سفارشی
        ];
        return $messages[$code] ?? parent::get_error_message($code, $lang);
    }

    // validate و sanitize_value والد در این ماژول استفاده می‌شوند.
}