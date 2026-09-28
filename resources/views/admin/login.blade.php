<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台登录 - XTCMS V2.0</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-100 text-gray-800">
<div class="w-full max-w-sm">
    <div class="bg-white rounded-lg border border-gray-200 p-8">
        <h1 class="text-xl font-bold text-center mb-1">XTCMS V2.0</h1>
        <p class="text-xs text-gray-500 text-center mb-6">后台管理登录</p>

        @if (session('status'))
            <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ session('status') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('admin.login.attempt') }}" method="post" class="space-y-4">
            @csrf
            <div>
                <label for="username" class="block text-sm font-medium mb-1">登录用户</label>
                <input id="username" type="text" name="username" value="{{ old('username') }}" required maxlength="20"
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium mb-1">登录密码</label>
                <input id="password" type="password" name="password" required maxlength="40"
                       class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit"
                    class="w-full rounded-md bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
                登 录
            </button>
        </form>
    </div>
    <p class="text-center text-xs text-gray-400 mt-4">Powered by XTCMS V2.0 · Laravel 11</p>
</div>
</body>
</html>
