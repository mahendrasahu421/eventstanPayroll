# TODO

## Completed / In Progress
- [x] Fix Emirates ID expiry date not importing into employee_documents.expiry_date


## Steps
1. Update `app/Imports/EmployeesImport.php` to normalize Excel heading keys (trim/case-insensitive) for Emirates ID expiry columns.
2. Update `parseDate()` to support Excel numeric date serials (so `Carbon::parse()` failures return correct Y-m-d).
3. Keep backward compatible support for existing header spellings.
4. Test bulk import with a sample CSV/XLSX where Emirates expiry previously failed.
5. Verify in UI/DB that `employee_documents` row for `document_type='emirates_id'` has correct `expiry_date`.

