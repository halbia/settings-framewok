<?php
/**
 * ماژول انتخابگر تصویر از بین گزینه‌های از پیش تعیین شده با استفاده از URL تصویر (Nader_Image_Select).
 * آرگومان options به صورت value => image_link (URL) است.
 * کاربر با کلیک روی تصویر گزینه را انتخاب می‌کند.
 */

if (!defined('ABSPATH'))
    exit; // اگر مستقیماً فراخوانی شده، خارج شو.

class Nader_Image_Select extends Nader_Module {

    /**
     * سازنده کلاس. آرگومان‌های پیش‌فرض ماژول انتخابگر تصویر را تنظیم می‌کند.
     * آرگومان 'options' الزامی است و باید آرایه‌ای از value => image_link (URL) باشد.
     */
    public function __construct(array $args = []) {
        $default_args = [
            'name'        => 'image_select_field',
            'title'       => 'فیلد انتخابگر تصویر',
            'description' => '',
            'required'    => false,
            'default'     => '', // پیش‌فرض می‌تواند یک value تکی یا آرایه خالی باشد.
            'multilang'   => false, // معمولاً این ماژول چندزبانه نیست
            'multiple'    => false, // آیا اجازه انتخاب چندین گزینه را می‌دهد؟
            'options'     => [], // الزامی: آرایه‌ای از value => image_link (URL)
            // 'image_size' => این آرگومان معنی ندارد چون از URL مستقیم استفاده می‌کنیم.
        ];

        parent::__construct(wp_parse_args($args, $default_args));

        // بررسی اینکه آرگومان 'options' ارائه شده و یک آرایه غیرخالی است.
        if (empty($this->args['options']) || !is_array($this->args['options'])) {
            error_log('Nader Settings: ماژول Image Select "' . $this->get_name() . '" بدون آرگومان options معتبر نمونه‌سازی شده است.');
            $this->args['options'] = []; // برای جلوگیری از خطا
        } else {
            // اختیاری: اعتبارسنجی اولیه ساختار options (هر آیتم باید یک رشته غیرخالی باشد)
            foreach($this->args['options'] as $option_value => $image_link) {
                if (!is_string($image_link) || empty($image_link)) {
                    error_log('Nader Settings: ماژول Image Select "' . $this->get_name() . '" دارای لینک تصویر نامعتبر برای value "' . $option_value . '" است.');
                    unset($this->args['options'][$option_value]); // حذف گزینه نامعتبر
                }
            }
        }


        // اطمینان از اینکه مقدار پیش‌فرض با حالت multiple همخوانی دارد.
        if ((bool)$this->args['multiple'] && !is_array($this->args['default'])) {
            $this->args['default'] = [];
        } elseif (!(bool)$this->args['multiple'] && is_array($this->args['default'])) {
            $this->args['default'] = reset($this->args['default']); // اولین عنصر آرایه را بگیر
        }
    }

