<div x-data="turnoForm('{{ $prefix }}')" class="space-y-6">
    
    <!-- SELECCIÓN DE RECURSOS -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Producto *</label>
            <select name="{{ $prefix }}[producto_id]" @change="actualizarParametros($event)" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-fenix focus:outline-none bg-white">
                <option value="">-- Seleccionar Producto --</option>
                @foreach($productos as $prod)
                    @php
                        $empaquesJson = $prod->matrizEmpaques->keyBy('presentacion')->toJson();
                        
                        // Obtenemos el número de cavidades directamente de la relación con el molde
                        $numCavidades = $prod->molde->numero_cavidades ?? $prod->nro_cavidades ?? $prod->cavidades ?? 0;
                    @endphp
                    <option value="{{ $prod->id }}" 
                        data-unidad="{{ $prod->unidad_medida ?? 'CAJA' }}"
                        data-empaques="{{ $empaquesJson }}"
                        data-cavidades="{{ $numCavidades }}">
                        {{ $prod->nombre ?? $prod->codigo }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Operario / Encargado *</label>
            <select name="{{ $prefix }}[operario_id]" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-fenix focus:outline-none bg-white">
                <option value="">-- Seleccionar Operario --</option>
                @foreach($operarios as $op)
                    <option value="{{ $op->id }}">{{ $op->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Máquina / Inyectora *</label>
            <select name="{{ $prefix }}[maquina_id]" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-fenix focus:outline-none bg-white">
                <option value="">-- Seleccionar Máquina --</option>
                @foreach($maquinas as $maq)
                    <option value="{{ $maq->id }}">{{ $maq->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Resina *</label>
            <select name="{{ $prefix }}[resina_id]" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-fenix focus:outline-none bg-white">
                <option value="">-- Seleccionar Resina --</option>
                @foreach($resinas as $res)
                    <option value="{{ $res->id }}">{{ $res->nombre }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <hr class="border-gray-100">

    <!-- PARÁMETROS Y CÁLCULOS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Presentación</label>
            <input type="text" name="{{ $prefix }}[presentacion]" x-model="presentacion" readonly class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-semibold text-gray-700">
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Cantidad Producida *</label>
            <input type="number" step="0.01" name="{{ $prefix }}[cantidad_producida]" x-model.number="cantidad" @input="calcularTotales()" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-fenix focus:outline-none bg-white">
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Total Kilos (Calculado)</label>
            <input type="number" step="0.01" name="{{ $prefix }}[total_kg]" x-model.number="totalKg" readonly class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-bold text-fenix">
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Total Millares (Calculado)</label>
            <input type="number" step="0.0001" name="{{ $prefix }}[total_millares]" x-model.number="totalMillares" readonly class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-bold text-gray-700">
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Scrap (Kg)</label>
            <input type="number" step="0.01" name="{{ $prefix }}[scrap]" x-model.number="scrap" @input="calcularTotales()" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-fenix focus:outline-none bg-white" value="0">
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">% de Scrap</label>
            <input type="text" x-model="porcentajeScrapText" readonly class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-bold text-red-600">
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Ciclo</label>
            <input type="number" step="0.01" name="{{ $prefix }}[ciclo]" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-fenix focus:outline-none bg-white">
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Cavidades (Matriz)</label>
            <input type="number" name="{{ $prefix }}[cavidades]" x-model.number="cavidades" readonly class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-700">
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Cavidades Trabajadas</label>
            <input type="number" name="{{ $prefix }}[cavidades_trabajadas]" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-fenix focus:outline-none bg-white">
        </div>
    </div>

    <div>
        <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Motivo del Scrap</label>
        <select name="{{ $prefix }}[motivo_scrap]" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-fenix focus:outline-none bg-white">
            <option value="">-- Sin Novedad / Ninguno --</option>
            <option value="Crudo">Crudo</option>
            <option value="Puntos negros">Puntos negros</option>
            <option value="Arranque de maquina">Arranque de máquina</option>
        </select>
    </div>

    <!-- MÁQUINA PARADA -->
    <div class="bg-gray-50 p-5 rounded-2xl border border-gray-200 space-y-4">
        <label class="flex items-center space-x-3 cursor-pointer">
            <input type="checkbox" name="{{ $prefix }}[maquina_parada]" value="1" x-model="maquinaParada" class="w-5 h-5 text-fenix rounded focus:ring-fenix">
            <span class="text-sm font-bold text-gray-800">¿Hubo Máquina Parada en el turno de {{ $turnoLabel }}?</span>
        </label>

        <div x-show="maquinaParada" class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Hora Inicio Parada</label>
                <input type="time" name="{{ $prefix }}[hora_inicio_parada]" x-model="horaInicio" @change="calcularHorasOperativas()" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Hora Fin Parada</label>
                <input type="time" name="{{ $prefix }}[hora_fin_parada]" x-model="horaFin" @change="calcularHorasOperativas()" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white">
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Horas Operativas</label>
                <input type="text" x-model="horasOperativasText" readonly class="w-full bg-emerald-50 border border-emerald-200 text-emerald-800 font-bold rounded-xl px-3 py-2 text-sm">
            </div>
        </div>
    </div>
</div>