# Postman Collection - PolicyEval API

## Files

| File | Description |
|------|-------------|
| `PolicyEval-All.postman_collection.json` | Complete collection (all modules) |
| `PolicyEval-Local.postman_environment.json` | Environment variables |

## Collection Structure

```
PolicyEval - All API
├── Auth
│   ├── Register
│   ├── Login
│   ├── Logout
│   └── Get Profile
├── Admin - Periode
│   ├── List Periods
│   ├── Create Period
│   ├── Get Period
│   ├── Update Period
│   └── Delete Period
├── Admin - Kuesioner
│   ├── List Questionnaires
│   ├── Create Questionnaire
│   ├── Get Questionnaire
│   ├── Update Questionnaire
│   └── Delete Questionnaire
├── Admin - Komponen
│   ├── List Components
│   ├── Create Component
│   ├── Get Component
│   ├── Update Component
│   └── Delete Component
├── Admin - Sub Komponen
│   ├── List Sub-Components
│   ├── Create Sub-Component
│   ├── Get Sub-Component
│   ├── Update Sub-Component
│   └── Delete Sub-Component
├── Admin - Indikator (coming soon)
├── Admin - Pertanyaan (coming soon)
├── Admin - Responden (coming soon)
└── Admin - Rekomendasi (coming soon)
```

## Response Format

### Success (List)
```json
{
  "status": true,
  "message": "Data retrieved successfully",
  "data": {
    "contents": [],
    "meta": {
      "page": 1,
      "limit": 10,
      "total": 100
    }
  }
}
```

### Success (Single/Create/Update/Delete)
```json
{
  "status": true,
  "message": "Action completed successfully",
  "data": {}
}
```

### Error
```json
{
  "status": false,
  "message": "Error message",
  "errors": []
}
```

## Cara Import

1. Buka Postman
2. Klik **Import** → drag & drop `PolicyEval-All.postman_collection.json`
3. Import environment: `PolicyEval-Local.postman_environment.json`
4. Select environment: **PolicyEval - Local Development**

## Auto-Save Token

Tambahkan di tab **Tests** pada request Login:

```javascript
if (pm.response.code === 200) {
    const response = pm.response.json();
    if (response.status && response.data.token) {
        pm.environment.set("authToken", response.data.token);
    }
}
```

## Test Data

| User | Email | Password | Role |
|------|-------|----------|------|
| Admin | admin@cbt.com | password | ADMIN |
| Respondent | respondent@sekolah.id | password123 | RESPONDENT |
