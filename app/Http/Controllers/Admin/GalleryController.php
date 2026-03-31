<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index()
    {
        $galleries = Gallery::latest()->paginate(8);
        return view('admin.gallery.index', compact('galleries'));
    }

    public function create()
    {
        return view('admin.gallery.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'         => ['required', 'string', 'max:255'],
            'cropped_image' => ['required', 'string'],
        ]);

        $filename = $this->saveBase64Image($request->input('cropped_image'), 'gallery');

        Gallery::create([
            'title' => $request->title,
            'image' => $filename,
        ]);

        return redirect()->route('admin.gallery.index')->with('success', 'Gallery berhasil ditambahkan!');
    }

    public function edit(Gallery $gallery)
    {
        return view('admin.gallery.edit', compact('gallery'));
    }

    public function update(Request $request, Gallery $gallery)
    {
        $request->validate([
            'title'         => ['required', 'string', 'max:255'],
            'cropped_image' => ['nullable', 'string'],
        ]);

        $data = ['title' => $request->title];

        if ($request->filled('cropped_image')) {
            if ($gallery->image) {
                Storage::disk('public')->delete($gallery->image);
            }
            $data['image'] = $this->saveBase64Image($request->input('cropped_image'), 'gallery');
        }

        $gallery->update($data);

        return redirect()->route('admin.gallery.index')->with('success', 'Gallery berhasil diupdate!');
    }

    /**
     * Decode a base64 data-URL and save it as a file on the public disk.
     */
    private function saveBase64Image(string $base64, string $directory): string
    {
        preg_match('/^data:image\/(\w+);base64,/', $base64, $matches);
        $ext       = isset($matches[1]) ? ($matches[1] === 'jpeg' ? 'jpg' : $matches[1]) : 'jpg';
        $imageData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $base64));
        $filename  = $directory . '/' . uniqid() . '.' . $ext;
        Storage::disk('public')->put($filename, $imageData);
        return $filename;
    }

    public function destroy(Gallery $gallery)
    {
        if ($gallery->image) {
            Storage::disk('public')->delete($gallery->image);
        }

        $gallery->delete();

        return redirect()->route('admin.gallery.index')->with('success', 'Gallery berhasil dihapus!');
    }
}
