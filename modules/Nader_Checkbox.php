<?php
/**
 * ماژول چک‌باکس (تک حالته و چند حالته) برای چارچوب تنظیمات نادر.
 */

if (!defined('ABSPATH'))
    exit;

class Nader_Checkbox extends Nader_Module {

    public function __construct(array $args = []) {
        $default_args = [
            'name'        => 'checkbox_field',
            'title'       => 'فیلد چک‌باکس',
            'description' => '',
            'required'    => false,
            'default'     => [],
            'multilang'   => false,
            'value'       => '1',
            'label'       => '',
            'options'     => [],
            'inline'      => false
        ];

        $args = wp_parse_args($args, $default_args);
        parent::__construct($args);

        if (empty($this->args['label'])) {
            $this->args['label'] = $this->args['title'];
        }
    }

    protected function render_field(string $name, $value): void {
        if (!empty($this->args['options'])) {
            $current_values = (array) $value;
            $name .= '[]';

            // افزودن کلاس برای حالت inline
            $group_class = $this->args['inline'] ? 'nader-checkbox-inline' : 'nader-checkbox-block';

            echo '<div class="' . esc_attr($group_class) . '">';
            foreach ($this->args['options'] as $option_value => $option_label) {
                $checked = in_array($option_value, $current_values) ? 'checked' : '';
                $input_id = sanitize_title($name.'_'.$option_value);
                ?>
                <div class="nader-checkbox-item">
                    <input type="checkbox"
                           id="<?php echo esc_attr($input_id); ?>"
                           name="<?php echo esc_attr($name); ?>"
                           value="<?php echo esc_attr($option_value); ?>"
                        <?php echo $checked; ?>
                    >
                    <label for="<?php echo esc_attr($input_id); ?>">
                        <?php echo esc_html($option_label); ?>
                    </label>
                </div>
                <?php
            }
            echo '</div>';
        } else {
            $current_value = (string) $value;
            $checked = checked((int)$current_value, 1, false);
            ?>
            <div class="nader-checkbox-field">
                <label>
                    <input type="checkbox"
                           name="<?php echo esc_attr($name); ?>"
                           value="<?php echo esc_attr($this->args['value']); ?>"
                        <?php echo $checked; ?>
                    >
                    <?php echo esc_html($this->args['label']); ?>
                </label>
            </div>
            <?php
        }

        $this->render_errors($name);
    }

    protected function sanitize_value($value) {
        if (!empty($this->args['options'])) {
            return array_map('sanitize_text_field', (array)$value);
        }
        return ((string) $value === (string) $this->args['value']) ? 1 : 0;
    }

    protected function custom_validation($value, string $lang = ''): array {
        $errors = [];

        if (!empty($this->args['options'])) {
            // اعتبارسنجی چندگانه
            if ($this->is_required() && empty(array_filter((array)$value))) {
                $errors[] = $this->get_error_message('required_checked', $lang);
            }
        } else {
            // اعتبارسنجی تک گزینه‌ای
            if ($this->is_required() && (string)$value !== (string)$this->args['value']) {
                $errors[] = $this->get_error_message('required_checked', $lang);
            }
        }

        return $errors;
    }

    protected function get_error_message(string $code, string $lang = ''): string {
        $messages = [
            'required_checked' => 'انتخاب حداقل یک گزینه الزامی است.',
        ];
        return $messages[$code] ?? parent::get_error_message($code, $lang);
    }
}