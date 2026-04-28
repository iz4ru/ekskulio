<?php

namespace App\Enums;

enum MembershipStatus: string
{
    case AKTIF = 'aktif';
    case SELESAI = 'selesai';
    case DROP = 'drop';
}
