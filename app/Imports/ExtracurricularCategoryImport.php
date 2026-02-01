<?php

namespace App\Imports;

use App\Models\ExtracurricularCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class ExtracurricularCategoryImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $data = [
            'name' => ucwords(strtolower($row['nama_kategori'])),
        ];

        if (isset($row['id']) && !empty($row['id'])) {
            $data['id'] = $row['id'];
        }

        return new ExtracurricularCategory($data);
    }

    /**
     * Validation Rules
     */
    public function rules(): array
    {
        return [
            'id' => 'nullable|integer|unique:extracurricular_categories,id',
            'nama_kategori' => 'required|string|max:255|unique:extracurricular_categories,name',
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
        ];
    }
}
