export interface BeneficiaryAssignedService {
  id: number;
  beneficiary_id: number;
  service_id: number;
  provided_at: string;
  comment: string | null;
  created_at: string;
  service: {
    id: number;
    name: string;
    description: string | null;
    is_active: boolean;
  };
}

export interface BeneficiaryAssignedServicesResponse {
  data: BeneficiaryAssignedService[];
}

export interface BeneficiaryServiceAssignmentItemPayload {
  service_id: number;
  provided_at: string;
  comment: string | null;
}

export interface BeneficiaryServiceAssignmentPayload {
  items: BeneficiaryServiceAssignmentItemPayload[];
}

export interface BeneficiaryServiceAssignmentResponse {
  data: BeneficiaryAssignedService[];
}

export interface ApiValidationErrorResponse {
  message: string;
  errors: Record<string, string>;
}

export interface ApiMessageResponse {
  message: string;
}
