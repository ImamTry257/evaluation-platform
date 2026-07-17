<script setup lang="ts">
import { ref, computed } from 'vue'
import { RouterLink } from 'vue-router'

// State
const searchQuery = ref('')

// Modal state
const showFormModal = ref(false)
const showDeleteModal = ref(false)
const formMode = ref<'add' | 'edit'>('add')
const editingId = ref<number | null>(null)
const deletingComponent = ref<any>(null)

// Form
const form = ref({
  name: '',
  description: '',
})

// Mock data - Components (context: Instrument 2024)
const components = ref([
  {
    id: 1,
    name: 'Kebijakan Struktural',
    description: 'Aspek kebijakan organisasi',
    status: 'active',
    totalSubComponents: 3,
    totalIndicators: 9,
    totalQuestions: 27,
  },
  {
    id: 2,
    name: 'Program Utama',
    description: 'Program lingkungan sekolah',
    status: 'active',
    totalSubComponents: 2,
    totalIndicators: 6,
    totalQuestions: 18,
  },
  {
    id: 3,
    name: 'Operasional',
    description: 'Pelaksanaan harian',
    status: 'active',
    totalSubComponents: 2,
    totalIndicators: 5,
    totalQuestions: 15,
  },
  {
    id: 4,
    name: 'Edukasi',
    description: 'Pendidikan lingkungan',
    status: 'inactive',
    totalSubComponents: 2,
    totalIndicators: 4,
    totalQuestions: 12,
  },
  {
    id: 5,
    name: 'Evaluasi & Inovasi',
    description: 'Monitoring dan peningkatan',
    status: 'active',
    totalSubComponents: 2,
    totalIndicators: 5,
    totalQuestions: 15,
  },
])