    /**
     * پیاده‌سازی متد render_field برای نمایش لیست تصاویر برای انتخاب.
     * از یک فیلد hidden برای ذخیره مقدار/مقادیر انتخاب شده استفاده می‌کند.
     */
    protected function render_field(string $name, $value): void {
        $options = $this->args['options']; // آرایه value => image_link (URL)
        $is_multiple = (bool)$this->args['multiple'];

        // تبدیل مقدار ذخیره شده به آرایه ای از value های انتخاب شده
        $selected_values = [];
        if ($is_multiple && is_array($value)) {
            $selected_values = array_map('strval', $value); // اطمینان از اینکه مقادیر رشته هستند
        } elseif (!$is_multiple) {
            // اگر تکی است و مقدار خالی نیست، آن را به عنوان یک آرایه تک عنصری در نظر بگیر.
            // '0' را به عنوان value معتبر در نظر بگیر.
            if (!empty($value) || $value === '0' || $value === 0) {
                $selected_values = [(string)$value];
            }
        }


        // مقدار فیلد hidden به صورت رشته (تکی یا جدا شده با کاما)
        $hidden_input_value = $is_multiple ? implode(',', $selected_values) : (string)reset($selected_values);
        // اطمینان از اینکه برای حالت تکی، اگر nothing selected بود، رشته خالی ذخیره شود.
        if (!$is_multiple && empty($selected_values)) {
            $hidden_input_value = '';
        }


        if (empty($options)) {
            echo '<p>تصویری برای انتخاب در این فیلد وجود ندارد.</p>';
            return;
        }

        ?>
        <div class="nader-image-select-field" data-multiple="<?php echo $is_multiple ? 'true' : 'false'; ?>">
            <input type="hidden"
                   name="<?php echo esc_attr($name); ?>"
                   id="<?php echo esc_attr($name); ?>"
                   value="<?php echo esc_attr($hidden_input_value); ?>"
                   class="nader-image-select-input"
                <?php echo $this->is_required() ? 'data-required="true"' : ''; ?>
            />

            <ul class="nader-image-select-list">
                <?php
                // حلقه روی گزینه‌های ارائه شده
                foreach ($options as $option_value => $image_link) :
                    // اطمینان از اینکه image_link یک URL معتبر است (فقط فرمت)
                    $image_url = esc_url($image_link);
                    // Alt text را از value می‌گیریم (ممکن است توصیفی نباشد) یا خالی می‌گذاریم.
                    $image_alt = esc_attr($option_value); // یا فقط esc_attr('')

                    // بررسی اینکه آیا این گزینه در مقادیر ذخیره شده فعلی است
                    $is_selected = in_array((string)$option_value, $selected_values, true); // مقایسه strict با تبدیل به رشته

                    // رندر آیتم لیست تصویر
                    ?>
                    <li class="image-option <?php echo $is_selected ? 'selected' : ''; ?>"
                        data-value="<?php echo esc_attr($option_value); ?>"
                    >
                        <?php if ($image_url) : ?>
                            <img src="<?php echo $image_url; ?>" alt="<?php echo $image_alt; ?>">
                        <?php else : ?>
                            <div class="no-image-placeholder">تصویر یافت نشد</div>
                        <?php endif; ?>
                    </li>
                <?php
                endforeach;
                ?>
            </ul>

            <?php $this->render_errors($name); ?>
        </div>
        <?php
        // نکته: نیاز به کد جاوا اسکریپت در admin.js برای مدیریت کلیک روی آیتم‌های لیست و به‌روزرسانی فیلد hidden.
    }

    /**
     * پیاده‌سازی متد custom_validation برای اعتبارسنجی فیلد انتخابگر تصویر.
     * اعتبارسنجی می‌کند که مقدار/مقادیر ارسالی، کلیدهای معتبری از آرایه options باشند.
     *
     * @param mixed $value مقدار ارسالی فیلد (value تکی یا رشته‌ای از valueهای جدا شده با کاما).
     * @param string $lang کد زبان.
     * @return array آرایه‌ای از پیام‌های خطا.
     */
    protected function custom_validation($value, string $lang = ''): array {
        $errors = [];
        $is_multiple = (bool)$this->args['multiple'];
        $available_values = array_keys($this->args['options']); // کلیدهای معتبر از آرایه options

        // تبدیل مقدار ارسالی به آرایه‌ای از valueها
        $submitted_values = [];
        if ($is_multiple) {
            // اگر multiple است، رشته جدا شده با کاما را به آرایه تبدیل کن.
            $submitted_values = array_map('strval', explode(',', (string) $value));
        } else {
            // اگر تکی است، مقدار را به عنوان یک آرایه تک عنصری در نظر بگیر.
            if (!empty($value) || $value === '0' || $value === 0) { // Handle '0' as a valid value
                $submitted_values = [(string)$value];
            }
        }
        // فیلتر کردن valueهای خالی که ممکن است در explode رشته‌های خالی ایجاد کنند.
        $submitted_values = array_filter($submitted_values, 'strlen');


        // اگر فیلد الزامی است و هیچ value معتبری انتخاب نشده
        if ($this->is_required() && empty($submitted_values)) {
            $errors[] = $this->get_error_message('required', $lang);
            return $errors; // اگر الزامی و خالی بود، اعتبارسنجی بیشتر لازم نیست.
        }

        // اگر الزامی نیست و خالی است، اعتبارسنجی بیشتر لازم نیست.
        if (!$this->is_required() && empty($submitted_values)) {
            return [];
        }

        // بررسی اینکه تمام valueهای ارسالی در کلیدهای آرایه options معتبر وجود دارند.
        foreach ($submitted_values as $submitted_value) {
            if (!in_array($submitted_value, $available_values, true)) {
                $errors[] = sprintf('گزینه "%s" انتخاب شده نامعتبر است.', esc_html($submitted_value));
                // می‌توانید در صورت یافتن اولین خطای نامعتبر، break کنید.
                // break;
            } else {
                // اختیاری: اعتبارسنجی اینکه URL معتبر است (فقط فرمت URL، نه اینکه آیا تصویر واقعی است)
                // $image_link = $this->args['options'][$submitted_value] ?? '';
                // if (!filter_var($image_link, FILTER_VALIDATE_URL)) {
                //      $errors[] = sprintf('لینک تصویر برای گزینه "%s" نامعتبر است.', esc_html($submitted_value));
                // }
            }
        }


        return $errors;
    }

