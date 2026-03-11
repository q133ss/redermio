import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { Observable } from 'rxjs';

import { API_BASE_URL } from '../../../core/config/runtime-config';
import { BeneficiaryListQuery, BeneficiaryListResponse } from '../models/beneficiary.models';

@Injectable({
  providedIn: 'root',
})
export class BeneficiariesApiService {
  private readonly httpClient = inject(HttpClient);
  private readonly apiBaseUrl = inject(API_BASE_URL);

  public getList(filters: BeneficiaryListQuery): Observable<BeneficiaryListResponse> {
    const params: Record<string, string> = {};

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
}
