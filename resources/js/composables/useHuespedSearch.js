// resources/js/composables/useHuespedSearch.js
import { reactive } from 'vue'

export function useHuespedSearch(initialText = '', initialId = '') {
  const state = reactive({
    busq: initialText,         // <-- string plano
    opciones: [],              // [{ id, display }]
    cargando: false,
    abierto: false,
    seleccionadoId: initialId, // id real
    seleccionadoDisplay: '',
    highlighted: -1,
  })

  let timer = null

  async function fetchHuespedes(q) {
    if (!q || q.trim().length < 2) {
      state.opciones = []
      state.abierto = false
      return
    }
    state.cargando = true
    try {
      const { data } = await window.axios.get(
        route('reservas.buscarHuespedes'),
        { params: { q } }
      )
      state.opciones = data
      state.abierto  = data.length > 0
      state.highlighted = data.length ? 0 : -1
    } catch (e) {
      console.error('buscarHuespedes error:', e)
      state.opciones = []
      state.abierto  = false
    } finally {
      state.cargando = false
    }
  }

  function onInput() {
    state.seleccionadoId = ''
    state.seleccionadoDisplay = ''
    clearTimeout(timer)
    timer = setTimeout(() => fetchHuespedes(state.busq), 300)
  }

  function seleccionar(op) {
    state.seleccionadoId = op.id
    state.seleccionadoDisplay = op.display
    state.busq = op.display          // <-- string seguro
    state.abierto = false
    state.opciones = []
    state.highlighted = -1
  }

  function limpiar() {
    state.seleccionadoId = ''
    state.seleccionadoDisplay = ''
    state.busq = ''
    state.opciones = []
    state.abierto = false
    state.highlighted = -1
  }

  function blurConDelay() {
    setTimeout(() => { state.abierto = false }, 150)
  }

  function onKeydown(e) {
    if (!state.abierto || !state.opciones.length) return
    if (e.key === 'ArrowDown') {
      e.preventDefault()
      state.highlighted = (state.highlighted + 1) % state.opciones.length
    } else if (e.key === 'ArrowUp') {
      e.preventDefault()
      state.highlighted = (state.highlighted - 1 + state.opciones.length) % state.opciones.length
    } else if (e.key === 'Enter') {
      e.preventDefault()
      if (state.highlighted >= 0) seleccionar(state.opciones[state.highlighted])
    } else if (e.key === 'Escape') {
      state.abierto = false
    }
  }

  return { state, onInput, seleccionar, limpiar, blurConDelay, onKeydown }
}
