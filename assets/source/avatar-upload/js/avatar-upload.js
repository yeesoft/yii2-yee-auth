$(function () {
    $('.avatar-upload form').on('submit', (function (e) {
        e.preventDefault();

        if (typeof window.isAvatarLoading !== 'undefined' && window.isAvatarLoading === true) {
            return;
        }

        window.isAvatarLoading = true;
        var uploader = $(this).parent();
        var action = $(this).attr('action');

        $(uploader).find('.image-submit i').removeClass('fa-save').addClass('fa-spin fa-repeat');
        $(uploader).find('.upload-button').addClass('hidden');

        $.ajax({
            url: action,
            type: 'POST',
            data: new FormData(this),
            dataType: "json",
            contentType: false,
            cache: false,
            processData: false,
            complete: function () {
                $(uploader).find('.image-submit i').removeClass('fa-spin fa-repeat').addClass('fa-save');
                window.isAvatarLoading = false;
            },
            error: function (data) {
                $(uploader).find('.upload-status').html(data.responseText);
                $(uploader).find('.upload-button').removeClass('hidden');
            },
            success: function (data) {
                $(uploader).find('.avatar-preview img').attr('src', data.large);
                $(uploader).find("input[name='image']").val('');
                $(uploader).find('.remove-button').removeClass('hidden');
            }
        });
    }));

    $('.avatar-upload .upload-button').click(function () {
        $('.avatar-upload input[type="file"]').click();
    });

    $('.avatar-upload input[type="file"]').change(function () {
        var uploader = $(this).closest('.avatar-upload');
        $(uploader).find('.upload-status').empty();

        var file = this.files[0];
        var fileType = file.type;
        var match = ["image/jpeg", "image/png", "image/jpg"];

        if ((fileType === match[0]) || (fileType === match[1]) || (fileType === match[2])) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $(uploader).find('.avatar-preview img').attr('src', e.target.result);
            };
            reader.readAsDataURL(this.files[0]);

            $('.avatar-upload form').submit();
        }
    });

    $('.avatar-upload .remove-button').click(function () {
        var imageRemove = $(this);
        var action = $(this).data('action');
        var uploader = $(this).closest('.avatar-upload');

        if (confirm(confRemovingAvatarMessage)) {
            $(uploader).find('.upload-status').empty();
            $(imageRemove).find('i').removeClass('fa-remove').addClass('fa-spin fa-repeat');

            $.ajax({
                url: action,
                type: 'POST',
                dataType: "json",
                contentType: false,
                cache: false,
                processData: false,
                complete: function () {
                    $(imageRemove).find('i').removeClass('fa-spin fa-repeat').addClass('fa-remove');
                },
                error: function (data) {
                    $(uploader).find('.upload-status').html(data.responseText);
                },
                success: function (data) {
                    $(uploader).find('.avatar-preview img').attr('src', data);
                    $(uploader).find('.upload-status').empty();
                    $(uploader).find('.upload-button').removeClass('hidden');
                    $(uploader).find('.remove-button').addClass('hidden');
                }
            });
        }
    });   
});