import { CommonModule } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import { ChangeDetectionStrategy, Component, DestroyRef, computed, inject, signal } from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { FormsModule } from '@angular/forms';

import { ServicesApiService } from '../data-access/services-api.service';
import { ServiceFormComponent } from '../components/service-form.component';
import { PaginationMeta, ServiceListItem, ServiceListQuery, ServicePayload } from '../models/service.models';

@Component({
  selector: 'app-service-list-page',
  imports: [CommonModule, FormsModule, ServiceFormComponent],
  templateUrl: './service-list-page.component.html',
  styleUrl: './service-list-page.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ServiceListPageComponent {
  private readonly destroyRef = inject(DestroyRef);
  private readonly servicesApiService = inject(ServicesApiService);

  protected readonly services = signal<ServiceListItem[]>([]);
  protected readonly isLoading = signal(true);
  protected readonly isSaving = signal(false);
  protected readonly deletingId = signal<number | null>(null);
  protected readonly formMode = signal<'create' | 'edit'>('create');
  protected readonly editingService = signal<ServiceListItem | null>(null);
  protected readonly fieldErrors = signal<Record<string, string>>({});
  protected readonly errorMessage = signal<string | null>(null);
  protected readonly successMessage = signal<string | null>(null);
  protected readonly formInitialValue = computed<ServicePayload | null>(() => {
    const service = this.editingService();

    if (service === null) {
      return null;
    }

    return {
      name: service.name,
      description: service.description,
      is_active: service.is_active,
    };
  });
  protected readonly currentFilters = signal<ServiceListQuery>({
    search: '',
    is_active: '',
    page: 1,
    per_page: 20,
  });
  protected readonly meta = signal<PaginationMeta>({
    page: 1,
    per_page: 20,
    total: 0,
    last_page: 1,
  });
  protected searchValue = '';
  protected statusValue: '' | 'true' | 'false' = '';

  public constructor() {
    this.loadServices();
  }

  protected reload(): void {
    this.loadServices();
  }

  protected submitFilters(): void {
    this.loadServices({
      ...this.currentFilters(),
      search: this.searchValue.trim(),
      is_active: this.statusValue,
      page: 1,
    });
  }

  protected resetFilters(): void {
    this.searchValue = '';
    this.statusValue = '';

    this.loadServices({
      search: this.searchValue,
      is_active: this.statusValue,
      page: 1,
      per_page: this.currentFilters().per_page,
    });
  }

  protected startCreate(): void {
    this.formMode.set('create');
    this.editingService.set(null);
    this.fieldErrors.set({});
    this.successMessage.set(null);
  }

  protected startEdit(service: ServiceListItem): void {
    this.formMode.set('edit');
    this.editingService.set(service);
    this.fieldErrors.set({});
    this.successMessage.set(null);
  }

  protected cancelForm(): void {
    this.startCreate();
  }

  protected saveService(payload: ServicePayload): void {
    this.isSaving.set(true);
    this.fieldErrors.set({});
    this.errorMessage.set(null);
    this.successMessage.set(null);

    const editingService = this.editingService();
    const request$ = editingService === null
      ? this.servicesApiService.create(payload)
      : this.servicesApiService.update(editingService.id, payload);

    request$.subscribe({
      next: () => {
        const isCreating = editingService === null;
        this.isSaving.set(false);
        this.startCreate();
        this.successMessage.set(isCreating ? 'Услуга создана.' : 'Услуга обновлена.');
        this.loadServices({
          ...this.currentFilters(),
          page: 1,
        });
      },
      error: (error: HttpErrorResponse) => {
        if (error.status === 422 && error.error?.errors) {
          this.fieldErrors.set(error.error.errors as Record<string, string>);
        } else {
          this.errorMessage.set(
            editingService === null ? 'Не удалось создать услугу.' : 'Не удалось обновить услугу.',
          );
        }

        this.isSaving.set(false);
      },
    });
  }

  protected deleteService(service: ServiceListItem): void {
    if (this.deletingId() !== null || !window.confirm(`Удалить услугу "${service.name}"?`)) {
      return;
    }

    this.deletingId.set(service.id);
    this.errorMessage.set(null);
    this.successMessage.set(null);

    this.servicesApiService.delete(service.id).subscribe({
      next: () => {
        const fallbackPage = this.services().length === 1 && this.meta().page > 1 ? this.meta().page - 1 : this.meta().page;

        if (this.editingService()?.id === service.id) {
          this.startCreate();
        }

        this.deletingId.set(null);
        this.successMessage.set('Услуга удалена.');
        this.loadServices({
          ...this.currentFilters(),
          page: fallbackPage,
        });
      },
      error: () => {
        this.errorMessage.set('Не удалось удалить услугу.');
        this.deletingId.set(null);
      },
    });
  }

  protected goToPage(page: number): void {
    const meta = this.meta();

    if (page < 1 || page > meta.last_page || page === meta.page) {
      return;
    }

    this.loadServices({
      ...this.currentFilters(),
      page,
    });
  }

  protected hasPreviousPage(): boolean {
    return this.meta().page > 1;
  }

  protected hasNextPage(): boolean {
    return this.meta().page < this.meta().last_page;
  }

  protected trackById(_: number, service: ServiceListItem): number {
    return service.id;
  }

  protected isDeleting(id: number): boolean {
    return this.deletingId() === id;
  }

  protected getStatusLabel(isActive: boolean): string {
    return isActive ? 'Активна' : 'Неактивна';
  }

  protected getFormTitle(): string {
    return this.formMode() === 'create' ? 'Новая услуга' : 'Редактирование услуги';
  }

  protected getSubmitLabel(): string {
    return this.formMode() === 'create' ? 'Создать услугу' : 'Сохранить изменения';
  }

  private loadServices(filters: ServiceListQuery = this.currentFilters()): void {
    this.isLoading.set(true);
    this.errorMessage.set(null);
    this.currentFilters.set(filters);

    this.servicesApiService
      .getList(filters)
      .pipe(takeUntilDestroyed(this.destroyRef))
      .subscribe({
        next: (response) => {
          this.services.set(response.data);
          this.meta.set(response.meta);
          const normalizedFilters: ServiceListQuery = {
            search: response.filters.search ?? '',
            is_active: response.filters.is_active === null ? '' : String(response.filters.is_active) as 'true' | 'false',
            page: response.meta.page,
            per_page: response.meta.per_page,
          };
          this.currentFilters.set(normalizedFilters);
          this.searchValue = normalizedFilters.search;
          this.statusValue = normalizedFilters.is_active;
          this.isLoading.set(false);
        },
        error: () => {
          this.errorMessage.set('Не удалось загрузить список услуг.');
          this.isLoading.set(false);
        },
      });
  }
}
