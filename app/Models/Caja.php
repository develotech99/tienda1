<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    use HasFactory;

    protected $table = 'caja';
    protected $primaryKey = 'caja_id';
    
    protected $fillable = [
        'caja_nombre',
        'caja_saldo',
        'caja_estado'
    ];

    protected $casts = [
        'caja_saldo' => 'decimal:2',
    ];

    // Relaciones
    public function ventas()
    {
        return $this->hasMany(ventas::class, 'caja_id');
    }

    public function movimientos()
    {
        return $this->hasMany(caja_movimientos::class, 'caja_id');
    }

    // Scopes útiles
    public function scopeAbierta($query)
    {
        return $query->where('caja_estado', 'Abierta');
    }

    public function scopeCerrada($query)
    {
        return $query->where('caja_estado', 'Cerrada');
    }
}