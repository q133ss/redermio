import { Component, inject, signal } from '@angular/core';
import { EMPTY } from 'rxjs';
import { catchError } from 'rxjs/operators';

import { API_BASE_URL } from './core/config/runtime-config';
import { HealthApiService } from './core/services/health-api.service';

@Component({
  selector: 'app-root',
  templateUrl: './app.html',
  styleUrl: './app.scss',
})
export class App {
  private readonly healthApiService = inject(HealthApiService);

  protected readonly apiBaseUrl = inject(API_BASE_URL);
  protected readonly message = signal('Проверяем ответ от backend...');

  public constructor() {
    this.checkBackend();
  }

  protected retryHealthCheck(): void {
    this.checkBackend();
  }

  private checkBackend(): void {
    this.message.set('Проверяем ответ от backend...');

    this.healthApiService
      .getHealth()
      .pipe(
        catchError(() => {
          this.message.set('Backend недоступен');

          return EMPTY;
        }),
      )
      .subscribe((response) => {
        this.message.set(response.message);
      });
  }
}
