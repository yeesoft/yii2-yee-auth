$(function () {
    $('.image-uploader form').on('submit', (function (e) {
        e.preventDefault();

        if (typeof window.isAvatarLoading !== 'undefined' && window.isAvatarLoading === true) {
            return;
        }

        window.isAvatarLoading = true;
        var uploader = $(this).parent();
        var action = $(this).attr('action');

        $(uploader).find('.image-submit i').removeClass('fa-save').addClass('fa-spin fa-repeat');

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
            },
            success: function (data) {
                $(uploader).find('.image-preview img').attr('src', data.large);
                $(uploader).find("input[name='image']").val('');
            }
        });
    }));

    $('.image-input').change(function () {
        var uploader = $(this).closest('.image-uploader');
        $(uploader).find('.upload-status').empty();

        var file = this.files[0];
        var fileType = file.type;
        var match = ["image/jpeg", "image/png", "image/jpg"];

        if ((fileType === match[0]) || (fileType === match[1]) || (fileType === match[2])) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $(uploader).find('.image-preview img').attr('src', e.target.result);
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    $('.image-remove').click(function () {
        var imageRemove = $(this);
        var action = $(this).data('action');
        var uploader = $(this).closest('.image-uploader');

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
                    $(uploader).find('.image-preview img').attr('src', data);
                    $(uploader).find('.upload-status').empty();
                }
            });
        }
    });

    $('.oauth-authorized-services .auth-client a').on('click', function () {
        return confirm(confRemovingAuthMessage);
    });
});