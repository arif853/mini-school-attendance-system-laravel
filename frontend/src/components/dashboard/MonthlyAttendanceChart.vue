<script setup>
import { computed, watch } from 'vue'
import { Bar } from 'vue-chartjs'
import {
  Chart,
  BarElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend,
} from 'chart.js'

Chart.register(BarElement, CategoryScale, LinearScale, Tooltip, Legend)

const props = defineProps({
  rows: { type: Array, default: () => [] },
})

const labels = computed(() => props.rows.map((row) => row.student?.name ?? 'Student'))

const chartData = computed(() => ({
  labels: labels.value,
  datasets: [
    {
      label: 'Present',
      backgroundColor: '#4ade80',
      data: props.rows.map((row) => row.present),
      borderRadius: 8,
    },
    {
      label: 'Absent',
      backgroundColor: '#f87171',
      data: props.rows.map((row) => row.absent),
      borderRadius: 8,
    },
    {
      label: 'Late',
      backgroundColor: '#facc15',
      data: props.rows.map((row) => row.late),
      borderRadius: 8,
    },
  ],
}))

const options = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
    },
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        precision: 0,
      },
    },
  },
}

watch(
  () => props.rows,
  () => {
    // trigger re-render
  }
)
</script>

<template>
  <div class="chart-wrapper">
    <Bar :data="chartData" :options="options" />
  </div>
</template>

<style scoped>
.chart-wrapper {
  height: 320px;
}
</style>
