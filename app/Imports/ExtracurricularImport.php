<?php

namespace App\Imports;

use App\Models\Extracurricular;
use App\Models\ExtracurricularUser;
use App\Models\ExtracurricularSchedule;
use App\Models\ExtracurricularCategory;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class ExtracurricularImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Lookup category_id dari kode_kategori_ekstrakurikuler
        $categoryId = null;
        if (!empty($row['kode_kategori_ekstrakurikuler'])) {
            $category = ExtracurricularCategory::where('code', strtoupper($row['kode_kategori_ekstrakurikuler']))->first();
            if ($category) {
                $categoryId = $category->id;
            }
        }

        // Lookup user_id dari email_pembina
        $userId = null;
        if (!empty($row['email_pembina'])) {
            $user = User::where('email', $row['email_pembina'])->first();
            if ($user) {
                $userId = $user->id;
            }
        }

        // Build data ekstrakurikuler
        $data = [
            'name' => ucwords(strtolower($row['nama_ekstrakurikuler'])),
            'code' => strtoupper($row['kode_ekstrakurikuler']),
            'category_id' => $categoryId,
            'description' => $row['deskripsi'] ?? null,
            'award' => (!empty($row['penghargaan']) && $row['penghargaan'] !== '-') ? $row['penghargaan'] : null,
            'is_active' => isset($row['status']) && strtolower($row['status']) === 'aktif' ? true : false,
        ];

        if (isset($row['id']) && !empty($row['id'])) {
            $data['id'] = $row['id'];
        }

        $extracurricular = Extracurricular::create($data);

        // Assign pembina jika ada
        if ($userId) {
            ExtracurricularUser::create([
                'extracurricular_id' => $extracurricular->id,
                'user_id' => $userId,
            ]);
        }

        // Tambah jadwal (days) - format: "Senin,Rabu,Jumat"
        if (isset($row['hari']) && !empty($row['hari'])) {
            $days = array_map('trim', explode(',', $row['hari']));
            
            foreach ($days as $day) {
                if (!empty($day)) {
                    ExtracurricularSchedule::create([
                        'extracurricular_id' => $extracurricular->id,
                        'day' => ucfirst(strtolower($day)),
                    ]);
                }
            }
        }

        return $extracurricular;
    }

    /**
     * Validation Rules
     */
    public function rules(): array
    {
        return [
            'id' => 'nullable|integer|unique:extracurriculars,id',
            'nama_ekstrakurikuler' => 'required|string|max:255',
            'kode_ekstrakurikuler' => 'required|string|max:50|unique:extracurriculars,code',
            'kode_kategori_ekstrakurikuler' => 'nullable|string|exists:extracurricular_categories,code',
            'email_pembina' => 'nullable|email|exists:users,email',
            'hari' => 'nullable|string',
            'deskripsi' => 'nullable|string',
            'penghargaan' => 'nullable|string',
            'status' => 'nullable|in:Aktif,Tidak Aktif,aktif,tidak aktif',
        ];
    }

    /**
     * Custom Validation Messages
     */
    public function customValidationMessages(): array
    {
        return [
            'id.integer' => 'ID harus berupa angka pada baris :row',
            'id.unique' => 'ID :input sudah digunakan pada baris :row',
            'nama_ekstrakurikuler.required' => 'Nama ekstrakurikuler wajib diisi pada baris :row',
            'nama_ekstrakurikuler.max' => 'Nama ekstrakurikuler maksimal 255 karakter pada baris :row',
            'kode_ekstrakurikuler.required' => 'Kode ekstrakurikuler wajib diisi pada baris :row',
            'kode_ekstrakurikuler.unique' => 'Kode ":input" sudah digunakan pada baris :row',
            'kode_kategori_ekstrakurikuler.exists' => 'Kode kategori ":input" tidak ditemukan pada baris :row',
            'email_pembina.email' => 'Email pembina tidak valid pada baris :row',
            'email_pembina.exists' => 'Email pembina ":input" tidak ditemukan pada baris :row',
            'status.in' => 'Status harus "Aktif" atau "Tidak Aktif" pada baris :row',
        ];
    }
}