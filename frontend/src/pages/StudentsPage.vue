<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { useStudentsStore } from '../stores/students'

const store = useStudentsStore()
const { items, pagination, filters, loading, error } = storeToRefs(store)

const searchTerm = ref('')
const classFilter = ref('')
const sectionFilter = ref('')
const isFormOpen = ref(false)
const editingStudent = ref(null)
const formError = ref('')
const formLoading = ref(false)
const deleteModalOpen = ref(false)
const deleteTarget = ref(null)
const deleteError = ref('')
const deleteLoading = ref(false)
const fileInputKey = ref(0)

const defaultFormState = () => ({
  name: '',
  student_id: '',
  class: '',
  section: '',
  photo_path: '',
  photo: null,
})

const formState = reactive(defaultFormState())

const pageNumbers = computed(() => {
  const pages = []
  for (let i = 1; i <= pagination.value.last_page; i += 1) {
    pages.push(i)
  }
  return pages
})

async function applyFilters(page = 1) {
  await store.loadStudents({
    page,
    search: searchTerm.value || undefined,
    class: classFilter.value || undefined,
    section: sectionFilter.value || undefined,
  })
}

function openCreateForm() {
  editingStudent.value = null
  Object.assign(formState, defaultFormState())
  formError.value = ''
  isFormOpen.value = true
  fileInputKey.value += 1
}

function openEditForm(student) {
  editingStudent.value = { ...student }
  Object.assign(formState, {
    name: student.name,
    student_id: student.student_id,
    class: student.class,
    section: student.section,
    photo_path: student.photo_path ?? '',
    photo: null,
  })
  formError.value = ''
  isFormOpen.value = true
  fileInputKey.value += 1
}

function closeForm() {
  isFormOpen.value = false
  formLoading.value = false
  formError.value = ''
  editingStudent.value = null
  Object.assign(formState, defaultFormState())
  fileInputKey.value += 1
}

function handlePhotoChange(event) {
  const [file] = event.target.files || []
  formState.photo = file ?? null
}

async function handleFormSubmit() {
  formError.value = ''
  formLoading.value = true
  const payload = {
    name: formState.name,
    student_id: formState.student_id,
    class: formState.class,
    section: formState.section,
    photo_path: formState.photo_path || null,
    photo: formState.photo,
  }

  try {
    if (editingStudent.value) {
      await store.updateStudent(editingStudent.value.id, payload)
      await applyFilters(pagination.value.current_page)
    } else {
      await store.createStudent(payload)
      await applyFilters(1)
    }
    closeForm()
  } catch (err) {
    formError.value = err.message
  } finally {
    formLoading.value = false
  }
}

function openDeleteConfirm(student) {
  deleteTarget.value = student
  deleteError.value = ''
  deleteModalOpen.value = true
}

function closeDeleteModal() {
  deleteModalOpen.value = false
  deleteLoading.value = false
  deleteError.value = ''
}

