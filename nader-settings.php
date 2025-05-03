<?php
/**
 * Plugin Name: Nader Settings Framework
 * Description: چارچوبی قدرتمند برای مدیریت تنظیمات وردپرس.
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: nader-settings
 * Domain Path: /languages
 */

if (!defined('ABSPATH'))
    exit; // اگر مستقیماً فراخوانی شده، خارج شو.

final class Nader_Settings{

    private static $instance;

    public static $settings_key = 'nader_settings'; // کلید ذخیره‌سازی تنظیمات در wp_options
    private $settings_data = [];                    // کش برای نگهداری تنظیمات بارگذاری شده
    private $loaded_module_classes = [];            // نگهداری لیست نام کلاس‌های ماژول بارگذاری شده
    private $registered_module_configs = [];        // <-- جدید: نگهداری پیکربندی‌های ماژول ثبت شده از تب‌ها
    public $tabs = [];                              // نگهداری اطلاعات تب‌های ثبت شده
    public $current_tab_id;                         // شناسه تب فعال فعلی

    /**
     * متد Singleton برای دریافت نمونه کلاس.
     */
    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    /**
     * مقداردهی اولیه پلاگین.
     */
    private function init()
    {
        $this->constants();
        $this->includes();
        $this->load_module_classes(); // <-- تغییر نام: فقط کلاس‌ها را بارگذاری کن، نمونه‌سازی نکن
        $this->load_tabs();           // بارگذاری فایل‌های تب (این‌ها پیکربندی ماژول‌ها را ثبت می‌کنند)
        $this->hooks();               // ثبت هوک‌های وردپرس
        $this->load_settings();       // بارگذاری تنظیمات ذخیره شده
    }

    /**
     * تعریف ثابت‌های پلاگین.
     */
    private function constants()
    {
        if (!defined('NAD_SETTINGS_PATH')) {
            define('NAD_SETTINGS_PATH', plugin_dir_path(__FILE__));
        }
        if (!defined('NAD_SETTINGS_URL')) {
            define('NAD_SETTINGS_URL', plugin_dir_url(__FILE__));
        }
        if (!defined('NAD_SETTINGS_VERSION')) {
            define('NAD_SETTINGS_VERSION', '1.0.0');
        }
    }

    /**
     * شامل کردن فایل‌های اصلی پلاگین.
     */
    private function includes()
    {
        // شامل کردن کلاس پایه ماژول. فایل‌های ماژول‌های خاص توسط load_module_classes شامل می‌شوند.
        require_once NAD_SETTINGS_PATH . 'includes/class-nader-module.php';
        // سایر فایل‌های اصلی اینجا شامل می‌شوند اگر نیاز باشد.
        require_once NAD_SETTINGS_PATH . 'includes/choose-search.php';

    }

    /**
     * بارگذاری فایل‌های کلاس ماژول.
     * کلاس‌ها در حافظه در دسترس قرار می‌گیرند، اما نمونه‌سازی نمی‌شوند.
     * اصلاح شد تا نام کلاس را به درستی از نام فایل استخراج کند.
     */
    private function load_module_classes()
    { // <-- تغییر نام متد
        $modules_dir = NAD_SETTINGS_PATH . 'modules/';
        if (!is_dir($modules_dir)) {
            error_log('Nader Settings: پوشه modules یافت نشد: ' . $modules_dir);
            return; // اگر پوشه modules وجود نداشت، کاری انجام نده.
        }
        // شامل کردن هر فایل در پوشه modules
        foreach (glob($modules_dir . '*.php') as $module_file) {
            require_once $module_file; // استفاده از require_once برای کلاس‌های ضروری

            // --- منطق صحیح ساخت نام کلاس از نام فایل ---
            $file_name_without_ext = basename($module_file, '.php');
            // اگر نام فایل از قبل با 'Nader_' شروع شده، همان را به عنوان نام کلاس در نظر بگیر.
            if (strpos($file_name_without_ext, 'Nader_') === 0) {
                $class_name = $file_name_without_ext;
            } else {
                // در غیر این صورت، نام فایل را به فرمت CamelCase تبدیل کرده و 'Nader_' را اضافه کن.
                // مثال: 'text' -> 'Nader_Text', 'my-field' -> 'Nader_MyField'
                $class_name = 'Nader_' . str_replace(' ', '', ucwords(str_replace([
                        '-',
                        '_'
                    ], ' ', $file_name_without_ext)));
            }
            // -----------------------------------------------------

            // بررسی اینکه آیا کلاس وجود دارد و از کلاس پایه Nader_Module ارث می‌برد
            if (class_exists($class_name) && is_subclass_of($class_name, 'Nader_Module')) {
                // فقط نام کلاس را ذخیره می‌کنیم، نه نمونه کلاس را
                $this->loaded_module_classes[$module_file] = $class_name; // ذخیره با کلید نام فایل برای ردیابی
                // می‌توانید نام اصلی ماژول را هم استخراج و ذخیره کنید اگر نیاز باشد
            } else {
                // ثبت خطا اگر کلاس یافت نشد یا از Nader_Module ارث نبرده است
                error_log("Nader Settings: قادر به بارگذاری کلاس ماژول از فایل " . $module_file . ". کلاس مورد انتظار " . $class_name . " وجود ندارد یا از Nader_Module ارث نمی‌برد.");
            }
        }
        // آرایه this->modules دیگر در اینجا پر نمی‌شود.
    }


