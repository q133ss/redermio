import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { Observable } from 'rxjs';

import { API_BASE_URL } from '../../../core/config/runtime-config';
import { BeneficiaryListResponse } from '../models/beneficiary.models';

@Injectable({
  providedIn: 'root',
})
export class BeneficiariesApiService {
  private readonly httpClient = inject(HttpClient);
  private readonly apiBaseUrl = inject(API_BASE_URL);

  public getList(): Observable<BeneficiaryListResponse> {
    return this.httpClient.get<BeneficiaryListResponse>(`${this.apiBaseUrl}/beneficiaries`);
  }
}
