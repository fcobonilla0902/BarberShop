<div class="campo campo--stack">
    <label for="nombre">Nombre</label>
    <input type="text" id="nombre" name="nombre" placeholder="Nombre del servicio" value="<?php echo s($servicio->nombre ?? ''); ?>">
</div>

<div class="campo campo--stack">
    <label for="descripcion">Descripción</label>
    <input type="text" id="descripcion" name="descripcion" placeholder="Descripción breve" value="<?php echo s($servicio->descripcion ?? ''); ?>">
</div>

<div class="campo campo--stack">
    <label for="categoria_servicio_id">Categoría</label>
    <input type="number" id="categoria_servicio_id" name="categoria_servicio_id" placeholder="Ej. 1" value="<?php echo s($servicio->categoria_servicio_id ?? '1'); ?>">
</div>

<div class="campo campo--stack">
    <label for="duracion_minutos">Duración en minutos</label>
    <input type="number" id="duracion_minutos" name="duracion_minutos" step="15" placeholder="30" value="<?php echo s($servicio->duracion_minutos ?? '30'); ?>">
</div>

<div class="campo campo--stack">
    <label for="precio_base_sin_iva">Precio sin IVA</label>
    <input type="number" id="precio_base_sin_iva" name="precio_base_sin_iva" step="0.01" placeholder="150.00" value="<?php echo s($servicio->precio_base_sin_iva ?? ''); ?>">
</div>

<div class="campo campo--stack">
    <label for="costo_estimado_sin_iva">Costo estimado sin IVA</label>
    <input type="number" id="costo_estimado_sin_iva" name="costo_estimado_sin_iva" step="0.01" placeholder="40.00" value="<?php echo s($servicio->costo_estimado_sin_iva ?? '0.00'); ?>">
</div>

<div class="campo campo--stack">
    <label for="iva_porcentaje">IVA %</label>
    <input type="number" id="iva_porcentaje" name="iva_porcentaje" step="0.01" placeholder="16.00" value="<?php echo s($servicio->iva_porcentaje ?? '16.00'); ?>">
</div>

<input type="hidden" name="activo" value="<?php echo s($servicio->activo ?? '1'); ?>">
<input type="hidden" name="created_at" value="<?php echo s($servicio->created_at ?? date('Y-m-d H:i:s')); ?>">
<input type="hidden" name="updated_at" value="<?php echo date('Y-m-d H:i:s'); ?>">