    /**
     * بارگذاری فایل‌های تعریف تب از پوشه 'tabs/'.
     * هر فایل تب انتظار می‌رود که متد register_tab() و register_module_config() را فراخوانی کند.
     */
    private function load_tabs()
    {
        $tabs_dir = NAD_SETTINGS_PATH . 'tabs/';
        if (!is_dir($tabs_dir)) {
            error_log('Nader Settings: پوشه tabs یافت نشد: ' . $tabs_dir);
            return; // اگر پوشه tabs وجود نداشت، کاری انجام نده.
        }
        // شامل کردن هر فایل در پوشه tabs. انتظار می‌رود هر فایل register_tab() و register_module_config() را فراخوانی کند.
        foreach (glob($tabs_dir . '*.php') as $tab_file) {
            include_once $tab_file;
        }
        // تب‌ها در متد register_tab مرتب می‌شوند.
    }

    /**
     * ثبت یک تب تنظیمات.
     * قرار است از فایل‌های داخل پوشه 'tabs/' فراخوانی شود.
     *
     * @param array $tab_args آرگومان‌های تب: id (الزامی), title (الزامی), icon, order.
     */
    public function register_tab(array $tab_args)
    {
        // بررسی حداقل آرگومان‌های لازم
        if (empty($tab_args['id']) || empty($tab_args['title'])) {
            error_log('Nader Settings: تلاش برای ثبت تب با آرگومان‌های نامعتبر.');
            return;
        }
        // ادغام آرگومان‌های ورودی با آرگومان‌های پیش‌فرض و ذخیره تب
        $this->tabs[$tab_args['id']] = wp_parse_args($tab_args, [
            'id'    => '',
            'title' => '',
            'icon'  => '',
            'order' => 99 // ترتیب پیش‌فرض
        ]);
        // مرتب‌سازی تب‌ها پس از اضافه کردن برای حفظ ترتیب
        uasort($this->tabs, function($a, $b) {
            return $a['order'] <=> $b['order'];
        });
    }

    /**
     * جدید: ثبت پیکربندی یک ماژول (فیلد تنظیمات).
     * قرار است از فایل‌های داخل پوشه 'tabs/' فراخوانی شود.
     * این متد پیکربندی فیلد (مثل نام، عنوان، نوع، الزامی بودن، چندزبانه بودن) را ذخیره می‌کند.
     *
     * @param array $module_args آرگومان‌های ماژول (شامل 'name', 'type').
     */
    public function register_module_config(array $module_args)
    {
        if (empty($module_args['name']) || empty($module_args['type'])) {
            error_log('Nader Settings: تلاش برای ثبت پیکربندی ماژول بدون نام یا نوع.');
            return;
        }
        // ذخیره آرگومان‌های ماژول با نام به عنوان کلید
        $this->registered_module_configs[$module_args['name']] = $module_args;
    }

    /**
     * ثبت هوک‌های اکشن و فیلتر وردپرس.
     */
    private function hooks()
    {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_nader_save_settings', [$this, 'save_settings']);
        add_action('wp_ajax_nader_reset_settings', [$this, 'reset_settings']);
        // اکشن‌ها و فیلترهای دیگر اینجا ثبت می‌شوند.
    }

