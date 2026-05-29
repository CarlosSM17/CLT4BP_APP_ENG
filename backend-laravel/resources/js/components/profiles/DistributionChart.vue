<!-- src/components/profiles/DistributionChart.vue -->
<template>
  <div class="distribution-chart">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps({
  data: {
    type: Object,
    required: true
  }
});

const chartCanvas = ref(null);
let chartInstance = null;

const createChart = () => {
  if (chartInstance) {
    chartInstance.destroy();
  }

  const ctx = chartCanvas.value.getContext('2d');

  chartInstance = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['Alto', 'Medio', 'Bajo'],
      datasets: [{
        data: [
          props.data.alto || 0,
          props.data.medio || 0,
          props.data.bajo || 0
        ],
        backgroundColor: [
          'rgb(34, 197, 94)',  // green
          'rgb(251, 191, 36)', // yellow
          'rgb(239, 68, 68)'   // red
        ],
        borderWidth: 2,
        borderColor: '#fff'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: {
          position: 'bottom'
        }
      }
    }
  });
};

onMounted(() => {
  createChart();
});

watch(() => props.data, () => {
  createChart();
}, { deep: true });
</script>
