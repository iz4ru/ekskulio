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
            'name'      => ucwords(strtoupper($row['kelas'])),
            'is_active' => isset($row['status']) && strtolower($row['status']) === 'aktif',
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
            'id' => 'nullable|integer|unique:student_classes,id',
            'kelas' => 'required|string|max:255|unique:student_classes,name',
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
            'kelas.required' => 'Nama kelas wajib diisi pada baris :row',
            'kelas.string' => 'Nama kelas harus berupa teks pada baris :row',
            'kelas.max' => 'Nama kelas maksimal 255 karakter pada baris :row',
            'kelas.unique' => 'Nama kelas ":input" sudah ada pada baris :row',
            'status.in' => 'Status harus berupa "Aktif" atau "Tidak Aktif" pada baris :row',
        ];
    }
}
