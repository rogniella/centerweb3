<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        $allShortcuts = collect(config('home.shortcuts'))->filter(function ($shortcut) use ($user) {
            $roles = $shortcut['roles'] ?? ['*'];
            return in_array('*', $roles) || in_array($user->perfil_id, $roles);
        })->all();

        $saved = $user->home_shortcuts;
        $hasSaved = $saved !== null;

        $userShortcuts = $hasSaved ? $saved : array_keys($allShortcuts);

        $sortedShortcuts = [];
        foreach ($userShortcuts as $route) {
            if (isset($allShortcuts[$route])) {
                $sortedShortcuts[$route] = $allShortcuts[$route];
            }
        }

        if ($hasSaved) {
            $sortedAllShortcuts = [];
            foreach ($saved as $route) {
                if (isset($allShortcuts[$route])) {
                    $sortedAllShortcuts[$route] = $allShortcuts[$route];
                }
            }
            foreach ($allShortcuts as $route => $shortcut) {
                if (!isset($sortedAllShortcuts[$route])) {
                    $sortedAllShortcuts[$route] = $shortcut;
                }
            }
        } else {
            $sortedAllShortcuts = $allShortcuts;
        }

        return view('home', compact('allShortcuts', 'sortedAllShortcuts', 'sortedShortcuts', 'userShortcuts'));
    }

    public function saveShortcuts(Request $request)
    {
        $raw = $request->input('shortcuts', '[]');
        $shortcuts = is_array($raw) ? $raw : json_decode($raw, true);
        $shortcuts = is_array($shortcuts) ? $shortcuts : [];

        auth()->user()->update([
            'home_shortcuts' => $shortcuts,
        ]);

        return back()->with('success', 'Accesos directos guardados correctamente.');
    }
}
