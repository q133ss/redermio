import { CommonModule } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import { ChangeDetectionStrategy, Component, DestroyRef, Input, OnInit, computed, inject, signal } from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { forkJoin } from 'rxjs';

import { ServicesApiService } from '../../services/data-access/services-api.service';
import { ServiceListItem } from '../../services/models/service.models';
import { BeneficiaryServicesApiService } from '../data-access/beneficiary-services-api.service';
import {
  ApiValidationErrorResponse,
  BeneficiaryAssignedService,
  BeneficiaryServiceAssignmentPayload,
} from '../models/beneficiary-service.models';

@Component({
  selector: 'app-beneficiary-services-block',
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './beneficiary-services-block.component.html',
  styleUrl: './beneficiary-services-block.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class BeneficiaryServicesBlockComponent implements OnInit {
  private readonly destroyRef = inject(DestroyRef);
  private readonly formBuilder = inject(FormBuilder);
  private readonly servicesApiService = inject(ServicesApiService);
  private readonly beneficiaryServicesApiService = inject(BeneficiaryServicesApiService);

  protected readonly form = this.formBuilder.nonNullable.group({
    provided_at: [this.getTodayDate(), Validators.required],
    comment: [''],
  });

  @Input({ required: true }) public beneficiaryId = 0;

  protected readonly availableServices = signal<ServiceListItem[]>([]);
  protected readonly assignments = signal<BeneficiaryAssignedService[]>([]);
  protected readonly selectedServiceIds = signal<number[]>([]);
  protected readonly serviceSearch = signal('');
  protected readonly isLoading = signal(true);
  protected readonly isSaving = signal(false);
  protected readonly deletingAssignmentId = signal<number | null>(null);
  protected readonly errorMessage = signal<string | null>(null);
  protected readonly successMessage = signal<string | null>(null);
  protected readonly formErrors = signal<string[]>([]);
  protected readonly filteredServices = computed(() => {
    const query = this.serviceSearch().trim().toLowerCase();

    if (query === '') {
      return this.availableServices();
    }

    return this.availableServices().filter((service) => {
      const haystack = `${service.name} ${service.description ?? ''}`.toLowerCase();

      return haystack.includes(query);
    });
  });

  public ngOnInit(): void {
    if (this.beneficiaryId < 1) {
      this.errorMessage.set('Некорректный идентификатор благополучателя.');
      this.isLoading.set(false);
      return;
    }

    this.loadData();
  }

  protected submit(): void {
    if (this.isSaving()) {
      return;
    }

    if (this.selectedServiceIds().length === 0) {
      this.formErrors.set(['Выберите хотя бы одну услугу для назначения.']);
      return;
    }

    if (this.form.invalid) {
      this.form.markAllAsTouched();
      this.formErrors.set(['Укажите дату оказания услуги.']);
      return;
    }

    const formValue = this.form.getRawValue();
    const payload: BeneficiaryServiceAssignmentPayload = {
      items: this.selectedServiceIds().map((serviceId) => ({
        service_id: serviceId,
        provided_at: formValue.provided_at,
        comment: this.normalizeOptionalValue(formValue.comment),
      })),
    };

    this.isSaving.set(true);
    this.errorMessage.set(null);
    this.successMessage.set(null);
    this.formErrors.set([]);

    this.beneficiaryServicesApiService
      .create(this.beneficiaryId, payload)
      .pipe(takeUntilDestroyed(this.destroyRef))
      .subscribe({
        next: () => {
          this.isSaving.set(false);
          this.selectedServiceIds.set([]);
          this.serviceSearch.set('');
          this.form.patchValue({
            provided_at: this.getTodayDate(),
            comment: '',
          });
          this.successMessage.set('Услуги назначены благополучателю.');
          this.loadAssignments();
        },
        error: (error: HttpErrorResponse) => {
          this.applyValidationError(error);
          this.isSaving.set(false);
        },
      });
  }

  protected deleteAssignment(assignment: BeneficiaryAssignedService): void {
    if (
      this.deletingAssignmentId() !== null ||
      !window.confirm(`Удалить привязку услуги "${assignment.service.name}"?`)
    ) {
      return;
    }

    this.deletingAssignmentId.set(assignment.id);
    this.errorMessage.set(null);
    this.successMessage.set(null);

    this.beneficiaryServicesApiService
      .delete(this.beneficiaryId, assignment.id)
      .pipe(takeUntilDestroyed(this.destroyRef))
      .subscribe({
        next: () => {
          this.assignments.set(this.assignments().filter((item) => item.id !== assignment.id));
          this.deletingAssignmentId.set(null);
          this.successMessage.set('Привязка услуги удалена.');
        },
        error: () => {
          this.errorMessage.set('Не удалось удалить привязку услуги.');
          this.deletingAssignmentId.set(null);
        },
      });
  }

  protected toggleServiceSelection(serviceId: number, checked: boolean): void {
    const selectedIds = this.selectedServiceIds();

    if (checked) {
      if (selectedIds.includes(serviceId)) {
        return;
      }

      this.selectedServiceIds.set([...selectedIds, serviceId]);
      this.formErrors.set([]);
      return;
    }

    this.selectedServiceIds.set(selectedIds.filter((id) => id !== serviceId));
  }

  protected isSelected(serviceId: number): boolean {
    return this.selectedServiceIds().includes(serviceId);
  }

  protected isDeletingAssignment(assignmentId: number): boolean {
    return this.deletingAssignmentId() === assignmentId;
  }

  protected trackServiceById(_: number, service: ServiceListItem): number {
    return service.id;
  }

  protected trackAssignmentById(_: number, assignment: BeneficiaryAssignedService): number {
    return assignment.id;
  }

  protected setServiceSearch(value: string): void {
    this.serviceSearch.set(value);
  }

  private loadData(): void {
    this.isLoading.set(true);
    this.errorMessage.set(null);

    forkJoin({
      servicesResponse: this.servicesApiService.getList({
        search: '',
        is_active: 'true',
        page: 1,
        per_page: 100,
      }),
      assignmentsResponse: this.beneficiaryServicesApiService.getList(this.beneficiaryId),
    })
      .pipe(takeUntilDestroyed(this.destroyRef))
      .subscribe({
        next: ({ servicesResponse, assignmentsResponse }) => {
          this.availableServices.set(servicesResponse.data);
          this.assignments.set(assignmentsResponse.data);
          this.isLoading.set(false);
        },
        error: () => {
          this.errorMessage.set('Не удалось загрузить услуги благополучателя.');
          this.isLoading.set(false);
        },
      });
  }

  private loadAssignments(): void {
    this.beneficiaryServicesApiService
      .getList(this.beneficiaryId)
      .pipe(takeUntilDestroyed(this.destroyRef))
      .subscribe({
        next: (response) => {
          this.assignments.set(response.data);
        },
        error: () => {
          this.errorMessage.set('Не удалось обновить список назначенных услуг.');
        },
      });
  }

  private applyValidationError(error: HttpErrorResponse): void {
    if (error.status === 422 && error.error) {
      const response = error.error as ApiValidationErrorResponse;
      const errorMessages = Object.values(response.errors ?? {});

      this.formErrors.set(
        errorMessages.length > 0 ? errorMessages : ['Проверьте данные назначения услуги.'],
      );
      this.errorMessage.set(response.message ?? null);
      return;
    }

    this.errorMessage.set('Не удалось назначить услуги благополучателю.');
  }

  private normalizeOptionalValue(value: string): string | null {
    const normalized = value.trim();

    return normalized === '' ? null : normalized;
  }

  private getTodayDate(): string {
    return new Date().toISOString().slice(0, 10);
  }
}
