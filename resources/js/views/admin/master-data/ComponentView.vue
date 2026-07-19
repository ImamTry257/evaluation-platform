<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useComponent } from '@/hooks/useComponent'

const route = useRoute()
const questionnaireId = Number(route.params.instrumentId)

const {
  components,
  breadCrumbList,
  loading,
  error,
  currentPage,
  perPage,
  totalItems,
  totalPages,
  searchQuery,
  fetchComponents,
  createComponent,
  updateComponent,
  deleteComponent,
  onSearch,
} = useComponent(questionnaireId)

// Modal state
const showFormModal = ref(false)
const showDeleteModal = ref(false)
const showViewModal = ref(false)
const formMode = ref<'add' | 'edit'>('add')
const editingId = ref<number | null>(null)
const deletingComponent = ref<any>(null)
const viewingComponent = ref<any>(null)

// Form
const form = ref({
  name: '',
  description: '',
  is_active: 1,
})

// Load data on mount
onMounted(() => {
  fetchComponents()
})

// Form handlers
function openAddModal() {
  formMode.value = 'add'
  editingId.value = null
  form.value = { name: '', description: '', is_active: 1 }
  showFormModal.value = true
}

function openEditModal(c: any) {
  formMode.value = 'edit'
  editingId.value = c.id
  form.value = { name: c.name, description: c.description || '', is_active: c.is_active }
  showFormModal.value = true
}
async function handleFormSubmit() {
  loading.value = true
  try {
    if (formMode.value === 'add') {
      await createComponent({
        questionnaireId: questionnaireId,
        name: form.value.name,
        description: form.value.description,
        is_active: form.value.is_active,
      })
    } else if (editingId.value) {
      await updateComponent(editingId.value, {
        questionnaireId: questionnaireId,
        name: form.value.name,
        description: form.value.description,
        is_active: form.value.is_active,
      })
    }
    showFormModal.value = false
  } catch (err) {
    // Error handled by hook
  } finally {
    loading.value = false
  }
}
function openDeleteModal(c: any) {
  deletingComponent.value = c
  showDeleteModal.value = true
}

async function confirmDelete() {
  if (deletingComponent.value) {
    await deleteComponent(deletingComponent.value.id)
    showDeleteModal.value = false
    deletingComponent.value = null
  }
}

// More menu actions
function openViewModal(item: any) {
  viewingComponent.value = item
  console.log(viewingComponent,'ccc')
  showViewModal.value = true
}

