<?php

namespace Database\Seeders;

use App\Models\ExtracurricularCategory;
use Illuminate\Database\Seeder;

class ExtracurricularCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Array 1 dimensi (hanya berisi nama kategori)
        $extracurricularCategories = [
            'OLAHRAGA DAN BELA DIRI',
            'SENI DAN BUDAYA',
            'BAHASA DAN LITERASI',
            'ORGANISASI DAN KEPEMIMPINAN',
            'KETERAMPILAN DAN KEWIRAUSAHAAN',
            'KEAGAMAAN',
            'TEKNOLOGI',
        ];

        function makeThreeLetterCodeUniqueForCategory($text, &$usedCategoryCodes)
        {
            $clean = strtoupper(preg_replace('/[^A-Z ]/', '', $text));
            $words = array_values(array_filter(explode(' ', $clean)));

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
                $candidates[] = substr($words[0], 0, 1)
                    . substr($words[1], 0, 2);
                $candidates[] = substr($words[0], 0, 2)
                    . substr($words[1], 0, 1);
            } else {
                $candidates[] = substr($words[0], 0, 1)
                    . substr($words[1], 0, 1)
                    . substr($words[2], 0, 1);
            }

            foreach ($candidates as $code) {
                if ($suffix !== '') {
                    $code = substr($code, 0, 2) . $suffix;
                }
                if (strlen($code) === 3 && !in_array($code, $usedCategoryCodes)) {
                    $usedCategoryCodes[] = $code;
                    return $code;
                }
            }

            $i = 1;
            while (true) {
                $code = substr($letters, 0, 2) . $i;
                if (!in_array($code, $usedCategoryCodes)) {
                    $usedCategoryCodes[] = $code;
                    return $code;
                }
                $i++;
            }
        }

        $usedCategoryCodes = [];

        foreach ($extracurricularCategories as $name) {
            ExtracurricularCategory::create([
                'name' => $name,
                'code' => makeThreeLetterCodeUniqueForCategory($name, $usedCategoryCodes), 
            ]);
        }
    }
}