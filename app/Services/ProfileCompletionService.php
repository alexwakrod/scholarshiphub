<?php

namespace App\Services;

use App\Models\StudentProfile;
use Illuminate\Support\Facades\Log;

class ProfileCompletionService
{
    /**
     * Only these fields are mandatory – everything else is optional.
     */
    protected array $requiredFields = [
        'full_name',
        'academic_level',
        'major',
        'date_of_birth',
        'country',
    ];

    /**
     * Required demographic sub‑fields (stored inside the JSON).
     */
    protected array $requiredDemographicsFields = [
        'english_level',
        'budget',
    ];

    public function calculate(StudentProfile $profile): int
    {
        try {
            $filled = 0;
            $total = count($this->requiredFields) + count($this->requiredDemographicsFields);

            foreach ($this->requiredFields as $field) {
                if ($this->isFilled($profile->$field)) {
                    $filled++;
                }
            }

            $demographics = $profile->demographics ?? [];
            foreach ($this->requiredDemographicsFields as $field) {
                if ($this->isFilled($demographics[$field] ?? null)) {
                    $filled++;
                }
            }

            return (int) round(($filled / $total) * 100);
        } catch (\Throwable $e) {
            Log::error('ProfileCompletionService error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * True only when the profile is 100 % complete.
     */
    public function isComplete(StudentProfile $profile): bool
    {
        return $this->calculate($profile) === 100;
    }

    private function isFilled($value): bool
    {
        if (is_null($value)) {
            return false;
        }
        if (is_string($value)) {
            return trim($value) !== '';
        }
        if (is_array($value)) {
            return !empty($value);
        }
        return true; // booleans, numbers etc. are considered filled
    }
}