async function confirmDelete() {
  if (!deleteTarget.value) return
  deleteError.value = ''
  deleteLoading.value = true

  try {
    await store.deleteStudent(deleteTarget.value.id)
    const currentPage = pagination.value.current_page
    const shouldGoBack = items.value.length === 1 && currentPage > 1
    await applyFilters(shouldGoBack ? currentPage - 1 : currentPage)
    closeDeleteModal()
  } catch (err) {
    deleteError.value = err.message
  } finally {
    deleteLoading.value = false
  }
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
        <div class="section-actions">
          <button class="ghost" @click="applyFilters(pagination.current_page)">Refresh</button>
          <button class="primary" @click="openCreateForm">Add Student</button>
        </div>
      </header>

      <div v-if="error" class="alert alert-error">{{ error }}</div>
      <div v-else class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>ID</th>
              <th>Class</th>
              <th>Section</th>
              <th>Photo</th>
              <th class="actions-col">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!loading && !items.length">
              <td colspan="7">No records found.</td>
            </tr>
            <tr v-for="(student, index) in items" :key="student.id">
              <td>{{ index + 1 }}</td>
              <td>{{ student.name }}</td>
              <td>{{ student.student_id }}</td>
              <td>{{ student.class }}</td>
              <td>{{ student.section }}</td>
              <td>
                <a
                  v-if="student.photo_url"
                  :href="student.photo_url"
                  target="_blank"
                  rel="noopener"
                  class="badge link"
                >
                  View photo
                </a>
                <span v-else class="badge ghost">Pending</span>
              </td>
              <td class="actions">
                <button class="ghost" @click="openEditForm(student)">Edit</button>
                <button class="danger" @click="openDeleteConfirm(student)">Delete</button>
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

    <div v-if="isFormOpen" class="modal" @click.self="closeForm">
      <div class="modal__panel">
        <header class="modal__header">
          <div>
            <p class="modal__eyebrow">Student</p>
            <h3>{{ editingStudent ? 'Edit student' : 'Add new student' }}</h3>
          </div>
          <button class="ghost" @click="closeForm">Close</button>
        </header>
        <form class="modal__body" @submit.prevent="handleFormSubmit">
          <label>
            Name
            <input v-model="formState.name" type="text" required placeholder="Jane Doe" />
          </label>
          <label>
            Student ID
            <input v-model="formState.student_id" type="text" required placeholder="STD-001" />
          </label>
          <label>
            Class
            <input v-model="formState.class" type="text" required placeholder="10" />
          </label>
          <label>
            Section
            <input v-model="formState.section" type="text" required placeholder="A" />
          </label>
          <label class="file-input">
            Photo (PNG/JPG, max 2MB)
            <input :key="fileInputKey" type="file" accept="image/*" @change="handlePhotoChange" />
          </label>
          <p v-if="editingStudent?.photo_url" class="form-hint">
            Current photo:
            <a :href="editingStudent.photo_url" target="_blank" rel="noopener">Open in new tab</a>. Uploading a new file
            replaces it.
          </p>

          <div v-if="formError" class="alert alert-error">{{ formError }}</div>

          <footer class="modal__footer">
            <button type="button" class="ghost" @click="closeForm">Cancel</button>
            <button type="submit" class="primary" :disabled="formLoading">
              {{ formLoading ? 'Saving…' : editingStudent ? 'Save changes' : 'Create student' }}
            </button>
          </footer>
        </form>
      </div>
    </div>

    <div v-if="deleteModalOpen" class="modal" @click.self="closeDeleteModal">
      <div class="modal__panel">
        <header class="modal__header">
          <div>
            <p class="modal__eyebrow">Danger zone</p>
            <h3>Delete student</h3>
          </div>
          <button class="ghost" @click="closeDeleteModal">Close</button>
        </header>
        <div class="modal__body">
          <p>
            Remove <strong>{{ deleteTarget?.name }}</strong> ({{ deleteTarget?.student_id }}) from the roster? Attendance
            history stays intact.
          </p>
          <div v-if="deleteError" class="alert alert-error">{{ deleteError }}</div>
        </div>
        <footer class="modal__footer">
          <button type="button" class="ghost" @click="closeDeleteModal">Cancel</button>
          <button type="button" class="danger" :disabled="deleteLoading" @click="confirmDelete">
            {{ deleteLoading ? 'Deleting…' : 'Delete' }}
          </button>
        </footer>
      </div>
    </div>
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

.badge.link {
  border: 1px solid var(--color-border);
  background: transparent;
  color: var(--color-text);
  text-decoration: none;
  gap: 0.25rem;
}

.file-input input {
  border: 1px dashed var(--color-border);
  padding: 0.4rem;
  background: var(--color-background);
}

.form-hint {
  font-size: 0.85rem;
  color: var(--color-text-soft);
}

.form-hint a {
  color: var(--color-text);
  text-decoration: underline;
}

.actions {
  display: flex;
  gap: 0.4rem;
  flex-wrap: wrap;
}

.actions-col {
  width: 140px;
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.section-actions {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
  justify-content: flex-end;
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

button.danger {
  background: #fee2e2;
  color: #7f1d1d;
  border: 1px solid #fecaca;
}

.modal {
  position: fixed;
  inset: 0;
  background: rgba(10, 10, 10, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  z-index: 1000;
}

.modal__panel {
  background: var(--color-background);
  border-radius: 1rem;
  width: min(520px, 100%);
  border: 1px solid var(--color-border);
  box-shadow: 0 12px 45px rgba(0, 0, 0, 0.2);
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.modal__header,
.modal__footer {
  padding: 1.25rem 1.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.modal__header {
  border-bottom: 1px solid var(--color-border);
}

.modal__body {
  padding: 0 1.5rem 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
}

.modal__body label {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  font-size: 0.85rem;
  color: var(--color-text-soft);
}

.modal__body input {
  width: 100%;
}

.modal__eyebrow {
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.08em;
  color: var(--color-text-soft);
  margin-bottom: 0.3rem;
}

.modal__footer {
  border-top: 1px solid var(--color-border);
}

@media (max-width: 900px) {
  .modal {
    padding: 1rem;
  }
}

@media (max-width: 900px) {
  .filters {
    grid-template-columns: 1fr;
  }
}
</style>
