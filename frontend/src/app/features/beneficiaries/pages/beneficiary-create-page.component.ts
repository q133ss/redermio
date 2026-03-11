import { ChangeDetectionStrategy, Component } from '@angular/core';
import { RouterLink } from '@angular/router';

@Component({
  selector: 'app-beneficiary-create-page',
  imports: [RouterLink],
  templateUrl: './beneficiary-create-page.component.html',
  styleUrl: './beneficiary-create-page.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class BeneficiaryCreatePageComponent {}
