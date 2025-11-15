<script setup>
import { computed, onMounted, ref } from 'vue'
import StatCard from '../components/common/StatCard.vue'
import MonthlyAttendanceChart from '../components/dashboard/MonthlyAttendanceChart.vue'
import { fetchMonthlyReport, fetchTodayStats } from '../services/api'

const stats = ref(null)
const chartRows = ref([])
const month = ref(new Date().toISOString().slice(0, 7))
const classFilter = ref('')
const sectionFilter = ref('')
const loadingStats = ref(false)
const loadingChart = ref(false)
const error = ref('')

const presentHint = computed(() => {
  if (!stats.value) return 'Awaiting data'
  return `Out of ${stats.value.total} students`
})

const attendancePercent = computed(() => {
  if (!stats.value) return '—'
  return `${stats.value.attendance_percentage}%`
})

async function loadStats() {
  loadingStats.value = true
  error.value = ''
  try {
    stats.value = await fetchTodayStats({ class: classFilter.value || undefined })
  } catch (err) {
    error.value = err.message
  } finally {
    loadingStats.value = false
  }
}

async function loadChart() {
  loadingChart.value = true
  error.value = ''
  try {
    const response = await fetchMonthlyReport({
      month: month.value,
      class: classFilter.value || undefined,
      section: sectionFilter.value || undefined,
    })
    chartRows.value = response.data.map((row) => ({
      student: row.student,
      present: row.present,
      absent: row.absent,
      late: row.late,
      attendance_percentage: row.attendance_percentage,
    }))
  } catch (err) {
    error.value = err.message
  } finally {
    loadingChart.value = false
  }
}

function reloadAll() {
  loadStats()
  loadChart()
}

onMounted(() => {
  reloadAll()
})
</script>

<template>
  <div class="dashboard">
    <section class="panel filters">
      <div>
        <label>Month</label>
        <input v-model="month" type="month" />
      </div>
      <div>
        <label>Class</label>
        <input v-model="classFilter" placeholder="e.g. 10" />
      </div>
      <div>
        <label>Section</label>
        <input v-model="sectionFilter" placeholder="e.g. A" />
      </div>
      <button class="primary" @click="reloadAll">Refresh</button>
    </section>

    <section class="card-grid">
      <StatCard label="Present" :value="stats?.present ?? '—'" :hint="presentHint" accent="#22c55e" />
      <StatCard label="Absent" :value="stats?.absent ?? '—'" hint="Today" accent="#fb7185" />
      <StatCard label="Late" :value="stats?.late ?? '—'" hint="Today" accent="#facc15" />
      <StatCard label="Attendance %" :value="attendancePercent" hint="Today vs. target" accent="#38bdf8" />
    </section>

    <section class="panel">
      <header class="section-header">
        <div>
          <h2>Monthly Attendance</h2>
          <p class="muted">{{ month }} · {{ classFilter || 'All classes' }}</p>
        </div>
        <button class="ghost" @click="loadChart" :disabled="loadingChart">Reload Chart</button>
      </header>

      <div v-if="error" class="alert alert-error">{{ error }}</div>
      <div v-else>
        <MonthlyAttendanceChart :rows="chartRows" />
        <p v-if="!chartRows.length && !loadingChart" class="muted">No data for this selection.</p>
        <div v-if="loadingChart" class="loading">Loading chart…</div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.dashboard {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.filters {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 1rem;
  align-items: end;
}

label {
  font-size: 0.85rem;
  color: var(--color-text-soft);
  display: block;
  margin-bottom: 0.3rem;
}

input {
  width: 100%;
  border: 1px solid var(--color-border);
  border-radius: 0.6rem;
  padding: 0.55rem 0.75rem;
}

button.primary {
  border: none;
  border-radius: 0.6rem;
  background: var(--color-accent);
  color: #0a0a0a;
  padding: 0.6rem 1rem;
  font-weight: 600;
}

button.ghost {
  border: 1px solid var(--color-border);
  background: transparent;
  border-radius: 0.6rem;
  padding: 0.5rem 1rem;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.alert {
  margin-top: 1rem;
  padding: 0.8rem;
  border-radius: 0.6rem;
}

.alert-error {
  background: #ffe1e1;
  color: #8c1c1c;
}

.loading {
  margin-top: 1rem;
  color: var(--color-text-soft);
}

.card-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}
</style>
