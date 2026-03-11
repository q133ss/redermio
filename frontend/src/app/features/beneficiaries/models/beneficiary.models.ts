export interface BeneficiaryListItem {
  id: number;
  type: 'individual' | 'legal_entity';
  full_name: string;
  short_name: string | null;
  document_number: string | null;
  tax_number: string | null;
  phone: string | null;
  email: string | null;
  address: string | null;
  notes: string | null;
  created_at: string;
  updated_at: string;
}

export interface PaginationMeta {
  page: number;
  per_page: number;
  total: number;
  last_page: number;
}

export interface BeneficiaryFilters {
  search: string | null;
  type: string | null;
}

export interface BeneficiaryListQuery {
  search: string;
  type: '' | 'individual' | 'legal_entity';
}

export interface BeneficiaryListResponse {
  data: BeneficiaryListItem[];
  meta: PaginationMeta;
  filters: BeneficiaryFilters;
}
