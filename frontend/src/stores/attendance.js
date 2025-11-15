import { defineStore } from 'pinia'
import { bulkAttendance, fetchStudents } from '../services/api'

export const useAttendanceStore = defineStore('attendance', {
  state: () => ({
    roster: [],
    classFilter: '10',
    sectionFilter: 'A',
    date: new Date().toISOString().slice(0, 10),
    statusMap: {},
    noteMap: {},
    loading: false,
    submitting: false,
    message: '',
    error: '',
  }),
  getters: {
    attendanceSummary(state) {
      const totals = { present: 0, absent: 0, late: 0 }
      state.roster.forEach((student) => {
        const status = state.statusMap[student.id] || 'present'
        totals[status] += 1
      })
      const total = state.roster.length || 1
      return {
        ...totals,
        percentage: Math.round((totals.present / total) * 100),
      }
    },
  },
  actions: {
    async loadRoster() {
      this.loading = true
      this.error = ''
      try {
        const data = await fetchStudents({
          class: this.classFilter,
          section: this.sectionFilter,
          per_page: 100,
        })
        this.roster = data.data
        this.statusMap = this.roster.reduce((acc, student) => {
          acc[student.id] = this.statusMap[student.id] || 'present'
          return acc
        }, {})
      } catch (error) {
        this.error = error.message
      } finally {
        this.loading = false
      }
    },
    setClass(value) {
      this.classFilter = value
    },
    setSection(value) {
      this.sectionFilter = value
    },
    setStatus(studentId, status) {
      this.statusMap[studentId] = status
    },
    setNote(studentId, note) {
      this.noteMap[studentId] = note
    },
    async submitAttendance(recordedBy = 'Coordinator') {
      if (!this.roster.length) {
        this.error = 'Load a roster before submitting attendance.'
        return
      }

      this.submitting = true
      this.error = ''
      this.message = ''

      try {
        const entries = this.roster.map((student) => ({
          student_id: student.id,
          status: this.statusMap[student.id] || 'present',
          note: this.noteMap[student.id] || null,
        }))

        await bulkAttendance({
          date: this.date,
          class: this.classFilter,
          section: this.sectionFilter,
          recorded_by: recordedBy,
          entries,
        })

        this.message = 'Attendance submitted successfully.'
      } catch (error) {
        this.error = error.message
      } finally {
        this.submitting = false
      }
    },
    applyBulk(status) {
      this.roster.forEach((student) => {
        this.statusMap[student.id] = status
      })
    },
  },
})
