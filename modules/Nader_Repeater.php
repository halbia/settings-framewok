<?php
if (!defined('ABSPATH'))
    exit;

class Nader_Repeater extends Nader_Module {

    public function __construct(array $args = []) {
        $defaults = [
            'name'        => 'repeater_field',
            'title' => 'فیلد تکرار شونده',
            'description' => '',
            'required'    => false,
            'default'     => [],
            'min_items'   => 0,
            'max_items'   => 0,
            'collapsible' => true,
            'fields'      => [],
        ];

        $args = wp_parse_args($args, $defaults);
        parent::__construct($args);
    }

    protected function render_field(string $name, $value): void {
        $items = is_array($value) ? $value : [];
        ?>
        <div class="nader-repeater-field"
             data-name="<?php echo esc_attr($this->get_field_name()); ?>"
             data-min-items="<?php echo $this->args['min_items']; ?>"
             data-max-items="<?php echo $this->args['max_items']; ?>"
             data-fields='<?php echo json_encode($this->args['fields'], JSON_HEX_APOS | JSON_HEX_QUOT); ?>'>

            <div class="repeater-items">
                <?php foreach ($items as $index => $item): ?>
                    <div class="repeater-item" data-index="<?php echo $index; ?>">
                        <div class="item-header">
                            <span class="item-title">آیتم <?php echo $index + 1; ?></span>
                            <div class="item-actions">
                                <button type="button" class="move-up">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M13.0001 22.0003L11.0002 22.0004L11.0002 5.82845L7.05044 9.77817L5.63623 8.36396L12.0002 2L18.3642 8.36396L16.9499 9.77817L13.0002 5.8284L13.0001 22.0003Z"></path>
                                    </svg>
                                </button>
                                <button type="button" class="move-down">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M13.0001 1.99974L11.0002 1.9996L11.0002 18.1715L7.05044 14.2218L5.63623 15.636L12.0002 22L18.3642 15.636L16.9499 14.2218L13.0002 18.1716L13.0001 1.99974Z"></path>
                                    </svg>
                                </button>
                                <button type="button" class="toggle-item">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M11.9995 0.499512L16.9492 5.44926L15.535 6.86347L12.9995 4.32794V9.99951H10.9995L10.9995 4.32794L8.46643 6.86099L7.05222 5.44678L11.9995 0.499512ZM10.9995 13.9995L10.9995 19.6704L8.46448 17.1353L7.05026 18.5496L12 23.4995L16.9497 18.5498L15.5355 17.1356L12.9995 19.6716V13.9995H10.9995Z"></path></svg>
                                </button>
                                <button type="button"
                                        class="remove-item" <?php echo $index < $this->args['min_items'] ? 'disabled' : ''; ?>>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M11.9997 10.5865L16.9495 5.63672L18.3637 7.05093L13.4139 12.0007L18.3637 16.9504L16.9495 18.3646L11.9997 13.4149L7.04996 18.3646L5.63574 16.9504L10.5855 12.0007L5.63574 7.05093L7.04996 5.63672L11.9997 10.5865Z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="item-content"
                             style="<?php echo $this->args['collapsible'] ? 'display:none;' : ''; ?>">
                            <?php
                            foreach ($this->args['fields'] as $field_config) {
                                $module_class = 'Nader_' . str_replace('-', '_', ucwords($field_config['type'], '-'));
                                if (class_exists($module_class)) {
                                    $field_name = "{$name}[{$index}][{$field_config['name']}]";
                                    $module = new $module_class(
                                        [
                                            'name'        => $field_name,
                                            'title'       => $field_config['title'],
                                            'description' => $field_config['description'] ?? '',
                                            'required'    => $field_config['required'] ?? false,
                                            'default'     => $item[$field_config['name']] ?? null,
                                            'multilang'   => false,
                                        ]
                                        + $field_config);
                                    $module->render();
                                }
                            }
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="add-repeater-item">Add Item</button>
        </div>
        <?php
    }

    public function handle_submission(array $submitted_data): array {
        $base_name = $this->get_field_name();
        $raw_items = $submitted_data[$base_name] ?? [];
        $processed_data = [];
        $errors = [];

        // تبدیل ساختار داده‌ها به فرمت صحیح
        $items = [];
        foreach ($raw_items as $index => $item) {
            if (is_array($item)) {
                $items[$index] = $item;
            }
        }

        foreach ($items as $index => $item) {
            foreach ($this->args['fields'] as $field_config) {
                $field_name = $field_config['name'];
                $module_class = 'Nader_' . str_replace('-', '_', ucwords($field_config['type'], '-'));

                if (class_exists($module_class)) {
                    $module = new $module_class($field_config + [
                            'name' => "{$base_name}[{$index}][{$field_name}]",
                            'default' => $item[$field_name] ?? null
                        ]);

                    $result = $module->handle_submission([
                        $field_name => $item[$field_name] ?? ''
                    ]);

                    if (!empty($result['errors'])) {
                        $errors = array_merge($errors, $result['errors']);
                    }

                    $processed_data["{$base_name}[{$index}][{$field_name}]"] =
                        $result['processed_data'][$field_name] ?? null;
                }
            }
        }

        return [
            'processed_data' => $processed_data,
            'errors' => $errors
        ];
    }

    protected function sanitize_value($value) {
        if (!is_array($value)) return [];

        $sanitized = [];
        foreach ($value as $index => $item) {
            foreach ($this->args['fields'] as $field_config) {
                $field_name = $field_config['name'];
                $sanitized[$index][$field_name] = sanitize_text_field($item[$field_name] ?? '');
            }
        }

        return $sanitized;
    }

    protected function custom_validation($value, string $lang = ''): array {
        $errors = [];

        if (!is_array($value)) {
            $errors[] = 'فرمت داده‌های تکراری نامعتبر است';
            return $errors;
        }

        foreach ($value as $index => $item) {
            foreach ($this->args['fields'] as $field_config) {
                $field_name = $field_config['name'];
                if (!empty($field_config['required']) && empty($item[$field_name])) {
                    $errors[] = sprintf(
                        'فیلد "%s" در آیتم شماره %d الزامی است',
                        $field_config['title'],
                        $index + 1
                    );
                }
            }
        }

        return $errors;
    }
}