<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::oldest()->where(function ($query) {
            $query->where('role', UserRole::CUSTOMER->value)
                ->orWhere('role', UserRole::SELLER->value);
        })->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    public function updateIsBanned(Request $request, User $user)
    {
        $request->validate([
            'is_banned' => 'required|boolean',
        ]);

        $user->update([
            'is_banned' => $request->is_banned,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User banned status updated successfully!');
    }

    public function search(Request $request)
    {
        $searchTerm = $request->input('search');

        $users = User::oldest()
            ->where(function ($query) {
                $query->where('role', UserRole::CUSTOMER->value)
                    ->orWhere('role', UserRole::SELLER->value);
            })
            ->where('email', 'like', '%' . $searchTerm . '%')
            ->paginate(10);

        return view('admin.users.index', compact('users'));
    }
}
