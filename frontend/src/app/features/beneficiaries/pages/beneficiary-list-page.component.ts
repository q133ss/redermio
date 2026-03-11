import { CommonModule } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import { ChangeDetectionStrategy, Component, DestroyRef, inject, signal } from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { RouterLink } from '@angular/router';

import { BeneficiariesApiService } from '../data-access/beneficiaries-api.service';
import { BeneficiaryFiltersComponent } from '../components/beneficiary-filters.component';
import {
  BeneficiaryImportErrorResponse,
  BeneficiaryListItem,
  BeneficiaryListQuery,
  ImportErrorRow,
  ImportSummary,
  PaginationMeta,
} from '../models/beneficiary.models';

@Component({
  selector: 'app-beneficiary-list-page',
  imports: [CommonModule, RouterLink, BeneficiaryFiltersComponent],
  templateUrl: './beneficiary-list-page.component.html',
  styleUrl: './beneficiary-list-page.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class BeneficiaryListPageComponent {
  private readonly destroyRef = inject(DestroyRef);
  private readonly beneficiariesApiService = inject(BeneficiariesApiService);

  protected readonly beneficiaries = signal<BeneficiaryListItem[]>([]);
  protected readonly isLoading = signal(true);
  protected readonly isImporting = signal(false);
  protected readonly deletingId = signal<number | null>(null);
  protected readonly errorMessage = signal<string | null>(null);
  protected readonly importMessage = signal<string | null>(null);
  protected readonly importSummary = signal<ImportSummary | null>(null);
  protected readonly importErrorRows = signal<ImportErrorRow[]>([]);
  protected readonly currentFilters = signal<BeneficiaryListQuery>({
    search: '',
    type: '',
    page: 1,
    per_page: 20,
  });
  protected readonly meta = signal<PaginationMeta>({
    page: 1,
    per_page: 20,
    total: 0,
    last_page: 1,
  });

  public constructor() {
    this.loadBeneficiaries();
  }

  protected reload(): void {
    this.loadBeneficiaries();
  }

  protected applyFilters(filters: BeneficiaryListQuery): void {
    this.loadBeneficiaries(filters);
  }

  protected getExportUrl(): string {
    return this.beneficiariesApiService.getExportUrl(this.currentFilters());
  }

  protected getImportTemplateUrl(): string {
    return this.beneficiariesApiService.getImportTemplateUrl();
  }

  protected goToPage(page: number): void {
    const meta = this.meta();

    if (page < 1 || page > meta.last_page || page === meta.page) {
      return;
    }

    this.loadBeneficiaries({
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

  protected trackById(_: number, beneficiary: BeneficiaryListItem): number {
    return beneficiary.id;
  }

  protected isDeleting(id: number): boolean {
    return this.deletingId() === id;
  }

  protected getTypeLabel(type: BeneficiaryListItem['type']): string {
    return type === 'individual' ? 'Физическое лицо' : 'Юридическое лицо';
  }

  protected deleteBeneficiary(beneficiary: BeneficiaryListItem): void {
    if (this.deletingId() !== null || !window.confirm(`Удалить благополучателя "${beneficiary.full_name}"?`)) {
      return;
    }

    this.deletingId.set(beneficiary.id);
    this.errorMessage.set(null);

    this.beneficiariesApiService.delete(beneficiary.id).subscribe({
      next: () => {
        const fallbackPage = this.beneficiaries().length === 1 && this.meta().page > 1 ? this.meta().page - 1 : this.meta().page;

        this.deletingId.set(null);
        this.loadBeneficiaries({
          ...this.currentFilters(),
          page: fallbackPage,
        });
      },
      error: (_error: HttpErrorResponse) => {
        this.errorMessage.set('Не удалось удалить благополучателя.');
        this.deletingId.set(null);
      },
    });
  }

  protected importFile(event: Event): void {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (!file) {
      return;
    }

    this.isImporting.set(true);
    this.importMessage.set(null);
    this.importSummary.set(null);
    this.importErrorRows.set([]);

    this.beneficiariesApiService.uploadImportFile(file).subscribe({
      next: (response) => {
        this.importMessage.set(response.message);
        this.importSummary.set(response.summary);
        this.importErrorRows.set([]);
        this.isImporting.set(false);
        target.value = '';
        this.loadBeneficiaries({
          ...this.currentFilters(),
          page: 1,
        });
      },
      error: (error: HttpErrorResponse) => {
        this.applyImportError(error);
        this.isImporting.set(false);
        target.value = '';
      },
    });
  }

  private loadBeneficiaries(filters: BeneficiaryListQuery = this.currentFilters()): void {
    this.isLoading.set(true);
    this.errorMessage.set(null);
    this.currentFilters.set(filters);

    this.beneficiariesApiService
      .getList(filters)
      .pipe(takeUntilDestroyed(this.destroyRef))
      .subscribe({
        next: (response) => {
          this.beneficiaries.set(response.data);
          this.meta.set(response.meta);
          this.currentFilters.set({
            search: response.filters.search ?? '',
            type: response.filters.type === 'individual' || response.filters.type === 'legal_entity' ? response.filters.type : '',
            page: response.meta.page,
            per_page: response.meta.per_page,
          });
          this.isLoading.set(false);
        },
        error: () => {
          this.errorMessage.set('Не удалось загрузить список благополучателей.');
          this.isLoading.set(false);
        },
      });
  }

  private applyImportError(error: HttpErrorResponse): void {
    if (error.status === 422 && error.error) {
      const response = error.error as BeneficiaryImportErrorResponse;
      this.importMessage.set(response.message ?? 'Не удалось импортировать файл.');
      this.importSummary.set(response.summary ?? null);
      this.importErrorRows.set(response.error_rows ?? []);
      return;
    }

    this.importMessage.set('Не удалось импортировать файл.');
    this.importSummary.set(null);
    this.importErrorRows.set([]);
  }
}
