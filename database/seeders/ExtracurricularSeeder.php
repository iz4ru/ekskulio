<?php

namespace Database\Seeders;

use App\Models\Extracurricular;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ExtracurricularSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            1 => [
                'BASKET BALL',
                'BULU TANGKIS A',
                'BULU TANGKIS B',
                'BULU TANGKIS C',
                'FUTSAL A',
                'FUTSAL B',
                'HAND BALL',
                'VOLLY BALL',
                'PENCAK SILAT',
                'KARATE',
                'TAEKWONDO',
                'TARUNG DRAJAT',
            ],

            2 => [
                'KARAWITAN',
                'SENI TARI',
                'PADUAN SUARA',
                'MARCHING BAND A',
                'MARCHING BAND B',
                'MARCHING BAND C',
            ],

            6 => [
                'BTQ A',
                'BTQ B',
                'KALIGRAFI',
                'NASYID',
                "QI'ROAT",
                'TAHFIZD',
                'MARAWIS',
            ],

            3 => [
                'ENGLISH CLUB A',
                'ENGLISH CLUB B',
                'LITERASI',
                'NIHONGO KAI',
            ],

            4 => [
                'PRAMUKA',
                'PASKIBRA A',
                'PASKIBRA B',
                'PMR',
                'PIK-R',
            ],

            5 => [
                'TATA BOGA A',
                'TATA BOGA B',
                'TATA BUSANA',
                'TATA RIAS',
            ],

            7 => [
                'IT CLUB',
                'HOVER',
                'PLH A',
                'PLH B',
            ],
        ];

        function makeThreeLetterCodeUnique($text, &$usedCodes)
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
                if (strlen($code) === 3 && !in_array($code, $usedCodes)) {
                    $usedCodes[] = $code;
                    return $code;
                }
            }

            $i = 1;
            while (true) {
                $code = substr($letters, 0, 2) . $i;
                if (!in_array($code, $usedCodes)) {
                    $usedCodes[] = $code;
                    return $code;
                }
                $i++;
            }
        }

        $usedCodes = [];

        foreach ($categories as $categoryId => $items) {
            foreach ($items as $name) {
                Extracurricular::create([
                    'name' => $name,
                    'code' => makeThreeLetterCodeUnique($name, $usedCodes),
                    'award' => null,
                    'category_id' => $categoryId,
                    'description' => $name . ' adalah kegiatan ekstrakurikuler.',
                    'is_active' => true,
                ]);
            }
        }
    }
}
