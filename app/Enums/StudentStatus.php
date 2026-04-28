<?php

namespace App\Enums;

enum StudentStatus: string
{
    case AKTIF = 'aktif';
    case LULUS = 'lulus';
    case MUTASI = 'mutasi';
}
