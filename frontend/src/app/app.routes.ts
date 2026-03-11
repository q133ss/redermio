import { Routes } from '@angular/router';

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
];
