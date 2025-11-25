import { router } from '@inertiajs/vue3'
import Swal from 'sweetalert2'

export function useCambioHabitacion() {
    async function confirmCambioHabitacion(habitacionActualId, asignacionId, nuevaHabitacionId) {
        if (!nuevaHabitacionId) {
            Swal.fire(
                'Elegí una habitación',
                'Seleccioná una nueva habitación antes de confirmar el cambio.',
                'warning'
            )
            return
        }

        const { value: motivo, isConfirmed } = await Swal.fire({
            title: '¿Confirmar cambio?',
            text: 'Se verificará disponibilidad y se moverá la reserva si no hay solapamientos.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Cambiar habitación',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',

            input: 'text',
            inputLabel: 'Motivo del cambio',
            inputPlaceholder: 'Ej: Preferencia del huésped',
            inputValidator: (value) => {
                if (!value) return 'El motivo es obligatorio.'
            },
        })

        if (!isConfirmed) return

        router.post(
            route('habitaciones.cambiar', habitacionActualId),
            {
                nueva_habitacion_id: nuevaHabitacionId,
                asignacion_id: asignacionId,
                motivo_cambio: motivo,
            },
            {
                onSuccess: () => {
                    Swal.fire(
                        'Éxito',
                        'La habitación fue cambiada correctamente.',
                        'success'
                    )
                },

                onError: (errors) => {
                    Swal.fire(
                        'No se pudo cambiar',
                        errors?.error ||
                            'La nueva habitación no está disponible en ese período.',
                        'error'
                    )
                },
                preserveScroll: true,
            }
        )
    }

    return { confirmCambioHabitacion }
}
