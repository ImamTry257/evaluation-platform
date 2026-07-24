// ============================================================
// V2 Questions — Type Definitions
// ============================================================

/** Single question item returned from API */
export interface Question {
  id: number
  questionText: string
  weight: number
  isActive: boolean
  orderNumber: number
  createdAt: string
  updatedAt: string
  indicator: IndicatorNode | null
}

/** Hierarchy context nested inside each question */
export interface IndicatorNode {
  id: number
  name: string
  subComponent: SubComponentNode | null
}

export interface SubComponentNode {
  id: number
  name: string
  component: ComponentNode | null
}

export interface ComponentNode {
  id: number
  name: string
  questionnaire: QuestionnaireNode | null
}

export interface QuestionnaireNode {
  id: number
  title: string
  period: { id: number; name: string } | null
}

// ============================================================
// Cascade Tree (for dropdowns)
// ============================================================

export interface TreeInstrument {
  id: number
  title: string
  period: string | null
  components: TreeComponent[]
}

export interface TreeComponent {
  id: number
  name: string
  subComponents: TreeSubComponent[]
}

export interface TreeSubComponent {
  id: number
  name: string
  indicators: TreeIndicator[]
}

export interface TreeIndicator {
  id: number
  name: string
}

// ============================================================
// Filter & Pagination
// ============================================================

export interface QuestionFilterParams {
  search?: string
  instrumentId?: number
  componentId?: number
  subComponentId?: number
  indicatorId?: number
  page?: number
  limit?: number
  sortBy?: 'order_number' | 'created_at' | 'question_text'
  sortOrder?: 'asc' | 'desc'
}

export interface PaginationMeta {
  page: number
  limit: number
  total: number
  totalPages: number
}

export interface ListResponse<T> {
  contents: T[]
  meta: PaginationMeta
}

// ============================================================
// Form
// ============================================================

export interface QuestionFormData {
  indicatorId: number | null
  questionText: string
  weight: number
  isActive: boolean
}

export interface SelectedHierarchy {
  instrumentId: number | null
  componentId: number | null
  subComponentId: number | null
  indicatorId: number | null
}
