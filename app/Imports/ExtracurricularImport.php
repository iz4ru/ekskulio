<?php

namespace App\Imports;

use App\Models\Extracurricular;
use App\Models\ExtracurricularCategory;
use App\Models\ExtracurricularSchedule;
use App\Models\ExtracurricularUser;
use App\Models\Log;
use App\Models\User;
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

    // Cache di memory untuk validasi tanpa query DB berulang
    protected $existingIds = [];
    protected $existingCodes = [];
    protected $existingCategoryCodes = [];
    protected $existingUserEmails = [];
    
    // Untuk mendeteksi duplikasi di dalam file Excel itu sendiri
    protected $processedIds = [];
    protected $processedCodes = [];

    // Cache untuk lookup di method model()
    protected $categoryCache = [];
    protected $userCache = [];

    public function __construct(?User $importedBy = null)
    {
        set_time_limit(0);

        $this->importedBy = $importedBy;

        $this->existingIds = Extracurricular::pluck('id')->toArray();
        $this->existingCodes = Extracurricular::pluck('code')->map(fn($c) => strtoupper(trim($c)))->toArray();
        
        $this->existingCategoryCodes = ExtracurricularCategory::pluck('code')->map(fn($c) => strtoupper(trim($c)))->toArray();
        $this->existingUserEmails = User::pluck('email')->map(fn($e) => strtolower(trim($e)))->toArray();
    }

    public function prepareForValidation($data, $index)
    {
        return [
            'id'                           => isset($data['id']) && $data['id'] !== '' ? (int) $data['id'] : null,
            'nama_ekstrakurikuler'         => isset($data['nama_ekstrakurikuler']) ? trim($data['nama_ekstrakurikuler']) : null,
            'kode_ekstrakurikuler'         => isset($data['kode_ekstrakurikuler']) ? strtoupper(trim($data['kode_ekstrakurikuler'])) : null,
            'kode_kategori_ekstrakurikuler'=> isset($data['kode_kategori_ekstrakurikuler']) ? strtoupper(trim($data['kode_kategori_ekstrakurikuler'])) : null,
            'email_pembina'                => isset($data['email_pembina']) ? strtolower(trim($data['email_pembina'])) : null,
            'hari'                         => isset($data['hari']) ? trim($data['hari']) : null,
            'deskripsi'                    => isset($data['deskripsi']) ? trim($data['deskripsi']) : null,
            'penghargaan'                  => isset($data['penghargaan']) ? trim($data['penghargaan']) : null,
            'status'                       => isset($data['status']) ? trim($data['status']) : null,
        ];
    }

    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $categoryId = null;
        if (!empty($row['kode_kategori_ekstrakurikuler'])) {
            $code = $row['kode_kategori_ekstrakurikuler'];
            
            if (!isset($this->categoryCache[$code])) {
                $this->categoryCache[$code] = ExtracurricularCategory::where('code', $code)->value('id');
            }
            $categoryId = $this->categoryCache[$code];
        }

        $userId = null;
        if (!empty($row['email_pembina'])) {
            $email = $row['email_pembina'];
            
            if (!isset($this->userCache[$email])) {
                $this->userCache[$email] = User::where('email', $email)->value('id');
            }
            $userId = $this->userCache[$email];
        }

        $data = [
            'name'        => ucwords(strtolower($row['nama_ekstrakurikuler'])),
            'code'        => $row['kode_ekstrakurikuler'],
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
            'id' => [
                'nullable', 'integer',
                // Cek unique di memory
                function ($attribute, $value, $fail) {
                    if (in_array($value, $this->existingIds) || in_array($value, $this->processedIds)) {
                        $fail('ID ' . $value . ' sudah digunakan.');
                    }
                    $this->processedIds[] = $value;
                }
            ],
            'nama_ekstrakurikuler' => 'required|string|max:255',
            'kode_ekstrakurikuler' => [
                'required', 'string', 'max:50',
                // Cek unique di memory
                function ($attribute, $value, $fail) {
                    $normalized = strtoupper(trim($value));
                    if (in_array($normalized, $this->existingCodes) || in_array($normalized, $this->processedCodes)) {
                        $fail('Kode "' . $value . '" sudah digunakan.');
                    }
                    $this->processedCodes[] = $normalized;
                }
            ],
            'kode_kategori_ekstrakurikuler' => [
                'nullable', 'string',
                // Cek exists di memory (menggantikan rule 'exists')
                function ($attribute, $value, $fail) {
                    if (!in_array(strtoupper(trim($value)), $this->existingCategoryCodes)) {
                        $fail('Kode kategori "' . $value . '" tidak ditemukan.');
                    }
                }
            ],
            'email_pembina' => [
                'nullable', 'email',
                // Cek exists di memory (menggantikan rule 'exists')
                function ($attribute, $value, $fail) {
                    if (!in_array(strtolower(trim($value)), $this->existingUserEmails)) {
                        $fail('Email pembina "' . $value . '" tidak ditemukan.');
                    }
                }
            ],
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
            'nama_ekstrakurikuler.required' => 'Nama ekstrakurikuler wajib diisi pada baris :row',
            'nama_ekstrakurikuler.max' => 'Nama ekstrakurikuler maksimal 255 karakter pada baris :row',
            'kode_ekstrakurikuler.required' => 'Kode ekstrakurikuler wajib diisi pada baris :row',
            'email_pembina.email' => 'Email pembina tidak valid pada baris :row',
            'status.in' => 'Status harus "Aktif" atau "Tidak Aktif" pada baris :row',
        ];
    }
}