<?php
/**
 * ماژول WP Editor (ویرایشگر پیشرفته وردپرس) برای چارچوب تنظیمات نادر.
 */

if (!defined('ABSPATH'))
    exit; // اگر مستقیماً فراخوانی شده، خارج شو.

class Nader_Wp_Editor extends Nader_Module {

    /**
     * سازنده کلاس. آرگومان‌های پیش‌فرض ماژول WP Editor را تنظیم می‌کند.
     */
    public function __construct(array $args = []) {
        $default_args = [
            'name'        => 'wp_editor_field',
            'title'       => 'فیلد ویرایشگر',
            'description' => '',
            'required'    => false,
            'default'     => '',
            'multilang'   => false,
            'settings'    => [], // آرگومان‌های سفارشی برای wp_editor()
            'editor_height' => 200, // ارتفاع ویرایشگر
            'teeny'       => false, // نمایش دکمه‌های کمتر (ویرایشگر ساده)
            'media_buttons' => true, // نمایش دکمه افزودن رسانه
        ];

        parent::__construct(wp_parse_args($args, $default_args));
    }

    /**
     * پیاده‌سازی متد render_field برای نمایش WP Editor.
     * از تابع wp_editor() وردپرس استفاده می‌کند.
     *
     * @param string $name ویژگی 'name' کامل HTML برای فیلد.
     * @param mixed $value مقدار فعلی فیلد (محتوای ویرایشگر).
     */
    protected function render_field(string $name, $value): void {
        // ID ویرایشگر در HTML باید با نام یکی باشد تا wp_editor به درستی عمل کند.
        $editor_id = esc_attr($name);
        $content = (string) $value;

        // آرگومان‌های پیش‌فرض برای wp_editor
        $editor_settings = [
            'textarea_name' => $editor_id, // نام ویژگی 'name' برای textarea پنهان
            'textarea_rows' => $this->args['editor_height'] ? null : 10, // تنظیم rows اگر ارتفاع مشخص نشده
            'editor_height' => $this->args['editor_height'] ? (int)$this->args['editor_height'] : '', // ارتفاع ویرایشگر
            'teeny'         => (bool)$this->args['teeny'], // ویرایشگر ساده
            'media_buttons' => (bool)$this->args['media_buttons'], // دکمه رسانه
            'tinymce'       => true, // فعال کردن TinyMCE
            'quicktags'     => true, // فعال کردن Quicktags
            'editor_class'  => 'nader-wp-editor-input', // کلاس CSS سفارشی برای textarea
            // اضافه کردن آرگومان‌های سفارشی از پیکربندی ماژول
        ];

        // ادغام آرگومان‌های سفارشی با آرگومان‌های پیش‌فرض wp_editor
        if (!empty($this->args['settings']) && is_array($this->args['settings'])) {
            $editor_settings = array_merge($editor_settings, $this->args['settings']);
        }


        // فراخوانی تابع wp_editor وردپرس برای رندر ویرایشگر
        // این تابع HTML و JS لازم را اضافه می‌کند.
        wp_editor($content, $editor_id, $editor_settings);

        // Placeholder برای نمایش خطا
        $this->render_errors($name);

        // نکته: برای دریافت محتوای ویرایشگر با AJAX، نیاز به کد جاوا اسکریپت است
        // که قبل از $.serialize() محتوای هر ویرایشگر را به‌روزرسانی کند.
        // wp.editor.getContent(editor_id) برای این کار استفاده می‌شود.
    }

    /**
     * پیاده‌سازی متد custom_validation برای اعتبارسنجی WP Editor.
     * (مثلاً اعتبارسنجی خالی نبودن محتوا اگر الزامی است).
     */
    protected function custom_validation($value, string $lang = ''): array {
        $errors = [];
        // اعتبارسنجی الزامی در متد validate والد انجام می‌شود (چک می‌کند که محتوا خالی نباشد).
        // اگر نیاز به اعتبارسنجی‌های پیچیده‌تر روی محتوای HTML دارید، اینجا اضافه کنید.
        return $errors;
    }


    /**
     * پیاده‌سازی متد sanitize_value برای پاکسازی محتوای WP Editor.
     */
    protected function sanitize_value($value) {
        // استفاده از wp_kses_post برای پاکسازی محتوای HTML (مناسب برای محتوای پست/ویرایشگر)
        // یا wp_kses_data برای HTML ساده‌تر.
        return wp_kses_post((string) $value);
    }
}