<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ExtracurricularCategory;
use App\Imports\ExtracurricularCategoryImport;

class ExtracurricularCategoryController extends Controller
{
    public function index()
    {
        $x['categories'] = ExtracurricularCategory::with('extracurriculars')->orderBy('name')->get();

        return view('role.kesiswaan.contents.extracurricular-category.index', $x);
    }

    public function create()
    {
        return view('role.kesiswaan.contents.extracurricular-category.create');
    }

    public function import()
    {
        return view('role.kesiswaan.contents.extracurricular-category.import');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:255|unique:extracurricular_categories,name',
        ],
        [
            'category_name.required' => 'Nama kategori ekstrakurikuler wajib diisi.',
            'category_name.string' => 'Nama kategori ekstrakurikuler harus berupa teks.',
            'category_name.max' => 'Nama kategori ekstrakurikuler maksimal 255 karakter.',
            'category_name.unique' => 'Nama kategori ekstrakurikuler sudah ada.',
        ]);

        ExtracurricularCategory::create([
            'name' => $validated['category_name'],
        ]);

        return redirect()->route('extracurricular-category.index')
            ->with('success', 'Kategori ekstrakurikuler ' . $validated['category_name'] . ' berhasil ditambahkan.');
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'upload' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ], [
            'upload.required' => 'File wajib diupload',
            'upload.mimes' => 'File harus berformat .xlsx, .xls, atau .csv',
            'upload.max' => 'Ukuran file maksimal 2MB',
        ]);

        try {
            Excel::import(new ExtracurricularCategoryImport, $request->file('upload'));

            return redirect()
                ->route('extracurricular-category.index')
                ->with('success', 'Data kategori ekstrakurikuler berhasil diimpor!');

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            
            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            }

            return back()
                ->withErrors(['upload' => $errors])
                ->withInput();

        } catch (\Exception $e) {
            return back()
                ->withErrors(['upload' => 'Terjadi kesalahan: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function edit(ExtracurricularCategory $extracurricularCategory)
    {
        return view('role.kesiswaan.contents.extracurricular-category.edit', compact('extracurricularCategory'));
    }

    public function update(Request $request, ExtracurricularCategory $extracurricularCategory)
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:255|unique:extracurricular_categories,name,' . $extracurricularCategory->id,
        ],
        [
            'category_name.required' => 'Nama kategori ekstrakurikuler wajib diisi.',
            'category_name.string' => 'Nama kategori ekstrakurikuler harus berupa teks.',
            'category_name.max' => 'Nama kategori ekstrakurikuler maksimal 255 karakter.',
            'category_name.unique' => 'Nama kategori ekstrakurikuler sudah ada.',
        ]);

        $extracurricularCategory->update([
            'name' => $validated['category_name'],
        ]);

        return redirect()->route('extracurricular-category.index')
            ->with('success', 'Kategori ekstrakurikuler ' . $validated['category_name'] . ' berhasil diperbarui.');
    }

    public function destroy(Request $request, ExtracurricularCategory $extracurricularCategory)
    {
        $request->validate([
            'password' => 'required',
        ], [
            'password.required' => 'Password wajib diisi untuk menghapus kategori.',
        ]);

        // Cek password
        if (!Hash::check($request->password, Auth::user()->password)) {
            return back()->withErrors([
                'password' => 'Password yang Anda masukkan salah!'
            ]);
        }

        // Cek apakah kategori memiliki ekstrakurikuler
        $extracurricularCount = $extracurricularCategory->extracurriculars()->count();
        
        if ($extracurricularCount > 0) {
            return back()->withErrors([
                'category' => 'Kategori tidak dapat dihapus karena masih memiliki ' . $extracurricularCount . ' ekstrakurikuler. Hapus atau pindahkan ekstrakurikuler terlebih dahulu.'
            ]);
        }

        $name = $extracurricularCategory->name;
        $extracurricularCategory->delete();

        return redirect()->route('extracurricular-category.index')
            ->with('success', 'Kategori ekstrakurikuler ' . $name . ' berhasil dihapus.');
    }
}
