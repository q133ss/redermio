import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { Observable } from 'rxjs';

import { API_BASE_URL } from '../../../core/config/runtime-config';
import {
  ApiMessageResponse,
  BeneficiaryListQuery,
  BeneficiaryListResponse,
  BeneficiaryPayload,
  BeneficiaryResponse,
} from '../models/beneficiary.models';

@Injectable({
  providedIn: 'root',
})
export class BeneficiariesApiService {
  private readonly httpClient = inject(HttpClient);
  private readonly apiBaseUrl = inject(API_BASE_URL);

  public getList(filters: BeneficiaryListQuery): Observable<BeneficiaryListResponse> {
    const params: Record<string, string> = {
      page: String(filters.page),
      per_page: String(filters.per_page),
    };

    if (filters.search !== '') {
      params['search'] = filters.search;
    }

    if (filters.type !== '') {
      params['type'] = filters.type;
    }

    return this.httpClient.get<BeneficiaryListResponse>(`${this.apiBaseUrl}/beneficiaries`, {
      params,
    });
  }

  public getById(id: number): Observable<BeneficiaryResponse> {
    return this.httpClient.get<BeneficiaryResponse>(`${this.apiBaseUrl}/beneficiaries/${id}`);
  }

  public create(payload: BeneficiaryPayload): Observable<BeneficiaryResponse> {
    return this.httpClient.post<BeneficiaryResponse>(`${this.apiBaseUrl}/beneficiaries`, payload);
  }

  public update(id: number, payload: BeneficiaryPayload): Observable<BeneficiaryResponse> {
    return this.httpClient.put<BeneficiaryResponse>(`${this.apiBaseUrl}/beneficiaries/${id}`, payload);
  }

  public delete(id: number): Observable<ApiMessageResponse> {
    return this.httpClient.delete<ApiMessageResponse>(`${this.apiBaseUrl}/beneficiaries/${id}`);
  }
}
