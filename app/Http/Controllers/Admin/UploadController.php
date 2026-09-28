<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    /**
     * TinyMCE 富文本图片上传，保存到 storage/app/public/uploads。
     * 返回格式符合 TinyMCE images_upload_url 约定：{"location": "图片可访问 URL"}
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'image', 'max:5120'], // 最大 5MB
        ]);

        $path = $request->file('file')->store(
            'uploads/'.now()->format('Ym'),
            'public'
        );

        return response()->json([
            'location' => Str::finish(config('app.url'), '/').'storage/'.$path,
        ]);
    }
}
