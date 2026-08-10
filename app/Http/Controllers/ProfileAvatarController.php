<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileAvatarController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = $request->user();

        // Supprimer l'ancien avatar s'il existe
        if ($user->avatar_url && Storage::disk('public')->exists(str_replace('/storage/', '', $user->avatar_url))) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $user->avatar_url));
        }

        // Stocker le nouveau
        $path = $request->file('avatar')->store('avatars/' . $user->id, 'public');
        $url = '/storage/' . $path;

        $user->update(['avatar_url' => $url]);

        return back()->with('success', 'Photo de profil mise à jour.');
    }
}
