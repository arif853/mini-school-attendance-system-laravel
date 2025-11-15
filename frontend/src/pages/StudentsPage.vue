<script setup>
import { computed, onMounted, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { useStudentsStore } from '../stores/students'

const store = useStudentsStore()
const { items, pagination, filters, loading, error } = storeToRefs(store)

const searchTerm = ref('')
const classFilter = ref('')
const sectionFilter = ref('')

const pageNumbers = computed(() => {
  const pages = []
  for (let i = 1; i <= pagination.value.last_page; i += 1) {
    pages.push(i)
  }
  return pages
})

function applyFilters(page = 1) {
  store.loadStudents({
    page,
    search: searchTerm.value || undefined,
    class: classFilter.value || undefined,
    section: sectionFilter.value || undefined,
  })
}

onMounted(() => {
  searchTerm.value = filters.value.search
  classFilter.value = filters.value.class
  sectionFilter.value = filters.value.section
  if (!items.value.length) {
    applyFilters()
  }
})
</script>

<template>
  <div class="page-stack">
    <section class="panel filters">
      <div>
        <label>Search</label>
        <input v-model="searchTerm" type="search" placeholder="Name or student ID" />
      </div>
      <div>
        <label>Class</label>
        <input v-model="classFilter" type="text" placeholder="e.g. 10" />
      </div>
      <div>
        <label>Section</label>
        <input v-model="sectionFilter" type="text" placeholder="e.g. A" />
      </div>
      <button class="primary" @click="applyFilters()">Apply</button>
    </section>

    <section class="panel">
      <header class="section-header">
        <div>
          <h2>Students</h2>
          <p class="muted">Showing {{ pagination.total }} students</p>
        </div>
        <button class="ghost" @click="applyFilters(pagination.current_page)">Refresh</button>
      </header>

      <div v-if="error" class="alert alert-error">{{ error }}</div>
      <div v-else class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>ID</th>
              <th>Class</th>
              <th>Section</th>
              <th>Photo</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!loading && !items.length">
              <td colspan="5">No records found.</td>
            </tr>
            <tr v-for="student in items" :key="student.id">
              <td>{{ student.name }}</td>
              <td>{{ student.student_id }}</td>
              <td>{{ student.class }}</td>
              <td>{{ student.section }}</td>
              <td>
                <span v-if="student.photo_path" class="badge">Uploaded</span>
                <span v-else class="badge ghost">Pending</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="pagination" v-if="pagination.last_page > 1">
        <button :disabled="pagination.current_page === 1" @click="applyFilters(pagination.current_page - 1)">
          Prev
        </button>
        <button
          v-for="page in pageNumbers"
          :key="page"
          :class="{ active: page === pagination.current_page }"
          @click="applyFilters(page)"
        >
          {{ page }}
        </button>
        <button
          :disabled="pagination.current_page === pagination.last_page"
          @click="applyFilters(pagination.current_page + 1)"
        >
          Next
        </button>
      </div>

      <div v-if="loading" class="loading">Loading students…</div>
    </section>
  </div>
</template>

<style scoped>
.page-stack {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.filters {
  display: grid;
  grid-template-columns: repeat(4, minmax(150px, 1fr));
  gap: 1rem;
  align-items: end;
}

.filters label {
  font-size: 0.8rem;
  color: var(--color-text-soft);
  display: block;
  margin-bottom: 0.3rem;
}

.filters input {
  width: 100%;
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

th {
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--color-text-soft);
}

.badge {
  display: inline-flex;
  padding: 0.2rem 0.5rem;
  border-radius: 999px;
  background: var(--color-accent-soft);
  font-size: 0.75rem;
}

.badge.ghost {
  background: transparent;
  border: 1px dashed var(--color-border);
  color: var(--color-text-soft);
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.muted {
  color: var(--color-text-soft);
}

.pagination {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
  flex-wrap: wrap;
  margin-top: 1rem;
}

.pagination button {
  padding: 0.4rem 0.7rem;
  border-radius: 0.6rem;
  border: 1px solid var(--color-border);
  background: transparent;
}

.pagination button.active {
  background: var(--color-accent-soft);
}

.loading {
  margin-top: 1rem;
  color: var(--color-text-soft);
}

input {
  border: 1px solid var(--color-border);
  border-radius: 0.6rem;
  padding: 0.55rem 0.75rem;
  background: var(--color-background);
}

button {
  border: none;
  border-radius: 0.6rem;
  padding: 0.55rem 1rem;
  cursor: pointer;
}

button.primary {
  background: var(--color-accent);
  color: #0a0a0a;
  font-weight: 600;
}

button.ghost {
  border: 1px solid var(--color-border);
  background: transparent;
}

@media (max-width: 900px) {
  .filters {
    grid-template-columns: 1fr;
  }
}
</style>
