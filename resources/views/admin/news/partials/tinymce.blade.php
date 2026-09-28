{{-- TinyMCE 6 富文本编辑器，作用于 #content-editor --}}
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
<script src="https://cdn.jsdelivr.net/npm/@tinymce/tinymce-langs@6/language/zh_CN.js"></script>
<script>
    tinymce.init({
        selector: '#content-editor',
        language: 'zh_CN',
        height: 480,
        menubar: false,
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
        toolbar: 'undo redo | blocks | bold italic forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | link image media table | code fullscreen | help',
        branding: false,
        promotion: false,
        convert_urls: false,
        relative_urls: false,
        // 图片：选择/粘贴/拖拽自动上传到 storage/app/public/uploads
        // 注意：必须用自定义 handler 随请求携带 CSRF token，否则会被 Laravel 419 拦截
        automatic_uploads: true,
        paste_data_images: true,
        images_file_types: 'jpg,jpeg,png,gif,webp',
        images_upload_handler: function (blobInfo, progress) {
            return new Promise(function (resolve, reject) {
                var formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                fetch('{{ route('admin.upload') }}', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                })
                .then(function (resp) {
                    if (!resp.ok) {
                        return resp.json().catch(function () { return {}; }).then(function (data) {
                            reject(data.message || ('图片上传失败（' + resp.status + '）'));
                        });
                    }
                    return resp.json().then(function (data) {
                        if (data.location) { resolve(data.location); } else { reject('上传响应格式异常'); }
                    });
                })
                .catch(function () { reject('网络错误，图片上传失败'); });
            });
        }
    });
</script>
