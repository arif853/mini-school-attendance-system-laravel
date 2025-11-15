import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api',
  timeout: 10000,
})

export function setAuthToken(token) {
  if (token) {
    api.defaults.headers.common.Authorization = `Bearer ${token}`
  } else {
    delete api.defaults.headers.common.Authorization
  }
}

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const message = error.response?.data?.message || error.message || 'Unexpected error'
    return Promise.reject(new Error(message))
  }
)

export function fetchStudents(params = {}) {
  return api.get('/students', { params }).then((res) => res.data)
}

export function createStudent(payload, config = {}) {
  return api.post('/students', payload, config).then((res) => res.data)
}

export function updateStudent(id, payload, config = {}) {
  return api.put(`/students/${id}`, payload, config).then((res) => res.data)
}

export function deleteStudent(id) {
  return api.delete(`/students/${id}`)
}

export function fetchAttendance(params = {}) {
  return api.get('/attendance', { params }).then((res) => res.data)
}

export function bulkAttendance(payload) {
  return api.post('/attendance/bulk', payload).then((res) => res.data)
}

export function fetchMonthlyReport(params = {}) {
  return api.get('/attendance/reports/monthly', { params }).then((res) => res.data)
}

export function fetchTodayStats(params = {}) {
  return api.get('/attendance/stats/today', { params }).then((res) => res.data)
}

export default api
