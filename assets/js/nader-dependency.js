document.addEventListener('DOMContentLoaded', function() {
    // تابع اصلی بررسی وابستگی‌ها
    const checkDependencies = () => {
        document.querySelectorAll('[data-dependencies]').forEach(wrapper => {
            try {
                const { relation, rules } = JSON.parse(wrapper.dataset.dependencies);
                let results = [];

                rules.forEach(rule => {
                    const fields = document.querySelectorAll(`[name="${rule.field}"]`);
                    let value;

                    // حالت خاص برای رادیو باتن‌ها
                    if (fields[0]?.type === 'radio') {
                        const selected = Array.from(fields).find(f => f.checked);
                        value = selected ? selected.value : null;
                    } else {
                        // برای سایر انواع فیلدها
                        const field = fields[0];
                        if (!field) return;

                        value = field.type === 'checkbox' ? field.checked : field.value;
                    }

                    // تبدیل نوع داده
                    if (typeof rule.value === 'number') value = Number(value);
                    if (typeof rule.value === 'boolean') value = value === 'true' ? true : Boolean(value);

                    // بررسی عملگرها
                    switch(rule.operator) {
                        case '==': results.push(value == rule.value); break;
                        case '!=': results.push(value != rule.value); break;
                        case '>':
                            results.push(Number(value) > rule.value);
                            break;
                        case '>=':
                            results.push(Number(value) >= rule.value);
                            break;
                        case '<':
                            results.push(Number(value) < rule.value);
                            break;
                        case '<=':
                            results.push(Number(value) <= rule.value);
                            break;
                        default:
                            results.push(false);
                    }
                });

                // اعمال نتیجه نهایی
                const showElement = relation === 'AND' ?
                    results.every(Boolean) :
                    results.some(Boolean);

                wrapper.style.display = showElement ? 'block' : 'none';

            } catch (error) {
                console.error('خطا در پردازش وابستگی‌ها:', error);
            }
        });
    };

    // رویدادهای تغییر مقدار
    document.querySelectorAll('input, select, textarea').forEach(element => {
        element.addEventListener('change', checkDependencies);
    });

    // اجرای اولیه
    checkDependencies();
});