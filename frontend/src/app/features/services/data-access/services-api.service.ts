import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { Observable } from 'rxjs';

import { API_BASE_URL } from '../../../core/config/runtime-config';
import {
  ApiMessageResponse,
  ServiceListQuery,
  ServiceListResponse,
  ServicePayload,
  ServiceResponse,
} from '../models/service.models';

@Injectable({
  providedIn: 'root',
})
export class ServicesApiService {
  private readonly httpClient = inject(HttpClient);
  private readonly apiBaseUrl = inject(API_BASE_URL);

  public getList(filters: ServiceListQuery): Observable<ServiceListResponse> {
    const params: Record<string, string> = {
      page: String(filters.page),
      per_page: String(filters.per_page),
    };

    if (filters.search !== '') {
      params['search'] = filters.search;
    }

    if (filters.is_active !== '') {
      params['is_active'] = filters.is_active;
    }

    return this.httpClient.get<ServiceListResponse>(`${this.apiBaseUrl}/services`, {
      params,
    });
  }

  public create(payload: ServicePayload): Observable<ServiceResponse> {
    return this.httpClient.post<ServiceResponse>(`${this.apiBaseUrl}/services`, payload);
  }

  public update(id: number, payload: ServicePayload): Observable<ServiceResponse> {
    return this.httpClient.put<ServiceResponse>(`${this.apiBaseUrl}/services/${id}`, payload);
  }

  public delete(id: number): Observable<ApiMessageResponse> {
    return this.httpClient.delete<ApiMessageResponse>(`${this.apiBaseUrl}/services/${id}`);
  }
}
