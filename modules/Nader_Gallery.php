<?php
/**
 * ماژول گالری تصویر برای چارچوب تنظیمات نادر.
 */

if (!defined('ABSPATH'))
    exit; // اگر مستقیماً فراخوانی شده، خارج شو.

// کلاس باید Nader_Gallery نام داشته باشد و از Nader_Module ارث ببرد.
class Nader_Gallery extends Nader_Module {

    /**
     * سازنده کلاس. آرگومان‌های پیش‌فرض ماژول گالری را تنظیم می‌کند.
     *
     * @param array $args آرایه‌ای از آرگومان‌ها که آرگومان‌های پیش‌فرض را override می‌کنند.
     */
    public function __construct(array $args = []) {
        $default_args = [
            'name'        => 'gallery_field',
            'title'       => 'فیلد گالری تصویر',
            'description' => '',
            'required'    => false,
            'default'     => '', // پیش‌فرض می‌تواند رشته خالی یا آرایه خالی باشد
            'multilang'   => false,
            'button_text' => 'انتخاب یا افزودن تصاویر', // متن دکمه انتخاب
            'modal_title' => 'انتخاب تصاویر گالری', // عنوان پنجره مدال کتابخانه رسانه
            'modal_button_text' => 'افزودن به گالری', // متن دکمه انتخاب در مدال
            'mime_types'  => 'image', // انواع Mime مجاز
            'preview_size' => 'thumbnail', // <-- جدید: اندازه تصویر برای پیش‌نمایش
        ];

        parent::__construct(wp_parse_args($args, $default_args));
    }

    /**
     * پیاده‌سازی متد render_field برای نمایش فیلد گالری تصویر.
     * شامل: فیلد ورودی مخفی برای ذخیره شناسه‌های تصویر (جدا شده با کاما)، ناحیه نمایش تصاویر، دکمه انتخاب/افزودن و دکمه حذف.
     *
     * @param string $name ویژگی 'name' کامل HTML برای فیلد (شامل __lang برای چندزبانه).
     * @param mixed $value مقدار فعلی فیلد (انتظار می‌رود رشته‌ای از شناسه‌های جدا شده با کاما باشد).
     */
    protected function render_field(string $name, $value): void {
        // تبدیل رشته شناسه‌های جدا شده با کاما به آرایه‌ای از شناسه‌ها
        $image_ids = array_filter(array_map('intval', explode(',', (string) $value)));
        $image_urls = [];
        $preview_size = $this->args['preview_size']; // اندازه تصویر برای پیش‌نمایش


        // دریافت URL تصاویر برای نمایش پیش‌نمایش
        if (!empty($image_ids)) {
            foreach ($image_ids as $image_id) {
                $image_attributes = wp_get_attachment_image_src($image_id, $preview_size);

                if ($image_attributes) {
                    $image_urls[$image_id] = $image_attributes[0]; // ذخیره URL با کلید شناسه
                }
            }
        }

        // شناسه‌های فعلی گالری به صورت رشته برای استفاده در دکمه باز کردن مدال Media Library و فیلد hidden
        $current_ids_string = implode(',', array_keys($image_urls)); // استفاده از کلیدهای image_urls برای اطمینان از اینکه فقط URLهای معتبر را شامل می‌شود


        ?>
        <div class="nader-gallery-field" data-uploader-title="<?php echo esc_attr($this->args['modal_title']); ?>" data-uploader-button-text="<?php echo esc_attr($this->args['modal_button_text']); ?>" data-mime-types="<?php echo esc_attr($this->args['mime_types']); ?>" data-preview-size="<?php echo esc_attr($preview_size); ?>">

            <input type="hidden"
                   name="<?php echo esc_attr($name); ?>"
                   id="<?php echo esc_attr($name); ?>"
                   value="<?php echo esc_attr($current_ids_string); ?>"
                   class="nader-gallery-ids-input"
            />

            <ul class="nader-gallery-preview">
                <?php
                // --- اصلاحیه: حلقه رندر تصاویر با ترکیب بهتر PHP و HTML ---
                // روی شناسه‌هایی که URL معتبر برایشان پیدا کردیم، حلقه می‌زنیم.
                foreach ($image_urls as $id => $url) :
                    ?>
                    <li class="image" data-id="<?php echo esc_attr($id); ?>">
                        <img src="<?php echo esc_url($url); ?>" alt="">
                        <button type="button" class="remove-image-button"><span class="dashicons dashicons-no-alt"></span></button>
                    </li>
                <?php
                endforeach;
                // --- پایان اصلاحیه ---
                ?>
            </ul>

            <button type="button" class="button nader-select-gallery-button">
                <?php echo esc_html($this->args['button_text']); ?>
            </button>

            <?php
            // نمایش دکمه حذف همه فقط زمانی که گالری خالی نیست
            if (!empty($image_urls)) :
                ?>
                <button type="button" class="button button-secondary nader-clear-gallery-button">
                    حذف همه تصاویر
                </button>
            <?php
            endif;
            ?>

            <?php $this->render_errors($name); ?>
        </div>
        <?php
        // نکته: منطق جاوا اسکریپت برای باز کردن مدال کتابخانه رسانه (با قابلیت انتخاب چندگانه)،
        // مدیریت افزودن/حذف تصاویر به ناحیه پیش‌نمایش، مرتب‌سازی تصاویر، و به‌روزرسانی فیلد hidden ids باید در admin.js پیاده‌سازی شود.
        // همچنین، قابلیت مرتب‌سازی نیاز به یک کتابخانه JS (مثل SortableJS یا jQuery UI Sortable) و منطق به‌روزرسانی ترتیب در فیلد hidden دارد.
    }

