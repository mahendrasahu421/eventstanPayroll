# TODO - Nationality from Country Master

- [x] Create `countries` table migration.
- [x] Add `country_id` to `employees` table migration.
- [x] Add `Country` model.
- [x] Update `Employee` model with `country()` relation + make `country_id` fillable.
- [x] Update `EmployeeRequest` validation to include `country_id`.
- [x] Update `EmployeeController` to load countries for create/edit and to save `country_id` (and keep legacy `nationality`).
- [x] Update employee create/edit views: replace nationality textbox with country dropdown.
- [x] Update employee show view: display country name when available.
- [x] Update bulk import (`EmployeesImport`) to map nationality text to `country_id` when it matches country name.
- [x] Add `CountriesSeeder` and register it in `DatabaseSeeder` to insert default country records.


