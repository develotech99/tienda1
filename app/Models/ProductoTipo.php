<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoTipo extends Model
{
    use HasFactory;

    protected $table = "producto_tipo";
    protected $primaryKey = 'tprod_id';
    public $timestamps = false;
    protected $fillable = [
        'tprod_nombre',
        'tprod_desc',
        'tprod_situacion'
    ];


}
