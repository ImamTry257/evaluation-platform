<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'

// State
const searchQuery = ref('')
const respondents = ref<any[]>([])
const loading = ref(false)
const currentPage = ref(1)
const totalPages = ref(1)
const totalItems = ref(0)
const perPage = ref(10)

// Modal state
const showFormModal = ref(false)
const showDeleteModal = ref(false)
const formMode = ref<'add' | 'edit'>('add')
const editingId = ref<number | null>(null)
const deletingRespondent = ref<any>(null)
const formLoading = ref(false)

// Form
const form = ref({
  name: '',
  username: '',
  email: '',
  password: '',
  isActive: true,
})

// Stats
const stats = ref({
  total: 0,
  active: 0,
  inactive: 0,
})

// Fetch respondents
async function fetchRespondents(page = 1) {
  loading.value = true
  try {
    const params: any = {
      page,
      limit: perPage.value,
    }
    if (searchQuery.value) {
      params.search = searchQuery.value
    }
    const { data } = await api.get('/admin/respondents', { params })
    const payload = data.data ?? data
    respondents.value = payload.data ?? payload
    currentPage.value = payload.current_page ?? 1
    totalPages.value = payload.last_page ?? 1
    totalItems.value = payload.total ?? 0
    updateStats()
  } catch (err: any) {
    console.error('Failed to fetch respondents:', err)
  } finally {
    loading.value = false
  }
}

function updateStats() {
  stats.value.total = totalItems.value
  stats.value.active = respondents.value.filter((r) => r.isActive).length
  stats.value.inactive = respondents.value.filter((r) => !r.isActive).length
}

// Computed
const filteredRespondents = computed(() => {
  if (!searchQuery.value) return respondents.value
  const q = searchQuery.value.toLowerCase()
  return respondents.value.filter(
    (r) =>
      r.name?.toLowerCase().includes(q) ||
      r.username?.toLowerCase().includes(q) ||
      r.email?.toLowerCase().includes(q)
  )
})

// Search handler (debounced)
let searchTimeout: ReturnType<typeof setTimeout>
function onSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    fetchRespondents(1)
  }, 400)
}

// Form handlers
function openAddModal() {
  formMode.value = 'add'
  editingId.value = null
  form.value = { name: '', username: '', email: '', password: '', isActive: true }
  showFormModal.value = true
}

function openEditModal(r: any) {
  formMode.value = 'edit'
  editingId.value = r.id
  form.value = {
    name: r.name,
    username: r.username,
    email: r.email,
    password: '',
    isActive: r.isActive,
  }
  showFormModal.value = true
}

async function handleFormSubmit() {
  formLoading.value = true
  try {
    if (formMode.value === 'add') {
      await api.post('/admin/respondents', form.value)
    } else {
      const payload: any = { ...form.value }
      if (!payload.password) delete payload.password
      await api.put(`/admin/respondents/${editingId.value}`, payload)
    }
    showFormModal.value = false
    fetchRespondents(currentPage.value)
  } catch (err: any) {
    console.error('Form submit error:', err)
  } finally {
    formLoading.value = false
  }
}

// Delete handlers
function openDeleteModal(r: any) {
  deletingRespondent.value = r
  showDeleteModal.value = true
}

async function confirmDelete() {
  try {
    await api.delete(`/admin/respondents/${deletingRespondent.value.id}`)
    showDeleteModal.value = false
    deletingRespondent.value = null
    fetchRespondents(currentPage.value)
  } catch (err: any) {
    console.error('Delete error:', err)
  }
}

// Pagination
function goToPage(page: number) {
  if (page < 1 || page > totalPages.value) return
  fetchRespondents(page)
}

