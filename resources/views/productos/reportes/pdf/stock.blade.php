<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Reporte de Stock - {{ $fecha->format('d/m/Y') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .bajo-stock {
            background-color: #fff3cd;
        }

        .sin-stock {
            background-color: #f8d7da;
        }

        .foto-producto {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }

        .estado-critico {
            color: #dc3545;
            font-weight: bold;
        }

        .estado-bajo {
            color: #ffc107;
            font-weight: bold;
        }

        .estado-normal {
            color: #28a745;
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Reporte de Stock</h1>
        <p><strong>Generado:</strong> {{ $fecha->format('d/m/Y H:i') }}</p>
        @if(!empty($filtros['categoria']))
        <p><strong>Categoría filtrada:</strong> {{ $filtros['categoria'] }}</p>
        @endif
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Foto</th>
                <th>Código</th>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Stock Actual</th>
                <th>Stock Mínimo</th>
                <th>Estado</th>
                <th>Precio</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $producto)
            @php
            $estado = 'NORMAL';
            $claseEstado = 'estado-normal';
            $claseFila = '';

            if ($producto->prod_stock_actual == 0) {
            $estado = 'SIN STOCK';
            $claseEstado = 'estado-critico';
            $claseFila = 'sin-stock';
            } elseif ($producto->prod_stock_actual <= $producto->prod_stock_minimo) {
                $estado = 'STOCK BAJO';
                $claseEstado = 'estado-bajo';
                $claseFila = 'bajo-stock';
                }
                @endphp
                <tr class="{{ $claseFila }}">
                    <td>
                        @if($producto->prod_imagen)
                        <img src="{{ storage_path('app/public/' . $producto->prod_imagen) }}" class="foto-producto">
                        @else
                        <div style="width: 50px; height: 50px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                            <span style="font-size: 8px; color: #999;">Sin imagen</span>
                        </div>
                        @endif
                    </td>
                    <td>{{ $producto->prod_codigo }}</td>
                    <td>{{ $producto->prod_nombre }}</td>
                    <td>{{ $producto->tipo->tprod_nombre ?? 'N/A' }}</td>
                    <td>{{ $producto->prod_stock_actual }}</td>
                    <td>{{ $producto->prod_stock_minimo }}</td>
                    <td class="{{ $claseEstado }}">{{ $estado }}</td>
                    <td>Q{{ number_format($producto->prod_precio_venta, 2) }}</td>
                </tr>
                @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Total de productos: {{ $productos->count() }}</p>
        <p>Carlos Vásquez Ordoñez - Developer</p>
    </div>
</body>

</html>