    /**
     * بارگذاری تنظیمات ذخیره شده از جدول wp_options.
     * این کار یک بار در طول init انجام می‌شود.
     */
    private function load_settings()
    {
        $this->settings_data = get_option(self::$settings_key, []);
        //        error_log('Nader Settings Load: داده‌های بارگذاری شده از پایگاه داده -> ' . print_r($this->settings_data, true)); // لاگ برای عیب‌یابی
    }

    /**
     * اضافه کردن صفحه تنظیمات به منوی مدیریت وردپرس.
     */
    public function add_admin_menu()
    {
        add_menu_page(
            'تنظیمات نادر',                  // عنوان صفحه در <title>
            'تنظیمات نادر',                  // متن منو در نوار کناری
            'manage_options',                // قابلیت مورد نیاز برای دسترسی
            'nader-settings',                // شناسه منحصر به فرد منو
            [$this, 'render_settings_page'], // تابع کال‌بک برای نمایش محتوای صفحه
            'dashicons-admin-settings',      // کلاس آیکون Dashicons
            80 // موقعیت در ترتیب منو
        );
    }


    /**
     * صف‌بندی اسکریپت‌ها و استایل‌ها برای صفحه تنظیمات مدیریت.
     * این متد بر روی هوک 'admin_enqueue_scripts' فراخوانی می‌شود.
     *
     * @param string $hook هوک صفحه مدیریت فعلی.
     */
    public function enqueue_assets($hook) {
        // فقط در صفحه تنظیمات افزونه دارایی‌ها را بارگذاری کن
        // 'toplevel_page_nader-settings' نام صفحه تنظیمات ما است که هنگام add_menu_page تعریف شد.
        if ($hook !== 'toplevel_page_nader-settings') {
            return;
        }

        wp_enqueue_style('nader-settings-admin-css', NAD_SETTINGS_URL . 'assets/css/admin.css', [], NAD_SETTINGS_VERSION);
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style('nader-select2-css', NAD_SETTINGS_URL . 'assets/select2/select2.min.css', [], '4.0.3');
        wp_enqueue_style('thickbox');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('nader-select2', NAD_SETTINGS_URL . 'assets/select2/select2.min.js', ['jquery'], '4.0.3', true);
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
        wp_enqueue_script('wp-editor');

        wp_enqueue_script(
            'nader-settings-admin', // Handle سفارشی
            NAD_SETTINGS_URL . 'assets/js/admin.js',
            [
                'jquery',           // نیاز عمومی به jQuery
                'wp-color-picker',  // برای ماژول Color
                'nader-select2',    // برای ماژول Choose
                'media-upload',     // برای ماژول Image و Gallery (مدال وردپرس)
                'thickbox',         // برای مدال Media Library
                'wp-editor',        // برای ماژول WP Editor (API)
                'wp-util',          // ممکن است برای برخی توابع وردپرس (مانند wp.template) لازم باشد، اختیاری اما توصیه می‌شود.
            ],
            NAD_SETTINGS_VERSION, // نسخه برای کش
            true // بارگذاری در فوتر (توصیه می‌شود)
        );

        // محلی‌سازی اسکریپت اصلی با داده‌های لازم برای AJAX و UI
        // این باید بعد از wp_enqueue_script برای 'nader-settings-admin' باشد.
        wp_localize_script(
            'nader-settings-admin',
            'naderSettings', // نام شیء JS در فرانت‌اند (مثال: naderSettings.ajaxurl)
            [
                'ajaxurl'          => admin_url('admin-ajax.php'), // آدرس endpoint ایجکس وردپرس
                'nonce'            => wp_create_nonce('nader_settings_nonce'), // Nonce برای امنیت
                'active_languages' => $this->get_active_languages(), // لیست زبان‌های فعال (برای Repeater چندزبانه و ...)
                'confirm_reset'    => 'آیا مطمئنید می‌خواهید تمام تنظیمات را بازنشانی کنید؟ این عمل قابل بازگشت نیست.', // پیام تایید بازنشانی
                'saving_text'      => 'در حال ذخیره...', // متن دکمه ذخیره هنگام ذخیره‌سازی
                'saved_text'       => 'ذخیره شد', // متن دکمه ذخیره پس از ذخیره‌سازی
                'save_text'        => 'ذخیره تغییرات', // متن پیش‌فرض دکمه ذخیره
                'validation_error_message' => 'خطا در اعتبارسنجی فیلدها. لطفا موارد مشخص شده را بررسی کنید.', // پیام کلی خطای اعتبارسنجی
                'save_success_message' => 'تنظیمات با موفقیت ذخیره شد.', // پیام موفقیت ذخیره‌سازی
                'reset_success_message' => 'تنظیمات با موفقیت بازنشانی شد.', // پیام موفقیت بازنشانی
            ]
        );

        wp_enqueue_script('nader-dependency', NAD_SETTINGS_URL . 'assets/js/nader-dependency.js', [], NAD_SETTINGS_VERSION, true);

        do_action('nader_settings_enqueue_module_assets', $this->current_tab_id, $this);
    }

