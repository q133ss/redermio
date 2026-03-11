import { Routes } from '@angular/router';

import { BeneficiaryCreatePageComponent } from './features/beneficiaries/pages/beneficiary-create-page.component';
import { BeneficiaryListPageComponent } from './features/beneficiaries/pages/beneficiary-list-page.component';

export const routes: Routes = [
  {
    path: '',
    pathMatch: 'full',
    redirectTo: 'beneficiaries',
  },
  {
    path: 'beneficiaries',
    component: BeneficiaryListPageComponent,
  },
  {
    path: 'beneficiaries/create',
    component: BeneficiaryCreatePageComponent,
  },
];
