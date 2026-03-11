export interface PaginationMeta {
  page: number;
  per_page: number;
  total: number;
  last_page: number;
}

export interface ApiMessageResponse {
  message: string;
}

export interface ServiceListItem {
  id: number;
  name: string;
  description: string | null;
  is_active: boolean;
  created_at: string | null;
  updated_at: string | null;
}

export interface ServicePayload {
  name: string;
  description: string | null;
  is_active: boolean;
}

export interface ServiceListQuery {
  search: string;
  is_active: '' | 'true' | 'false';
  page: number;
  per_page: number;
}

export interface ServiceListResponse {
  data: ServiceListItem[];
  meta: PaginationMeta;
  filters: {
    search: string | null;
    is_active: boolean | null;
  };
}

export interface ServiceResponse {
  data: ServiceListItem;
}
