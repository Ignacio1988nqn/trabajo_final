import { reactive, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'

export function useReservaCreate() {
  /* ---------- Formulario ---------- */
  const form = useForm({
    huesped_id: '',
    estado: 'pendiente',
    observaciones: '',
    asignaciones: [
      { habitacion_id: '', fecha_inicio: '', fecha_fin: '' }
    ]
  })

  /* ---------- Disponibles por asignación ---------- */
  const disponibles = reactive({ 0: [] })   // mapa: idx -> habitaciones disponibles
  let debounceTimers = {}                   // para no spamear el endpoint

  function addAsignacion () {
    form.asignaciones.push({ habitacion_id: '', fecha_inicio: '', fecha_fin: '' })
    disponibles[form.asignaciones.length - 1] = []
  }

  function removeAsignacion (idx) {
    if (form.asignaciones.length === 1) return
    form.asignaciones.splice(idx, 1)

    // limpiar timer e índice eliminado
    clearTimeout(debounceTimers[idx])
    delete debounceTimers[idx]
    delete disponibles[idx]

    // reindexar conservando reactividad
    const nuevo = {}
    form.asignaciones.forEach((_, i) => {
      nuevo[i] = disponibles[i] ?? []
    })
    Object.keys(disponibles).forEach(k => delete disponibles[k])
    Object.assign(disponibles, nuevo)
  }

  /* ---------- Buscar disponibles (pide SIEMPRE inicio y fin) ---------- */
  async function fetchDisponibles (idx) {
    const a = form.asignaciones[idx]
    if (!a?.fecha_inicio || !a?.fecha_fin) return

    const check_in  = a.fecha_inicio
    const check_out = a.fecha_fin

    try {
      const { data } = await window.axios.get(route('reservas.disponibles'), {
        params: { check_in, check_out }
      })
      disponibles[idx] = data

      // si la selección actual ya no está disponible, vaciarla
      if (a.habitacion_id && !data.some(h => String(h.id) === String(a.habitacion_id))) {
        a.habitacion_id = ''
      }
    } catch (e) {
      console.error(e)
    }
  }

  /* ---------- Validación de solapes internos (misma habitación) ---------- */
  function rangosSeSolapan(a, b) {
    // regla [inicio, fin): solapan si iniA < finB y iniB < finA
    return a.fecha_inicio < b.fecha_fin && b.fecha_inicio < a.fecha_fin
  }

  /* ---------- Submit ---------- */
  function submit () {
    // 1) Campos obligatorios por fila
    for (let i = 0; i < form.asignaciones.length; i++) {
      const a = form.asignaciones[i]
      if (!a.habitacion_id || !a.fecha_inicio || !a.fecha_fin) {
        form.setError(`asignaciones.${i}.habitacion_id`, 'Completá habitación, inicio y fin')
        return
      }
      if (a.fecha_fin < a.fecha_inicio) {
        form.setError(`asignaciones.${i}.fecha_fin`, 'El fin debe ser posterior al inicio')
        return
      }
    }

    // 2) Evitar solapes internos de la MISMA habitación
    for (let i = 0; i < form.asignaciones.length; i++) {
      for (let j = i + 1; j < form.asignaciones.length; j++) {
        const A = form.asignaciones[i], B = form.asignaciones[j]
        if (A.habitacion_id && B.habitacion_id && A.habitacion_id === B.habitacion_id) {
          if (rangosSeSolapan(A, B)) {
            form.setError(`asignaciones.${i}.habitacion_id`, 'Se superpone con otra fila')
            form.setError(`asignaciones.${j}.habitacion_id`, 'Se superpone con otra fila')
            return
          }
        }
      }
    }

    form.post(route('reservas.store'))
  }

  /* ---------- Watchers: cuando cambian fechas, refrescar lista ---------- */
  function setupWatchers() {
    watch(
      () => form.asignaciones.map(a => [a.fecha_inicio, a.fecha_fin]),
      (arr) => {
        arr.forEach((_, idx) => {
          const a = form.asignaciones[idx]
          if (a?.fecha_inicio && a?.fecha_fin && a.fecha_inicio > a.fecha_fin) {
            a.fecha_fin = a.fecha_inicio
          }
          clearTimeout(debounceTimers[idx])
          debounceTimers[idx] = setTimeout(() => fetchDisponibles(idx), 250)
        })
      },
      { deep: true, immediate: true }
    )
  }

  /* ---------- Helpers UI ---------- */
  function isSeleccionDisponible(idx, habitacionId) {
    return (disponibles[idx] || []).some(h => String(h.id) === String(habitacionId))
  }

  return {
    form,
    disponibles,
    addAsignacion,
    removeAsignacion,
    fetchDisponibles,
    submit,
    setupWatchers,
    isSeleccionDisponible
  }
}
