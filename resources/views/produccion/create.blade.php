@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="multiProduccionForm()">
    
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Registrar Turnos de Producción</h1>
            <p class="text-xs text-gray-500 mt-0.5">Seleccione los turnos trabajados en la fecha para desplegar y rellenar sus formularios.</p>
        </div>
        <a href="{{ route('produccion.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition-all">
            ← Volver al Listado
        </a>
    </div>

    <form action="{{ route('produccion.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- FECHAS GENERALES -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Fecha de Registro (Hoy)</label>
                <input type="date" name="fecha_registro" value="{{ $fechaRegistro }}" readonly class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm font-mono text-gray-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Fecha de Producción (Ayer / Editable)</label>
                <input type="date" name="fecha_produccion" value="{{ $fechaProduccionSugerida }}" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-fenix focus:outline-none bg-white">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Seleccionar Turnos a Registrar</label>
                <div class="flex items-center space-x-4 mt-2">
                    <label class="flex items-center space-x-2 cursor-pointer bg-amber-50 px-3 py-2 rounded-xl border border-amber-200">
                        <input type="checkbox" name="turnos_activos[]" value="DIA" x-model="activoDia" class="text-amber-600 focus:ring-amber-500 rounded">
                        <span class="text-xs font-bold text-amber-900"> DIA ({{ $nroDia }})</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer bg-indigo-50 px-3 py-2 rounded-xl border border-indigo-200">
                        <input type="checkbox" name="turnos_activos[]" value="NOCHE" x-model="activoNoche" class="text-indigo-600 focus:ring-indigo-500 rounded">
                        <span class="text-xs font-bold text-indigo-900"> NOCHE ({{ $nroNoche }})</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- FORMULARIO TURNO DIA (Usa x-if)            -->
        <!-- ========================================== -->
        <template x-if="activoDia">
            <div class="bg-white p-8 rounded-2xl shadow-sm border-2 border-amber-300 space-y-6">
                <div class="flex items-center justify-between border-b border-amber-100 pb-3">
                    <h2 class="text-base font-bold text-amber-900 flex items-center space-x-2">
                        <span>☀️ Formulario - Turno DIA</span>
                        <span class="text-xs bg-amber-100 text-amber-800 px-2.5 py-0.5 rounded-full font-mono">{{ $nroDia }}</span>
                    </h2>
                </div>
                
                @include('produccion._form_partial', ['prefix' => 'dia', 'turnoLabel' => 'DIA'])
            </div>
        </template>

        <!-- ========================================== -->
        <!-- FORMULARIO TURNO NOCHE (Usa x-if)          -->
        <!-- ========================================== -->
        <template x-if="activoNoche">
            <div class="bg-white p-8 rounded-2xl shadow-sm border-2 border-indigo-300 space-y-6">
                <div class="flex items-center justify-between border-b border-indigo-100 pb-3">
                    <h2 class="text-base font-bold text-indigo-900 flex items-center space-x-2">
                        <span>🌙 Formulario - Turno NOCHE</span>
                        <span class="text-xs bg-indigo-100 text-indigo-800 px-2.5 py-0.5 rounded-full font-mono">{{ $nroNoche }}</span>
                    </h2>
                </div>

                @include('produccion._form_partial', ['prefix' => 'noche', 'turnoLabel' => 'NOCHE'])
            </div>
        </template>

        <!-- BOTÓN GUARDAR GENERAL -->
        <div class="flex justify-end pt-2">
            <button type="submit" class="px-6 py-3.5 bg-fenix hover:bg-fenix-dark text-white rounded-xl text-sm font-bold shadow-lg transition-all cursor-pointer">
                Guardar Registros de Producción
            </button>
        </div>
    </form>
</div>

<!-- SCRIPT DE CÁLCULOS DINÁMICOS -->
<script>
function multiProduccionForm() {
    return {
        activoDia: true,   // Arranca marcado por defecto
        activoNoche: false
    }
}

function turnoForm(prefix) {
    return {
        productoId: '',
        presentacion: '',
        empaques: {},
        pesoNeto: 0,
        factorMillares: 0,
        cavidades: 0,
        cantidad: 0,
        scrap: 0,
        totalKg: 0,
        totalMillares: 0,
        porcentajeScrapText: '0.00 %',
        maquinaParada: false,
        horaInicio: '',
        horaFin: '',
        horasOperativasText: '12.00 Horas',

        actualizarParametros(event) {
            let select = event.target;
            let option = select.options[select.selectedIndex];
            if (option && option.value) {
                let unidadMedida = (option.getAttribute('data-unidad') || 'CAJA').toUpperCase();
                
                if (unidadMedida.includes('SACO')) this.presentacion = 'SACO';
                else if (unidadMedida.includes('JUMBO')) this.presentacion = 'JUMBO';
                else this.presentacion = 'CAJA';

                try {
                    this.empaques = JSON.parse(option.getAttribute('data-empaques') || '{}');
                } catch(e) {
                    this.empaques = {};
                }

                let empaqueActivo = this.empaques[this.presentacion] || Object.values(this.empaques)[0] || {};
                
                this.pesoNeto = parseFloat(empaqueActivo.factor_peso_kg) || 0;
                this.factorMillares = parseFloat(empaqueActivo.factor_millares) || 0;
                this.cavidades = parseInt(option.getAttribute('data-cavidades')) || 0;

                this.calcularTotales();
            } else {
                this.presentacion = '';
                this.pesoNeto = 0;
                this.factorMillares = 0;
                this.cavidades = 0;
                this.totalKg = 0;
                this.totalMillares = 0;
            }
        },

        calcularTotales() {
            this.totalKg = parseFloat((this.cantidad * this.pesoNeto).toFixed(2));
            this.totalMillares = parseFloat((this.cantidad * this.factorMillares).toFixed(4));

            let totalConScrap = this.totalKg + parseFloat(this.scrap || 0);
            if (totalConScrap > 0) {
                let pScrap = (parseFloat(this.scrap || 0) / totalConScrap) * 100;
                this.porcentajeScrapText = pScrap.toFixed(2) + ' %';
            } else {
                this.porcentajeScrapText = '0.00 %';
            }
        },

        calcularHorasOperativas() {
            if (this.horaInicio && this.horaFin) {
                let [h1, m1] = this.horaInicio.split(':').map(Number);
                let [h2, m2] = this.horaFin.split(':').map(Number);
                
                let minutos1 = (h1 * 60) + m1;
                let minutos2 = (h2 * 60) + m2;
                
                let diffMinutos = minutos2 - minutos1;
                if (diffMinutos > 0) {
                    let horasParada = diffMinutos / 60;
                    let operativasDecimal = Math.max(0, 12 - horasParada);
                    
                    // Convertir a formato de horas y minutos para mostrar
                    let hrs = Math.floor(operativasDecimal);
                    let mins = Math.round((operativasDecimal - hrs) * 60);
                    
                    this.horasOperativasText = hrs + 'h ' + mins + 'm (' + diffMinutos + ' min parados)';
                } else {
                    this.horasOperativasText = '12h 00m (Rango inválido)';
                }
            }
        }
    }
}
</script>
@endsection