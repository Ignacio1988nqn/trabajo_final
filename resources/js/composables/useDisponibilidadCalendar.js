// resources/js/composables/useCalendarioDisponibilidad.js
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'

export function useCalendarioDisponibilidad(props) {
  // columna de un día según ISO (YYYY-MM-DD)
  const colIndex = (iso) => props.daysArr.findIndex(d => d.iso === iso)

  const spanDays = (startIso, endIso) => {
    const s = colIndex(startIso)
    const e = colIndex(endIso)
    return e > s ? (e - s) : Math.max(1, props.days - s)
  }

  // 1 col fija para "Habitación" + N días
  const gridTemplate = computed(
    () => `260px repeat(${props.days}, minmax(60px, 1fr))`
  )

  // navegar cambiando filtros
  const go = (params) =>
    router.get(route('disponibilidad.index'), params, {
      preserveState: true,
      replace: true,
    })
  // const go = (params) =>
  //   router.get(route('disponibilidad.calendario'), params, {
  //     preserveState: false, // mejor forzar refresco completo de props
  //     replace: true,
  //   })
  const hoy = new Date().toISOString().slice(0, 10)

  const colorState = (state) =>
    ({
      pendiente:     'bg-yellow-400/80 text-yellow-900',
      ocupada:       'bg-rose-500/80 text-white',
      checkout:      'bg-blue-400/80 text-blue-900',
      cancelada:     'bg-gray-400/80 text-gray-900',
      limpieza:      'bg-amber-500/30 text-amber-800 border border-amber-500/40',
      mantenimiento: 'bg-purple-500/20 text-purple-900 border border-purple-500/40',
    }[state] || 'bg-slate-300/80 text-slate-900')

  return {
    colIndex,
    spanDays,
    gridTemplate,
    go,
    hoy,
    colorState,
  }
}