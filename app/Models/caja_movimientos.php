<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class caja_movimientos extends Model
{
    use HasFactory;

    protected $table = 'caja_movimientos';
    protected $primaryKey = 'cajamov_id';
    
    protected $fillable = [
        'caja_id',
        'user_id',
        'ven_id',
        'cajamov_tipo',
        'cajamov_monto',
        'cajamov_saldo_final',
        'cajamov_desc'
    ];

    protected $casts = [
        'cajamov_monto' => 'decimal:2',
        'cajamov_saldo_final' => 'decimal:2',
    ];

    // Relaciones
    public function caja()
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function venta()
    {
        return $this->belongsTo(Ventas::class, 'ven_id');
    }

    // Scopes útiles
    public function scopeTipo($query, $tipo)
    {
        return $query->where('cajamov_tipo', $tipo);
    }

    public function scopePorCaja($query, $cajaId)
    {
        return $query->where('caja_id', $cajaId);
    }
}