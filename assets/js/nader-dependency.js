document.addEventListener('DOMContentLoaded', function() {
    // تابع اصلی بررسی وابستگی‌ها
    const checkDependencies = () => {
        document.querySelectorAll('[data-dependencies]').forEach(wrapper => {
            try {
                const { relation, rules } = JSON.parse(wrapper.dataset.dependencies);
                let results = [];

                rules.forEach(rule => {
                    const field = document.querySelector(`[name="${rule.field}"]`);
                    if (!field) return;

                    // دریافت مقدار فیلد با توجه به نوع آن
                    let value = field.type === 'checkbox' ? field.checked : field.value;

                    // تبدیل نوع داده بر اساس مقدار rule
                    if (typeof rule.value === 'number') value = Number(value);
                    if (typeof rule.value === 'boolean') value = Boolean(value);

                    // بررسی عملگرها
                    switch(rule.operator) {
                        case '==': results.push(value == rule.value); break;
                        case '!=': results.push(value != rule.value); break;
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
    document.querySelectorAll('input, select').forEach(element => {
        element.addEventListener('change', checkDependencies);
    });

    // اجرای اولیه
    checkDependencies();
});