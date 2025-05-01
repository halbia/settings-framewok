<?php
/**
 * ماژول تکرار کننده (Repeater Field) برای چارچوب تنظیمات نادر.
 * اجازه می‌دهد مجموعه‌ای از زیرفیلدها تکرار شده و جابجا شوند.
 * اضافه شدن قابلیت بستن/باز کردن آیتم‌ها.
 */

if (!defined('ABSPATH'))
    exit; // اگر مستقیماً فراخوانی شده، خارج شو.

class Nader_Repeater extends Nader_Module {

    /**
     * سازنده کلاس. آرگومان‌های پیش‌فرض ماژول تکرار کننده را تنظیم می‌کند.
     */
    public function __construct(array $args = []) {
        $default_args = [
            'name'        => 'repeater_field',
            'title'       => 'فیلد تکرار کننده',
            'description' => '',
            'required'    => false, // آیا حداقل 1 آیتم الزامی است؟ (نیاز به custom validation)
            'multilang'   => false, // آیا لیست آیتم‌ها برای هر زبان متفاوت است؟
            'sub_fields'  => [], // الزامی: آرایه‌ای از پیکربندی زیرفیلدها
            'button_text' => 'افزودن آیتم جدید', // متن دکمه اضافه کردن آیتم
            'min_items'   => 0, // حداقل تعداد آیتم‌های مجاز
            'max_items'   => 0, // حداکثر تعداد آیتم‌های مجاز (0 به معنای نامحدود)
            'closed'      => false, // آیا آیتم‌ها به صورت پیش فرض بسته باشند؟
        ];

        parent::__construct(wp_parse_args($args, $default_args));

        // بررسی اینکه آرگومان 'sub_fields' ارائه شده و یک آرایه غیرخالی است.
        if (empty($this->args['sub_fields']) || !is_array($this->args['sub_fields'])) {
            error_log('Nader Settings: ماژول Repeater "' . $this->get_name() . '" بدون آرگومان sub_fields معتبر نمونه‌سازی شده است.');
            $this->args['sub_fields'] = []; // برای جلوگیری از خطا
        } else {
            foreach($this->args['sub_fields'] as $sub_field_config) {
                if (!is_array($sub_field_config) || empty($sub_field_config['name']) || empty($sub_field_config['type'])) {
                    error_log('Nader Settings: ماژول Repeater "' . $this->get_name() . '" دارای پیکربندی sub_fields نامعتبر است.');
                }
                if (!empty($sub_field_config['multilang'])) {
                    error_log('Nader Settings: زیرفیلد "' . ($sub_field_config['name'] ?? 'نامشخص') . '" در ماژول Repeater "' . $this->get_name() . '" نباید multilang باشد.');
                }
            }
        }
    }

    /**
     * رندر کردن HTML برای نمایش ساختار تکرار کننده.
     */
    protected function render_field(string $name, $value): void {
        $items = is_array($value) ? $value : [];
        $sub_fields_config = $this->args['sub_fields'];
        $repeater_id = esc_attr($name);
        $is_multilang_repeater = $this->is_multilang();
        $language_code = '';
        if ($is_multilang_repeater && strpos($name, '__') !== false) {
            $name_parts = explode('__', $name);
            $language_code = end($name_parts);
        }
        $is_closed_by_default = (bool) $this->args['closed'];

        ?>
        <div class="nader-repeater-field"
             id="nader-repeater-<?php echo $repeater_id; ?>"
             data-name="<?php echo esc_attr($name); ?>"
             data-multilang="<?php echo $is_multilang_repeater ? 'true' : 'false'; ?>"
             data-language="<?php echo esc_attr($language_code); ?>"
             data-min-items="<?php echo esc_attr($this->args['min_items']); ?>"
             data-max-items="<?php echo esc_attr($this->args['max_items']); ?>"
             data-closed-by-default="<?php echo $is_closed_by_default ? 'true' : 'false'; ?>"
        >

            <ul class="nader-repeater-items-list">
                <?php
                if (!empty($items)) {
                    foreach ($items as $item_index => $item_data) :
                        $this->render_repeater_item($repeater_id, $item_index, $item_data, $sub_fields_config, $language_code, false, $is_closed_by_default); // false for template, pass $is_closed_by_default
                    endforeach;
                }
                ?>
            </ul>

            <button type="button" class="button nader-repeater-add-item">
                <span class="dashicons dashicons-plus"></span>
                <?php echo esc_html($this->args['button_text']); ?>
            </button>

            <?php $this->render_errors($name); ?>

            <div class="nader-repeater-item-template" style="display: none !important;"> <?php
                // رندر کردن یک آیتم تمپلیت با ایندکس placeholder (__INDEX__)
                $this->render_repeater_item($repeater_id, '__INDEX__', [], $sub_fields_config, $language_code, true, $is_closed_by_default); // true for template, pass $is_closed_by_default
                ?>
            </div>

        </div>
        <?php
    }

