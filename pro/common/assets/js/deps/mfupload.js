(function ($) {
    let mfUploadInputs = $('.mf-upload'),
        mfUploadFiles = {},
        mfUploadFilesInProgress = {},
        mfUploadAjaxInProgress = {};
    $.fn.mfUpload = function (e, files) {

        let fieldContainer = $(this).closest('.mf_input_file'),
            pendingFilesContainer = $(fieldContainer).siblings('.mf_files_pending'),
            completedFilesContainer = $(fieldContainer).siblings('.mf_files_completed'),
            form = $(fieldContainer).closest('form'),
            formId = $(form).find('[name="_mf_form_id"]').val(),
            self = {
                settings: {
                    multipleFiles: false,
                    maxCount: 0,
                    maxSize: 0,
                    supportedTypes: "",
                },
                errors: [],
                pendingFiles: [],
                pendingFile: null,
                completedFiles: [],
                open: () => {
                    // Prepare data
                    if ($(this).attr('multiple')) {
                        self.settings.multipleFiles = true;
                        self.settings.maxCount = $(this).attr('data-max-count') || 0;
                    }
                    self.settings.maxSize = $(this).attr('data-max-size') || 0;
                    self.settings.supportedTypes = $(this).attr('data-types') || $(this).attr('accept');
                    self.settings.supportedTypes = self.settings.supportedTypes.startsWith('{') ? JSON.parse(self.settings.supportedTypes) : self.settings.supportedTypes;

                    // Make sure storage variables holds a record for the current form (allow handling multiple forms on same page)
                    if (!(formId in mfUploadFiles)) {
                        mfUploadFiles[formId] = [];
                    }
                    if (!(formId in mfUploadFilesInProgress)) {
                        mfUploadFilesInProgress[formId] = [];
                    }
                    if (!(formId in mfUploadAjaxInProgress)) {
                        mfUploadAjaxInProgress[formId] = [];
                    }
                    // Remove any existing errors
                    $(fieldContainer).find('.mf_notice').remove();
                    // Validation
                    if (self.settings.multipleFiles) {
                        self.validateMultiple();
                    } else {
                        self.validateFile(files[0]);
                    }

                },
                validateMultiple: () => {
                    let count = mfUploadFiles[formId].filter(value => value !== null).length + $(completedFilesContainer).find('span').length;
                    for (let i = 0; i < files.length; i++) {

                        if (count + self.pendingFiles.length < parseInt(self.settings.maxCount) || parseInt(self.settings.maxCount) === 0) {
                            self.validateFile(files[i]);
                        } else {
                            self.errors.push(mfield_file_vars.max_reached);
                            return false;
                        }
                    }
                },
                validateFile: (file) => {
                    let typeValid = false,
                        sizeValid = false,
                        fileName = file.name,
                        fileType = file.type,
                        fileSize = file.size,
                        fileMax = parseInt(self.settings.maxSize),
                        fileExt = fileName.split('.').pop().toLowerCase(),
                        error = '';

                    // Validate type
                    if (typeof self.settings.supportedTypes == "object") {
                        if (Object.values(self.settings.supportedTypes).includes(fileType) !== false) {
                            typeValid = true;
                        } else {
                            if (Object.keys(self.settings.supportedTypes).filter(element => element.includes(fileExt)).length > 0) {
                                typeValid = true;
                            }
                        }
                    } else {
                        typeValid = self.settings.supportedTypes.includes('.' + fileExt);
                    }

                    // Validate Size
                    if (fileMax > 0 && fileMax > fileSize) {
                        sizeValid = true;
                    }

                    if (!typeValid && !sizeValid) {
                        error = mfield_file_vars.invalid_size_and_type;
                    } else if (!typeValid) {
                        if (typeof self.settings.supportedTypes == "object") {
                            error = mfield_file_vars.illegal_extension;
                        } else {
                            error = mfield_file_vars.invalid_file_extension + self.settings.supportedTypes;
                        }

                    } else if (!sizeValid) {
                        error = mfield_file_vars.file_exceeds_limit;
                    }

                    if (error !== '') {
                        self.errors.push(fileName + ' - ' + error);
                    } else {
                        if (self.settings.multipleFiles) {
                            self.pendingFiles.push(file);
                        } else {
                            self.pendingFile = file;
                        }
                    }
                },
                generateErrorsHTML: (omit_notice_holder = false) => {

                    let html = "";
                    if (self.errors.length > 0) {
                        if (!omit_notice_holder) {
                            html += '<span class="mf-notice-holder mf_notice">';
                        }
                        for (let i = 0; i < self.errors.length; i++) {
                            html += '<bdi>';
                            html += self.errors[i];
                            html += '</bdi>';
                        }
                        if (!omit_notice_holder) {
                            html += '<span>';
                        }
                    }
                    return html;
                },
                generateCompletedFileHTML: (id) => {

                    let html = "",
                        baseId = $(fieldContainer).find('input[type="file"]').attr('name');

                    if (typeof mfUploadFiles[formId][id] !== "undefined") {

                        let size = self.completedFiles[id].size / 1024;
                        size = parseFloat(size).toFixed(2);

                        html += '<span class="completed" data-id="' + self.completedFiles[id].hash + '">';
                        html += '<i class="mf-delete-file mega-icons-clear"></i>';
                        html += '<strong>' + self.completedFiles[id].name + ' </strong>';
                        html += size > 1024 ? ' (' + parseFloat(size / 1024).toFixed(2) + ' mb)' : ' (' + size + ' kb)';
                        html += '<input type="hidden" name="' + baseId + '[files][' + self.completedFiles[id].hash + '][name]" value="' + self.completedFiles[id].name + '">';
                        html += '<input type="hidden" name="' + baseId + '[files][' + self.completedFiles[id].hash + '][size]" value="' + self.completedFiles[id].size + '">';

                        html += '</span>';
                    }

                    return html;
                },
                generatePendingFileHTML: (id, showProgress = false) => {

                    let html = "";

                    if (typeof mfUploadFiles[formId][id] !== "undefined" && mfUploadFiles[formId] !== null) {

                        let size = mfUploadFiles[formId][id].size / 1024;
                        size = size > 1 ? parseInt(size) : parseFloat(size).toFixed(2);

                        html += '<span data-id="' + id + '">';
                        html += mfUploadFiles[formId][id].name;
                        html += ' (' + size + ' kb)';
                        if (showProgress) {
                            html += ' <bdi class="mf_progress">0%</bdi>';
                            html += ' <a href="#" class="mf-cancel-file"><strong>' + mfield_file_vars.cancel + '</strong></a>';
                        }
                        html += '</span>';
                    }

                    return html;
                },
                uploadFiles: (ids) => {

                    let field_id = $(fieldContainer).closest('.mfield').attr('data-id'),
                        mf_referrer = $(form).find('[name="_mf_referrer"]').val(),
                        form_nonce = $(form).find('[name="_mf_nonce"]').val(),
                        form_extra_nonce = $(form).find('[name="_mf_extra_nonce"]').val();

                    for (let i = 0; ids.length > i; i++) {
                        let id = ids[i];
                        // Store the file id
                        mfUploadFilesInProgress[formId].push(id);
                        // Add file object to the formData
                        let formData = new FormData();
                        formData.append('type', 'upload');
                        formData.append('file', mfUploadFiles[formId][id]);
                        formData.append('_mf_field_id', field_id);
                        formData.append('_mf_form_id', formId);
                        formData.append('_mf_referrer', mf_referrer);
                        formData.append('_mf_nonce', form_nonce);
                        formData.append('_mf_extra_nonce', form_extra_nonce);
                        formData.append('action', 'megaforms_file_handler');

                        mfUploadAjaxInProgress[formId][id] = jQuery.ajax({
                            type: 'POST',
                            url: megaForms.ajaxurl,
                            data: formData,
                            contentType: false,
                            processData: false,
                            xhr: function () {
                                // var xhr = new window.XMLHttpRequest();
                                var xhr = $.ajaxSettings.xhr();
                                xhr.upload.addEventListener("progress", function (evt) {
                                    if (evt.lengthComputable) {
                                        let percentComplete = (evt.loaded / evt.total) * 100;
                                        self.setProgressPercentage(id, percentComplete - 1);
                                    }
                                }, false);
                                return xhr;
                            },
                            beforeSend: function () {
                                $(pendingFilesContainer).append(self.generatePendingFileHTML(id, true));
                            },
                            success: (response) => {

                                let result = JSON.parse(response);
                                if (result.success) {
                                    self.completedFiles[id] = result.data;
                                    // Display a list of completed files
                                    $(completedFilesContainer).append(self.generateCompletedFileHTML(id));
                                } else {
                                    self.errors.push(mfUploadFiles[formId][id].name + ' - ' + result.message);
                                    self.updateErrors(true);
                                }

                                // Clear the file pointers and elements
                                self.clearPendingFile(id, false);

                                return true;
                            },
                            error: (xhr, textStatus, errorThrown) => {
                                if (textStatus !== 'abort') {
                                    console.log('XHR ERROR ' + xhr.status + ': ' + errorThrown || textStatus);
                                    self.errors.push(mfUploadFiles[formId][id].name + ' - ' + mfield_file_vars.unknown_error);
                                    self.updateErrors(true);
                                    self.clearPendingFile(id, false);
                                } else {
                                    self.clearPendingFile(id, true);
                                }

                                return false;
                            }
                        });

                    }
                },
                clearPendingFile: (id, fade = true) => {
                    let el = $(pendingFilesContainer).find('[data-id="' + id + '"]');
                    // Remove the file element from pending files list
                    if (fade) {
                        $(el).fadeOut(300, function () {
                            $(this).remove();
                        });
                    } else {
                        $(el).remove();
                    }
                    // Unset pointers and records
                    mfUploadFiles[formId][id] = null;
                    mfUploadAjaxInProgress[formId][id] = null;
                    // Since we used ` mfUploadFilesInProgress[formId].push(id)` the index will be different from the ID, that's why we are using `indexOf`
                    mfUploadFilesInProgress[formId][mfUploadFilesInProgress[formId].indexOf(id)] = null;
                },
                setProgressPercentage: (id, percentage) => {
                    let progressEl = $(pendingFilesContainer).find('[data-id="' + id + '"] .mf_progress');
                    if (progressEl.length > 0) {
                        $(progressEl).text(parseInt(percentage) + "%");
                    }
                },
                updateErrors: (append_errors = false) => {
                    // Display errors if available
                    if (self.errors.length > 0) {
                        let mf_notice_holder = $(fieldContainer).find('.mf_notice');
                        if (append_errors && $(mf_notice_holder).length > 0) {
                            $(mf_notice_holder).append(self.generateErrorsHTML(append_errors))
                        } else {
                            if ($(mf_notice_holder).length > 0) {
                                $(fieldContainer).find('.mf_notice').remove();
                            }

                            $(fieldContainer).append(self.generateErrorsHTML());
                        }
                    }
                },
                close: () => {
                    // Reset the file input if necessqry
                    if ((self.settings.multipleFiles && e.currentTarget.files) || (self.settings.multipleFiles === false && self.errors.length > 0)) {
                        e.currentTarget.value = null;
                    }

                    if (self.settings.multipleFiles) {
                        // Upload the files instantly if multiple files are enabled
                        let ids = [];

                        for (let i = 0; i < self.pendingFiles.length; i++) {
                            let newLength = mfUploadFiles[formId].push(self.pendingFiles[i]);
                            ids.push(newLength - 1);
                        }

                        self.uploadFiles(ids);

                        // Update uploads count
                        $(fieldContainer).find('.mf-files-count').text(mfUploadFiles[formId].filter(value => value !== null).length + $(completedFilesContainer).find('span').length);

                    } else {
                        // Store the file object for single uploads ( to be uploaded on submission )
                        let newLength = mfUploadFiles[formId].push(self.pendingFile),
                            alreadyUploadedFile = $(completedFilesContainer).find('span');
                        // Add the file html to the pending files list
                        $(pendingFilesContainer).html(self.generatePendingFileHTML(newLength - 1));
                        // If there is a file uploaded already, add a strikethrough to indicate that it will not be submitted
                        if ($(alreadyUploadedFile).length > 0) {
                            $(alreadyUploadedFile).html(function (index, html) {
                                return html.replace('strong>', 'strike>');
                            });
                            $(alreadyUploadedFile).find('.mf-delete-file').remove();
                        }
                    }

                    // Update HTML ( files list & errors )
                    self.updateErrors();

                },
            }

        self.open();
        self.close();
    };

    $.fn.mfUploadInit = function (e) {

        $(this).on('change', '.mf-upload', function (e) {
            $(this).mfUpload(e, e.currentTarget.files);
        });

        // Handle multi-uploads drop box
        let mfUploadDropable = $('.mf_files_dock');
        if (mfUploadDropable.length > 0) {
            $(this).on('drag dragstart dragend dragover dragenter dragleave drop', '.mf_files_dock', function (e) {
                e.preventDefault(), e.stopPropagation();
            })
            $(this).on('dragover', '.mf_files_dock', function (e) {
                $(this).addClass('mf_files_dock_hover');
            })
            $(this).on('dragleave', '.mf_files_dock', function (e) {
                $(this).removeClass('mf_files_dock_hover');
            })
            // handle files drop
            $(this).on('drop', '.mf_files_dock', function (e) {
                $(this).removeClass('mf_files_dock_hover');
                if (e.originalEvent.dataTransfer) {
                    if (e.originalEvent.dataTransfer.files.length) {
                        $(this).closest('.mf_input_file').find('input[type="file"]').mfUpload(e, e.originalEvent.dataTransfer.files);
                    }
                }
            })
        }

        // Handle file removal
        $(this).on('click', '.mf-delete-file', function (e) {
            e.preventDefault();
            $(this).closest('span').mfUploadDelete(e);
        });

        // Handle file upload cancellation
        $(this).on('click', '.mf-cancel-file', function (e) {
            e.preventDefault();

            let el = $(this),
                id = parseInt($(el).closest('span').attr('data-id')),
                form = $(el).closest('form'),
                formId = parseInt($(form).find('input[name="_mf_form_id"]').val());

            if (typeof formId !== "undefined" & typeof id !== "undefined") {
                if (typeof mfUploadAjaxInProgress[formId][id] !== "undefined") {
                    mfUploadAjaxInProgress[formId][id].abort("abort");
                }
            }
        });

        // Prevent form submit when files are being uploaded
        $(this).on('click', '[type="submit"], .mf-prev-btn', function (e) {
            let pending = Object.values(mfUploadFilesInProgress);
            if (pending.length > 0) {
                for (let i = 0; pending.length > i; i++) {
                    if (pending[i].filter(value => value !== null).length > 0) {
                        e.preventDefault(), e.stopPropagation(), e.stopImmediatePropagation();
                        alert(mfield_file_vars.currently_uploading);
                        return false;
                    }
                }
            }
        });
    };

    $.fn.mfUploadDelete = function (e, fade = true) {

        // Add file object to the formData
        let el = $(this),
            form = $(el).closest('form'),
            hash = $(el).attr('data-id'),
            formId = $(form).find('input[name="_mf_form_id"]').val(),
            mf_referrer = $(form).find('input[name="_mf_referrer"]').val(),
            form_nonce = $(form).find('input[name="_mf_nonce"]').val(),
            form_extra_nonce = $(form).find('input[name="_mf_extra_nonce"]').val();

        let formData = new FormData();
        formData.append('type', 'delete');
        formData.append('hash', hash);
        formData.append('_mf_form_id', formId);
        formData.append('_mf_referrer', mf_referrer);
        formData.append('_mf_nonce', form_nonce);
        formData.append('_mf_extra_nonce', form_extra_nonce);
        formData.append('action', 'megaforms_file_handler');

        jQuery.ajax({
            type: 'POST',
            url: megaForms.ajaxurl,
            data: formData,
            contentType: false,
            processData: false,
            beforeSend: function () {
                let completedContainer = $(el).closest('.mf_files_completed');
                if (fade) {
                    $(el).closest('span').fadeOut(300, function () {
                        $(this).remove();
                        // Refresh uploads count
                        $(completedContainer).siblings('.mf_input_file').find('.mf-files-count').text($(completedContainer).find('span').length);
                    });
                } else {
                    $(el).remove();
                    // Refresh uploads count
                    $(completedContainer).siblings('.mf_input_file').find('.mf-files-count').text($(completedContainer).find('span').length);
                }
            }
        });
    }

    if (mfUploadInputs.length > 0) {
        $(document).mfUploadInit();
    }

}(jQuery));
