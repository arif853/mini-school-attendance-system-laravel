<script setup>
import { computed, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useAttendanceStore } from '../stores/attendance'

const store = useAttendanceStore()
const {
  roster,
  classFilter,
  sectionFilter,
  date,
  statusMap,
  noteMap,
  loading,
  submitting,
  message,
  error,
} = storeToRefs(store)

const summary = computed(() => store.attendanceSummary)
const classes = ['9', '10', '11', '12']
const sections = ['A', 'B', 'C']
const statuses = [
  { value: 'present', label: 'Present' },
  { value: 'absent', label: 'Absent' },
  { value: 'late', label: 'Late' },
]

function loadRoster() {
  store.loadRoster()
}

function submit() {
  store.submitAttendance('Vue Client')
}

function bulk(status) {
  store.applyBulk(status)
}

onMounted(() => {
  if (!roster.value.length) {
    loadRoster()
  }
})
</script>

<template>
  <div class="page-stack">
    <section class="panel" aria-labelledby="filters">
      <header class="section-header">
        <div>
          <h2 id="filters">Attendance Recorder</h2>
          <p class="muted">Select a class/section then load roster.</p>
        </div>
        <button class="ghost" @click="loadRoster">Reload</button>
      </header>

      <div class="filters">
        <label>
          Class
          <select v-model="classFilter">
            <option disabled value="">Select class</option>
            <option v-for="c in classes" :key="c" :value="c">{{ c }}</option>
          </select>
        </label>
        <label>
          Section
          <select v-model="sectionFilter">
            <option disabled value="">Select section</option>
            <option v-for="s in sections" :key="s" :value="s">{{ s }}</option>
          </select>
        </label>
        <label>
          Date
          <input v-model="date" type="date" />
        </label>
        <div class="actions">
          <button class="primary" @click="loadRoster" :disabled="loading">Load Roster</button>
        </div>
      </div>
    </section>

    <section class="panel">
      <header class="section-header">
        <div>
          <h3>Summary</h3>
          <p class="muted">{{ summary.present }} present · {{ summary.absent }} absent · {{ summary.late }} late</p>
        </div>
        <span class="badge">{{ summary.percentage }}% present</span>
      </header>

      <div class="bulk-buttons">
        <button @click="bulk('present')">Mark all Present</button>
        <button @click="bulk('absent')">Mark all Absent</button>
        <button @click="bulk('late')">Mark all Late</button>
      </div>

      <div v-if="error" class="alert alert-error">{{ error }}</div>
      <div v-if="message" class="alert alert-success">{{ message }}</div>

      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Status</th>
              <th>Note</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!loading && !roster.length">
              <td colspan="3">Load a roster to begin.</td>
            </tr>
            <tr v-for="student in roster" :key="student.id">
              <td>
                <p class="student-name">{{ student.name }}</p>
                <p class="muted">{{ student.student_id }}</p>
              </td>
              <td>
                <select :value="statusMap[student.id]" @change="store.setStatus(student.id, $event.target.value)">
                  <option v-for="option in statuses" :key="option.value" :value="option.value">
                    {{ option.label }}
                  </option>
                </select>
              </td>
              <td>
                <input
                  :value="noteMap[student.id]"
                  type="text"
                  placeholder="Optional note"
                  @input="store.setNote(student.id, $event.target.value)"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <footer class="submit-row">
        <button class="primary" :disabled="submitting || !roster.length" @click="submit">
          {{ submitting ? 'Submitting…' : 'Submit Attendance' }}
        </button>
      </footer>
    </section>

    <div v-if="loading" class="loading">Loading roster…</div>
  </div>
</template>

<style scoped>
.page-stack {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.filters {
  margin-top: 1rem;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 1rem;
  align-items: end;
}

label {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
  font-size: 0.85rem;
  color: var(--color-text-soft);
}

select,
input {
  border: 1px solid var(--color-border);
  border-radius: 0.6rem;
  padding: 0.55rem 0.75rem;
  background: var(--color-background);
}

.actions {
  display: flex;
  justify-content: flex-end;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.bulk-buttons {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  margin: 1rem 0;
}

.bulk-buttons button {
  border: 1px dashed var(--color-border);
  border-radius: 0.6rem;
  padding: 0.4rem 0.8rem;
  background: transparent;
}

.badge {
  padding: 0.3rem 0.7rem;
  border-radius: 999px;
  background: var(--color-accent-soft);
}

.table-wrapper {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th,
td {
  text-align: left;
  padding: 0.75rem 0.5rem;
  border-bottom: 1px solid var(--color-border);
}

.student-name {
  font-weight: 600;
}

.submit-row {
  margin-top: 1rem;
  display: flex;
  justify-content: flex-end;
}

button.primary {
  background: var(--color-accent);
  color: #0a0a0a;
  border: none;
  border-radius: 0.6rem;
  padding: 0.7rem 1.5rem;
  font-weight: 600;
}

button.ghost {
  border: 1px solid var(--color-border);
  background: transparent;
  border-radius: 0.6rem;
  padding: 0.5rem 1rem;
}

.loading {
  color: var(--color-text-soft);
}

.alert {
  padding: 0.8rem;
  border-radius: 0.6rem;
  margin-bottom: 1rem;
}

.alert-error {
  background: #ffe1e1;
  color: #8c1c1c;
}

.alert-success {
  background: #e1ffe9;
  color: #1b6a32;
}
</style>