    /**
     * رندر کردن HTML برای یک آیتم تکرار کننده تکی.
     *
     * @param string $repeater_id ID اصلی تکرار کننده.
     * @param int|string $item_index ایندکس آیتم (عدد یا __INDEX__ برای تمپلیت).
     * @param array $item_data داده‌های فعلی برای این آیتم.
     * @param array $sub_fields_config پیکربندی زیرفیلدها.
     * @param string $language_code کد زبان (برای repeater چندزبانه).
     * @param bool $is_template آیا این آیتم یک تمپلیت است؟
     * @param bool $is_closed_by_default آیا آیتم به صورت پیش فرض بسته باشد؟
     */
    protected function render_repeater_item(string $repeater_id, $item_index, array $item_data, array $sub_fields_config, string $language_code = '', bool $is_template = false, bool $is_closed_by_default = false): void {
        $sub_field_base_name = sprintf('%s[%s]', $repeater_id, $item_index);

        $item_classes = ['nader-repeater-item'];
        if ($is_template) {
            $item_classes[] = 'template';
        }
        // اضافه کردن کلاس بسته به صورت پیش فرض
        if ($is_closed_by_default && !$is_template) { // آیتم های موجود اگر بسته باشند
            $item_classes[] = 'closed';
        } elseif ($is_closed_by_default && $is_template) { // تمپلیت هم بسته باشد
            $item_classes[] = 'closed';
        }


        ?>
        <li class="<?php echo esc_attr(implode(' ', $item_classes)); ?>" data-item-index="<?php echo esc_attr($item_index); ?>">
            <div class="item-header">
                <span class="item-title"><?php printf('آیتم #%s', is_numeric($item_index) ? ($item_index + 1) : $item_index); ?></span>
                <span class="item-actions">
                     <span class="item-handle dashicons dashicons-move" title="جابجایی"></span>
                    <span class="item-toggle dashicons <?php echo $is_closed_by_default ? 'dashicons-arrow-down' : 'dashicons-arrow-up'; ?>" title="<?php echo $is_closed_by_default ? 'باز کردن' : 'بستن'; ?>"></span> <button type="button" class="item-remove dashicons dashicons-trash" title="حذف"></button>
                 </span>
            </div>
            <div class="item-content" <?php echo $is_closed_by_default ? 'style="display: none;"' : ''; ?>> <?php
                foreach ($sub_fields_config as $sub_field_config) {
                    $sub_field_name = $sub_field_config['name'];
                    $sub_field_type = $sub_field_config['type'];
                    $sub_field_args = $sub_field_config;

                    // ساخت نام کامل HTML برای زیرفیلد: repeater_name[item_index][sub_field_name]
                    $full_sub_field_name = sprintf('%s[%s][%s]', $this->get_name(), $item_index, $sub_field_name);
                    // نام کامل برای گزارش خطا به displayValidationErrors در JS
                    // اگر repeater چندزبانه است، شامل نام زبان در انتها است.
                    $full_sub_field_name_for_errors = sprintf('%s[%s][%s]', $this->get_field_name($language_code), $item_index, $sub_field_name);


                    $current_sub_field_value = $item_data[$sub_field_name] ?? ($sub_field_args['default'] ?? null); // استفاده از null برای تشخیص دقیق‌تر


                    unset($sub_field_args['name']);
                    unset($sub_field_args['type']);
                    unset($sub_field_args['multilang']); // مطمئن شو که multilang برای زیرفیلد استفاده نمی‌شود.


                    $sub_module_class_name = 'Nader_' . str_replace('-', '_', ucwords($sub_field_type, '-_'));
                    if (!class_exists($sub_module_class_name)) {
                        error_log('Nader Settings: کلاس ماژول زیرفیلد "' . $sub_module_class_name . '" برای Repeater "' . $this->get_name() . '" یافت نشد.');
                        echo '<p>خطا: ماژول زیرفیلد یافت نشد.</p>';
                        continue;
                    }

                    $sub_module_instance = new $sub_module_class_name($sub_field_args);

                    ?>
                    <div class="nader-repeater-sub-field-wrapper nader-field-wrapper nader-sub-field-<?php echo esc_attr($sub_field_type); ?>">
                        <label class="nader-field-label" for="<?php echo esc_attr($full_sub_field_name); ?>">
                            <?php echo esc_html($sub_field_config['title'] ?? $sub_field_name); ?>
                            <?php if (!empty($sub_field_config['required'])) : ?>
                                <span class="required">*</span>
                            <?php endif; ?>
                        </label>
                        <?php
                        // اینجا render_field ماژول زیرفیلد را فراخوانی می‌کنیم.
                        // نام و مقدار کامل را به آن پاس می‌دهیم.
                        // نام کامل برای رندر باید همان نامی باشد که برای ارسال فرم انتظار داریم.
                        $sub_module_instance->render_field($full_sub_field_name, $current_sub_field_value);
                        ?>
                        <?php if (!empty($sub_field_config['description'])) : ?>
                            <p class="description"><?php echo esc_html($sub_field_config['description']); ?></p>
                        <?php endif; ?>
                        <ul class="nader-errors" id="<?php echo esc_attr($full_sub_field_name_for_errors); ?>-errors"></ul>
                    </div>
                    <?php
                }
                ?>
            </div>
        </li>
        <?php
    }


