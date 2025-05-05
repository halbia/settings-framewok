<?php
/**
 * ماژول فیلدهای ورودی پیشرفته برای چارچوب تنظیمات نادر.
 */

if (!defined('ABSPATH'))
    exit;

class Nader_Text extends Nader_Module {

    public function __construct(array $args = []) {
        $default_args = [
            'name'         => 'text_field',
            'title'        => 'فیلد متنی',
            'description'  => '',
            'required'     => false,
            'default'      => '',
            'multilang'    => false,
            'placeholder'  => '',
            'maxlength'    => '',
            'minlength'    => '',
            'pattern'      => '',
            'class'        => 'regular-text',
            'wrapper_class' => '',
            'attributes'   => [],
            'type'         => 'text', // انواع: text, url, tel, number, email
            'min'          => '',     // برای نوع number
            'max'          => '',     // برای نوع number
            'step' => 1   // برای نوع number
        ];

        parent::__construct(wp_parse_args($args, $default_args));
    }

    protected function render_field(string $name, $value): void {
        $input_type = $this->validate_input_type($this->args['type']);
        $attributes = $this->build_attributes($name, $value, $input_type);

        echo '<input ' . $this->render_attributes($attributes) . '>';
    }

    private function validate_input_type($type): string {
        $valid_types = ['text', 'url', 'tel', 'number', 'email'];
        return in_array($type, $valid_types) ? $type : 'text';
    }

    private function build_attributes($name, $value, $input_type): array {
        $attrs = [
            'type'        => $input_type,
            'name'        => esc_attr($name),
            'id'          => esc_attr($name),
            'value'       => esc_attr($value),
            'placeholder' => esc_attr($this->args['placeholder']),
            'maxlength'   => esc_attr($this->args['maxlength']),
            'minlength'   => esc_attr($this->args['minlength']),
            'pattern'     => esc_attr($this->args['pattern']),
            'class'       => esc_attr('nader-text-input ' . $this->args['class']),
            'dir'         => 'auto'
        ];

        if ($input_type === 'number') {
            $attrs['min'] = esc_attr($this->args['min']);
            $attrs['max'] = esc_attr($this->args['max']);
            $attrs['step'] = esc_attr($this->args['step']);
        }

        if ($this->is_required()) {
            $attrs['required'] = true;
        }

        return array_merge($attrs, array_map('esc_attr', $this->args['attributes']));
    }

    private function render_attributes($attributes): string {
        $output = [];
        foreach ($attributes as $attr => $val) {
            if (is_bool($val)) {
                if ($val) $output[] = esc_attr($attr);
            } elseif ($val !== '') {
                $output[] = sprintf('%s="%s"', esc_attr($attr), $val);
            }
        }
        return implode(' ', $output);
    }

    protected function custom_validation($value, string $lang = ''): array {
        $errors = [];
        $value_str = (string) $value;
        $input_type = $this->args['type'];

        // اعتبارسنجی الزامی بودن
        if ($this->is_required() && empty($value_str)) {
            $errors[] = $this->get_error_message('required', $lang);
            return $errors;
        }

        if (empty($value_str)) {
            return $errors;
        }

        // اعتبارسنجی نوع ورودی
        switch ($input_type) {

            case 'url':
                if (!filter_var($value_str, FILTER_VALIDATE_URL)) {
                    $errors[] = $this->get_error_message('invalid_url', $lang);
                }
                break;

            case 'tel':
                if (!preg_match('/^\+?[0-9\s\-\(\)]{6,20}$/', $value_str)) {
                    $errors[] = $this->get_error_message('invalid_tel', $lang);
                }
                break;

            case 'number':
                if (!is_numeric($value_str)) {
                    $errors[] = $this->get_error_message('not_number', $lang);
                } else {
                    $num = floatval($value_str);
                    if ($this->args['min'] !== '' && $num < $this->args['min']) {
                        $errors[] = sprintf(
                            $this->get_error_message('min_number', $lang),
                            $this->args['min']
                        );
                    }
                    if ($this->args['max'] !== '' && $num > $this->args['max']) {
                        $errors[] = sprintf(
                            $this->get_error_message('max_number', $lang),
                            $this->args['max']
                        );
                    }
                }
                break;

            case 'email':
                if (!filter_var($value_str, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = $this->get_error_message('invalid_email', $lang);
                }
                break;
        }

        // اعتبارسنجی طول
        $this->validate_length($value_str, $errors, $lang);

        // اعتبارسنجی الگو
        $this->validate_pattern($value_str, $errors, $lang);

        return $errors;
    }

    private function validate_length($value, &$errors, $lang) {
        $length = mb_strlen($value);

        if ($this->args['minlength'] && $length < $this->args['minlength']) {
            $errors[] = sprintf(
                $this->get_error_message('minlength', $lang),
                $this->args['minlength']
            );
        }

        if ($this->args['maxlength'] && $length > $this->args['maxlength']) {
            $errors[] = sprintf(
                $this->get_error_message('maxlength', $lang),
                $this->args['maxlength']
            );
        }
    }

    private function validate_pattern($value, &$errors, $lang) {
        if (!empty($this->args['pattern'])) {
            if (@preg_match($this->args['pattern'], null) === false) {
                error_log("Nader Text: الگوی نامعتبر برای فیلد {$this->get_name()} - {$this->args['pattern']}");
            } elseif (!preg_match($this->args['pattern'], $value)) {
                $errors[] = $this->get_error_message('pattern_mismatch', $lang);
            }
        }
    }

    protected function sanitize_value($value) {
        switch ($this->args['type']) {
            case 'number':
                return is_numeric($value) ? floatval($value) : 0;

            case 'url':
                return esc_url_raw(trim((string) $value));

            case 'tel':
                return preg_replace('/[^\d+]/', '', (string) $value);

            case 'email':
                return sanitize_email(trim((string) $value));

            default:
                return sanitize_text_field(trim((string) $value));
        }
    }

    protected function get_error_message(string $code, string $lang = ''): string {
        $messages = [
            'required'         => 'پر کردن این فیلد الزامی است.',
            'invalid_url'      => 'آدرس URL معتبر نیست.',
            'invalid_tel'      => 'شماره تلفن معتبر نیست. فرمت مجاز: +981234567890',
            'invalid_email'    => 'آدرس ایمیل معتبر نیست.',
            'not_number'       => 'مقدار باید عددی باشد.',
            'min_number'       => 'حداقل مقدار مجاز: %s',
            'max_number'       => 'حداکثر مقدار مجاز: %s',
            'minlength'        => 'حداقل طول مجاز: %s کاراکتر',
            'maxlength'        => 'حداکثر طول مجاز: %s کاراکتر',
            'pattern_mismatch' => 'قالب ورودی صحیح نیست.'
        ];

        $message = $messages[$code] ?? parent::get_error_message($code, $lang);

        return apply_filters(
            "nader_text_error_{$code}",
            $message,
            $this->args['name'],
            $lang
        );
    }
}