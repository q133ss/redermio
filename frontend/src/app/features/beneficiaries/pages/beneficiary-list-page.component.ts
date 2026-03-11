import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, DestroyRef, inject, signal } from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';

import { BeneficiariesApiService } from '../data-access/beneficiaries-api.service';
import { BeneficiaryFiltersComponent } from '../components/beneficiary-filters.component';
import { BeneficiaryListItem, BeneficiaryListQuery, PaginationMeta } from '../models/beneficiary.models';

@Component({
  selector: 'app-beneficiary-list-page',
  imports: [CommonModule, BeneficiaryFiltersComponent],
  templateUrl: './beneficiary-list-page.component.html',
  styleUrl: './beneficiary-list-page.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class BeneficiaryListPageComponent {
  private readonly destroyRef = inject(DestroyRef);
  private readonly beneficiariesApiService = inject(BeneficiariesApiService);

  protected readonly beneficiaries = signal<BeneficiaryListItem[]>([]);
  protected readonly isLoading = signal(true);
  protected readonly errorMessage = signal<string | null>(null);
  protected readonly currentFilters = signal<BeneficiaryListQuery>({
    search: '',
    type: '',
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

  protected trackById(_: number, beneficiary: BeneficiaryListItem): number {
    return beneficiary.id;
  }

  protected getTypeLabel(type: BeneficiaryListItem['type']): string {
    return type === 'individual' ? 'Физическое лицо' : 'Юридическое лицо';
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
          });
          this.isLoading.set(false);
        },
        error: () => {
          this.errorMessage.set('Не удалось загрузить список благополучателей.');
          this.isLoading.set(false);
        },
      });
  }
}