    /**
     * پیاده‌سازی متد handle_submission برای پردازش داده‌های ارسالی Repeater.
     */
    public function handle_submission(array $submitted_data): array {
        $processed_data = [];
        $module_errors = [];
        $sub_fields_config = $this->args['sub_fields'];
        $is_multilang_repeater = $this->is_multilang();
        $active_languages = $this->get_active_languages();

        if ($is_multilang_repeater) {
            foreach ($active_languages as $lang) {
                $full_repeater_name = $this->get_field_name($lang);
                $submitted_items_for_lang = $submitted_data[$full_repeater_name] ?? [];

                $processed_items_for_lang = $this->process_submitted_items($submitted_items_for_lang, $sub_fields_config, $full_repeater_name);

                $processed_data[$full_repeater_name] = $processed_items_for_lang['processed_items'];

                if (!empty($processed_items_for_lang['item_errors'])) {
                    $module_errors = array_merge($module_errors, $processed_items_for_lang['item_errors']);
                }
            }
        } else {
            $full_repeater_name = $this->get_field_name();
            $submitted_items = $submitted_data[$full_repeater_name] ?? [];

            $processed_items_result = $this->process_submitted_items($submitted_items, $sub_fields_config, $full_repeater_name);

            $processed_data[$full_repeater_name] = $processed_items_result['processed_items'];
            $module_errors = $processed_items_result['item_errors'];
        }

        // انجام اعتبارسنجی سطح Repeater (min_items, max_items) پس از پردازش زیرفیلدها.
        if ($is_multilang_repeater) {
            foreach ($active_languages as $lang) {
                $full_repeater_name = $this->get_field_name($lang);
                $current_item_count = count($processed_data[$full_repeater_name] ?? []);
                $repeater_level_errors_lang = $this->validate_repeater_level($current_item_count, $lang);
                if (!empty($repeater_level_errors_lang)) {
                    $module_errors[$full_repeater_name] = array_merge($module_errors[$full_repeater_name] ?? [], $repeater_level_errors_lang);
                }
            }
        } else {
            $full_repeater_name = $this->get_field_name();
            $current_item_count = count($processed_data[$full_repeater_name] ?? []);
            $repeater_level_errors = $this->validate_repeater_level($current_item_count);
            if (!empty($repeater_level_errors)) {
                $module_errors[$full_repeater_name] = array_merge($module_errors[$full_repeater_name] ?? [], $repeater_level_errors);
            }
        }

        $sanitized_data = $this->sanitize_value($processed_data);

        return [
            'processed_data' => $sanitized_data,
            'errors'         => $module_errors,
        ];
    }

