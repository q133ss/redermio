import { ChangeDetectionStrategy, Component, effect, inject, input, output } from '@angular/core';
import { FormBuilder, ReactiveFormsModule } from '@angular/forms';

import { BeneficiaryListQuery } from '../models/beneficiary.models';

@Component({
  selector: 'app-beneficiary-filters',
  imports: [ReactiveFormsModule],
  templateUrl: './beneficiary-filters.component.html',
  styleUrl: './beneficiary-filters.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class BeneficiaryFiltersComponent {
  private readonly formBuilder = inject(FormBuilder);

  protected readonly form = this.formBuilder.nonNullable.group({
    search: '',
    type: '' as BeneficiaryListQuery['type'],
  });

  public readonly currentFilters = input.required<BeneficiaryListQuery>();
  public readonly isLoading = input(false);
  public readonly filtersApplied = output<BeneficiaryListQuery>();

  public constructor() {
    effect(() => {
      const currentFilters = this.currentFilters();

      this.form.patchValue(
        {
          search: currentFilters.search,
          type: currentFilters.type,
        },
        { emitEvent: false },
      );
    });

    effect(() => {
      if (this.isLoading()) {
        this.form.disable({ emitEvent: false });
        return;
      }

      this.form.enable({ emitEvent: false });
    });
  }

  protected submit(): void {
    const value = this.form.getRawValue();

    this.filtersApplied.emit({
      search: value.search.trim(),
      type: value.type,
    });
  }

  protected reset(): void {
    const defaultFilters: BeneficiaryListQuery = {
      search: '',
      type: '',
    };

    this.form.reset(defaultFilters);
    this.filtersApplied.emit(defaultFilters);
  }
}
