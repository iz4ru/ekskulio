<?php

namespace App\Imports;

use App\Models\Extracurricular;
use App\Models\ExtracurricularUser;
use App\Models\ExtracurricularSchedule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class ExtracurricularImport implements 
    ToModel, 
    WithHeadingRow, 
    WithValidation, 
    SkipsEmptyRows
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Build data ekstrakurikuler
        $data = [
            'name' => ucwords(strtoupper($row['nama_ekstrakurikuler'])),
            'code' => strtoupper($row['kode']),
            'category_id' => !empty($row['category_id']) ? $row['category_id'] : null,
            'description' => $row['deskripsi'] ?? null,
            'award' => (!empty($row['penghargaan']) && $row['penghargaan'] !== '-') ? $row['penghargaan'] : null,
            'is_active' => isset($row['status']) && strtolower($row['status']) === 'aktif' ? true : false,
        ];

        if (isset($row['id']) && !empty($row['id'])) {
            $data['id'] = $row['id'];
        }

        $extracurricular = Extracurricular::create($data);

        if (isset($row['user_id']) && !empty($row['user_id'])) {
            ExtracurricularUser::create([
                'extracurricular_id' => $extracurricular->id,
                'user_id' => $row['user_id'],
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
            'kode' => 'required|string|max:50|unique:extracurriculars,code',
            'category_id' => 'nullable|integer|exists:extracurricular_categories,id',
            'user_id' => 'nullable|integer|exists:users,id',
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
            'kode.required' => 'Kode ekstrakurikuler wajib diisi pada baris :row',
            'kode.unique' => 'Kode ":input" sudah digunakan pada baris :row',
            'category_id.integer' => 'ID Kategori harus berupa angka pada baris :row',
            'category_id.exists' => 'ID Kategori :input tidak valid pada baris :row',
            'user_id.integer' => 'ID Pembina harus berupa angka pada baris :row',
            'user_id.exists' => 'ID Pembina :input tidak valid pada baris :row',
            'status.in' => 'Status harus "Aktif" atau "Tidak Aktif" pada baris :row',
        ];
    }
}
