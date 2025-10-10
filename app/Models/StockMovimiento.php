<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovimiento extends Model
{
    use HasFactory;

    protected $table = "stock_movimientos";
    protected $primaryKey = 'mov_id';
    
    protected $fillable = [
        'prod_id',
        'mov_tipo',
        'mov_cantidad',
        'mov_stock_anterior',
        'mov_stock_nuevo',
        'mov_motivo',
        'mov_observacion'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'prod_id', 'prod_id');
    }
}