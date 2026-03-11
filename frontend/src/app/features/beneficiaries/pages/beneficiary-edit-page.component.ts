import { HttpErrorResponse } from '@angular/common/http';
import { ChangeDetectionStrategy, Component, DestroyRef, inject, signal } from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';

import { BeneficiaryFormComponent } from '../components/beneficiary-form.component';
import { BeneficiariesApiService } from '../data-access/beneficiaries-api.service';
import { ApiValidationErrorResponse, BeneficiaryPayload } from '../models/beneficiary.models';

@Component({
  selector: 'app-beneficiary-edit-page',
  imports: [RouterLink, BeneficiaryFormComponent],
  templateUrl: './beneficiary-edit-page.component.html',
  styleUrl: './beneficiary-edit-page.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class BeneficiaryEditPageComponent {
  private readonly destroyRef = inject(DestroyRef);
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  private readonly beneficiariesApiService = inject(BeneficiariesApiService);
  private readonly beneficiaryId = Number(this.route.snapshot.paramMap.get('id'));

  protected readonly beneficiary = signal<BeneficiaryPayload | null>(null);
  protected readonly isLoading = signal(true);
  protected readonly isSaving = signal(false);
  protected readonly isDeleting = signal(false);
  protected readonly errorMessage = signal<string | null>(null);
  protected readonly fieldErrors = signal<Record<string, string>>({});
  protected readonly successMessage = signal<string | null>(null);

  public constructor() {
    this.loadBeneficiary();
  }

  protected save(payload: BeneficiaryPayload): void {
    this.isSaving.set(true);
    this.errorMessage.set(null);
    this.successMessage.set(null);
    this.fieldErrors.set({});

    this.beneficiariesApiService.update(this.beneficiaryId, payload).subscribe({
      next: (response) => {
        this.beneficiary.set(this.mapPayload(response.data));
        this.successMessage.set('Изменения сохранены.');
        this.isSaving.set(false);
      },
      error: (error: HttpErrorResponse) => {
        this.applyRequestError(error);
        this.isSaving.set(false);
      },
    });
  }

  protected deleteBeneficiary(): void {
    if (this.isDeleting() || !window.confirm('Удалить благополучателя?')) {
      return;
    }

    this.isDeleting.set(true);
    this.errorMessage.set(null);

    this.beneficiariesApiService.delete(this.beneficiaryId).subscribe({
      next: () => {
        void this.router.navigate(['/beneficiaries']);
      },
      error: () => {
        this.errorMessage.set('Не удалось удалить благополучателя.');
        this.isDeleting.set(false);
      },
    });
  }

  private loadBeneficiary(): void {
    if (!Number.isInteger(this.beneficiaryId) || this.beneficiaryId < 1) {
      this.errorMessage.set('Некорректный идентификатор благополучателя.');
      this.isLoading.set(false);
      return;
    }

    this.beneficiariesApiService
      .getById(this.beneficiaryId)
      .pipe(takeUntilDestroyed(this.destroyRef))
      .subscribe({
        next: (response) => {
          this.beneficiary.set(this.mapPayload(response.data));
          this.isLoading.set(false);
        },
        error: (error: HttpErrorResponse) => {
          this.errorMessage.set(error.status === 404 ? 'Благополучатель не найден.' : 'Не удалось загрузить карточку благополучателя.');
          this.isLoading.set(false);
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

    this.errorMessage.set('Не удалось сохранить изменения.');
  }

  private mapPayload(data: BeneficiaryPayload): BeneficiaryPayload {
    return {
      type: data.type,
      full_name: data.full_name,
      short_name: data.short_name,
      document_number: data.document_number,
      tax_number: data.tax_number,
      phone: data.phone,
      email: data.email,
      address: data.address,
      notes: data.notes,
    };
  }
}
