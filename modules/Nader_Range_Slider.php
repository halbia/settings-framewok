<?php
/**
 * ماژول Range Slider برای چارچوب تنظیمات نادر.
 */

if (!defined('ABSPATH'))
    exit; // اگر مستقیماً فراخوانی شده، خارج شو.

class Nader_Range_Slider extends Nader_Module {

    /**
     * سازنده کلاس. آرگومان‌های پیش‌فرض ماژول اسلایدر را تنظیم می‌کند.
     */
    public function __construct(array $args = []) {
        $default_args = [
            'name'        => 'range_slider_field',
            'title'       => 'فیلد اسلایدر محدوده',
            'description' => '',
            'required'    => false,
            'default'     => 0, // مقدار پیش‌فرض عددی
            'multilang'   => false,
            'min'         => 0, // حداقل مقدار محدوده
            'max'         => 100, // حداکثر مقدار محدوده
            'step'        => 1, // گام‌های تغییر مقدار
            'unit'        => '', // واحد نمایش مقدار (مثال: 'px', '%')
            'show_value'  => true, // نمایش مقدار فعلی کنار اسلایدر
        ];

        parent::__construct(wp_parse_args($args, $default_args));

        // اطمینان از اینکه min, max, step اعداد معتبری هستند
        $this->args['min'] = (float)($this->args['min'] ?? 0);
        $this->args['max'] = (float)($this->args['max'] ?? 100);
        $this->args['step'] = (float)($this->args['step'] ?? 1);
        $this->args['default'] = (float)($this->args['default'] ?? $this->args['min']); // پیش‌فرض باید در محدوده باشد
        $this->args['default'] = max($this->args['min'], min($this->args['max'], $this->args['default'])); // clamp default
    }

    /**
     * پیاده‌سازی متد render_field برای نمایش اسلایدر و فیلد عددی.
     */
    protected function render_field(string $name, $value): void {
        $current_value = is_numeric($value) ? (float)$value : (float)$this->get_default();
        // اطمینان از اینکه مقدار فعلی در محدوده min/max قرار دارد
        $current_value = max($this->args['min'], min($this->args['max'], $current_value));

        $min = esc_attr($this->args['min']);
        $max = esc_attr($this->args['max']);
        $step = esc_attr($this->args['step']);
        $unit = esc_html($this->args['unit']); // واحد نمایش

        ?>
        <div class="nader-range-slider-field">
            <input type="range"
                   name="<?php echo esc_attr($name); ?>"
                   id="<?php echo esc_attr($name); ?>"
                   value="<?php echo esc_attr($current_value); ?>"
                   min="<?php echo $min; ?>"
                   max="<?php echo $max; ?>"
                   step="<?php echo $step; ?>"
                   class="nader-range-input"
                <?php echo $this->is_required() ? 'required' : ''; ?>
                <?php
                // رندر ویژگی‌های HTML اضافی
                if (!empty($this->args['attributes']) && is_array($this->args['attributes'])) {
                    foreach (array_map('esc_attr', $this->args['attributes']) as $attr => $val) {
                        printf('%s="%s" ', $attr, $val);
                    }
                }
                ?>
            />

            <?php if ($this->args['show_value']) : ?>
                <input type="number"
                       id="<?php echo esc_attr($name); ?>-number"
                       value="<?php echo esc_attr($current_value); ?>"
                       min="<?php echo $min; ?>"
                       max="<?php echo $max; ?>"
                       step="<?php echo $step; ?>"
                       class="nader-number-input"
                />
                <?php if (!empty($unit)) : ?>
                    <span class="nader-range-unit"><?php echo $unit; ?></span>
                <?php endif; ?>
            <?php endif; ?>

            <?php $this->render_errors($name); ?>
        </div>
        <?php
        // نکته: نیاز به کد جاوا اسکریپت در admin.js برای همگام‌سازی مقدار بین input range و input number.
    }

    /**
     * پیاده‌سازی متد custom_validation برای اعتبارسنجی فیلد اسلایدر.
     * اعتبارسنجی می‌کند که مقدار ارسالی یک عدد در محدوده min/max باشد (اگر غیر از خالی بودن).
     */
    protected function custom_validation($value, string $lang = ''): array {
        $errors = [];

        // اگر فیلد الزامی نیست و مقدار تهی است، اعتبارسنجی بیشتری لازم نیست.
        $is_empty = empty($value) && $value !== 0 && $value !== '0';
        if (!$this->is_required() && $is_empty) {
            return [];
        }

        // مقدار باید عددی باشد
        if (!is_numeric($value)) {
            $errors[] = 'مقدار باید عددی باشد.';
        } else {
            $numeric_value = (float) $value;
            // بررسی در محدوده min/max
            if ($numeric_value < (float)$this->args['min'] || $numeric_value > (float)$this->args['max']) {
                $errors[] = sprintf('مقدار باید بین %s و %s باشد.', $this->args['min'], $this->args['max']);
            }
            // بررسی گام (اختیاری و ممکن است پیچیده باشد، فعلاً چک نمی‌کنیم)
            /*
            if ($this->args['step'] > 0 && fmod($numeric_value - $this->args['min'], $this->args['step']) !== 0.0) {
                $errors[] = sprintf('مقدار باید مضربی از گام %s باشد.', $this->args['step']);
            }
            */
        }

        return $errors;
    }

    /**
     * پیاده‌سازی متد sanitize_value برای پاکسازی مقدار اسلایدر.
     */
    protected function sanitize_value($value) {
        // پاکسازی: اطمینان از اینکه مقدار عددی است و آن را به float تبدیل کن.
        $sanitized_value = is_numeric($value) ? (float)$value : 0.0;
        // اطمینان از اینکه مقدار در محدوده مجاز است
        $sanitized_value = max((float)$this->args['min'], min((float)$this->args['max'], $sanitized_value));
        return $sanitized_value;
    }
}