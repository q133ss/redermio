import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { Observable } from 'rxjs';

import { API_BASE_URL } from '../../../core/config/runtime-config';
import {
  ApiMessageResponse,
  BeneficiaryAssignedServicesResponse,
  BeneficiaryServiceAssignmentPayload,
  BeneficiaryServiceAssignmentResponse,
} from '../models/beneficiary-service.models';

@Injectable({
  providedIn: 'root',
})
export class BeneficiaryServicesApiService {
  private readonly httpClient = inject(HttpClient);
  private readonly apiBaseUrl = inject(API_BASE_URL);

  public getList(beneficiaryId: number): Observable<BeneficiaryAssignedServicesResponse> {
    return this.httpClient.get<BeneficiaryAssignedServicesResponse>(`${this.apiBaseUrl}/beneficiaries/${beneficiaryId}/services`);
  }

  public create(
    beneficiaryId: number,
    payload: BeneficiaryServiceAssignmentPayload,
  ): Observable<BeneficiaryServiceAssignmentResponse> {
    return this.httpClient.post<BeneficiaryServiceAssignmentResponse>(
      `${this.apiBaseUrl}/beneficiaries/${beneficiaryId}/services`,
      payload,
    );
  }

  public delete(beneficiaryId: number, assignmentId: number): Observable<ApiMessageResponse> {
    return this.httpClient.delete<ApiMessageResponse>(
      `${this.apiBaseUrl}/beneficiaries/${beneficiaryId}/services/${assignmentId}`,
    );
  }
}