    /**
     * پردازش زیرفیلدهای هر آیتم ارسالی.
     */
    protected function process_submitted_items(array $submitted_items, array $sub_fields_config, string $full_repeater_name): array {
        $processed_items = [];
        $item_errors = [];

        if (!is_array($submitted_items)) {
            return ['processed_items' => [], 'item_errors' => []];
        }

        foreach ($submitted_items as $item_index => $item_data) {
            $processed_item_data = [];
            $has_item_errors = false;

            if (!is_array($item_data)) { continue; }

            foreach ($sub_fields_config as $sub_field_config) {
                $sub_field_name = $sub_field_config['name'];
                $sub_field_type = $sub_field_config['type'];
                $sub_field_args = $sub_field_config;

                // نام کامل فیلد برای این زیرفیلد در این آیتم خاص: repeater_name[item_index][sub_field_name]
                $full_sub_field_name = sprintf('%s[%s][%s]', $this->get_name(), $item_index, $sub_field_name);
                // نام کامل برای گزارش خطا به displayValidationErrors در JS
                // اگر repeater چندزبانه است، شامل نام زبان در انتها است.
                $full_sub_field_name_for_errors = sprintf('%s[%s][%s]', $full_repeater_name, $item_index, $sub_field_name);


                $submitted_sub_field_value = $item_data[$sub_field_name] ?? null;


                unset($sub_field_args['name']);
                unset($sub_field_args['type']);
                unset($sub_field_args['multilang']);


                $sub_module_class_name = 'Nader_' . str_replace('-', '_', ucwords($sub_field_type, '-_'));
                if (!class_exists($sub_module_class_name)) { continue; }

                $sub_module_instance = new $sub_module_class_name($sub_field_args);

                $sub_module_result = $sub_module_instance->handle_submission([$full_sub_field_name_for_errors => $submitted_sub_field_value]);

                $processed_item_data[$sub_field_name] = $sub_module_result['processed_data'][$full_sub_field_name_for_errors] ?? null;


                if (!empty($sub_module_result['errors'])) {
                    $item_errors = array_merge($item_errors, $sub_module_result['errors']);
                    $has_item_errors = true;
                }
            }

            $is_entirely_empty_item = true;
            foreach($processed_item_data as $sf_value) {
                if (!empty($sf_value) || $sf_value === '0' || $sf_value === 0 || (is_array($sf_value) && !empty($sf_value))) {
                    $is_entirely_empty_item = false;
                    break;
                }
            }

            if (!$is_entirely_empty_item) {
                $processed_items[] = $processed_item_data;
            }

        }

        return [
            'processed_items' => $processed_items,
            'item_errors'     => $item_errors,
        ];
    }

    /**
     * پیاده‌سازی متد custom_validation برای اعتبارسنجی سطح Repeater.
     */
    protected function custom_validation($value, string $lang = ''): array {
        $errors = [];
        $item_count = is_array($value) ? count($value) : 0;

        $min_items = (int)$this->args['min_items'];
        $max_items = (int)$this->args['max_items'];

        if ($min_items > 0 && $item_count < $min_items) {
            $errors[] = sprintf('حداقل تعداد آیتم‌های مورد نیاز %d است.', $min_items);
        }

        if ($max_items > 0 && $item_count > $max_items) {
            $errors[] = sprintf('حداکثر تعداد آیتم‌های مجاز %d است.', $max_items);
        }

        return $errors;
    }

    /**
     * پیاده‌سازی متد sanitize_value برای پاکسازی نهایی داده‌های Repeater.
     */
    protected function sanitize_value($value) {
        $sanitized_value = is_array($value) ? $value : [];
        return $sanitized_value;
    }

    /**
     * override کردن متد get_error_message برای افزودن پیام‌های خطای خاص ماژول Repeater.
     */
    protected function get_error_message(string $code, string $lang = ''): string {
        $messages = [
            // 'min_items' => 'حداقل تعداد آیتم ها ...',
        ];
        return $messages[$code] ?? parent::get_error_message($code, $lang);
    }
}