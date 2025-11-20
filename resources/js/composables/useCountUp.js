import { ref, watch, onMounted } from "vue";

/**
 * Anima un número desde un valor anterior hasta uno nuevo.
 * sourceRef: ref / toRef que contiene el valor "real"
 * duration: duración de la animación en ms
 */
export function useCountUp(sourceRef, duration = 600) {
  const display = ref(0);

  function animate(from, to) {
    const start = Number(from) || 0;
    const target = Number(to) || 0;
    const startTime = performance.now();

    function step(now) {
      const t = Math.min((now - startTime) / duration, 1);
      display.value = Math.round(start + (target - start) * t);
      if (t < 1) requestAnimationFrame(step);
    }

    requestAnimationFrame(step);
  }

  // Primera vez que se muestra el componente
  onMounted(() => {
    animate(0, sourceRef.value);
  });

  // Cada vez que cambie el valor “real”
  watch(
    sourceRef,
    (newVal, oldVal) => {
      animate(oldVal, newVal);
    }
  );

  return { display };
}
