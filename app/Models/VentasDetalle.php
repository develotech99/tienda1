<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentasDetalle extends Model
{
    use HasFactory;

    protected $table = 'venta_detalles';
    protected $primaryKey = 'vendet_id';
    
    protected $fillable = [
        'ven_id',
        'prod_id',
        'vendet_cantidad',
        'vendet_precio',
        'vendet_total'
    ];

    protected $casts = [
        'vendet_precio' => 'decimal:2',
        'vendet_total' => 'decimal:2',
    ];

    // Relaciones
    public function venta()
    {
        return $this->belongsTo(Ventas::class, 'ven_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'prod_id');
    }
}