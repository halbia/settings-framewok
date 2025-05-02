jQuery(document).ready(function($) {

    // مطمئن شوید شی naderSettings توسط wp_localize_script تعریف شده است.
    if (typeof naderSettings === 'undefined') {
        console.error('Nader Settings JS: شی naderSettings در جاوا اسکریپت تعریف نشده است.');
        return;
    }

    const $form = $('#nader-settings-form');
    const $saveButton = $form.find('.save-settings');
    const $resetButton = $form.find('.reset-settings');
    const $spinner = $form.find('.spinner');
    const $noticeArea = $('.nader-notice-area'); // کانتینر برای نمایش پیام‌های کلی

    // --- مدیریت نمایش پیام‌های عمومی (موفقیت/خطا) ---
    function showNotice(type, message) {
        $noticeArea.empty();
        const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        const $notice = $(`
            <div class="notice ${noticeClass} is-dismissible">
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">رد کردن این پیام.</span>
                </button>
            </div>
        `);
        $noticeArea.append($notice);
        $notice.delay(5000).fadeOut(400, function() { $(this).remove(); });
        $notice.on('click', '.notice-dismiss', function() { $notice.fadeOut(400, function() { $(this).remove(); }); });
    }

    // --- مدیریت نمایش خطاهای اعتبارسنجی کنار فیلدها ---
    function displayValidationErrors(errors) {
        // ابتدا تمام خطاهای قبلی را پاک کن
        $('.nader-errors').empty().hide();
        $('.nader-field-wrapper').removeClass('has-error');
        $('.nader-lang-field').removeClass('has-error');
        $('.nader-image-preview img, .nader-gallery-preview img').removeClass('error-border');
        $('.wp-editor-wrap').removeClass('has-error');
        $('.select2-container').removeClass('has-error-select2');
        $('.nader-repeater-item').removeClass('has-error'); // جدید: حذف کلاس خطا از آیتم Repeater

        if (errors) {
            for (const fieldName in errors) {
                if (errors.hasOwnProperty(fieldName)) {
                    // پیدا کردن کانتینر خطای مربوط به این فیلد با استفاده از ID
                    const $errorList = $(`#${fieldName}-errors`);
                    if ($errorList.length) {
                        // اضافه کردن هر پیام خطا به لیست
                        errors[fieldName].forEach(errorMessage => { $errorList.append(`<li>${escHtml(errorMessage)}</li>`); });
                        $errorList.show();

                        // اضافه کردن کلاس خطا به wrapper والد فیلد
                        $errorList.closest('.nader-field-wrapper, .nader-lang-field').addClass('has-error');

                        // اضافه کردن کلاس خطا به WP Editor wrapper اگر مربوط به WP Editor است.
                        // WP Editor ID همان fieldName است.
                        $(`#wp-${fieldName}-wrap`).addClass('has-error');

                        // جدید: اضافه کردن کلاس خطا به Select2/SelectWoo container
                        $(`#${fieldName}`).next('.select2-container').addClass('has-error-select2');

                        // جدید: پیدا کردن آیتم Repeater والد بر اساس نام فیلد خطا
                        // نام فیلد خطا ممکن است شامل repeater_name[index][sub_name] باشد
                        // باید نام repeater اصلی را استخراج کنیم و آیتم خاص را پیدا کنیم.
                        const repeaterMatch = fieldName.match(/^([^\[]+)\[(\d+)\]/);
                        if (repeaterMatch && repeaterMatch[1] && repeaterMatch[2]) {
                            const repeaterBaseName = repeaterMatch[1];
                            const itemIndex = repeaterMatch[2];
                            // پیدا کردن کانتینر Repeater بر اساس نام
                            // توجه: ID Repeater در PHP به صورت nader-repeater-FIELD_NAME رندر می‌شود.
                            const $repeaterField = $(`#nader-repeater-${repeaterBaseName}`);
                            if ($repeaterField.length) {
                                // پیدا کردن آیتم Repeater خاص بر اساس data-item-index
                                $repeaterField.find(`.nader-repeater-items-list > li.nader-repeater-item[data-item-index="${itemIndex}"]`).addClass('has-error');
                            }
                        }


                    } else {
                        console.warn(`Nader Settings JS: کانتینر خطا برای فیلد "${fieldName}" یافت نشد.`, errors[fieldName]);
                    }
                }
            }
        }
    }

    // --- پاک کردن تمام خطاهای اعتبارسنجی ---
    function clearValidationErrors() {
        $('.nader-errors').empty().hide();
        $('.nader-field-wrapper').removeClass('has-error');
        $('.nader-lang-field').removeClass('has-error');
        $('.nader-image-preview img, .nader-gallery-preview img').removeClass('error-border');
        $('.wp-editor-wrap').removeClass('has-error');
        $('.select2-container').removeClass('has-error-select2');
        $('.nader-repeater-item').removeClass('has-error'); // جدید: پاک کردن کلاس خطا از آیتم Repeater
    }

    // --- کمک برای escape کردن HTML در پیام‌ها ---
    function escHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }


    // --- مدیریت ذخیره تنظیمات با AJAX ---
    $form.on('submit', function(e) {
        e.preventDefault(); // جلوگیری از ارسال فرم به روش سنتی

        // پاک کردن خطاهای قبلی و پیام‌های کلی
        clearValidationErrors();
        $noticeArea.empty();

        // --- به‌روزرسانی محتوای WP Editor قبل از سریالایز کردن فرم ---
        // اطمینان از اینکه TinyMCE محتوای خود را به textarea اصلی کپی کرده است.
        // این باید برای تمام WP Editor ها در صفحه، از جمله داخل Repeater ها اجرا شود.
        $('.nader-wp-editor-input').each(function() {
            const editorId = $(this).attr('id');
            // بررسی وجود TinyMCE و فعال بودن آن (حالت بصری)
            if (typeof tinymce !== 'undefined' && tinymce.get(editorId)) {
                const editor = tinymce.get(editorId);
                // editor.isHidden() چک می‌کند که آیا در حالت Text (Quicktags) هستیم یا نه.
                if (editor && !editor.isHidden()) {
                    editor.save(); // ذخیره محتوای TinyMCE در textarea
                }
            }
            // برای حالت Text (Quicktags) نیازی به save نیست، محتوا مستقیماً در textarea است.
        });


        $saveButton.text(naderSettings.saving_text).prop('disabled', true);
        $resetButton.prop('disabled', true);
        $spinner.addClass('is-active');

        // جمع‌آوری داده‌های فرم
        // $.serialize() تمام فیلدهای فرم را به یک رشته URL-encoded تبدیل می‌کند.
        // ایندکس __INDEX__ در فیلدهای تمپلیت نباید در serialization حضور داشته باشد.
        // این با display: none روی کانتینر تمپلیت مدیریت می‌شود.

        const formData = $form.serialize();

        $.ajax({
            url: naderSettings.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'nader_save_settings',
                nonce: naderSettings.nonce,
                settings: formData
            },
            success: function(response) {
                console.log('پاسخ موفقیت:', response);
                showNotice('success', naderSettings.save_success_message);
                $saveButton.text(naderSettings.saved_text);
                setTimeout(() => { $saveButton.text(naderSettings.save_text); }, 2000);
            },
            error: function(xhr, status, error) {
                console.error('خطای AJAX:', status, error);
                console.log('پاسخ خطا:', xhr.responseJSON);
                const response = xhr.responseJSON;
                const errorMessage = response && response.data && response.data.message ? response.data.message : naderSettings.validation_error_message;
                showNotice('error', errorMessage);
                if (response && response.data && response.data.errors) {
                    displayValidationErrors(response.data.errors);
                }
                $saveButton.text(naderSettings.save_text);
            },
            complete: function() {
                $saveButton.prop('disabled', false);
                $resetButton.prop('disabled', false);
                $spinner.removeClass('is-active');
            }
        });
    });

    // --- مدیریت بازنشانی تنظیمات با AJAX ---
    $resetButton.on('click', function(e) {
        e.preventDefault();
        if (!confirm(naderSettings.confirm_reset)) { return; }
        clearValidationErrors();
        $noticeArea.empty();
        $.ajax({
            url: naderSettings.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'nader_reset_settings',
                nonce: naderSettings.nonce
            },
            success: function(response) {
                console.log('پاسخ موفقیت بازنشانی:', response);
                showNotice('success', naderSettings.reset_success_message);
                if (response.success) {
                    setTimeout(() => { window.location.reload(); }, 800);
                }
            },
            error: function(xhr, status, error) {
                console.error('خطای AJAX بازنشانی:', status, error);
                console.log('پاسخ خطا بازنشانی:', xhr.responseJSON);
                const response = xhr.responseJSON;
                const errorMessage = response && response.data ? response.data : 'خطا در بازنشانی تنظیمات.';
                showNotice('error', errorMessage);
            }
        });
    });


    // --- منطق ماژول‌ها سمت کلاینت ---

    // --- مدیریت انتخاب تصویر برای ماژول Nader_Image ---
    $('.nader-settings-wrap').on('click', '.nader-select-image-button', function(e) {
        e.preventDefault();
        const $button = $(this);
        // پیدا کردن کانتینر فیلد، حالا ممکن است داخل آیتم Repeater باشد.
        const $fieldContainer = $button.closest('.nader-image-upload-field');
        const $imageIdInput = $fieldContainer.find('.nader-image-id-input');
        const $imagePreview = $fieldContainer.find('.nader-image-preview');

        let mediaFrame = wp.media.frames.naderSettingsMedia = wp.media({
            title: $fieldContainer.data('uploader-title') || 'انتخاب تصویر',
            button: { text: $fieldContainer.data('uploader-button-text') || 'استفاده از این تصویر', },
            multiple: false,
            library: { type: $fieldContainer.data('mime-types') || 'image', }
        });
        mediaFrame.on('open', function() {
            const selection = mediaFrame.state().get('selection');
            const selectedId = $imageIdInput.val();
            if (selectedId) {
                const attachment = wp.media.attachment(selectedId);
                attachment.fetch();
                selection.add(attachment ? [attachment] : []);
            }
        });
        mediaFrame.on('select', function() {
            const attachment = mediaFrame.state().get('selection').first().toJSON();
            $imageIdInput.val(attachment.id);
            const imageUrl = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
            $imagePreview.html(`<img src="${escHtml(imageUrl)}" alt="${escHtml(attachment.alt || attachment.title || '')}">`);
            const $removeButton = $fieldContainer.find('.nader-remove-image-button');
            if ($removeButton.length === 0) {
                const removeBtnHtml = `<button type="button" class="button button-secondary nader-remove-image-button">حذف تصویر</button>`;
                $button.after(removeBtnHtml);
            } else { $removeButton.show(); }
        });
        mediaFrame.open();
    });

    // مدیریت حذف تصویر برای ماژول Nader_Image
    $('.nader-settings-wrap').on('click', '.nader-remove-image-button', function(e) {
        e.preventDefault();
        const $button = $(this);
        const $fieldContainer = $button.closest('.nader-image-upload-field');
        const $imageIdInput = $fieldContainer.find('.nader-image-id-input');
        const $imagePreview = $fieldContainer.find('.nader-image-preview');
        $imageIdInput.val('');
        $imagePreview.empty();
        $button.hide();
    });


    // --- مدیریت انتخاب تصاویر برای ماژول Nader_Gallery ---
    $('.nader-settings-wrap').on('click', '.nader-select-gallery-button', function(e) {
        e.preventDefault();
        const $button = $(this);
        // پیدا کردن کانتینر فیلد، حالا ممکن است داخل آیتم Repeater باشد.
        const $fieldContainer = $button.closest('.nader-gallery-field');
        const $galleryIdsInput = $fieldContainer.find('.nader-gallery-ids-input');
        const $galleryPreviewList = $fieldContainer.find('.nader-gallery-preview');
        let $clearButton = $fieldContainer.find('.nader-clear-gallery-button');

        const currentIdsString = $galleryIdsInput.val();
        const currentIdsArray = currentIdsString ? currentIdsString.split(',').map(id => parseInt(id, 10)).filter(id => !isNaN(id) && id > 0) : [];

        let mediaFrame = wp.media.frames.naderSettingsGallery = wp.media({
            title: $fieldContainer.data('uploader-title') || 'انتخاب تصاویر گالری',
            button: { text: $fieldContainer.data('uploader-button-text') || 'افزودن به گالری', },
            multiple: true,
            library: { type: $fieldContainer.data('mime-types') || 'image', }
        });

        mediaFrame.on('open', function() {
            const selection = mediaFrame.state().get('selection');
            currentIdsArray.forEach(id => {
                const attachment = wp.media.attachment(id);
                if(attachment) { selection.add(attachment); }
            });
        });

        mediaFrame.on('select', function() {
            const selection = mediaFrame.state().get('selection').toJSON();
            let newImageIds = [];
            $galleryPreviewList.empty();

            selection.forEach(attachment => {
                if (attachment.id) {
                    newImageIds.push(attachment.id);
                    const imageUrl = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
                    const $galleryItem = $(`
                         <li class="image" data-id="${escHtml(attachment.id)}">
                            <img src="${escHtml(imageUrl)}" alt="${escHtml(attachment.alt || attachment.title || '')}">
                            <button type="button" class="remove-image-button"><span class="dashicons dashicons-no-alt"></span></button>
                         </li>
                     `);
                    $galleryPreviewList.append($galleryItem);
                }
            });
            $galleryIdsInput.val(newImageIds.join(','));

            if (newImageIds.length > 0) {
                if ($clearButton.length === 0) {
                    const clearBtnHtml = `<button type="button" class="button button-secondary nader-clear-gallery-button">حذف همه تصاویر</button>`;
                    $button.after(clearBtnHtml);
                    $clearButton = $fieldContainer.find('.nader-clear-gallery-button');
                }
                $clearButton.show();
            } else { $clearButton.hide(); }

            // TODO: SortableJS initialization and update logic here if used.
        });
        mediaFrame.open();
    });

    // مدیریت حذف یک تصویر تکی از گالری
    $('.nader-settings-wrap').on('click', '.nader-gallery-preview .remove-image-button', function() {
        const $button = $(this);
        const $galleryItem = $button.closest('li.image');
        const removedId = $galleryItem.data('id');
        // پیدا کردن کانتینر فیلد، حالا ممکن است داخل آیتم Repeater باشد.
        const $fieldContainer = $galleryItem.closest('.nader-gallery-field');
        const $galleryIdsInput = $fieldContainer.find('.nader-gallery-ids-input');
        const $clearButton = $fieldContainer.find('.nader-clear-gallery-button');
        $galleryItem.remove();
        let currentIdsArray = $galleryIdsInput.val().split(',').map(id => parseInt(id, 10)).filter(id => !isNaN(id) && id > 0);
        currentIdsArray = currentIdsArray.filter(id => id !== removedId);
        $galleryIdsInput.val(currentIdsArray.join(','));
        if (currentIdsArray.length === 0) { $clearButton.hide(); }
    });

    // مدیریت حذف همه تصاویر از گالری
    $('.nader-settings-wrap').on('click', '.nader-clear-gallery-button', function() {
        const $button = $(this);
        // پیدا کردن کانتینر فیلد، حالا ممکن است داخل آیتم Repeater باشد.
        const $fieldContainer = $button.closest('.nader-gallery-field');
        const $galleryIdsInput = $fieldContainer.find('.nader-gallery-ids-input');
        const $galleryPreviewList = $fieldContainer.find('.nader-gallery-preview');
        $galleryIdsInput.val('');
        $galleryPreviewList.empty();
        $button.hide();
    });


    // --- منطق فعال کردن ماژول‌های سمت کلاینت ---

    // فعال کردن Color Picker وردپرس
    // این تابع حالا یک پارامتر $container می‌گیرد.
    function initializeColorPickers($container) {
        // اطمینان از وجود تابع wpColorPicker قبل از فراخوانی آن
        if (typeof $.fn.wpColorPicker === 'undefined') {
            // گزارش کلی در initializeNaderSettingsModules انجام می‌شود.
            return;
        }
        // در کانتینر مشخص شده (یا کل صفحه)، inputهایی با کلاس مخصوص را پیدا کن و Color Picker را متصل کن.

        $container.find('input.nader-color-picker').not('.wp-color-picker').each(function() {
            const $input = $(this);
            $input.wpColorPicker();
        });
    }

    // فعال کردن همگام‌سازی Range Slider و Number Input
    // این تابع حالا یک پارامتر $container می‌گیرد.
    function initializeRangeSliders($container) {
        // از کلاس initialized برای جلوگیری از اولیه سازی مجدد استفاده می‌کنیم.
        $container.find('.nader-range-slider-field').not('.initialized').each(function() {
            const $container = $(this); // این $container با پارامتر تابع فرق دارد، این کانتینر فیلد است.
            const $rangeInput = $container.find('.nader-range-input');
            const $numberInput = $container.find('.nader-number-input');

            // حذف event handler های قبلی قبل از اتصال جدید برای جلوگیری از اجرای چندباره
            $rangeInput.off('input change').on('input change', function() { $numberInput.val($(this).val()); });
            $numberInput.off('input change').on('input change', function() {
                let val = parseFloat($(this).val());
                const min = parseFloat($rangeInput.attr('min'));
                const max = parseFloat($rangeInput.attr('max'));

                if (!isNaN(val)) {
                    val = Math.max(min, Math.min(max, val));
                } else { val = parseFloat($rangeInput.attr('min')); }

                $rangeInput.val(val);
                $(this).val(val);
            });
            $container.addClass('initialized'); // اضافه کردن کلاس برای نشان دادن اولیه سازی
        });
    }

    // فعال کردن Select2 برای ماژول Choose
    // این تابع حالا یک پارامتر $container می‌گیرد.
    function initializeChooseFields($container) {
        // اطمینان از وجود تابع select2 قبل از فراخوانی آن
        if (typeof $.fn.select2 === 'undefined') {
            // گزارش کلی در initializeNaderSettingsModules انجام می‌شود.
            return;
        }

        // در کانتینر مشخص شده (یا کل صفحه)، selectهایی با کلاس مخصوص را پیدا کن و Select2 را فعال کن.
        $container.find('select.nader-choose-select').not('.select2-hidden-accessible').each(function() {
            const $select = $(this);
            const queryArgs = $select.data('query-args');

            $select.select2({
                ajax: {
                    url: naderSettings.ajaxurl, dataType: 'json', delay: 250,
                    data: function (params) { return { term: params.term, action: 'nader_choose_search', nonce: naderSettings.nonce, query_args: JSON.stringify(queryArgs), }; },
                    processResults: function (data) { return data; }, cache: true
                },
                placeholder: $select.data('placeholder') || 'برای جستجو تایپ کنید...',
                allowClear: !($select.prop('required')),
                dir: "rtl"
            });
        });
    }

    // فعال کردن منطق Image Select
    // این تابع حالا یک پارامتر $container می‌گیرد.
    function initializeImageSelectFields($container) {
        // استفاده از Event Delegation روی کانتینر مشخص شده
        // .off() قبل از .on() برای جلوگیری از اتصال چندباره event handler
        $container.off('click', '.nader-image-select-list .image-option').on('click', '.nader-image-select-list .image-option', function() {
            const $option = $(this);
            const $fieldContainer = $option.closest('.nader-image-select-field');
            const $hiddenInput = $fieldContainer.find('.nader-image-select-input');
            const isMultiple = $fieldContainer.data('multiple') === true;
            const optionValue = $option.data('value');

            if (isMultiple) {
                $option.toggleClass('selected');
                let selectedValues = [];
                $fieldContainer.find('.nader-image-select-list .image-option.selected').each(function() {
                    selectedValues.push($(this).data('value'));
                });
                $hiddenInput.val(selectedValues.join(','));
            } else {
                $fieldContainer.find('.nader-image-select-list .image-option').removeClass('selected');
                $option.addClass('selected');
                $hiddenInput.val(optionValue);
            }
        });
    }

    // فعال کردن WP Editor در آیتم های جدید یا کانتینر مشخص شده
    // این تابع نیاز به تابع wp.editor.init() وردپرس دارد.
    function initializeWpEditors($container) {
        // اطمینان از وجود شیء wp و تابع wp.editor.init
        if (typeof wp === 'undefined' || typeof wp.editor === 'undefined' || typeof wp.editor.init !== 'function') {
            // گزارش کلی در initializeNaderSettingsModules انجام می‌شود.
            return;
        }

        // پیدا کردن تمام textarea هایی با کلاس WP Editor که هنوز توسط TinyMCE اولیه نشده‌اند
        $container.find('textarea.nader-wp-editor-input').each(function() {
            const $textarea = $(this);
            const editorId = $textarea.attr('id');

            // چک کنید که این ویرایشگر قبلا توسط TinyMCE یا Quicktags اولیه نشده باشد
            // (این چک ممکن است نیاز به روش دقیق تری داشته باشد بسته به نحوه کار wp.editor.init)
            // یک راه ساده: چک کنید کلاس tinymce یا quicktags-editor روی textarea یا wrapper آن نباشد.
            const $wrapper = $textarea.closest('.wp-editor-wrap');
            if (!$wrapper.hasClass('tinymce-editor') && !$wrapper.hasClass('html-active')) {
                // اولیه سازی ویرایشگر با ID آن
                // این تابع ممکن است نیاز به بارگذاری فایل‌های JS اضافی به صورت پویا داشته باشد.
                // در حال حاضر، فرض می‌کنیم فایل‌های اصلی TinyMCE/Quicktags قبلا با wp_enqueue_script صف‌بندی شده‌اند.
                wp.editor.init(editorId);
            }
        });
    }


    // --- جدید: منطق فعال کردن ماژول Repeater ---

    // تابع برای به‌روزرسانی ایندکس‌ها در نام فیلدها پس از تغییر (افزودن، حذف، جابجایی)
    function updateRepeaterItemIndices($repeaterList) {
        $repeaterList.find('> li.nader-repeater-item').each(function(itemIndex) {
            const $item = $(this);
            // به‌روزرسانی ویژگی داده ایندکس آیتم
            $item.data('item-index', itemIndex);

            // پیدا کردن تمام فیلدهای ورودی (input, select, textarea), کانتینرهای خطا و label ها در این آیتم
            $item.find(':input, .nader-errors, label').each(function() {
                const $el = $(this);
                const currentId = $el.attr('id');
                const currentName = $el.attr('name'); // فقط برای input/select/textarea
                const currentFor = $el.attr('for'); // فقط برای label
                const elementTag = $el.prop('tagName').toLowerCase();

                // به‌روزرسانی ID: جایگزینی __INDEX__ با ایندکس جدید
                if (currentId && currentId.indexOf('__INDEX__') !== -1) {
                    $el.attr('id', currentId.replace(/__INDEX__/g, itemIndex)); // استفاده از g برای جایگزینی همه موارد
                } else if (currentId) {
                    // اگر از قبل ایندکس عددی داشت، آن را با ایندکس جدید جایگزین کن.
                    const newId = currentId.replace(/\[\d+\]/, '[' + itemIndex + ']');
                    if (newId !== currentId) { // فقط اگر جایگزینی انجام شد
                        $el.attr('id', newId);
                    }
                }


                // به‌روزرسانی Name (برای input, select, textarea)
                if (currentName && currentName.indexOf('__INDEX__') !== -1) {
                    $el.attr('name', currentName.replace(/__INDEX__/g, itemIndex)); // استفاده از g برای جایگزینی همه موارد
                } else if (currentName) {
                    // اگر از قبل ایندکس عددی داشت، آن را با ایندکس جدید جایگزین کن.
                    const newName = currentName.replace(/\[\d+\]/, '[' + itemIndex + ']');
                    if (newName !== currentName) { // فقط اگر جایگزینی انجام شد
                        $el.attr('name', newName);
                    }
                }

                // به‌روزرسانی For (برای label)
                if (currentFor && currentFor.indexOf('__INDEX__') !== -1 && elementTag === 'label') {
                    $el.attr('for', currentFor.replace(/__INDEX__/g, itemIndex)); // استفاده از g
                } else if (currentFor && elementTag === 'label') {
                    // اگر از قبل ایندکس عددی در for داشت، آن را با ایندکس جدید جایگزین کن.
                    const newFor = currentFor.replace(/\[\d+\]/, '[' + itemIndex + ']');
                    if (newFor !== currentFor) { // فقط اگر جایگزینی انجام شد
                        $el.attr('for', newFor);
                    }
                }

            });

            // به‌روزرسانی عنوان آیتم (اختیاری)
            $item.find('.item-title').text('آیتم #' + (itemIndex + 1));

            // اگر ماژول خاصی نیاز به Initializing مجدد پس از کلون/جابجایی دارد، اینجا صدا زده شود.
            // این تابع InitializeNaderSettingsModules را با این آیتم فراخوانی می‌کند
            // (initializeNaderSettingsModules خودش توابع اولیه سازی ماژول‌های خاص را صدا می‌زند).
            // این فراخوانی در initializeRepeaters در زمان افزودن آیتم جدید انجام می‌شود.

        });

        // نمایش/پنهان کردن دکمه افزودن آیتم بر اساس max_items
        const $repeaterField = $repeaterList.closest('.nader-repeater-field');
        const maxItems = parseInt($repeaterField.data('max-items'), 10);
        const currentItemCount = $repeaterList.find('> li.nader-repeater-item').length;
        const $addButton = $repeaterField.find('.nader-repeater-add-item');

        if (maxItems > 0 && currentItemCount >= maxItems) {
            $addButton.hide();
        } else {
            $addButton.show();
        }

        // نمایش/پنهان کردن دکمه حذف آیتم بر اساس min_items
        const minItems = parseInt($repeaterField.data('min-items'), 10);
        $repeaterList.find('> li.nader-repeater-item .item-remove').each(function() {
            // دکمه حذف را فقط زمانی نمایش بده که تعداد آیتم‌ها بیشتر از حداقل است.
            if (minItems > 0 && currentItemCount <= minItems) {
                $(this).hide();
            } else {
                $(this).show();
            }
        });
    }


    // تابع اولیه سازی Repeater
    function initializeRepeaters() {
        $('.nader-repeater-field').each(function() {
            const $repeaterField = $(this);
            const $repeaterList = $repeaterField.find('.nader-repeater-items-list');
            const $addButton = $repeaterField.find('.nader-repeater-add-item');
            // تمپلیت آیتم، کلاس template روی li است.
            const $templateItem = $repeaterField.find('.nader-repeater-item-template > li.nader-repeater-item.template');

            // --- مدیریت دکمه افزودن آیتم ---
            $addButton.on('click', function() {
                const newItemIndex = $repeaterList.find('> li.nader-repeater-item').length; // ایندکس آیتم جدید
                const $newItem = $templateItem.clone(); // کلون کردن تمپلیت آیتم

                // حذف کلاس template از آیتم جدید کلون شده
                $newItem.removeClass('template');

                // به‌روزرسانی ایندکس‌ها و اولیه سازی زیرفیلدها در آیتم جدید
                // در این بخش، فقط آیتم جدید را به‌روزرسانی و اولیه سازی می‌کنیم.
                // updateRepeaterItemIndices در پایان افزودن/حذف/جابجایی کلی صدا زده می‌شود.

                // به‌روزرسانی ID و Name و For در آیتم جدید کلون شده
                $newItem.find(':input, .nader-errors, label').each(function() {
                    const $el = $(this);
                    const currentId = $el.attr('id');
                    const currentName = $el.attr('name');
                    const currentFor = $el.attr('for');
                    const elementTag = $el.prop('tagName').toLowerCase();

                    if (currentId && currentId.indexOf('__INDEX__') !== -1) {
                        $el.attr('id', currentId.replace(/__INDEX__/g, newItemIndex));
                    }
                    if (currentName && currentName.indexOf('__INDEX__') !== -1) {
                        $el.attr('name', currentName.replace(/__INDEX__/g, newItemIndex));
                    }
                    if (currentFor && currentFor.indexOf('__INDEX__') !== -1 && elementTag === 'label') {
                        $el.attr('for', currentFor.replace(/__INDEX__/g, newItemIndex));
                    }
                    // پاک کردن مقادیر پیش‌فرض در فیلدهای آیتم جدید
                    if ($el.is('input[type="text"], input[type="number"], input[type="email"], textarea')) {
                        $el.val('');
                    } else if ($el.is('input[type="checkbox"], input[type="radio"]')) {
                        $el.prop('checked', false);
                    } else if ($el.is('select')) {
                        // Select2 نیاز به اولیه سازی دارد، مقدار خالی پیش فرض آن است.
                        // $el.val(''); // مقدار Select2 را خالی کن.
                    } else if ($el.is('.nader-image-id-input, .nader-gallery-ids-input')) {
                        $el.val('');
                        $el.closest('.nader-image-upload-field, .nader-gallery-field').find('.nader-image-preview, .nader-gallery-preview').empty();
                        $el.closest('.nader-image-upload-field').find('.nader-remove-image-button').hide();
                        $el.closest('.nader-gallery-field').find('.nader-clear-gallery-button').hide();
                    } else if ($el.is('.nader-color-picker')) {
                        $el.val('');
                        // Color Picker نیاز به اولیه سازی دارد.
                    } else if ($el.is('.nader-image-select-input')) {
                        $el.val('');
                        $el.closest('.nader-image-select-field').find('.image-option.selected').removeClass('selected');
                    }
                    // WP Editor نیاز به initializition مجدد دارد.
                });

                // به‌روزرسانی ویژگی داده ایندکس آیتم جدید
                $newItem.data('item-index', newItemIndex);
                // به‌روزرسانی عنوان آیتم جدید
                $newItem.find('.item-title').text('آیتم #' + (newItemIndex + 1));


                // اضافه کردن آیتم جدید به لیست
                $repeaterList.append($newItem);

                // اولیه سازی ماژول‌های سمت کلاینت در آیتم جدید
                initializeNaderSettingsModules($newItem); // فراخوانی تابع کلی اولیه سازی برای آیتم جدید

                // به‌روزرسانی ایندکس‌ها در تمام آیتم‌ها پس از افزودن (این شامل آیتم جدید هم می‌شود)
                updateRepeaterItemIndices($repeaterList);

            });


            // --- مدیریت دکمه حذف آیتم ---
            // استفاده از Event Delegation روی لیست اصلی Repeater
            $repeaterList.on('click', '.item-remove', function() {
                const $itemToRemove = $(this).closest('.nader-repeater-item');
                const currentItemCount = $repeaterList.find('> li.nader-repeater-item').length;
                const minItems = parseInt($repeaterField.data('min-items'), 10);

                if (minItems > 0 && currentItemCount <= minItems) {
                    alert('نمی‌توانید کمتر از ' + minItems + ' آیتم داشته باشید.');
                    return;
                }

                // قبل از حذف، اگر WP Editor در این آیتم وجود دارد، آن را destroy کن.
                $itemToRemove.find('textarea.nader-wp-editor-input').each(function() {
                    const editorId = $(this).attr('id');
                    if (typeof tinymce !== 'undefined' && tinymce.get(editorId)) {
                        tinymce.get(editorId).remove(); // حذف نمونه TinyMCE
                    }
                    // برای Quicktags نیاز به destroy خاصی نیست.
                });


                $itemToRemove.remove();

                // به‌روزرسانی ایندکس‌ها در تمام آیتم‌های باقی‌مانده
                updateRepeaterItemIndices($repeaterList);

            });

            // --- مدیریت جابجایی (SortableJS) ---
            if (typeof Sortable === 'undefined') {
                console.warn('Nader Settings JS: کتابخانه SortableJS در دسترس نیست. قابلیت جابجایی فعال نخواهد شد.');
            } else {
                Sortable.create($repeaterList[0], { // [0] برای گرفتن عنصر DOM اصلی از آبجکت jQuery
                    handle: '.item-handle', // عنصر دستگیره برای کشیدن
                    animation: 150, // سرعت انیمیشن جابجایی
                    onUpdate: function (evt) {
                        // هنگامی که ترتیب آیتم‌ها تغییر می‌کند، ایندکس‌ها را به‌روز کن.
                        updateRepeaterItemIndices($repeaterList);
                    },
                    // اختیاری: callback هنگام شروع کشیدن (برای افزودن کلاس visual)
                    // onStart: function (evt) { $(evt.item).addClass('is-dragging'); },
                    // اختیاری: callback هنگام پایان کشیدن (برای حذف کلاس visual)
                    // onEnd: function (evt) { $(evt.item).removeClass('is-dragging'); },
                });
            }

            // اولیه سازی اولیه ایندکس‌ها و دکمه‌ها هنگام بارگذاری صفحه
            updateRepeaterItemIndices($repeaterList);

        });
    }


    // --- اولیه سازی تمام ماژول‌های سمت کلاینت هنگام بارگذاری صفحه یا در یک کانتینر خاص ---
    // این تابع حالا یک پارامتر اختیاری $container می‌گیرد تا بتواند فقط ماژول‌های داخل یک کانتینر خاص را اولیه سازی کند (مثلاً آیتم جدید Repeater).
    function initializeNaderSettingsModules($container = null) {
        // اگر کانتینر null بود، در کل صفحه جستجو کن. از $container به عنوان یک شیء jQuery استفاده می‌کنیم.
        const $searchContext = ($container instanceof $) ? $container : $('.nader-settings-wrap');

        // گزارش برای عیب یابی
        // console.log('Initializing modules within:', $searchContext);


        // اولیه سازی Color Pickers
        // بررسی وجود تابع wpColorPicker قبل از فراخوانی تابع initializeColorPickers
        if (typeof $.fn.wpColorPicker === 'undefined') {
            console.warn('Nader Settings JS: تابع wpColorPicker در دسترس نیست. ماژول Color کار نخواهد کرد.');
        } else {
            initializeColorPickers($searchContext);
        }


        // اولیه سازی Range Sliders
        initializeRangeSliders($searchContext);


        // فعال کردن Select2 برای ماژول Choose
        // اطمینان از وجود تابع select2 قبل از فراخوانی تابع initializeChooseFields
        if (typeof $.fn.select2 === 'undefined') {
            console.warn('Nader Settings JS: تابع select2 در دسترس نیست. ماژول Choose کار نخواهد کرد.');
        } else {
            initializeChooseFields($searchContext);
        }


        // فعال کردن منطق Image Select
        initializeImageSelectFields($searchContext);


        // فعال کردن WP Editor در کانتینر مشخص شده
        // این نیاز به initialize مجدد برای آیتم‌های جدید Repeater دارد.
        // باید wp.editor.init() را برای textarea های جدید اجرا کنیم.
        if (typeof wp === 'undefined' || typeof wp.editor === 'undefined' || typeof wp.editor.init !== 'function') {
            console.warn('Nader Settings JS: WP Editor API (wp.editor.init) در دسترس نیست. ماژول WP Editor در آیتم‌های جدید Repeater به درستی اولیه سازی نخواهد شد.');
        } else {
            initializeWpEditors($searchContext); // اولیه سازی WP Editors در کانتینر فعلی
        }


        // فعال کردن Repeater (فراخوانی اولیه سازی اصلی)
        // این تابع نباید در هر بار initializeNaderSettingsModules اجرا شود، فقط یک بار برای کل صفحه.
        // بنابراین، آن را خارج از این تابع صدا می‌زنیم، یا چک می‌کنیم که کانتینر کل صفحه باشد.
        // منطق اولیه سازی repeater ها در تابع initializeRepeaters قرار داده شده است.

        // اگر $container null بود، یعنی اولین بار برای کل صفحه صدا زده شده و initializeRepeaters را صدا بزن.
        // اگر $container یک شیء jQuery بود، یعنی برای یک آیتم Repeater صدا زده شده و نیازی به صدا زدن مجدد initializeRepeaters نیست.
        if ($container === null) {
            initializeRepeaters(); // فقط یک بار برای کل صفحه
        }
    } // پایان initializeNaderSettingsModules


    // --- فراخوانی اولیه سازی هنگام بارگذاری صفحه ---
    // اطمینان از اینکه jQuery و DOM آماده هستند.
    initializeNaderSettingsModules(null); // null برای اولیه سازی در کل صفحه


}); // پایان document ready
