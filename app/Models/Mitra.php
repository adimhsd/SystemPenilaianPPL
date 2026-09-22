<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Mitra extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_mitra',
        'kategori',
        'alamat',
    ];

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class, 'mitra_id');
    }

    public function students(): HasManyThrough
    {
        return $this->hasManyThrough(Student::class, Group::class, 'mitra_id', 'group_id');
    }
}
