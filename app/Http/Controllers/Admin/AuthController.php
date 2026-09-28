<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'max:40'],
        ]);

        $admin = Admin::query()->where('username', $credentials['username'])->first();

        if (! $admin || ! $this->verifyPassword($admin, $credentials['password'])) {
            return back()
                ->onlyInput('username')
                ->withErrors(['username' => '用户名或密码错误']);
        }

        $request->session()->regenerate();
        $request->session()->put('admin_uid', $admin->id);
        $request->session()->put('admin_name', $admin->username);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    public function showPasswordForm(): View
    {
        return view('admin.password', ['adminName' => session('admin_name')]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string', 'max:40'],
            'password'         => ['required', 'string', 'min:6', 'max:40', 'confirmed'],
        ], [
            'current_password.required' => '请输入当前密码',
            'password.required'         => '请输入新密码',
            'password.min'              => '新密码至少 6 位',
            'password.max'              => '新密码最多 40 位',
            'password.confirmed'        => '两次输入的新密码不一致',
        ]);

        $admin = Admin::query()->findOrFail(session('admin_uid'));

        if (! $this->verifyPassword($admin, $data['current_password'])) {
            return back()->withErrors(['current_password' => '当前密码不正确']);
        }

        $admin->forceFill(['password' => Hash::make($data['password'])])->save();

        $request->session()->regenerate();

        return back()->with('status', '密码修改成功');
    }

    /**
     * 校验密码。
     * 兼容 V1 迁移数据：旧库使用 MD5(32位十六进制)，校验通过后自动升级为 bcrypt。
     */
    private function verifyPassword(Admin $admin, string $password): bool
    {
        if (Hash::check($password, $admin->password)) {
            return true;
        }

        if (preg_match('/^[0-9a-f]{32}$/i', $admin->password)
            && strtolower(md5($password)) === strtolower($admin->password)) {
            $admin->forceFill(['password' => Hash::make($password)])->save();

            return true;
        }

        return false;
    }
}