    /**
     * رندر کردن HTML صفحه تنظیمات اصلی.
     */
    public function render_settings_page()
    {
        // تعیین تب فعلی بر اساس پارامتر 'tab' در URL یا پیش‌فرض به اولین تب
        $this->current_tab_id = $_GET['tab'] ?? array_key_first($this->tabs);

        if (empty($this->tabs) || !isset($this->tabs[$this->current_tab_id])) {
            if (!empty($this->tabs)) {
                $this->current_tab_id = array_key_first($this->tabs);
            } else {
                wp_die('هیچ تبی برای تنظیمات این پلاگین ثبت نشده است.');
            }
        }

        ?>
        <div class="nader-notice-area"></div>
        <form id="nader-settings-form" class="nader-settings-wrap" method="post">


            <div class="nader-settings-header">
                <h2>تنظیمات قالب نادر</h2>
                <div class="actions">
                    <span class="spinner"></span>
                    <button type="submit" class="button button-primary save-settings">
                        ذخیره تغییرات
                    </button>
                    <button type="button" class="button button-secondary reset-settings">
                        بازنشانی
                    </button>
                </div>
            </div>

            <div class="nader-settings-body">
                <nav class="nader-tabs-nav">
                    <?php
                    foreach ($this->tabs as $tab_id => $tab) {

                    }

                    foreach ($this->tabs as $tab_id => $tab) {
                        $active_class = ($tab_id === $this->current_tab_id) ? ' active' : '';
                        echo '<a href="#' . esc_attr($tab_id) . '" class="nader-tab' . $active_class . '" data-tab="' . esc_attr($tab_id) . '">';
                        if (!empty($tab['icon'])) { ?>
                                <span class="dashicons <?php echo esc_attr($tab['icon']) ?>"></span>
                        <?php }
                        echo esc_html($tab['title']);
                        echo '</a>';
                    } ?>
                </nav>

                <div class="nader-tab-content">
                    <?php
                    wp_nonce_field('nader_settings_nonce', 'nader_nonce');

                    foreach ($this->tabs as $tab_id => $tab) {
                        $active_class = ($tab_id === $this->current_tab_id) ? ' active' : '';
                        echo '<div class="nader-tab-panel' . $active_class . '" id="tab-' . esc_attr($tab_id) . '">';
                        do_action("nader_settings_tab_{$tab_id}", $this);
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </form>
        <?php
    }

    public function save_settings() {
        check_ajax_referer('nader_settings_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز.', 403);
        }

        $raw_data = $_POST['settings'] ?? '';
        parse_str($raw_data, $submitted_data);

        // حذف فیلدهای سیستمی
        unset(
            $submitted_data['nader_nonce'],
            $submitted_data['_wp_http_referer'],
            $submitted_data['current_tab_id']
        );

        $processed_settings = [];
        $validation_errors = [];
        $all_settings = get_option(self::$settings_key, []);

        foreach ($this->registered_module_configs as $module_name => $module_config) {
            try {
                $module_type = $module_config['type'];
                $module_class = 'Nader_' . str_replace('-', '_', ucwords($module_type, '-'));

                if (!class_exists($module_class) || !is_subclass_of($module_class, 'Nader_Module')) {
                    error_log("Nader Settings: Invalid module class for {$module_type}");
                    continue;
                }

                // پردازش ویژه برای Repeater
                if ($module_type === 'repeater') {
                    $module_instance = new $module_class($module_config);
                    $result = $module_instance->handle_submission($submitted_data);

                    if (!empty($result['errors'])) {
                        $validation_errors = array_merge($validation_errors, $result['errors']);
                    }

                    $processed_settings = array_merge(
                        $processed_settings,
                        $this->normalize_repeater_data($result['processed_data'])
                    );
                    continue;
                }

                // پردازش استاندارد برای سایر ماژول‌ها
                $module_instance = new $module_class($module_config);
                $result = $module_instance->handle_submission($submitted_data);

                if (!empty($result['errors'])) {
                    $validation_errors = array_merge($validation_errors, $result['errors']);
                }

                $processed_settings = array_merge(
                    $processed_settings,
                    $result['processed_data']
                );

            } catch (Exception $e) {
                error_log("Nader Settings Error processing {$module_name}: " . $e->getMessage());
            }
        }

        if (!empty($validation_errors)) {
            wp_send_json_error([
                'message' => 'خطا در اعتبارسنجی فیلدها',
                'errors' => $validation_errors
            ], 400);
        }

        //         اعتبارسنجی نهایی قبل از ذخیره
        if (!$this->validate_final_data($processed_settings)) {
            wp_send_json_error('داده‌های نامعتبر', 400);
        }

        update_option(self::$settings_key, $processed_settings);

        // Log final data
//        error_log('Nader Settings Final Saved Data: ' . print_r($updated_settings, true));

        wp_send_json_success('تنظیمات با موفقیت ذخیره شدند');
    }

