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
            'id' => 'nullable|integer|unique:extracurricular_categories,id',
            'nama_kategori' => 'required|string|max:255|unique:extracurricular_categories,name',
            'kode_kategori' => 'required|string|max:10|unique:extracurricular_categories,code',
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
            'nama_kategori.required' => 'Nama kategori wajib diisi pada baris :row',
            'nama_kategori.string' => 'Nama kategori harus berupa teks pada baris :row',
            'nama_kategori.max' => 'Nama kategori maksimal 255 karakter pada baris :row',
            'nama_kategori.unique' => 'Nama kategori ":input" sudah ada pada baris :row',
            'kode_kategori.required' => 'Kode kategori wajib diisi pada baris :row',
            'kode_kategori.unique' => 'Kode ":input" sudah digunakan pada baris :row',
        ];
    }
}
