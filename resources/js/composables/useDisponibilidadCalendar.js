// resources/js/composables/useCalendarioDisponibilidad.js
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'
import { router } from '@inertiajs/vue3'

export function useCalendarioDisponibilidad(props) {
  // ---------- helpers de calendario ----------
  const colIndex = (iso) => props.daysArr.findIndex(d => d.iso === iso)

  const spanDays = (startIso, endIso) => {
    const s = colIndex(startIso)
    const e = colIndex(endIso)
    return e > s ? (e - s) : Math.max(1, props.days - s)
  }

  const gridTemplate = computed(() =>
    `260px repeat(${props.days}, minmax(60px, 1fr))`
  )

  const go = (params) =>
    router.get(route('disponibilidad.calendario'), params, { preserveState: true, replace: true })

  const hoy = new Date().toISOString().slice(0,10)

  const colorState = (state) => ({
    'pendiente':     'bg-yellow-400/80 text-yellow-900',
    'ocupada':       'bg-rose-500/80 text-white',
    'checkout':      'bg-blue-400/80 text-blue-900',
    'cancelada':     'bg-gray-400/80 text-gray-900',
    'limpieza':      'bg-amber-500/30 text-amber-800 border border-amber-500/40',
    'mantenimiento': 'bg-purple-500/20 text-purple-900 border border-purple-500/40',
  }[state] || 'bg-slate-300/80 text-slate-900')

  // ---------- selección de rango (encapsulado) ----------
  const selecting = ref(false)
  const selRowId  = ref(null)
  const selStart  = ref(null)   // índice inicio
  const selEnd    = ref(null)   // índice fin (inclusive)

  const dayIndexByIso = (iso) => props.daysArr.findIndex(d => d.iso === iso)
  const isoByIndex    = (idx) => props.daysArr[idx]?.iso ?? null

  function onCellMouseDown(r, dIdx) {
    selecting.value = true
    selRowId.value  = r?.id ?? null
    selStart.value  = dIdx
    selEnd.value    = dIdx
  }

  function onCellMouseEnter(r, dIdx) {
    if (!selecting.value) return
    // Si querés limitar a la misma fila:
    // if (selRowId.value && r?.id !== selRowId.value) return
    selEnd.value = dIdx
  }

  function finishSelection() {
    if (!selecting.value) return
    selecting.value = false

    const a = Math.min(selStart.value, selEnd.value)
    const b = Math.max(selStart.value, selEnd.value)

    const fromIso = isoByIndex(a)
    // calendario usa end exclusivo → sumo 1 para incluir el último día
    const toIso   = isoByIndex(b + 1) || isoByIndex(b)

    router.get(route('reservas.create'), {
      from: fromIso,
      to: toIso,
      room_id: selRowId.value, // opcional
    }, { preserveState: true })
  }

  function onGlobalMouseUp() { finishSelection() }

  onMounted(() => {
    if (typeof window !== 'undefined') {
      window.addEventListener('mouseup', onGlobalMouseUp)
    }
  })
  onBeforeUnmount(() => {
    if (typeof window !== 'undefined') {
      window.removeEventListener('mouseup', onGlobalMouseUp)
    }
  })

  function isCellSelected(dIdx /*, r */) {
    if (!selecting.value || selStart.value == null || selEnd.value == null) return false
    const a = Math.min(selStart.value, selEnd.value)
    const b = Math.max(selStart.value, selEnd.value)
    return dIdx >= a && dIdx <= b
  }

  return {
    // calendario
    colIndex, spanDays, gridTemplate, go, hoy, colorState,
    // selección
    selecting, onCellMouseDown, onCellMouseEnter, finishSelection, isCellSelected,
  }
}
