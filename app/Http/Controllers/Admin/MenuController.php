<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MenuController extends Controller
{
    /**
     * Display a listing of menu items.
     */
    public function index(): View
    {
        $menus = Menu::with('kategori')->latest()->get();
        $kategoris = Kategori::all();

        return view('admin.menu.index', compact('menus', 'kategoris'));
    }

    /**
     * Show the form for creating a new menu item.
     */
    public function create(): View
    {
        $kategoris = Kategori::all();

        return view('admin.menu.create', compact('kategoris'));
    }

    /**
     * Store a newly created menu item.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_kategori' => ['required', 'exists:kategori,id_kategori'],
            'nama_menu' => ['required', 'string', 'max:100'],
            'harga' => ['required', 'numeric', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ], [
            'foto.uploaded' => 'Ukuran foto melebihi batas upload server (maksimal 2MB). Silakan gunakan foto dengan ukuran lebih kecil.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Format foto harus berupa jpeg, png, jpg, gif, atau webp.',
            'foto.max' => 'Ukuran foto maksimal adalah 2MB.',
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('menu', 'public');
        } else {
            unset($validated['foto']);
        }

        Menu::create($validated);

        return redirect()->route('admin.menu.index')
            ->with('success', 'Menu berhasil ditambahkan.');
    }

    /**
     * Show the form for editing a menu item.
     */
    public function edit(Menu $menu): View
    {
        $kategoris = Kategori::all();

        return view('admin.menu.edit', compact('menu', 'kategoris'));
    }

    /**
     * Update the specified menu item.
     */
    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $validated = $request->validate([
            'id_kategori' => ['required', 'exists:kategori,id_kategori'],
            'nama_menu' => ['required', 'string', 'max:100'],
            'harga' => ['required', 'numeric', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ], [
            'foto.uploaded' => 'Ukuran foto melebihi batas upload server (maksimal 2MB). Silakan gunakan foto dengan ukuran lebih kecil.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Format foto harus berupa jpeg, png, jpg, gif, atau webp.',
            'foto.max' => 'Ukuran foto maksimal adalah 2MB.',
        ]);

        if ($request->hasFile('foto')) {
            if ($menu->foto) {
                Storage::disk('public')->delete($menu->foto);
            }

            $validated['foto'] = $request->file('foto')->store('menu', 'public');
        } else {
            unset($validated['foto']);
        }

        $menu->update($validated);

        return redirect()->route('admin.menu.index')
            ->with('success', 'Menu berhasil diperbarui.');
    }

    /**
     * Remove the specified menu item.
     */
    public function destroy(Menu $menu): RedirectResponse
    {
        if ($menu->foto) {
            Storage::disk('public')->delete($menu->foto);
        }

        $menu->delete();

        return redirect()->route('admin.menu.index')
            ->with('success', 'Menu berhasil dihapus.');
    }
}
