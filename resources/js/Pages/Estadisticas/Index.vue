<script setup>
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { onMounted } from 'vue'

const props = defineProps({
    estados: Object,
    ocupacionActual: Number,
    diaActual: Number,
    historico: Object
})

onMounted(() => {
    // Pie: estado actual
    const dataEstados = Object.entries(props.estados || {}).map(([estado, total]) => ({
        name: estado,
        y: total
    }))
    makePieChartEstados(dataEstados, 'graficoEstados', 'Estados de habitaciones', 'Distribución actual de todas las habitaciones')

    // Pie: ocupación actual
    makePieChartOcupacion(props.ocupacionActual, 'graficoOcupacion',
        `Ocupación mensual (hasta el día ${props.diaActual})`,
        'Porcentaje del mes en curso')

    // Line: histórico 12 meses previos
    makeLineChart([props.historico.data], 'graficoHistorico', props.historico.labels)
})

function makePieChartEstados(data, id, title, subtitle) {
    Highcharts.chart(id, {
        chart: { type: 'pie' },
        title: { text: title },
        subtitle: { text: subtitle, align: 'center' },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                },
                showInLegend: true
            }
        },
        series: [{ name: 'Habitaciones', colorByPoint: true, data }]

    })
}

function makePieChartOcupacion(valor, id, title, subtitle) {
    Highcharts.chart(id, {
        chart: { type: 'pie' },
        title: { text: title },
        subtitle: { text: subtitle, align: 'center' },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                },
                showInLegend: true
            }
        },
        series: [{
            name: 'Ocupación',
            colorByPoint: true,
            data: [
                { name: 'Ocupadas', y: valor, color: '#f87171' }, // rojo suave
                { name: 'Disponibles', y: 100 - valor, color: '#4ade80' } // verde suave
            ]
        }]
    })
}

function makeLineChart(data, id, labels) {
    Highcharts.chart(id, {
        title: { text: 'Ocupación mensual (últimos 12 meses completos)' },
        subtitle: { text: 'Porcentaje de ocupación promedio por mes' },
        yAxis: { title: { text: 'Porcentaje (%)' } },
        xAxis: { categories: labels },
        tooltip: { valueSuffix: '%' },
        plotOptions: {
            series: {
                cursor: 'pointer',          
                marker: {
                    enabled: true,
                    radius: 5,
                    states: {
                        hover: {
                            enabled: true,
                            fillColor: '#1d4ed8',
                            lineColor: '#fff',
                            lineWidth: 2
                        }
                    }
                }
            },
            line: {
                lineWidth: 3
            }
        },
        series: [{
            name: 'Ocupación',
            data: data[0],
            color: '#3b82f6' // azul
        }],
        legend: { enabled: false }
    })
}
</script>

<template>

    <Head title="Estadísticas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">Estadísticas</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                <div id="graficoEstados" class="bg-white p-4 shadow rounded-lg"></div>
                <div id="graficoOcupacion" class="bg-white p-4 shadow rounded-lg"></div>
            </div>

            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-8">
                <div id="graficoHistorico" class="bg-white p-4 shadow rounded-lg"></div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
