<?php
/**
 * ماژول انتخابگر ایجکسی (جستجو در پست‌ها، ترم‌ها، کاربران، نقش‌ها) برای چارچوب تنظیمات نادر.
 */

if (!defined('ABSPATH'))
    exit;

class Nader_Choose extends Nader_Module {

    public function __construct(array $args = []) {
        $default_args = [
            'name'        => 'choose_field',
            'title'       => 'فیلد انتخابگر',
            'description' => '',
            'required'    => false,
            'default'     => '',
            'multilang'   => false,
            'multiple'    => false,
            'query'       => [],
            'placeholder' => 'برای جستجو تایپ کنید...',
            'class'       => 'nader-choose-select',
            'attributes'  => [],
            'initial_value_label' => ''
        ];

        parent::__construct(wp_parse_args($args, $default_args));

        // مدیریت پیش‌فرض برای نوع role
        if ($this->args['query']['type'] === 'role') {
            if ($this->args['multiple'] && !is_array($this->args['default'])) {
                $this->args['default'] = [];
            } elseif (!$this->args['multiple'] && is_array($this->args['default'])) {
                $this->args['default'] = reset($this->args['default']);
            }
        }
    }

    protected function render_field(string $name, $value): void {
        $current_value = $value;
        $is_multiple = (bool)$this->args['multiple'];
        $query_type = $this->args['query']['type'];

        // تبدیل مقدار به فرمت صحیح
        $selected_ids = [];
        if ($query_type === 'role') {
            $selected_ids = $is_multiple ? (array)$current_value : [$current_value];
        } else {
            $selected_ids = $this->normalize_ids($current_value, $is_multiple);
        }

        $initial_options = [];
        if (!empty($selected_ids)) {
            $initial_items = $this->get_initial_selected_items($selected_ids, $query_type);
            foreach ($initial_items as $item) {
                $initial_options[(string)$item['id']] = $item['text'];
            }
        }

        $attributes = [
            'name' => esc_attr($name) . ($is_multiple ? '[]' : ''),
            'id' => esc_attr($name),
            'class' => esc_attr('nader-choose-select ' . $this->args['class']),
            'data-placeholder' => esc_attr($this->args['placeholder']),
            'data-query-args' => esc_attr(json_encode($this->args['query'])),
            'dir' => 'rtl'
        ];

        if ($is_multiple) $attributes['multiple'] = 'multiple';
        if ($this->is_required() && !$is_multiple && empty($selected_ids)) {
            $attributes['required'] = 'required';
        }

        echo '<select ';
        foreach ($attributes as $attr => $val) {
            if (is_bool($val)) {
                if ($val) echo esc_attr($attr) . ' ';
            } else {
                printf('%s="%s" ', esc_attr($attr), $val);
            }
        }
        echo '>';

        foreach ($initial_options as $id => $label) {
            printf('<option value="%s" selected>%s</option>', esc_attr($id), esc_html($label));
        }

        echo '</select>';
        $this->render_errors($name);
    }

    private function get_initial_selected_items(array $ids, string $type): array {
        $items = [];
        if (empty($ids)) {
            return $items;
        }
        switch ($type) {
            case 'post':
                $posts = get_posts([
                    'post_type' => $this->args['query']['post_type'] ?? 'post',
                    'post__in' => $ids,
                    'posts_per_page' => -1,
                    'orderby' => 'post__in'
                ]);
                foreach ($posts as $post) {
                    $items[] = ['id' => $post->ID, 'text' => $post->post_title];
                }
                break;

            case 'taxonomy':
                $terms = get_terms([
                    'taxonomy' => $this->args['query']['taxonomy'],
                    'include' => $ids,
                    'hide_empty' => false
                ]);
                foreach ($terms as $term) {
                    $items[] = ['id' => $term->term_id, 'text' => $term->name];
                }
                break;

            case 'user':
                $users = get_users(['include' => $ids]);
                foreach ($users as $user) {
                    $items[] = ['id' => $user->ID, 'text' => $user->display_name];
                }
                break;

            case 'role':
                $roles = get_editable_roles();
                foreach ($ids as $role_id) {
                    if (isset($roles[$role_id])) {
                        $items[] = [
                            'id' => $role_id,
                            'text' => translate_user_role($roles[$role_id]['name'])
                        ];
                    }
                }
                break;
        }
        return $items;
    }

    protected function custom_validation($value, string $lang = ''): array {
        $errors = [];
        $query_type = $this->args['query']['type'];

        // اگر فیلد الزامی نیست و مقدار خالی است
        if (!$this->is_required() && empty($value)) {
            return $errors;
        }

        // اعتبارسنجی نقش‌ها
        if ($query_type === 'role') {
            $submitted = $this->args['multiple'] ? (array)$value : [$value];
            $submitted = array_filter($submitted); // حذف مقادیر خالی

            if (!empty($submitted)) {
                $valid_roles = array_keys(get_editable_roles());
                foreach ($submitted as $role) {
                    if (!in_array($role, $valid_roles)) {
                        $errors[] = 'نقش انتخاب شده نامعتبر است.';
                        break;
                    }
                }
            }
        }

        // اعتبارسنجی الزامی بودن
        if ($this->is_required() && empty($value)) {
            $errors[] = $this->get_error_message('required', $lang);
        }

        return $errors;
    }

    protected function sanitize_value($value) {
        $query_type = $this->args['query']['type'];

        if ($query_type === 'role') {
            $valid_roles = array_keys(get_editable_roles());

            // اگر مقدار خالی است
            if (empty($value)) {
                return $this->args['multiple'] ? [] : '';
            }

            if ($this->args['multiple']) {
                return array_filter(
                    (array)$value,
                    fn($role) => in_array($role, $valid_roles)
                );
            }

            return in_array($value, $valid_roles) ? $value : '';
        }

        return parent::sanitize_value($value);
    }

    private function normalize_ids($value, bool $is_multiple): array {
        if ($is_multiple) {
            return array_filter((array)$value, fn($id) => is_numeric($id) && $id > 0);
        }
        return is_numeric($value) && $value > 0 ? [(int)$value] : [];
    }
}