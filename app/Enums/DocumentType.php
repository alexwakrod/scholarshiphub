<?php

namespace App\Enums;

enum DocumentType: string
{
    case Cv = 'cv';
    case PersonalStatement = 'personal_statement';
    case Other = 'other';
}