    /**
     * پیاده‌سازی متد sanitize_value برای پاکسازی مقدار انتخاب شده.
     * value/valueها را بر اساس کلیدهای معتبر options فیلتر می‌کند و به فرمت صحیح برمی‌گرداند.
     *
     * @param mixed $value مقدار ارسالی فیلد (value تکی یا رشته‌ای از valueهای جدا شده با کاما).
     * @return string|array مقدار پاکسازی شده (value تکی یا آرایه‌ای از valueها).
     */
    protected function sanitize_value($value) {
        $is_multiple = (bool)$this->args['multiple'];
        $available_values = array_keys($this->args['options']); // کلیدهای معتبر از آرایه options

        // تبدیل مقدار ارسالی به آرایه‌ای از valueها
        $submitted_values = [];
        if ($is_multiple) {
            // اگر multiple است، رشته جدا شده با کاما را به آرایه تبدیل کن.
            $submitted_values = array_map('strval', explode(',', (string) $value));
        } else {
            // اگر تکی است، مقدار را به عنوان یک آرایه تک عنصری در نظر بگیر.
            if (!empty($value) || $value === '0' || $value === 0) { // Handle '0' as a valid value
                $submitted_values = [(string)$value];
            }
        }
        // فیلتر کردن valueهای خالی
        $submitted_values = array_filter($submitted_values, 'strlen');


        // فیلتر کردن valueهایی که در کلیدهای options معتبر وجود ندارند.
        $sanitized_values = array_filter($submitted_values, function($submitted_value) use ($available_values) {
            return in_array($submitted_value, $available_values, true);
        });

        if ($is_multiple) {
            // برای multiple، آرایه‌ای از valueهای معتبر برگردان.
            return array_values($sanitized_values); // بازنشانی کلیدهای آرایه
        } else {
            // برای تکی، اولین value معتبر (اگر وجود دارد) یا رشته خالی برگردان.
            return !empty($sanitized_values) ? reset($sanitized_values) : '';
        }
    }

    /**
     * override کردن متد get_error_message برای افزودن پیام خطای خاص ماژول Image Select.
     */
    protected function get_error_message(string $code, string $lang = ''): string {
        $messages = [
            'invalid_option' => 'گزینه انتخاب شده نامعتبر است.', // پیام خطای پیش‌فرض برای گزینه نامعتبر
            'required_checked' => 'حداقل یک گزینه باید انتخاب شود.', // پیام خطای پیش‌فرض برای الزامی بودن در حالت multiple
            // می‌توانید پیام‌های سفارشی بیشتری بر اساس کدهای خطا در custom_validation اضافه کنید.
        ];
        return $messages[$code] ?? parent::get_error_message($code, $lang);
    }
}