    /**
     * پیاده‌سازی متد custom_validation برای اعتبارسنجی فیلد گالری.
     * اعتبارسنجی می‌کند که اگر الزامی است، حداقل یک شناسه معتبر وجود داشته باشد.
     *
     * @param mixed $value مقدار ارسالی فیلد (انتظار می‌رود رشته‌ای از شناسه‌های جدا شده با کاما باشد).
     * @param string $lang کد زبان.
     * @return array آرایه‌ای از پیام‌های خطا.
     */
    protected function custom_validation($value, string $lang = ''): array {
        $errors = [];

        // تبدیل مقدار ارسالی (رشته جدا شده با کاما) به آرایه‌ای از شناسه‌های عددی معتبر
        $image_ids = array_filter(array_map('intval', explode(',', (string) $value)));

        // اگر فیلد الزامی است و هیچ شناسه معتبری وجود ندارد
        if ($this->is_required() && empty($image_ids)) {
            $errors[] = $this->get_error_message('required', $lang); // استفاده از پیام خطای 'required' والد
        }

        // اختیاری: می‌توانید بررسی‌های بیشتری روی شناسه‌های تکی انجام دهید.
        /*
        foreach($image_ids as $id) {
             if ($id <= 0 || !wp_attachment_is_image($id)) { // چک می‌کنیم شناسه معتبر و برای تصویر باشد
                  $errors[] = sprintf('شناسه تصویر نامعتبر یا حذف شده یافت شد: %d', $id);
                  // می‌توانیم در اینجا break کنیم یا تمام خطاها را جمع‌آوری کنیم.
             }
        }
        */


        return $errors;
    }

    /**
     * پیاده‌سازی متد sanitize_value برای پاکسازی شناسه‌های گالری.
     * ورودی می‌تواند رشته‌ای از شناسه‌های جدا شده با کاما یا آرایه‌ای از شناسه‌ها باشد (بسته به نحوه ارسال در JS).
     * ما آن را به رشته‌ای از شناسه‌های عددی صحیح جدا شده با کاما تبدیل و برمی‌گردانیم.
     *
     * @param mixed $value مقدار ارسالی فیلد (رشته جدا شده با کاما یا آرایه).
     * @return string رشته‌ای از شناسه‌های تصویر جدا شده با کاما.
     */
    protected function sanitize_value($value) {
        // اطمینان از اینکه با آرایه یا رشته کار می‌کنیم
        if (is_string($value)) {
            // اگر رشته است، آن را بر اساس کاما تقسیم کرده و به اعداد صحیح تبدیل کن.
            $image_ids = array_filter(array_map('intval', explode(',', $value)));
        } elseif (is_array($value)) {
            // اگر آرایه است، فقط مطمئن شو که عناصر آن عدد صحیح هستند و فیلتر کن.
            $image_ids = array_filter(array_map('intval', $value));
        } else {
            // اگر نه رشته و نه آرایه بود، رشته خالی برگردان.
            return '';
        }

        // فیلتر کردن شناسه‌های نامعتبر (اعداد صحیح مثبت)
        $valid_image_ids = array_filter($image_ids, function($id) {
            return $id > 0;
        });

        // برگرداندن شناسه‌های معتبر به صورت رشته جدا شده با کاما
        return implode(',', $valid_image_ids);
    }

    /**
     * override کردن متد get_error_message برای افزودن پیام‌های خطای خاص ماژول گالری.
     */
    protected function get_error_message(string $code, string $lang = ''): string {
        $messages = [
            // 'invalid_id' => 'شناسه تصویر نامعتبر است.', // مثال پیام خطای سفارشی
        ];
        return $messages[$code] ?? parent::get_error_message($code, $lang);
    }

    // متد handle_submission از کلاس والد Nader_Module استفاده می‌کند
    // چون داده‌ها به صورت یک مقدار تکی (رشته جدا شده با کاما) برای هر فیلد (یا هر فیلد زبان) ارسال و پردازش می‌شوند.
}