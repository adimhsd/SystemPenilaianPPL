<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'nim',
        'name',
        'jenis_kelamin',
        'prodi',
        'konsentrasi',
        'no_hp',
        'alamat',
        'mitra_score',
        'dpl_score',
        'final_score',
        'letter_grade',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'mitra_score' => 'float',
            'dpl_score' => 'float',
            'final_score' => 'float',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    /**
     * Hitung nilai akhir berdasarkan formula: (Mitra x 60%) + (Laporan x 40%)
     */
    public static function calculateFinalScore(?float $mitraScore, ?float $dplScore): ?float
    {
        if ($mitraScore === null && $dplScore === null) {
            return null;
        }

        $mitra = $mitraScore ?? 0;
        $dpl = $dplScore ?? 0;

        return round(($mitra * 0.6) + ($dpl * 0.4), 2);
    }

    /**
     * Konversi nilai akhir angka ke nilai huruf sesuai standar FEB UNIKU
     */
    public static function calculateLetterGrade(?float $finalScore): ?string
    {
        if ($finalScore === null) {
            return null;
        }

        if ($finalScore >= 81.0) {
            return 'A';
        } elseif ($finalScore >= 75.0) {
            return 'AB';
        } elseif ($finalScore >= 69.0) {
            return 'B';
        } elseif ($finalScore >= 63.0) {
            return 'BC';
        } elseif ($finalScore >= 57.0) {
            return 'C';
        } elseif ($finalScore >= 51.0) {
            return 'CD';
        } elseif ($finalScore >= 45.0) {
            return 'D';
        } else {
            return 'E';
        }
    }

    protected static function booted(): void
    {
        static::saving(function (Student $student) {
            if ($student->mitra_score !== null || $student->dpl_score !== null) {
                $student->final_score = self::calculateFinalScore($student->mitra_score, $student->dpl_score);
                $student->letter_grade = self::calculateLetterGrade($student->final_score);
            }
        });
    }
}
