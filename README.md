# Redermio

Система учёта благополучателей

Стек:
- backend: CodeIgniter 4
- frontend: Angular
- база данных: PostgreSQL
- окружение: Docker Compose

## Быстрый запуск

1. Скопировать env:

```bash
cp .env.example .env
```

2. Поднять контейнеры:

```bash
docker compose up -d --build
```

3. Выполнить миграции:

```bash
docker exec redermio_backend_php php spark migrate
```

4. Заполнить базу тестовыми данными для ручной проверки:

```bash
docker exec redermio_backend_php php spark db:seed DatabaseSeeder
```

5. При необходимости отдельно добавить нагрузочные данные:

```bash
docker exec redermio_backend_php php spark db:seed LoadTestBeneficiarySeeder
```

## Адреса сервисов

- frontend: `http://localhost:4200`
- backend API: `http://localhost:8080/api`
- PostgreSQL: `localhost:5432`