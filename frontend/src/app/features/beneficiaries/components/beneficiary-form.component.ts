import { ChangeDetectionStrategy, Component, effect, inject, input, output } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { RouterLink } from '@angular/router';

import { BeneficiaryPayload } from '../models/beneficiary.models';

type BeneficiaryField =
  | 'type'
  | 'full_name'
  | 'short_name'
  | 'document_number'
  | 'tax_number'
  | 'phone'
  | 'email'
  | 'address'
  | 'notes';

@Component({
  selector: 'app-beneficiary-form',
  imports: [ReactiveFormsModule, RouterLink],
  templateUrl: './beneficiary-form.component.html',
  styleUrl: './beneficiary-form.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class BeneficiaryFormComponent {
  private readonly formBuilder = inject(FormBuilder);

  protected readonly form = this.formBuilder.nonNullable.group({
    type: ['individual' as BeneficiaryPayload['type'], Validators.required],
    full_name: ['', [Validators.required, Validators.maxLength(255)]],
    short_name: ['', Validators.maxLength(255)],
    document_number: ['', Validators.maxLength(100)],
    tax_number: ['', Validators.maxLength(50)],
    phone: ['', Validators.maxLength(50)],
    email: ['', [Validators.maxLength(255), Validators.email]],
    address: [''],
    notes: [''],
  });

  public readonly initialValue = input<BeneficiaryPayload | null>(null);
  public readonly fieldErrors = input<Record<string, string>>({});
  public readonly isSaving = input(false);
  public readonly submitLabel = input('Сохранить');
  public readonly cancelLink = input('/beneficiaries');
  public readonly formSubmitted = output<BeneficiaryPayload>();

  public constructor() {
    effect(() => {
      const value = this.initialValue();

      this.form.reset(
        {
          type: value?.type ?? 'individual',
          full_name: value?.full_name ?? '',
          short_name: value?.short_name ?? '',
          document_number: value?.document_number ?? '',
          tax_number: value?.tax_number ?? '',
          phone: value?.phone ?? '',
          email: value?.email ?? '',
          address: value?.address ?? '',
          notes: value?.notes ?? '',
        },
        { emitEvent: false },
      );
    });

    effect(() => {
      if (this.isSaving()) {
        this.form.disable({ emitEvent: false });
        return;
      }

      this.form.enable({ emitEvent: false });
    });
  }

  protected submit(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    const value = this.form.getRawValue();

    this.formSubmitted.emit({
      type: value.type,
      full_name: value.full_name.trim(),
      short_name: this.normalizeOptionalValue(value.short_name),
      document_number: this.normalizeOptionalValue(value.document_number),
      tax_number: this.normalizeOptionalValue(value.tax_number),
      phone: this.normalizeOptionalValue(value.phone),
      email: this.normalizeOptionalValue(value.email),
      address: this.normalizeOptionalValue(value.address),
      notes: this.normalizeOptionalValue(value.notes),
    });
  }

  protected getFieldError(fieldName: BeneficiaryField): string | null {
    const backendError = this.fieldErrors()[fieldName];

    if (backendError) {
      return backendError;
    }

    const control = this.form.controls[fieldName];

    if (!control.touched) {
      return null;
    }

    if (control.hasError('required')) {
      return fieldName === 'type' ? 'Выберите тип благополучателя.' : 'Поле обязательно для заполнения.';
    }

    if (control.hasError('email')) {
      return 'Введите корректный email.';
    }

    if (control.hasError('maxlength')) {
      return 'Превышена допустимая длина поля.';
    }

    return null;
  }

  private normalizeOptionalValue(value: string): string | null {
    const normalized = value.trim();

    return normalized === '' ? null : normalized;
  }
}
