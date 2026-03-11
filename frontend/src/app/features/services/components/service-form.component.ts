import { ChangeDetectionStrategy, Component, effect, inject, input, output } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';

import { ServicePayload } from '../models/service.models';

type ServiceField = 'name' | 'description' | 'is_active';

@Component({
  selector: 'app-service-form',
  imports: [ReactiveFormsModule],
  templateUrl: './service-form.component.html',
  styleUrl: './service-form.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ServiceFormComponent {
  private readonly formBuilder = inject(FormBuilder);

  protected readonly form = this.formBuilder.nonNullable.group({
    name: ['', [Validators.required, Validators.maxLength(255)]],
    description: [''],
    is_active: [true],
  });

  public readonly initialValue = input<ServicePayload | null>(null);
  public readonly fieldErrors = input<Record<string, string>>({});
  public readonly isSaving = input(false);
  public readonly submitLabel = input('Сохранить');
  public readonly formSubmitted = output<ServicePayload>();
  public readonly canceled = output<void>();

  public constructor() {
    effect(() => {
      const value = this.initialValue();

      this.form.reset(
        {
          name: value?.name ?? '',
          description: value?.description ?? '',
          is_active: value?.is_active ?? true,
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
      name: value.name.trim(),
      description: this.normalizeOptionalValue(value.description),
      is_active: value.is_active,
    });
  }

  protected cancel(): void {
    this.canceled.emit();
  }

  protected getFieldError(fieldName: ServiceField): string | null {
    const backendError = this.fieldErrors()[fieldName];

    if (backendError) {
      return backendError;
    }

    const control = this.form.controls[fieldName];

    if (!control.touched) {
      return null;
    }

    if (control.hasError('required')) {
      return 'Поле обязательно для заполнения.';
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
