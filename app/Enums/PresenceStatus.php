<?php

namespace App\Enums;

enum PresenceStatus: string
{
    case HADIR = 'present';
    case IZIN = 'permission';
    case SAKIT = 'sick';
    case ALPHA = 'absent';
}
