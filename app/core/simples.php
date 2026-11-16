<?php
// Referenciales de un solo campo: depósitos, tipos de producto y unidades
defined('APP') or exit;

const SIMPLES = [
    'deposito' => [
        'tabla' => 'deposito', 'pk' => 'id_deposito', 'campo' => 'descrip', 'max' => 60,
        'titulo' => 'Depósitos', 'singular' => 'depósito', 'icono' => 'fas fa-warehouse',
        'ok_alta' => 'Depósito agregado correctamente.', 'ok_mod' => 'Depósito modificado correctamente.',
        'ok_baja' => 'Depósito eliminado correctamente.', 'no_existe' => 'El depósito no existe.',
        'duplicado' => 'Ya existe un depósito con la descripción',
        'en_uso' => 'No se puede eliminar el depósito porque tiene compras, ventas o stock registrados.',
        'usos' => '(SELECT COUNT(*) FROM stock s WHERE s.id_deposito = t.id_deposito AND s.cantidad > 0)', 'col_usos' => 'Productos con stock',
    ],
    'tipo_producto' => [
        'tabla' => 'tipo_producto', 'pk' => 'id_tipo_producto', 'campo' => 't_p_descrip', 'max' => 60,
        'titulo' => 'Tipos de producto', 'singular' => 'tipo de producto', 'icono' => 'fas fa-tags',
        'ok_alta' => 'Tipo de producto agregado correctamente.', 'ok_mod' => 'Tipo de producto modificado correctamente.',
        'ok_baja' => 'Tipo de producto eliminado correctamente.', 'no_existe' => 'El tipo de producto no existe.',
        'duplicado' => 'Ya existe un tipo de producto con la descripción',
        'en_uso' => 'No se puede eliminar el tipo de producto porque está asignado a productos.',
        'usos' => '(SELECT COUNT(*) FROM producto p WHERE p.id_tipo_producto = t.id_tipo_producto)', 'col_usos' => 'Productos',
    ],
    'u_medida' => [
        'tabla' => 'u_medida', 'pk' => 'id_u_medida', 'campo' => 'u_descrip', 'max' => 30,
        'titulo' => 'Unidades de medida', 'singular' => 'unidad de medida', 'icono' => 'fas fa-ruler',
        'ok_alta' => 'Unidad de medida agregada correctamente.', 'ok_mod' => 'Unidad de medida modificada correctamente.',
        'ok_baja' => 'Unidad de medida eliminada correctamente.', 'no_existe' => 'La unidad de medida no existe.',
        'duplicado' => 'Ya existe una unidad de medida con la descripción',
        'en_uso' => 'No se puede eliminar la unidad de medida porque está asignada a productos.',
        'usos' => '(SELECT COUNT(*) FROM producto p WHERE p.id_u_medida = t.id_u_medida)', 'col_usos' => 'Productos',
    ],
];
