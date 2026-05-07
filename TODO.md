# TODO - Live Salary Breakdown Preview

## Steps
1. Add AJAX endpoint in `PayrollController` to calculate preview breakdown for selected employee+month+attendance/deductions.
2. Refactor `PayrollService` to expose a preview calculation method (without DB write) that returns the breakdown numbers.
3. Add route in `routes/web.php` for the AJAX endpoint.
4. Update `resources/views/payroll/process.blade.php` right-side card to dynamically render breakdown values using JS `fetch()` on dropdown (and key input) changes.
5. Basic manual test in browser: select employee and verify right-side shows breakdown numbers without saving.

