<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Interested = 'interested';
    case Applied = 'applied';
    case Submitted = 'submitted';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
}