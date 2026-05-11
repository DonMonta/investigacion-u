import { ref, reactive, onMounted, onUnmounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { Pane } from 'tweakpane';

export function useBienvenidoLogic() {
    const router = useRouter();
  // --- ESTADO ---
  const density = ref(5);
  const distance = ref(0);
  const speed = ref(200);
  const isPaused = ref(false);
  const selectedCell = ref(null);
  const directions = ['top', 'right', 'bottom', 'left'];

  const totalImagenesLocales = 40; 
  const images = Array.from(
    { length: totalImagenesLocales }, 
    (_, i) => `/gallery/${i + 1}.jpg` // Ruta absoluta para archivos en public
  );

  const gridData = reactive({
    top: [], right: [], bottom: [], left: []
  });

  const totalCells = computed(() => density.value * density.value);
  let intervalId = null;
  let pane = null;

  // --- MÉTODOS ---
  const initGrid = () => {
    directions.forEach(dir => {
      gridData[dir] = Array.from({ length: totalCells.value }, () => ({ loaded: false, bg: '' }));
    });
    startImageInterval();
  };

  const startImageInterval = () => {
    if (intervalId) clearInterval(intervalId);
    intervalId = setInterval(() => {
      if (isPaused.value) return;
      const pending = [];
      directions.forEach(dir => {
        gridData[dir].forEach((cell, idx) => { if (!cell.loaded) pending.push({ dir, idx }); });
      });
      if (pending.length === 0) { clearInterval(intervalId); return; }
      const randomPick = pending[Math.floor(Math.random() * pending.length)];
      const randomImg = images[Math.floor(Math.random() * images.length)];
      gridData[randomPick.dir][randomPick.idx].bg = `url(${randomImg})`;
      gridData[randomPick.dir][randomPick.idx].loaded = true;
    }, speed.value);
  };

  const selectImage = (dir, index) => {
    selectedCell.value = { dir, index };
    isPaused.value = true;
  };

  const resumeInterval = () => {
    selectedCell.value = null;
    isPaused.value = false;
    startImageInterval();
  };

  // --- NUEVA FUNCIÓN DE ANIMACIÓN ---
  const animateDistance = (toValue, duration = 1000, onComplete = null) => {
    const fromValue = distance.value;
    const startTime = performance.now();

    const update = (currentTime) => {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);
      
      // Easing cúbico: 1 - pow(1 - progress, 3)
      const eased = 1 - Math.pow(1 - progress, 3);
      
      // Actualizamos el valor reactivo
      distance.value = fromValue + (toValue - fromValue) * eased;
      
      // Sincronizamos Tweakpane si existe
      if (progress < 1) {
        requestAnimationFrame(update);
      } else if (onComplete) {
        // Cuando llega al final (progress === 1), ejecutamos la navegación
        onComplete();
      }
    };

    requestAnimationFrame(update);
  };

  // --- MÉTODOS ACTUALIZADOS ---
  const toggleFocus = () => {
    if (distance.value < 50) {
      animateDistance(100, 1200, () => {
        // CAMBIA '/pedido' por la ruta de tu componente de destino
        router.push('/login'); 
      });
    } else {
      animateDistance(0, 1000);
    }
  };

  // --- LIFECYCLE ---
  onMounted(() => {
    initGrid();
    /*pane = new Pane();
    pane.addBinding(density, 'value', { min: 2, max: 8, step: 1, label: 'Size' }).on('change', initGrid);
    pane.addBinding(distance, 'value', { min: 0, max: 100, step: 1, label: 'Distance' });
    pane.addBinding(speed, 'value', { min: 50, max: 400, step: 50, label: 'Speed' }).on('change', startImageInterval);*/
  });

  onUnmounted(() => {
    if (intervalId) clearInterval(intervalId);
    if (pane) pane.dispose();
  });

  // Retornamos todo lo que el componente necesite usar
  return {
    density, distance, selectedCell, directions, gridData, totalCells,
    selectImage, resumeInterval, toggleFocus
  };
}