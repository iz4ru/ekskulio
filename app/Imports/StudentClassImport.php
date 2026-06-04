<?php

namespace App\Imports;

use App\Models\Log;
use App\Models\StudentClass;
use App\Models\User;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterImport;

class StudentClassImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, WithEvents
{
    protected $importedBy;
    protected $importedCount = 0;

    protected $existingClassNames = [];
    protected $existingClassIds = [];
    protected $processedNamesInFile = [];

    public function __construct(?User $importedBy = null)
    {
        set_time_limit(0);

        $this->importedBy = $importedBy;

        $this->existingClassNames = StudentClass::pluck('name')
            ->map(fn($n) => strtolower(trim($n)))
            ->toArray();
            
        $this->existingClassIds = StudentClass::pluck('id')->toArray();
    }

    public function prepareForValidation($data, $index)
    {
        return [
            'id'     => isset($data['id']) && $data['id'] !== '' ? (int) $data['id'] : null,
            'kelas'  => isset($data['kelas']) ? trim($data['kelas']) : null,
            'status' => isset($data['status']) ? trim($data['status']) : null,
        ];
    }

    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $data = [
            'name'      => ucwords(strtoupper(trim($row['kelas']))),
            'is_active' => isset($row['status']) && strtolower(trim($row['status'])) === 'aktif',
        ];

        if (isset($row['id']) && !empty($row['id'])) {
            $data['id'] = $row['id'];
        }

        $studentClass = new StudentClass($data);
        
        $this->importedCount++;

        return $studentClass;
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                if ($this->importedBy && $this->importedCount > 0) {
                    Log::create([
                        'user_id'  => $this->importedBy->id,
                        'activity' => 'Import kelas',
                        'detail'   => $this->importedBy->name . ' mengimpor ' . $this->importedCount . ' kelas',
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
                'nullable',
                'integer',
                // Cek unique di memory, bukan query DB
                function ($attribute, $value, $fail) {
                    if (in_array($value, $this->existingClassIds)) {
                        $fail('ID ' . $value . ' sudah digunakan.');
                    }
                }
            ],
            'kelas' => [
                'required',
                'string',
                'max:255',
                // Cek unique di memory + cek duplikasi dalam file Excel
                function ($attribute, $value, $fail) {
                    $normalized = strtolower(trim($value));

                    // Cek apakah sudah ada di database (dari cache)
                    if (in_array($normalized, $this->existingClassNames)) {
                        $fail('Nama kelas "' . $value . '" sudah ada di database.');
                        return;
                    }

                    // Cek apakah sudah ada di file Excel ini (duplikasi dalam batch)
                    if (in_array($normalized, $this->processedNamesInFile)) {
                        $fail('Nama kelas "' . $value . '" duplikat dalam file Excel.');
                        return;
                    }

                    // Tambahkan ke list yang sudah diproses untuk baris berikutnya
                    $this->processedNamesInFile[] = $normalized;
                }
            ],
            'status' => 'nullable|in:Aktif,Tidak Aktif,aktif,tidak aktif',
        ];
    }

    /**
     * Custom Validation Messages
     */
    public function customValidationMessages(): array
    {
        return [
            'id.integer'     => 'ID harus berupa angka pada baris :row',
            'kelas.required' => 'Nama kelas wajib diisi pada baris :row',
            'kelas.string'   => 'Nama kelas harus berupa teks pada baris :row',
            'kelas.max'      => 'Nama kelas maksimal 255 karakter pada baris :row',
            'status.in'      => 'Status harus berupa "Aktif" atau "Tidak Aktif" pada baris :row',
        ];
    }
}