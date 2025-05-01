<?php
/**
 * ماژول آپلود تصویر برای چارچوب تنظیمات نادر.
 */

if (!defined('ABSPATH'))
    exit; // اگر مستقیماً فراخوانی شده، خارج شو.

// کلاس باید Nader_Image نام داشته باشد و از Nader_Module ارث ببرد.
class Nader_Image extends Nader_Module {

    /**
     * سازنده کلاس. آرگومان‌های پیش‌فرض ماژول آپلود تصویر را تنظیم می‌کند.
     *
     * @param array $args آرایه‌ای از آرگومان‌ها که آرگومان‌های پیش‌فرض را override می‌کنند.
     */
    public function __construct(array $args = []) {
        $default_args = [
            'name'        => 'image_field',
            'title'       => 'فیلد تصویر',
            'description' => '',
            'required'    => false,
            'default'     => '', // پیش‌فرض می‌تواند شناسه خالی یا 0 باشد
            'multilang'   => false,
            'button_text' => 'انتخاب تصویر', // متن دکمه انتخاب
            'modal_title' => 'انتخاب تصویر', // عنوان پنجره مدال کتابخانه رسانه
            'modal_button_text' => 'استفاده از این تصویر', // متن دکمه انتخاب در مدال
            // می‌توانید انواع فایل مجاز ('image', 'audio', 'video') را هم به آرگومان اضافه کنید.
            'mime_types'  => 'image', // انواع Mime مجاز در کتابخانه رسانه (comma separated)
        ];

        parent::__construct(wp_parse_args($args, $default_args));
    }

    /**
     * پیاده‌سازی متد render_field برای نمایش فیلد آپلود تصویر.
     * شامل: فیلد ورودی مخفی برای ذخیره شناسه تصویر، ناحیه نمایش تصویر، و دکمه انتخاب تصویر.
     *
     * @param string $name ویژگی 'name' کامل HTML برای فیلد (شامل __lang برای چندزبانه).
     * @param mixed $value مقدار فعلی فیلد (انتظار می‌رود شناسه تصویر باشد).
     */
    protected function render_field(string $name, $value): void {
        // اطمینان از اینکه مقدار یک عدد معتبر است (شناسه تصویر)
        $image_id = is_numeric($value) ? (int)$value : 0;
        $image_url = ''; // URL تصویر برای نمایش پیش‌نمایش

        // اگر شناسه تصویر معتبر بود، URL آن را دریافت کن
        if ($image_id > 0) {
            $image_attributes = wp_get_attachment_image_src($image_id, 'thumbnail'); // می‌توانید اندازه دیگری را مشخص کنید
            if ($image_attributes) {
                $image_url = $image_attributes[0];
            }
        }

        ?>
        <div class="nader-image-upload-field" data-uploader-title="<?php echo esc_attr($this->args['modal_title']); ?>" data-uploader-button-text="<?php echo esc_attr($this->args['modal_button_text']); ?>" data-mime-types="<?php echo esc_attr($this->args['mime_types']); ?>">
            <input type="hidden"
                   name="<?php echo esc_attr($name); ?>"
                   id="<?php echo esc_attr($name); ?>"
                   value="<?php echo esc_attr($image_id); ?>"
                   class="nader-image-id-input"
            />

            <div class="nader-image-preview">
                <?php if ($image_url) : ?>
                    <img src="<?php echo esc_url($image_url); ?>" alt="">
                <?php endif; ?>
            </div>

            <button type="button" class="button nader-select-image-button">
                <?php echo esc_html($this->args['button_text']); ?>
            </button>

            <?php if ($image_id > 0) : ?>
                <button type="button" class="button button-secondary nader-remove-image-button">
                    حذف تصویر
                </button>
            <?php endif; ?>

            <?php $this->render_errors($name); ?>
        </div>
        <?php
        // نکته: منطق جاوا اسکریپت برای باز کردن مدال کتابخانه رسانه و مدیریت دکمه‌ها باید در admin.js پیاده‌سازی شود.
    }

    /**
     * پیاده‌سازی متد custom_validation برای اعتبارسنجی فیلد تصویر.
     * اعتبارسنجی می‌کند که مقدار وارد شده یک عدد صحیح مثبت (شناسه) باشد اگر الزامی است.
     *
     * @param mixed $value مقدار ارسالی فیلد (انتظار می‌رود شناسه تصویر باشد).
     * @param string $lang کد زبان.
     * @return array آرایه‌ای از پیام‌های خطا.
     */
    protected function custom_validation($value, string $lang = ''): array {
        $errors = [];

        // اگر فیلد الزامی نیست و مقدار آن خالی یا 0 است، اعتبارسنجی بیشتری لازم نیست.
        if (!$this->is_required() && (empty($value) || (is_numeric($value) && (int)$value === 0))) {
            return [];
        }

        // مقدار باید یک عدد باشد.
        if (!is_numeric($value) || (int)$value <= 0) {
            $errors[] = 'شناسه تصویر نامعتبر است.'; // یا پیام خطای مناسب‌تر
        } else {
            // اختیاری: می‌توانید اینجا بررسی کنید که آیا این شناسه واقعا یک تصویر در کتابخانه رسانه است.
            // $attachment = get_post((int)$value);
            // if (!$attachment || $attachment->post_type !== 'attachment' || !wp_attachment_is_image((int)$value)) {
            //     $errors[] = 'فایل انتخاب شده یک تصویر معتبر نیست.';
            // }
        }


        return $errors;
    }

    /**
     * پیاده‌سازی متد sanitize_value برای پاکسازی شناسه تصویر.
     *
     * @param mixed $value مقدار ارسالی فیلد.
     * @return int شناسه تصویر به صورت عدد صحیح یا 0.
     */
    protected function sanitize_value($value) {
        // پاکسازی: مطمئن شوید که مقدار یک عدد صحیح است.
        return is_numeric($value) ? (int)$value : 0;
    }

    /**
     * override کردن متد get_error_message برای افزودن پیام‌های خطای خاص ماژول تصویر.
     */
    protected function get_error_message(string $code, string $lang = ''): string {
        $messages = [
            // 'invalid_id' => 'شناسه تصویر نامعتبر است.', // مثال پیام خطای سفارشی
        ];
        return $messages[$code] ?? parent::get_error_message($code, $lang);
    }

    // متد handle_submission از کلاس والد Nader_Module استفاده می‌کند
    // چون داده‌ها (شناسه تصویر) به صورت تک مقداری (یا تک مقداری در هر زبان) ارسال می‌شوند.
}