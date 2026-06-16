<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UpdateAvatarRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function show()
    {
        // Get the authenticated user
        $user = Auth::user();

        return view('pages.profile.index', compact('user'));
    }

    public function edit()
    {
        $user = auth()->user();

        return view('pages.profile.edit', compact('user'));
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = auth()->user();

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return redirect()->route('profile.index')->with('success', 'Profile updated successfully!');
    }

    public function updateAvatar(UpdateAvatarRequest $request, CloudinaryService $cloudinary)
    {
        $user = auth()->user();

        if ($user->avatar_public_id) {
            $cloudinary->delete($user->avatar_public_id);
        }

        $result = $cloudinary->upload(
            $request->file('avatar')->getRealPath(),
            CloudinaryService::FOLDER_AVATAR
        );

        $user->update([
            'avatar_url' => $result['url'],
            'avatar_public_id' => $result['public_id'],
        ]);

        return back()->with('success', 'Update avatar successfully!');
    }
}
