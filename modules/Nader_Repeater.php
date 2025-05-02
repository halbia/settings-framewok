<?php
if (!defined('ABSPATH'))
    exit;

class Nader_Repeater extends Nader_Module {

    public function __construct(array $args = []) {
        $defaults = [
            'name'        => 'repeater_field',
            'title'       => 'Repeater Field',
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
                            <span class="item-title">Item #<?php echo $index + 1; ?></span>
                            <div class="item-actions">
                                <button type="button" class="move-up">↑</button>
                                <button type="button" class="move-down">↓</button>
                                <button type="button" class="toggle-item">▼</button>
                                <button type="button"
                                        class="remove-item" <?php echo $index < $this->args['min_items'] ? 'disabled' : ''; ?>>
                                    ×
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
                                    $module = new $module_class([
                                                                    'name'        => $field_name,
                                                                    'title'       => $field_config['title'],
                                                                    'description' => $field_config['description'] ?? '',
                                                                    'default'     => $item[$field_config['name']] ?? null,
                                                                    'multilang'   => false,
                                                                ] + $field_config);
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
        $processed_data = [];
        $errors = [];

        $base_name = $this->get_field_name();
        $items = $submitted_data[$base_name] ?? [];

        foreach ($items as $index => $item) {
            foreach ($this->args['fields'] as $field_config) {
                $module_class = 'Nader_' . str_replace('-', '_', ucwords($field_config['type'], '-'));
                if (class_exists($module_class)) {
                    $module = new $module_class($field_config);
                    $result = $module->handle_submission($item);

                    if (!empty($result['errors'])) {
                        foreach ($result['errors'] as $error_key => $error_msgs) {
                            $errors["{$base_name}[{$index}][{$error_key}]"] = $error_msgs;
                        }
                    }

                    $processed_data["{$base_name}[{$index}]"] = $result['processed_data'];
                }
            }
        }

        return [
            'processed_data' => $processed_data,
            'errors'         => $errors,
        ];
    }

    protected function sanitize_value($value)
    {
        return is_array($value) ? $value : [];
    }

    protected function custom_validation($value, string $lang = ''): array {
        $errors = [];
        $count = count($value);

        if ($this->args['min_items'] > 0 && $count < $this->args['min_items']) {
            $errors[] = "Minimum {$this->args['min_items']} items required";
        }

        if ($this->args['max_items'] > 0 && $count > $this->args['max_items']) {
            $errors[] = "Maximum {$this->args['max_items']} items allowed";
        }

        return $errors;
    }
}