    // تابع کمکی برای نرمال‌سازی داده‌های Repeater
    private function normalize_repeater_data(array $repeater_data): array {
        $normalized = [];

        foreach ($repeater_data as $key => $value) {
            preg_match('/(.+?)\[(\d+)\]\[(.+?)\]/', $key, $matches);

            if (count($matches) === 4) {
                $field_name = $matches[1];
                $index = $matches[2];
                $sub_field = $matches[3];

                $normalized[$field_name][$index][$sub_field] = $value;
            }
        }

        // تبدیل به آرایه عددی
        foreach ($normalized as $field => $items) {
            $normalized[$field] = array_values($items);
        }

        return $normalized;
    }

    // تابع اعتبارسنجی نهایی
    private function validate_final_data(array $data): bool {
        foreach ($data as $key => $value) {
            if (!is_string($key) || !$this->is_valid_key($key)) {
                error_log("Nader Settings: Invalid key format - {$key}");
                return false;
            }

            if (is_array($value)) {
                foreach ($value as $nested) {
                    if (!is_array($nested) && !is_scalar($nested)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    // بررسی فرمت کلیدهای مجاز
    private function is_valid_key(string $key): bool {
        return preg_match('/^[a-z0-9_\-\[\]]+$/i', $key) === 1;
    }

    /**
     * مدیریت درخواست AJAX برای بازنشانی تنظیمات.
     */
    public function reset_settings()
    {
        check_ajax_referer('nader_settings_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('دسترسی غیرمجاز.', 403);
        }

        // حذف گزینه تنظیمات از جدول wp_options
        delete_option(self::$settings_key);

        wp_send_json_success('تنظیمات با موفقیت بازنشانی شد!');
    }


    /**
     * دریافت مقدار یک تنظیم خاص.
     * از کش $settings_data بارگذاری شده در زمان init می‌خواند.
     *
     * @param string $name نام تنظیم (مثال: 'site_title__fa' یا 'site_tagline').
     * @param mixed $default مقدار پیش‌فرض برای برگرداندن اگر تنظیم یافت نشد. به صورت پیش‌فرض null.
     * @return mixed مقدار تنظیم یا مقدار پیش‌فرض.
     */
    public function get_setting(string $name, $default = null)
    {
        if (isset($this->settings_data[$name])) {
            return $this->settings_data[$name];
        }
        return $default;
    }

    /**
     * دریافت لیست زبان‌های فعال.
     * این متد قابل فیلتر کردن است.
     *
     * @return array آرایه‌ای از کدهای زبان (مثال: ['fa', 'en']).
     */
    public function get_active_languages(): array
    {
        return apply_filters('nader_settings_active_languages', ['fa', 'en']);
    }

    /**
     * متد کمکی برای دریافت پیکربندی یک ماژول ثبت شده بر اساس نام آن.
     *
     * @param string $name نام اصلی ماژول (مثال: 'site_title').
     * @return array|null پیکربندی ماژول یا null.
     */
    public function get_registered_module_config(string $name): ?array
    {
        return $this->registered_module_configs[$name] ?? null;
    }

    // می‌توانید متدهای کمکی دیگر مورد نیاز را اینجا اضافه کنید.
}

// نمونه‌سازی کلاس اصلی پلاگین با فراخوانی متد singleton.
Nader_Settings::instance();