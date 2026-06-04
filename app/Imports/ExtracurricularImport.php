<?php

namespace App\Imports;

use App\Models\Extracurricular;
use App\Models\ExtracurricularCategory;
use App\Models\ExtracurricularSchedule;
use App\Models\ExtracurricularUser;
use App\Models\Log;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterImport;

class ExtracurricularImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, WithEvents
{
    protected $importedBy;
    protected $importedCount = 0;

    public function __construct(?User $importedBy = null)
    {
        $this->importedBy = $importedBy;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return DB::transaction(function () use ($row) {
            $categoryId = null;
            if (!empty($row['kode_kategori_ekstrakurikuler'])) {
                $category = ExtracurricularCategory::where('code', strtoupper($row['kode_kategori_ekstrakurikuler']))->first();
                if ($category) {
                    $categoryId = $category->id;
                }
            }

            $userId = null;
            if (!empty($row['email_pembina'])) {
                $user = User::where('email', $row['email_pembina'])->first();
                if ($user) {
                    $userId = $user->id;
                }
            }

            $data = [
                'name'        => ucwords(strtolower($row['nama_ekstrakurikuler'])),
                'code'        => strtoupper($row['kode_ekstrakurikuler']),
                'category_id' => $categoryId,
                'description' => $row['deskripsi'] ?? null,
                'award'       => (!empty($row['penghargaan']) && $row['penghargaan'] !== '-') ? $row['penghargaan'] : null,
                'is_active'   => isset($row['status']) && strtolower($row['status']) === 'aktif',
            ];

            if (isset($row['id']) && !empty($row['id'])) {
                $data['id'] = $row['id'];
            }

            $extracurricular = Extracurricular::create($data);

            if ($userId) {
                ExtracurricularUser::create([
                    'extracurricular_id' => $extracurricular->id,
                    'user_id'            => $userId,
                ]);
            }

            if (!empty($row['hari'])) {
                $days = array_map('trim', explode(',', $row['hari']));
                foreach ($days as $day) {
                    if (!empty($day)) {
                        ExtracurricularSchedule::create([
                            'extracurricular_id' => $extracurricular->id,
                            'day'                => ucfirst(strtolower($day)),
                        ]);
                    }
                }
            }

            $this->importedCount++;

            return $extracurricular;
        });
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                if ($this->importedBy && $this->importedCount > 0) {
                    Log::create([
                        'user_id'  => $this->importedBy->id,
                        'activity' => 'Import ekstrakurikuler',
                        'detail'   => $this->importedBy->name . ' mengimpor ' . $this->importedCount . ' ekstrakurikuler',
                    ]);
                }
            },
        ];
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