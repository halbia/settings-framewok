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

    function initializeRepeaterFields($container) {
        $container.find('.nader-repeater-field').each(function() {
            const $repeater = $(this);
            const baseName = $repeater.data('name');
            const fieldsConfig = $repeater.data('fields');
            const $itemsContainer = $repeater.find('.repeater-items');
            const $addButton = $repeater.find('.add-repeater-item');
            const minItems = parseInt($repeater.data('min-items')) || 0;
            const maxItems = parseInt($repeater.data('max-items')) || 0;

            // افزودن آیتم جدید
            $addButton.off('click').on('click', function() {
                if(maxItems > 0 && $itemsContainer.children().length >= maxItems) return;

                const newIndex = $itemsContainer.children().length;
                const $newItem = createRepeaterItem(baseName, newIndex, fieldsConfig);
                $itemsContainer.append($newItem);
                initializeNaderSettingsModules($newItem);
                updateUI();
            });

            // مدیریت حذف آیتم
            $itemsContainer.off('click', '.remove-item').on('click', '.remove-item', function() {
                if($itemsContainer.children().length <= minItems) return;

                $(this).closest('.repeater-item').remove();
                reindexItems();
                updateUI();
            });

            // جابجایی آیتم‌ها
            $itemsContainer.off('click', '.move-up').on('click', '.move-up', function() {
                const $item = $(this).closest('.repeater-item');
                $item.insertBefore($item.prev());
                reindexItems();
            });

            $itemsContainer.off('click', '.move-down').on('click', '.move-down', function() {
                const $item = $(this).closest('.repeater-item');
                $item.insertAfter($item.next());
                reindexItems();
            });

            // ایجاد آیتم جدید
            function createRepeaterItem(baseName, index, fields) {
                const $item = $(`
                <div class="repeater-item" data-index="${index}">
                    <div class="item-header">
                        <span class="item-title">آیتم ${index + 1}</span>
                        <div class="item-actions">
                            <button type="button" class="move-up">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M13.0001 22.0003L11.0002 22.0004L11.0002 5.82845L7.05044 9.77817L5.63623 8.36396L12.0002 2L18.3642 8.36396L16.9499 9.77817L13.0002 5.8284L13.0001 22.0003Z"></path></svg>
                            </button>
                            <button type="button" class="move-down">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M13.0001 1.99974L11.0002 1.9996L11.0002 18.1715L7.05044 14.2218L5.63623 15.636L12.0002 22L18.3642 15.636L16.9499 14.2218L13.0002 18.1716L13.0001 1.99974Z"></path></svg>
                            </button>
                            <button type="button" class="toggle-item">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M11.9995 13.4995 16.9492 18.4493 15.535 19.8635 12.9995 17.3279 12.9995 22.9995H10.9995L10.9995 17.3279 8.46643 19.861 7.05222 18.4468 11.9995 13.4995ZM10.9995.999512 10.9995 6.67035 8.46448 4.13535 7.05026 5.54956 12 10.4995 16.9497 5.54977 15.5355 4.13555 12.9995 6.67157V.999512L10.9995.999512Z"></path></svg>
                            </button>
                            <button type="button" class="remove-item">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M11.9997 10.5865L16.9495 5.63672L18.3637 7.05093L13.4139 12.0007L18.3637 16.9504L16.9495 18.3646L11.9997 13.4149L7.04996 18.3646L5.63574 16.9504L10.5855 12.0007L5.63574 7.05093L7.04996 5.63672L11.9997 10.5865Z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="item-content"></div>
                </div>
            `);

                fields.forEach(field => {
                    const $field = createField(baseName, index, field);
                    $item.find('.item-content').append($field);
                });

                return $item;
            }

            // ایجاد فیلدها
            function createField(baseName, index, fieldConfig) {
                const fieldName = `${baseName}[${index}][${fieldConfig.name}]`;
                let $field;

                switch(fieldConfig.type) {
                    case 'text':
                        $field = $(`<input type="text" name="${fieldName}" class="nader-text-input">`);
                        break;

                    case 'textarea':
                        $field = $(`<textarea name="${fieldName}" rows="3" class="nader-textarea-input"></textarea>`);
                        break;

                    case 'checkbox':
                        $field = $(`
                        <label class="nader-checkbox-wrapper">
                            <input type="checkbox" name="${fieldName}" value="1" class="nader-checkbox-input">
                            <span class="checkmark"></span>
                            ${fieldConfig.label || ''}
                        </label>
                    `);
                        break;

                    case 'radio':
                        $field = $(`<div class="nader-radio-group"></div>`);
                        fieldConfig.options.forEach(option => {
                            $field.append(`
                            <label class="nader-radio-option">
                                <input type="radio" name="${fieldName}" value="${option.value}">
                                ${option.label}
                            </label>
                        `);
                        });
                        break;

                    case 'select':
                        $field = $(`<select name="${fieldName}" class="nader-select-input"></select>`);
                        fieldConfig.options.forEach(option => {
                            $field.append(`<option value="${option.value}">${option.label}</option>`);
                        });
                        break;

                    case 'color':
                        $field = $(`
                        <input type="text" 
                            class="nader-color-picker"
                            name="${fieldName}"
                            data-default-color="${fieldConfig.default || '#ffffff'}">
                    `);
                        break;

                    case 'image':
                        $field = $(`
                        <div class="nader-image-upload-field">
                            <input type="hidden" class="nader-image-id-input" name="${fieldName}">
                            <button type="button" class="button nader-select-image-button">
                                ${fieldConfig.button_text || 'انتخاب تصویر'}
                            </button>
                            <div class="nader-image-preview"></div>
                        </div>
                    `);
                        break;

                    case 'gallery':
                        $field = $(`
                        <div class="nader-gallery-field">
                            <input type="hidden" class="nader-gallery-ids-input" name="${fieldName}">
                            <button type="button" class="button nader-select-gallery-button">
                                ${fieldConfig.button_text || 'انتخاب گالری'}
                            </button>
                            <div class="nader-gallery-preview"></div>
                        </div>
                    `);
                        break;

                    case 'range_slider':
                        $field = $(`
                        <div class="nader-range-slider-field">
                            <input type="range" 
                                class="nader-range-input"
                                name="${fieldName}"
                                min="${fieldConfig.min || 0}"
                                max="${fieldConfig.max || 100}"
                                step="${fieldConfig.step || 1}"
                                value="${fieldConfig.default || 50}">
                            <input type="number" 
                                class="nader-number-input"
                                value="${fieldConfig.default || 50}">
                            <span class="unit">${fieldConfig.unit || ''}</span>
                        </div>
                    `);
                        break;

                    case 'toggle':
                        $field = $(`
                        <label class="nader-toggle-switch">
                            <input type="checkbox" name="${fieldName}" class="nader-toggle-input">
                            <span class="slider round"></span>
                        </label>
                    `);
                        break;

                    case 'wp_editor':
                        $field = $(`
                        <div class="nader-wp-editor-wrapper">
                            <textarea name="${fieldName}" class="nader-wp-editor-input"></textarea>
                        </div>
                    `);
                        break;

                    default:
                        $field = $(`<p>نوع فیلد نامعتبر: ${fieldConfig.type}</p>`);
                }

                return $(`
                <div class="nader-field-wrapper" data-type="${fieldConfig.type}">
                    <label class="nader-field-label">${fieldConfig.title}</label>
                    <div class="nader-field-content">${$field.prop('outerHTML')}</div>
                    <ul class="nader-errors"></ul>
                </div>
            `);
            }

            // به‌روزرسانی شماره آیتم‌ها
            function reindexItems() {
                $itemsContainer.children().each(function(index) {
                    $(this)
                        .attr('data-index', index)
                        .find('.item-title').text(`آیتم ${index + 1}`);
                });
            }

            // به‌روزرسانی وضعیت UI
            function updateUI() {
                const itemCount = $itemsContainer.children().length;
                $addButton.toggle(!maxItems || itemCount < maxItems);
                $itemsContainer.find('.remove-item').prop('disabled', itemCount <= minItems);
            }

            // مقداردهی اولیه
            updateUI();
        });
    }

    // مدیریت رویداد toggle برای آیتم‌های موجود و جدید
    $(document).on('click', '.toggle-item', function() {
        const $button = $(this);
        const $content = $button.closest('.repeater-item').find('.item-content');

        // نمایش/مخفی کردن محتوا با انیمیشن
        $content.slideToggle(300, function() {
            $button.html($content.is(':visible') ? '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M11.9995 13.4995 16.9492 18.4493 15.535 19.8635 12.9995 17.3279 12.9995 22.9995H10.9995L10.9995 17.3279 8.46643 19.861 7.05222 18.4468 11.9995 13.4995ZM10.9995.999512 10.9995 6.67035 8.46448 4.13535 7.05026 5.54956 12 10.4995 16.9497 5.54977 15.5355 4.13555 12.9995 6.67157V.999512L10.9995.999512Z"></path></svg>' : '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M11.9995 0.499512L16.9492 5.44926L15.535 6.86347L12.9995 4.32794V9.99951H10.9995L10.9995 4.32794L8.46643 6.86099L7.05222 5.44678L11.9995 0.499512ZM10.9995 13.9995L10.9995 19.6704L8.46448 17.1353L7.05026 18.5496L12 23.4995L16.9497 18.5498L15.5355 17.1356L12.9995 19.6716V13.9995H10.9995Z"></path></svg>');
        });
    });

    function initializeNaderSettingsModules($container) {

        if (!$container || $container.length === 0) {
            $container = ($container instanceof $) ? $container : $('.nader-settings-wrap');
        }

        initializeColorPickers($container)

        initializeChooseFields($container)

        initializeRepeaterFields($container)

        initializeWpEditors($container)

        initializeImageSelectFields($container)

        initializeRangeSliders($container)

    }

    initializeNaderSettingsModules(null); // null برای اولیه سازی در کل صفحه



    // انتخاب عناصر مورد نیاز
    const $tabLinks = $('.nader-tabs-nav a');
    const $tabPanes = $('.nader-tab-pane');

    // مدیریت کلیک روی تب‌ها
    $tabLinks.on('click', function(e) {
        e.preventDefault();
        const tabId = $(this).data('tab');

        // حذف کلاس active از همه عناصر
        $tabLinks.removeClass('active');
        $tabPanes.removeClass('active');

        // افزودن کلاس active به عناصر مرتبط
        $(this).addClass('active');
        $(`#tab-${tabId}`).addClass('active');

        // به روزرسانی URL بدون رفرش
        history.pushState(null, null, `?page=nader-settings&tab=${tabId}`);
    });

    // مدیریت تغییرات در تاریخچه مرورگر
    $(window).on('popstate', function() {
        const params = new URLSearchParams(window.location.search);
        const tabId = params.get('tab');

        if (tabId) {
            const $targetTab = $(`.nader-tabs-nav a[data-tab="${tabId}"]`);
            if ($targetTab.length) {
                $tabLinks.removeClass('active');
                $tabPanes.removeClass('active');
                $targetTab.addClass('active');
                $(`#tab-${tabId}`).addClass('active');
            }
        }
    });

    // فعال سازی تب اولیه بر اساس URL
    const initialTab = new URLSearchParams(window.location.search).get('tab');
    if (initialTab) {
        $(`.nader-tabs-nav a[data-tab="${initialTab}"]`).trigger('click');
    } else {
        $tabLinks.first().trigger('click');
    }

}); // پایان document ready
