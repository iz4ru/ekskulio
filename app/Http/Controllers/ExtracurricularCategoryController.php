<?php

namespace App\Http\Controllers;

use App\Imports\ExtracurricularCategoryImport;
use App\Models\ExtracurricularCategory;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class ExtracurricularCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = ExtracurricularCategory::with('extracurriculars');

        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = strtolower($request->search);
            $q->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"])
                ->orWhereHas('extracurriculars', function ($q) use ($search) {
                  $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
              });
            });
        });

        $x['categories'] = $query->orderBy('name')->paginate(15)->withQueryString();

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

    /**
     * Generate unique 3-letter code dari nama ekstrakurikuler
     */
    public function generateCode($name)
    {
        if (!$name) {
            return response()->json(['code' => '']);
        }

        // Decode URL-encoded name
        $name = urldecode($name);

        // Ambil semua kode yang sudah ada
        $usedCodes = ExtracurricularCategory::pluck('code')->toArray();

        $clean = preg_replace('/[^A-Z ]/', '', strtoupper($name));
        $words = array_values(array_filter(explode(' ', $clean)));

        if (empty($words)) {
            return response()->json(['code' => $this->generateFallbackCode($usedCodes)]);
        }

        $suffix = '';
        $last = end($words);
        if (strlen($last) === 1) {
            $suffix = $last;
            array_pop($words);
        }

        $letters = implode('', $words);
        $candidates = [];

        if (count($words) === 1) {
            $candidates[] = substr($letters, 0, 3);
            $candidates[] = substr($letters, 0, 2) . substr($letters, 3, 1);
            $candidates[] = substr($letters, 0, 1) . substr($letters, 2, 2);
        } elseif (count($words) === 2) {
            $candidates[] = substr($words[0], 0, 1) . substr($words[1], 0, 2);
            $candidates[] = substr($words[0], 0, 2) . substr($words[1], 0, 1);
        } else {
            $candidates[] = substr($words[0], 0, 1) . substr($words[1], 0, 1) . substr($words[2], 0, 1);
        }

        foreach ($candidates as $code) {
            if ($suffix !== '') {
                $code = substr($code, 0, 2) . $suffix;
            }
            if (strlen($code) === 3 && !in_array($code, $usedCodes)) {
                return response()->json(['code' => $code]);
            }
        }

        // Fallback: tambahkan angka
        $i = 1;
        while (true) {
            $code = substr($letters, 0, 2) . $i;
            if (!in_array($code, $usedCodes)) {
                return response()->json(['code' => $code]);
            }
            $i++;
        }
    }

    private function generateFallbackCode($usedCodes)
    {
        $i = 1;
        while (true) {
            $code = 'EX' . $i;
            if (!in_array($code, $usedCodes)) {
                return $code;
            }
            $i++;
        }
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'category_name' => 'required|string|max:255|unique:extracurricular_categories,name',
            'category_code' => 'required|string|max:10|unique:extracurricular_categories,code',
        ],
        [
            'category_name.required' => 'Nama kategori ekstrakurikuler wajib diisi.',
            'category_name.string' => 'Nama kategori ekstrakurikuler harus berupa teks.',
            'category_name.max' => 'Nama kategori ekstrakurikuler maksimal 255 karakter.',
            'category_name.unique' => 'Nama kategori ekstrakurikuler sudah ada.',
            'category_code.required' => 'Kode kategori ekstrakurikuler wajib diisi.',
            'category_code.unique' => 'Kode kategori ekstrakurikuler sudah digunakan.',
        ]);

        DB::transaction(function () use ($validated, $user) {
            ExtracurricularCategory::create([
                'name' => $validated['category_name'],
                'code' => $validated['category_code'],
            ]);

            Log::create([
                'user_id'  => $user->id,
                'activity' => 'Tambah kategori ekstrakurikuler',
                'detail'   => $user->name . ' menambahkan kategori ' . $validated['category_name'] . ' (' . $validated['category_code'] . ')',
            ]);
        });

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
            Excel::import(new ExtracurricularCategoryImport(Auth::user()), $request->file('upload'));

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
        $user = Auth::user();

        $validated = $request->validate([
            'category_name' => 'required|string|max:255|unique:extracurricular_categories,name,' . $extracurricularCategory->id,
            'category_code' => 'required|string|max:10|unique:extracurricular_categories,code,' . $extracurricularCategory->id,
        ],
        [
            'category_name.required' => 'Nama kategori ekstrakurikuler wajib diisi.',
            'category_name.string' => 'Nama kategori ekstrakurikuler harus berupa teks.',
            'category_name.max' => 'Nama kategori ekstrakurikuler maksimal 255 karakter.',
            'category_name.unique' => 'Nama kategori ekstrakurikuler sudah ada.',
            'category_code.required' => 'Kode kategori ekstrakurikuler wajib diisi.',
            'category_code.unique' => 'Kode kategori ekstrakurikuler sudah digunakan.',
        ]);

        if ($extracurricularCategory->name === $validated['category_name']
            && $extracurricularCategory->code === $validated['category_code']
        ) {
            return redirect()->route('extracurricular-category.index')
                ->with('success', 'Kategori ekstrakurikuler ' . $validated['category_name'] . ' berhasil diperbarui.');
        }

        $oldName = $extracurricularCategory->name;
        $oldCode = $extracurricularCategory->code;

        DB::transaction(function () use ($validated, $extracurricularCategory, $user, $oldName, $oldCode) {
            $extracurricularCategory->update([
                'name' => $validated['category_name'],
                'code' => $validated['category_code'],
            ]);

            Log::create([
                'user_id'  => $user->id,
                'activity' => 'Ubah kategori ekstrakurikuler',
                'detail'   => $user->name . ' mengubah kategori ' . $oldName . ' (' . $oldCode . ') menjadi ' . $validated['category_name'] . ' (' . $validated['category_code'] . ')',
            ]);
        });

        return redirect()->route('extracurricular-category.index')
            ->with('success', 'Kategori ekstrakurikuler ' . $validated['category_name'] . ' berhasil diperbarui.');
    }

    public function destroy(Request $request, ExtracurricularCategory $extracurricularCategory)
    {
        $user = Auth::user();

        $request->validate([
            'password' => 'required',
        ], [
            'password.required' => 'Password wajib diisi untuk menghapus kategori.',
        ]);

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'Password yang Anda masukkan salah!'
            ]);
        }

        $extracurricularCount = $extracurricularCategory->extracurriculars()->count();

        if ($extracurricularCount > 0) {
            return back()->withErrors([
                'category' => 'Kategori tidak dapat dihapus karena masih memiliki ' . $extracurricularCount . ' ekstrakurikuler. Hapus atau pindahkan ekstrakurikuler terlebih dahulu.'
            ]);
        }

        $name = $extracurricularCategory->name;
        $code = $extracurricularCategory->code;

        DB::transaction(function () use ($user, $extracurricularCategory, $name, $code) {
            $extracurricularCategory->delete();

            Log::create([
                'user_id'  => $user->id,
                'activity' => 'Hapus kategori ekstrakurikuler',
                'detail'   => $user->name . ' menghapus kategori ' . $name . ' (' . $code . ') (ID: ' . $extracurricularCategory->id . ')',
            ]);
        });

        return redirect()->route('extracurricular-category.index')
            ->with('success', 'Kategori ekstrakurikuler ' . $name . ' berhasil dihapus.');
    }
}