// Helpers
function getInitials(name: string) {
  return name
    .split(' ')
    .map((w) => w[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

const avatarColors = [
  'bg-emerald-100 text-emerald-700',
  'bg-blue-100 text-blue-700',
  'bg-purple-100 text-purple-700',
  'bg-orange-100 text-orange-700',
  'bg-rose-100 text-rose-700',
  'bg-cyan-100 text-cyan-700',
]

function getAvatarColor(id: number) {
  return avatarColors[id % avatarColors.length]
}

// Date formatter
function formatDate(dateStr: string): string {
  const d = new Date(dateStr)
  return d.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

// Init
onMounted(() => {
  fetchRespondents()
})
</script>

<template>
  <div class="p-8 max-w-[1440px] w-full mx-auto">
    <!-- Header Section -->
    <section class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4 fade-in">
      <div>
        <h2 class="font-headline-xl text-headline-xl text-on-surface">Manajemen Responden</h2>
        <p class="font-body-base text-body-base text-on-surface-variant mt-2 max-w-2xl">
          Kelola data responden dan pantau status partisipasi evaluasi sekolah secara real-time.
        </p>
      </div>
    </section>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8 fade-in-delay">
      <div class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant/30 flex items-center gap-4">
        <div class="w-12 h-12 bg-primary-container/20 rounded-xl flex items-center justify-center text-primary">
          <span class="material-symbols-outlined">person</span>
        </div>
        <div>
          <p class="text-xs font-bold text-secondary uppercase tracking-wider">Total Responden</p>
          <p class="text-2xl font-bold text-on-surface">{{ stats.total }}</p>
        </div>
      </div>
      <div class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant/30 flex items-center gap-4">
        <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-700">
          <span class="material-symbols-outlined">check_circle</span>
        </div>
        <div>
          <p class="text-xs font-bold text-secondary uppercase tracking-wider">Aktif</p>
          <p class="text-2xl font-bold text-on-surface">{{ stats.active }}</p>
        </div>
      </div>
      <div class="bg-surface-container-lowest p-5 rounded-2xl shadow-sm border border-outline-variant/30 flex items-center gap-4">
        <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center text-slate-700">
          <span class="material-symbols-outlined">cancel</span>
        </div>
        <div>
          <p class="text-xs font-bold text-secondary uppercase tracking-wider">Inaktif</p>
          <p class="text-2xl font-bold text-on-surface">{{ stats.inactive }}</p>
        </div>
      </div>
    </div>

    <!-- Action Bar & Content Card -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/10 overflow-hidden fade-in-delay-2">
      <!-- Action Bar -->
      <div class="p-6 border-b border-outline-variant/10 flex flex-col sm:flex-row gap-4 bg-surface-container-low/30">
        <div class="relative flex-1">
          <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
          <input
            v-model="searchQuery"
            @input="onSearch"
            class="search-input w-full bg-white border border-outline-variant/50 rounded-xl pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-primary-container outline-none transition-all text-body-sm font-body-sm"
            placeholder="Cari nama, username, atau email..."
            type="text"
          />
        </div>
        <button
          @click="openAddModal"
          class="bg-primary hover:bg-primary/90 text-on-primary font-body-base font-semibold px-6 py-2.5 rounded-xl flex items-center gap-2 shadow-sm transition-all active:scale-95"
        >
          <span class="material-symbols-outlined text-[20px]">add</span>
          <span class="text-body-sm">Tambah Responden</span>
        </button>
      </div>

      <!-- Data Table -->
      <div class="overflow-x-auto data-table-scroll">
        <!-- Loading -->
        <div v-if="loading" class="p-12 text-center">
          <span class="material-symbols-outlined text-[32px] text-outline animate-spin">progress_activity</span>
          <p class="text-body-sm text-on-surface-variant mt-2">Memuat data...</p>
        </div>

        <!-- Empty -->
        <div v-else-if="filteredRespondents.length === 0" class="p-12 text-center">
          <span class="material-symbols-outlined text-[48px] text-outline">group_off</span>
          <p class="text-body-base text-on-surface-variant mt-3">Tidak ada data responden ditemukan.</p>
        </div>

        <!-- Table -->
        <table v-else class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-surface-container-low/50">
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Nama & Email</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Username</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Status Akun</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Terakhir Update</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/10">
            <tr
              v-for="r in filteredRespondents"
              :key="r.id"
              class="table-row hover:bg-surface-container-low/30 transition-colors"
            >
              <td class="px-6 py-5">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm" :class="getAvatarColor(r.id)">
                    {{ getInitials(r.name) }}
                  </div>
                  <div>
                    <p class="font-medium text-on-surface">{{ r.name }}</p>
                    <p class="text-xs text-secondary">{{ r.email }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-5">
                <span class="text-body-sm text-on-surface font-body-sm">{{ r.username }}</span>
              </td>
              <td class="px-6 py-5">
                <span
                  class="px-2.5 py-1 rounded-full text-xs font-semibold"
                  :class="r.isActive ? 'bg-emerald-100 text-emerald-800' : 'bg-secondary-fixed text-secondary'"
                >
                  {{ r.isActive ? 'Aktif' : 'Inaktif' }}
                </span>
              </td>
              <td class="px-6 py-5 text-sm text-secondary">
                {{ r.updated_at ? formatDate(r.updated_at) : '-' }}
              </td>
              <td class="px-6 py-5">
                <div class="flex items-center justify-center gap-2">
                  <button
                    @click="openEditModal(r)"
                    class="table-btn p-2 text-on-surface-variant hover:bg-surface-container-high rounded-lg transition-colors"
                    title="Edit"
                  >
                    <span class="material-symbols-outlined text-[18px]">edit</span>
                  </button>
                  <button
                    @click="openDeleteModal(r)"
                    class="p-2 text-error hover:bg-error-container/20 rounded-lg transition-colors"
                    title="Hapus"
                  >
                    <span class="material-symbols-outlined text-[18px]">delete</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer -->
      <div class="p-6 bg-surface-container-lowest flex items-center justify-between border-t border-outline-variant/10">
        <p class="text-sm text-secondary">
          Menampilkan <span class="font-semibold text-on-surface">{{ (currentPage - 1) * perPage + 1 }}-{{ Math.min(currentPage * perPage, totalItems) }}</span>
          dari <span class="font-semibold text-on-surface">{{ totalItems }}</span> responden
        </p>
        <div class="flex items-center gap-1">
          <button
            @click="goToPage(currentPage - 1)"
            :disabled="currentPage <= 1"
            class="p-2 hover:bg-surface-container rounded-lg text-secondary disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
          >
            <span class="material-symbols-outlined">chevron_left</span>
          </button>
          <button
            v-for="page in totalPages"
            :key="page"
            @click="goToPage(page)"
            class="w-8 h-8 flex items-center justify-center rounded-lg font-medium text-sm transition-colors"
            :class="page === currentPage ? 'bg-primary text-white shadow-sm' : 'hover:bg-surface-container text-on-surface'"
          >
            {{ page }}
          </button>
          <button
            @click="goToPage(currentPage + 1)"
            :disabled="currentPage >= totalPages"
            class="p-2 hover:bg-surface-container rounded-lg text-secondary disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
          >
            <span class="material-symbols-outlined">chevron_right</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ==================== ADD/EDIT MODAL ==================== -->
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="showFormModal"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        @click.self="showFormModal = false"
      >
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showFormModal = false"></div>
        <div class="relative bg-surface-container-lowest rounded-2xl shadow-xl w-full max-w-lg z-10 modal-content">
          <!-- Modal Header -->
          <div class="flex items-center justify-between p-6 border-b border-outline-variant/10">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                <span class="material-symbols-outlined text-primary">{{ formMode === 'add' ? 'person_add' : 'edit' }}</span>
              </div>
              <div>
                <h3 class="font-title-md text-title-md text-on-surface">{{ formMode === 'add' ? 'Tambah Responden' : 'Edit Responden' }}</h3>
                <p class="text-body-sm text-on-surface-variant">{{ formMode === 'add' ? 'Buat akun responden baru' : 'Ubah data responden' }}</p>
              </div>
            </div>
            <button
              @click="showFormModal = false"
              class="w-8 h-8 flex items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container transition-colors"
            >
              <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
          </div>

          <!-- Modal Body -->
          <div class="p-6 space-y-5">
            <!-- Nama Lengkap -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Nama Lengkap</label>
              <input
                v-model="form.name"
                type="text"
                placeholder="Contoh: Andi Budiman"
                class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all"
              />
            </div>

            <!-- Username -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Username</label>
              <input
                v-model="form.username"
                type="text"
                placeholder="Contoh: andi_budiman"
                class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all"
              />
            </div>

            <!-- Email -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Email</label>
              <input
                v-model="form.email"
                type="email"
                placeholder="Contoh: andi@email.com"
                class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all"
              />
            </div>

            <!-- Password -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">
                Password
                <span v-if="formMode === 'edit'" class="text-outline">(Kosongkan jika tidak diubah)</span>
              </label>
              <input
                v-model="form.password"
                type="password"
                :placeholder="formMode === 'edit' ? '••••••••' : 'Minimal 8 karakter'"
                class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all"
              />
            </div>

            <!-- Status -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Status Akun</label>
              <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="radio" :value="true" v-model="form.isActive" class="accent-primary w-4 h-4" />
                  <span class="text-body-sm text-on-surface">Aktif</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="radio" :value="false" v-model="form.isActive" class="accent-primary w-4 h-4" />
                  <span class="text-body-sm text-on-surface">Inaktif</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="flex items-center justify-end gap-3 p-6 border-t border-outline-variant/10">
            <button
              @click="showFormModal = false"
              :disabled="formLoading"
              class="px-5 py-2.5 rounded-xl border border-outline-variant/50 text-on-surface font-body-base font-medium hover:bg-surface-container transition-colors"
            >
              Batal
            </button>
            <button
              @click="handleFormSubmit"
              :disabled="!form.name || !form.username || !form.email || (formMode === 'add' && !form.password) || formLoading"
              class="px-5 py-2.5 rounded-xl bg-primary text-on-primary font-body-base font-semibold shadow-sm transition-all hover:bg-primary/90 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="formLoading" class="material-symbols-outlined text-[18px] animate-spin mr-1">progress_activity</span>
              {{ formMode === 'add' ? 'Simpan Responden' : 'Simpan Perubahan' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>

  <!-- ==================== DELETE MODAL ==================== -->
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="showDeleteModal"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        @click.self="showDeleteModal = false"
      >
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showDeleteModal = false"></div>
        <div class="relative bg-surface-container-lowest rounded-2xl shadow-xl w-full max-w-md z-10 modal-content">
          <!-- Modal Header -->
          <div class="flex items-center justify-between p-6 pb-0">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-error/10 flex items-center justify-center">
                <span class="material-symbols-outlined text-error">delete</span>
              </div>
              <h3 class="font-title-md text-title-md text-on-surface">Hapus Responden</h3>
            </div>
            <button
              @click="showDeleteModal = false"
              class="w-8 h-8 flex items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container transition-colors"
            >
              <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
          </div>

          <!-- Modal Body -->
          <div class="p-6">
            <p class="text-body-base text-on-surface-variant mb-4">
              Anda yakin ingin menghapus responden berikut?
            </p>
            <div v-if="deletingRespondent" class="bg-surface-container-low rounded-xl p-4 border border-outline-variant/20">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm" :class="getAvatarColor(deletingRespondent.id)">
                  {{ getInitials(deletingRespondent.name) }}
                </div>
                <div>
                  <p class="font-body-base font-semibold text-on-surface">{{ deletingRespondent.name }}</p>
                  <p class="text-body-sm text-on-surface-variant">{{ deletingRespondent.email }}</p>
                </div>
              </div>
            </div>
            <p class="text-body-sm text-error mt-3 flex items-center gap-1.5">
              <span class="material-symbols-outlined text-[16px]">warning</span>
              Tindakan ini tidak dapat dibatalkan.
            </p>
          </div>

          <!-- Modal Footer -->
          <div class="flex items-center justify-end gap-3 p-6 pt-0">
            <button
              @click="showDeleteModal = false"
              class="px-5 py-2.5 rounded-xl border border-outline-variant/50 text-on-surface font-body-base font-medium hover:bg-surface-container transition-colors"
            >
              Batal
            </button>
            <button
              @click="confirmDelete"
              class="px-5 py-2.5 rounded-xl bg-error text-on-error font-body-base font-semibold shadow-sm transition-all hover:bg-error/90 active:scale-95"
            >
              Ya, Hapus
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
/* ===== INTERACTIVE CSS STYLING ===== */
.search-input {
  transition: all 0.3s ease;
}
.search-input:focus {
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
  transform: translateY(-2px);
  border-color: #10b981;
}

/* ===== TABLE ===== */
.table-row {
  transition: all 0.3s ease;
  cursor: pointer;
}
.table-row:hover {
  background-color: rgba(238, 246, 238, 0.5);
  transform: scale(1.01);
}
.table-row:hover td {
  color: #161d19;
}

/* ===== TABLE BUTTONS ===== */
.table-btn {
  transition: all 0.3s ease;
  border-radius: 0.5rem;
}
.table-btn:hover {
  background-color: #eef6ee;
  color: #006c49;
  transform: translateY(-1px);
}

/* ===== DATA TABLE SCROLL ===== */
.data-table-scroll::-webkit-scrollbar {
  height: 6px;
}
.data-table-scroll::-webkit-scrollbar-thumb {
  background: #bbcabf;
  border-radius: 10px;
}

/* ===== MODAL STYLING ===== */
.modal-input {
  transition: all 0.3s ease;
}
.modal-input:focus {
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
  transform: translateY(-1px);
}

/* Modal Transition */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.25s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
.modal-content {
  animation: modalIn 0.25s ease-out;
}
@keyframes modalIn {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(10px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

/* ===== FADE IN ANIMATION ===== */
.fade-in {
  animation: fadeIn 0.5s ease-out forwards;
}
.fade-in-delay {
  animation: fadeIn 0.5s ease-out 0.1s forwards;
  opacity: 0;
}
.fade-in-delay-2 {
  animation: fadeIn 0.5s ease-out 0.2s forwards;
  opacity: 0;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
