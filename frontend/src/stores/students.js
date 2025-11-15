import { defineStore } from 'pinia'
import {
  fetchStudents,
  createStudent as createStudentRequest,
  updateStudent as updateStudentRequest,
  deleteStudent as deleteStudentRequest,
} from '../services/api'

function toFormData(payload) {
  const formData = new FormData()
  Object.entries(payload).forEach(([key, value]) => {
    if (value === undefined || value === null) return
    formData.append(key, value)
  })
  return formData
}

export const useStudentsStore = defineStore('students', {
  state: () => ({
    items: [],
    pagination: {
      current_page: 1,
      per_page: 15,
      total: 0,
      last_page: 1,
    },
    filters: {
      search: '',
      class: '',
      section: '',
    },
    loading: false,
    error: '',
  }),
  actions: {
    async loadStudents(overrides = {}) {
      this.loading = true
      this.error = ''

      const params = {
        page: overrides.page ?? this.pagination.current_page,
        per_page: overrides.perPage ?? this.pagination.per_page,
        search: overrides.search ?? (this.filters.search || undefined),
        class: overrides.class ?? (this.filters.class || undefined),
        section: overrides.section ?? (this.filters.section || undefined),
      }

      try {
        const data = await fetchStudents(params)
        this.items = data.data
        this.pagination = {
          current_page: data.meta.current_page,
          per_page: data.meta.per_page,
          total: data.meta.total,
          last_page: data.meta.last_page,
        }
        this.filters = {
          search: params.search ?? '',
          class: params.class ?? '',
          section: params.section ?? '',
        }
      } catch (error) {
        this.error = error.message
      } finally {
        this.loading = false
      }
    },
    async createStudent(payload) {
      const response = await createStudentRequest(toFormData(payload), {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      return response.data
    },
    async updateStudent(id, payload) {
      const response = await updateStudentRequest(id, toFormData(payload), {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      return response.data
    },
    async deleteStudent(id) {
      await deleteStudentRequest(id)
    },
  },
})
