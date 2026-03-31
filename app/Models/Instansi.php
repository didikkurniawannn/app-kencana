<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Instansi extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty();
    }

    protected $fillable = ['kode', 'name', 'slug', 'logo', 'primary_color'];

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function realisasis(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Realisasi::class);
    }

    public function sp2ds(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Sp2d::class);
    }

    public function programs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Program::class);
    }

    public function kegiatans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Kegiatan::class);
    }

    public function subKegiatans(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SubKegiatan::class);
    }

    public function rekenings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Rekening::class);
    }

    public function detailBelanjas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DetailBelanja::class);
    }
}
