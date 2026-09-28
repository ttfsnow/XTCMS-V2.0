@extends('layouts.admin')

@section('title', '修改密码')

@section('content')
    <h1 class="text-xl font-bold mb-6">修改密码</h1>

    <form action="{{ route('admin.password.update') }}" method="post"
          class="bg-white rounded-lg border border-gray-200 p-6 space-y-5 max-w-md">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-xs text-gray-500 mb-1">当前密码 <span class="text-red-500">*</span></label>
            <input type="password" name="current_password" required maxlength="40"
                   class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
            @error('current_password')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-xs text-gray-500 mb-1">新密码（至少 6 位） <span class="text-red-500">*</span></label>
            <input type="password" name="password" required minlength="6" maxlength="40"
                   class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
            @error('password')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-xs text-gray-500 mb-1">确认新密码 <span class="text-red-500">*</span></label>
            <input type="password" name="password_confirmation" required minlength="6" maxlength="40"
                   class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm">
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="rounded-md bg-blue-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-blue-700">
                保存新密码
            </button>
        </div>
    </form>
@endsection
