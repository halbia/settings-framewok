<?php
/**
 * ماژول چک‌باکس تک حالته برای چارچوب تنظیمات نادر.
 */

if (!defined('ABSPATH'))
    exit; // اگر مستقیماً فراخوانی شده، خارج شو.

// کلاس باید Nader_Checkbox نام داشته باشد و از Nader_Module ارث ببرد.
class Nader_Checkbox extends Nader_Module {

    /**
     * سازنده کلاس. آرگومان‌های پیش‌فرض ماژول چک‌باکس را تنظیم می‌کند.
     *
     * @param array $args آرایه‌ای از آرگومان‌ها.
     */
    public function __construct(array $args = []) {
        $default_args = [
            'name'        => 'checkbox_field',
            'title'       => 'فیلد چک‌باکس',
            'description' => '',
            'required'    => false, // چک‌باکس معمولاً الزامی نیست به معنای اجبار به تیک زدن.
            'default'     => 0, // پیش‌فرض می‌تواند 0 (عدم انتخاب) یا 1 (انتخاب) باشد.
            'multilang'   => false,
            'value'       => '1', // مقداری که در صورت انتخاب شدن ارسال می‌شود.
            'label'       => '', // برچسب کنار چک‌باکس (اگر با title تفاوت دارد).
        ];

        parent::__construct(wp_parse_args($args, $default_args));

        // اگر برچسب کنار چک‌باکس داده نشده بود، از عنوان اصلی استفاده کن.
        if (empty($this->args['label'])) {
            $this->args['label'] = $this->args['title'];
        }
    }

    /**
     * پیاده‌سازی متد render_field برای نمایش یک چک‌باکس.
     *
     * @param string $name ویژگی 'name' کامل HTML برای چک‌باکس.
     * @param mixed $value مقدار فعلی فیلد (انتظار می‌رود 0 یا 1 باشد).
     */
    protected function render_field(string $name, $value): void {
        $current_value = (string) $value; // تبدیل به رشته برای مقایسه دقیق

        // بررسی اینکه آیا چک‌باکس باید تیک خورده باشد.
        // مقدار ذخیره شده را با مقدار "انتخاب شده" چک‌باکس (پیش‌فرض '1') مقایسه می‌کنیم.
        // یا می‌توانیم مستقیماً مقدار را به boolean تبدیل کنیم (true برای مقادیر true/1/'1'، false برای سایر).
        $checked = checked((int)$current_value, 1, false); // تیک خورده اگر مقدار ذخیره شده 1 باشد.
        $input_value = esc_attr($this->args['value']); // مقداری که در صورت تیک خوردن ارسال می‌شود ('1' پیش‌فرض)

        ?>
        <div class="nader-checkbox-field">
            <label for="<?php echo esc_attr($name); ?>">
                <input type="checkbox"
                       name="<?php echo esc_attr($name); ?>"
                       id="<?php echo esc_attr($name); ?>"
                       value="<?php echo $input_value; ?>"
                    <?php echo $checked; ?>
                    <?php
                    // رندر ویژگی‌های HTML اضافی از آرگومان 'attributes'
                    if (!empty($this->args['attributes']) && is_array($this->args['attributes'])) {
                        foreach (array_map('esc_attr', $this->args['attributes']) as $attr => $val) {
                            printf('%s="%s" ', $attr, $val);
                        }
                    }
                    ?>
                />
                <?php echo esc_html($this->args['label']); ?> </label>

            <?php $this->render_errors($name); ?>
        </div>
        <?php
        // توجه: اگر checkbox تیک نخورد، به صورت پیش‌فرض هیچ مقداری برای آن name در داده‌های فرم ارسال نمی‌شود.
        // این موضوع در handle_submission و sanitize_value باید مدیریت شود.
    }

    /**
     * پیاده‌سازی متد custom_validation برای اعتبارسنجی فیلد چک‌باکس.
     * اعتبارسنجی الزامی بودن چک‌باکس (اگر لازم باشد تیک خورده باشد) در اینجا قابل انجام است.
     *
     * @param mixed $value مقدار ارسالی فیلد ('1' اگر تیک خورده یا ''/null اگر تیک نخورده).
     * @param string $lang کد زبان.
     * @return array آرایه‌ای از پیام‌های خطا.
     */
    protected function custom_validation($value, string $lang = ''): array {
        $errors = [];

        // چک‌باکس معمولاً الزامی نیست به معنای اجبار به تیک زدن.
        // اما اگر required true باشد و شما می‌خواهید کاربر *حتماً* آن را تیک بزند:
        if ($this->is_required()) {
            // مقدار ارسالی برای چک‌باکس تیک خورده معمولاً برابر با value آرگومان است (پیش‌فرض '1').
            // اگر مقدار ارسالی با مقدار value چک‌باکس برابر نبود، یعنی تیک نخورده.
            if ((string) $value !== (string) $this->args['value']) {
                // می‌توانید یک پیام خطای مخصوص اجبار به انتخاب چک‌باکس اضافه کنید.
                $errors[] = $this->get_error_message('required_checked', $lang); // نیاز به تعریف پیام 'required_checked'
            }
        }

        return $errors;
    }

    /**
     * پیاده‌سازی متد sanitize_value برای پاکسازی مقدار چک‌باکس.
     * مقدار ارسالی را به 1 (تیک خورده) یا 0 (تیک نخورده) تبدیل می‌کند.
     *
     * @param mixed $value مقدار ارسالی فیلد ('1' اگر تیک خورده یا ''/null اگر تیک نخورده).
     * @return int 1 اگر تیک خورده، 0 اگر تیک نخورده.
     */
    protected function sanitize_value($value) {
        // اگر مقدار ارسالی برابر با مقداری است که چک‌باکس هنگام انتخاب ارسال می‌کند ('1' پیش‌فرض)، 1 برگردان.
        // در غیر این صورت (اگر مقداری ارسال نشده یا مقدار دیگری است)، 0 برگردان.
        return ((string) $value === (string) $this->args['value']) ? 1 : 0;
    }

    /**
     * override کردن متد get_error_message برای افزودن پیام خطای خاص ماژول چک‌باکس.
     */
    protected function get_error_message(string $code, string $lang = ''): string {
        $messages = [
            'required_checked' => 'برای ادامه، لطفاً این گزینه را انتخاب کنید.', // پیام خطای اجبار به تیک زدن
        ];
        return $messages[$code] ?? parent::get_error_message($code, $lang);
    }

    // متد handle_submission از کلاس والد Nader_Module استفاده می‌کند.


    // --- مثال: پیاده‌سازی چک‌باکس برای انتخاب چند گزینه ---
    // اگر نیاز به انتخاب چند گزینه از یک مجموعه داشتید (مثل دسته بندی‌ها)،
    // باید یک ماژول جدید بسازید که آرایه‌ای از گزینه‌ها را در args['options'] بگیرد
    // و در render_field چندین input type="checkbox" با *نام یکسان و علامت [] در انتها*
    // (مثال: name="my_options[]") و valueهای مختلف رندر کند.
    // متد handle_submission والد ممکن است نیاز به override داشته باشد
    // تا آرایه مقادیر ارسال شده را به درستی sanitize و validate کند.
    // متد sanitize_value باید آرایه را دریافت و روی عناصر آن sanitize_text_field را اعمال کند.
    // متد validate باید آرایه را بررسی کند (مثلاً اگر الزامی بود، آرایه خالی نباشد و عناصر آن معتبر باشند).
}
