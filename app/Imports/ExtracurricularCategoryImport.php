<?php

namespace App\Imports;

use App\Models\ExtracurricularCategory;
use App\Models\Log;
use App\Models\User;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterImport;

class ExtracurricularCategoryImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, WithEvents
{
    protected $importedBy;
    protected $importedCount = 0;

    protected $existingIds = [];
    protected $existingNames = [];
    protected $existingCodes = [];
    
    protected $processedIds = [];
    protected $processedNames = [];
    protected $processedCodes = [];

    public function __construct(?User $importedBy = null)
    {
        set_time_limit(0);

        $this->importedBy = $importedBy;

        $this->existingIds = ExtracurricularCategory::pluck('id')->toArray();
        $this->existingNames = ExtracurricularCategory::pluck('name')
            ->map(fn($n) => strtolower(trim($n)))
            ->toArray();
        $this->existingCodes = ExtracurricularCategory::pluck('code')
            ->map(fn($c) => strtoupper(trim($c)))
            ->toArray();
    }

    public function prepareForValidation($data, $index)
    {
        return [
            'id'            => isset($data['id']) && $data['id'] !== '' ? (int) $data['id'] : null,
            'nama_kategori' => isset($data['nama_kategori']) ? trim($data['nama_kategori']) : null,
            'kode_kategori' => isset($data['kode_kategori']) ? strtoupper(trim($data['kode_kategori'])) : null,
        ];
    }

    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $data = [
            'name' => ucwords(strtolower($row['nama_kategori'])),
            'code' => strtoupper($row['kode_kategori']),
        ];

        if (isset($row['id']) && !empty($row['id'])) {
            $data['id'] = $row['id'];
        }

        $category = new ExtracurricularCategory($data);

        $this->importedCount++;

        return $category;
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                if ($this->importedBy && $this->importedCount > 0) {
                    Log::create([
                        'user_id'  => $this->importedBy->id,
                        'activity' => 'Import kategori ekstrakurikuler',
                        'detail'   => $this->importedBy->name . ' mengimpor ' . $this->importedCount . ' kategori ekstrakurikuler',
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
            'nama_kategori' => [
                'required', 'string', 'max:255',
                // Cek unique di memory (case-insensitive)
                function ($attribute, $value, $fail) {
                    $normalized = strtolower(trim($value));
                    if (in_array($normalized, $this->existingNames) || in_array($normalized, $this->processedNames)) {
                        $fail('Nama kategori "' . $value . '" sudah ada.');
                    }
                    $this->processedNames[] = $normalized;
                }
            ],
            'kode_kategori' => [
                'required', 'string', 'max:10',
                // Cek unique di memory (case-insensitive)
                function ($attribute, $value, $fail) {
                    $normalized = strtoupper(trim($value));
                    if (in_array($normalized, $this->existingCodes) || in_array($normalized, $this->processedCodes)) {
                        $fail('Kode kategori "' . $value . '" sudah digunakan.');
                    }
                    $this->processedCodes[] = $normalized;
                }
            ],
        ];
    }

    /**
     * Custom Validation Messages
     */
    public function customValidationMessages(): array
    {
        return [
            'id.integer' => 'ID harus berupa angka pada baris :row',
            'nama_kategori.required' => 'Nama kategori wajib diisi pada baris :row',
            'nama_kategori.string' => 'Nama kategori harus berupa teks pada baris :row',
            'nama_kategori.max' => 'Nama kategori maksimal 255 karakter pada baris :row',
            'kode_kategori.required' => 'Kode kategori wajib diisi pada baris :row',
            'kode_kategori.max' => 'Kode kategori maksimal 10 karakter pada baris :row',
        ];
    }
}