// Computed
const filteredComponents = computed(() => {
  return components.value.filter((c) =>
    c.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

// Methods
function getStatusBadge(status: string) {
  return status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500'
}

function getStatusLabel(status: string) {
  return status === 'active' ? 'Active' : 'Inactive'
}

// Form handlers
function openAddModal() {
  formMode.value = 'add'
  editingId.value = null
  form.value = { name: '', description: '' }
  showFormModal.value = true
}

function openEditModal(c: any) {
  formMode.value = 'edit'
  editingId.value = c.id
  form.value = { name: c.name, description: c.description }
  showFormModal.value = true
}

function handleFormSubmit() {
  if (formMode.value === 'add') {
    const newId = Math.max(...components.value.map((c) => c.id)) + 1
    components.value.unshift({
      id: newId,
      name: form.value.name,
      description: form.value.description,
      status: 'active',
      totalSubComponents: 0,
      totalIndicators: 0,
      totalQuestions: 0,
    })
  } else {
    const idx = components.value.findIndex((c) => c.id === editingId.value)
    if (idx !== -1) {
      components.value[idx] = {
        ...components.value[idx],
        name: form.value.name,
        description: form.value.description,
      }
    }
  }
  showFormModal.value = false
}

// Delete handlers
function openDeleteModal(c: any) {
  deletingComponent.value = c
  showDeleteModal.value = true
}

function confirmDelete() {
  components.value = components.value.filter((c) => c.id !== deletingComponent.value.id)
  showDeleteModal.value = false
  deletingComponent.value = null
}

// More menu actions
function handleView(item: any) {
  console.log('View:', item)
}

function handleEdit(item: any) {
  openEditModal(item)
}

function handleToggleStatus(item: any) {
  const idx = components.value.findIndex((c) => c.id === item.id)
  if (idx !== -1) {
    components.value[idx].status = item.status === 'active' ? 'inactive' : 'active'
  }
}

function handleDelete(item: any) {
  openDeleteModal(item)
}
</script>

<template>
  <div class="p-8 max-w-[1440px] w-full mx-auto">
    <!-- Breadcrumb -->
    <nav class="mb-6 flex items-center gap-2 text-sm">
      <a href="/admin/instrument" class="text-primary font-medium hover:underline cursor-pointer">Instrument Penelitian</a>
      <span class="text-outline">›</span>
      <a href="/admin/instrument/1" class="text-primary font-medium hover:underline cursor-pointer">Kuesioner Kebijakan Lingkungan</a>
      <span class="text-outline">›</span>
      <span class="text-on-surface font-semibold">Components</span>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-4 fade-in">
      <div class="flex items-center gap-4">
        <a href="/admin/instrument" class="back-btn flex items-center gap-1 text-primary text-sm font-medium hover:bg-primary/10 px-3 py-2 rounded-lg transition-colors no-underline">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>
          Kembali ke Instrument
        </a>
      </div>
      <button
        @click="openAddModal"
        class="bg-primary hover:bg-primary/90 text-on-primary font-body-base font-semibold px-6 py-3 rounded-xl flex items-center gap-2 shadow-sm transition-all active:scale-95"
      >
        <span class="material-symbols-outlined">add</span>
        Tambah Component
      </button>
    </div>

    <!-- Action Bar & Content Card -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/10 overflow-hidden fade-in-delay">
      <!-- Action Bar -->
      <div class="p-6 border-b border-outline-variant/10 bg-surface-container-low/30">
        <div class="relative">
          <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
          <input
            v-model="searchQuery"
            class="search-input w-full bg-white border border-outline-variant/50 rounded-xl pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-primary-container outline-none transition-all text-body-sm font-body-sm"
            placeholder="Cari nama komponen..."
            type="text"
          />
        </div>
      </div>

      <!-- Data Table -->
      <div class="overflow-x-auto data-table-scroll">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-surface-container-low/50">
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Nama Komponen</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Status</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Sub Comp</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Indikator</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Pertanyaan</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/10">
            <tr
              v-for="c in filteredComponents"
              :key="c.id"
              class="table-row hover:bg-surface-container-low/30 transition-colors"
              :class="{ 'opacity-50': c.status === 'inactive' }"
            >
              <td class="px-6 py-5">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[20px]">widgets</span>
                  </div>
                  <div>
                    <span class="font-body-base font-semibold text-on-surface">{{ c.name }}</span>
                    <p class="text-body-sm text-on-surface-variant line-clamp-1 mt-0.5">{{ c.description }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-5">
                <span class="status-badge inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold" :class="getStatusBadge(c.status)">
                  {{ getStatusLabel(c.status) }}
                </span>
              </td>
              <td class="px-6 py-5">
                <span class="count-badge">{{ c.totalSubComponents }}</span>
              </td>
              <td class="px-6 py-5">
                <span class="count-badge">{{ c.totalIndicators }}</span>
              </td>
              <td class="px-6 py-5">
                <span class="count-badge">{{ c.totalQuestions }}</span>
              </td>
              <td class="px-6 py-5">
                <div class="flex items-center justify-center gap-2">
                  <RouterLink
                    :to="`/admin/component/${c.id}/sub-component`"
                    class="action-link text-xs font-semibold text-primary cursor-pointer hover:bg-primary/10 px-2 py-1 rounded-lg"
                  >
                    <span class="material-symbols-outlined text-[14px]">subdirectory_arrow_right</span>
                    Lihat Sub
                  </RouterLink>
                  <div class="relative more-wrapper">
                    <button class="more-btn w-8 h-8 flex items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container-high transition-colors">
                      <span class="material-symbols-outlined text-[20px]">more_vert</span>
                    </button>
                    <div class="more-dropdown">
                      <div class="more-dropdown-item" @click="handleView(c)">
                        <span class="material-symbols-outlined">visibility</span>
                        View Detail
                      </div>
                      <div class="more-dropdown-item" @click="handleEdit(c)">
                        <span class="material-symbols-outlined">edit</span>
                        Edit
                      </div>
                      <div class="more-dropdown-item" @click="handleToggleStatus(c)">
                        <span class="material-symbols-outlined">toggle_on</span>
                        {{ c.status === 'active' ? 'Set Inactive' : 'Set Active' }}
                      </div>
                      <div class="more-dropdown-divider"></div>
                      <div class="more-dropdown-item danger" @click="handleDelete(c)">
                        <span class="material-symbols-outlined">delete</span>
                        Hapus
                      </div>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Footer -->
      <div class="px-6 py-4 bg-surface-container-low/30 border-t border-outline-variant/10">
        <p class="text-body-sm font-body-sm text-on-surface-variant">
          Menampilkan <span class="font-semibold text-on-surface">{{ filteredComponents.length }}</span> dari <span class="font-semibold text-on-surface">{{ components.length }}</span> komponen
        </p>
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
                <span class="material-symbols-outlined text-primary">{{ formMode === 'add' ? 'add' : 'edit' }}</span>
              </div>
              <div>
                <h3 class="font-title-md text-title-md text-on-surface">{{ formMode === 'add' ? 'Tambah Component' : 'Edit Component' }}</h3>
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
            <!-- Nama Komponen -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Nama Komponen</label>
              <input
                v-model="form.name"
                type="text"
                placeholder="Contoh: Kebijakan Struktural"
                class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all"
              />
            </div>

            <!-- Deskripsi -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Deskripsi</label>
              <textarea
                v-model="form.description"
                rows="3"
                placeholder="Deskripsi komponen evaluasi..."
                class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all resize-none"
              ></textarea>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="flex items-center justify-end gap-3 p-6 border-t border-outline-variant/10">
            <button
              @click="showFormModal = false"
              class="px-8 py-2 rounded-xl bg-error text-on-surface text-white font-body-base font-medium hover:bg-error/70 transition-colors"
            >
              Batal
            </button>
            <button
              @click="handleFormSubmit"
              :disabled="!form.name || !form.description"
              class="px-8 py-2 rounded-xl bg-primary text-on-primary font-body-base font-semibold shadow-sm transition-all hover:bg-primary/90 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ formMode === 'add' ? 'Simpan' : 'Ubah' }}
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
              <h3 class="font-title-md text-title-md text-on-surface">Hapus Component</h3>
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
              Anda yakin ingin menghapus komponen berikut?
            </p>
            <div v-if="deletingComponent" class="bg-surface-container-low rounded-xl p-4 border border-outline-variant/20">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center text-primary">
                  <span class="material-symbols-outlined text-[20px]">widgets</span>
                </div>
                <div>
                  <p class="font-body-base font-semibold text-on-surface">{{ deletingComponent.name }}</p>
                  <p class="text-body-sm text-on-surface-variant">{{ deletingComponent.totalSubComponents }} sub-komponen, {{ deletingComponent.totalIndicators }} indikator</p>
                </div>
              </div>
            </div>
            <p class="text-body-sm text-error mt-3 flex items-center gap-1.5">
              <span class="material-symbols-outlined text-[16px]">warning</span>
              Semua sub-komponen di dalamnya akan ikut terhapus. Tindakan ini tidak dapat dibatalkan.
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
/* ===== SEARCH INPUT ===== */
.search-input {
  transition: all 0.3s ease;
}
.search-input:focus {
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
  transform: translateY(-2px);
  border-color: #10b981;
}

/* ===== BACK BUTTON ===== */
.back-btn {
  transition: all 0.2s ease;
  text-decoration: none;
}
.back-btn:hover {
  background-color: rgba(16, 185, 129, 0.1);
  text-decoration: none;
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

/* ===== ACTION LINK ===== */
.action-link {
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}
.action-link:hover {
  background-color: #eef6ee;
}

/* ===== MORE MENU ===== */
.more-wrapper {
  position: relative;
  display: inline-block;
}
.more-dropdown {
  display: none;
  position: absolute;
  right: 0;
  top: 100%;
  margin-top: 4px;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.12);
  z-index: 50;
  min-width: 160px;
  overflow: hidden;
}
.more-wrapper:hover .more-dropdown {
  display: block;
}
.more-dropdown-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 14px;
  font-size: 12px;
  color: #3c4a42;
  cursor: pointer;
  transition: background 0.15s;
}
.more-dropdown-item:hover {
  background: #f9fdf9;
}
.more-dropdown-item .material-symbols-outlined {
  font-size: 16px;
}
.more-dropdown-item.danger {
  color: #ba1a1a;
}
.more-dropdown-item.danger:hover {
  background: #fef2f2;
}
.more-dropdown-divider {
  height: 1px;
  background: #e5e7eb;
  margin: 2px 0;
}

/* ===== COUNT BADGE ===== */
.count-badge {
  background: #eef6ee;
  color: #006c49;
  font-size: 11px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 8px;
}

/* ===== STATUS BADGE ===== */
.status-badge {
  transition: all 0.3s ease;
}
.status-badge:hover {
  transform: scale(1.05);
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
  transition: all 0.3s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
.modal-enter-from .modal-content,
.modal-leave-to .modal-content {
  transform: scale(0.95) translateY(10px);
}
.modal-content {
  transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

/* ===== FADE IN ANIMATIONS ===== */
.fade-in {
  animation: fadeIn 0.5s ease-out forwards;
}
.fade-in-delay {
  animation: fadeIn 0.5s ease-out 0.1s forwards;
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
