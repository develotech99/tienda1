<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ventas extends Model
{
    use HasFactory;

    protected $table = 'ventas';
    protected $primaryKey = 'ven_id';
    
    protected $fillable = [
        'ven_fecha',
        'ven_hora',
        'ven_cliente',
        'ven_total',
        'ven_efectivo',
        'ven_cambio',
        'user_id',
        'caja_id',
        'ven_estado',
        'user_anulacion_id',
        'fecha_anulacion',
        'motivo_anulacion'
    ];

    protected $casts = [
        'ven_fecha' => 'date',
        'ven_total' => 'decimal:2',
        'ven_efectivo' => 'decimal:2',
        'ven_cambio' => 'decimal:2',
        'fecha_anulacion' => 'datetime'
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function usuarioAnulacion()
    {
        return $this->belongsTo(User::class, 'user_anulacion_id');
    }

    public function caja()
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }

    public function detalles()
    {
        return $this->hasMany(VentasDetalle::class, 'ven_id');
    }

    public function movimientosCaja()
    {
        return $this->hasMany(caja_movimientos::class, 'ven_id');
    }

    // Scopes útiles
    public function scopeActivas($query)
    {
        return $query->where('ven_estado', 'Activa');
    }

    public function scopeAnuladas($query)
    {
        return $query->where('ven_estado', 'Anulada');
    }

    public function scopePorFecha($query, $fecha)
    {
        return $query->where('ven_fecha', $fecha);
    }

    public function scopePorCaja($query, $cajaId)
    {
        return $query->where('caja_id', $cajaId);
    }
}