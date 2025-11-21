import { reactive, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'

export function useReservaCreate(props) {
  /* ---------- Formulario ---------- */
  const form = useForm({
    huesped_id: '',
    estado: 'pendiente',
    observaciones: '',
    asignaciones: [
      {
    habitacion_id: '',
    fecha_inicio: props.initialFrom || '',
    fecha_fin:    props.initialTo   || '',
      },
  ], 
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
    return a.fecha_inicio < b.fecha_fin && b.fecha_inicio < a.fecha_fin;
  }

  /* ---------- Submit ---------- */
   function submit() {
  // Limpiar errores previos
  form.clearErrors();

  // 1. Validar que todas las filas estén completas
  let hasError = false;
  for (let i = 0; i < form.asignaciones.length; i++) {
    const a = form.asignaciones[i];
    if (!a.habitacion_id || !a.fecha_inicio || !a.fecha_fin) {
      form.setError(`asignaciones.${i}.habitacion_id`, 'Completá habitación, check-in y check-out');
      hasError = true;
    }
    if (a.fecha_fin <= a.fecha_inicio) {
      form.setError(`asignaciones.${i}.fecha_fin`, 'Check-out debe ser posterior al check-in');
      hasError = true;
    }
  }
  if (hasError) return;

  // 2. Validar solapamiento de la MISMA habitación entre filas (igual que tu backend)
  for (let i = 0; i < form.asignaciones.length; i++) {
    for (let j = i + 1; j < form.asignaciones.length; j++) {
      const A = form.asignaciones[i];
      const B = form.asignaciones[j];

      if (A.habitacion_id && B.habitacion_id && A.habitacion_id === B.habitacion_id) {
        if (rangosSeSolapan(A, B)) {
          form.setError(`asignaciones.${i}.habitacion_id`, 'Habitación duplicada con fechas superpuestas');
          form.setError(`asignaciones.${j}.habitacion_id`, 'Habitación duplicada con fechas superpuestas');
          return;
        }
      }
    }
  }

  // 3. Payload limpio y correcto
  const payload = {
    huesped_id: form.huesped_id ? Number(form.huesped_id) : null,
    estado: form.estado || 'pendiente',
    observaciones: form.observaciones?.trim() || null,
    asignaciones: form.asignaciones.map(a => ({
      habitacion_id: Number(a.habitacion_id),
      fecha_inicio: a.fecha_inicio,
      fecha_fin: a.fecha_fin,
    }))
  };

  // 4. Enviar
  form.post(route('reservas.store'), {
    data: payload,
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      Swal.fire({
        icon: 'success',
        title: '¡Reserva creada!',
        text: 'Todo perfecto.',
        timer: 2000,
        showConfirmButton: false
      });
    },
    onError: (errors) => {
      // Esto es clave: los errores del backend ahora se muestran correctamente
      console.log('Errores del servidor:', errors);
      Swal.fire({
        icon: 'error',
        title: 'No se pudo guardar',
        text: 'Revisá los campos marcados en rojo',
      });
    }
  });
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
