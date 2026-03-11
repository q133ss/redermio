import { HttpErrorResponse } from '@angular/common/http';
import { ChangeDetectionStrategy, Component, inject, signal } from '@angular/core';
import { Router, RouterLink } from '@angular/router';

import { BeneficiaryFormComponent } from '../components/beneficiary-form.component';
import { BeneficiariesApiService } from '../data-access/beneficiaries-api.service';
import { ApiValidationErrorResponse, BeneficiaryPayload } from '../models/beneficiary.models';

@Component({
  selector: 'app-beneficiary-create-page',
  imports: [RouterLink, BeneficiaryFormComponent],
  templateUrl: './beneficiary-create-page.component.html',
  styleUrl: './beneficiary-create-page.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class BeneficiaryCreatePageComponent {
  private readonly router = inject(Router);
  private readonly beneficiariesApiService = inject(BeneficiariesApiService);

  protected readonly isSaving = signal(false);
  protected readonly errorMessage = signal<string | null>(null);
  protected readonly fieldErrors = signal<Record<string, string>>({});

  protected save(payload: BeneficiaryPayload): void {
    this.isSaving.set(true);
    this.errorMessage.set(null);
    this.fieldErrors.set({});

    this.beneficiariesApiService.create(payload).subscribe({
      next: (response) => {
        void this.router.navigate(['/beneficiaries', response.data.id, 'edit']);
      },
      error: (error: HttpErrorResponse) => {
        this.applyRequestError(error);
        this.isSaving.set(false);
      },
    });
  }

  private applyRequestError(error: HttpErrorResponse): void {
    if (error.status === 422 && error.error) {
      const response = error.error as ApiValidationErrorResponse;
      this.fieldErrors.set(response.errors ?? {});
      this.errorMessage.set(response.message ?? 'Проверьте введённые данные.');
      return;
    }

    this.errorMessage.set('Не удалось сохранить благополучателя.');
  }
}