function formatDate(dateStr: string): string {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  return d.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

async function toggleStatus(item: any) {
  const newStatus = item.is_active === 1 ? 0 : 1
  await updateComponent(item.id, {
    questionnaireId: questionnaireId,
    name: item.name,
    description: item.description,
    is_active: newStatus,
  })
  fetchComponents(currentPage.value)
}

function handleEdit(item: any) {
  openEditModal(item)
}

function handleDelete(item: any) {
  openDeleteModal(item)
}
</script>

<template>
  <div class="p-8 max-w-[1440px] w-full mx-auto">
    <!-- Breadcrumb -->
    <nav class="mb-6 flex items-center gap-2 text-sm">
      <RouterLink to="/admin/instrument" class="text-primary font-medium hover:underline cursor-pointer">Instrument Penelitian</RouterLink>
      <span class="text-outline">›</span>
      <RouterLink v-if="breadCrumbList?.questionnaire" :to="`/admin/instrument/${breadCrumbList.questionnaire.id}/component`" class="text-primary font-medium hover:underline cursor-pointer">
        {{ breadCrumbList.questionnaire.title }}
      </RouterLink>
      <span v-else class="text-primary font-medium">-</span>
      <span class="text-outline">›</span>
      <span class="text-on-surface font-semibold">Components</span>
    </nav>
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-4 fade-in">
      <div class="flex items-center gap-4">
        <RouterLink to="/admin/instrument" class="back-btn flex items-center gap-1 text-primary text-sm font-medium hover:bg-primary/10 px-3 py-2 rounded-lg transition-colors no-underline">
          <span class="material-symbols-outlined text-[18px]">arrow_back</span>
          Kembali
        </RouterLink>
      </div>
      <button
        @click="openAddModal"
        class="bg-primary hover:bg-primary/90 text-on-primary font-body-base font-semibold px-6 py-3 rounded-xl flex items-center gap-2 shadow-sm transition-all active:scale-95"
      >
        <span class="material-symbols-outlined">add</span>
        Tambah Component
      </button>
    </div>

    <!-- Error Alert -->
    <div v-if="error" class="mb-4 p-4 bg-error/10 border border-error/20 rounded-xl text-error text-body-sm">
      {{ error }}
    </div>

    <!-- Action Bar & Content Card -->
    <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-outline-variant/10 overflow-hidden fade-in-delay">
      <!-- Action Bar -->
      <div class="p-6 border-b border-outline-variant/10 bg-surface-container-low/30">
        <div class="relative">
          <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline">search</span>
          <input
            :value="searchQuery"
            @input="onSearch(($event.target as HTMLInputElement).value)"
            class="search-input w-full bg-white border border-outline-variant/50 rounded-xl pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-primary-container outline-none transition-all text-body-sm font-body-sm"
            placeholder="Cari nama komponen..."
            type="text"
          />
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="p-12 text-center">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-primary border-t-transparent"></div>
        <p class="text-body-sm text-on-surface-variant mt-3">Memuat data...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="components.length === 0" class="p-12 text-center">
        <span class="material-symbols-outlined text-4xl text-outline">folder_open</span>
        <p class="text-body-base text-on-surface-variant mt-3">Belum ada komponen</p>
        <button @click="openAddModal" class="mt-4 text-primary text-body-sm font-semibold hover:underline">
          + Tambah Komponen Pertama
        </button>
      </div>

      <!-- Data Table -->
      <div v-else class="overflow-x-auto data-table-scroll">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-surface-container-low/50">
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">No</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Nama Komponen</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Deskripsi</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Urutan</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase">Status</th>
              <th class="px-6 py-4 font-label-caps text-label-caps text-outline uppercase text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-outline-variant/10">
            <tr
                v-for="c in components"
                :key="c.id"
                class="table-row hover:bg-surface-container-low/30 transition-colors"
                :class="{ 'opacity-50': c.is_active === 0 }"
              >
                <td class="px-6 py-5">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                      <span class="material-symbols-outlined text-primary text-[20px]">folder</span>
                    </div>
                    <div>
                      <span class="font-body-base font-semibold text-on-surface">{{ c.name }}</span>
                      <p class="text-body-sm text-on-surface-variant">{{ c.questionnaire?.title || '-' }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-5">
                  <span
                    class="status-badge inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                    :class="c.is_active === 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                    @click="toggleStatus(c)"
                    style="cursor: pointer;"
                  >
                    {{ c.is_active === 1 ? 'Active' : 'Inactive' }}
                  </span>
                </td>
              <td class="px-6 py-5">
                <span class="text-body-sm text-on-surface-variant">{{ c.description || '-' }}</span>
              </td>
              <td class="px-6 py-5">
                <span class="count-badge">{{ c.orderNumber }}</span>
              </td>
              <td class="px-6 py-5">
                <div class="flex items-center justify-center gap-1">
                  <RouterLink
                    :to="`/admin/component/${c.id}/sub-component`"
                    class="action-btn"
                    title="Lihat Sub-Komponen"
                  >
                    <span class="material-symbols-outlined text-[18px]">subdirectory_arrow_right</span>
                  </RouterLink>
                  <button
                    @click="openViewModal(c)"
                    class="action-btn"
                    title="Detail"
                  >
                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                  </button>
                  <button
                    @click="handleEdit(c)"
                    class="action-btn"
                    title="Edit"
                  >
                    <span class="material-symbols-outlined text-[18px]">edit</span>
                  </button>
                  <button
                    @click="handleDelete(c)"
                    class="action-btn danger"
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

      <!-- Footer -->
      <div v-if="!loading && components.length > 0" class="px-6 py-4 bg-surface-container-low/30 border-t border-outline-variant/10 flex items-center justify-between">
        <p class="text-body-sm font-body-sm text-on-surface-variant">
          Menampilkan <span class="font-semibold text-on-surface">{{ components.length }}</span> dari <span class="font-semibold text-on-surface">{{ totalItems }}</span> komponen
        </p>
        <!-- Pagination -->
        <div v-if="totalPages > 1" class="flex items-center gap-2">
          <button
            @click="fetchComponents(currentPage - 1)"
            :disabled="currentPage <= 1"
            class="px-3 py-1 rounded-lg border border-outline-variant/50 text-body-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-surface-container-low"
          >
            Sebelumnya
          </button>
          <span class="text-body-sm text-on-surface-variant">Hal {{ currentPage }} / {{ totalPages }}</span>
          <button
            @click="fetchComponents(currentPage + 1)"
            :disabled="currentPage >= totalPages"
            class="px-3 py-1 rounded-lg border border-outline-variant/50 text-body-sm disabled:opacity-50 disabled:cursor-not-allowed hover:bg-surface-container-low"
          >
            Selanjutnya
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
                rows="2"
                placeholder="Deskripsi komponen evaluasi..."
                class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface placeholder:text-outline focus:ring-2 focus:ring-primary-container outline-none transition-all resize-none"
              ></textarea>
            </div>

            <!-- Status -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Status</label>
              <select
                v-model="form.is_active"
                class="modal-input w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface focus:ring-2 focus:ring-primary-container outline-none transition-all"
              >
                <option :value="1">Active</option>
                <option :value="0">Inactive</option>
              </select>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="flex items-center justify-end gap-3 p-6 border-t border-outline-variant/10">
            <button @click="showFormModal = false" class="px-4 py-2.5 rounded-xl bg-surface-container-high text-on-surface font-body-base font-semibold hover:bg-surface-container-highest transition-all">Batal</button>
            <button
              @click="handleFormSubmit"
              :disabled="!form.name || loading"
              class="px-8 py-2 rounded-xl bg-primary text-on-primary font-body-base font-semibold shadow-sm transition-all hover:bg-primary/90 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ loading ? 'Menyimpan...' : (formMode === 'add' ? 'Simpan' : 'Ubah') }}
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
                  <p class="text-body-sm text-on-surface-variant">{{ deletingComponent.description || 'Tanpa deskripsi' }}</p>
                </div>
              </div>
            </div>
            <p class="text-body-sm text-error mt-3 flex items-center gap-1.5">
              <span class="material-symbols-outlined text-[16px]">warning</span>
              Komponen akan di-soft delete dan tidak tampil di daftar. Data tetap tersimpan di database.
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
              :disabled="loading"
              class="px-5 py-2.5 rounded-xl bg-error text-on-error font-body-base font-semibold shadow-sm transition-all hover:bg-error/90 active:scale-95 disabled:opacity-50"
            >
              {{ loading ? 'Menghapus...' : 'Ya, Hapus' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
  <!-- ==================== VIEW MODAL ==================== -->
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="showViewModal"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        @click.self="showViewModal = false"
      >
        <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showViewModal = false"></div>
        <div class="relative bg-surface-container-lowest rounded-2xl shadow-xl w-full max-w-lg z-10 modal-content">
          <!-- Modal Header -->
          <div class="flex items-center justify-between p-6 border-b border-outline-variant/10">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                <span class="material-symbols-outlined text-primary">visibility</span>
              </div>
              <div>
                <h3 class="font-title-md text-title-md text-on-surface">Detail Component</h3>
                <p class="text-body-sm text-on-surface-variant">Informasi lengkap komponen</p>
              </div>
            </div>
            <button
              @click="showViewModal = false"
              class="w-8 h-8 flex items-center justify-center rounded-lg text-on-surface-variant hover:bg-surface-container transition-colors"
            >
              <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
          </div>

          <!-- Modal Body -->
          <div class="p-6 space-y-5" v-if="viewingComponent">
            <!-- Nama Component -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Nama Component</label>
              <input
                :value="viewingComponent.name"
                type="text"
                disabled
                class="w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface opacity-60 cursor-not-allowed"
              />
            </div>

            <!-- Deskripsi -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Deskripsi</label>
              <textarea
                :value="viewingComponent.description || ''"
                rows="2"
                disabled
                class="w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface opacity-60 cursor-not-allowed resize-none"
              ></textarea>
            </div>

            <!-- Status -->
            <div>
              <label class="block font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider mb-2">Status</label>
              <input
                :value="viewingComponent.isActive ? 'Active' : 'Inactive'"
                type="text"
                disabled
                class="w-full bg-surface-container-low border border-outline-variant/50 rounded-xl px-4 py-3 text-body-base font-body-base text-on-surface opacity-60 cursor-not-allowed"
              />
            </div>

            <!-- Info Tambahan -->
            <div class="pt-4 border-t border-outline-variant/20">
              <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <span class="text-secondary">Dibuat:</span>
                  <p class="font-medium text-on-surface">{{ formatDate(viewingComponent.createdAt) }}</p>
                </div>
                <div>
                  <span class="text-secondary">Diupdate:</span>
                  <p class="font-medium text-on-surface">{{ formatDate(viewingComponent.updatedAt) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="flex items-center justify-end gap-3 p-6 border-t border-outline-variant/10">
            <button
              @click="showViewModal = false"
              class="px-5 py-2.5 rounded-xl bg-primary text-on-primary font-body-base font-semibold shadow-sm transition-all hover:bg-primary/90 active:scale-95"
            >
              Tutup
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

/* ===== ACTION BUTTON ===== */
.action-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  color: #3c4a42;
  cursor: pointer;
  transition: all 0.2s ease;
}
.action-btn:hover {
  background-color: #eef6ee;
  color: #006c49;
}
.action-btn.danger {
  color: #ba1a1a;
}
.action-btn.danger:hover {
  background-color: #fef2f2;
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
